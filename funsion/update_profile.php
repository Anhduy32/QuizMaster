<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include '../config/database.php';


$ten_dang_nhap = $_SESSION['username'];
$truy_van = "SELECT * FROM users WHERE username = ?";
$chuan_bi = $conn->prepare($truy_van);
$chuan_bi->bind_param('s', $ten_dang_nhap);
$chuan_bi->execute();
$ket_qua = $chuan_bi->get_result();
$nguoi_dung = $ket_qua->fetch_assoc();

// Lấy thông tin người dùng
$ho_va_ten = $nguoi_dung['full_name'] ?? '';
$ngay_sinh = $nguoi_dung['birthdate'] ?? '';
$gioi_tinh = $nguoi_dung['gender'] ?? '';
$dia_chi = $nguoi_dung['address'] ?? '';
$chuc_vu = $nguoi_dung['role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ho_va_ten = trim($_POST['full_name']);
    $ngay_sinh = trim($_POST['birthdate']);
    $gioi_tinh = trim($_POST['gender']);
    $dia_chi = trim($_POST['address']);
    $chuc_vu = trim($_POST['role']);

    $truy_van_cap_nhat = "UPDATE users SET full_name = ?, birthdate = ?, gender = ?, address = ?, role = ? WHERE username = ?";
    $chuan_bi_cap_nhat = $conn->prepare($truy_van_cap_nhat);
    $chuan_bi_cap_nhat->bind_param('ssssss', $ho_va_ten, $ngay_sinh, $gioi_tinh, $dia_chi, $chuc_vu, $ten_dang_nhap);

    if ($chuan_bi_cap_nhat->execute()) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href='../Index.php';</script>";
    } else {
        echo "Lỗi cập nhật: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật thông tin cá nhân</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <h1>Cập nhật thông tin cá nhân</h1>
        <form method="POST" action="update_profile.php">
            <label for="full_name">Họ và tên:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($ho_va_ten); ?>" required>

            <label for="birthdate">Ngày sinh:</label>
            <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($ngay_sinh); ?>" required min="1995-01-01">

            <label for="gender">Giới tính:</label>
            <select id="gender" name="gender" required>
                <option value="Nam" <?php echo $gioi_tinh === 'Nam' ? 'selected' : ''; ?>>Nam</option>
                <option value="Nữ" <?php echo $gioi_tinh === 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                <option value="Khác" <?php echo $gioi_tinh === 'Khác' ? 'selected' : ''; ?>>Khác</option>
        </select>

            <label for="address">Địa chỉ:</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($dia_chi); ?>" required>

            <label for="role">Chức vụ:</label>
            <select id="role" name="role" disabled>
            <option value="hieutruong" <?php echo $chuc_vu === 'hieutruong' ? 'selected' : ''; ?>>Hiệu trưởng</option>
            <option value="hieupho" <?php echo $chuc_vu === 'hieupho' ? 'selected' : ''; ?>>Hiệu phó</option>
            <option value="giaovien" <?php echo $chuc_vu === 'giaovien' ? 'selected' : ''; ?>>Giáo viên</option>
        </select>
<input type="hidden" name="role" value="<?php echo htmlspecialchars($chuc_vu); ?>">
            <button type="submit">Cập nhật</button>
        </form>
            <a href="../Index.php">
            <button>Quay lại trang chủ</button>
            </a>
    </div>
</body>
</html>
