<?php
session_start(); 
include '../../config/database.php'; 

$loi = ''; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten_dang_nhap = trim($_POST['username']);
    $mat_khau = $_POST['password'];
    
    // Tìm kiếm tài khoản trong database bằng Prepared Statement (Chống Hack SQL Injection)
    $truy_van = "SELECT * FROM users WHERE username = ?";
    $chuan_bi = $conn->prepare($truy_van);
    $chuan_bi->bind_param('s', $ten_dang_nhap);
    $chuan_bi->execute();
    $ket_qua = $chuan_bi->get_result();
    
    if ($ket_qua && $ket_qua->num_rows > 0) {
        $nguoi_dung = $ket_qua->fetch_assoc();
        
        // Kiểm tra mật khẩu đã mã hóa
        if (password_verify($mat_khau, $nguoi_dung['password'])) {
            $_SESSION['username'] = $ten_dang_nhap;
            // Tối ưu UI/UX: Đăng nhập thành công thì phi thẳng vào Dashboard
            header('Location: ../../home.php'); 
            exit();
        } else {
            $loi = 'Sai tên đăng nhập hoặc mật khẩu!';
        }
    } else {
        $loi = 'Sai tên đăng nhập hoặc mật khẩu!';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/login.css?v=<?php echo time(); ?>">
    <style>
        .error-msg {
            background: #fed7d7; color: #9b2c2c; padding: 12px 15px; border-radius: 10px;
            font-size: 0.88rem; font-weight: 600; margin-bottom: 20px; display: flex;
            align-items: center; gap: 8px; animation: fadeIn 0.3s ease; border: 1px solid #fecaca;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="main-container">
        <a href="../../index.php" class="logo-ramro" style="text-decoration: none;">
            <i class="fa-solid fa-graduation-cap"></i> QUIZMASTER
        </a>

        <div class="login-box">
            <div class="login-left">
                <div class="left-content">
                    <h1>Đăng nhập</h1>
                    <p class="subtitle">Truy cập vào tài khoản của bạn</p>
                    
                    <?php if (!empty($loi)): ?>
                        <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $loi; ?></div>
                    <?php endif; ?>

                    <form action="" method="post"> 
                        <div class="form-group">
                            <input type="text" name="username" class="form-input" placeholder="Tên đăng nhập" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-input" placeholder="Mật khẩu" required>
                        </div>
                        <button type="submit" class="btn-login">ĐĂNG NHẬP</button>
                    </form>
                    <a href="#" class="forgot-password">Quên mật khẩu?</a>
                </div>
            </div>

            <div class="or-divider">hoặc</div>

            <div class="login-right">
                <div class="right-background"></div>
                <div class="right-content">
                    <h2>Kết nối nhanh</h2>
                    <p class="subtitle">Đăng nhập bằng mạng xã hội của bạn</p>
                    
                    <div class="social-icons">
                        <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-btn twitter"><i class="fab fa-twitter"></i></a>
                        <a href="google_login.php" class="social-btn google-plus"><i class="fab fa-google-plus-g"></i></a>
                    </div>
                    <div class="sign-up-section">
                        <p>Bạn chưa có tài khoản?</p>
                        <a href="register.php" class="sign-up-link">ĐĂNG KÝ NGAY</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() { this.parentElement.classList.add('focused'); });
            input.addEventListener('blur', function() { if (!this.value) { this.parentElement.classList.remove('focused'); } });
            if (input.value) { input.parentElement.classList.add('focused'); }
        });
    </script>
</body>
</html>