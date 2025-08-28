<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
include '../config/database.php';
include '../require_profile_update.php';

$ten_giao_vien = $_SESSION['username'];
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sql_subjects = "SELECT id, name AS subject_name, department FROM subjects";
$subjects_result = $conn->query($sql_subjects);

// Xử lý thêm câu hỏi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $noi_dung_cau_hoi = trim($_POST['question_text']);
    $dap_an_a = trim($_POST['answer_a']);
    $dap_an_b = trim($_POST['answer_b']);
    $dap_an_c = trim($_POST['answer_c']);
    $dap_an_d = trim($_POST['answer_d']);
    $dap_an_dung = trim($_POST['correct_answer']);
    $do_kho = trim($_POST['difficulty']);
    $nganh = trim($_POST['category']);
    $subject_id = intval($_POST['subject_id']);

    // Kiểm tra dữ liệu
    if (empty($noi_dung_cau_hoi) || empty($dap_an_a) || empty($dap_an_b) || 
        empty($dap_an_c) || empty($dap_an_d) || empty($dap_an_dung) || 
        empty($do_kho) || empty($nganh) || empty($subject_id)) {
        die("Vui lòng điền đầy đủ thông tin.");
    }

    // Xử lý upload ảnh
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            die("Lỗi khi upload ảnh!");
        }
    }

    // Thêm vào DB
    $truy_van = "INSERT INTO create_questions 
        (question_text, answer_a, answer_b, answer_c, answer_d, correct_answer, difficulty, teacher_name, category, subject_id, image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $chuan_bi = $conn->prepare($truy_van);

    if (!$chuan_bi) {
        die("Lỗi prepare: " . $conn->error);
    }

    $chuan_bi->bind_param(
        'sssssssssis',
        $noi_dung_cau_hoi,
        $dap_an_a,
        $dap_an_b,
        $dap_an_c,
        $dap_an_d,
        $dap_an_dung,
        $do_kho,
        $ten_giao_vien,
        $nganh,
        $subject_id,
        $image_name
    );

    if (!$chuan_bi->execute()) {
        die("Lỗi execute: " . $chuan_bi->error);
    } else {
        echo "<script>alert('Thêm câu hỏi thành công!'); window.location.href='../Index.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Câu Hỏi</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <script>
        function hienThiNganh() {
            var select = document.getElementById("subject_id");
            var selected = select.options[select.selectedIndex];
            var department = selected.getAttribute("data-department");
            document.getElementById("category").value = department || '';
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Thêm Câu Hỏi Mới</h1>
        <form method="POST" action="" enctype="multipart/form-data">
            <p><strong>Giáo viên:</strong> <?php echo htmlspecialchars($ten_giao_vien); ?></p>

            <label for="subject_id">Chọn bộ môn:</label>
            <select name="subject_id" id="subject_id" required onchange="hienThiNganh()">
                <option value="">-- Chọn bộ môn --</option>
                <?php while ($row = $subjects_result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" data-department="<?= $row['department'] ?>">
                        <?= $row['subject_name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="category">Ngành:</label>
            <input type="text" id="category" name="category" readonly required>

            <label for="question_text">Câu hỏi:</label>
            <textarea id="question_text" name="question_text" rows="2" required></textarea>

            <!-- Thêm upload ảnh -->
            <label for="image">Ảnh minh họa (tùy chọn):</label>
            <input type="file" name="image" id="image" accept="image/*"><br><br>

            <label for="answer_a">Đáp án A:</label>
            <textarea id="answer_a" name="answer_a" rows="2" required></textarea>

            <label for="answer_b">Đáp án B:</label>
            <textarea id="answer_b" name="answer_b" rows="2" required></textarea>

            <label for="answer_c">Đáp án C:</label>
            <textarea id="answer_c" name="answer_c" rows="2" required></textarea>

            <label for="answer_d">Đáp án D:</label>
            <textarea id="answer_d" name="answer_d" rows="2" required></textarea>

            <label for="correct_answer">Chọn đáp án đúng:</label>
            <select id="correct_answer" name="correct_answer" required>
                <option value="">Chọn đáp án đúng</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>

            <label for="difficulty">Chọn độ khó:</label>
            <select id="difficulty" name="difficulty" required>
                <option value="Dễ">Dễ</option>
                <option value="Khá">Khá</option>
                <option value="Khó">Khó</option>
            </select>

            <button type="submit">Thêm câu hỏi</button>
        </form>
        <a href="../Index.php">Quay lại</a>
    </div>
</body>
</html>
