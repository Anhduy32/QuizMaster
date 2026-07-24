<?php
session_start();
include '../../config/database.php';

$root_path = dirname(__DIR__, 2); 
$db_path = $root_path . '/config/database.php';

if (file_exists($db_path)) {
    include $db_path;
} else {
    die("Lỗi hệ thống: Không tìm thấy file kết nối cơ sở dữ liệu tại đường dẫn: " . $db_path);
}

if (!isset($_SESSION['username'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['quiz_id'])) {
    header("Location: ../../index.php");
    exit();
}

$quiz_id = (int)$_POST['quiz_id'];
$user_answers = isset($_POST['answers']) ? $_POST['answers'] : [];
$username = $_SESSION['username'];

// Lấy thông tin đề thi
$query_quiz = "SELECT * FROM quizzes WHERE id = ?";
$stmt_quiz = $conn->prepare($query_quiz);
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$quiz = $stmt_quiz->get_result()->fetch_assoc();

// Lấy danh sách câu hỏi
$query_q = "SELECT id, content, opt_a, opt_b, opt_c, opt_d, correct_opt FROM questions WHERE quiz_id = ? ORDER BY id ASC";
$stmt_q = $conn->prepare($query_q);
$stmt_q->bind_param("i", $quiz_id);
$stmt_q->execute();
$questions = $stmt_q->get_result();

$total_q = $questions->num_rows;
$correct_count = 0;
$is_essay_quiz = false;

$review_data = []; // Mảng lưu lịch sử làm bài để hiển thị lại

while ($q = $questions->fetch_assoc()) {
    $q_id = $q['id'];
    $u_ans = isset($user_answers[$q_id]) ? $user_answers[$q_id] : null;
    $c_ans = $q['correct_opt'];
    
    // Kiểm tra nếu là đề tự luận (opt_b trống)
    if (empty($q['opt_b'])) {
        $is_essay_quiz = true;
        $is_correct = false; // Tự luận chờ giáo viên chấm
    } else {
        $is_correct = ($u_ans === $c_ans);
        if ($is_correct) $correct_count++;
    }

    $review_data[] = [
        'question_id' => $q_id,
        'question' => $q,
        'user_ans' => $u_ans,
        'is_correct' => $is_correct
    ];
}

// 1. TÍNH TOÁN ĐIỂM SỐ & TRẠNG THÁI
$total_score = 10; 
$score = ($total_q > 0) ? round(($correct_count / $total_q) * 10, 2) : 0;
$is_graded = 1; // Mặc định là đã chấm (Trắc nghiệm)

// Nếu là bài tự luận, điểm tạm thời bằng 0 và trạng thái là chờ chấm
if ($is_essay_quiz) {
    $score = 0;
    $is_graded = 0; 
}

// ============================================================
// 2. LƯU LỊCH SỬ CHUNG VÀO BẢNG quiz_history
// ============================================================
$insert_history = "INSERT INTO quiz_history (username, quiz_id, score, total_score, is_graded, completed_at) VALUES (?, ?, ?, ?, ?, NOW())";
$stmt_history = $conn->prepare($insert_history);
$stmt_history->bind_param("siddi", $username, $quiz_id, $score, $total_score, $is_graded);
$stmt_history->execute();

// Lấy ID của lượt thi vừa lưu để liên kết với bảng chi tiết
$history_id = $stmt_history->insert_id;
$stmt_history->close();

// ============================================================
// 3. LƯU CHI TIẾT TỪNG ĐÁP ÁN VÀO BẢNG user_answers
// ============================================================
if ($history_id > 0 && !empty($review_data)) {
    $insert_ans = "INSERT INTO user_answers (history_id, question_id, user_answer, is_correct) VALUES (?, ?, ?, ?)";
    $stmt_ans = $conn->prepare($insert_ans);
    
    foreach ($review_data as $data) {
        $q_id = $data['question_id'];
        $u_a = $data['user_ans'] !== null ? $data['user_ans'] : ''; 
        $is_corr = $data['is_correct'] ? 1 : 0;
        
        $stmt_ans->bind_param("iisi", $history_id, $q_id, $u_a, $is_corr);
        $stmt_ans->execute();
    }
    $stmt_ans->close();
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thi - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; margin: 0; padding: 40px 20px; color: #1e293b; }
        .container { max-width: 800px; margin: 0 auto; }
        
        /* Bảng điểm tổng kết */
        .score-board { background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); text-align: center; margin-bottom: 40px; }
        .score-circle { width: 150px; height: 150px; border-radius: 50%; border: 8px solid #3b82f6; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px; font-size: 2.5rem; font-weight: 800; color: #3b82f6; background: #eff6ff; }
        .stats-row { display: flex; justify-content: center; gap: 30px; margin-top: 20px; }
        .stat-item { padding: 10px 20px; background: #f1f5f9; border-radius: 8px; font-weight: 600; }
        
        /* Review Từng câu */
        .review-card { background: #fff; padding: 25px; border-radius: 12px; margin-bottom: 20px; border-left: 5px solid #cbd5e0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .review-card.correct { border-left-color: #10b981; }
        .review-card.wrong { border-left-color: #ef4444; }
        
        .r-title { font-weight: 600; margin-bottom: 15px; font-size: 1.1rem; }
        .r-options { display: flex; flex-direction: column; gap: 10px; margin-bottom: 15px; }
        .r-opt { padding: 12px; border-radius: 6px; background: #f8fafc; border: 1px solid #e2e8f0; }
        
        /* Màu sắc cho đáp án */
        .r-opt.is-correct { background: #d1fae5; border-color: #34d399; font-weight: 600; color: #065f46; }
        .r-opt.is-user-wrong { background: #fee2e2; border-color: #f87171; text-decoration: line-through; color: #991b1b; }
        
        .btn-home { display: inline-block; padding: 15px 30px; background: #1e293b; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 700; transition: 0.2s; margin-top: 20px; }
        .btn-home:hover { background: #0f172a; }
    </style>
</head>
<body>

<div class="container">
    
    <div class="score-board">
        <h2 style="margin-top:0; color: #64748b; font-weight: 600;">KẾT QUẢ BÀI LÀM</h2>
        <h3 style="font-size: 1.5rem; margin-bottom: 30px;"><?php echo htmlspecialchars($quiz['title']); ?></h3>
        
        <?php if ($quiz['has_answers'] == 0 || $is_essay_quiz): ?>
            <div style="font-size: 4rem; color: #10b981; margin-bottom: 20px;"><i class="fas fa-check-circle"></i></div>
            <h2 style="color: #10b981;">Đã nộp bài thành công!</h2>
            <p style="color: #64748b;">Đây là dạng đề tự luận hoặc chưa cấu hình đáp án tự động. Giảng viên sẽ chấm điểm thủ công bài làm của bạn.</p>
        <?php else: ?>
            <div class="score-circle">
                <?php echo $score; ?>
            </div>
            
            <div class="stats-row">
                <div class="stat-item" style="color: #10b981;"><i class="fas fa-check"></i> Đúng: <?php echo $correct_count; ?>/<?php echo $total_q; ?></div>
                <div class="stat-item" style="color: #ef4444;"><i class="fas fa-times"></i> Sai: <?php echo $total_q - $correct_count; ?>/<?php echo $total_q; ?></div>
            </div>
        <?php endif; ?>

        <a href="../../home.php" class="btn-home"><i class="fas fa-arrow-left"></i> Về Bảng điều khiển</a>
    </div>

    <?php if ($quiz['has_answers'] == 1 && !$is_essay_quiz): ?>
        <h3 style="border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px;">Chi tiết bài làm</h3>
        
        <?php 
        $count = 1;
        foreach ($review_data as $data): 
            $q = $data['question'];
            $status_class = $data['is_correct'] ? 'correct' : 'wrong';
            $status_icon = $data['is_correct'] ? '<i class="fas fa-check-circle" style="color: #10b981;"></i>' : '<i class="fas fa-times-circle" style="color: #ef4444;"></i>';
        ?>
            <div class="review-card <?php echo $status_class; ?>">
                <div class="r-title">
                    Câu <?php echo $count; ?>: <?php echo htmlspecialchars($q['content']); ?> <?php echo $status_icon; ?>
                </div>
                
                <div class="r-options">
                    <?php 
                    $options = ['A' => $q['opt_a'], 'B' => $q['opt_b'], 'C' => $q['opt_c'], 'D' => $q['opt_d']];
                    foreach ($options as $key => $text):
                        $css_class = 'r-opt';
                        
                        // Đánh dấu đáp án ĐÚNG của hệ thống
                        if ($key === $q['correct_opt']) {
                            $css_class .= ' is-correct';
                        }
                        // Đánh dấu đáp án SAI mà người dùng lỡ chọn
                        elseif ($key === $data['user_ans'] && $data['user_ans'] !== $q['correct_opt']) {
                            $css_class .= ' is-user-wrong';
                        }
                    ?>
                        <div class="<?php echo $css_class; ?>">
                            <strong><?php echo $key; ?>.</strong> <?php echo htmlspecialchars($text); ?>
                            <?php if ($key === $data['user_ans']) echo "<span style='float:right; font-size:0.8rem;'>(Bạn chọn)</span>"; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php $count++; endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>