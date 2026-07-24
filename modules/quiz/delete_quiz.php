<?php
session_start();

// Thiết lập header trả về JSON để Fetch API có thể đọc được
header('Content-Type: application/json; charset=utf-8');

$root_path = dirname(__DIR__, 2);
include $root_path . '/config/database.php';

// 1. Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.']);
    exit();
}

// 2. Chuyển đổi từ GET sang POST để bảo mật (chống CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $quiz_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $username = $_SESSION['username'];

    if ($quiz_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID đề thi không hợp lệ.']);
        exit();
    }

    // 3. Kiểm tra quyền sở hữu & lấy thông tin file PDF (nếu có)
    $stmt = $conn->prepare("SELECT creator_username, file_path, quiz_type FROM quizzes WHERE id = ?");
    $stmt->bind_param('i', $quiz_id);
    $stmt->execute();
    $quiz = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($quiz && $quiz['creator_username'] === $username) {
        
        // 4. Nếu là đề PDF, xóa file vật lý trong thư mục uploads/pdfs/
        if (!empty($quiz['file_path'])) {
            $physical_file_path = $root_path . '/' . ltrim($quiz['file_path'], '/');
            if (file_exists($physical_file_path)) {
                @unlink($physical_file_path); // Xóa file thực tế trên ổ cứng
            }
        }

        // 5. Sử dụng Transaction để xóa sạch dữ liệu liên quan trong DB
        $conn->begin_transaction();
        try {
            // Xóa các câu hỏi liên quan (nếu là đề trắc nghiệm)
            $del_q = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
            $del_q->bind_param('i', $quiz_id);
            $del_q->execute();
            $del_q->close();

            // Xóa đề thi
            $del_quiz = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
            $del_quiz->bind_param('i', $quiz_id);
            $del_quiz->execute();
            $del_quiz->close();

            $conn->commit();
            
            // Trả về thông báo thành công cho JavaScript
            echo json_encode(['success' => true, 'message' => 'Đã xóa đề thi và tập tin liên quan thành công!']);
        } catch (Exception $e) {
            $conn->rollback();
            // Bắt lỗi hệ thống nhưng không văng lỗi PHP ra ngoài
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: Không thể xóa đề thi lúc này.']);
        }
    } else {
        // Sai người dùng hoặc không tìm thấy đề
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa đề thi này hoặc đề không tồn tại.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
}
?>