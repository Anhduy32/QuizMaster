<?php
session_start();
include '../../config/database.php';

// ================= KIỂM TRA ĐĂNG NHẬP =================
if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit();
}

// ================= CẤU HÌNH PHÂN TRANG =================
$limit = 9;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
$offset = ($page - 1) * $limit;

// ================= LẤY THAM SỐ TÌM KIẾM & LỌC =================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$subject_filter = isset($_GET['subject']) ? trim($_GET['subject']) : '';
$audience_filter = isset($_GET['audience']) ? trim($_GET['audience']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// ================= XÂY DỰNG CÂU LỆNH SQL ĐỘNG =================
// Cập nhật: Cho phép hiển thị nếu có câu hỏi HOẶC có file đính kèm (PDF)
$where_clauses = ["q.status = 'completed'", "(q.num_questions > 0 OR q.file_path IS NOT NULL)"];
$params = [];
$types = "";

if (!empty($search)) {
    $where_clauses[] = "(q.title LIKE ? OR q.subject LIKE ? OR u.full_name LIKE ?)";
    $escaped_search = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search);
    $search_param = "%{$escaped_search}%";
    array_push($params, $search_param, $search_param, $search_param);
    $types .= "sss";
}

if (!empty($subject_filter)) {
    $where_clauses[] = "q.subject = ?";
    $params[] = $subject_filter;
    $types .= "s";
}

if (!empty($audience_filter)) {
    $where_clauses[] = "q.target_audience = ?";
    $params[] = $audience_filter;
    $types .= "s";
}

$where_sql = implode(" AND ", $where_clauses);

if ($sort === 'popular') {
    $order_sql = "ORDER BY q.views DESC, q.created_at DESC";
} elseif ($sort === 'oldest') {
    $order_sql = "ORDER BY q.created_at ASC";
} else {
    $order_sql = "ORDER BY q.created_at DESC";
}

// ================= ĐẾM TỔNG RECORD =================
$count_query = "SELECT COUNT(q.id) as total FROM quizzes q JOIN users u ON q.creator_username = u.username WHERE $where_sql";
$stmt_count = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_quizzes = $stmt_count->get_result()->fetch_assoc()['total'] ?? 0;

$total_pages = ceil($total_quizzes / $limit) ?: 1; 
if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// ================= LẤY DỮ LIỆU CHÍNH =================
$query = "SELECT q.*, u.full_name AS creator_name 
          FROM quizzes q 
          JOIN users u ON q.creator_username = u.username 
          WHERE $where_sql 
          $order_sql 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt->bind_param($types, ...$params);
$stmt->execute();
$quizzes = $stmt->get_result();

