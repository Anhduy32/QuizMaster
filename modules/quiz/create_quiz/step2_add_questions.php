<?php
session_start();
include '../../../config/database.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$loi = '';
$thong_bao = '';

// 1. Kiểm tra quyền truy cập
$check_quiz = $conn->prepare("SELECT title, num_questions, status FROM quizzes WHERE id = ? AND creator_username = ?");
$check_quiz->bind_param("is", $quiz_id, $_SESSION['username']);
$check_quiz->execute();
$quiz_result = $check_quiz->get_result();

if ($quiz_result->num_rows == 0) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>Đề thi không tồn tại hoặc bạn không có quyền chỉnh sửa! <a href='../../../home.php'>Về trang chủ</a></div>");
}
$quiz_info = $quiz_result->fetch_assoc();

// 2. Xử lý XÓA câu hỏi (Đặt lên trước để danh sách cập nhật ngay)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_question') {
    $question_id = (int)$_POST['question_id'];
    $conn->query("DELETE FROM questions WHERE id = $question_id AND quiz_id = $quiz_id");
    $conn->query("UPDATE quizzes SET num_questions = num_questions - 1 WHERE id = $quiz_id");
    $thong_bao = "Đã xóa câu hỏi thành công!";
}

// 3. Xử lý THÊM câu hỏi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_question') {
    $question_text = trim($_POST['question_text']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = $_POST['correct_option'];
    $difficulty = $_POST['difficulty'] ?? 'medium';

    if (!empty($question_text) && !empty($option_a) && !empty($option_b)) {
        $insert_q = "INSERT INTO questions (quiz_id, content, opt_a, opt_b, opt_c, opt_d, correct_opt, difficulty, status) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
        $stmt_q = $conn->prepare($insert_q);
        $stmt_q->bind_param("isssssss", $quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option, $difficulty);
        
        if ($stmt_q->execute()) {
            $conn->query("UPDATE quizzes SET num_questions = num_questions + 1 WHERE id = $quiz_id");
            $thong_bao = "Đã thêm câu hỏi thành công!";
            $_POST = array(); // Reset form
        } else {
            $loi = "Lỗi khi lưu câu hỏi. Vui lòng thử lại!";
        }
    } else {
        $loi = "Vui lòng nhập đủ nội dung câu hỏi và ít nhất 2 đáp án.";
    }
}

// 4. Xử lý HOÀN TẤT đề thi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'finish_quiz') {
    $conn->query("UPDATE quizzes SET status = 'completed' WHERE id = $quiz_id");
    header("Location: success.php?quiz_id=" . $quiz_id);
    exit();
}

