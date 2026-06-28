<?php
session_start();
include 'config/database.php'; 

if (!isset($_SESSION['username'])) {
    header('Location: modules/auth/login.php');
    exit();
}

$ten_dang_nhap = $_SESSION['username'];
date_default_timezone_set('Asia/Ho_Chi_Minh');

// [Các đoạn PHP lấy dữ liệu người dùng, thống kê, chuỗi đăng nhập... GIỮ NGUYÊN NHƯ CŨ CỦA BẠN]
// LẤY THÔNG TIN NGƯỜI DÙNG
$truy_van = "SELECT * FROM users WHERE username = ?";
$chuan_bi = $conn->prepare($truy_van);
$chuan_bi->bind_param('s', $ten_dang_nhap);
$chuan_bi->execute();
$nguoi_dung = $chuan_bi->get_result()->fetch_assoc();

$ho_va_ten = $nguoi_dung['full_name'] ?? $ten_dang_nhap;
$ten_ngan_gon = explode(' ', trim($ho_va_ten));
$ten_goi = end($ten_ngan_gon); 

$avatar_url = !empty($nguoi_dung['picture']) ? $nguoi_dung['picture'] : "https://ui-avatars.com/api/?name=" . urlencode($ho_va_ten) . "&background=0f5c6b&color=fff&size=150";

$gio_hien_tai = date('H');
if ($gio_hien_tai >= 5 && $gio_hien_tai < 12) { $loi_chao = "Chào buổi sáng"; } 
elseif ($gio_hien_tai >= 12 && $gio_hien_tai < 18) { $loi_chao = "Chào buổi chiều"; } 
else { $loi_chao = "Chào buổi tối"; }

$hom_nay = date('Y-m-d');
$hom_qua = date('Y-m-d', strtotime('-1 day'));
$ngay_dang_nhap_cuoi = $nguoi_dung['last_login_date'];
$chuoi_hien_tai = $nguoi_dung['login_streak'] ?? 0;

if ($ngay_dang_nhap_cuoi !== $hom_nay) {
    $chuoi_hien_tai = ($ngay_dang_nhap_cuoi === $hom_qua) ? $chuoi_hien_tai + 1 : 1;
    $conn->query("UPDATE users SET last_login_date = '$hom_nay', login_streak = $chuoi_hien_tai WHERE username = '$ten_dang_nhap'");
}

// THỐNG KÊ
$stmt_stats = $conn->prepare("SELECT COUNT(id) as total_taken, AVG(score) as avg_score FROM quiz_history WHERE username = ?");
$stmt_stats->bind_param('s', $ten_dang_nhap);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
$tong_bai_da_lam = $stats['total_taken'] ?? 0;
$diem_trung_binh = $stats['avg_score'] ? round($stats['avg_score'], 1) : 0;

$stmt_recent = $conn->prepare("SELECT q.title, h.score, h.completed_at FROM quiz_history h JOIN quizzes q ON h.quiz_id = q.id WHERE h.username = ? ORDER BY h.completed_at DESC LIMIT 4");
$stmt_recent->bind_param('s', $ten_dang_nhap);
$stmt_recent->execute();
$res_history = $stmt_recent->get_result();

$stmt_weekly = $conn->prepare("SELECT COUNT(id) as weekly_taken FROM quiz_history WHERE username = ? AND YEARWEEK(completed_at, 1) = YEARWEEK(CURDATE(), 1)");
$stmt_weekly->bind_param('s', $ten_dang_nhap);
$stmt_weekly->execute();
$weekly_taken = $stmt_weekly->get_result()->fetch_assoc()['weekly_taken'] ?? 0;
$muc_tieu_tuan = 5;

$res_suggest = $conn->query("SELECT * FROM quizzes WHERE status = 'completed' ORDER BY RAND() LIMIT 6");