// ================= HÀM HỖ TRỢ =================
function getSubjectStyle($subject) {
    $sub = mb_strtolower($subject, 'UTF-8');
    $styles = [
        'toán' => ['icon' => 'fa-square-root-alt', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', 'color' => '#667eea', 'bg' => 'rgba(102, 126, 234, 0.1)'],
        'lý'   => ['icon' => 'fa-atom', 'gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)', 'color' => '#f5576c', 'bg' => 'rgba(245, 87, 108, 0.1)'],
        'hóa'  => ['icon' => 'fa-flask', 'gradient' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)', 'color' => '#4facfe', 'bg' => 'rgba(79, 172, 254, 0.1)'],
        'anh'  => ['icon' => 'fa-language', 'gradient' => 'linear-gradient(135deg, #f43f5e 0%, #e11d48 100%)', 'color' => '#e11d48', 'bg' => 'rgba(225, 29, 72, 0.1)'],
        'tin'  => ['icon' => 'fa-laptop-code', 'gradient' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)', 'color' => '#43e97b', 'bg' => 'rgba(67, 233, 123, 0.1)']
    ];

    foreach ($styles as $key => $style) {
        if (strpos($sub, $key) !== false) return $style;
    }
    return ['icon' => 'fa-book-open', 'gradient' => 'linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%)', 'color' => '#8b5cf6', 'bg' => 'rgba(139, 92, 246, 0.1)'];
}

// ================= PAGE CONFIG =================
$page_title = 'Khám phá Đề Thi - QuizMaster';
$page_css = 'sum_question.css';

require_once '../../includes/layouts/header.php';
?>

<!-- ================= NỘI DUNG CHÍNH ================= -->
<main class="main-wrapper">
    <div class="content-wrap">
        <div class="explore-container">
            
            <a href="add_question_hub.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Trở về Không gian làm việc
            </a>

            <div class="explore-header">
                <h1 class="explore-title">
                    <i class="fas fa-rocket"></i> Cộng Đồng Đề Thi
                </h1>
                <p class="explore-desc">
                    Khám phá và chinh phục <strong><?php echo number_format($total_quizzes); ?></strong> bộ đề từ hàng ngàn giáo viên và học viên
                </p>
            </div>

            <div class="filter-section">
                <form action="" method="GET" class="filter-form">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Nhập tên đề, tên tác giả..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <select name="subject" class="filter-select">
                        <option value="">-- Môn học --</option>
                        <option value="Toán học" <?php if($subject_filter == 'Toán học') echo 'selected'; ?>>Toán học</option>
                        <option value="Vật lý" <?php if($subject_filter == 'Vật lý') echo 'selected'; ?>>Vật lý</option>
                        <option value="Hóa học" <?php if($subject_filter == 'Hóa học') echo 'selected'; ?>>Hóa học</option>
                        <option value="Tiếng Anh" <?php if($subject_filter == 'Tiếng Anh') echo 'selected'; ?>>Tiếng Anh</option>
                    </select>

                    <select name="audience" class="filter-select">
                        <option value="">-- Đối tượng --</option>
                        <option value="hoc_sinh" <?php if($audience_filter == 'hoc_sinh') echo 'selected'; ?>>Học sinh phổ thông</option>
                        <option value="sinh_vien" <?php if($audience_filter == 'sinh_vien') echo 'selected'; ?>>Sinh viên đại học</option>
                    </select>

                    <select name="sort" class="filter-select">
                        <option value="newest" <?php if($sort == 'newest') echo 'selected'; ?>>Mới nhất trước</option>
                        <option value="popular" <?php if($sort == 'popular') echo 'selected'; ?>>Nhiều lượt thi nhất</option>
                        <option value="oldest" <?php if($sort == 'oldest') echo 'selected'; ?>>Cũ nhất trước</option>
                    </select>

                    <button type="submit" class="btn-filter">
                        <i class="fas fa-sliders-h"></i> Lọc
                    </button>
                    
                    <?php if(!empty($search) || !empty($subject_filter) || !empty($audience_filter)): ?>
                        <a href="sum_question.php" class="btn-filter-reset" title="Xóa bộ lọc">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="quiz-grid">
                <?php if ($quizzes && $quizzes->num_rows > 0): ?>
                    <?php while($q = $quizzes->fetch_assoc()): 
                        $style = getSubjectStyle($q['subject']);
                        $created_time = strtotime($q['created_at']);
                        $is_new = ($created_time > strtotime('-7 days'));
                        $initials = strtoupper(substr($q['creator_name'], 0, 1));
                    ?>
                        <div class="extended-quiz-card">
                            <div class="card-top">
                                <div class="subject-icon-box" style="background: <?php echo $style['bg']; ?>; color: <?php echo $style['color']; ?>;">
                                    <i class="fas <?php echo $style['icon']; ?>"></i>
                                </div>
                                <?php if($is_new): ?>
                                    <span class="badge-new">🔥 Mới</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-mid">
                                <span class="subject-badge">
                                    <?php echo htmlspecialchars($q['subject']); ?>
                                </span>
                                
                                <h3 class="quiz-item-title">
                                    <?php echo htmlspecialchars($q['title']); ?>
                                    <!-- Hiển thị nhãn PDF nếu đề thi có file đính kèm -->
                                    <?php if (!empty($q['file_path'])): ?>
                                        <span style="display: inline-block; background: #fee2e2; color: #dc2626; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; border: 1px solid #fecaca; vertical-align: middle; margin-left: 6px;">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </span>
                                    <?php endif; ?>
                                </h3>
                                
                                <div class="quiz-item-meta">
                                    <i class="fas fa-user-edit"></i> Tác giả: <strong><?php echo htmlspecialchars($q['creator_name']); ?></strong>
                                </div>
                                <div class="quiz-item-meta" style="display: flex; gap: 12px; align-items: center;">
                                    <span><i class="fas fa-layer-group"></i> Số lượng: <strong><?php echo (int)$q['num_questions']; ?> câu</strong></span>
                                    
                                    <!-- Thêm trạng thái Có đáp án hay Không -->
                                    <?php if (isset($q['has_answers'])): ?>
                                        <span style="color: #cbd5e1;">|</span>
                                        <span>
                                            <i class="fas <?php echo $q['has_answers'] ? 'fa-check-circle' : 'fa-times-circle'; ?>" style="color: <?php echo $q['has_answers'] ? '#059669' : '#dc2626'; ?>;"></i> 
                                            Đáp án: <strong><?php echo $q['has_answers'] ? 'Có' : 'Không'; ?></strong>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="quiz-item-meta">
                                    <i class="fas fa-fire" style="color: #dd6b20;"></i> Lượt thi: <strong><?php echo (int)$q['views']; ?></strong>
                                </div>
                            </div>

                            <div class="card-bottom">
                                <div class="creator-info">
                                    <div class="creator-img" style="background: <?php echo $style['gradient']; ?>; color: white;">
                                        <?php echo $initials; ?>
                                    </div>
                                    <div class="creator-text">
                                        <h4><?php echo htmlspecialchars($q['creator_name']); ?></h4>
                                        <span><i class="far fa-calendar-alt" style="margin-right: 4px;"></i> <?php echo date('d/m/Y', strtotime($q['created_at'])); ?></span>
                                    </div>
                                </div>
                                <a href="quiz_detail.php?id=<?php echo (int)$q['id']; ?>" class="btn-start-quiz">
                                    Thử sức <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-knowledge-box">
                        <i class="fas fa-search"></i>
                        <h3>Không tìm thấy bộ đề nào</h3>
                        <p>Không có đề thi nào khớp với bộ lọc của bạn.</p>
                        <a href="sum_question.php" class="btn-create-now">
                            <i class="fas fa-sync-alt"></i> Xóa bộ lọc
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php 
                    $url_params = $_GET;
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    if ($start_page > 1) {
                        $url_params['page'] = 1;
                        echo '<a href="?' . http_build_query($url_params) . '" class="page-link">1</a>';
                        if ($start_page > 2) {
                            echo '<span class="page-link" style="border:none; background:transparent; box-shadow:none; cursor:default;">...</span>';
                        }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++): 
                        $url_params['page'] = $i;
                        $active = ($i == $page) ? 'active' : '';
                ?>
                    <a href="?<?php echo http_build_query($url_params); ?>" class="page-link <?php echo $active; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<span class="page-link" style="border:none; background:transparent; box-shadow:none; cursor:default;">...</span>';
                        }
                        $url_params['page'] = $total_pages;
                        echo '<a href="?' . http_build_query($url_params) . '" class="page-link">' . $total_pages . '</a>';
                    }
                ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</main>