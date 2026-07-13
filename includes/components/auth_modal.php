<?php
/**
 * Auth Modal - Popup yêu cầu đăng nhập
 * Sử dụng: require_once 'includes/components/auth_modal.php';
 */
?>
<div id="authModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-close" onclick="closeAuthModal()">&times;</div>
        <div class="modal-icon"><i class="fas fa-lock"></i></div>
        <h3>Yêu cầu đăng nhập</h3>
        <p>Vui lòng đăng nhập hoặc tạo tài khoản để có thể làm bài thi và sử dụng các công cụ học tập.</p>
        <div class="modal-buttons">
            <a href="modules/auth/login.php" class="btn-modal-login">Đăng nhập ngay</a>
            <a href="modules/auth/register.php" class="btn-modal-register">Tạo tài khoản mới</a>
        </div>
    </div>
</div>

<script>
    function closeAuthModal() {
        document.getElementById('authModal').classList.remove('active');
    }
</script>