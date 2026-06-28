<?php
session_start();
include '../../config/database.php';

// CẤU HÌNH PHÂN TRANG
$limit = 9; // Hiển thị 9 đề thi mỗi trang
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// LẤY THAM SỐ TÌM KIẾM & LỌC
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$subject_filter = isset($_GET['subject']) ? $_GET['subject'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// XÂY DỰNG CÂU LỆNH SQL ĐỘNG (Dynamic Query)
$where_clauses = ["q.status = 'completed'"];
$params = [];
$types = "";

if (!empty($search)) {
    $where_clauses[] = "(q.title LIKE ? OR q.subject LIKE ? OR u.full_name LIKE ?)";
    $search_param = "%{$search}%";
    array_push($params, $search_param, $search_param, $search_param);
    $types .= "sss";
}

if (!empty($subject_filter)) {
    $where_clauses[] = "q.subject = ?";
    $params[] = $subject_filter;
    $types .= "s";
}

$where_sql = implode(" AND ", $where_clauses);
$order_sql = ($sort === 'oldest') ? "ASC" : "DESC";

// 1. Đếm tổng số record để làm phân trang
$count_query = "SELECT COUNT(q.id) as total FROM quizzes q JOIN users u ON q.creator_username = u.username WHERE $where_sql";
$stmt_count = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_quizzes = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_quizzes / $limit);

// 2. Lấy dữ liệu chính
$query = "SELECT q.*, u.full_name AS creator_name 
          FROM quizzes q JOIN users u ON q.creator_username = u.username 
          WHERE $where_sql 
          ORDER BY q.created_at $order_sql 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

// Gắn thêm limit và offset vào mảng params
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt->bind_param($types, ...$params);
$stmt->execute();
$quizzes = $stmt->get_result();

