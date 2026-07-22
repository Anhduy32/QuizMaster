<?php
session_start();
include '../../../config/database.php';

if (!isset($_SESSION['username'])) {
<<<<<<< HEAD
    header("Location: ../../auth/login.php");
=======
    header("Location: ../../login/login.php");
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
    exit();
}

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$loi = '';
$thong_bao = '';

<<<<<<< HEAD
// Kiểm tra quyền truy cập
$check_quiz = $conn->prepare("SELECT title, num_questions, status FROM quizzes WHERE id = ? AND creator_username = ?");
=======
$check_quiz = $conn->prepare("SELECT title FROM quizzes WHERE id = ? AND creator_username = ?");
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
$check_quiz->bind_param("is", $quiz_id, $_SESSION['username']);
$check_quiz->execute();
$quiz_result = $check_quiz->get_result();

if ($quiz_result->num_rows == 0) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>Đề thi không tồn tại hoặc bạn không có quyền chỉnh sửa! <a href='../../../home.php'>Về trang chủ</a></div>");
}
$quiz_info = $quiz_result->fetch_assoc();

<<<<<<< HEAD
// Xử lý thêm câu hỏi
=======
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_question') {
    $question_text = trim($_POST['question_text']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = $_POST['correct_option'];
<<<<<<< HEAD
    $difficulty = $_POST['difficulty'] ?? 'medium';
=======
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8

    if (empty($question_text) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d) || empty($correct_option)) {
        $loi = "Vui lòng điền đầy đủ nội dung câu hỏi và các đáp án!";
    } else {
<<<<<<< HEAD
        $insert_q = "INSERT INTO questions (quiz_id, content, opt_a, opt_b, opt_c, opt_d, correct_opt, difficulty, status) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
        $stmt_q = $conn->prepare($insert_q);
        $stmt_q->bind_param("isssssss", $quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option, $difficulty);
=======
        $insert_q = "INSERT INTO questions (quiz_id, content, opt_a, opt_b, opt_c, opt_d, correct_opt, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')";
        $stmt_q = $conn->prepare($insert_q);
        $stmt_q->bind_param("issssss", $quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option);
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
        
        if ($stmt_q->execute()) {
            $conn->query("UPDATE quizzes SET num_questions = num_questions + 1 WHERE id = $quiz_id");
            $thong_bao = "Đã thêm câu hỏi thành công!";
<<<<<<< HEAD
            
            // Reset form sau khi thêm thành công
            $_POST = array();
=======
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
        } else {
            $loi = "Lỗi khi lưu câu hỏi. Vui lòng thử lại!";
        }
    }
}

<<<<<<< HEAD
// Xử lý xóa câu hỏi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_question') {
    $question_id = (int)$_POST['question_id'];
    $conn->query("DELETE FROM questions WHERE id = $question_id AND quiz_id = $quiz_id");
    $conn->query("UPDATE quizzes SET num_questions = num_questions - 1 WHERE id = $quiz_id");
    $thong_bao = "Đã xóa câu hỏi thành công!";
}

// Xử lý hoàn tất đề thi
=======
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'finish_quiz') {
    $conn->query("UPDATE quizzes SET status = 'completed' WHERE id = $quiz_id");
    header("Location: success.php?quiz_id=" . $quiz_id);
    exit();
}

<<<<<<< HEAD
// Lấy danh sách câu hỏi
$list_q = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY id ASC");
$total_questions = $list_q->num_rows;
=======
$list_q = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY id DESC");
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Câu Hỏi - QuizMaster</title>
    
<<<<<<< HEAD
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- MathJax cho hiển thị công thức toán -->
=======
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Tích hợp MathJax Render Toán học cho Admin Panel -->
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
    <script>
        MathJax = {
            tex: { inlineMath: [['$', '$'], ['\\(', '\\)']], displayMath: [['$$', '$$'], ['\\[', '\\]']] },
            svg: { fontCache: 'global' }
        };
    </script>
    <script type="text/javascript" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"></script>
    
    <style>
