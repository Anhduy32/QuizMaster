<?php
session_start();
include 'config/database.php'; 

if (!isset($_SESSION['username'])) {
    header('Location: modules/auth/login.php');
    exit();
}

$ten_dang_nhap = $_SESSION['username'];
date_default_timezone_set('Asia/Ho_Chi_Minh');

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
    if (strpos($sub, 'toán') !== false) return ['bg' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', 'icon' => 'fa-square-root-alt', 'color' => '#667eea'];
    if (strpos($sub, 'lý') !== false || strpos($sub, 'địa') !== false) return ['bg' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)', 'icon' => 'fa-landmark', 'color' => '#f5576c'];
    if (strpos($sub, 'anh') !== false) return ['bg' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)', 'icon' => 'fa-language', 'color' => '#4facfe'];
    if (strpos($sub, 'tin') !== false) return ['bg' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)', 'icon' => 'fa-laptop-code', 'color' => '#43e97b'];
    return ['bg' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)', 'icon' => 'fa-flask', 'color' => '#fa709a'];
}

// ================= PAGE CONFIG =================
$page_title = 'Bảng điều khiển - QuizMaster';
$page_css = 'home.css';
$show_auth_modal = false;

require_once 'includes/layouts/header.php';
?>

<!-- ================= SIDEBAR ================= -->
<?php require_once 'includes/sections/sidebar.php'; ?>


<!-- ================= NỘI DUNG CHÍNH ================= -->
<main class="main-wrapper">
    <div class="content-wrap">
        
        <!-- ===== HEADER GLASSMORPHISM ===== -->
        <header class="top-header">
            <div class="header-title">
                <h2>
                    <span class="badge-dashboard"><i class="fas fa-chart-pie"></i> Dashboard</span>
                    Tổng quan
                </h2>
            </div>
            <div class="header-actions">
                <div class="top-action-btns">
                    <a href="/WebTaoBoDeTuDong/modules/quiz/create_quiz/step1_create_quiz.php" class="btn-header-action btn-header-primary">
                        <i class="fas fa-plus-circle"></i> Tạo đề mới
                    </a>
                    <a href="/WebTaoBoDeTuDong/modules/quiz/add_question_hub.php" class="btn-header-action btn-header-secondary">
                        <i class="fas fa-puzzle-piece"></i> Thêm câu hỏi
                    </a>
                </div>
                <div class="btn-notification">
                    <i class="far fa-bell"></i>
                    <span class="badge-count">3</span>
                </div>
                <a href="/WebTaoBoDeTuDong/modules/user/update_profile.php" class="user-profile-widget">
                    <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Avatar">
                    <span><?php echo htmlspecialchars($ten_goi); ?></span>
                    <i class="fas fa-chevron-down" style="font-size: 0.7rem; color: #94a3b8;"></i>
                </a>
            </div>
        </header>

        <div class="dashboard-grid">
            <div class="main-content">
                
                <!-- ===== WELCOME BANNER ===== -->
                <div class="welcome-banner">
                    <div class="welcome-text">
                        <h1><?php echo $loi_chao; ?>, <span class="highlight-name"><?php echo htmlspecialchars($ten_goi); ?></span> 👋</h1>
                        <p>Bạn đã hoàn thành <strong><?php echo $tong_bai_da_lam; ?></strong> bài kiểm tra. Tiếp tục duy trì phong độ nhé!</p>
                        <a href="/WebTaoBoDeTuDong/modules/quiz/sum_question.php" class="btn-random-quiz">
                            <i class="fas fa-compass"></i> Khám phá ngay
                        </a>
                    </div>
                    <div class="decoration-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                </div>

                <!-- ===== QUICK STATS ===== -->
                <div class="quick-stats-row">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fas fa-tasks"></i></div>
                        <div class="stat-info">
                            <h4>Đã hoàn thành</h4>
                            <span><?php echo $tong_bai_da_lam; ?> <small>bài</small></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="fas fa-star"></i></div>
                        <div class="stat-info">
                            <h4>Điểm trung bình</h4>
                            <span><?php echo $diem_trung_binh; ?> <small>/10</small></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon orange"><i class="fas fa-fire"></i></div>
                        <div class="stat-info">
                            <h4>Chuỗi đăng nhập</h4>
                            <span><?php echo $chuoi_hien_tai; ?> <small>ngày</small></span>
                        </div>
                    </div>
                </div>

                <!-- ===== SUGGESTED QUIZZES ===== -->
<<<<<<< HEAD
                <!-- ===== SUGGESTED QUIZZES ===== -->
=======
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
                <section style="margin-bottom: 40px;">
                    <div class="section-header-custom">
                        <h2 class="section-title">
                            <span class="icon-wrapper"><i class="fas fa-lightbulb" style="color: #f59e0b;"></i></span>
                            Gợi ý luyện tập
                        </h2>
                        <a href="/WebTaoBoDeTuDong/modules/quiz/sum_question.php" class="view-all-link">
                            Xem tất cả <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="quiz-scroll-container">
                        <?php if ($res_suggest && $res_suggest->num_rows > 0): ?>
                            <?php while ($quiz = $res_suggest->fetch_assoc()): 
                                $style = getSubjectStyle($quiz['subject']);
                            ?>
<<<<<<< HEAD
                                <!-- Cập nhật link trỏ về quiz_detail.php -->
                                <a href="/WebTaoBoDeTuDong/modules/quiz/quiz_detail.php?id=<?php echo $quiz['id']; ?>" class="quiz-card-modern">
