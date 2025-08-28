<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $ten_bo_mon = mysqli_real_escape_string($conn, $_POST['full_name']);
    $ghi_chu = mysqli_real_escape_string($conn, $_POST['note']);
    $nganh = mysqli_real_escape_string($conn, $_POST['department']);
    $teacher_id = intval($_POST['teacher_id']); 
    $sql_add_subject = "INSERT INTO subjects (name, teacher_id, department, description)
                        VALUES ('$ten_bo_mon', '$teacher_id', '$nganh', '$ghi_chu')";
    if ($conn->query($sql_add_subject) === TRUE) {
        echo "<script>alert('Thêm bộ môn thành công!'); window.location.href='../manage/manage_subjects.php';</script>";
    } else {
        echo "Lỗi khi thêm vào bảng subjects: " . $conn->error;
    }
}

$sql_get_teachers = "SELECT id, full_name FROM users WHERE role = 'giaovien' AND full_name IS NOT NULL AND full_name != ''";
$teacher_result = $conn->query($sql_get_teachers);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Bộ môn</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <h1>Thêm Bộ môn</h1>
        <form method="POST" action="add_subject.php">
            <label for="full_name">Tên Bộ môn:</label>
            <input type="text" name="full_name" id="full_name" required>

            <label for="note">Mô tả Bộ môn:</label>
            <textarea name="note" id="note" required></textarea>

            <label for="department">Ngành:</label>
            <input type="text" name="department" id="department" required>

            <label for="teacher_id">Giảng viên phụ trách:</label>
            <select name="teacher_id" id="teacher_id" required>
                <option value="">-- Chọn giảng viên --</option>
                <?php 
                if ($teacher_result->num_rows > 0) {
                    while ($teacher = $teacher_result->fetch_assoc()) {
                        echo "<option value='".$teacher['id']."'>".$teacher['full_name']."</option>";
                    }
                }
                ?>
            </select>

            <button type="submit">Thêm Bộ môn</button>
        </form>

        <a href="../manage/manage_subjects.php"><button>Quay lại</button></a>
    </div>
</body>
</html>
