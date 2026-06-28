<?php
session_start();
include '../../config/database.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_dang_nhap = trim($_POST['ten_dang_nhap']);
    $mat_khau = $_POST['mat_khau'];

    if (empty($ten_dang_nhap) || empty($mat_khau)) {
        $error_message = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!';
    } else {
        $truy_van_check = "SELECT id FROM users WHERE username = ?";
        $chuan_bi_check = $conn->prepare($truy_van_check);
        $chuan_bi_check->bind_param('s', $ten_dang_nhap);
        $chuan_bi_check->execute();
        $ket_qua_check = $chuan_bi_check->get_result();

        if ($ket_qua_check->num_rows > 0) {
            $error_message = 'Tên đăng nhập này đã tồn tại!';
        } else {
            $mat_khau_ma_hoa = password_hash($mat_khau, PASSWORD_BCRYPT);
            
            $truy_van_insert = "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')";
            $chuan_bi_insert = $conn->prepare($truy_van_insert);
            $chuan_bi_insert->bind_param('ss', $ten_dang_nhap, $mat_khau_ma_hoa);

            if ($chuan_bi_insert->execute()) {
                $success_message = 'Đăng ký tài khoản thành công! Đang chuyển hướng...';
                echo "<script>setTimeout(function() { window.location.href = 'login.php'; }, 2000);</script>";
            } else {
                $error_message = 'Đã xảy ra lỗi trong quá trình đăng ký, vui lòng thử lại!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/register.css?v=<?php echo time(); ?>">
    <style>
        .error-message { background: #fed7d7; color: #9b2c2c; padding: 12px 15px; border-radius: 10px; font-size: 0.88rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border: 1px solid #fecaca; }
        .success-message { background: #f0fff4; color: #2f855a; padding: 12px 15px; border-radius: 10px; font-size: 0.88rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border: 1px solid #c6f6d5; }
        .strength-weak { background: #e53e3e !important; } .strength-medium { background: #dd6b20 !important; } .strength-strong { background: #38a169 !important; }
        .strength-bar { background: #edf2f7; height: 6px; border-radius: 4px; overflow: hidden; margin-top: 8px; }
        .strength-fill { height: 100%; width: 0%; transition: width 0.3s ease, background-color 0.3s ease; }
        .strength-text { font-size: 0.8rem; color: #718096; font-weight: 600; margin-top: 5px; text-align: right; }
    </style>
</head>
<body>
    <div class="main-container">
        <a href="../../index.php" class="logo-brand" style="text-decoration: none;">
            <i class="fa-solid fa-graduation-cap"></i> QUIZMASTER
        </a>

        <div class="register-box">
            <div class="register-left">
                <div class="left-content">
                    <h1>Tạo tài khoản</h1>
                    <p class="subtitle">Tham gia cộng đồng học tập và chia sẻ đề thi lớn nhất</p>

                    <div id="messageContainer">
                        <?php if (!empty($error_message)): ?>
                            <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success_message)): ?>
                            <div class="success-message"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
                        <?php endif; ?>
                    </div>

                    <form class="register-form" id="registerForm" method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Tên đăng nhập</label>
                            <div class="input-with-icon">
                                <i class="input-icon fas fa-user"></i>
                                <input type="text" name="ten_dang_nhap" class="form-input" placeholder="Nhập tên đăng nhập của bạn" value="<?php echo isset($_POST['ten_dang_nhap']) ? htmlspecialchars($_POST['ten_dang_nhap']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mật khẩu</label>
                            <div class="input-with-icon">
                                <i class="input-icon fas fa-lock"></i>
                                <input type="password" name="mat_khau" id="password" class="form-input" placeholder="Tạo mật khẩu của bạn" required oninput="checkPasswordStrength(this.value)">
                            </div>
                            <div class="password-strength">
                                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                                <div class="strength-text" id="strengthText">Độ mạnh mật khẩu</div>
                            </div>
                        </div>

                        <div class="form-group terms-group">
                            <label class="terms-label">
                                <input type="checkbox" class="terms-checkbox" id="termsCheckbox" required>
                                <span>Tôi đồng ý với <a href="#">Điều khoản</a> & <a href="#">Chính sách</a></span>
                            </label>
                        </div>
                        
                        <button type="submit" class="register-btn" id="registerBtn">Đăng ký tài khoản</button>                    
                    </form>
                </div>
            </div>

            <div class="register-right">
                <div class="right-background"></div>
                <div class="right-content">
                    <h2>Học tập không giới hạn</h2>
                    <p class="right-subtitle">Kho tàng đề thi trắc nghiệm dành cho tất cả mọi người</p>
                    
                    <div class="info-lines">
                        <div class="info-line"><i class="fas fa-file-signature"></i><span><strong>Thi thử miễn phí:</strong> Thử sức với hàng ngàn đề thi chuẩn cấu trúc từ mọi lĩnh vực.</span></div>
                        <div class="info-line"><i class="fas fa-share-nodes"></i><span><strong>Chia sẻ kiến thức:</strong> Tự tạo bộ câu hỏi và đóng góp đề thi của riêng bạn cho cộng đồng.</span></div>
                        <div class="info-line"><i class="fas fa-chart-line"></i><span><strong>Theo dõi tiến độ:</strong> Lưu lại kết quả làm bài để đánh giá năng lực cá nhân.</span></div>
                    </div>

                    <div class="login-section">
                        <p>Bạn đã có tài khoản từ trước?</p>
                        <a href="login.php" class="login-link">Đăng nhập ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('.form-input[required]');
        const termsCheckbox = document.getElementById('termsCheckbox');
        const registerBtn = document.getElementById('registerBtn');

        function checkInputs() {
            let allFilled = true;
            inputs.forEach(input => { if (input.value.trim() === '') allFilled = false; });
            if (!termsCheckbox.checked) allFilled = false;

            if (allFilled) {
                registerBtn.removeAttribute('disabled');
                registerBtn.style.opacity = '1'; registerBtn.style.pointerEvents = 'auto'; 
            } else {
                registerBtn.setAttribute('disabled', 'true');
                registerBtn.style.opacity = '0.5'; registerBtn.style.pointerEvents = 'none';     
            }
        }
        inputs.forEach(input => { input.addEventListener('input', checkInputs); });
        termsCheckbox.addEventListener('change', checkInputs);
        checkInputs();

        function checkPasswordStrength(password) {
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            let strength = 0, text = 'Độ mạnh mật khẩu', width = 0, colorClass = '';
            
            if (password.length === 0) {
                strengthFill.style.width = '0%'; strengthText.textContent = 'Độ mạnh mật khẩu';
                strengthFill.className = 'strength-fill'; return;
            }
            if (password.length >= 6) strength++;
            if (password.match(/[a-zA-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            
            switch(strength) {
                case 1: text = 'Yếu'; width = 30; colorClass = 'strength-weak'; break;
                case 2: text = 'Trung bình'; width = 60; colorClass = 'strength-medium'; break;
                case 3: case 4: text = 'Mạnh'; width = 100; colorClass = 'strength-strong'; break;
            }
            strengthFill.className = 'strength-fill ' + colorClass;
            strengthText.textContent = text; strengthFill.style.width = width + '%';
        }
    </script>
</body>
</html>