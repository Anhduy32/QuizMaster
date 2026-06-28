<?php
// Tự động nhận diện trang đang mở để Active menu
$current_page = basename($_SERVER['PHP_SELF']);
// Đường dẫn gốc của dự án trên XAMPP (Tránh lỗi sai đường dẫn khi nhúng vào các thư mục khác nhau)
$base_url = '/WebTaoBoDeTuDong'; 
?>

<aside class="sidebar">
    <a href="<?php echo $base_url; ?>/index.php" class="brand-logo" title="Về trang giới thiệu">
        <i class="fa-solid fa-graduation-cap"></i> <span>QUIZMASTER</span>
    </a>
    <nav class="nav-menu" style="display: flex; flex-direction: column; height: 100%;">
        <div>
            <a href="<?php echo $base_url; ?>/home.php" class="nav-item <?php echo ($current_page == 'home.php') ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> <span>Bảng điều khiển</span>
            </a>
            <a href="<?php echo $base_url; ?>/explore.php" class="nav-item <?php echo ($current_page == 'explore.php') ? 'active' : ''; ?>">
                <i class="fas fa-compass"></i> <span>Khám phá đề thi</span>
            </a>            
            <a href="<?php echo $base_url; ?>/modules/user/my_library.php" class="nav-item <?php echo ($current_page == 'my_library.php') ? 'active' : ''; ?>">
                <i class="fas fa-folder-open"></i> <span>Thư viện của tôi</span>
            </a>
            <a href="<?php echo $base_url; ?>/modules/user/history.php" class="nav-item <?php echo ($current_page == 'history.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> <span>Lịch sử học tập</span>
            </a>
            
            <?php if (isset($nguoi_dung['role']) && $nguoi_dung['role'] === 'admin'): ?>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--border-light);">
                    <a href="<?php echo $base_url; ?>/modules/admin/admin_approval.php" class="nav-item <?php echo ($current_page == 'admin_approval.php') ? 'active' : ''; ?>" style="color: #dd6b20;">
                        <i class="fas fa-user-shield"></i> <span style="font-weight: 700;">Duyệt đề thi</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: auto; padding-top: 20px;">
            <a href="<?php echo $base_url; ?>/modules/auth/logout.php" class="nav-item" style="color: #e53e3e; background: #fff5f5;">
                <i class="fas fa-sign-out-alt"></i> <span style="font-weight: 700;">Đăng xuất</span>
            </a>
        </div>
    </nav>
</aside>