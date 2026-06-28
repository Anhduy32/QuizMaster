<?php
session_start();
include '../../../config/database.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../../login/login.php");
    exit();
}

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// Lấy thông tin đề thi để hiển thị
$query = "SELECT title, num_questions FROM quizzes WHERE id = ? AND creator_username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $quiz_id, $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>Không tìm thấy thông tin đề thi! <a href='../../../index.php'>Về trang chủ</a></div>");
}

$quiz = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo đề thi thành công! - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #ebf8ff; margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .success-card { background: #fff; padding: 50px 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(49, 130, 206, 0.1); text-align: center; max-width: 500px; width: 100%; animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        
        @keyframes popIn { 0% { opacity: 0; transform: scale(0.8); } 100% { opacity: 1; transform: scale(1); } }
        
        .icon-wrapper { width: 90px; height: 90px; background: #c6f6d5; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 25px; color: #38a169; font-size: 3rem; }
        
        h1 { margin: 0 0 10px; color: #1a202c; font-size: 1.8rem; font-weight: 800; }
        p { color: #718096; line-height: 1.6; margin-bottom: 30px; font-size: 1.05rem; }
        
        .quiz-summary { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 12px; margin-bottom: 30px; }
        .quiz-summary strong { color: #2b6cb0; display: block; font-size: 1.1rem; margin-bottom: 5px; }
        
        .action-buttons { display: flex; flex-direction: column; gap: 12px; }
        .btn { padding: 14px 20px; border-radius: 10px; font-weight: 600; text-decoration: none; transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 10px; }
        
        .btn-primary { background: #3182ce; color: #fff; }
        .btn-primary:hover { background: #2b6cb0; transform: translateY(-2px); }
        
        .btn-outline { background: #fff; border: 2px solid #e2e8f0; color: #4a5568; }
        .btn-outline:hover { border-color: #cbd5e0; background: #f8fafc; }
    </style>
</head>
<body>

    <div class="success-card">
        <div class="icon-wrapper">
            <i class="fas fa-check"></i>
        </div>
        
        <h1>Tuyệt vời!</h1>
        <p>Bộ đề thi của bạn đã được xuất bản thành công và sẵn sàng trên hệ thống.</p>
        
        <div class="quiz-summary">
            <strong><?php echo htmlspecialchars($quiz['title']); ?></strong>
            <span>Tổng cộng: <?php echo $quiz['num_questions']; ?> câu hỏi</span>
        </div>
        
        <div class="action-buttons">
            <a href="../take_quiz.php?id=<?php echo $quiz_id; ?>" class="btn btn-primary">
                <i class="fas fa-play-circle"></i> Trải nghiệm thi thử ngay
            </a>
            
            <a href="step1_create_quiz.php" class="btn btn-outline">
                <i class="fas fa-plus"></i> Tạo thêm đề thi khác
            </a>
            
            <a href="../../../home.php" class="btn btn-outline">
                <i class="fas fa-home"></i> Trở về Bảng điều khiển
            </a>
        </div>
    </div>

</body>
</html>