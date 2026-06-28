<?php
session_start();
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
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
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
</body>
</html>