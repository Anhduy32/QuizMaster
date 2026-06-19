<?php
session_start();
include '../config/database.php'; // Trỏ ra 1 nấc về thư mục gốc

if (!isset($_SESSION['username'])) {
    header('Location: login/login.php');
    exit();
}

$ten_dang_nhap = $_SESSION['username'];
$thong_bao = '';

// ================= XỬ LÝ XÓA ĐỀ THI =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $quiz_id_to_delete = (int)$_POST['quiz_id'];
    
    // Câu lệnh xóa (ON DELETE CASCADE trong SQL sẽ lo việc dọn dẹp bảng questions)
    // Chỉ cho phép xóa nếu người đó là tác giả của đề thi
    $stmt_del = $conn->prepare("DELETE FROM quizzes WHERE id = ? AND creator_username = ?");
    $stmt_del->bind_param('is', $quiz_id_to_delete, $ten_dang_nhap);
    
    if ($stmt_del->execute()) {
        $thong_bao = "<div class='alert' style='background: #f0fff4; color: #2f855a; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600;'><i class='fas fa-check-circle'></i> Đã xóa đề thi thành công!</div>";
    } else {
        $thong_bao = "<div class='alert' style='background: #fff5f5; color: #c53030; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600;'><i class='fas fa-exclamation-circle'></i> Lỗi khi xóa đề thi.</div>";
    }
}

// ================= LẤY DANH SÁCH ĐỀ THI CÁ NHÂN =================
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE creator_username = ? ORDER BY created_at DESC");
$stmt->bind_param('s', $ten_dang_nhap);
$stmt->execute();
$my_quizzes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện của tôi - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/my_library.css?v=<?php echo time(); ?>">
</head>
<body>

    <aside class="sidebar">
        <a href="../index.php" class="brand-logo">
            <i class="fa-solid fa-graduation-cap"></i> <span>QUIZMASTER</span>
        </a>
        <nav class="nav-menu">
            <a href="../home.php" class="nav-item"><i class="fas fa-home"></i> <span>Bảng điều khiển</span></a>
            <a href="question/sum_question.php" class="nav-item"><i class="fas fa-compass"></i> <span>Khám phá đề thi</span></a>
            <a href="my_library.php" class="nav-item active"><i class="fas fa-folder-open"></i> <span>Thư viện của tôi</span></a>
            <a href="history.php" class="nav-item"><i class="fas fa-chart-bar"></i> <span>Lịch sử học tập</span></a>
        </nav>
        <a href="question/create_quiz/step1_create_quiz.php" class="btn-create-quiz">
            <i class="fas fa-plus-circle"></i> <span>Tạo đề thi mới</span>
        </a>
    </aside>

    <main class="main-wrapper">
        <div class="library-container">
            
            <div class="library-header">
                <div>
                    <h1 class="library-title"><i class="fas fa-folder-open" style="color: var(--accent-teal);"></i> Thư viện của tôi</h1>
                    <p class="library-desc">Quản lý toàn bộ đề thi do chính bạn thiết kế và chia sẻ.</p>
                </div>
                <a href="question/create_quiz/step1_create_quiz.php" class="btn-create-new">
                    <i class="fas fa-plus"></i> Soạn đề mới
                </a>
            </div>

            <?php echo $thong_bao; ?>

            <div class="library-grid">
                <?php if ($my_quizzes && $my_quizzes->num_rows > 0): ?>
                    <?php while ($quiz = $my_quizzes->fetch_assoc()): ?>
                        <div class="admin-quiz-card">
                            <?php if($quiz['status'] == 'draft'): ?>
                                <div class="status-badge status-draft">Bản nháp</div>
                            <?php else: ?>
                                <div class="status-badge status-completed">Hoàn tất</div>
                            <?php endif; ?>

                            <div class="card-info">
                                <span class="subject-tag"><?php echo htmlspecialchars($quiz['subject']); ?></span>
                                <h3 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                <div class="quiz-meta">
                                    <span><i class="fas fa-question-circle"></i> <?php echo $quiz['num_questions']; ?> câu</span>
                                    <span><i class="fas fa-clock"></i> <?php echo $quiz['time_limit']; ?>p</span>
                                    <span><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($quiz['created_at'])); ?></span>
                                </div>
                            </div>

                            <div class="card-actions">
                                <a href="question/create_quiz/step2_add_questions.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Sửa câu hỏi
                                </a>
                                
                                <form method="POST" action="" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn đề thi này không? Toàn bộ câu hỏi bên trong cũng sẽ bị xóa.');" style="flex: 1;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                    <button type="submit" class="btn-action btn-delete" style="width: 100%;">
                                        <i class="fas fa-trash-alt"></i> Xóa đề
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px; background: white; border-radius: 20px; border: 2px dashed #cbd5e0;">
                        <i class="fas fa-folder-plus" style="font-size: 4rem; color: #cbd5e0; margin-bottom: 20px;"></i>
                        <h3 style="font-size: 1.3rem; margin-bottom: 10px;">Thư viện của bạn đang trống</h3>
                        <p style="color: var(--text-muted);">Hãy bắt đầu tạo ra những bộ đề chất lượng để thử thách bản thân và mọi người.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

</body>
</html>