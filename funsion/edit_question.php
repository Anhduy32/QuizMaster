<?php
session_start();
include '../config/database.php';
include '../require_profile_update.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Câu hỏi không tồn tại.");
}

$question_id = $_GET['id'];
$sql = "SELECT * FROM create_questions WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $question_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Câu hỏi không tồn tại trong cơ sở dữ liệu.");
}

$question = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = $_POST['question_text'];
    $answer_a = $_POST['answer_a'];
    $answer_b = $_POST['answer_b'];
    $answer_c = $_POST['answer_c'];
    $answer_d = $_POST['answer_d'];
    $correct_answer = $_POST['correct_answer'];
    $difficulty = $_POST['difficulty'];
    $teacher_name = $_POST['teacher_name'];
    $content = $_POST['content'];

    // --- xử lý ảnh upload ---
    $image_name = $question['image']; // giữ ảnh cũ
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    }

    $sql_update = "UPDATE create_questions 
                   SET question_text = ?, answer_a = ?, answer_b = ?, answer_c = ?, answer_d = ?, 
                       correct_answer = ?, difficulty = ?, teacher_name = ?, content = ?, image = ? 
                   WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param(
        'ssssssssssi',
        $question_text,
        $answer_a,
        $answer_b,
        $answer_c,
        $answer_d,
        $correct_answer,
        $difficulty,
        $teacher_name,
        $content,
        $image_name,
        $question_id
    );

    if ($stmt_update->execute()) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href='../funsion/Sum_question.php';</script>";
        exit();
    } else {
        echo "Lỗi cập nhật: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh Sửa Câu Hỏi</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <h1>Chỉnh Sửa Câu Hỏi</h1>
        <form method="POST" enctype="multipart/form-data">
            <label for="question_text">Câu hỏi:</label>
            <textarea name="question_text" id="question_text" required><?= htmlspecialchars($question['question_text']) ?></textarea>

            <label for="answer_a">Đáp án A:</label>
            <input type="text" name="answer_a" value="<?= htmlspecialchars($question['answer_a']) ?>" required>

            <label for="answer_b">Đáp án B:</label>
            <input type="text" name="answer_b" value="<?= htmlspecialchars($question['answer_b']) ?>" required>

            <label for="answer_c">Đáp án C:</label>
            <input type="text" name="answer_c" value="<?= htmlspecialchars($question['answer_c']) ?>" required>

            <label for="answer_d">Đáp án D:</label>
            <input type="text" name="answer_d" value="<?= htmlspecialchars($question['answer_d']) ?>" required>

            <label for="correct_answer">Đáp án đúng:</label>
            <select name="correct_answer" required>
                <option value="A" <?= ($question['correct_answer'] === 'A') ? 'selected' : '' ?>>A</option>
                <option value="B" <?= ($question['correct_answer'] === 'B') ? 'selected' : '' ?>>B</option>
                <option value="C" <?= ($question['correct_answer'] === 'C') ? 'selected' : '' ?>>C</option>
                <option value="D" <?= ($question['correct_answer'] === 'D') ? 'selected' : '' ?>>D</option>
            </select>

            <label for="difficulty">Độ khó:</label>
            <select name="difficulty" required>
                <option value="Dễ" <?= ($question['difficulty'] === 'Dễ') ? 'selected' : '' ?>>Dễ</option>
                <option value="Khá" <?= ($question['difficulty'] === 'Khá') ? 'selected' : '' ?>>Khá</option>
                <option value="Khó" <?= ($question['difficulty'] === 'Khó') ? 'selected' : '' ?>>Khó</option>
            </select>

            <label for="teacher_name">Tên giáo viên:</label>
            <input type="text" name="teacher_name" value="<?= htmlspecialchars($question['teacher_name']) ?>" required>

            <label for="content">Nội dung bổ sung:</label>
            <textarea name="content"><?= htmlspecialchars($question['content']) ?></textarea>

            <!-- hiển thị ảnh hiện tại -->
            <?php if (!empty($question['image'])): ?>
                <p>Ảnh hiện tại:</p>
                <img src="../uploads/<?= htmlspecialchars($question['image']) ?>" alt="Ảnh minh họa" style="max-width:200px; height:auto;">
            <?php endif; ?>

            <label for="image">Chọn ảnh mới (nếu muốn thay):</label>
            <input type="file" name="image" id="image" accept="image/*">

            <button type="submit">Cập nhật câu hỏi</button>
        </form>

        <br>
        <a href="../funsion/Sum_question.php" class="button">Quay lại trang chủ</a>
    </div>
</body>
</html>
