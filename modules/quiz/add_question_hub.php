<?php
session_start();
include '../../config/database.php'; 

if (!isset($_SESSION['username'])) { 
    header("Location: ../auth/login.php"); 
    exit(); 
}

$username = $_SESSION['username'];

// Kiểm tra session full_name (Nếu chưa có thì truy vấn DB)
if (isset($_SESSION['full_name'])) {
    $ho_va_ten = $_SESSION['full_name'];
} else {
    $stmt_user = $conn->prepare("SELECT full_name FROM users WHERE username = ?");
    $stmt_user->bind_param("s", $username);
    $stmt_user->execute();
    $user_data = $stmt_user->get_result()->fetch_assoc();
    $ho_va_ten = $user_data['full_name'] ?? $username;
}

$mang_ten = explode(' ', trim($ho_va_ten));
$ten_goi = end($mang_ten); 

$stmt_stats = $conn->prepare("
    SELECT 
        COUNT(id) as total_quizzes,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count,
        SUM(num_questions) as total_questions
    FROM quizzes WHERE creator_username = ?
");
$stmt_stats->bind_param("s", $username);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
$draft_count = $stats['draft_count'] ?? 0;
$total_quizzes = $stats['total_quizzes'] ?? 0;
$total_questions = $stats['total_questions'] ?? 0;

// TỐI ƯU LOGIC: Ưu tiên kéo 1 bản nháp (nếu có) và 2 bản đã hoàn thành
$stmt_recent = $conn->prepare("
    (SELECT id, title, status, created_at FROM quizzes 
     WHERE creator_username = ? AND status = 'draft' 
     ORDER BY created_at DESC LIMIT 1)
    UNION ALL
    (SELECT id, title, status, created_at FROM quizzes 
     WHERE creator_username = ? AND status = 'completed' 
     ORDER BY created_at DESC LIMIT 2)
");
$stmt_recent->bind_param("ss", $username, $username);
$stmt_recent->execute();
$recent_quizzes = $stmt_recent->get_result();

// ================= PAGE CONFIG =================
$page_title = 'Không gian làm việc - QuizMaster';
$page_css = 'add_question.css'; 

require_once '../../includes/layouts/header.php';
?>
<main class="main-wrapper">
    <div class="content-wrap">
        <div class="hub-container">
            
            <nav class="breadcrumb">
                <a href="../../home.php"><i class="fas fa-home"></i> Bảng điều khiển</a> 
                <span style="margin: 0 10px;">/</span> 
                <strong>Không gian làm việc</strong>
            </nav>

            <div class="hub-banner">
                <div class="hub-banner-content">
                    <h1>Chào <?php echo htmlspecialchars($ten_goi); ?>, sẵn sàng sáng tạo chưa?</h1>
                    <p>Khởi tạo những bài giảng đột phá và quản lý kho dữ liệu của bạn.</p>
                    
                    <div class="creator-mini-stats">
                        <span>Đã tạo: <strong><?php echo $total_quizzes; ?></strong> Đề thi</span>
                        <span>Ngân hàng: <strong><?php echo (int)$total_questions; ?></strong> Câu hỏi</span>
                        <?php if($draft_count > 0): ?>
                            <span style="color: #fca5a5;">(<?php echo $draft_count; ?> Bản nháp)</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 3 Công cụ cốt lõi -->
            <div class="workspace-grid">
                <a href="create_quiz/step1_create_quiz.php" class="workspace-card card-primary">
                    <div class="ws-icon icon-blue"><i class="fas fa-magic"></i></div>
                    <h3 class="ws-title">Tạo Đề Thi Mới</h3>
                    <p class="ws-desc">Khởi tạo bộ đề bằng thủ công hoặc tự động bóc tách từ file PDF/Word.</p>
                    <div style="margin-top: 20px; font-weight: 700; color: var(--primary-teal);">Bắt đầu ngay <i class="fas fa-arrow-right"></i></div>
                </a>
                
                <a href="my_library.php" class="workspace-card">
                    <div class="ws-icon icon-green"><i class="fas fa-book"></i></div>
                    <h3 class="ws-title">Thư Viện Của Tôi</h3>
                    <p class="ws-desc">Quản lý, chỉnh sửa và theo dõi trạng thái các đề thi bạn đã biên soạn.</p>
                </a>
                
                <a href="sum_question.php" class="workspace-card">
                    <div class="ws-icon icon-purple"><i class="fas fa-globe"></i></div>
                    <h3 class="ws-title">Khám Phá Cộng Đồng</h3>
                    <p class="ws-desc">Tìm kiếm tài nguyên từ các giáo viên và học viên khác trên toàn quốc.</p>
                </a>
            </div>
            
            <div class="secondary-grid">
                
                <div class="section-box">
                    <h3 class="section-title"><i class="fas fa-history" style="color: var(--primary-teal);"></i> Đề thi vừa thao tác</h3>
                    <div class="recent-list">
                        <?php if ($recent_quizzes && $recent_quizzes->num_rows > 0): ?>
                            <?php while($rq = $recent_quizzes->fetch_assoc()): 
                                $edit_link = ($rq['status'] == 'draft') ? "create_quiz/step2_add_questions.php?quiz_id=".$rq['id'] : "edit_quiz.php?id=".$rq['id'];
                                $badge_color = ($rq['status'] == 'draft') ? '#f6ad55' : '#38a169';
                                $badge_text = ($rq['status'] == 'draft') ? 'Đang nháp' : 'Hoàn thành';
                            ?>
                                <a href="<?php echo $edit_link; ?>" class="recent-item">
                                    <div>
                                        <h4><?php echo htmlspecialchars($rq['title']); ?></h4>
                                        <div class="recent-meta">
                                            <span><i class="far fa-clock"></i> <?php echo date('d/m/Y', strtotime($rq['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    <span style="font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 6px; background: rgba(0,0,0,0.05); color: <?php echo $badge_color; ?>; border: 1px solid <?php echo $badge_color; ?>;">
                                        <?php echo $badge_text; ?>
                                    </span>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: var(--text-muted); font-size: 0.9rem; text-align: center; padding: 20px 0;">Chưa có dữ liệu. Hãy tạo đề thi đầu tiên của bạn!</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="section-box">
                    <h3 class="section-title"><i class="fas fa-bolt" style="color: #d97706;"></i> Mẫu tạo nhanh</h3>
                    <div class="template-grid">
                        <a href="create_quiz/step1_create_quiz.php?template=ai_pdf" class="template-btn tpl-ai">
                            <i class="fas fa-file-pdf"></i>
                            Tách đề từ File PDF
                        </a>
                        <a href="create_quiz/step1_create_quiz.php?template=mini_test" class="template-btn tpl-15m">
                            <i class="fas fa-stopwatch"></i>
                            Đề kiểm tra 15 phút
                        </a>
                        <a href="create_quiz/step1_create_quiz.php?template=essay" class="template-btn tpl-essay">
                            <i class="fas fa-pen-nib"></i>
                            Bài tập Tự luận
                        </a>
                    </div>
                </div>

            </div>
            
        </div>
    </div>
</main>