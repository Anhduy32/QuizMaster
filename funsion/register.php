<?php
include '../config/database.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten_dang_nhap = $_POST['ten_dang_nhap'] ?? '';
    $mat_khau = $_POST['mat_khau'] ?? '';
    $vai_tro = $_POST['vai_tro'] ?? '';

    if (empty($ten_dang_nhap) || empty($mat_khau) || empty($vai_tro)) {
        $thong_bao_loi = 'Vui lòng nhập đầy đủ thông tin.';
    } else {
        $chuan_bi = $conn->prepare("SELECT 1 FROM users WHERE username = ?");
        $chuan_bi->bind_param("s", $ten_dang_nhap);
        $chuan_bi->execute();
        $ket_qua = $chuan_bi->get_result();

        if ($ket_qua->num_rows > 0) {
            $thong_bao_loi = 'Tên đăng nhập đã tồn tại.';
        } else {
            $truy_van = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
            $chuan_bi = $conn->prepare($truy_van);
            $chuan_bi->bind_param("sss", $ten_dang_nhap, $mat_khau, $vai_tro);

            $thong_bao_thanh_cong = ($chuan_bi->execute()) ? 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.' : 'Lỗi hệ thống: ' . $conn->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <script type="text/javascript">
        <?php if (!empty($thong_bao_loi)): ?>
            window.onload = function() {
                alert("<?php echo $thong_bao_loi; ?>");
            }
        <?php elseif (!empty($thong_bao_thanh_cong)): ?>
            window.onload = function() {
                alert("<?php echo $thong_bao_thanh_cong; ?>");
            }
        <?php endif; ?>
    </script>
</head>
<body>
    <div class="container">
        <h1>Đăng ký tài khoản</h1>
        <form method="POST">
            <label for="ten_dang_nhap">Tên đăng nhập</label>
            <input type="text" name="ten_dang_nhap" id="ten_dang_nhap" required>

            <label for="mat_khau">Mật khẩu</label>
            <input type="password" name="mat_khau" id="mat_khau" required>
            
            <button type="submit">Đăng ký</button>
        </form>
        <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </div>
</body>
</html>

