<?php
/**
 * Stats Section cho Index.php
 * Sử dụng: require_once 'includes/sections/stats.php';
 * Biến cần có: $total_members, $total_quizzes, $total_questions
 */
?>
<section id="stats" class="section stats-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title text-white">Cộng đồng học tập lớn mạnh mỗi ngày</h2>
        </div>
        <div class="stats-box">
            <div class="stat-item">
                <div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div>
                <span class="stat-number" data-target="<?php echo htmlspecialchars($total_questions ?? 0); ?>">0</span>
                <span class="stat-label">Câu Hỏi</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-icon"><i class="fa-solid fa-file-lines"></i></div>
                <span class="stat-number" data-target="<?php echo htmlspecialchars($total_quizzes ?? 0); ?>">0</span>
                <span class="stat-label">Đề Thi</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                <span class="stat-number" data-target="<?php echo htmlspecialchars($total_members ?? 0); ?>">0</span>
                <span class="stat-label">Thành Viên</span>
            </div>
        </div>
    </div>
</section>