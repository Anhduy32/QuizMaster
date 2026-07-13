<?php
session_start();
include '../../../config/database.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../../login/login.php");
    exit();
}

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$loi = '';
$thong_bao = '';

$check_quiz = $conn->prepare("SELECT title FROM quizzes WHERE id = ? AND creator_username = ?");
$check_quiz->bind_param("is", $quiz_id, $_SESSION['username']);
$check_quiz->execute();
$quiz_result = $check_quiz->get_result();

if ($quiz_result->num_rows == 0) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>Đề thi không tồn tại hoặc bạn không có quyền chỉnh sửa! <a href='../../../home.php'>Về trang chủ</a></div>");
}
$quiz_info = $quiz_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_question') {
    $question_text = trim($_POST['question_text']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = $_POST['correct_option'];

    if (empty($question_text) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d) || empty($correct_option)) {
        $loi = "Vui lòng điền đầy đủ nội dung câu hỏi và các đáp án!";
    } else {
        $insert_q = "INSERT INTO questions (quiz_id, content, opt_a, opt_b, opt_c, opt_d, correct_opt, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')";
        $stmt_q = $conn->prepare($insert_q);
        $stmt_q->bind_param("issssss", $quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option);
        
        if ($stmt_q->execute()) {
            $conn->query("UPDATE quizzes SET num_questions = num_questions + 1 WHERE id = $quiz_id");
            $thong_bao = "Đã thêm câu hỏi thành công!";
        } else {
            $loi = "Lỗi khi lưu câu hỏi. Vui lòng thử lại!";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'finish_quiz') {
    $conn->query("UPDATE quizzes SET status = 'completed' WHERE id = $quiz_id");
    header("Location: success.php?quiz_id=" . $quiz_id);
    exit();
}

$list_q = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Câu Hỏi - QuizMaster</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Tích hợp MathJax Render Toán học cho Admin Panel -->
    <script>
        MathJax = {
            tex: { inlineMath: [['$', '$'], ['\\(', '\\)']], displayMath: [['$$', '$$'], ['\\[', '\\]']] },
            svg: { fontCache: 'global' }
        };
    </script>
    <script type="text/javascript" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #2d3748; }
        .page-header { max-width: 1200px; margin: 0 auto 20px; display: flex; justify-content: space-between; align-items: center; }
        .page-title h2 { margin: 0; color: #1a202c; font-size: 1.5rem; }
        .page-title p { margin: 5px 0 0 0; color: #718096; }
        
        .layout-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        @media(max-width: 768px) { .layout-grid { grid-template-columns: 1fr; } }
        
        .card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .card-header { font-size: 1.2rem; font-weight: 700; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #4a5568; }
        textarea, input[type="text"] { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-family: 'Inter', sans-serif; box-sizing: border-box; transition: 0.2s; outline: none; }
        textarea:focus, input[type="text"]:focus { border-color: #3182ce; background: #fbfdff; }
        
        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .option-item { position: relative; }
        .option-item span { position: absolute; left: 15px; top: 13px; font-weight: bold; color: #718096; }
        .option-item input[type="text"] { padding-left: 40px; }
        
        .correct-answer-select { display: flex; gap: 10px; margin-top: 10px; }
        .correct-answer-select label { flex: 1; text-align: center; background: #edf2f7; padding: 10px; border-radius: 8px; cursor: pointer; transition: 0.2s; border: 2px solid transparent; }
        .correct-answer-select input { display: none; }
        .correct-answer-select input:checked + label { background: #e6fffa; border-color: #38a169; color: #276749; }
        
        .btn { padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: #3182ce; color: #fff; width: 100%; justify-content: center; }
        .btn-primary:hover { background: #2b6cb0; }
        .btn-success { background: #38a169; color: #fff; }
        .btn-success:hover { background: #2f855a; }
        
        .question-list { max-height: 600px; overflow-y: auto; padding-right: 10px; }
        .q-item { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        .q-item h4 { margin: 0 0 10px 0; color: #2d3748; }
        .q-item .q-options { font-size: 0.9rem; color: #4a5568; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .correct-badge { background: #c6f6d5; color: #22543d; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 700; margin-left: 10px; }
        
        .alert-success { background: #c6f6d5; color: #22543d; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600; }
        .alert-error { background: #fed7d7; color: #9b2c2c; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600; }
    </style>
</head>
<body>

<div class="page-header">
    <div class="page-title">
        <h2>Nhập liệu câu hỏi</h2>
        <p>Đề thi: <strong><?php echo htmlspecialchars($quiz_info['title']); ?></strong></p>
    </div>
    <form method="POST" action="">
        <input type="hidden" name="action" value="finish_quiz">
        <button type="submit" class="btn btn-success" onclick="return confirm('Bạn có chắc chắn muốn hoàn tất bộ đề này không?');">
            <i class="fas fa-check-double"></i> Hoàn tất & Xuất bản
        </button>
    </form>
</div>

<div class="layout-grid">
    <div class="card">
        <div class="card-header">
            <span><i class="fas fa-plus-circle"></i> Thêm câu hỏi mới</span>
        </div>
        
        <?php if(!empty($loi)) echo "<div class='alert-error'>$loi</div>"; ?>
        <?php if(!empty($thong_bao)) echo "<div class='alert-success'>$thong_bao</div>"; ?>

        <form method="POST" action="">
            <input type="hidden" name="action" value="add_question">
            
            <div class="form-group">
                <label>Nội dung câu hỏi</label>
                <textarea name="question_text" rows="4" placeholder="Nhập nội dung câu hỏi vào đây..." required></textarea>
            </div>
            
            <div class="form-group">
                <label>Các đáp án lựa chọn</label>
                <div class="options-grid">
                    <div class="option-item"><span>A</span><input type="text" name="option_a" required></div>
                    <div class="option-item"><span>B</span><input type="text" name="option_b" required></div>
                    <div class="option-item"><span>C</span><input type="text" name="option_c" required></div>
                    <div class="option-item"><span>D</span><input type="text" name="option_d" required></div>
                </div>
            </div>

            <div class="form-group">
                <label>Chọn đáp án ĐÚNG</label>
                <div class="correct-answer-select">
                    <input type="radio" id="ans_a" name="correct_option" value="A" required>
                    <label for="ans_a">Đáp án A</label>
                    <input type="radio" id="ans_b" name="correct_option" value="B">
                    <label for="ans_b">Đáp án B</label>
                    <input type="radio" id="ans_c" name="correct_option" value="C">
                    <label for="ans_c">Đáp án C</label>
                    <input type="radio" id="ans_d" name="correct_option" value="D">
                    <label for="ans_d">Đáp án D</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu câu hỏi & Thêm tiếp</button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <span><i class="fas fa-list-ul"></i> Các câu đã thêm (<?php echo $list_q->num_rows; ?>)</span>
        </div>
        <div class="question-list">
            <?php if($list_q->num_rows > 0): ?>
                <?php $i = $list_q->num_rows; while($q = $list_q->fetch_assoc()): ?>
                <div class="q-item">
                    <!-- SỬ DỤNG white-space ĐỂ HIỂN THỊ ĐẸP CÔNG THỨC TOÁN & MA TRẬN -->
                    <h4 style="white-space: pre-wrap; word-wrap: break-word; font-weight: 600; line-height: 1.5;">Câu <?php echo $i; ?>: <?php echo htmlspecialchars($q['content']); ?></h4>
                    <div class="q-options" style="white-space: pre-wrap; word-wrap: break-word;">
                        <div><strong>A.</strong> <?php echo htmlspecialchars($q['opt_a']); ?> <?php if($q['correct_opt']=='A') echo "<span class='correct-badge'>Đúng</span>"; ?></div>
                        <div><strong>B.</strong> <?php echo htmlspecialchars($q['opt_b']); ?> <?php if($q['correct_opt']=='B') echo "<span class='correct-badge'>Đúng</span>"; ?></div>
                        <div><strong>C.</strong> <?php echo htmlspecialchars($q['opt_c']); ?> <?php if($q['correct_opt']=='C') echo "<span class='correct-badge'>Đúng</span>"; ?></div>
                        <div><strong>D.</strong> <?php echo htmlspecialchars($q['opt_d']); ?> <?php if($q['correct_opt']=='D') echo "<span class='correct-badge'>Đúng</span>"; ?></div>
                    </div>
                </div>
                <?php $i--; endwhile; ?>
            <?php else: ?>
                <div style="text-align:center; color:#a0aec0; padding: 40px 0;">
                    <i class="fas fa-box-open" style="font-size:3rem; margin-bottom:10px;"></i>
                    <p>Chưa có câu hỏi nào được thêm.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>