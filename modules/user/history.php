<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['username'])) {
    header('Location: login/login.php');
    exit();
}

$ten_dang_nhap = $_SESSION['username'];

// Kéo toàn bộ lịch sử làm bài, join với bảng quizzes để lấy tên đề thi
$truy_van = "SELECT h.*, q.title, q.subject, q.num_questions 
             FROM quiz_history h 
             JOIN quizzes q ON h.quiz_id = q.id 
             WHERE h.username = ? 
             ORDER BY h.completed_at DESC";
$stmt = $conn->prepare($truy_van);
$stmt->bind_param('s', $ten_dang_nhap);
$stmt->execute();
$lich_su = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử học tập - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/home.css?v=<?php echo time(); ?>">
    <style>
        .history-container { max-width: 1000px; margin: 0 auto; }
        .history-header { background: white; padding: 30px; border-radius: 20px; box-shadow: var(--glass-shadow); margin-bottom: 30px; border: 1px solid var(--border-light); }
        .history-title { font-size: 1.8rem; font-weight: 800; color: var(--primary-teal); margin-bottom: 8px; }
        .history-list { display: flex; flex-direction: column; gap: 15px; }
        .history-card { background: white; border-radius: 16px; padding: 20px 25px; display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--border-light); transition: 0.3s; }
        .history-card:hover { border-color: var(--primary-teal); box-shadow: var(--glass-shadow); transform: translateX(5px); }
        .h-info h3 { font-size: 1.15rem; color: var(--text-main); margin-bottom: 5px; }
        .h-meta { font-size: 0.85rem; color: var(--text-muted); display: flex; gap: 15px; }
        .h-score { text-align: right; }
        .score-circle { display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; border-radius: 50%; font-weight: 800; font-size: 1.1rem; }
        .score-good { background: #f0fff4; color: #2f855a; border: 2px solid #c6f6d5; }
        .score-bad { background: #fff5f5; color: #c53030; border: 2px solid #fed7d7; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <a href="../index.php" class="brand-logo">
            <i class="fa-solid fa-graduation-cap"></i> <span>QUIZMASTER</span>
        </a>
        <nav class="nav-menu">
            <a href="../home.php" class="nav-item"><i class="fas fa-home"></i> <span>Bảng điều khiển</span></a>
            <a href="question/sum_question.php" class="nav-item"><i class="fas fa-compass"></i> <span>Khám phá đề thi</span></a>
            <a href="my_library.php" class="nav-item"><i class="fas fa-folder-open"></i> <span>Thư viện của tôi</span></a>
            <a href="history.php" class="nav-item active"><i class="fas fa-chart-bar"></i> <span>Lịch sử học tập</span></a>
        </nav>
        <a href="question/create_quiz/step1_create_quiz.php" class="btn-create-quiz">
            <i class="fas fa-plus-circle"></i> <span>Tạo đề thi mới</span>
        </a>
    </aside>

    <main class="main-wrapper">
        <div class="history-container">
            <div class="history-header">
                <h1 class="history-title"><i class="fas fa-history"></i> Lịch sử học tập</h1>
                <p style="color: var(--text-muted);">Xem lại điểm số và quá trình rèn luyện của bạn qua từng ngày.</p>
            </div>

            <div class="history-list">
                <?php if ($lich_su && $lich_su->num_rows > 0): ?>
                    <?php while ($row = $lich_su->fetch_assoc()): 
                        $diem = $row['score'];
                        $class_diem = ($diem >= 5) ? 'score-good' : 'score-bad';
                    ?>
                        <div class="history-card">
                            <div class="h-info">
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <div class="h-meta">
                                    <span><i class="fas fa-book"></i> <?php echo htmlspecialchars($row['subject']); ?></span>
                                    <span><i class="far fa-calendar-alt"></i> <?php echo date('H:i - d/m/Y', strtotime($row['completed_at'])); ?></span>
                                </div>
                            </div>
                            <div class="h-score">
                                <div class="score-circle <?php echo $class_diem; ?>">
                                    <?php echo $diem; ?>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px; font-weight: 600;">/ 10 điểm</div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 50px; background: white; border-radius: 16px; border: 2px dashed var(--border-light);">
                        <i class="fas fa-clipboard-list" style="font-size: 3rem; color: #cbd5e0; margin-bottom: 15px;"></i>
                        <h3>Chưa có lịch sử làm bài</h3>
                        <p style="color: var(--text-muted); margin-bottom: 20px;">Bạn chưa hoàn thành bài kiểm tra nào.</p>
                        <a href="question/sum_question.php" style="background: var(--primary-teal); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700;">Khám phá đề thi ngay</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>