function getSubjectStyle($subject) {
    $sub = mb_strtolower($subject, 'UTF-8');
    if (strpos($sub, 'toán') !== false) return ['bg' => 'linear-gradient(135deg, #4299e1, #3182ce)', 'icon' => 'fa-square-root-alt'];
    if (strpos($sub, 'lý') !== false || strpos($sub, 'địa') !== false) return ['bg' => 'linear-gradient(135deg, #ed8936, #dd6b20)', 'icon' => 'fa-landmark'];
    if (strpos($sub, 'anh') !== false) return ['bg' => 'linear-gradient(135deg, #f56565, #e53e3e)', 'icon' => 'fa-language'];
    if (strpos($sub, 'tin') !== false) return ['bg' => 'linear-gradient(135deg, #9f7aea, #805ad5)', 'icon' => 'fa-laptop-code'];
    return ['bg' => 'linear-gradient(135deg, #48bb78, #38a169)', 'icon' => 'fa-flask'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/home.css?v=<?php echo time(); ?>">
    <style>
        .top-action-btns { display: flex; gap: 12px; margin-right: 25px; border-right: 2px solid #edf2f7; padding-right: 25px; }
        .btn-header-action { padding: 10px 18px; border-radius: 12px; font-weight: 700; font-size: 0.95rem; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-header-primary { background: var(--primary-teal); color: white; }
        .btn-header-primary:hover { background: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(15, 92, 107, 0.2); }
        .btn-header-secondary { background: #ebf8ff; color: #2b6cb0; }
        .btn-header-secondary:hover { background: #bee3f8; transform: translateY(-2px); }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-wrapper">
        <header class="top-header" style="justify-content: space-between; margin-bottom: 40px;">
            <div class="header-title">
                <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin: 0;">Tổng quan</h2>
            </div>
            
            <div class="header-actions">
                <div class="top-action-btns">
                    <a href="modules/quiz/create_step1.php" class="btn-header-action btn-header-primary">
                        <i class="fas fa-plus"></i> Tạo đề mới
                    </a>
                    <a href="modules/quiz/add_question_hub.php" class="btn-header-action btn-header-secondary">
                        <i class="fas fa-puzzle-piece"></i> Thêm câu hỏi
                    </a>
                </div>

                <div class="btn-notification"><i class="far fa-bell"></i></div>
                <a href="modules/user/update_profile.php" class="user-profile-widget" title="Cài đặt tài khoản">
                    <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Avatar">
                    <span><?php echo htmlspecialchars($ten_goi); ?></span>
                </a>
            </div>
        </header>

        <div class="dashboard-grid">
            
            <div class="main-content">
                
                <div class="welcome-banner">
                    <div class="welcome-text">
                        <h1><?php echo $loi_chao; ?>, <?php echo htmlspecialchars($ten_goi); ?>! 👋</h1>
                        <p>Bạn đã hoàn thành <?php echo $tong_bai_da_lam; ?> bài kiểm tra. Tiếp tục duy trì phong độ nhé!</p>
                        <a href="explore.php" class="btn-random-quiz"><i class="fas fa-random"></i> Khám phá ngay</a>
                    </div>
                    <div style="font-size: 6rem; opacity: 0.9; margin-right: 20px;"><i class="fas fa-rocket"></i></div>
                </div>

                <div class="quick-stats-row">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #ebf8ff; color: #3182ce;"><i class="fas fa-tasks"></i></div>
                        <div class="stat-info">
                            <h4>Đã hoàn thành</h4>
                            <span><?php echo $tong_bai_da_lam; ?> <small style="font-size: 14px; color: #718096;">bài</small></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #f0fff4; color: #38a169;"><i class="fas fa-star"></i></div>
                        <div class="stat-info">
                            <h4>Điểm trung bình</h4>
                            <span><?php echo $diem_trung_binh; ?> <small style="font-size: 14px; color: #718096;">/10</small></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #fffaf0; color: #dd6b20;"><i class="fas fa-fire"></i></div>
                        <div class="stat-info">
                            <h4>Chuỗi đăng nhập</h4>
                            <span><?php echo $chuoi_hien_tai; ?> <small style="font-size: 14px; color: #718096;">ngày</small></span>
                        </div>
                    </div>
                </div>

                <section style="margin-bottom: 40px;">
                    <div class="section-header">
                        <h2 class="section-title">Gợi ý luyện tập cho bạn</h2>
                        <a href="explore.php" class="view-all">Xem tất cả <i class="fas fa-angle-right"></i></a>
                    </div>
                    <div class="quiz-scroll-container">
                        <?php if ($res_suggest && $res_suggest->num_rows > 0): ?>
                            <?php while ($quiz = $res_suggest->fetch_assoc()): 
                                $style = getSubjectStyle($quiz['subject']);
                            ?>
                                <a href="modules/quiz/take_quiz.php?id=<?php echo $quiz['id']; ?>" class="quiz-card">
                                    <div class="card-icon" style="background: <?php echo $style['bg']; ?>">
                                        <i class="fas <?php echo $style['icon']; ?>"></i>
                                    </div>
                                    <span class="card-subject"><?php echo htmlspecialchars($quiz['subject']); ?></span>
                                    <h3 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                    <div class="card-meta">
                                        <span><i class="fas fa-layer-group"></i> <?php echo $quiz['num_questions']; ?> Câu</span>
                                        <div class="btn-play"><i class="fas fa-play"></i></div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: var(--text-muted);">Cộng đồng chưa có đề thi nào.</p>
                        <?php endif; ?>
                    </div>
                </section>

            </div>

            <div class="right-sidebar">
                <div class="widget-card">
                    <h3 class="widget-title"><i class="fas fa-bullseye" style="color: var(--primary-teal);"></i> Mục tiêu tuần này</h3>
                    <p style="font-size: 0.9rem; color: var(--text-muted);">Hoàn thành <?php echo $muc_tieu_tuan; ?> bài kiểm tra</p>
                    <div class="goal-progress">
                        <?php 
                            $tien_do = ($weekly_taken / $muc_tieu_tuan) * 100;
                            if($tien_do > 100) $tien_do = 100;
                        ?>
                        <div class="goal-fill" style="width: <?php echo $tien_do; ?>%;"></div>
                    </div>
                    <div class="goal-text">
                        <span><?php echo $weekly_taken; ?> bài đã làm</span>
                        <span><?php echo $muc_tieu_tuan; ?> bài</span>
                    </div>
                </div>

                <div class="widget-card">
                    <h3 class="widget-title"><i class="fas fa-history" style="color: var(--primary-teal);"></i> Hoạt động gần đây</h3>
                    <div class="activity-list">
                        <?php if ($res_history && $res_history->num_rows > 0): ?>
                            <?php while ($history = $res_history->fetch_assoc()): ?>
                                <div class="activity-item">
                                    <div class="activity-icon"><i class="fas fa-check"></i></div>
                                    <div class="activity-info">
                                        <h4><?php echo htmlspecialchars($history['title']); ?></h4>
                                        <p>Đạt <?php echo $history['score']; ?> điểm • <?php echo date('d/m', strtotime($history['completed_at'])); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            <a href="modules/user/history.php" class="view-all-history">Xem toàn bộ lịch sử</a>
                        <?php else: ?>
                            <p style="font-size: 0.9rem; color: var(--text-muted); text-align: center; padding: 10px 0;">Bạn chưa làm bài kiểm tra nào trong tuần này.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>