// 5. Lấy danh sách câu hỏi (Sắp xếp DESC để câu mới thêm hiện lên đầu)
$list_q = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY id DESC");
$total_questions = $list_q->num_rows;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Câu Hỏi - QuizMaster</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- MathJax cho hiển thị công thức toán -->
    <script>
        MathJax = {
            tex: { inlineMath: [['$', '$'], ['\\(', '\\)']], displayMath: [['$$', '$$'], ['\\[', '\\]']] },
            svg: { fontCache: 'global' }
        };
    </script>
    <script type="text/javascript" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"></script>
    
    <style>
        /* ============================================================
           RESET & ROOT VARIABLES
           ============================================================ */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary-teal: #0f5c6b;
            --primary-hover: #0a4a56;
            --accent-teal: #2c9c8c;
            --text-main: #1a202c;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-shadow: 0 8px 32px rgba(15, 92, 107, 0.08);
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 12px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #eef7f9 0%, #daecef 100%);
            min-height: 100vh;
            padding: 30px 20px;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        /* HEADER */
        .page-header {
            background: var(--glass-bg); backdrop-filter: blur(20px);
            border-radius: var(--radius-lg); padding: 28px 35px; margin-bottom: 30px;
            box-shadow: var(--glass-shadow); border: 1px solid rgba(255, 255, 255, 0.5);
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;
        }
        .page-header .left { display: flex; align-items: center; gap: 16px; }
        .page-header .left .icon-wrapper {
            width: 50px; height: 50px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-teal), var(--accent-teal));
            display: flex; align-items: center; justify-content: center; color: white; font-size: 1.3rem;
        }
        .page-header .left .info h2 { font-size: 1.4rem; font-weight: 800; color: var(--text-main); margin: 0; }
        .page-header .left .info p { color: var(--text-muted); font-size: 0.9rem; margin: 2px 0 0 0; }
        
        .page-header .right { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .badge { padding: 6px 16px; border-radius: 40px; font-size: 0.8rem; font-weight: 700; }
        .badge-questions { background: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8; }
        .badge-status { background: #feebc8; color: #c05621; border: 1px solid #fde68a; }
        .badge-status.completed { background: #c6f6d5; color: #276749; border: 1px solid #a7f3d0; }

        /* LAYOUT & CARDS */
        .layout-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        @media (max-width: 992px) { .layout-grid { grid-template-columns: 1fr; } }

        .card {
            background: var(--glass-bg); backdrop-filter: blur(20px); border-radius: var(--radius-lg);
            padding: 30px 35px; box-shadow: var(--glass-shadow); border: 1px solid rgba(255, 255, 255, 0.5);
            transition: var(--transition-smooth);
        }
        .card-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid rgba(15, 92, 107, 0.06);
        }
        .card-header .title { font-size: 1.1rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 10px; }
        .card-header .title i { color: var(--primary-teal); }
        .card-header .count { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); background: rgba(15, 92, 107, 0.06); padding: 4px 14px; border-radius: 40px; }

        /* FORM CONTROLS */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; font-size: 0.9rem; color: var(--text-main); margin-bottom: 6px; }
        .form-group label .required { color: #e53e3e; margin-left: 2px; }
        .form-group label .hint { font-weight: 400; color: var(--text-muted); font-size: 0.8rem; }
        .form-control {
            width: 100%; padding: 12px 16px; border: 2px solid rgba(15, 92, 107, 0.08); border-radius: var(--radius-sm);
            font-size: 0.95rem; font-family: 'Inter', sans-serif; outline: none; transition: var(--transition-smooth);
            background: rgba(255, 255, 255, 0.6); color: var(--text-main);
        }
        .form-control:focus { border-color: var(--primary-teal); background: white; box-shadow: 0 0 0 4px rgba(15, 92, 107, 0.06); }
        textarea.form-control { resize: vertical; min-height: 80px; }

        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 600px) { .options-grid { grid-template-columns: 1fr; } }
        .option-item { position: relative; }
        .option-item .option-label { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--text-muted); font-size: 0.9rem; pointer-events: none; }
        .option-item .form-control { padding-left: 38px; }

        /* RADIO BUTTONS */
        .correct-select { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 4px; }
        @media (max-width: 500px) { .correct-select { grid-template-columns: repeat(2, 1fr); } }
        .difficulty-select { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 4px; }
        
        .correct-select label, .difficulty-select label {
            display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 12px;
            background: rgba(255, 255, 255, 0.5); border: 2px solid rgba(15, 92, 107, 0.06);
            border-radius: var(--radius-sm); cursor: pointer; transition: var(--transition-smooth);
            font-weight: 600; font-size: 0.9rem; color: var(--text-muted); margin: 0;
        }
        .correct-select input[type="radio"], .difficulty-select input[type="radio"] { display: none; }
        .correct-select input[type="radio"]:checked + label { border-color: #38a169; background: #f0fff4; color: #276749; }
        .difficulty-select input[type="radio"]:checked + label { border-color: var(--primary-teal); background: white; color: var(--text-main); }
        
        .correct-select input[type="radio"]:checked + label .check-icon { display: inline; }
        .correct-select label .check-icon { display: none; color: #38a169; }
        .difficulty-select label .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .dot-easy { background: #48bb78; } .dot-medium { background: #ed8936; } .dot-hard { background: #fc8181; }

        /* BUTTONS & ALERTS */
        .btn { padding: 12px 28px; border-radius: var(--radius-sm); font-weight: 700; font-size: 0.95rem; font-family: 'Inter', sans-serif; cursor: pointer; border: none; transition: var(--transition-smooth); display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, var(--primary-teal), var(--accent-teal)); color: white; width: 100%; justify-content: center; }
        .btn-success { background: linear-gradient(135deg, #059669, #10b981); color: white; }
        .btn-outline { background: rgba(255, 255, 255, 0.5); color: var(--text-muted); border: 2px solid rgba(15, 92, 107, 0.06); }
        .btn-danger-outline { background: rgba(255, 255, 255, 0.5); color: #e53e3e; border: 2px solid rgba(229, 62, 62, 0.15); padding: 6px 14px; font-size: 0.8rem; }
        .btn:hover { transform: translateY(-2px); }
        
        .alert { padding: 14px 20px; border-radius: var(--radius-sm); margin-bottom: 20px; display: flex; align-items: center; gap: 12px; font-weight: 600; }
        .alert-success { background: #f0fff4; color: #276749; border: 1px solid #a7f3d0; }
        .alert-error { background: #fff5f5; color: #9b2c2c; border: 1px solid #fecaca; }

        /* QUESTION LIST */
        .question-list { max-height: 600px; overflow-y: auto; padding-right: 8px; }
        .question-list::-webkit-scrollbar { width: 6px; }
        .question-list::-webkit-scrollbar-thumb { background: rgba(15, 92, 107, 0.15); border-radius: 10px; }
        
        .question-item { background: rgba(255, 255, 255, 0.6); border: 1px solid rgba(15, 92, 107, 0.06); border-radius: var(--radius-sm); padding: 16px 18px; margin-bottom: 12px; transition: var(--transition-smooth); }
        .question-item:hover { background: white; border-color: rgba(15, 92, 107, 0.12); box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .q-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .q-number { font-weight: 700; color: var(--text-main); font-size: 0.95rem; }
        .q-difficulty { font-size: 0.7rem; font-weight: 700; padding: 2px 12px; border-radius: 40px; text-transform: uppercase; }
        .q-difficulty.easy { background: #c6f6d5; color: #276749; } .q-difficulty.medium { background: #feebc8; color: #c05621; } .q-difficulty.hard { background: #fed7d7; color: #c53030; }
        
        .q-content { font-size: 0.9rem; color: var(--text-main); white-space: pre-wrap; word-wrap: break-word; line-height: 1.6; margin-bottom: 8px; }
        .q-options { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 16px; font-size: 0.85rem; color: var(--text-muted); }
        @media (max-width: 600px) { .q-options { grid-template-columns: 1fr; } }
        .q-options .opt .correct-badge { color: #38a169; font-weight: 700; font-size: 0.7rem; }
        .q-footer { margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(15, 92, 107, 0.04); display: flex; justify-content: flex-end; }

        .empty-state { text-align: center; padding: 40px 20px; }
        .empty-state i { font-size: 3rem; color: #cbd5e0; margin-bottom: 12px; display: block; }
    </style>
</head>
<body>

<div class="container">
    <!-- HEADER -->
    <div class="page-header">
        <div class="left">
            <div class="icon-wrapper"><i class="fas fa-plus-circle"></i></div>
            <div class="info">
                <h2>Nhập liệu câu hỏi</h2>
                <p>Đề thi: <strong><?php echo htmlspecialchars($quiz_info['title']); ?></strong></p>
            </div>
        </div>
        <div class="right">
            <span class="badge badge-questions"><i class="fas fa-list"></i> <?php echo $total_questions; ?> câu hỏi</span>
            <span class="badge badge-status <?php echo $quiz_info['status'] === 'completed' ? 'completed' : ''; ?>">
                <i class="fas <?php echo $quiz_info['status'] === 'completed' ? 'fa-check-circle' : 'fa-pen'; ?>"></i>
                <?php echo $quiz_info['status'] === 'completed' ? 'Đã xuất bản' : 'Bản nháp'; ?>
            </span>
            <?php if ($total_questions > 0 && $quiz_info['status'] !== 'completed'): ?>
            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn hoàn tất bộ đề này?');">
                <input type="hidden" name="action" value="finish_quiz">
                <button type="submit" class="btn btn-success" style="padding: 8px 20px; font-size: 0.85rem;">
                    <i class="fas fa-check-double"></i> Xuất bản
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- LAYOUT GRID -->
    <div class="layout-grid">
        <!-- LEFT: FORM THÊM CÂU HỎI -->
        <div class="card">
            <div class="card-header">
                <div class="title"><i class="fas fa-plus-circle"></i> Thêm câu hỏi mới</div>
                <span class="count">#<?php echo $total_questions + 1; ?></span>
            </div>

            <?php if(!empty($loi)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><span><?php echo $loi; ?></span></div>
            <?php endif; ?>

            <?php if(!empty($thong_bao)): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?php echo $thong_bao; ?></span></div>
            <?php endif; ?>

            <form method="POST" action="" id="addQuestionForm">
                <input type="hidden" name="action" value="add_question">

                <div class="form-group">
                    <label for="question_text">Nội dung câu hỏi <span class="required">*</span></label>
                    <textarea id="question_text" name="question_text" class="form-control" rows="4" placeholder="Nhập nội dung câu hỏi..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Các đáp án lựa chọn <span class="required">*</span></label>
                    <div class="options-grid">
                        <div class="option-item"><span class="option-label">A</span><input type="text" name="option_a" class="form-control" required></div>
                        <div class="option-item"><span class="option-label">B</span><input type="text" name="option_b" class="form-control" required></div>
                        <div class="option-item"><span class="option-label">C</span><input type="text" name="option_c" class="form-control"></div>
                        <div class="option-item"><span class="option-label">D</span><input type="text" name="option_d" class="form-control"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Chọn đáp án ĐÚNG <span class="required">*</span></label>
                    <div class="correct-select">
                        <input type="radio" id="ans_a" name="correct_option" value="A" checked>
                        <label for="ans_a"><span class="check-icon">✅</span> A</label>
                        
                        <input type="radio" id="ans_b" name="correct_option" value="B">
                        <label for="ans_b"><span class="check-icon">✅</span> B</label>
                        
                        <input type="radio" id="ans_c" name="correct_option" value="C">
                        <label for="ans_c"><span class="check-icon">✅</span> C</label>
                        
                        <input type="radio" id="ans_d" name="correct_option" value="D">
                        <label for="ans_d"><span class="check-icon">✅</span> D</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Độ khó <span class="hint">(tùy chọn)</span></label>
                    <div class="difficulty-select">
                        <input type="radio" id="diff_easy" name="difficulty" value="easy">
                        <label for="diff_easy"><span class="dot dot-easy"></span> Dễ</label>
                        
                        <input type="radio" id="diff_medium" name="difficulty" value="medium" checked>
                        <label for="diff_medium"><span class="dot dot-medium"></span> Trung bình</label>
                        
                        <input type="radio" id="diff_hard" name="difficulty" value="hard">
                        <label for="diff_hard"><span class="dot dot-hard"></span> Khó</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu câu hỏi & Thêm tiếp</button>
            </form>
        </div>

        <!-- RIGHT: DANH SÁCH CÂU HỎI -->
        <div class="card">
            <div class="card-header">
                <div class="title"><i class="fas fa-list-ul"></i> Danh sách câu hỏi</div>
                <span class="count"><?php echo $total_questions; ?> câu</span>
            </div>

            <div class="question-list">
                <?php if ($total_questions > 0): ?>
                    <?php 
                    // Vì chúng ta dùng ORDER BY id DESC, câu mới nhất lên đầu. Nên đếm ngược $i.
                    $i = $total_questions; 
                    while($q = $list_q->fetch_assoc()): 
                    ?>
                    <div class="question-item">
                        <div class="q-header">
                            <span class="q-number">Câu <?php echo $i; ?></span>
                            <span class="q-difficulty <?php echo htmlspecialchars($q['difficulty'] ?? 'medium'); ?>">
                                <?php 
                                    $diff_labels = ['easy' => 'Dễ', 'medium' => 'Trung bình', 'hard' => 'Khó'];
                                    echo $diff_labels[$q['difficulty'] ?? 'medium']; 
                                ?>
                            </span>
                        </div>
                        <div class="q-content"><?php echo htmlspecialchars($q['content']); ?></div>
                        <div class="q-options">
                            <span class="opt"><strong>A.</strong> <?php echo htmlspecialchars($q['opt_a']); ?> <?php if($q['correct_opt']=='A') echo '<span class="correct-badge">✓ Đúng</span>'; ?></span>
                            <span class="opt"><strong>B.</strong> <?php echo htmlspecialchars($q['opt_b']); ?> <?php if($q['correct_opt']=='B') echo '<span class="correct-badge">✓ Đúng</span>'; ?></span>
                            <span class="opt"><strong>C.</strong> <?php echo htmlspecialchars($q['opt_c']); ?> <?php if($q['correct_opt']=='C') echo '<span class="correct-badge">✓ Đúng</span>'; ?></span>
                            <span class="opt"><strong>D.</strong> <?php echo htmlspecialchars($q['opt_d']); ?> <?php if($q['correct_opt']=='D') echo '<span class="correct-badge">✓ Đúng</span>'; ?></span>
                        </div>
                        <div class="q-footer">
                            <form method="POST" action="" onsubmit="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?');">
                                <input type="hidden" name="action" value="delete_question">
                                <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                                <button type="submit" class="btn btn-danger-outline"><i class="fas fa-trash"></i> Xóa</button>
                            </form>
                        </div>
                    </div>
                    <?php $i--; endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h4>Chưa có câu hỏi nào</h4>
                        <p>Hãy thêm câu hỏi đầu tiên bằng form bên cạnh.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Nút điều hướng dưới cùng -->
            <div style="margin-top: 20px; padding-top: 16px; border-top: 2px solid rgba(15, 92, 107, 0.06); display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="step1_create_quiz.php" class="btn btn-outline" style="flex: 1; justify-content: center;">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <?php if ($total_questions > 0 && $quiz_info['status'] !== 'completed'): ?>
                <form method="POST" action="" style="flex: 1; display: flex;" onsubmit="return confirm('Bạn có chắc chắn muốn hoàn tất bộ đề này?');">
                    <input type="hidden" name="action" value="finish_quiz">
                    <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center;">
                        <i class="fas fa-check-double"></i> Hoàn tất
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tự động focus vào textarea
        const textarea = document.getElementById('question_text');
        if (textarea) textarea.focus();

        // Clear form sau khi submit thành công
        const alertSuccess = document.querySelector('.alert-success');
        if (alertSuccess) {
            setTimeout(function() {
                const form = document.getElementById('addQuestionForm');
                if (form) {
                    form.reset();
                    document.getElementById('ans_a').checked = true;
                    document.getElementById('diff_medium').checked = true;
                }
                if (textarea) textarea.focus();
            }, 1500);
        }
    });
</script>
</body>
</html>