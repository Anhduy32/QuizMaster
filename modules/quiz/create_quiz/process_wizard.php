<?php
session_start();
<<<<<<< HEAD

// ==========================================
// ĐƯỜNG DẪN CHÍNH XÁC
// File hiện tại: modules/quiz/create_quiz/process_wizard.php
// ==========================================

// Đi lên 3 cấp: create_quiz -> quiz -> modules -> WebTaoBoDeTuDong
include '../../../config/database.php';
require '../../../vendor/autoload.php';

ini_set('memory_limit', '2048M'); 
set_time_limit(300); 
=======
include '../../../config/database.php';
require '../../../vendor/autoload.php'; // Load thư viện PDF
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8

if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
<<<<<<< HEAD
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
=======
    // Lấy tất cả dữ liệu từ Form Bước 1 -> Bước 4
    $creator_username = $_SESSION['username'];
    $title = trim($_POST['title']);
    $subject = ($_POST['subject'] === 'other') ? trim($_POST['custom_subject']) : $_POST['subject'];
    $target_audience = $_POST['target_audience'] ?? 'hoc_sinh';
    $major = $_POST['major'] ?? NULL;
    $input_method = $_POST['input_method'] ?? 'manual';
    $has_answers = (int)($_POST['has_answers'] ?? 0);
    
    // Đặt trạng thái dựa theo phương thức nhập
    $status = ($input_method === 'manual') ? 'draft' : 'completed';

    // ==========================================
    // 1. KHỞI TẠO ĐỀ THI MỚI VÀO DATABASE
    // ==========================================
    $query = "INSERT INTO quizzes (title, subject, creator_username, target_audience, major, has_answers, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssis", $title, $subject, $creator_username, $target_audience, $major, $has_answers, $status);
    
    if (!$stmt->execute()) {
        die("Lỗi cơ sở dữ liệu: Không thể khởi tạo đề thi.");
    }
    $quiz_id = $stmt->insert_id;

    // ==========================================
    // 2. ĐIỀU HƯỚNG THEO PHƯƠNG THỨC NHẬP LIỆU
    // ==========================================
    if ($input_method === 'manual') {
        header("Location: step2_add_questions.php?quiz_id=" . $quiz_id);
        exit();
    } 
    else if ($input_method === 'bank') {
        // [C] TỰ ĐỘNG TẠO TỪ NGÂN HÀNG (LOCAL API)
        $count_easy = isset($_POST['count_easy']) ? (int)$_POST['count_easy'] : 0;
        $count_medium = isset($_POST['count_medium']) ? (int)$_POST['count_medium'] : 0;
        $count_hard = isset($_POST['count_hard']) ? (int)$_POST['count_hard'] : 0;
        
        $total_requested = $count_easy + $count_medium + $count_hard;
        if ($total_requested == 0) {
            $conn->query("DELETE FROM quizzes WHERE id = $quiz_id");
            die("Lỗi: Số lượng câu hỏi phải lớn hơn 0.");
        }

        $inserted_count = 0;

        // Hàm hỗ trợ bốc ngẫu nhiên theo độ khó
        function fetchAndInsertRandom($conn, $quiz_id, $subject, $difficulty, $limit, &$inserted_count) {
            if ($limit <= 0) return;
            // Tìm các câu hỏi từ các đề thi khác có cùng môn học và thuộc public (completed)
            $query = "SELECT q.content, q.opt_a, q.opt_b, q.opt_c, q.opt_d, q.correct_opt 
                      FROM questions q 
                      JOIN quizzes qz ON q.quiz_id = qz.id 
                      WHERE qz.subject = ? AND q.difficulty = ? AND qz.status = 'completed'
                      ORDER BY RAND() LIMIT ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $subject, $difficulty, $limit);
            $stmt->execute();
            $results = $stmt->get_result();

            // Insert câu hỏi tìm được vào đề thi mới
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

        // Thực thi bốc câu hỏi
        fetchAndInsertRandom($conn, $quiz_id, $subject, 'easy', $count_easy, $inserted_count);
        fetchAndInsertRandom($conn, $quiz_id, $subject, 'medium', $count_medium, $inserted_count);
        fetchAndInsertRandom($conn, $quiz_id, $subject, 'hard', $count_hard, $inserted_count);

        // Cập nhật lại tổng số câu hỏi thực tế (đề phòng kho không đủ số lượng yêu cầu)
        $conn->query("UPDATE quizzes SET num_questions = $inserted_count, status = 'completed' WHERE id = $quiz_id");

        // Chuyển hướng đến màn hình chi tiết đề thi để xem lại và thi luôn
        header("Location: quiz_detail.php?id=" . $quiz_id);
        exit();
    }
    else if ($input_method === 'upload') {
        // [B] NẾU TẢI FILE -> Tiến hành xử lý bóc tách PDF
        if (!isset($_FILES['quiz_file']) || $_FILES['quiz_file']['error'] !== UPLOAD_ERR_OK) {
            $conn->query("DELETE FROM quizzes WHERE id = $quiz_id");
            die("Lỗi: Không có file nào được tải lên hoặc dung lượng file quá lớn.");
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
        }

        $file_name = $_FILES['quiz_file']['name'];
        $tmp_name = $_FILES['quiz_file']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
<<<<<<< HEAD

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
=======
        $raw_text = "";

        try {
            if ($ext === 'pdf') {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($tmp_name);
                $raw_text = $pdf->getText();
            } else {
                $conn->query("DELETE FROM quizzes WHERE id = $quiz_id");
                die("Rất tiếc! Hiện tại hệ thống chỉ mới hỗ trợ định dạng .pdf.");
            }

            // ==========================================
            // BỘ LỌC CẢNH BÁO (SAFETY CHECK): Ký tự gây lỗi
            // ==========================================
            // Các ký tự này thường do MathType sinh ra khi chuyển từ Word sang PDF
            $complex_math_pattern = '/[∑∫∂∞≈≠≤≥]/u';
            
            if (preg_match($complex_math_pattern, $raw_text)) {
                // Xóa đề thi vừa tạo để tránh rác DB
                $conn->query("DELETE FROM quizzes WHERE id = $quiz_id");
                
                // Hiển thị màn hình thông báo lỗi
                die("
                <!DOCTYPE html>
                <html lang='vi'>
                <head>
                    <meta charset='utf-8'>
                    <title>Cảnh báo định dạng - QuizMaster</title>
                    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap' rel='stylesheet'>
                    <style>
                        body { font-family: 'Inter', sans-serif; background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; padding: 20px; }
                        .alert-box { background: #fff; padding: 40px; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 550px; text-align: center; border-top: 6px solid #e53e3e; }
                        .alert-box h2 { color: #2d3748; margin-top: 0; font-size: 1.6rem; font-weight: 800; }
                        .alert-box p { color: #4a5568; line-height: 1.6; font-size: 1.05rem; margin-bottom: 20px; }
                        .highlight-box { background: #fff5f5; border: 1px solid #fed7d7; padding: 15px; border-radius: 12px; margin-bottom: 25px; text-align: left; }
                        .btn-back { display: inline-block; padding: 14px 28px; background: #3182ce; color: #fff; text-decoration: none; border-radius: 12px; font-weight: 600; transition: 0.2s; box-shadow: 0 4px 6px rgba(49, 130, 206, 0.2); }
                        .btn-back:hover { background: #2b6cb0; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(49, 130, 206, 0.3); }
                    </style>
                </head>
                <body>
                    <div class='alert-box'>
                        <div style='font-size: 4.5rem; color: #fc8181; margin-bottom: 20px;'>⚠️</div>
                        <h2>Phát hiện Công thức phức tạp!</h2>
                        <p>Hệ thống nhận thấy file PDF của bạn chứa nhiều ký tự công thức Toán học (MathType/Equation) rất phức tạp. Việc bóc tách tự động các ký tự này sẽ gây ra <strong>lỗi phông chữ và vỡ giao diện</strong>.</p>
                        
                        <div class='highlight-box'>
                            <strong style='color: #c53030; display: block; margin-bottom: 5px;'>💡 Giải pháp đề xuất:</strong>
                            Đối với các file tài liệu dạng này, bạn nên chọn chế độ <strong>Nhập câu hỏi thủ công</strong>, sau đó dán trực tiếp <span style='color: #3182ce; font-weight: 600;'>Link tải file (Google Drive)</span> vào phần nội dung để học viên tải về xem sẽ rõ nét và an toàn nhất!
                        </div>
                        
                        <a href='step1_create_quiz.php' class='btn-back'>Quay lại tạo đề thi</a>
                    </div>
                </body>
                </html>
                ");
            }

            // --- 2.1. DỌN DẸP NHIỄU (Tối ưu cho file nhiều Đề thi) ---
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

            // --- 2.2. TÁCH CÂU HỎI & INSERT VÀO DATABASE ---
            $questions = parseQuestions($raw_text);
            $question_count = 0;
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8

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
<<<<<<< HEAD
                
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
=======
                $conn->query("UPDATE quizzes SET num_questions = $question_count WHERE id = $quiz_id");
            }

            header("Location: success.php?quiz_id=" . $quiz_id);
            exit();

        } catch (Exception $e) {
            $conn->query("DELETE FROM quizzes WHERE id = $quiz_id");
            die("Lỗi xử lý file PDF: " . $e->getMessage());
        }
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
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
<<<<<<< HEAD
    
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
=======
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
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
<<<<<<< HEAD
            
            foreach ($option_matches as $opt) {
                $options[] = trim($opt[2]);
            }
            
=======
            foreach ($option_matches as $opt) {
                $options[] = trim($opt[2]);
            }
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
            if (preg_match('/(?:Đáp án|ĐA)\s*[:]?\s*([A-D])/i', $remaining, $ans_match)) {
                $correct = strtoupper($ans_match[1]);
            }
        }
        
        while (count($options) < 4) {
            $options[] = '';
        }
        
<<<<<<< HEAD
        if (!empty($question_text)) {
            $questions[] = [
                'content' => $question_text,
                'options' => $options,
                'correct' => $correct
            ];
        }
    }
    
=======
        $questions[] = [
            'content' => $question_text,
            'options' => $options,
            'correct' => $correct
        ];
    }
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
    return $questions;
}
?>