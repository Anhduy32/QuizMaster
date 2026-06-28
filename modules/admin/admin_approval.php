<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['username'])) {
    header('Location: login/login.php');
    exit();
}

$ten_dang_nhap = $_SESSION['username'];

// ================= KIỂM TRA QUYỀN ADMIN =================
$stmt_check = $conn->prepare("SELECT role FROM users WHERE username = ?");
$stmt_check->bind_param('s', $ten_dang_nhap);
$stmt_check->execute();
$user_data = $stmt_check->get_result()->fetch_assoc();

if (!$user_data || $user_data['role'] !== 'admin') {
    die("<div style='text-align:center; padding: 50px; font-family: sans-serif;'>
            <h1 style='color: #e53e3e;'>Truy cập bị từ chối</h1>
            <p>Bạn không có quyền Quản trị viên để xem trang này.</p>
            <a href='../home.php'>Quay về Bảng điều khiển</a>
         </div>");
}

// ================= XỬ LÝ DUYỆT / TỪ CHỐI =================
$thong_bao = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['quiz_id'])) {
    $quiz_id = (int)$_POST['quiz_id'];
    $action = $_POST['action'];
    
    $new_status = ($action === 'approve') ? 'completed' : 'rejected';
    
    $stmt_update = $conn->prepare("UPDATE quizzes SET status = ? WHERE id = ?");
    $stmt_update->bind_param('si', $new_status, $quiz_id);
    
    if ($stmt_update->execute()) {
        $msg = ($action === 'approve') ? "Đã DUYỆT đề thi công khai!" : "Đã TỪ CHỐI đề thi.";
        $color = ($action === 'approve') ? "#2f855a" : "#c53030";
        $bg = ($action === 'approve') ? "#f0fff4" : "#fff5f5";
        $thong_bao = "<div style='background: $bg; color: $color; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 700;'><i class='fas fa-info-circle'></i> $msg</div>";
    }
}

// ================= LẤY DANH SÁCH ĐỀ ĐANG CHỜ DUYỆT =================
$truy_van = "SELECT q.*, u.full_name AS creator_name 
             FROM quizzes q 
             JOIN users u ON q.creator_username = u.username 
             WHERE q.status = 'pending' 
             ORDER BY q.created_at ASC";
$danh_sach_cho = $conn->query($truy_van);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm duyệt nội dung - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/home.css?v=<?php echo time(); ?>">
    <style>
        .admin-container { max-width: 1100px; margin: 0 auto; }
        .admin-header { background: linear-gradient(135deg, #1a202c, #2d3748); color: white; padding: 30px; border-radius: 20px; box-shadow: var(--glass-shadow); margin-bottom: 30px; display: flex; align-items: center; gap: 20px; }
        .admin-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; }
        .admin-title { font-size: 1.8rem; font-weight: 800; margin-bottom: 5px; }
        .admin-desc { color: #a0aec0; font-size: 1rem; }
        
        .pending-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .pending-card { background: white; border-radius: 16px; padding: 25px; border: 2px solid #feebc8; box-shadow: 0 4px 15px rgba(221, 107, 32, 0.05); transition: 0.3s; }
        .pending-card:hover { border-color: #dd6b20; box-shadow: 0 10px 25px rgba(221, 107, 32, 0.15); transform: translateY(-3px); }
        
        .p-author { font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        .p-title { font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 15px; line-height: 1.4; }
        .p-meta { display: flex; gap: 15px; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px dashed var(--border-light); }
        
        .action-group { display: flex; gap: 10px; }
        .btn-decision { flex: 1; padding: 12px; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-approve { background: #f0fff4; color: #2f855a; border: 1px solid #c6f6d5; }
        .btn-approve:hover { background: #38a169; color: white; }
        .btn-reject { background: #fff5f5; color: #c53030; border: 1px solid #fed7d7; }
        .btn-reject:hover { background: #e53e3e; color: white; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <a href="../index.php" class="brand-logo">
            <i class="fa-solid fa-graduation-cap"></i> <span>QUIZMASTER</span>
        </a>
        <nav class="nav-menu">
            <a href="../home.php" class="nav-item"><i class="fas fa-home"></i> <span>Bảng điều khiển</span></a>
            <a href="question/sum_question.php" class="nav-item"><i class="fas fa-compass"></i> <span>Khám phá đề thi</span></a>
            <a href="my_library.php" class="nav-item"><i class="fas fa-folder-open"></i> <span>Thư viện của tôi</span></a>
            <a href="history.php" class="nav-item"><i class="fas fa-chart-bar"></i> <span>Lịch sử học tập</span></a>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-light);">
                <a href="admin_approval.php" class="nav-item active" style="color: #dd6b20;"><i class="fas fa-user-shield"></i> <span>Duyệt đề thi</span></a>
            </div>
        </nav>
    </aside>

    <main class="main-wrapper">
        <div class="admin-container">
            <div class="admin-header">
                <div class="admin-icon"><i class="fas fa-user-shield"></i></div>
                <div>
                    <h1 class="admin-title">Khu vực Kiểm Duyệt</h1>
                    <p class="admin-desc">Quản lý các bộ đề do cộng đồng gửi lên. Phê duyệt để hiển thị công khai hoặc từ chối nếu vi phạm.</p>
                </div>
            </div>

            <?php echo $thong_bao; ?>

            <div class="pending-grid">
                <?php if ($danh_sach_cho && $danh_sach_cho->num_rows > 0): ?>
                    <?php while ($quiz = $danh_sach_cho->fetch_assoc()): ?>
                        <div class="pending-card">
                            <div class="p-author">
                                <i class="fas fa-user-edit"></i> Tác giả: <?php echo htmlspecialchars($quiz['creator_name']); ?>
                            </div>
                            <h3 class="p-title"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                            <div class="p-meta">
                                <span><i class="fas fa-book"></i> <?php echo htmlspecialchars($quiz['subject']); ?></span>
                                <span><i class="fas fa-list"></i> <?php echo $quiz['num_questions']; ?> câu</span>
                                <span><i class="fas fa-clock"></i> <?php echo $quiz['time_limit']; ?>'</span>
                            </div>
                            
                            <div class="action-group">
                                <form method="POST" style="flex: 1;">
                                    <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-decision btn-approve" onclick="return confirm('Duyệt đề thi này lên Kho khám phá?');">
                                        <i class="fas fa-check"></i> Duyệt ngay
                                    </button>
                                </form>
                                <form method="POST" style="flex: 1;">
                                    <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn-decision btn-reject" onclick="return confirm('Từ chối đề thi này và yêu cầu tác giả sửa lại?');">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align:center; padding: 60px; background: white; border-radius: 16px; border: 2px dashed #cbd5e0;">
                        <i class="fas fa-mug-hot" style="font-size: 3rem; color: #cbd5e0; margin-bottom: 15px;"></i>
                        <h3 style="color: var(--text-main);">Tuyệt vời! Không có đề thi nào đang tồn đọng.</h3>
                        <p style="color: var(--text-muted);">Bạn có thể nghỉ ngơi hoặc đi làm bài test.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>