<?php
session_start();

// Bật hiển thị lỗi tạm thời để dễ phát hiện nếu có lỗi đường dẫn
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit();
}

<<<<<<< HEAD
// Lấy ID đề thi và tham số lỗi
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = isset($_GET['error']) ? $_GET['error'] : '';
=======
// Lấy ID đề thi
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8

if ($quiz_id <= 0) {
    header("Location: sum_question.php");
    exit();
}

// 1. TĂNG LƯỢT XEM (VIEWS) - AN TOÀN BẰNG TRY-CATCH
try {
    $update_views = "UPDATE quizzes SET views = views + 1 WHERE id = ?";
    $stmt_views = $conn->prepare($update_views);
    if ($stmt_views) {
        $stmt_views->bind_param("i", $quiz_id);
        $stmt_views->execute();
    }
} catch (Exception $e) {
    // Nếu bảng quizzes chưa có cột views, bỏ qua lỗi để không làm sập trang
}

// 2. LẤY THÔNG TIN CHI TIẾT ĐỀ THI VÀ TÁC GIẢ
$query = "SELECT q.*, u.full_name, u.picture 
          FROM quizzes q 
          JOIN users u ON q.creator_username = u.username 
          WHERE q.id = ?";
$stmt = $conn->prepare($query);

// Kiểm tra nếu câu lệnh SQL bị lỗi (Ví dụ: thiếu cột picture trong users)
if (!$stmt) {
    die("<div style='padding:50px; text-align:center;'><h3>Lỗi truy vấn Database:</h3><p>" . htmlspecialchars($conn->error) . "</p></div>");
}

$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$quiz = $result->fetch_assoc();

if (!$quiz) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>Đề thi không tồn tại hoặc đã bị xóa! <a href='sum_question.php'>Quay lại</a></div>");
}

<<<<<<< HEAD
// Lấy thông tin quiz_type và file_path
$quiz_type = $quiz['quiz_type'] ?? 'multiple_choice';
$file_path = $quiz['file_path'] ?? '';
$has_answers = isset($quiz['has_answers']) ? (int)$quiz['has_answers'] : 1;
$num_questions = (int)$quiz['num_questions'];

// Kiểm tra file PDF tồn tại
$full_path = '';
$file_exists = false;
$file_name = '';
$file_size = 0;

if (!empty($file_path)) {
    $full_path = '../../' . $file_path;
    if (file_exists($full_path)) {
        $file_exists = true;
        $file_name = basename($file_path);
        $file_size = filesize($full_path);
    }
}

