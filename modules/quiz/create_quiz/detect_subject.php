<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1. Kiểm tra xác thực
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Lỗi xác thực (Unauthorized).']);
    exit();
}

// 2. Kiểm tra file upload
if (!isset($_FILES['quiz_file']) || $_FILES['quiz_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy file hoặc quá trình tải lên bị lỗi.']);
    exit();
}

$file = $_FILES['quiz_file'];
$file_name = $file['name'];
$file_size = $file['size'];
$file_tmp = $file['tmp_name'];
$file_ext = mb_strtolower(pathinfo($file_name, PATHINFO_EXTENSION), 'UTF-8');

// 3. Ràng buộc an toàn
if ($file_ext !== 'pdf') {
    echo json_encode(['success' => false, 'message' => 'Chỉ hỗ trợ tự động nhận diện với định dạng PDF.']);
    exit();
}
if ($file_size > 10 * 1024 * 1024) { // 10MB
    echo json_encode(['success' => false, 'message' => 'Dung lượng file vượt quá giới hạn 10MB.']);
    exit();
}

// Yêu cầu thư viện (Tuỳ chỉnh lại đường dẫn cho đúng tới thư mục vendor của bạn)
require_once '../../../vendor/autoload.php';

try {
    // 4. Khởi tạo Parser và đọc file
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($file_tmp);
    
    // Lấy tối đa 3 trang đầu
    $pages = $pdf->getPages();
    $text = '';
    $max_pages = min(3, count($pages));
    for ($i = 0; $i < $max_pages; $i++) {
        $text .= $pages[$i]->getText() . "\n";
    }

    if (trim($text) === '') {
        throw new Exception("PDF dạng scan hoặc không có dữ liệu văn bản (text).");
    }

    $text_lower = mb_strtolower($text, 'UTF-8');
    
    // 5. Logic nhận diện Môn học
    $subjectKeywords = [
        'Toán học' => ['toán', 'đại số', 'hình học', 'giải tích', 'xác suất', 'thống kê', 'lượng giác', 'tích phân', 'phương trình', 'ma trận', 'đạo hàm', 'tích phân', 'logarit', 'số phức'],
        'Vật lý' => ['vật lý', 'cơ học', 'điện từ', 'quang học', 'nhiệt học', 'dao động', 'sóng', 'điện trường', 'từ trường', 'lực', 'năng lượng', 'động lượng', 'gia tốc', 'vận tốc'],
        'Hóa học' => ['hóa học', 'phản ứng', 'bảng tuần hoàn', 'hợp chất', 'axit', 'bazơ', 'muối', 'kim loại', 'phi kim', 'nguyên tử', 'phân tử', 'dung dịch', 'cân bằng'],
        'Tiếng Anh' => ['tiếng anh', 'english', 'grammar', 'vocabulary', 'reading comprehension', 'ielts', 'toeic', 'toefl', 'pronunciation', 'listening', 'speaking', 'writing'],
        'Ngữ Văn' => ['ngữ văn', 'văn học', 'thơ', 'truyện', 'tác phẩm', 'đọc hiểu', 'nghị luận', 'cảm thụ', 'phân tích', 'bình luận', 'tác giả', 'tác phẩm'],
        'Lịch sử' => ['lịch sử', 'chiến tranh', 'triều đại', 'cách mạng', 'thế giới', 'việt nam', 'nhà nước', 'phong kiến', 'đế quốc', 'kháng chiến', 'đô hộ'],
        'Địa lý' => ['địa lý', 'địa lí', 'khí hậu', 'địa hình', 'sông', 'biển', 'dân cư', 'kinh tế', 'vùng', 'miền', 'tự nhiên', 'đô thị'],
        'Sinh học' => ['sinh học', 'tế bào', 'gen', 'di truyền', 'tiến hóa', 'sinh thái', 'động vật', 'thực vật', 'vi sinh', 'miễn dịch', 'trao đổi chất'],
        'Tin học' => ['tin học', 'lập trình', 'máy tính', 'phần mềm', 'thuật toán', 'dữ liệu', 'hệ điều hành', 'mạng', 'internet', 'cơ sở dữ liệu', 'python', 'java', 'c++'],
        'Giáo dục công dân' => ['giáo dục công dân', 'pháp luật', 'đạo đức', 'công dân', 'quyền', 'nghĩa vụ', 'hiến pháp', 'trách nhiệm']
    ];

    $detected_subject = 'other';
    $custom_subject = '';

    // A. Ưu tiên cao: Tìm chuỗi khai báo môn học rõ ràng
    $explicit_match = false;
    if (preg_match('/(môn|bộ môn|môn học|môn thi)[\s:]+([a-záàảãạăắằẳẵặâấầẩẫậéèẻẽẹêếềểễệíìỉĩịóòỏõọôốồổỗộơớờởỡợúùủũụưứừửữựýỳỷỹỵđ\s]+)/iu', $text, $matches)) {
        $detected_explicit = trim(mb_strtolower($matches[2], 'UTF-8'));
        foreach (array_keys($subjectKeywords) as $subjName) {
            $subjKey = mb_strtolower(str_replace(' học', '', $subjName), 'UTF-8');
            if (strpos($detected_explicit, $subjKey) !== false) {
                $detected_subject = $subjName;
                $explicit_match = true;
                break;
            }
        }
    }

    // B. Fallback: Nếu không khai báo rõ, dùng hệ thống chấm điểm từ khóa
    if (!$explicit_match) {
        $max_score = 0;
        foreach ($subjectKeywords as $subjName => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                $score += mb_substr_count($text_lower, $kw);
            }
            // Ưu tiên các từ khóa dài hơn hoặc chính xác hơn
            if ($score > $max_score) {
                $max_score = $score;
                $detected_subject = $subjName;
            }
        }
        // Ngưỡng tối thiểu để xác nhận (chống nhận diện sai)
        if ($max_score < 3) {
            $detected_subject = 'other';
        }
    }

    // 6. Logic nhận diện Tiêu đề
    $detected_title = '';
    $lines = explode("\n", $text);
    foreach ($lines as $line) {
        $line_clean = trim($line);
        $line_upper = mb_strtoupper($line_clean, 'UTF-8');
        
        // Tìm dòng chứa từ khóa tiêu đề
        if ((strpos($line_upper, 'ĐỀ THI') !== false || 
             strpos($line_upper, 'ĐỀ KIỂM TRA') !== false || 
             strpos($line_upper, 'BÀI KIỂM TRA') !== false || 
             strpos($line_upper, 'KIỂM TRA') !== false || 
             strpos($line_upper, 'THI') !== false) && 
             mb_strlen($line_clean, 'UTF-8') >= 10 && mb_strlen($line_clean, 'UTF-8') <= 120) {
            
            // Làm sạch tiêu đề: bỏ các ký tự đặc biệt
            $detected_title = preg_replace('/[^\w\s\p{L}\p{N}\.\,\:\-]/u', '', $line_clean);
            $detected_title = trim($detected_title);
            if (!empty($detected_title)) {
                break;
            }
        }
    }

    // Fallback nếu không tìm thấy tiêu đề trong văn bản -> Lấy tên file bỏ đuôi
    if (empty($detected_title)) {
        $detected_title = pathinfo($file_name, PATHINFO_FILENAME);
        $detected_title = ucwords(str_replace(['_', '-', '.'], ' ', $detected_title));
        // Giới hạn độ dài tiêu đề
        if (strlen($detected_title) > 100) {
            $detected_title = substr($detected_title, 0, 100) . '...';
        }
    }

    // 7. Trả về JSON thành công
    echo json_encode([
        'success' => true,
        'title' => $detected_title,
        'subject' => $detected_subject,
        'custom_subject' => ($detected_subject === 'other') ? 'Môn học chưa xác định' : ''
    ]);

} catch (Exception $e) {
    // Xử lý lỗi (PDF dạng ảnh hoặc lỗi parse)
    $fallback_title = ucwords(str_replace(['_', '-'], ' ', pathinfo($file_name, PATHINFO_FILENAME)));
    echo json_encode([
        'success' => false,
        'message' => 'Tài liệu dạng Scan/Ảnh không hỗ trợ đọc tự động. Vui lòng điền thông tin thủ công.',
        'title' => $fallback_title,
        'subject' => 'other',
        'custom_subject' => ''
    ]);
}
?>