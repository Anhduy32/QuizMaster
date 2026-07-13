<?php
/**
 * Sidebar chung cho Dashboard
 * Sử dụng: require_once 'includes/sections/sidebar.php';
 */
?>
<aside class="sidebar">
    <a href="/WebTaoBoDeTuDong/index.php" class="brand-logo">
        <i class="fa-solid fa-graduation-cap"></i> <span>QUIZMASTER</span>
    </a>
    <nav class="nav-menu">
        <a href="/WebTaoBoDeTuDong/home.php" class="nav-item active">
            <i class="fas fa-home"></i> <span>Bảng điều khiển</span>
        </a>
        <a href="/WebTaoBoDeTuDong/modules/quiz/sum_question.php" class="nav-item">
            <i class="fas fa-compass"></i> <span>Khám phá đề thi</span>
        </a>
        <a href="/WebTaoBoDeTuDong/modules/quiz/my_library.php" class="nav-item">
            <i class="fas fa-folder-open"></i> <span>Thư viện của tôi</span>
        </a>
        <a href="/WebTaoBoDeTuDong/modules/user/history.php" class="nav-item">
            <i class="fas fa-chart-bar"></i> <span>Lịch sử học tập</span>
        </a>
        
        <?php if (isset($nguoi_dung) && $nguoi_dung['role'] === 'admin'): ?>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-light);">
                <a href="/WebTaoBoDeTuDong/modules/quiz/admin/admin_approval.php" class="nav-item" style="color: #dd6b20;">
                    <i class="fas fa-user-shield"></i> <span>Duyệt đề thi</span>
                </a>
            </div>
        <?php endif; ?>
    </nav>
    <a href="/WebTaoBoDeTuDong/modules/quiz/create_quiz/step1_create_quiz.php" class="btn-create-quiz">
        <i class="fas fa-plus-circle"></i> <span>Tạo đề thi mới</span>
    </a>
</aside>
<!-- Đóng sidebar ở đây -->