// Hàm hỗ trợ UI môn học
function getSubjectStyle($subject) {
    $sub = mb_strtolower($subject ?? '', 'UTF-8');
=======
// Hàm hỗ trợ UI môn học
function getSubjectStyle($subject) {
    $sub = mb_strtolower($subject, 'UTF-8');
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
    if (strpos($sub, 'toán') !== false) return ['icon' => 'fa-square-root-alt', 'color' => '#3182ce', 'bg' => '#ebf8ff'];
    if (strpos($sub, 'lý') !== false) return ['icon' => 'fa-atom', 'color' => '#dd6b20', 'bg' => '#fffaf0'];
    if (strpos($sub, 'hóa') !== false) return ['icon' => 'fa-flask', 'color' => '#38a169', 'bg' => '#f0fff4'];
    if (strpos($sub, 'anh') !== false) return ['icon' => 'fa-language', 'color' => '#e53e3e', 'bg' => '#fff5f5'];
    if (strpos($sub, 'tin') !== false) return ['icon' => 'fa-laptop-code', 'color' => '#805ad5', 'bg' => '#faf5ff'];
<<<<<<< HEAD
    if (strpos($sub, 'văn') !== false) return ['icon' => 'fa-book', 'color' => '#d69e2e', 'bg' => '#fffff0'];
    if (strpos($sub, 'sử') !== false) return ['icon' => 'fa-landmark', 'color' => '#dd6b20', 'bg' => '#fffaf0'];
    if (strpos($sub, 'địa') !== false) return ['icon' => 'fa-globe-asia', 'color' => '#38a169', 'bg' => '#f0fff4'];
    if (strpos($sub, 'gdcd') !== false) return ['icon' => 'fa-balance-scale', 'color' => '#805ad5', 'bg' => '#faf5ff'];
    if (strpos($sub, 'sinh') !== false) return ['icon' => 'fa-dna', 'color' => '#38a169', 'bg' => '#f0fff4'];
=======
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
    return ['icon' => 'fa-book-open', 'color' => '#718096', 'bg' => '#f1f5f9'];
}

$style = getSubjectStyle($quiz['subject']);

// ================= PAGE CONFIG =================
<<<<<<< HEAD
$page_title = htmlspecialchars($quiz['title'] ?? 'Chi tiết đề thi') . ' - QuizMaster';
$page_css = 'quiz_detail.css'; 

=======
$page_title = htmlspecialchars($quiz['title']) . ' - QuizMaster';
// SỬA LỖI: Dùng sum_question.css để kế thừa toàn bộ biến màu, tránh lỗi tàng hình CSS
$page_css = 'sum_question.css'; 
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8

require_once '../../includes/layouts/header.php';
?>

<<<<<<< HEAD
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Gọi file CSS thông qua biến $page_css -->
    <link rel="stylesheet" href="<?php echo $page_css; ?>">
</head>
<body>
=======
<!-- Định nghĩa lại các biến màu dự phòng trường hợp CSS chưa load kịp -->
<style>
    :root {
        --text-main: #1a202c;
        --text-muted: #718096;
        --border-light: #e2e8f0;
        --primary-teal: #0f5c6b;
        --primary-hover: #0a4a56;
    }
    .detail-container { max-width: 900px; margin: 0 auto; padding: 40px 20px; font-family: 'Inter', sans-serif;}
    .breadcrumb { margin-bottom: 25px; font-size: 0.95rem; font-weight: 600; color: var(--text-muted); }
    .breadcrumb a { color: var(--primary-teal); text-decoration: none; transition: 0.2s; }
    .breadcrumb a:hover { text-decoration: underline; }
    
    .quiz-detail-card { background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 15px 35px rgba(15, 92, 107, 0.08); border: 1px solid var(--border-light); }
    
    /* Header Card */
    .qd-header { padding: 40px; border-bottom: 1px solid var(--border-light); position: relative; }
    .qd-subject-badge { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 50px; font-weight: 700; font-size: 0.9rem; margin-bottom: 20px; }
    .qd-title { font-size: 2.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 20px; line-height: 1.4; }
    
    /* Thông tin tác giả */
    .qd-author { display: flex; align-items: center; gap: 15px; }
    .qd-author-avatar { width: 50px; height: 50px; border-radius: 50%; background: var(--primary-teal); color: white; display: flex; justify-content: center; align-items: center; font-weight: 700; font-size: 1.2rem; }
    .qd-author-info h4 { margin: 0 0 5px 0; font-size: 1.05rem; color: var(--text-main); }
    .qd-author-info p { margin: 0; font-size: 0.85rem; color: var(--text-muted); }
    
    /* Body Card (Thống kê) */
    .qd-body { padding: 40px; background: #f8fafc; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .stat-box { background: white; padding: 20px; border-radius: 16px; border: 1px solid var(--border-light); display: flex; align-items: center; gap: 15px; }
    .stat-icon { width: 45px; height: 45px; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 1.2rem; background: #edf2f7; color: #4a5568; }
    .stat-info h5 { margin: 0 0 5px 0; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; }
    .stat-info span { font-size: 1.1rem; font-weight: 700; color: var(--text-main); }
    
    /* Nút bắt đầu */
    .btn-start-massive { display: block; width: 100%; padding: 20px; background: var(--primary-teal); color: white; text-align: center; border-radius: 16px; font-size: 1.2rem; font-weight: 800; text-decoration: none; transition: 0.3s; box-shadow: 0 10px 20px rgba(15, 92, 107, 0.2); }
    .btn-start-massive:hover { background: var(--primary-hover); transform: translateY(-3px); box-shadow: 0 15px 25px rgba(15, 92, 107, 0.3); }
    .btn-start-massive i { margin-left: 8px; transition: 0.3s; }
    .btn-start-massive:hover i { transform: translateX(5px); }
</style>
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8

<main class="main-wrapper" style="margin-left: 0; padding: 0;"> 
    <div class="detail-container">
        
        <nav class="breadcrumb">
            <a href="sum_question.php"><i class="fas fa-arrow-left"></i> Khám phá cộng đồng</a> 
            <span style="margin: 0 10px;">/</span> 
            <strong>Chi tiết đề thi</strong>
        </nav>

        <div class="quiz-detail-card">
            <div class="qd-header">
                <div class="qd-subject-badge" style="background: <?php echo $style['bg']; ?>; color: <?php echo $style['color']; ?>;">
<<<<<<< HEAD
                    <i class="fas <?php echo $style['icon']; ?>"></i> <?php echo htmlspecialchars($quiz['subject'] ?? 'Khác'); ?>
                </div>
                
                <h1 class="qd-title">
                    <?php echo htmlspecialchars($quiz['title'] ?? 'Chưa có tiêu đề'); ?>
                    
                    <!-- Hiển thị nhãn loại đề thi -->
                    <?php if ($quiz_type === 'file_based'): ?>
                        <span class="badge badge-pdf">
                            <i class="fas fa-file-pdf"></i> File PDF
                        </span>
                    <?php else: ?>
                        <span class="badge badge-mcq">
                            <i class="fas fa-check-circle"></i> Trắc nghiệm
                        </span>
                    <?php endif; ?>
                    
                    <!-- Trạng thái đề thi -->
                    <span class="badge <?php echo $quiz['status'] === 'completed' ? 'badge-completed' : 'badge-draft'; ?>">
                        <?php echo $quiz['status'] === 'completed' ? 'Đã xuất bản' : 'Bản nháp'; ?>
                    </span>
                </h1>
                
                <div class="qd-author">
                    <div class="qd-author-avatar">
                        <?php echo strtoupper(substr($quiz['full_name'] ?? 'A', 0, 1)); ?>
                    </div>
                    <div class="qd-author-info">
                        <h4><?php echo htmlspecialchars($quiz['full_name'] ?? 'Người dùng Ẩn danh'); ?></h4>
=======
                    <i class="fas <?php echo $style['icon']; ?>"></i> <?php echo htmlspecialchars($quiz['subject']); ?>
                </div>
                
                <h1 class="qd-title"><?php echo htmlspecialchars($quiz['title']); ?></h1>
                
                <div class="qd-author">
                    <div class="qd-author-avatar">
                        <?php echo strtoupper(substr($quiz['full_name'], 0, 1)); ?>
                    </div>
                    <div class="qd-author-info">
                        <h4><?php echo htmlspecialchars($quiz['full_name']); ?></h4>
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
                        <p>Đăng ngày: <?php echo date('d/m/Y', strtotime($quiz['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <div class="qd-body">
<<<<<<< HEAD
                
                <!-- ==========================================
                     HIỂN THỊ CẢNH BÁO LỖI COMPLEX MATH
                     ========================================== -->
                <?php if ($error === 'complex_math'): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="alert-content">
                        <strong>Phát hiện công thức Toán học phức tạp!</strong>
                        <p>Hệ thống không thể tự động bóc tách câu hỏi từ file PDF do chứa nhiều ký tự công thức (MathType/Equation).</p>
                        <p style="margin-top: 6px;"><strong>💡 Giải pháp:</strong> Bạn có thể tải file PDF về máy và sử dụng làm tài liệu tham khảo. Để tạo đề thi trắc nghiệm, vui lòng sử dụng phương thức <strong>"Nhập thủ công"</strong>.</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- ==========================================
                     HIỂN THỊ THÔNG TIN FILE PDF
                     ========================================== -->
                <?php if ($quiz_type === 'file_based'): ?>
                    <?php if ($file_exists): ?>
                    <div class="file-info-box">
                        <i class="fas fa-file-pdf"></i>
                        <div class="file-details">
                            <strong><?php echo htmlspecialchars($file_name); ?></strong>
                            <span><?php echo number_format($file_size / 1024, 1); ?> KB</span>
                        </div>
                        <div class="file-actions">
                            <a href="<?php echo htmlspecialchars($full_path); ?>" download class="btn btn-danger">
                                <i class="fas fa-download"></i> Tải xuống
                            </a>
                            <a href="<?php echo htmlspecialchars($full_path); ?>" target="_blank" class="btn btn-outline">
                                <i class="fas fa-external-link-alt"></i> Xem trực tiếp
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div class="alert-content">
                            <strong>Không tìm thấy file PDF!</strong>
                            <p>File PDF đính kèm cho đề thi này không tồn tại trên máy chủ. Vui lòng liên hệ với người tạo đề thi để được hỗ trợ.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- ==========================================
                     THỐNG KÊ
                     ========================================== -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-icon" style="color: #3182ce; background: #ebf8ff;">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="stat-info">
                            <h5>Số câu hỏi</h5>
                            <span>
                                <?php if ($quiz_type === 'file_based'): ?>
                                    <i class="fas fa-file-pdf" style="color: #dc2626;"></i> Đề đính kèm
                                <?php else: ?>
                                    <?php echo $num_questions; ?> câu
                                <?php endif; ?>
                            </span>
=======
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-icon" style="color: #3182ce; background: #ebf8ff;"><i class="fas fa-layer-group"></i></div>
                        <div class="stat-info">
                            <h5>Số câu hỏi</h5>
                            <span><?php echo (int)$quiz['num_questions']; ?> câu</span>
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
                        </div>
                    </div>
                    
                    <div class="stat-box">
<<<<<<< HEAD
                        <div class="stat-icon" style="color: #dd6b20; background: #fffaf0;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-info">
                            <h5>Đối tượng</h5>
                            <span><?php echo htmlspecialchars($quiz['target_audience'] ?? 'Khác'); ?></span>
                        </div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-icon" style="color: <?php echo $has_answers ? '#059669' : '#dc2626'; ?>; background: <?php echo $has_answers ? '#d1fae5' : '#fee2e2'; ?>;">
                            <i class="fas <?php echo $has_answers ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        </div>
                        <div class="stat-info">
                            <h5>Đáp án</h5>
                            <span>
                                <?php if ($quiz_type === 'file_based'): ?>
                                    <span style="font-size: 0.8rem; color: #64748b;">Không áp dụng</span>
                                <?php else: ?>
                                    <?php echo $has_answers ? 'Có đáp án' : 'Không có'; ?>
                                <?php endif; ?>
=======
                        <div class="stat-icon" style="color: #dd6b20; background: #fffaf0;"><i class="fas fa-user-graduate"></i></div>
                        <div class="stat-info">
                            <h5>Đối tượng</h5>
                            <span>
                                <?php 
                                    if (isset($quiz['target_audience']) && $quiz['target_audience'] == 'sinh_vien') echo "Sinh viên";
                                    else echo "Học sinh";
                                ?>
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
                            </span>
                        </div>
                    </div>

                    <div class="stat-box">
<<<<<<< HEAD
                        <div class="stat-icon" style="color: #38a169; background: #f0fff4;">
                            <i class="fas fa-fire"></i>
                        </div>
=======
                        <div class="stat-icon" style="color: #38a169; background: #f0fff4;"><i class="fas fa-fire"></i></div>
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
                        <div class="stat-info">
                            <h5>Lượt quan tâm</h5>
                            <span><?php echo isset($quiz['views']) ? (int)$quiz['views'] : 0; ?> lượt</span>
                        </div>
                    </div>
                </div>

<<<<<<< HEAD
                <!-- ==========================================
                     NÚT HÀNH ĐỘNG CHÍNH
                     ========================================== -->
                <?php if ($quiz_type === 'file_based'): ?>
                    <!-- Đề thi dạng file PDF -->
                    <a href="take_quiz.php?id=<?php echo $quiz_id; ?>" class="btn-start-massive">
                        <i class="fas fa-file-pdf"></i> Xem đề thi <i class="fas fa-arrow-right"></i>
                    </a>
                    <div style="text-align: center; margin-top: 12px; font-size: 0.85rem; color: #64748b;">
                        <i class="fas fa-info-circle"></i> Đề thi dạng file PDF, bạn có thể tải về và làm bài ngoại tuyến
                    </div>
                <?php elseif ($num_questions > 0): ?>
                    <!-- Đề trắc nghiệm đã có câu hỏi -->
                    <a href="take_quiz.php?id=<?php echo $quiz_id; ?>" class="btn-start-massive">
                        Bắt đầu làm bài ngay <i class="fas fa-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <!-- Đề trắc nghiệm chưa có câu hỏi -->
                    <a href="step2_add_questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn-start-massive btn-start-massive-warning">
                        <i class="fas fa-plus-circle"></i> Thêm câu hỏi để xuất bản <i class="fas fa-arrow-right"></i>
                    </a>
                    <div style="text-align: center; margin-top: 12px; font-size: 0.85rem; color: #d97706;">
                        <i class="fas fa-exclamation-triangle"></i> Đề thi chưa có câu hỏi nào. Vui lòng thêm câu hỏi trước khi xuất bản.
                    </div>
                <?php endif; ?>
                
                <!-- ==========================================
                     NÚT HÀNH ĐỘNG PHỤ
                     ========================================== -->
                <div class="action-buttons">
                    <?php if ($quiz_type === 'file_based' && $file_exists): ?>
                        <a href="<?php echo htmlspecialchars($full_path); ?>" download class="btn btn-danger" style="flex: 1; justify-content: center;">
                            <i class="fas fa-download"></i> Tải file PDF
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($quiz['creator_username'] === $_SESSION['username']): ?>
                        <a href="edit_quiz.php?id=<?php echo $quiz_id; ?>" class="btn btn-outline" style="flex: 1; justify-content: center;">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                    <?php endif; ?>
                    
                    <a href="sum_question.php" class="btn btn-outline" style="flex: 1; justify-content: center;">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
=======
                <!-- Nút chuyển hướng sang take_quiz.php -->
                <a href="take_quiz.php?id=<?php echo $quiz_id; ?>" class="btn-start-massive">
                    Bắt đầu làm bài ngay <i class="fas fa-arrow-right"></i>
                </a>
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
            </div>
        </div>

    </div>
</main>
<<<<<<< HEAD
=======

<?php require_once '../../includes/layouts/footer.php'; ?>
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
