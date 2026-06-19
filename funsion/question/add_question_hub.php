<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login/login.php');
    exit();
}

$ten_dang_nhap = $_SESSION['username'];

// Truy vấn lấy danh sách đề thi CỦA RIÊNG USER NÀY
$stmt = $conn->prepare("SELECT id, title, subject, num_questions, time_limit, status, created_at FROM quizzes WHERE creator_username = ? ORDER BY created_at DESC");
$stmt->bind_param('s', $ten_dang_nhap);
$stmt->execute();
$quizzes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm câu hỏi - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../css/add_question.css?php echo time(); ?>">
</head>
<body>

    <aside class="sidebar">
        <a href="../../../index.php" class="brand-logo">
            <i class="fa-solid fa-graduation-cap"></i> <span>QUIZMASTER</span>
        </a>
        <nav class="nav-menu">
            <a href="../../../home.php" class="nav-item"><i class="fas fa-home"></i> <span>Bảng điều khiển</span></a>
            <a href="sum_question.php" class="nav-item"><i class="fas fa-compass"></i> <span>Khám phá đề thi</span></a>
        </nav>
        <a href="create_quiz/step1_create_quiz.php" class="btn-create-quiz">
            <i class="fas fa-plus-circle"></i> <span>Tạo đề thi mới</span>
        </a>
    </aside>

    <main class="main-wrapper">
        <div class="hub-container">
            <div class="hub-header">
                <div class="hub-icon"><i class="fas fa-puzzle-piece"></i></div>
                <div>
                    <h1 class="hub-title">Chọn đề thi để thêm câu hỏi</h1>
                    <p class="hub-desc">Bạn muốn bổ sung kiến thức vào bộ đề nào dưới đây?</p>
                </div>
            </div>

            <div class="quiz-list-grid">
                <?php if ($quizzes && $quizzes->num_rows > 0): ?>
                    <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                        <a href="create_quiz/step2_add_questions.php?quiz_id=<?php echo $quiz['id']; ?>" class="quiz-select-card">
                            <div class="q-card-top">
                                <div class="q-card-title"><?php echo htmlspecialchars($quiz['title']); ?></div>
                                <?php if($quiz['status'] == 'draft'): ?>
                                    <span class="status-badge status-draft">Bản nháp</span>
                                <?php else: ?>
                                    <span class="status-badge status-completed">Đã duyệt</span>
                                <?php endif; ?>
                            </div>
                            <div class="q-card-meta">
                                <span><i class="fas fa-book"></i> <?php echo htmlspecialchars($quiz['subject']); ?></span>
                                <span><i class="fas fa-list-ul"></i> <?php echo $quiz['num_questions']; ?> câu</span>
                                <span><i class="fas fa-clock"></i> <?php echo date('d/m', strtotime($quiz['created_at'])); ?></span>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open" style="font-size: 3rem; color: #cbd5e0; margin-bottom: 15px;"></i>
                        <h3>Bạn chưa có bộ đề nào</h3>
                        <p style="color: var(--text-muted); margin-bottom: 20px;">Vui lòng tạo thông tin đề thi trước khi nạp câu hỏi.</p>
                        <a href="create_quiz/step1_create_quiz.php" style="background: var(--primary-teal); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700;">Tạo đề thi mới</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>