<?php
session_start();

// ==========================================
// ĐƯỜNG DẪN CHÍNH XÁC
// File hiện tại: modules/quiz/create_quiz/process_wizard.php
// ==========================================

// Đi lên 3 cấp: create_quiz -> quiz -> modules -> WebTaoBoDeTuDong
include '../../../config/database.php';
require '../../../vendor/autoload.php';

ini_set('memory_limit', '2048M'); 
set_time_limit(300); 

if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $creator_username = $_SESSION['username'];
    $title = trim($_POST['title'] ?? '');
    $subject = $_POST['subject'] ?? '';
    $target_audience = $_POST['target_audience'] ?? '';
    $major = trim($_POST['major'] ?? '');
    $input_method = $_POST['input_method'] ?? 'manual';
    $has_answers = isset($_POST['has_answers']) ? (int)$_POST['has_answers'] : 1;
    $file_path = null;
    $raw_text = '';
    $upload_error = false;
    $error_message = '';
    $question_count = 0;
    
    // Xác định quiz_type
    $quiz_type = ($input_method === 'upload') ? 'file_based' : 'multiple_choice';

    // KIỂM TRA MÔN HỌC (Custom Subject)
    if ($subject === 'other') {
        $subject = trim($_POST['custom_subject'] ?? '');
        if (empty($subject)) {
            $_SESSION['error'] = 'Vui lòng nhập tên môn học cụ thể.';
            header("Location: step1_create_quiz.php");
            exit();
        }
    }

    // ==========================================
    // XỬ LÝ UPLOAD FILE PDF
    // ==========================================
    if ($input_method === 'upload') {
        if (!isset($_FILES['quiz_file']) || $_FILES['quiz_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Không có file đề thi nào được tải lên hoặc file quá lớn.';
            header("Location: step1_create_quiz.php");
            exit();
        }

        // Kiểm tra dung lượng file (10MB)
        if ($_FILES['quiz_file']['size'] > 10 * 1024 * 1024) {
            $_SESSION['error'] = 'Dung lượng file vượt quá 10MB.';
            header("Location: step1_create_quiz.php");
            exit();
        }

        // Đường dẫn upload: từ file hiện tại đi lên 3 cấp -> WebTaoBoDeTuDong
        $upload_dir = '../../../uploads/pdfs/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = $_FILES['quiz_file']['name'];
        $tmp_name = $_FILES['quiz_file']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            $_SESSION['error'] = 'Hệ thống chỉ chấp nhận định dạng file .pdf.';
            header("Location: step1_create_quiz.php");
            exit();
        }

        // Đổi tên ngẫu nhiên
        $new_file_name = 'quiz_' . uniqid() . '_' . time() . '.pdf';
        $destination = $upload_dir . $new_file_name;

        if (move_uploaded_file($tmp_name, $destination)) {
            $file_path = 'uploads/pdfs/' . $new_file_name;
            
            // BÓC TÁCH NỘI DUNG PDF
            try {
                if (class_exists('\Smalot\PdfParser\Parser')) {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($destination);
                    $raw_text = $pdf->getText();
                    
                    // Kiểm tra ký tự công thức phức tạp
                    $complex_math_pattern = '/[∑∫∂∞≈≠≤≥]/u';
                    
                    if (preg_match($complex_math_pattern, $raw_text)) {
                        $upload_error = true;
                        $error_message = "FILE_PDF_COMPLEX";
                    }
                    
                    // Dọn dẹp nhiễu
                    $noise_patterns = [
                        '/TRƯỜNG ĐH TÀI CHÍNH.*?\([\s\w]*KHÔNG ĐƯỢC sử dụng tài liệu\)/isu',
                        '/BỘ MÔN - TOÁN THỐNG KÊ/isu',
                        '/HẾT/isu',
                        '/Trang\s+\d+/isu',
                        '/\n{3,}/'
                    ];
                    foreach ($noise_patterns as $noise) {
                        $raw_text = preg_replace($noise, '', $raw_text);
                    }
                } else {
                    $raw_text = '';
                }
                
            } catch (Exception $e) {
                $raw_text = '';
                error_log("PDF Parse Error: " . $e->getMessage());
            }
        } else {
            $_SESSION['error'] = 'Không thể lưu file PDF vào máy chủ.';
            header("Location: step1_create_quiz.php");
            exit();
        }
    }

    // ==========================================
    // KHỞI TẠO ĐỀ THI VÀO DATABASE
    // ==========================================
    $status = ($quiz_type === 'file_based' || $upload_error) ? 'completed' : 'draft';
    
    $query = "INSERT INTO quizzes (title, subject, creator_username, target_audience, major, has_answers, quiz_type, file_path, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssisss", $title, $subject, $creator_username, $target_audience, $major, $has_answers, $quiz_type, $file_path, $status);
    
    if (!$stmt->execute()) {
        if ($file_path && file_exists('../../../' . $file_path)) {
            unlink('../../../' . $file_path);
        }
        $_SESSION['error'] = 'Lỗi cơ sở dữ liệu: ' . $conn->error;
        header("Location: step1_create_quiz.php");
        exit();
    }
    $quiz_id = $stmt->insert_id;

    // ==========================================
    // XỬ LÝ THEO PHƯƠNG THỨC NHẬP LIỆU
    // ==========================================
    
    if ($input_method === 'upload') {
        // Nếu có lỗi công thức phức tạp
        if ($upload_error) {
            // Đi lên 1 cấp: create_quiz -> quiz (vì quiz_detail.php cùng cấp với thư mục create_quiz)
            header("Location: ../quiz_detail.php?id=" . $quiz_id . "&error=complex_math");
            exit();
        }
        
        // Bóc tách câu hỏi từ PDF
        if (!empty($raw_text)) {
            $questions = parseQuestions($raw_text);

            if (!empty($questions)) {
                $insert_q = "INSERT INTO questions (quiz_id, content, opt_a, opt_b, opt_c, opt_d, correct_opt, status) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')";
                $stmt_q = $conn->prepare($insert_q);

                foreach ($questions as $q_data) {
                    $question_text = $q_data['content'];
                    $options = $q_data['options'];
                    
                    while (count($options) < 4) {
                        $options[] = '';
                    }
                    $correct = $q_data['correct'] ?? 'A';

                    $stmt_q->bind_param("issssss", 
                        $quiz_id, 
                        $question_text, 
                        $options[0], 
                        $options[1], 
                        $options[2], 
                        $options[3], 
                        $correct
                    );
                    
                    if ($stmt_q->execute()) {
                        $question_count++;
                    }
                }
                
                if ($question_count > 0) {
                    $conn->query("UPDATE quizzes SET num_questions = $question_count, status = 'completed' WHERE id = $quiz_id");
                } else {
                    $conn->query("UPDATE quizzes SET status = 'draft' WHERE id = $quiz_id");
                }
            } else {
                $conn->query("UPDATE quizzes SET status = 'draft' WHERE id = $quiz_id");
            }
        } else {
            $conn->query("UPDATE quizzes SET status = 'draft' WHERE id = $quiz_id");
        }
        
        // Đi lên 1 cấp: create_quiz -> quiz (vì quiz_detail.php cùng cấp với thư mục create_quiz)
        header("Location: ../quiz_detail.php?id=" . $quiz_id);
        exit();
    } 
    else if ($input_method === 'manual') {
        // Cùng thư mục create_quiz
        header("Location: step2_add_questions.php?quiz_id=" . $quiz_id);
        exit();
    } 
    else if ($input_method === 'bank') {
        $count_easy = isset($_POST['count_easy']) ? (int)$_POST['count_easy'] : 0;
        $count_medium = isset($_POST['count_medium']) ? (int)$_POST['count_medium'] : 0;
        $count_hard = isset($_POST['count_hard']) ? (int)$_POST['count_hard'] : 0;
        
        $total_requested = $count_easy + $count_medium + $count_hard;
        if ($total_requested == 0) {
            $conn->query("DELETE FROM quizzes WHERE id = $quiz_id");
            $_SESSION['error'] = 'Số lượng câu hỏi phải lớn hơn 0.';
            header("Location: step1_create_quiz.php");
            exit();
        }

        $inserted_count = 0;

        function fetchAndInsertRandom($conn, $quiz_id, $subject, $difficulty, $limit, &$inserted_count) {
            if ($limit <= 0) return;
            
            $query = "SELECT q.content, q.opt_a, q.opt_b, q.opt_c, q.opt_d, q.correct_opt 
                      FROM questions q 
                      JOIN quizzes qz ON q.quiz_id = qz.id 
                      WHERE qz.subject = ? AND q.difficulty = ? AND qz.status = 'completed'
                      ORDER BY RAND() LIMIT ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $subject, $difficulty, $limit);
            $stmt->execute();
            $results = $stmt->get_result();

            if ($results->num_rows > 0) {
                $insert_q = "INSERT INTO questions (quiz_id, content, opt_a, opt_b, opt_c, opt_d, correct_opt, difficulty, status) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
                $stmt_in = $conn->prepare($insert_q);
                
                while ($row = $results->fetch_assoc()) {
                    $stmt_in->bind_param("isssssss", $quiz_id, $row['content'], $row['opt_a'], $row['opt_b'], $row['opt_c'], $row['opt_d'], $row['correct_opt'], $difficulty);
                    if ($stmt_in->execute()) {
                        $inserted_count++;
                    }
                }
            }
        }

        fetchAndInsertRandom($conn, $quiz_id, $subject, 'easy', $count_easy, $inserted_count);
        fetchAndInsertRandom($conn, $quiz_id, $subject, 'medium', $count_medium, $inserted_count);
        fetchAndInsertRandom($conn, $quiz_id, $subject, 'hard', $count_hard, $inserted_count);

        $conn->query("UPDATE quizzes SET num_questions = $inserted_count, status = 'completed' WHERE id = $quiz_id");

        // Đi lên 1 cấp: create_quiz -> quiz (vì quiz_detail.php cùng cấp với thư mục create_quiz)
        header("Location: ../quiz_detail.php?id=" . $quiz_id);
        exit();
    }
} else {
    header("Location: step1_create_quiz.php");
    exit();
}

