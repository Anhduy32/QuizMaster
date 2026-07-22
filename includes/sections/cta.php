<?php
/**
 * CTA Section cho Index.php
 * Sử dụng: require_once 'includes/sections/cta.php';
 * Biến cần có: $is_logged_in
 */
?>
<section class="cta-section">
    <div class="container">
        <div class="cta-box">
            <h2>Sẵn sàng nâng tầm kiến thức của bạn?</h2>
            <p>Gia nhập cộng đồng QuizMaster ngay hôm nay để trải nghiệm môi trường học tập không giới hạn.</p>
            <?php if (!isset($is_logged_in) || !$is_logged_in): ?>
                <a href="modules/auth/register.php" class="btn-primary cta-btn">Tham gia hoàn toàn miễn phí</a>
            <?php else: ?>
                <a href="home.php" class="btn-primary cta-btn">Vào Bảng điều khiển ngay</a>
            <?php endif; ?>
        </div>
    </div>
</section>