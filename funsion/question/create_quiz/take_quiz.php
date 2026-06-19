<?php
session_start();
include '../../../config/database.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login/login.php');
    exit();
}

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$quiz_id) {
    header('Location: sum_question.php');
    exit();
}

// Lấy thông tin đề thi
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ? AND status = 'completed'");
$stmt->bind_param('i', $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    die("<h2 style='text-align:center; margin-top:50px; font-family:sans-serif;'>Đề thi không tồn tại hoặc chưa hoàn thiện!</h2>");
}

// Lấy danh sách câu hỏi (KHÔNG lấy cột correct_opt để tránh lộ đáp án)
$stmt_q = $conn->prepare("SELECT id, content, opt_a, opt_b, opt_c, opt_d FROM questions WHERE quiz_id = ? ORDER BY id ASC");
$stmt_q->bind_param('i', $quiz_id);
$stmt_q->execute();
$questions = $stmt_q->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Làm bài: <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/take_quiz.css?v=<?php echo time(); ?>">
</head>
<body>

    <header class="exam-header">
        <div class="exam-title"><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($quiz['title']); ?></div>
        <div class="timer-box" id="timer-display">
            <i class="fas fa-stopwatch"></i> <span id="time">00:00</span>
        </div>
    </header>

    <div class="exam-container">
        <form id="quiz-form" action="result.php" method="POST">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            
            <?php foreach ($questions as $index => $q): ?>
                <div class="question-card">
                    <div class="q-number">Câu hỏi <?php echo $index + 1; ?></div>
                    <div class="q-content"><?php echo nl2br(htmlspecialchars($q['content'])); ?></div>
                    
                    <div class="options-grid">
                        <?php foreach(['A', 'B', 'C', 'D'] as $opt): ?>
                            <label class="option-label">
                                <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $opt; ?>">
                                <div class="custom-radio"></div>
                                <div class="option-text">
                                    <strong><?php echo $opt; ?>.</strong> 
                                    <?php echo htmlspecialchars($q['opt_' . strtolower($opt)]); ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn-submit" onclick="return confirm('Bạn có chắc chắn muốn nộp bài?');">
                <i class="fas fa-paper-plane"></i> Nộp Bài Ngay
            </button>
        </form>
    </div>

    <script>
        let totalSeconds = <?php echo $quiz['time_limit'] * 60; ?>;
        const timeDisplay = document.getElementById('time');
        const form = document.getElementById('quiz-form');

        function updateTimer() {
            let minutes = Math.floor(totalSeconds / 60);
            let seconds = totalSeconds % 60;
            
            if(minutes < 10) minutes = "0" + minutes;
            if(seconds < 10) seconds = "0" + seconds;
            
            timeDisplay.textContent = minutes + ":" + seconds;

            if (totalSeconds <= 0) {
                clearInterval(timerInterval);
                alert("Hết giờ! Hệ thống sẽ tự động nộp bài của bạn.");
                form.submit();
            } else {
                totalSeconds--;
            }
        }

        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
    </script>
</body>
</html>