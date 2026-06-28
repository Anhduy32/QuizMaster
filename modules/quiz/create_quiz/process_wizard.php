<?php
session_start();
include '../../../config/database.php';
require '../../../vendor/autoload.php'; // Load thư viện PDF

if (!isset($_SESSION['username'])) {
    header("Location: ../../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['quiz_file'])) {
    $quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
    
    // Nếu bạn đang truyền dữ liệu trực tiếp từ step 1 sang (chưa có quiz_id), ta phải INSERT quiz mới trước
    if ($quiz_id == 0) {
        $creator_username = $_SESSION['username'];
        $title = trim($_POST['title']);
        $subject = ($_POST['subject'] === 'other') ? trim($_POST['custom_subject']) : $_POST['subject'];
        $target_audience = $_POST['target_audience'] ?? 'hoc_sinh';
        $major = $_POST['major'] ?? NULL;
        $has_answers = (int)$_POST['has_answers'];
        $status = 'completed'; // Upload file xong là hoàn thành luôn

        $query = "INSERT INTO quizzes (title, subject, creator_username, target_audience, major, has_answers, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssis", $title, $subject, $creator_username, $target_audience, $major, $has_answers, $status);
        $stmt->execute();
        $quiz_id = $stmt->insert_id;
    }

    // ==========================================
    // BẮT ĐẦU XỬ LÝ FILE ĐÍNH KÈM
    // ==========================================
    $file_name = $_FILES['quiz_file']['name'];
    $tmp_name = $_FILES['quiz_file']['tmp_name'];
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $raw_text = "";

    try {
        if ($ext === 'pdf') {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($tmp_name);
            $raw_text = $pdf->getText();
        } else {
            die("Hiện tại hệ thống chỉ mới hỗ trợ định dạng .pdf ở phiên bản này.");
        }

        // Dọn dẹp nhiễu văn bản (Xóa các dòng Header lặp lại của trường học)
        $noise_patterns = [
            '/TRƯỜNG ĐH TÀI CHÍNH.*?HẠNH PHÚC/is',
            '/ĐỀ THI : TOÁN CAO CẤP.*?tài liệu\)/is',
            '/BỘ MÔN - TOÁN THỐNG KÊ/is',
            '/HẾT/is'
        ];
        foreach ($noise_patterns as $noise) {
            $raw_text = preg_replace($noise, '', $raw_text);
        }

        // ==========================================
        // THUẬT TOÁN BÓC TÁCH THÔNG MINH (REGEX)
        // ==========================================
        // Cắt văn bản dựa trên từ khóa "Câu X" hoặc "Câu X:" hoặc "Câu X (Y điểm)"
        $pattern = '/(Câu\s+\d+.*?)(?=Câu\s+\d+|$)/is';
        preg_match_all($pattern, $raw_text, $matches);

        $question_count = 0;

        if (!empty($matches[0])) {
            $insert_q = "INSERT INTO questions (quiz_id, content, opt_a, opt_b, opt_c, opt_d, correct_opt, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')";
            $stmt_q = $conn->prepare($insert_q);

            foreach ($matches[0] as $block) {
                $block = trim($block);
                if (empty($block)) continue;

                // Cố gắng tìm các mẫu đáp án A., B., C., D. (Dành cho đề trắc nghiệm)
                $is_multiple_choice = preg_match('/A\.(.*?)B\.(.*?)C\.(.*?)D\.(.*?)$/is', $block, $opt_matches);

                if ($is_multiple_choice) {
                    // Nếu là Đề Trắc Nghiệm
                    $question_text = trim(preg_replace('/A\..*$/is', '', $block));
                    $opt_a = trim($opt_matches[1]);
                    $opt_b = trim($opt_matches[2]);
                    $opt_c = trim($opt_matches[3]);
                    $opt_d = trim($opt_matches[4]);
                    $correct = 'A'; // Mặc định nếu không có hệ thống nhận diện đáp án đỏ
                } else {
                    // Nếu là Đề Tự Luận (Như file Toán Cao Cấp của bạn)
                    $question_text = $block;
                    $opt_a = "(Đề tự luận - Học viên tự trình bày)";
                    $opt_b = "";
                    $opt_c = "";
                    $opt_d = "";
                    $correct = 'A'; // Set mặc định để không bị lỗi Database
                }

                // Tiến hành lưu vào CSDL
                $stmt_q->bind_param("issssss", $quiz_id, $question_text, $opt_a, $opt_b, $opt_c, $opt_d, $correct);
                if ($stmt_q->execute()) {
                    $question_count++;
                }
            }
            
            // Cập nhật lại tổng số câu hỏi vào bảng quizzes
            $conn->query("UPDATE quizzes SET num_questions = $question_count WHERE id = $quiz_id");
        }

        // Chuyển hướng đến trang Thành công
        header("Location: success.php?quiz_id=" . $quiz_id);
        exit();

    } catch (Exception $e) {
        die("Lỗi đọc file: " . $e->getMessage());
    }
} else {
    header("Location: step1_create_quiz.php");
    exit();
}
?>