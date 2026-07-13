<?php
session_start();
include '../../../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: ../../login/login.php");
    exit();
}

$loi = '';

// ================= PAGE CONFIG =================
$page_css = 'create_quiz.css'; 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khởi tạo Đề Thi - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- GỌI FILE CSS GIAO DIỆN WIZARD -->
    <link rel="stylesheet" href="/WebTaoBoDeTuDong/assets/css/<?php echo $page_css; ?>?v=<?php echo time(); ?>">
</head>
<body>

<div class="wizard-container">
    <!-- Header -->
    <div class="wizard-header">
        <div class="icon-wrapper">
            <i class="fas fa-pen-fancy"></i>
        </div>
        <h2>Khởi tạo Đề thi mới</h2>
        <p>Thiết lập thông số cơ bản cho bộ đề của bạn</p>
    </div>

    <!-- Error -->
    <?php if (!empty($loi)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($loi); ?>
        </div>
    <?php endif; ?>

    <!-- Progress Bar -->
    <div class="progress-bar">
        <div class="progress-fill" id="progressFill"></div>
        <div class="step-indicator active" id="ind-1">
            1
            <span class="step-label">Thông tin</span>
        </div>
        <div class="step-indicator" id="ind-2">
            2
            <span class="step-label">Đối tượng</span>
        </div>
        <div class="step-indicator" id="ind-3">
            3
            <span class="step-label">Nhập liệu</span>
        </div>
        <div class="step-indicator" id="ind-4">
            4
            <span class="step-label">Đáp án</span>
        </div>
    </div>

    <!-- Form -->
    <form id="quizWizardForm" method="POST" action="process_wizard.php" enctype="multipart/form-data">

        <!-- STEP 1 -->
        <div class="step active" id="step-1">
            <div class="step-title">
                <span class="step-number">1</span>
                Thông tin cơ bản
            </div>

            <div class="form-group">
                <label for="title">Tên bộ đề thi <span class="required">*</span></label>
                <input type="text" name="title" id="title" class="form-control" required placeholder="Ví dụ: Đề thi thử THPT Quốc Gia môn Toán năm 2026">
            </div>

            <div class="form-group">
                <label for="subject_select">Thuộc môn học nào? <span class="required">*</span></label>
                <select name="subject" id="subject_select" class="form-control" onchange="toggleOtherSubject()">
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
                <label for="custom_subject">Nhập tên môn học của bạn</label>
                <input type="text" name="custom_subject" id="custom_subject" class="form-control" placeholder="Nhập tên môn học...">
            </div>
        </div>

        <!-- STEP 2 -->
        <div class="step" id="step-2">
            <div class="step-title">
                <span class="step-number">2</span>
                Đối tượng &amp; cấp độ
            </div>

            <div class="form-group">
                <label>Bộ đề này dành cho đối tượng nào? <span class="required">*</span></label>
                <div class="radio-group">
                    <div class="radio-card selected" onclick="selectAudience(this, 'hoc_sinh')">
                        <input type="radio" name="target_audience" value="hoc_sinh" checked>
                        <span class="card-icon"><i class="fas fa-school"></i></span>
                        <span class="card-label">Học sinh</span>
                        <span class="card-sub">Cấp 1, 2, 3</span>
                    </div>
                    <div class="radio-card" onclick="selectAudience(this, 'sinh_vien')">
                        <input type="radio" name="target_audience" value="sinh_vien">
                        <span class="card-icon"><i class="fas fa-university"></i></span>
                        <span class="card-label">Sinh viên</span>
                        <span class="card-sub">Đại học, Cao đẳng</span>
                    </div>
                </div>
            </div>

            <div class="form-group" id="major_group" style="display: none;">
                <label for="major">Khối / Ngành học cụ thể (Không bắt buộc)</label>
                <input type="text" name="major" id="major" class="form-control" placeholder="Ví dụ: Công nghệ thông tin, Kinh tế, Y Dược...">
            </div>
        </div>

        <!-- STEP 3 -->
        <div class="step" id="step-3">
            <div class="step-title">
                <span class="step-number">3</span>
                Phương thức nhập liệu
            </div>

            <div class="form-group">
                <label>Bạn muốn nhập dữ liệu câu hỏi bằng cách nào? <span class="required">*</span></label>
                <div class="radio-group">
                    <div class="radio-card" onclick="selectInputMethod(this, 'manual')">
                        <input type="radio" name="input_method" value="manual">
                        <span class="card-icon"><i class="fas fa-keyboard"></i></span>
                        <span class="card-label">Nhập thủ công</span>
                        <span class="card-sub">Tự tạo từng câu</span>
                    </div>
                    <div class="radio-card selected" onclick="selectInputMethod(this, 'upload')">
                        <input type="radio" name="input_method" value="upload" checked>
                        <span class="card-icon"><i class="fas fa-file-upload"></i></span>
                        <span class="card-label">Tải file</span>
                        <span class="card-sub">Word / PDF</span>
                    </div>
                    <div class="radio-card" onclick="selectInputMethod(this, 'bank')">
                        <input type="radio" name="input_method" value="bank">
                        <span class="card-icon"><i class="fas fa-database"></i></span>
                        <span class="card-label">Ngân hàng câu hỏi</span>
                        <span class="card-sub">Bốc ngẫu nhiên</span>
                    </div>
                </div>
            </div>

            <!-- Upload section -->
            <div id="upload_section" class="upload-area">
                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <h4>Tải tài liệu của bạn lên đây</h4>
                <p>Chỉ hỗ trợ file định dạng <strong>.docx</strong> hoặc <strong>.pdf</strong></p>
                <input type="file" name="quiz_file" accept=".pdf, .docx">
            </div>

            <!-- Bank section -->
            <div id="bank_section" class="bank-section">
                <div class="bank-header">
                    <i class="fas fa-sliders-h"></i>
                    <div>
                        <h4>Tùy chỉnh cấu trúc đề</h4>
                        <p>Hệ thống sẽ bốc ngẫu nhiên câu hỏi của môn học này từ kho dữ liệu chung.</p>
                    </div>
                </div>
                <div class="bank-grid">
                    <div class="level-item">
                        <label class="easy"><i class="fas fa-circle"></i> Dễ</label>
                        <input type="number" name="count_easy" min="0" value="10">
                    </div>
                    <div class="level-item">
                        <label class="medium"><i class="fas fa-circle"></i> Trung bình</label>
                        <input type="number" name="count_medium" min="0" value="10">
                    </div>
                    <div class="level-item">
                        <label class="hard"><i class="fas fa-circle"></i> Khó</label>
                        <input type="number" name="count_hard" min="0" value="5">
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 4 -->
        <div class="step" id="step-4">
            <div class="step-title">
                <span class="step-number">4</span>
                Cấu hình đáp án
            </div>

            <div class="form-group">
                <label>Tài liệu / Đề thi của bạn có sẵn đáp án chưa? <span class="required">*</span></label>
                <div class="radio-group">
                    <div class="radio-card selected" onclick="selectAnswerConfig(this, 1)">
                        <input type="radio" name="has_answers" value="1" checked>
                        <span class="card-icon"><i class="fas fa-check-circle" style="color: #059669;"></i></span>
                        <span class="card-label">Đã có đáp án</span>
                        <span class="card-sub">Hệ thống tự động nhận diện</span>
                    </div>
                    <div class="radio-card" onclick="selectAnswerConfig(this, 0)">
                        <input type="radio" name="has_answers" value="0">
                        <span class="card-icon"><i class="fas fa-times-circle" style="color: #dc2626;"></i></span>
                        <span class="card-label">Đề trắc nghiệm trắng</span>
                        <span class="card-sub">Học viên tự làm, bạn chấm sau</span>
                    </div>
                </div>
            </div>

            <div class="info-box">
                <p>
                    <strong><i class="fas fa-info-circle"></i> Lưu ý:</strong><br>
                    • Nếu chọn <strong>"Đã có đáp án"</strong>, hệ thống sẽ tự tìm và đánh dấu đáp án đúng giúp bạn.<br>
                    • Nếu chọn <strong>"Đề trắng"</strong>, hệ thống chỉ lưu câu hỏi để học viên tự làm và bạn sẽ chấm sau.
                </p>
            </div>
        </div>

        <!-- Buttons -->
        <div class="btn-group">
            <button type="button" class="btn btn-prev" id="btnPrev" style="visibility: hidden;" onclick="nextPrev(-1)">
                <i class="fas fa-arrow-left"></i> Quay lại
            </button>
            <button type="button" class="btn btn-next" id="btnNext" onclick="nextPrev(1)">
                Tiếp theo <i class="fas fa-arrow-right"></i>
            </button>
        </div>

    </form>
</div>

<script>
    // ============================================================
    // WIZARD NAVIGATION
    // ============================================================
    let currentTab = 0;
    const totalSteps = 4;
    showTab(currentTab);

    function showTab(n) {
        const steps = document.getElementsByClassName("step");
        const indicators = [
            document.getElementById("ind-1"),
            document.getElementById("ind-2"),
            document.getElementById("ind-3"),
            document.getElementById("ind-4")
        ];

        // Update steps visibility
        for (let i = 0; i < steps.length; i++) {
            steps[i].classList.remove("active");
        }
        steps[n].classList.add("active");

        // Update indicators
        for (let i = 0; i < indicators.length; i++) {
            indicators[i].className = "step-indicator";
            if (i < n) {
                indicators[i].classList.add("completed");
                indicators[i].innerHTML = '<i class="fas fa-check"></i>';
            } else if (i === n) {
                indicators[i].classList.add("active");
                indicators[i].innerHTML = (i + 1);
            } else {
                indicators[i].innerHTML = (i + 1);
            }
        }

        // Update progress fill
        const progressPercent = (n / (totalSteps - 1)) * 100;
        document.getElementById("progressFill").style.width = progressPercent + '%';

        // Update buttons
        document.getElementById("btnPrev").style.visibility = (n === 0) ? "hidden" : "visible";

        const btnNext = document.getElementById("btnNext");
        if (n === totalSteps - 1) {
            btnNext.innerHTML = '<i class="fas fa-rocket"></i> Khởi tạo Đề thi';
            btnNext.className = 'btn btn-next btn-submit';
        } else {
            btnNext.innerHTML = 'Tiếp theo <i class="fas fa-arrow-right"></i>';
            btnNext.className = 'btn btn-next';
        }
    }

    function nextPrev(n) {
        const steps = document.getElementsByClassName("step");

        // Validate Step 1
        if (n === 1 && currentTab === 0) {
            const title = document.getElementById("title").value.trim();
            if (title === "") {
                showError("Vui lòng nhập tên đề thi!");
                document.getElementById("title").focus();
                return;
            }
        }

        // Validate Step 3 - if upload method but no file
        if (n === 1 && currentTab === 2) {
            const inputMethod = document.querySelector('input[name="input_method"]:checked');
            if (inputMethod && inputMethod.value === 'upload') {
                const fileInput = document.querySelector('input[name="quiz_file"]');
                if (fileInput && fileInput.files.length === 0) {
                    showError("Vui lòng chọn file tải lên!");
                    return;
                }
            }
        }

        // Move to next/prev
        steps[currentTab].classList.remove("active");
        currentTab += n;

        if (currentTab >= totalSteps) {
            // Submit form
            const btn = document.getElementById("btnNext");
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            btn.disabled = true;
            document.getElementById("quizWizardForm").submit();
            return;
        }

        showTab(currentTab);
    }

    // ============================================================
    // UI HELPERS
    // ============================================================
    function showError(message) {
        // Check if error exists, remove old
        const oldError = document.querySelector('.error-message');
        if (oldError) oldError.remove();

        const container = document.querySelector('.wizard-container');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
        container.insertBefore(errorDiv, container.querySelector('.progress-bar'));
    }

    function toggleOtherSubject() {
        const val = document.getElementById("subject_select").value;
        const group = document.getElementById("other_subject_group");
        group.style.display = (val === 'other') ? 'block' : 'none';
    }

    function selectAudience(element, val) {
        const group = element.closest('.radio-group');
        group.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
        element.classList.add('selected');
        element.querySelector('input').checked = true;

        document.getElementById("major_group").style.display = (val === 'sinh_vien') ? 'block' : 'none';
    }

    function selectInputMethod(element, val) {
        const group = element.closest('.radio-group');
        group.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
        element.classList.add('selected');
        element.querySelector('input').checked = true;

        document.getElementById("upload_section").style.display = (val === 'upload') ? 'block' : 'none';
        const bankSection = document.getElementById("bank_section");
        
        // Sửa lỗi: Thay vì toggle class 'visible' (nếu CSS không định nghĩa), đổi trực tiếp display style
        bankSection.style.display = (val === 'bank') ? 'block' : 'none';
    }

    function selectAnswerConfig(element, val) {
        const group = element.closest('.radio-group');
        group.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
        element.classList.add('selected');
        element.querySelector('input').checked = true;
    }

    // ============================================================
    // KEYBOARD SUPPORT (Enter to go next)
    // ============================================================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const activeStep = document.querySelector('.step.active');
            if (activeStep) {
                const inputs = activeStep.querySelectorAll('input, select');
                const lastInput = inputs[inputs.length - 1];
                if (document.activeElement === lastInput) {
                    e.preventDefault();
                    nextPrev(1);
                }
            }
        }
    });
</script>

</body>
</html>