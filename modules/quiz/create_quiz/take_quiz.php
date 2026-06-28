<?php
session_start();
include '../../../config/database.php';

// Yêu cầu đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: ../login/login.php");
    exit();
}

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin đề thi
$query_quiz = "SELECT * FROM quizzes WHERE id = ?";
$stmt_quiz = $conn->prepare($query_quiz);
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$quiz = $stmt_quiz->get_result()->fetch_assoc();

if (!$quiz) {
    die("<div style='text-align:center; padding:50px;'>Đề thi không tồn tại! <a href='../../index.php'>Về trang chủ</a></div>");
}

// Lấy danh sách câu hỏi
$query_questions = "SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC";
$stmt_q = $conn->prepare($query_questions);
$stmt_q->bind_param("i", $quiz_id);
$stmt_q->execute();
$questions = $stmt_q->get_result();
$total_questions = $questions->num_rows;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Làm bài: <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; margin: 0; padding: 0; color: #334155; }
        .navbar { background: #fff; padding: 15px 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .navbar h2 { margin: 0; font-size: 1.2rem; color: #0f172a; }
        
        .container { max-width: 1000px; margin: 30px auto; display: grid; grid-template-columns: 1fr 300px; gap: 30px; padding: 0 20px; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
        
        .question-card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 25px; border: 1px solid #e2e8f0; }
        .question-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 20px; color: #1e293b; line-height: 1.6; }
        
        .options-group { display: flex; flex-direction: column; gap: 12px; }
        .option-label { display: flex; align-items: center; padding: 15px; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: 0.2s; font-weight: 500; }
        .option-label:hover { border-color: #cbd5e0; background: #f8fafc; }
        .option-label input[type="radio"] { margin-right: 15px; transform: scale(1.2); cursor: pointer; }
        
        /* Hiệu ứng khi chọn đáp án */
        .option-label.selected { border-color: #3b82f6; background: #eff6ff; }
        
        textarea.essay-input { width: 100%; height: 150px; padding: 15px; border: 2px solid #e2e8f0; border-radius: 8px; font-family: 'Inter', sans-serif; resize: vertical; outline: none; }
        textarea.essay-input:focus { border-color: #3b82f6; }

        .sidebar { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; position: sticky; top: 80px; height: fit-content; }
        .sidebar-title { font-weight: 700; margin-bottom: 15px; font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        .progress-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 25px; }
        .q-badge { width: 100%; aspect-ratio: 1; display: flex; justify-content: center; align-items: center; background: #f1f5f9; border-radius: 6px; font-weight: 600; font-size: 0.9rem; color: #64748b; transition: 0.2s; }
        .q-badge.done { background: #3b82f6; color: #fff; }

        .btn-submit { width: 100%; padding: 15px; background: #10b981; color: #fff; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: 0.2s; }
        .btn-submit:hover { background: #059669; }
    </style>
</head>
<body>

    <div class="navbar">
        <h2><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($quiz['title']); ?></h2>
        <a href="../../home.php" style="text-decoration: none; color: #64748b; font-weight: 500;"><i class="fas fa-times"></i> Thoát</a>
    </div>

    <form action="result.php" method="POST" id="quizForm">
        <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
        
        <div class="container">
            <div class="main-content">
                <?php 
                $q_num = 1;
                while ($q = $questions->fetch_assoc()): 
                    // Kiểm tra xem là trắc nghiệm hay tự luận (Dựa vào cột opt_b có trống hay không)
                    $is_essay = empty($q['opt_b']);
                ?>
                    <div class="question-card" id="question-<?php echo $q_num; ?>">
                        <div class="question-title">
                            <span style="color: #3b82f6;">Câu <?php echo $q_num; ?>:</span> 
                            <?php echo nl2br(htmlspecialchars($q['content'])); ?>
                        </div>
                        
                        <?php if ($is_essay): ?>
                            <textarea name="answers[<?php echo $q['id']; ?>]" class="essay-input" placeholder="Nhập câu trả lời của bạn vào đây..." oninput="markDone(<?php echo $q_num; ?>)"></textarea>
                        <?php else: ?>
                            <div class="options-group">
                                <?php foreach (['A' => 'opt_a', 'B' => 'opt_b', 'C' => 'opt_c', 'D' => 'opt_d'] as $key => $col): ?>
                                    <label class="option-label" onclick="selectRadio(this, <?php echo $q_num; ?>)">
                                        <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $key; ?>" required>
                                        <strong><?php echo $key; ?>.</strong> &nbsp; <?php echo htmlspecialchars($q[$col]); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php $q_num++; endwhile; ?>
            </div>

            <div class="sidebar">
                <div class="sidebar-title">Tiến độ làm bài</div>
                <div class="progress-grid">
                    <?php for($i = 1; $i <= $total_questions; $i++): ?>
                        <a href="#question-<?php echo $i; ?>" class="q-badge" id="badge-<?php echo $i; ?>" style="text-decoration:none;"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                
                <hr style="border: 1px solid #f1f5f9; margin: 20px 0;">
                <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 15px;"><i class="fas fa-info-circle"></i> Vui lòng kiểm tra kỹ đáp án trước khi nộp.</p>
                <button type="submit" class="btn-submit" onclick="return confirm('Bạn có chắc chắn muốn nộp bài?');">
                    Nộp Bài Ngay
                </button>
            </div>
        </div>
    </form>

    <script>
        // JS để tạo hiệu ứng chọn đáp án và đổi màu thẻ Tiến độ
        function selectRadio(labelElement, qNum) {
            // Xóa class selected của các nhãn cùng câu hỏi
            let siblings = labelElement.parentElement.querySelectorAll('.option-label');
            siblings.forEach(el => el.classList.remove('selected'));
            
            // Thêm class cho nhãn được chọn
            labelElement.classList.add('selected');
            
            // Đánh dấu thẻ Tiến độ bên phải
            markDone(qNum);
        }

        function markDone(qNum) {
            document.getElementById('badge-' + qNum).classList.add('done');
        }
    </script>
</body>
</html>