<<<<<<< HEAD
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

        /* ============================================================
           CONTAINER
           ============================================================ */
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ============================================================
           HEADER
           ============================================================ */
        .page-header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: var(--radius-lg);
            padding: 28px 35px;
            margin-bottom: 30px;
            box-shadow: var(--glass-shadow);
            border: 1px solid rgba(255, 255, 255, 0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-header .left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .page-header .left .icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-teal), var(--accent-teal));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }

        .page-header .left .info h2 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-main);
            margin: 0;
        }

        .page-header .left .info p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin: 2px 0 0 0;
        }

        .page-header .right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .page-header .right .badge {
            padding: 6px 16px;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .badge-questions {
            background: #ebf8ff;
            color: #2b6cb0;
            border: 1px solid #bee3f8;
        }

        .badge-status {
            background: #feebc8;
            color: #c05621;
            border: 1px solid #fde68a;
        }

        .badge-status.completed {
            background: #c6f6d5;
            color: #276749;
            border: 1px solid #a7f3d0;
        }

        /* ============================================================
           LAYOUT GRID
           ============================================================ */
        .layout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        @media (max-width: 992px) {
            .layout-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ============================================================
           CARDS
           ============================================================ */
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: var(--radius-lg);
            padding: 30px 35px;
            box-shadow: var(--glass-shadow);
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: var(--transition-smooth);
        }

        .card:hover {
            box-shadow: 0 12px 48px rgba(15, 92, 107, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid rgba(15, 92, 107, 0.06);
        }

        .card-header .title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header .title i {
            color: var(--primary-teal);
        }

        .card-header .count {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            background: rgba(15, 92, 107, 0.06);
            padding: 4px 14px;
            border-radius: 40px;
        }

        /* ============================================================
           FORM
           ============================================================ */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-group label .required {
            color: #e53e3e;
            margin-left: 2px;
        }

        .form-group label .hint {
            font-weight: 400;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(15, 92, 107, 0.08);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: var(--transition-smooth);
            background: rgba(255, 255, 255, 0.6);
            color: var(--text-main);
        }

        .form-control:focus {
            border-color: var(--primary-teal);
            background: white;
            box-shadow: 0 0 0 4px rgba(15, 92, 107, 0.06);
        }

        .form-control::placeholder {
            color: #a0aec0;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* Options Grid */
        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        @media (max-width: 600px) {
            .options-grid {
                grid-template-columns: 1fr;
            }
        }

        .option-item {
            position: relative;
        }

        .option-item .option-label {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: 700;
            color: var(--text-muted);
            font-size: 0.9rem;
            pointer-events: none;
        }

        .option-item .form-control {
            padding-left: 38px;
        }

        /* Correct Answer Select */
        .correct-select {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 4px;
        }

        @media (max-width: 500px) {
            .correct-select {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .correct-select label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.5);
            border: 2px solid rgba(15, 92, 107, 0.06);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition-smooth);
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 0;
        }

        .correct-select label:hover {
            border-color: rgba(15, 92, 107, 0.15);
            background: rgba(255, 255, 255, 0.8);
        }

        .correct-select input[type="radio"] {
            display: none;
        }

        .correct-select input[type="radio"]:checked + label {
            border-color: #38a169;
            background: #f0fff4;
            color: #276749;
        }

        .correct-select input[type="radio"]:checked + label .check-icon {
            display: inline;
        }

        .correct-select label .check-icon {
            display: none;
            color: #38a169;
        }

        /* Difficulty Select */
        .difficulty-select {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 4px;
        }

        .difficulty-select label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.5);
            border: 2px solid rgba(15, 92, 107, 0.06);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition-smooth);
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
        }

        .difficulty-select label:hover {
            border-color: rgba(15, 92, 107, 0.15);
            background: rgba(255, 255, 255, 0.8);
        }

        .difficulty-select input[type="radio"] {
            display: none;
        }

        .difficulty-select input[type="radio"]:checked + label {
            border-color: var(--primary-teal);
            background: white;
            color: var(--text-main);
        }

        .difficulty-select label .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .dot-easy { background: #48bb78; }
        .dot-medium { background: #ed8936; }
        .dot-hard { background: #fc8181; }

        /* Buttons */
        .btn {
            padding: 12px 28px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            border: none;
            transition: var(--transition-smooth);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn:active {
            transform: scale(0.97);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-teal), var(--accent-teal));
            color: white;
            box-shadow: 0 4px 16px rgba(15, 92, 107, 0.25);
            width: 100%;
            justify-content: center;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(15, 92, 107, 0.35);
        }

        .btn-success {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            box-shadow: 0 4px 16px rgba(5, 150, 105, 0.25);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(5, 150, 105, 0.35);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.5);
            color: var(--text-muted);
            border: 2px solid rgba(15, 92, 107, 0.06);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.8);
            border-color: rgba(15, 92, 107, 0.15);
            color: var(--text-main);
        }

        .btn-danger-outline {
            background: rgba(255, 255, 255, 0.5);
            color: #e53e3e;
            border: 2px solid rgba(229, 62, 62, 0.15);
            padding: 6px 14px;
            font-size: 0.8rem;
        }

        .btn-danger-outline:hover {
            background: #fff5f5;
            border-color: #e53e3e;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .btn-group .btn {
            flex: 1;
            justify-content: center;
            min-width: 120px;
        }

        /* ============================================================
           ALERTS
           ============================================================ */
        .alert {
            padding: 14px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        .alert-success {
            background: #f0fff4;
            color: #276749;
            border: 1px solid #a7f3d0;
        }

        .alert-success i {
            color: #38a169;
        }

        .alert-error {
            background: #fff5f5;
            color: #9b2c2c;
            border: 1px solid #fecaca;
        }

        .alert-error i {
            color: #e53e3e;
        }

        /* ============================================================
           QUESTION LIST
           ============================================================ */
        .question-list {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .question-list::-webkit-scrollbar {
            width: 6px;
        }

        .question-list::-webkit-scrollbar-track {
            background: rgba(15, 92, 107, 0.04);
            border-radius: 10px;
        }

        .question-list::-webkit-scrollbar-thumb {
            background: rgba(15, 92, 107, 0.15);
            border-radius: 10px;
        }

        .question-item {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(15, 92, 107, 0.06);
            border-radius: var(--radius-sm);
            padding: 16px 18px;
            margin-bottom: 12px;
            transition: var(--transition-smooth);
        }

        .question-item:hover {
            background: white;
            border-color: rgba(15, 92, 107, 0.12);
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .question-item .q-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .question-item .q-header .q-number {
            font-weight: 700;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        .question-item .q-header .q-difficulty {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 12px;
            border-radius: 40px;
            text-transform: uppercase;
        }

        .q-difficulty.easy { background: #c6f6d5; color: #276749; }
        .q-difficulty.medium { background: #feebc8; color: #c05621; }
        .q-difficulty.hard { background: #fed7d7; color: #c53030; }

        .question-item .q-content {
            font-size: 0.9rem;
            color: var(--text-main);
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .question-item .q-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 16px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        @media (max-width: 600px) {
            .question-item .q-options {
                grid-template-columns: 1fr;
            }
        }

        .question-item .q-options .opt {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .question-item .q-options .opt .correct-badge {
            color: #38a169;
            font-weight: 700;
            font-size: 0.7rem;
        }

        .question-item .q-footer {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(15, 92, 107, 0.04);
            display: flex;
            justify-content: flex-end;
        }

        /* ============================================================
           EMPTY STATE
           ============================================================ */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 12px;
            display: block;
        }

        .empty-state h4 {
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 768px) {
            body { padding: 16px 12px; }
            .page-header { padding: 20px; flex-direction: column; align-items: flex-start; }
            .page-header .right { width: 100%; justify-content: flex-start; }
            .card { padding: 20px; }
            .btn-group .btn { flex: 1 1 100%; }
            .btn-group { flex-direction: column; }
        }
=======
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
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
    </style>
</head>
<body>

<<<<<<< HEAD
<div class="container">
    <!-- HEADER -->
    <div class="page-header">
        <div class="left">
            <div class="icon-wrapper">
                <i class="fas fa-plus-circle"></i>
            </div>
            <div class="info">
                <h2>Nhập liệu câu hỏi</h2>
                <p>Đề thi: <strong><?php echo htmlspecialchars($quiz_info['title']); ?></strong></p>
            </div>
        </div>
        <div class="right">
            <span class="badge badge-questions">
                <i class="fas fa-list"></i> <?php echo $total_questions; ?> câu hỏi
            </span>
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
                <div class="title">
                    <i class="fas fa-plus-circle"></i> Thêm câu hỏi mới
                </div>
                <span class="count">#<?php echo $total_questions + 1; ?></span>
            </div>

            <?php if(!empty($loi)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $loi; ?></span>
            </div>
            <?php endif; ?>

            <?php if(!empty($thong_bao)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $thong_bao; ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="addQuestionForm">
                <input type="hidden" name="action" value="add_question">

                <!-- Nội dung câu hỏi -->
                <div class="form-group">
                    <label for="question_text">Nội dung câu hỏi <span class="required">*</span></label>
                    <textarea id="question_text" name="question_text" class="form-control" rows="4" placeholder="Nhập nội dung câu hỏi vào đây..." required></textarea>
                </div>

                <!-- Các đáp án -->
                <div class="form-group">
                    <label>Các đáp án lựa chọn <span class="required">*</span></label>
                    <div class="options-grid">
                        <div class="option-item">
                            <span class="option-label">A</span>
                            <input type="text" name="option_a" class="form-control" placeholder="Đáp án A" required>
                        </div>
                        <div class="option-item">
                            <span class="option-label">B</span>
                            <input type="text" name="option_b" class="form-control" placeholder="Đáp án B" required>
                        </div>
                        <div class="option-item">
                            <span class="option-label">C</span>
                            <input type="text" name="option_c" class="form-control" placeholder="Đáp án C" required>
                        </div>
                        <div class="option-item">
                            <span class="option-label">D</span>
                            <input type="text" name="option_d" class="form-control" placeholder="Đáp án D" required>
                        </div>
                    </div>
                </div>

                <!-- Đáp án đúng -->
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

                <!-- Độ khó -->
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

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu câu hỏi & Thêm tiếp
                </button>
            </form>
        </div>

        <!-- RIGHT: DANH SÁCH CÂU HỎI -->
        <div class="card">
            <div class="card-header">
                <div class="title">
                    <i class="fas fa-list-ul"></i> Danh sách câu hỏi
                </div>
                <span class="count"><?php echo $total_questions; ?> câu</span>
            </div>

            <div class="question-list">
                <?php if ($total_questions > 0): ?>
                    <?php $i = 1; while($q = $list_q->fetch_assoc()): ?>
                    <div class="question-item">
                        <div class="q-header">
                            <span class="q-number">Câu <?php echo $i; ?></span>
                            <span class="q-difficulty <?php echo $q['difficulty'] ?? 'medium'; ?>">
                                <?php echo ucfirst($q['difficulty'] ?? 'Trung bình'); ?>
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
                                <button type="submit" class="btn btn-danger-outline">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php $i++; endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h4>Chưa có câu hỏi nào</h4>
                        <p>Hãy thêm câu hỏi đầu tiên bằng form bên cạnh.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Nút điều hướng dưới cùng -->
            <div style="margin-top: 20px; padding-top: 16px; border-top: 2px solid rgba(15, 92, 107, 0.06);">
                <div class="btn-group">
                    <a href="step1_create_quiz.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                    <?php if ($total_questions > 0 && $quiz_info['status'] !== 'completed'): ?>
                    <form method="POST" action="" style="flex:1;" onsubmit="return confirm('Bạn có chắc chắn muốn hoàn tất bộ đề này?');">
                        <input type="hidden" name="action" value="finish_quiz">
                        <button type="submit" class="btn btn-success" style="width:100%; justify-content:center;">
                            <i class="fas fa-check-double"></i> Hoàn tất & Xuất bản
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
=======
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
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
        </div>
    </div>
</div>

<<<<<<< HEAD
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tự động focus vào textarea khi trang tải
        const textarea = document.getElementById('question_text');
        if (textarea) {
            textarea.focus();
        }

        // Clear form sau khi submit thành công (thông báo success)
        const alertSuccess = document.querySelector('.alert-success');
        if (alertSuccess) {
            // Reset form sau 2 giây nếu có thông báo thành công
            setTimeout(function() {
                const form = document.getElementById('addQuestionForm');
                if (form) {
                    form.reset();
                    // Reset correct option về A
                    document.getElementById('ans_a').checked = true;
                    // Reset difficulty về medium
                    document.getElementById('diff_medium').checked = true;
                }
                // Focus lại vào textarea
                if (textarea) {
                    textarea.focus();
                }
            }, 2000);
        }
    });
</script>

=======
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
</body>
</html>