// ==========================================
// HÀM HỖ TRỢ XỬ LÝ TEXT
// ==========================================
function parseQuestions($text) {
    $questions = [];
    
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    $blocks = preg_split('/(?=Câu\s+\d+(?:\s*\([^)]+\))?[\.\:\)]?)/is', $text);
    
    foreach ($blocks as $block) {
        $block = trim($block);
        if (empty($block) || !preg_match('/^Câu\s+\d+/i', $block)) {
            continue;
        }
        
        $mcq_pattern = '/(.*?)(?=\s+(?:A\.|A\)|A\s+(?=[A-ZĐ]))|$)/is';
        preg_match($mcq_pattern, $block, $content_match);
        
        $question_text = trim($content_match[1] ?? $block);
        $remaining = trim(substr($block, strlen($question_text)));
        
        $options = [];
        $correct = 'A';
        
        if (!empty($remaining) && preg_match('/^A[\.\)]/i', $remaining)) {
            $option_pattern = '/(?:^|\s)([A-D])[\.\)]\s*([^A-D]*?)(?=\s*[A-D]\.|\s*[A-D]\)|$)/is';
            preg_match_all($option_pattern, $remaining, $option_matches, PREG_SET_ORDER);
            
            foreach ($option_matches as $opt) {
                $options[] = trim($opt[2]);
            }
            
            if (preg_match('/(?:Đáp án|ĐA)\s*[:]?\s*([A-D])/i', $remaining, $ans_match)) {
                $correct = strtoupper($ans_match[1]);
            }
        }
        
        while (count($options) < 4) {
            $options[] = '';
        }
        
        if (!empty($question_text)) {
            $questions[] = [
                'content' => $question_text,
                'options' => $options,
                'correct' => $correct
            ];
        }
    }
    
    return $questions;
}
?>