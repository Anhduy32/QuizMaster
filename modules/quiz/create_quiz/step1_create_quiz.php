<?php
session_start();
include '../../../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: ../../login/login.php");
    exit();
}

$loi = '';

// XỬ LÝ KHI NGƯỜI DÙNG BẤM "HOÀN TẤT & XỬ LÝ" Ở BƯỚC 4
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $creator_username = $_SESSION['username'];
    $title = trim($_POST['title']);
    $subject = ($_POST['subject'] === 'other') ? trim($_POST['custom_subject']) : $_POST['subject'];
    $target_audience = $_POST['target_audience'];
    $major = $_POST['major'] ?? NULL;
    $input_method = $_POST['input_method'];
    $has_answers = (int)$_POST['has_answers'];
    
    // Mặc định ban đầu đề thi là 'draft' (Bản nháp), sau khi nhập xong câu hỏi mới đổi thành 'completed'
    $status = 'draft';

    // 1. TẠO ĐỀ THI TRONG DATABASE
    $query = "INSERT INTO quizzes (title, subject, creator_username, target_audience, major, has_answers, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssis", $title, $subject, $creator_username, $target_audience, $major, $has_answers, $status);
    
    if ($stmt->execute()) {
        $quiz_id = $stmt->insert_id;

        // 2. ĐIỀU HƯỚNG DỰA TRÊN PHƯƠNG THỨC NHẬP LIỆU (BƯỚC 3)
        if ($input_method === 'manual') {
            // Chuyển sang file step2 cũ của bạn để nhập tay
            header("Location: step2_add_questions.php?quiz_id=" . $quiz_id);
            exit();
        } 
        else if ($input_method === 'upload') {
            // [Nơi đặt logic xử lý file Word/PDF sau này]
            // Tạm thời chuyển thẳng đến trang success
            
            // Ép status thành completed vì upload là xong luôn
            $conn->query("UPDATE quizzes SET status = 'completed' WHERE id = $quiz_id");
            
            header("Location: success.php?quiz_id=" . $quiz_id);
            exit();
        }
    } else {
        $loi = "Đã xảy ra lỗi khi khởi tạo đề thi. Vui lòng thử lại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khởi tạo Đề Thi - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; margin: 0; padding: 40px 20px; color: #1a202c; }
        .wizard-container { max-width: 750px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        .wizard-header { text-align: center; margin-bottom: 30px; }
        
        /* Hiệu ứng chuyển bước */
        .step { display: none; animation: fadeIn 0.4s ease; }
        .step.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        /* Thanh tiến trình */
        .progress-bar { display: flex; justify-content: space-between; margin-bottom: 40px; position: relative; }
        .progress-bar::before { content: ''; position: absolute; top: 18px; left: 0; right: 0; height: 4px; background: #e2e8f0; z-index: 1; border-radius: 2px; }
        .step-indicator { width: 40px; height: 40px; background: #fff; border: 4px solid #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; z-index: 2; color: #a0aec0; transition: all 0.3s ease; }
        .step-indicator.active { border-color: #3182ce; background: #3182ce; color: #fff; box-shadow: 0 0 0 4px rgba(49, 130, 206, 0.2); }
        .step-indicator.completed { border-color: #38a169; background: #38a169; color: #fff; }

        /* Form Controls */
        .form-group { margin-bottom: 25px; }
        label { display: block; font-weight: 600; margin-bottom: 10px; color: #4a5568; }
        input[type="text"], select { width: 100%; padding: 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; outline: none; transition: 0.2s; box-sizing: border-box; }
        input[type="text"]:focus, select:focus { border-color: #3182ce; background: #fbfdff; }
        
        /* Radio Cards (Cho Bước 2, 3, 4) */
        .radio-group { display: flex; gap: 15px; }
        .radio-card { flex: 1; border: 2px solid #e2e8f0; padding: 20px 15px; border-radius: 12px; cursor: pointer; text-align: center; font-weight: 600; transition: all 0.2s; color: #718096; }
        .radio-card:hover { border-color: #bee3f8; background: #fbfdff; }
        .radio-card input { display: none; }
        .radio-card i { font-size: 1.5rem; display: block; margin-bottom: 10px; color: #a0aec0; transition: 0.2s; }
        .radio-card.selected { border-color: #3182ce; background: #ebf8ff; color: #3182ce; }
        .radio-card.selected i { color: #3182ce; }

        /* Buttons */
        .btn-group { display: flex; justify-content: space-between; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
        .btn { padding: 14px 28px; border-radius: 10px; font-weight: 600; font-size: 1rem; cursor: pointer; border: none; transition: 0.2s; }
        .btn-prev { background: #edf2f7; color: #4a5568; }
        .btn-prev:hover { background: #e2e8f0; }
        .btn-next { background: #3182ce; color: #fff; }
        .btn-next:hover { background: #2b6cb0; transform: translateY(-1px); }
        
        /* Upload Area */
        .upload-area { border: 2px dashed #cbd5e0; padding: 40px; text-align: center; border-radius: 12px; background: #f8fafc; transition: 0.2s; }
        .upload-area:hover { border-color: #3182ce; background: #ebf8ff; }
    </style>
</head>
<body>

<div class="wizard-container">
    <div class="wizard-header">
        <h2>Khởi tạo Đề thi mới</h2>
        <p style="color: #718096; margin-top: 5px;">Thiết lập thông số cơ bản cho bộ đề của bạn</p>
    </div>

    <?php if(!empty($loi)): ?>
        <div style="background: #fed7d7; color: #9b2c2c; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold;">
            <?php echo $loi; ?>
        </div>
    <?php endif; ?>

    <div class="progress-bar">
        <div class="step-indicator active" id="ind-1">1</div>
        <div class="step-indicator" id="ind-2">2</div>
        <div class="step-indicator" id="ind-3">3</div>
        <div class="step-indicator" id="ind-4">4</div>
    </div>

<form id="quizWizardForm" method="POST" action="process_wizard.php" enctype="multipart/form-data">
            
        <div class="step active" id="step-1">
            <div class="form-group">
                <label>Tên bộ đề thi</label>
                <input type="text" name="title" id="title" required placeholder="Ví dụ: Đề thi thử THPT Quốc Gia môn Toán năm 2026">
            </div>
            <div class="form-group">
                <label>Thuộc môn học nào?</label>
                <select name="subject" id="subject_select" onchange="toggleOtherSubject()">
                    <option value="Toán học">Toán học</option>
                    <option value="Vật lý">Vật lý</option>
                    <option value="Hóa học">Hóa học</option>
                    <option value="Tiếng Anh">Tiếng Anh</option>
                    <option value="Ngữ Văn">Ngữ Văn</option>
                    <option value="Lịch sử">Lịch sử</option>
                    <option value="other">Khác (Tự nhập tên môn)...</option>
                </select>
            </div>
            <div class="form-group" id="other_subject_group" style="display: none;">
                <label>Nhập tên môn học của bạn</label>
                <input type="text" name="custom_subject" id="custom_subject" placeholder="Nhập tên môn học...">
            </div>
        </div>

        <div class="step" id="step-2">
            <label>Bộ đề này dành cho đối tượng nào?</label>
            <div class="form-group radio-group">
                <label class="radio-card selected" onclick="selectAudience(this, 'hoc_sinh')">
                    <input type="radio" name="target_audience" value="hoc_sinh" checked>
                    <i class="fas fa-school"></i>
                    <span>Học Sinh (Cấp 1, 2, 3)</span>
                </label>
                <label class="radio-card" onclick="selectAudience(this, 'sinh_vien')">
                    <input type="radio" name="target_audience" value="sinh_vien">
                    <i class="fas fa-university"></i>
                    <span>Sinh Viên Đại Học</span>
                </label>
            </div>
            <div class="form-group" id="major_group" style="display: none; margin-top: 25px;">
                <label>Khối / Ngành học cụ thể (Không bắt buộc)</label>
                <input type="text" name="major" placeholder="Ví dụ: Công nghệ thông tin, Kinh tế, Y Dược...">
            </div>
        </div>

        <div class="step" id="step-3">
            <label>Bạn muốn nhập dữ liệu câu hỏi bằng cách nào?</label>
            <div class="form-group radio-group">
                <label class="radio-card" onclick="selectInputMethod(this, 'manual')">
                    <input type="radio" name="input_method" value="manual">
                    <i class="fas fa-keyboard"></i>
                    <span>Nhập câu hỏi thủ công</span>
                </label>
                <label class="radio-card selected" onclick="selectInputMethod(this, 'upload')">
                    <input type="radio" name="input_method" value="upload" checked>
                    <i class="fas fa-file-upload"></i>
                    <span>Tự động qua File Word/PDF</span>
                </label>
            </div>
            
            <div id="upload_section" class="upload-area" style="margin-top: 25px;">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #a0aec0; margin-bottom: 15px;"></i>
                <h4 style="margin: 0 0 5px 0; color: #2d3748;">Tải tài liệu của bạn lên đây</h4>
                <p style="font-size: 0.9rem; color: #718096; margin-bottom: 20px;">Chỉ hỗ trợ file định dạng .docx hoặc .pdf</p>
                <input type="file" name="quiz_file" accept=".pdf, .docx">
            </div>
        </div>

        <div class="step" id="step-4">
            <label>Tài liệu/Đề thi của bạn có sẵn đáp án chưa?</label>
            <div class="form-group radio-group">
                <label class="radio-card selected" onclick="selectAnswerConfig(this, 1)">
                    <input type="radio" name="has_answers" value="1" checked>
                    <i class="fas fa-check-circle"></i>
                    <span>Đã có đáp án</span>
                </label>
                <label class="radio-card" onclick="selectAnswerConfig(this, 0)">
                    <input type="radio" name="has_answers" value="0">
                    <i class="fas fa-times-circle"></i>
                    <span>Đề trắc nghiệm trắng</span>
                </label>
            </div>
            <div style="background: #ebf8ff; border-left: 4px solid #3182ce; padding: 15px; margin-top: 25px; border-radius: 8px;">
                <p style="color: #2b6cb0; margin: 0; font-size: 0.95rem; line-height: 1.5;">
                    <strong>Lưu ý:</strong> <br>
                    - Nếu chọn "Đã có đáp án", hệ thống sẽ tự tìm và tick đáp án đúng giúp bạn.<br>
                    - Nếu chọn "Đề trắng", hệ thống chỉ lưu câu hỏi để học viên tự làm và bạn sẽ chấm sau.
                </p>
            </div>
        </div>

        <div class="btn-group">
            <button type="button" class="btn btn-prev" id="btnPrev" style="visibility: hidden;" onclick="nextPrev(-1)">Quay lại</button>
            <button type="button" class="btn btn-next" id="btnNext" onclick="nextPrev(1)">Tiếp theo</button>
        </div>
    </form>
</div>

<script>
    let currentTab = 0;
    showTab(currentTab);

    function showTab(n) {
        let x = document.getElementsByClassName("step");
        x[n].classList.add("active");
        
        for(let i=0; i<x.length; i++) {
            let indicator = document.getElementById("ind-" + (i+1));
            indicator.className = "step-indicator";
            if (i < n) indicator.classList.add("completed");
            if (i === n) indicator.classList.add("active");
            if (i < n) indicator.innerHTML = '<i class="fas fa-check"></i>'; else indicator.innerHTML = (i+1);
        }

        document.getElementById("btnPrev").style.visibility = (n == 0) ? "hidden" : "visible";
        document.getElementById("btnNext").innerHTML = (n == (x.length - 1)) ? "Khởi tạo Đề thi" : "Tiếp theo";
    }

    function nextPrev(n) {
        let x = document.getElementsByClassName("step");
        
        // Validate đơn giản ở bước 1
        if (n == 1 && currentTab == 0) {
            let title = document.getElementById("title").value.trim();
            if(title === "") {
                alert("Vui lòng nhập tên đề thi!");
                return false;
            }
        }

        x[currentTab].classList.remove("active");
        currentTab = currentTab + n;
        
        if (currentTab >= x.length) {
            // Hiển thị trạng thái loading trên nút
            document.getElementById("btnNext").innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            document.getElementById("btnNext").style.opacity = '0.7';
            document.getElementById("btnNext").style.pointerEvents = 'none';
            document.getElementById("quizWizardForm").submit();
            return false;
        }
        showTab(currentTab);
    }

    // Các hàm xử lý UI 
    function toggleOtherSubject() {
        let val = document.getElementById("subject_select").value;
        document.getElementById("other_subject_group").style.display = (val === 'other') ? 'block' : 'none';
    }

    function selectAudience(element, val) {
        document.querySelectorAll('input[name="target_audience"]').forEach(r => r.parentElement.classList.remove('selected'));
        element.classList.add('selected');
        element.querySelector('input').checked = true;
        document.getElementById("major_group").style.display = (val === 'sinh_vien') ? 'block' : 'none';
    }

    function selectInputMethod(element, val) {
        document.querySelectorAll('input[name="input_method"]').forEach(r => r.parentElement.classList.remove('selected'));
        element.classList.add('selected');
        element.querySelector('input').checked = true;
        document.getElementById("upload_section").style.display = (val === 'upload') ? 'block' : 'none';
    }

    function selectAnswerConfig(element, val) {
        document.querySelectorAll('input[name="has_answers"]').forEach(r => r.parentElement.classList.remove('selected'));
        element.classList.add('selected');
        element.querySelector('input').checked = true;
    }
</script>
</body>
</html>