=======
                                <a href="/WebTaoBoDeTuDong/modules/quiz/take_quiz.php?id=<?php echo $quiz['id']; ?>" class="quiz-card-modern">
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
                                    <div class="card-top">
                                        <div class="card-icon" style="background: <?php echo $style['bg']; ?>">
                                            <i class="fas <?php echo $style['icon']; ?>"></i>
                                        </div>
                                        <span class="card-subject" style="color: <?php echo $style['color']; ?>;">
                                            <?php echo htmlspecialchars($quiz['subject']); ?>
                                        </span>
                                    </div>
<<<<<<< HEAD
                                    
                                    <h3 class="card-title">
                                        <?php echo htmlspecialchars($quiz['title']); ?>
                                        <!-- Thêm Badge PDF nếu có file_path -->
                                        <?php if (!empty($quiz['file_path'])): ?>
                                            <span style="display: inline-block; background: #fee2e2; color: #dc2626; font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; border: 1px solid #fecaca; vertical-align: middle; margin-left: 6px;">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </span>
                                        <?php endif; ?>
                                    </h3>
                                    
                                    <div class="card-footer">
                                        <span class="card-meta">
                                            <i class="fas fa-layer-group"></i> 
                                            <!-- Hiển thị linh hoạt số câu hoặc nhãn PDF -->
                                            <?php echo (int)$quiz['num_questions'] > 0 ? (int)$quiz['num_questions'] . ' Câu' : 'Đề PDF'; ?>
=======
                                    <h3 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                    <div class="card-footer">
                                        <span class="card-meta">
                                            <i class="fas fa-layer-group"></i> <?php echo $quiz['num_questions']; ?> Câu
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
                                        </span>
                                        <div class="btn-play">
                                            <i class="fas fa-play"></i>
                                        </div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: #94a3b8; grid-column: 1/-1; text-align: center; padding: 40px 0; font-size: 1rem;">
                                <i class="fas fa-inbox" style="display: block; font-size: 3rem; opacity: 0.3; margin-bottom: 12px;"></i>
                                Cộng đồng chưa có đề thi nào.
                            </p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <!-- ===== RIGHT SIDEBAR ===== -->
            <div class="right-sidebar">
                
                <!-- Goal Widget -->
                <div class="widget-card-glass">
                    <h3 class="widget-title">
                        <span class="title-icon"><i class="fas fa-bullseye"></i></span>
                        Mục tiêu tuần này
                    </h3>
                    <p style="font-size: 0.9rem; color: #64748b; margin: 0 0 14px 0;">
                        Hoàn thành <strong style="color: #0f5c6b;"><?php echo $muc_tieu_tuan; ?></strong> bài kiểm tra
                    </p>
                    <div class="goal-progress">
                        <?php 
                            $tien_do = ($weekly_taken / $muc_tieu_tuan) * 100;
                            if($tien_do > 100) $tien_do = 100;
                        ?>
                        <div class="goal-fill" style="width: <?php echo $tien_do; ?>%;"></div>
                    </div>
                    <div class="goal-text">
                        <span><i class="fas fa-check-circle" style="color: #0f5c6b;"></i> <?php echo $weekly_taken; ?> bài đã làm</span>
                        <span><?php echo $muc_tieu_tuan; ?> bài</span>
                    </div>
                    <?php if($weekly_taken >= $muc_tieu_tuan): ?>
                        <div class="goal-complete">
                            <i class="fas fa-trophy" style="font-size: 1.2rem;"></i>
                            Hoàn thành! Bạn thật tuyệt vời!
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Activity -->
                <div class="widget-card-glass">
                    <h3 class="widget-title">
                        <span class="title-icon"><i class="fas fa-history"></i></span>
                        Hoạt động gần đây
                    </h3>
                    <div class="activity-list">
                        <?php if ($res_history && $res_history->num_rows > 0): ?>
                            <?php while ($history = $res_history->fetch_assoc()): ?>
                                <div class="activity-item-modern">
                                    <div class="activity-icon"><i class="fas fa-check"></i></div>
                                    <div class="activity-info">
                                        <h4><?php echo htmlspecialchars($history['title']); ?></h4>
                                        <p>
                                            <span class="score-highlight"><?php echo $history['score']; ?> điểm</span>
                                            <span>•</span>
                                            <span><?php echo date('d/m', strtotime($history['completed_at'])); ?></span>
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            <a href="/WebTaoBoDeTuDong/modules/user/history.php" class="view-all-history">
                                Xem toàn bộ lịch sử <i class="fas fa-arrow-right" style="margin-left: 6px;"></i>
                            </a>
                        <?php else: ?>
                            <div style="text-align: center; padding: 24px 0; color: #94a3b8;">
                                <i class="fas fa-inbox" style="display: block; font-size: 2.5rem; opacity: 0.3; margin-bottom: 8px;"></i>
                                <p style="margin: 0; font-size: 0.9rem;">Chưa có hoạt động nào</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Tip -->
                <div class="widget-tip">
                    <div class="tip-content">
                        <div class="tip-icon"><i class="fas fa-lightbulb"></i></div>
                        <div class="tip-text">
                            <h4>💡 Mẹo học tập</h4>
                            <p>Làm ít nhất 2 bài mỗi ngày để ghi nhớ kiến thức tốt hơn.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ===== FOOTER ===== -->
    <?php require_once 'includes/layouts/footer.php'; ?>
</main>