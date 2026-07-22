<?php
session_start();
$root_path = dirname(__DIR__, 2);
include $root_path . '/config/database.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit();
}

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$username = $_SESSION['username'];

// Lấy thông tin đề thi cần sửa
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ? AND creator_username = ?");
$stmt->bind_param('is', $quiz_id, $username);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    $_SESSION['error_message'] = 'Đề thi không tồn tại hoặc bạn không có quyền chỉnh sửa!';
    header('Location: my_library.php');
    exit();
}

$quiz_type = $quiz['quiz_type'] ?? 'multiple_choice';

// XỬ LÝ CẬP NHẬT FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($quiz_type === 'file_based' && isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        // Nâng cấp: Thay thế file PDF mới nếu người dùng chọn upload file mới
        $ext = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $new_filename = 'pdf_' . time() . '_' . uniqid() . '.pdf';
            $target_dir = $root_path . '/uploads/pdfs/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $target_dir . $new_filename)) {
                // Xóa file PDF cũ
                if (!empty($quiz['file_path'])) {
                    @unlink($root_path . '/' . ltrim($quiz['file_path'], '/'));
                }
                $new_file_path = 'uploads/pdfs/' . $new_filename;

                $update_stmt = $conn->prepare("UPDATE quizzes SET title = ?, subject = ?, description = ?, file_path = ? WHERE id = ?");
                $update_stmt->bind_param('ssssi', $title, $subject, $description, $new_file_path, $quiz_id);
                $update_stmt->execute();
            }
        }
    } else {
        // Cập nhật thông tin cơ bản
        $update_stmt = $conn->prepare("UPDATE quizzes SET title = ?, subject = ?, description = ? WHERE id = ?");
        $update_stmt->bind_param('sssi', $title, $subject, $description, $quiz_id);
        $update_stmt->execute();
    }

    $_SESSION['success_message'] = 'Cập nhật đề thi thành công!';
    header('Location: my_library.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa đề thi - QuizMaster</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<div style="max-width: 600px; margin: 40px auto; background:#fff; padding:30px; border-radius:12px;">
    <h2>Chỉnh sửa đề thi</h2>
    <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom:15px;">
            <label>Tên đề thi:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($quiz['title'] ?? ''); ?>" required style="width:100%; padding:8px; margin-top:5px;">
        </div>

        <div style="margin-bottom:15px;">
            <label>Môn học:</label>
            <input type="text" name="subject" value="<?php echo htmlspecialchars($quiz['subject'] ?? ''); ?>" required style="width:100%; padding:8px; margin-top:5px;">
        </div>

        <div style="margin-bottom:15px;">
            <label>Mô tả:</label>
            <textarea name="description" rows="3" style="width:100%; padding:8px; margin-top:5px;"><?php echo htmlspecialchars($quiz['description'] ?? ''); ?></textarea>
        </div>

        <?php if ($quiz_type === 'file_based'): ?>
            <div style="margin-bottom:15px; background:#f8fafc; padding:15px; border-radius:8px;">
                <label>Tải đè file PDF mới (Bỏ qua nếu giữ nguyên):</label>
                <input type="file" name="pdf_file" accept=".pdf" style="margin-top:8px;">
            </div>
        <?php endif; ?>

        <button type="submit" style="background:#0f5c6b; color:#fff; padding:10px 20px; border:none; border-radius:6px; cursor:pointer;">Lưu thay đổi</button>
        <a href="my_library.php" style="margin-left:10px; color:#666; text-decoration:none;">Hủy</a>
    </form>
</div>
</body>
</html>