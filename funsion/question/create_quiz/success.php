<?php
session_start();
include '../../../config/database.php';

if (!isset($_SESSION['username'])) { header('Location: login/login.php'); exit(); }

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->bind_param('i', $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hoàn tất - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/create_quiz.css?v=<?php echo time(); ?>">
    <style>
        .success-icon {
            width: 100px; height: 100px; border-radius: 50%;
            background: #d1fae5; color: #059669;
            display: flex; align-items: center; justify-content: center;
            font-size: 50px; margin: 0 auto 30px; animation: bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        @keyframes bounceIn { 0% { transform: scale(0); } 100% { transform: scale(1); } }
        .summary-box { background: #f8fafc; border-radius: 16px; padding: 30px; display: flex; justify-content: space-around; margin: 30px 0; }
        .s-item { text-align: center; }
        .s-val { font-size: 1.5rem; font-weight: 800; color: var(--primary-teal); }
        .s-lbl { color: var(--text-muted); font-size: 0.9rem; margin-top: 5px; }
        .btn-group { display: flex; gap: 15px; justify-content: center; }
        .btn-outline { background: white; border: 2px solid var(--primary-teal); color: var(--primary-teal); padding: 14px 30px; border-radius: 12px; font-weight: 700; text-decoration: none; transition: 0.3s; }
        .btn-outline:hover { background: var(--primary-teal); color: white; }
    </style>
</head>
<body>
    <div class="creator-header">
        <div class="container header-wrapper">
            <a href="../home.php" class="back-link"><i class="fas fa-home"></i> Trang chủ</a>
            <div class="steps">
                <div class="step completed"><div class="step-number"><i class="fas fa-check"></i></div><div class="step-label">Thông tin</div></div>
                <div class="step-line active"></div>
                <div class="step completed"><div class="step-number"><i class="fas fa-check"></i></div><div class="step-label">Câu hỏi</div></div>
                <div class="step-line active"></div>
                <div class="step completed"><div class="step-number"><i class="fas fa-check"></i></div><div class="step-label">Hoàn tất</div></div>
            </div>
        </div>
    </div>

    <div class="creator-main">
        <div class="container" style="max-width: 700px;">
            <div class="creator-card" style="text-align: center; padding: 60px 40px;">
                <div class="success-icon"><i class="fas fa-check"></i></div>
                <h1 class="card-title">Tạo bộ đề thành công!</h1>
                <p class="card-desc">Bộ đề "<strong><?php echo htmlspecialchars($quiz['title']); ?></strong>" đã được đẩy lên hệ thống.</p>
                
                <div class="summary-box">
                    <div class="s-item">
                        <div class="s-val"><?php echo $quiz['num_questions']; ?></div>
                        <div class="s-lbl">Câu hỏi</div>
                    </div>
                    <div class="s-item" style="border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; padding: 0 40px;">
                        <div class="s-val"><?php echo $quiz['time_limit']; ?>'</div>
                        <div class="s-lbl">Thời gian</div>
                    </div>
                    <div class="s-item">
                        <div class="s-val"><?php echo htmlspecialchars($quiz['subject']); ?></div>
                        <div class="s-lbl">Môn học</div>
                    </div>
                </div>

                <div class="btn-group">
                    <a href="step1_create_quiz.php" class="btn-outline">Tạo đề mới</a>
                    <a href="../Sum_question.php" class="btn-primary">Khám phá kho đề</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>