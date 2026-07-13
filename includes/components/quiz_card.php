<?php
/**
 * Quiz Card Component - Dùng chung cho các trang
 * Sử dụng: require_once 'includes/components/quiz_card.php';
 * Biến cần có: $quiz (mảng dữ liệu 1 đề thi), $style (array từ getSubjectStyle)
 */
?>
<a href="modules/quiz/take_quiz.php?id=<?php echo $quiz['id']; ?>" class="quiz-card">
    <div class="card-icon" style="background: <?php echo $style['bg']; ?>">
        <i class="fas <?php echo $style['icon']; ?>"></i>
    </div>
    <span class="card-subject"><?php echo htmlspecialchars($quiz['subject']); ?></span>
    <h3 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h3>
    <div class="card-meta">
        <span><i class="fas fa-layer-group"></i> <?php echo $quiz['num_questions']; ?> Câu</span>
        <div class="btn-play"><i class="fas fa-play"></i></div>
    </div>
</a>