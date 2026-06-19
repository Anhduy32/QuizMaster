<?php
session_start();
include '../../../config/database.php';

if (!isset($_SESSION['username'])) {
    header('Location: login/login.php');
    exit();
}

$ten_dang_nhap = $_SESSION['username'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tieu_de = trim($_POST['title']);
    $mon_hoc = trim($_POST['subject']);
    $thoi_gian = (int)$_POST['time_limit'];
    $mo_ta = trim($_POST['description']);
    
    if (empty($tieu_de) || empty($mon_hoc) || $thoi_gian < 1) {
        $error = "Vui lòng điền đầy đủ thông tin bắt buộc!";
    } else {
        $stmt = $conn->prepare("INSERT INTO quizzes (creator_username, title, subject, time_limit, description, status) VALUES (?, ?, ?, ?, ?, 'draft')");
        $stmt->bind_param('sssis', $ten_dang_nhap, $tieu_de, $mon_hoc, $thoi_gian, $mo_ta);
        
        if ($stmt->execute()) {
            $quiz_id = $conn->insert_id;
            header("Location: step2_add_questions.php?quiz_id=$quiz_id");
            exit();
        } else {
            $error = "Lỗi hệ thống: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bước 1: Tạo đề thi - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/create_quiz.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="quiz-creator">
        <div class="creator-header">
            <div class="container header-wrapper">
                <a href="../home.php" class="back-link"><i class="fas fa-arrow-left"></i> Trở về</a>
                <div class="steps">
                    <div class="step active"><div class="step-number">1</div><div class="step-label">Thông tin</div></div>
                    <div class="step-line"></div>
                    <div class="step"><div class="step-number">2</div><div class="step-label">Câu hỏi</div></div>
                    <div class="step-line"></div>
                    <div class="step"><div class="step-number">3</div><div class="step-label">Hoàn tất</div></div>
                </div>
            </div>
        </div>

        <div class="creator-main">
            <div class="container">
                <div class="creator-card">
                    <div class="card-header-center">
                        <div class="card-icon"><i class="fas fa-file-signature"></i></div>
                        <h1 class="card-title">Tạo Đề Thi Mới</h1>
                        <p class="card-desc">Cấu hình thông tin cơ bản để bắt đầu soạn thảo</p>
                    </div>

                    <?php if ($error): ?>
                        <div style="background: #fff5f5; color: #c53030; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="quiz-form">
                        <div class="form-group">
                            <label class="form-label">Tên đề thi <span class="required">*</span></label>
                            <input type="text" name="title" class="form-input" placeholder="Nhập tiêu đề rõ ràng, ví dụ: Đề kiểm tra 15p Toán..." required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Môn học <span class="required">*</span></label>
                                <select name="subject" class="form-select" required>
                                    <option value="" disabled selected>-- Lựa chọn --</option>
                                    <option value="Toán Học">Toán Học</option>
                                    <option value="Vật Lý">Vật Lý</option>
                                    <option value="Hóa Học">Hóa Học</option>
                                    <option value="Tiếng Anh">Tiếng Anh</option>
                                    <option value="Khác">Môn khác</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Thời gian <span class="required">*</span></label>
                                <div class="time-input-wrapper">
                                    <input type="number" name="time_limit" class="form-input" placeholder="45" min="1" required>
                                    <span class="time-unit">phút</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mô tả (Tùy chọn)</label>
                            <textarea name="description" class="form-textarea" placeholder="Ghi chú thêm về nội dung hoặc đối tượng làm bài..."></textarea>
                        </div>

                        <button type="submit" class="btn-primary">Tiếp tục: Soạn câu hỏi <i class="fas fa-arrow-right"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>