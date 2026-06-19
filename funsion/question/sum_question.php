<?php
session_start();
// Nhảy ra ngoài 2 lần để về thư mục gốc tìm config
include '../../config/database.php'; 

if (!isset($_SESSION['username'])) {
    header('Location: ../login/login.php');
    exit();
}

$ten_dang_nhap = $_SESSION['username'];

// 1. LẤY DANH SÁCH TẤT CẢ ĐỀ THI ĐÃ HOÀN THÀNH TỪ CỘNG ĐỒNG
$truy_van = "SELECT q.*, u.full_name AS creator_name, u.picture AS creator_avatar 
             FROM quizzes q 
             JOIN users u ON q.creator_username = u.username 
             WHERE q.status = 'completed' 
             ORDER BY q.created_at DESC";
$ket_qua = $conn->query($truy_van);

// Hàm bổ trợ đồng bộ màu sắc theo môn học
function getSubjectColor($subject) {
    $sub = mb_strtolower($subject, 'UTF-8');
    if (strpos($sub, 'toán') !== false) return ['bg' => 'linear-gradient(135deg, #4299e1, #3182ce)', 'icon' => 'fa-square-root-alt', 'light' => '#ebf8ff', 'text' => '#2b6cb0'];
    if (strpos($sub, 'lý') !== false || strpos($sub, 'địa') !== false) return ['bg' => 'linear-gradient(135deg, #ed8936, #dd6b20)', 'icon' => 'fa-landmark', 'light' => '#fffaf0', 'text' => '#dd6b20'];
    if (strpos($sub, 'anh') !== false) return ['bg' => 'linear-gradient(135deg, #f56565, #e53e3e)', 'icon' => 'fa-language', 'light' => '#fff5f5', 'text' => '#c53030'];
    if (strpos($sub, 'tin') !== false) return ['bg' => 'linear-gradient(135deg, #9f7aea, #805ad5)', 'icon' => 'fa-laptop-code', 'light' => '#faf5ff', 'text' => '#6b46c1'];
    return ['bg' => 'linear-gradient(135deg, #48bb78, #38a169)', 'icon' => 'fa-flask', 'light' => '#f0fff4', 'text' => '#2f855a'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khám phá đề thi - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../css/sum_question.css?v=<?php echo time(); ?>">
</head>
<body>

    <aside class="sidebar">
        <a href="../../../index.php" class="brand-logo">
            <i class="fa-solid fa-graduation-cap"></i> <span>QUIZMASTER</span>
        </a>
        <nav class="nav-menu">
            <a href="../../home.php" class="nav-item"><i class="fas fa-home"></i> <span>Bảng điều khiển</span></a>
            <a href="sum_question.php" class="nav-item active"><i class="fas fa-compass"></i> <span>Khám phá đề thi</span></a>
            <a href="../my_library.php" class="nav-item"><i class="fas fa-folder-open"></i> <span>Thư viện của tôi</span></a>
            <a href="../history.php" class="nav-item"><i class="fas fa-chart-bar"></i> <span>Lịch sử học tập</span></a>
        </nav>
        <a href="create_quiz/step1_create_quiz.php" class="btn-create-quiz">
            <i class="fas fa-plus-circle"></i> <span>Tạo đề thi mới</span>
        </a>
    </aside>

    <main class="main-wrapper">
        <div class="explore-container">
            
            <div class="explore-header">
                <div>
                    <h1 class="explore-title"><i class="fas fa-globe-asia" style="color: var(--accent-teal);"></i> Kho Tri Thức Cộng Đồng</h1>
                    <p class="explore-desc">Khám phá và thử sức với những bộ đề trắc nghiệm chất lượng được chia sẻ từ mọi thành viên.</p>
                </div>
            </div>

            <div class="quiz-grid">
                <?php if ($ket_qua && $ket_qua->num_rows > 0): ?>
                    <?php while ($quiz = $ket_qua->fetch_assoc()): 
                        $style = getSubjectColor($quiz['subject']);
                        
                        // Xử lý biến ảnh đại diện an toàn
                        if (!empty($quiz['creator_avatar'])) {
                            $avatar = htmlspecialchars($quiz['creator_avatar']);
                        } else {
                            $avatar = "https://ui-avatars.com/api/?name=" . urlencode($quiz['creator_name']) . "&background=2a5d6a&color=fff";
                        }
                    ?>
                        <div class="extended-quiz-card">
                            <div class="card-top">
                                <div class="subject-icon-box" style="background: <?php echo $style['bg']; ?>;">
                                    <i class="fas <?php echo $style['icon']; ?>"></i>
                                </div>
                                <span class="subject-badge" style="background: <?php echo $style['light']; ?>; color: <?php echo $style['text']; ?>;">
                                    <?php echo htmlspecialchars($quiz['subject']); ?>
                                </span>
                            </div>

                            <div class="card-mid">
                                <h3 class="quiz-item-title"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                <p class="quiz-item-desc">
                                    <?php echo !empty($quiz['description']) ? htmlspecialchars($quiz['description']) : "Không có mô tả chi tiết cho bộ đề này. Hãy tham gia thi để đánh giá kiến thức."; ?>
                                </p>
                            </div>

                            <div class="card-bottom">
                                <div class="creator-info">
                                    <img src="<?php echo $avatar; ?>" alt="Avatar" class="creator-img">
                                    <div class="creator-text">
                                        <h4><?php echo htmlspecialchars($quiz['creator_name']); ?></h4>
                                        <span><?php echo date('d/m/Y', strtotime($quiz['created_at'])); ?></span>
                                    </div>
                                </div>
                                
                                <div class="quiz-action-group">
                                    <div class="quiz-stats-info">
                                        <span><i class="fas fa-question-circle"></i> <?php echo $quiz['num_questions']; ?> câu</span>
                                        <span><i class="fas fa-clock"></i> <?php echo $quiz['time_limit']; ?>p</span>
                                    </div>
                                    <a href="create_quiz/take_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-start-quiz">
                                         Vào thi <i class="fas fa-play"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-knowledge-box">
                        <i class="fas fa-box-open"></i>
                        <h3>Kho đề thi đang trống</h3>
                        <p>Chưa có ai chia sẻ đề thi công khai. Hãy là người đầu tiên đóng góp bộ đề!</p>
                        <a href="create_quiz/step1_create_quiz.php" class="btn-create-now">Tạo đề thi ngay</a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

</body>
</html>