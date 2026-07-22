<?php
session_start();
include '../../config/database.php'; 

if (!isset($_SESSION['username'])) { 
    header("Location: ../auth/login.php"); 
    exit(); 
}

$username = $_SESSION['username'];

// Kiểm tra session full_name
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

// Xử lý xóa đề thi
if (isset($_GET['delete_quiz']) && is_numeric($_GET['delete_quiz'])) {
    $delete_id = (int)$_GET['delete_quiz'];
    
    // Kiểm tra quyền sở hữu
    $check_stmt = $conn->prepare("SELECT creator_username FROM quizzes WHERE id = ?");
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $quiz_owner = $check_result->fetch_assoc();
    
    if ($quiz_owner && $quiz_owner['creator_username'] === $username) {
        // Xóa các câu hỏi liên quan trước
        $conn->query("DELETE FROM questions WHERE quiz_id = $delete_id");
        // Xóa đề thi
        $conn->query("DELETE FROM quizzes WHERE id = $delete_id");
        
        // Chuyển hướng để tránh refresh lại form
        header("Location: add_question_hub.php?deleted=1");
        exit();
    }
}

// Thống kê
$stmt_stats = $conn->prepare("
    SELECT 
        COUNT(id) as total_quizzes,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
        SUM(num_questions) as total_questions
    FROM quizzes WHERE creator_username = ?
");
$stmt_stats->bind_param("s", $username);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();

$total_quizzes = $stats['total_quizzes'] ?? 0;
$draft_count = $stats['draft_count'] ?? 0;
$completed_count = $stats['completed_count'] ?? 0;
$total_questions = $stats['total_questions'] ?? 0;

// Lấy danh sách đề thi gần đây (thêm num_questions)
$stmt_recent = $conn->prepare("
    (SELECT id, title, status, created_at, num_questions FROM quizzes 
     WHERE creator_username = ? AND status = 'draft' 
     ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT id, title, status, created_at, num_questions FROM quizzes 
     WHERE creator_username = ? AND status = 'completed' 
     ORDER BY created_at DESC LIMIT 3)
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
            
            <!-- BREADCRUMB -->
            <nav class="breadcrumb">
                <a href="../../home.php"><i class="fas fa-home"></i> Bảng điều khiển</a> 
                <span style="margin: 0 10px;">/</span> 
                <strong>Không gian làm việc</strong>
            </nav>

            <!-- BANNER -->
            <div class="hub-banner">
                <div class="hub-banner-content">
                    <h1>Chào <?php echo htmlspecialchars($ten_goi); ?>, sẵn sàng sáng tạo chưa?</h1>
                    <p>Khởi tạo những bài giảng đột phá và quản lý kho dữ liệu của bạn.</p>
                    
                    <div class="creator-mini-stats">
                        <span>Đã tạo: <strong><?php echo $total_quizzes; ?></strong> Đề thi</span>
                        <span>Ngân hàng: <strong><?php echo (int)$total_questions; ?></strong> Câu hỏi</span>
                        <?php if($completed_count > 0): ?>
                            <span style="color: #48bb78;">✅ <strong><?php echo $completed_count; ?></strong> Đã xuất bản</span>
                        <?php endif; ?>
                        <?php if($draft_count > 0): ?>
                            <span style="color: #f6ad55;">📝 <strong><?php echo $draft_count; ?></strong> Bản nháp</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 3 Công cụ cốt lõi -->
            <div class="workspace-grid">
                <a href="create_quiz/step1_create_quiz.php" class="workspace-card card-primary">
                    <div class="ws-icon icon-blue"><i class="fas fa-magic"></i></div>
                    <h3 class="ws-title">Tạo Đề Thi Mới</h3>
                    <p class="ws-desc">Khởi tạo bộ đề bằng thủ công hoặc tải lên file PDF để nhúng trực tiếp.</p>
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
                
                <!-- RECENT QUIZZES -->
                <div class="section-box">
                    <h3 class="section-title"><i class="fas fa-history" style="color: var(--primary-teal);"></i> Đề thi vừa thao tác</h3>
                    <div class="recent-list">
                        <?php if ($recent_quizzes && $recent_quizzes->num_rows > 0): ?>
                            <?php while($rq = $recent_quizzes->fetch_assoc()): 
                                $is_draft = ($rq['status'] == 'draft');
                                $edit_link = $is_draft 
                                    ? "create_quiz/step2_add_questions.php?quiz_id=".$rq['id'] 
                                    : "edit_quiz.php?id=".$rq['id'];
                                $status_class = $is_draft ? 'status-draft' : 'status-completed';
                                $status_text = $is_draft ? '📝 Nháp' : '✅ Hoàn thành';
                            ?>
                                <div class="recent-item">
                                    <div class="recent-info">
                                        <h4><?php echo htmlspecialchars($rq['title']); ?></h4>
                                        <div class="recent-meta">
                                            <span><i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($rq['created_at'])); ?></span>
                                            <span><i class="far fa-question-circle"></i> <?php echo (int)$rq['num_questions']; ?> câu</span>
                                        </div>
                                    </div>
                                    
                                    <div class="recent-item-actions">
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                        
                                        <?php if ($is_draft): ?>
                                            <!-- Nút tiếp tục chỉnh sửa -->
                                            <a href="<?php echo $edit_link; ?>" class="btn-action btn-continue">
                                                <i class="fas fa-pen"></i> Tiếp tục
                                            </a>
                                            <!-- Nút xóa bản nháp -->
                                            <a href="add_question_hub.php?delete_quiz=<?php echo $rq['id']; ?>" 
                                               class="btn-action btn-delete" 
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa bản nháp \'<?php echo addslashes($rq['title']); ?>\' này? Hành động này không thể hoàn tác!');">
                                                <i class="fas fa-trash"></i> Xóa
                                            </a>
                                        <?php else: ?>
                                            <!-- Nút xem chi tiết -->
                                            <a href="<?php echo $edit_link; ?>" class="btn-action btn-edit">
                                                <i class="fas fa-eye"></i> Xem
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: var(--text-muted); font-size: 0.9rem; text-align: center; padding: 20px 0;">
                                <i class="fas fa-box-open" style="display: block; font-size: 2rem; margin-bottom: 10px; color: #cbd5e0;"></i>
                                Chưa có dữ liệu. Hãy tạo đề thi đầu tiên của bạn!
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TEMPLATES -->
                <div class="section-box">
                    <h3 class="section-title"><i class="fas fa-bolt" style="color: #d97706;"></i> Mẫu tạo nhanh</h3>
                    <div class="template-grid">
                        <a href="create_quiz/step1_create_quiz.php?template=ai_pdf" class="template-btn tpl-ai">
                            <i class="fas fa-file-pdf"></i>
                            Nhúng đề từ File PDF
                        </a>
                        <a href="create_quiz/step1_create_quiz.php?template=mini_test" class="template-btn tpl-15m">
                            <i class="fas fa-stopwatch"></i>
                            Đề kiểm tra 15 phút
                        </a>
                        <a href="create_quiz/step1_create_quiz.php?template=essay" class="template-btn tpl-essay">
                            <i class="fas fa-pen-nib"></i>
                            Bài tập Tự luận
                        </a>
                        <a href="create_quiz/step1_create_quiz.php?template=midterm" class="template-btn" style="border-color: rgba(15, 92, 107, 0.1);">
                            <i class="fas fa-graduation-cap" style="color: var(--primary-teal);"></i>
                            Đề thi giữa kỳ
                        </a>
                    </div>
                </div>

            </div>
            
        </div>
    </div>
<<<<<<< HEAD
</main>
=======
</main>
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
