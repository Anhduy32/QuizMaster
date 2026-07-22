<?php
/**
 * Navbar chính cho Index.php
 * Sử dụng: require_once 'includes/sections/navbar.php';
 * Biến cần có: $is_logged_in, $ho_va_ten (nếu đã login)
 */
?>
<header class="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-logo">
            <i class="fa-solid fa-graduation-cap"></i> QUIZMASTER
        </a>
        <nav class="nav-menu">
            <a href="#home" class="nav-link active">Trang Chủ</a>
            <a href="#recent-docs" class="nav-link">Đề Thi Mới</a>
            <a href="#features" class="nav-link">Khám Phá</a>
            <a href="#stats" class="nav-link">Thống Kê</a>
            
            <?php if (isset($is_logged_in) && $is_logged_in): ?>
                <a href="home.php" class="btn-nav-dashboard"><i class="fas fa-home"></i> Bảng điều khiển</a>
                <a href="modules/user/update_profile.php" class="nav-link"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($ho_va_ten ?? ''); ?></a>
                <a href="modules/auth/logout.php" class="btn-logout" title="Đăng xuất"><i class="fa-solid fa-right-from-bracket"></i></a>
            <?php else: ?>
                <a href="modules/auth/login.php" class="btn-nav-login">Đăng nhập</a>
                <a href="modules/auth/register.php" class="btn-nav-register">Đăng ký</a>
            <?php endif; ?>
        </nav>
    </div>
</header>