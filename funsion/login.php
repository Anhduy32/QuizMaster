<?php
session_start(); 
include '../config/database.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten_dang_nhap = $_POST['username'];
    $mat_khau = $_POST['password'];
    
    $truy_van = "SELECT * FROM users WHERE username='$ten_dang_nhap' AND password='$mat_khau'";
    $ket_qua = $conn->query($truy_van);
    
    if ($ket_qua->num_rows > 0) {
        $_SESSION['username'] = $ten_dang_nhap;
        header('Location: ../Index.php'); 
        exit();
    } else {
        $loi = 'Sai tên đăng nhập hoặc mật khẩu';
    }
}
?>




<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <script type="text/javascript">
        <?php if (isset($loi) && !empty($loi)): ?>
            window.onload = function() {
                alert("<?php echo $loi; ?>");
            }
        <?php endif; ?>
    </script>
</head>
<body>
    <div class="container">
        <h1>Đăng nhập</h1>
        <form method="POST">
            <label for="ten_dang_nhap">Tên đăng nhập</label>
            <input type="text" name="username" id="username" required>
            <label for="mat_khau">Mật khẩu</label>
            <input type="password" name="password" id="password" required>
            <button type="submit">Đăng nhập</button>
        </form>
        <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
    </div>
</body>
</html>
