<?php
session_start();
<<<<<<< HEAD
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
=======
include '../../config/database.php';
if (!isset($_SESSION['username'])) { header("Location: ../login/login.php"); exit(); }
$question_id = isset($_GET['id']) ? (int)$_GET['id'] : 0; $thong_bao = '';
$query = "SELECT * FROM questions WHERE id = ?";
$stmt = $conn->prepare($query); $stmt->bind_param("i", $question_id); $stmt->execute();
$q = $stmt->get_result()->fetch_assoc();
if (!$q) die("Câu hỏi không tồn tại.");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = trim($_POST['content']); $opt_a = trim($_POST['opt_a']); $opt_b = trim($_POST['opt_b']); $opt_c = trim($_POST['opt_c']); $opt_d = trim($_POST['opt_d']); $correct_opt = $_POST['correct_opt'];
    $update = "UPDATE questions SET content=?, opt_a=?, opt_b=?, opt_c=?, opt_d=?, correct_opt=? WHERE id=?";
    $stmt_up = $conn->prepare($update);
    $stmt_up->bind_param("ssssssi", $content, $opt_a, $opt_b, $opt_c, $opt_d, $correct_opt, $question_id);
    if ($stmt_up->execute()) { $thong_bao = "Cập nhật câu hỏi thành công!"; $q['content'] = $content; $q['opt_a'] = $opt_a; $q['opt_b'] = $opt_b; $q['opt_c'] = $opt_c; $q['opt_d'] = $opt_d; $q['correct_opt'] = $correct_opt; }
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
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
=======
    <title>Chỉnh sửa câu hỏi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
</head>
<body>
    <div class="container" style="max-width: 700px;">
        <div class="card">
            <h2 class="page-title mb-4"><i class="fas fa-pen-nib"></i> Chỉnh sửa câu hỏi</h2>
            
            <?php if($thong_bao): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 16px; border-radius: var(--radius-md); font-weight: 600; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <?php echo $thong_bao; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nội dung câu hỏi</label>
                    <textarea name="content" class="form-control" rows="4" required><?php echo htmlspecialchars($q['content']); ?></textarea>
                </div>
                
                <label class="form-label mb-4">Các đáp án (Đánh dấu vào ô tròn để chọn đáp án đúng)</label>
                
                <?php $opts = ['A' => 'opt_a', 'B' => 'opt_b', 'C' => 'opt_c', 'D' => 'opt_d']; foreach ($opts as $key => $col): ?>
                <div class="form-group" style="display: flex; align-items: center; gap: 12px;">
                    <input type="radio" name="correct_opt" value="<?php echo $key; ?>" <?php echo ($q['correct_opt'] == $key) ? 'checked' : ''; ?> required style="transform: scale(1.4); cursor: pointer; accent-color: var(--primary);">
                    <strong style="font-size: 1.1rem; color: var(--text-main);"><?php echo $key; ?>.</strong>
                    <input type="text" name="<?php echo $col; ?>" class="form-control" value="<?php echo htmlspecialchars($q[$col]); ?>">
                </div>
                <?php endforeach; ?>

                <div class="mt-4" style="display: flex; gap: 15px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;"><i class="fas fa-save"></i> Cập nhật thay đổi</button>
                    <a href="javascript:history.back()" class="btn btn-outline" style="flex: 1;"><i class="fas fa-times"></i> Hủy / Quay lại</a>
                </div>
            </form>
        </div>
    </div>
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
</body>
</html>