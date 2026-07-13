<?php
/**
 * Recent Documents Section cho Index.php
 * Sử dụng: require_once 'includes/sections/recent_docs.php';
 * Biến cần có: $ket_qua_de_moi (mysqli result)
 */
?>
<section id="recent-docs" class="section recent-docs-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Đề thi được cập nhật gần đây</h2>
            <a href="#" class="view-all-link check-auth-link" data-target="explore.php">Xem tất cả <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="docs-grid">
            <?php if (isset($ket_qua_de_moi) && $ket_qua_de_moi && $ket_qua_de_moi->num_rows > 0): ?>
                <?php while ($de_thi = $ket_qua_de_moi->fetch_assoc()): 
                    $style = getSubjectIcon($de_thi['subject']);
                    $ngay_tao = strtotime($de_thi['created_at']);
                    $is_new = ($ngay_tao > strtotime('-7 days'));
                ?>
                    <div class="doc-card">
                        <div class="doc-icon" style="color: <?php echo $style['color']; ?>;">
                            <i class="fa-solid <?php echo $style['icon']; ?>"></i>
                        </div>
                        <div class="doc-details">
                            <h3 class="doc-title"><?php echo htmlspecialchars($de_thi['title']); ?></h3>
                            <p class="doc-meta">Môn: <?php echo htmlspecialchars($de_thi['subject']); ?> • Đăng bởi: <?php echo htmlspecialchars($de_thi['creator_name']); ?></p>
                            <div class="doc-tags">
                                <span class="doc-badge"><?php echo $de_thi['num_questions']; ?> Câu</span>
                                <?php if ($is_new): ?>
                                    <span class="doc-badge badge-new" style="background: #e6f4ea; color: #1e8e3e;">Mới</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="#" class="doc-action-btn check-auth-link" data-target="modules/quiz/take_quiz.php?id=<?php echo $de_thi['id']; ?>"><i class="fas fa-play"></i></a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #718096; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                    <h3 style="font-size: 1.2rem;">Hệ thống chưa có đề thi nào</h3>
                    <p style="margin-top: 5px;">Hãy trở thành người đầu tiên đóng góp bộ đề cho cộng đồng!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>