// Hàm tạo màu sắc icon ngẫu nhiên theo môn học
function getSubjectStyle($subject) {
    $sub = mb_strtolower($subject, 'UTF-8');
    if (strpos($sub, 'toán') !== false) return ['icon' => 'fa-calculator', 'color' => '#3b82f6', 'bg' => '#eff6ff'];
    if (strpos($sub, 'lý') !== false) return ['icon' => 'fa-magnet', 'color' => '#f59e0b', 'bg' => '#fef3c7'];
    if (strpos($sub, 'hóa') !== false) return ['icon' => 'fa-flask', 'color' => '#10b981', 'bg' => '#d1fae5'];
    if (strpos($sub, 'anh') !== false) return ['icon' => 'fa-language', 'color' => '#ef4444', 'bg' => '#fee2e2'];
    if (strpos($sub, 'tin') !== false) return ['icon' => 'fa-laptop-code', 'color' => '#8b5cf6', 'bg' => '#ede9fe'];
    return ['icon' => 'fa-book-open', 'color' => '#64748b', 'bg' => '#f1f5f9'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khám phá Đề Thi - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <style>
        /* CSS nội bộ bổ sung cho giao diện Explorer */
        .explorer-hero {
            background: linear-gradient(135deg, var(--primary) 0%, #312e81 100%);
            border-radius: var(--radius-lg);
            padding: 40px;
            color: white;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        .explorer-hero h1 { margin: 0 0 10px 0; font-size: 2.2rem; font-weight: 800; }
        .explorer-hero p { margin: 0; font-size: 1.1rem; opacity: 0.9; }
        
        .filter-bar {
            background: #fff;
            padding: 20px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-bar .form-control { flex: 1; min-width: 200px; margin: 0; }
        .filter-bar select.form-control { flex: 0.5; min-width: 150px; cursor: pointer; }
        
        .q-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .q-icon-box { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .q-badge-new { background: #ef4444; color: white; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
        
        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 40px; }
        .page-link { padding: 10px 15px; border-radius: 8px; background: #fff; border: 1px solid var(--border-color); color: var(--text-main); text-decoration: none; font-weight: 600; transition: var(--transition); }
        .page-link:hover { border-color: var(--primary); color: var(--primary); }
        .page-link.active { background: var(--primary); color: white; border-color: var(--primary); pointer-events: none; }
    </style>
</head>
<body>
    <div class="container">
        
        <a href="add_question_hub.php" style="color: var(--text-muted); text-decoration: none; font-weight: 600; display: inline-block; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Trở về Không gian làm việc
        </a>

        <div class="explorer-hero">
            <i class="fas fa-rocket" style="font-size: 3rem; opacity: 0.2; position: absolute; right: 50px; top: -10px;"></i>
            <h1>Cộng Đồng Đề Thi</h1>
            <p>Khám phá và chinh phục <?php echo $total_quizzes; ?> bộ đề từ hàng ngàn giáo viên và học viên</p>
        </div>

        <form action="" method="GET" class="filter-bar">
            <div style="flex: 1; min-width: 250px; position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 15px; top: 15px; color: #94a3b8;"></i>
                <input type="text" name="search" class="form-control" style="padding-left: 40px;" placeholder="Nhập tên đề, tên tác giả..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <select name="subject" class="form-control">
                <option value="">-- Tất cả môn học --</option>
                <option value="Toán học" <?php if($subject_filter == 'Toán học') echo 'selected'; ?>>Toán học</option>
                <option value="Vật lý" <?php if($subject_filter == 'Vật lý') echo 'selected'; ?>>Vật lý</option>
                <option value="Hóa học" <?php if($subject_filter == 'Hóa học') echo 'selected'; ?>>Hóa học</option>
                <option value="Tiếng Anh" <?php if($subject_filter == 'Tiếng Anh') echo 'selected'; ?>>Tiếng Anh</option>
            </select>

            <select name="sort" class="form-control">
                <option value="newest" <?php if($sort == 'newest') echo 'selected'; ?>>Mới nhất trước</option>
                <option value="oldest" <?php if($sort == 'oldest') echo 'selected'; ?>>Cũ nhất trước</option>
            </select>

            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc</button>
            <?php if(!empty($search) || !empty($subject_filter)): ?>
                <a href="sum_question.php" class="btn btn-outline" style="padding: 12px;"><i class="fas fa-sync-alt"></i></a>
            <?php endif; ?>
        </form>

        <div class="grid-3">
            <?php if ($quizzes->num_rows > 0): ?>
                <?php while($q = $quizzes->fetch_assoc()): 
                    $style = getSubjectStyle($q['subject']);
                    
                    // Logic tính toán nhãn "MỚI" (Nếu đề tạo trong vòng 7 ngày)
                    $created_time = strtotime($q['created_at']);
                    $is_new = ($created_time > strtotime('-7 days'));
                ?>
                    <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div class="q-card-header">
                                <div class="q-icon-box" style="background: <?php echo $style['bg']; ?>; color: <?php echo $style['color']; ?>;">
                                    <i class="<?php echo $style['icon']; ?>"></i>
                                </div>
                                <?php if($is_new): ?>
                                    <span class="q-badge-new"><i class="fas fa-fire"></i> MỚI</span>
                                <?php endif; ?>
                            </div>
                            
                            <span class="badge" style="background: #f1f5f9; color: var(--text-muted); margin-bottom: 10px; display: inline-block;">
                                <?php echo htmlspecialchars($q['subject']); ?>
                            </span>
                            
                            <h3 style="margin: 0 0 10px 0; font-size: 1.15rem; line-height: 1.4; color: var(--text-main);">
                                <?php echo htmlspecialchars($q['title']); ?>
                            </h3>
                            
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 8px;">
                                <i class="fas fa-user-edit" style="width: 20px;"></i> Tác giả: <strong><?php echo htmlspecialchars($q['creator_name']); ?></strong>
                            </div>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 25px;">
                                <i class="fas fa-layer-group" style="width: 20px;"></i> Độ dài: <strong><?php echo $q['num_questions']; ?> câu hỏi</strong>
                            </div>
                        </div>
                        <a href="take_quiz.php?id=<?php echo $q['id']; ?>" class="btn btn-success" style="width: 100%; border-radius: 8px;">
                            Thử sức ngay <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1;" class="text-center mt-4">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" alt="Empty" style="width: 120px; opacity: 0.4; margin-bottom: 15px;">
                    <h3 style="color: var(--text-muted);">Không tìm thấy bộ đề nào khớp với bộ lọc của bạn.</h3>
                    <a href="sum_question.php" class="btn btn-primary mt-4">Xóa bộ lọc</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php 
                // Giữ lại các tham số filter trên URL khi chuyển trang
                $url_params = $_GET;
                for ($i = 1; $i <= $total_pages; $i++): 
                    $url_params['page'] = $i;
                    $link = '?' . http_build_query($url_params);
                    $active = ($i == $page) ? 'active' : '';
            ?>
                <a href="<?php echo $link; ?>" class="page-link <?php echo $active; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>