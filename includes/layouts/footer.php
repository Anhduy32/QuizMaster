<?php
/**
 * Footer chung cho toàn bộ trang
 * Sử dụng: require_once 'includes/layouts/footer.php';
 */
?>
    <!-- ================= GỌI CSS CHO FOOTER ================= -->
    <link rel="stylesheet" href="/WebTaoBoDeTuDong/assets/css/footer.css">

    <!-- ================= FOOTER ================= -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <i class="fa-solid fa-graduation-cap"></i> QUIZMASTER
                <p>Nền tảng tạo đề thi và quản lý ngân hàng câu hỏi thông minh.</p>
            </div>
            <div class="footer-links">
                <p>&copy; 2026 Bản quyền thuộc về WebTaoBoDeTuDong.</p>
            </div>
        </div>
    </footer>

    <?php if (isset($show_auth_modal) && $show_auth_modal): ?>
        <?php require_once 'includes/components/auth_modal.php'; ?>
    <?php endif; ?>

    <?php if (isset($page_js)): ?>
        <script src="/WebTaoBoDeTuDong/assets/js/<?php echo $page_js; ?>"></script>
    <?php endif; ?>
    
    <?php if (isset($page_inline_js)): ?>
        <script><?php echo $page_inline_js; ?></script>
    <?php endif; ?>
</body>
</html>