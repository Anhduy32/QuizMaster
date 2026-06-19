<?php
session_start();
include '../../../config/database.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['quiz_id'])) {
    header('Location: sum_question.php');
    exit();
}

$username = $_SESSION['username'];
$quiz_id = (int)$_POST['quiz_id'];
$user_answers = $_POST['answers'] ?? []; 

// 1. Kéo đáp án chuẩn từ Database để chấm điểm
$stmt = $conn->prepare("SELECT id, correct_opt FROM questions WHERE quiz_id = ?");
$stmt->bind_param('i', $quiz_id);
$stmt->execute();
$correct_answers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total_questions = count($correct_answers);
$correct_count = 0;

// Chấm điểm từng câu
foreach ($correct_answers as $row) {
    $q_id = $row['id'];
    if (isset($user_answers[$q_id]) && $user_answers[$q_id] === $row['correct_opt']) {
        $correct_count++;
    }
}

// Tính điểm trên thang 10 
$score = ($total_questions > 0) ? round(($correct_count / $total_questions) * 10, 1) : 0;

// 2. Lưu kết quả vào bảng quiz_history
$stmt_history = $conn->prepare("INSERT INTO quiz_history (username, quiz_id, score, completed_at) VALUES (?, ?, ?, NOW())");
$stmt_history->bind_param('sid', $username, $quiz_id, $score);
$stmt_history->execute();

// Lấy thông tin đề thi 
$stmt_quiz = $conn->prepare("SELECT title FROM quizzes WHERE id = ?");
$stmt_quiz->bind_param('i', $quiz_id);
$stmt_quiz->execute();
$quiz_title = $stmt_quiz->get_result()->fetch_assoc()['title'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả bài thi - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/result.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="result-card">
        <div class="trophy-icon"><i class="fas fa-trophy"></i></div>
        <h1>Hoàn Thành Bài Thi!</h1>
        <p class="quiz-name"><?php echo htmlspecialchars($quiz_title); ?></p>
        
        <div class="score-circle" style="border-color: <?php echo ($score >= 5) ? '#38a169' : '#e53e3e'; ?>; background: <?php echo ($score >= 5) ? '#f0fff4' : '#fff5f5'; ?>;">
            <div class="score-val" style="color: <?php echo ($score >= 5) ? '#2f855a' : '#c53030'; ?>;"><?php echo $score; ?></div>
            <div class="score-lbl" style="color: <?php echo ($score >= 5) ? '#38a169' : '#e53e3e'; ?>;">Điểm số</div>
        </div>

        <div class="stats-row">
            <div class="stat-item">
                <span class="stat-val"><?php echo $correct_count; ?></span>
                <span class="stat-lbl">Câu đúng</span>
            </div>
            <div class="stat-item" style="border-left: 2px solid #e2e8f0; border-right: 2px solid #e2e8f0; padding: 0 30px;">
                <span class="stat-val"><?php echo $total_questions - $correct_count; ?></span>
                <span class="stat-lbl">Câu sai</span>
            </div>
            <div class="stat-item">
                <span class="stat-val"><?php echo $total_questions; ?></span>
                <span class="stat-lbl">Tổng câu</span>
            </div>
        </div>

        <a href="../../../home.php" class="btn-home">
            <i class="fas fa-home"></i> Trở về Bảng Điều Khiển
        </a>
    </div>

</body>
</html>