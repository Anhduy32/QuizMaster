<?php
include '../config/database.php';

$id = $_GET['id'] ?? 0;

$sql = "SELECT * FROM subjects WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$subject = $result->fetch_assoc();

$teacher_sql = "SELECT full_name FROM users WHERE id = ?";
$teacher_stmt = $conn->prepare($teacher_sql);
$teacher_stmt->bind_param("i", $subject['teacher_id']);
$teacher_stmt->execute();
$teacher_result = $teacher_stmt->get_result();
$teacher = $teacher_result->fetch_assoc();
$teacher_name = $teacher ? $teacher['full_name'] : 'Chưa có giáo viên';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $department = $_POST['department'];
    $teacher_id = $_POST['teacher_id'];

    $sql_update = "UPDATE subjects SET name = ?, description = ?, department = ?, teacher_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sssii", $name, $description, $department, $teacher_id, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href='manage_subjects.php';</script>";
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Bộ môn</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <h1>Sửa Bộ môn</h1>
        <form method="POST">
            <label>Tên Bộ môn:</label>
            <input type="text" name="name" value="<?php echo $subject['name']; ?>" required>

            <label>Mô tả:</label>
            <textarea name="description" required><?php echo $subject['description']; ?></textarea>

            <label>Ngành:</label>
            <input type="text" name="department" value="<?php echo $subject['department']; ?>" required>

            <label>Giảng viên phụ trách:</label>
            <input type="text" name="teacher_name" value="<?php echo $teacher_name; ?>" readonly>

            <label>Chọn Giảng viên:</label>
            <select name="teacher_id" required>
                <option value="">-- Chọn giảng viên --</option>
                <?php 
                $teachers_sql = "SELECT id, full_name FROM users WHERE role = 'giaovien' AND full_name IS NOT NULL AND full_name != ''";
                $teachers_result = $conn->query($teachers_sql);

                if ($teachers_result->num_rows > 0) {
                    while ($teacher = $teachers_result->fetch_assoc()) {
                        $selected = ($subject['teacher_id'] == $teacher['id']) ? 'selected' : '';
                        echo "<option value='".$teacher['id']."' $selected>".$teacher['full_name']."</option>";
                    }
                }
                ?>
            </select>

            <button type="submit">Cập nhật</button>
        </form>

        <a href="manage_subjects.php"><button>Quay lại</button></a>
    </div>
</body>
</html>
