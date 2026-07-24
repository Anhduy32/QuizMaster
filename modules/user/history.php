<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

// Đường dẫn database
$root_path = dirname(__DIR__, 2);
$db_path = $root_path . '/config/database.php';
if (file_exists($db_path)) {
    include $db_path;
} else {
    die("Lỗi hệ thống: Không tìm thấy file kết nối cơ sở dữ liệu.");
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit();
}

$username = $_SESSION['username']; // Đã sửa lỗi biến

// 1. Kéo lịch sử làm bài
$query_history = "SELECT h.*, q.title, q.subject, q.num_questions 
                  FROM quiz_history h 
                  JOIN quizzes q ON h.quiz_id = q.id 
                  WHERE h.username = ? 
                  ORDER BY h.completed_at DESC";
$stmt = $conn->prepare($query_history);
$stmt->bind_param('s', $username);
$stmt->execute();
$lich_su = $stmt->get_result();

// 2. Lấy thống kê nhanh
$stats = ['total' => 0, 'avg' => 0];
$query_stats = "SELECT COUNT(*) as total, AVG(score) as avg_score FROM quiz_history WHERE username = ?";
$stmt_stats = $conn->prepare($query_stats);
$stmt_stats->bind_param('s', $username);
$stmt_stats->execute();
$result_stats = $stmt_stats->get_result();
if ($row_stats = $result_stats->fetch_assoc()) {
    $stats['total'] = (int)$row_stats['total'];
    $stats['avg'] = round((float)$row_stats['avg_score'], 1);
}

$page_title = 'Hồ sơ cá nhân - QuizMaster';
$page_css = 'history.css';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử học tập - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/history.css?v=<?php echo time(); ?>">
</head>
<body>
    <main class="main-wrapper">
        <!-- HEADER + THỐNG KÊ -->
        <div class="history-header">
            <div class="header-left">
                <h1><i class="fas fa-history"></i> Lịch sử học tập</h1>
                <p>Theo dõi quá trình rèn luyện và kết quả của bạn.</p>
            </div>
            <div class="stats-group">
                <div class="stat-item">
                    <i class="fas fa-file-alt"></i>
                    <div>
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Bài đã làm</div>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-star"></i>
                    <div>
                        <div class="stat-number"><?php echo $stats['avg']; ?></div>
                        <div class="stat-label">Điểm TB</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DANH SÁCH BÀI LÀM -->
        <div class="history-list">
            <?php if ($lich_su && $lich_su->num_rows > 0): ?>
                <?php while ($row = $lich_su->fetch_assoc()): 
                    $diem = (float)$row['score'];
                    $class_diem = ($diem >= 5) ? 'score-good' : 'score-bad';
                ?>
                    <div class="history-card">
                        <div class="h-info">
                            <h3>
                                <?php echo htmlspecialchars($row['title']); ?>
                                <span class="subject-badge"><?php echo htmlspecialchars($row['subject']); ?></span>
                            </h3>
                            <div class="h-meta">
                                <span><i class="fas fa-question-circle"></i> <?php echo (int)$row['num_questions']; ?> câu</span>
                                <span><i class="far fa-calendar-alt"></i> <?php echo date('H:i - d/m/Y', strtotime($row['completed_at'])); ?></span>
                                <span><i class="far fa-clock"></i> Đã nộp</span>
                            </div>
                        </div>
                        <div class="h-score">
                            <div class="score-circle <?php echo $class_diem; ?>">
                                <span><?php echo number_format($diem, 1); ?></span>
                            </div>
                            <div class="score-label">/ 10</div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Chưa có dữ liệu</h3>
                    <p>Bạn chưa hoàn thành bài kiểm tra nào. Hãy bắt đầu ôn tập ngay!</p>
                    <a href="question/sum_question.php" class="btn-start">
                        <i class="fas fa-rocket"></i> Khám phá đề thi
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>