<?php
/**
 * Hero Section cho Index.php
 * Sử dụng: require_once 'includes/sections/hero.php';
 */
?>
<section id="home" class="hero-section">
    <div class="hero-background"></div>
    <div class="hero-content text-center">
        <h1 class="hero-title">
            Học tập & Đánh giá năng lực<br><span>không giới hạn</span>
        </h1>
        <p class="hero-desc">Luyện thi, tự tạo bài kiểm tra và chia sẻ kiến thức với cộng đồng hàng ngàn người dùng trên toàn quốc.</p>
        
        <div class="hero-search-box">
            <form action="explore.php" method="GET" class="search-form">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" name="search" placeholder="Tìm kiếm đề thi, môn học, hoặc từ khóa..." class="search-input">
                <button type="submit" class="search-btn">Tìm Kiếm</button>
            </form>
        </div>
        
        <div class="hero-tags">
            <span>Xu hướng:</span>
            <a href="#" class="tag">Thi THPT Quốc Gia</a>
            <a href="#" class="tag">Tiếng Anh Giao Tiếp</a>
            <a href="#" class="tag">Tin học văn phòng</a>
            <a href="#" class="tag">Lịch sử Đảng</a>
        </div>
    </div>
</section>