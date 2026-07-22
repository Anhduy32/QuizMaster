<?php
session_start();
$root_path = dirname(__DIR__, 2);
include $root_path . '/config/database.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $quiz_id = (int)$_GET['id'];
    $username = $_SESSION['username'];

    // 1. Kiểm tra quyền sở hữu & lấy thông tin file PDF (nếu có)
    $stmt = $conn->prepare("SELECT creator_username, file_path, quiz_type FROM quizzes WHERE id = ?");
    $stmt->bind_param('i', $quiz_id);
    $stmt->execute();
    $quiz = $stmt->get_result()->fetch_assoc();

    if ($quiz && $quiz['creator_username'] === $username) {
        
        // 2. Nếu là đề PDF, xóa file vật lý trong thư mục uploads/pdfs/
        if (!empty($quiz['file_path'])) {
            $physical_file_path = $root_path . '/' . ltrim($quiz['file_path'], '/');
            if (file_exists($physical_file_path)) {
                @unlink($physical_file_path); // Xóa file thực tế trên ổ cứng
            }
        }

        // 3. Sử dụng Transaction để xóa sạch dữ liệu liên quan trong DB
        $conn->begin_transaction();
        try {
            // Xóa các câu hỏi liên quan (nếu là đề trắc nghiệm)
            $del_q = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
            $del_q->bind_param('i', $quiz_id);
            $del_q->execute();

            // Xóa đề thi
            $del_quiz = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
            $del_quiz->bind_param('i', $quiz_id);
            $del_quiz->execute();

            $conn->commit();
            $_SESSION['success_message'] = 'Đã xóa đề thi và tập tin liên quan thành công!';
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = 'Lỗi hệ thống: Không thể xóa đề thi.';
        }
    } else {
        $_SESSION['error_message'] = 'Bạn không có quyền xóa đề thi này hoặc đề không tồn tại.';
    }

    // Điều hướng tương đối về thư viện
    header('Location: my_library.php');
    exit();
} else {
    header('Location: my_library.php');
    exit();
}
?>