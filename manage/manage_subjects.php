<?php
session_start();
include '../config/database.php'; 

//lấy thông tin từ database
$truy_van = "SELECT 
                subjects.*, 
                users.full_name AS teacher_name
             FROM subjects
             LEFT JOIN users 
             ON subjects.teacher_id = users.id AND users.role = 'giaovien'";
$ket_qua = $conn->query($truy_van);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bộ môn</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <h1>Quản lý Bộ môn</h1>

        <a href="../Index.php"><button>Quay lại Trang Chủ</button></a>
        <a href="../manage/add_subject.php"><button>Thêm bộ môn</button></a>

        <table>
            <thead>
                <tr>
                    <th>ID Bộ môn</th>
                    <th>Tên bộ môn</th>
                    <th>Mô tả</th>
                    <th>Ngành</th>
                    <th>ID Giảng viên</th>
                    <th>Giáo viên phụ trách</th> 
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $ket_qua->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['description']; ?></td>
                        <td><?php echo $row['department'] ?? 'Chưa gán'; ?></td>
                        <td><?php echo $row['teacher_id'] ?? '—'; ?></td>
                        <td><?php echo $row['teacher_name'] ?? 'Chưa có giáo viên'; ?></td> 
                        <td>
                            <a href="../manage/edit_subject.php?id=<?php echo $row['id']; ?>">Sửa</a> | 
                            <a href="../manage/delete_subject.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa bộ môn này không?');">Xóa</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
