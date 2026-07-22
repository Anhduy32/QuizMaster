<?php
session_start();
include '../../../config/database.php';

<<<<<<< HEAD
if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$error_message = isset($_GET['error']) ? $_GET['error'] : '';
=======
// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: ../../login/login.php");
    exit();
}

$loi = '';
>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8

// ================= PAGE CONFIG =================
$page_css = 'create_quiz.css'; 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>Tạo đề thi mới - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">    
    <link rel="stylesheet" href="../../../assets/css/<?php echo $page_css; ?>?v=<?php echo time(); ?>">
    
    <style>
        /* CSS bổ sung cho phần upload file trong step2 */
        .upload-area.dragover { border-color: var(--primary-teal); background: rgba(255, 255, 255, 0.8); }
        .upload-area.has-file { border-color: #059669; background: rgba(5, 150, 105, 0.04); }
        .file-info { display: flex; align-items: center; gap: 12px; margin-top: 10px; padding: 10px 14px; background: #f0fff4; border-radius: 8px; border: 1px solid #a7f3d0; }
        .file-info i { color: #059669; font-size: 1.2rem; }
        .file-info .file-name { font-weight: 600; color: #065f46; flex: 1; }
        .file-info .file-size { font-size: 0.8rem; color: #64748b; }
        .file-info .btn-remove-file { background: none; border: none; color: #dc2626; cursor: pointer; font-size: 1.1rem; padding: 4px 8px; border-radius: 4px; transition: all 0.2s; }
        .file-info .btn-remove-file:hover { background: #fee2e2; }
        .upload-area .upload-hint { font-size: 0.8rem; color: #94a3b8; margin-top: 8px; }
        .upload-area .upload-hint i { color: #f59e0b; }
    </style>
</head>
<body>
    <div class="wizard-container">
        <!-- HEADER -->
        <div class="wizard-header">
            <div class="icon-wrapper"><i class="fas fa-pen-fancy"></i></div>
            <h2>Tạo đề thi mới</h2>
            <p>Nhập thông tin cơ bản và chọn phương thức nhập liệu</p>
        </div>

        <!-- ERROR MESSAGE -->
        <div class="error-message <?php echo $error_message ? 'show' : ''; ?>" id="errorMessage">
            <i class="fas fa-exclamation-circle"></i>
            <span id="errorText"><?php echo htmlspecialchars($error_message); ?></span>
        </div>

        <!-- PROGRESS BAR -->
        <div class="progress-bar">
            <div class="progress-fill"></div>
            <div class="step-indicator active">1<span class="step-label">Thông tin</span></div>
            <div class="step-indicator">2<span class="step-label">Nhập liệu</span></div>
            <div class="step-indicator">3<span class="step-label">Hoàn tất</span></div>
        </div>

        <!-- FORM CHÍNH -->
        <form id="quizForm" method="POST" action="process_wizard.php" enctype="multipart/form-data">
            
            <!-- STEP 1: THÔNG TIN CƠ BẢN -->
            <div class="step active" id="step1">
                <div class="step-title"><span class="step-number">1</span> Thông tin đề thi</div>

                <div class="form-group">
                    <label for="title">Tiêu đề đề thi <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="VD: Đề thi giữa kỳ - Toán cao cấp" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="subject">Môn học <span class="required">*</span></label>
                        <select id="subject" name="subject" class="form-control" required>
                            <option value="">-- Chọn môn --</option>
                            <option value="Toán học">Toán</option>
                            <option value="Ngữ Văn">Văn</option>
                            <option value="Tiếng Anh">Anh</option>
                            <option value="Vật lý">Lý</option>
                            <option value="Hóa học">Hóa</option>
                            <option value="Sinh học">Sinh</option>
                            <option value="Lịch sử">Sử</option>
                            <option value="Địa lý">Địa</option>
                            <option value="other">Môn khác...</option>
                        </select>
                        <div id="custom_subject_wrapper" style="display:none; margin-top:10px;">
                            <input type="text" id="custom_subject" name="custom_subject" class="form-control" placeholder="Nhập tên môn học cụ thể">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="target_audience">Đối tượng <span class="required">*</span></label>
                        <select id="target_audience" name="target_audience" class="form-control" required>
                            <option value="">-- Chọn --</option>
                            <option value="Sinh viên năm 1">Sinh viên năm 1</option>
                            <option value="Sinh viên năm 2">Sinh viên năm 2</option>
                            <option value="Sinh viên năm 3">Sinh viên năm 3</option>
                            <option value="Sinh viên năm 4">Sinh viên năm 4</option>
                            <option value="Học viên cao học">Học viên cao học</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="major">Chuyên ngành</label>
                        <input type="text" id="major" name="major" class="form-control" placeholder="VD: Kế toán, Công nghệ thông tin...">
                    </div>

                    <div class="form-group">
                        <label>Có đáp án?</label>
                        <div class="radio-group" style="grid-template-columns: 1fr 1fr;">
                            <label class="radio-card selected" style="padding: 12px 16px;">
                                <input type="radio" name="has_answers" value="1" checked>
                                <span class="card-icon" style="font-size:1.2rem;"><i class="fas fa-check-circle" style="color:#059669;"></i></span>
                                <span class="card-label">Có</span>
                            </label>
                            <label class="radio-card" style="padding: 12px 16px;">
                                <input type="radio" name="has_answers" value="0">
                                <span class="card-icon" style="font-size:1.2rem;"><i class="fas fa-times-circle" style="color:#dc2626;"></i></span>
                                <span class="card-label">Không</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="btn-group">
                    <a href="../../../home.php" class="btn btn-prev"><i class="fas fa-arrow-left"></i> Quay lại</a>
                    <button type="button" class="btn btn-next" onclick="goToStep(2)">Tiếp tục <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 2: PHƯƠNG THỨC NHẬP LIỆU -->
            <div class="step" id="step2">
                <div class="step-title"><span class="step-number">2</span> Chọn phương thức nhập liệu</div>

                <div class="form-group">
                    <label>Phương thức nhập liệu <span class="required">*</span></label>
                    <div class="radio-group" id="methodGroup">
                        <label class="radio-card selected" data-value="manual">
                            <input type="radio" name="input_method" value="manual" checked>
                            <span class="card-icon"><i class="fas fa-edit"></i></span>
                            <span class="card-label">Nhập thủ công</span>
                            <span class="card-sub">Tạo từng câu hỏi</span>
                        </label>
                        <label class="radio-card" data-value="bank">
                            <input type="radio" name="input_method" value="bank">
                            <span class="card-icon"><i class="fas fa-database"></i></span>
                            <span class="card-label">Từ ngân hàng</span>
                            <span class="card-sub">Bốc ngẫu nhiên</span>
                        </label>
                        <label class="radio-card" data-value="upload">
                            <input type="radio" name="input_method" value="upload">
                            <span class="card-icon"><i class="fas fa-file-pdf"></i></span>
                            <span class="card-label">Tải file PDF</span>
                            <span class="card-sub">Nhúng trực tiếp</span>
                        </label>
                    </div>
                </div>

                <!-- MANUAL SECTION -->
                <div id="section_manual" class="info-box">
                    <p><i class="fas fa-edit" style="color:var(--primary-teal);"></i> Sau khi tạo đề, bạn sẽ được chuyển sang trang nhập từng câu hỏi trắc nghiệm với 4 lựa chọn A, B, C, D.</p>
                </div>

                <!-- BANK SECTION -->
                <div id="section_bank" class="bank-section visible">
                    <div class="bank-header">
                        <i class="fas fa-layer-group"></i>
                        <div>
                            <h4>Chọn số lượng câu hỏi từ ngân hàng</h4>
                            <p>Hệ thống sẽ tự động bốc ngẫu nhiên từ các đề thi công khai cùng môn học</p>
                        </div>
                    </div>
                    
                    <div class="bank-grid">
                        <div class="level-item">
                            <label class="easy"><i class="fas fa-circle" style="color:#059669; font-size:0.6rem;"></i> Dễ</label>
                            <input type="number" name="count_easy" min="0" max="50" value="0" placeholder="0">
                        </div>
                        <div class="level-item">
                            <label class="medium"><i class="fas fa-circle" style="color:#d97706; font-size:0.6rem;"></i> Trung bình</label>
                            <input type="number" name="count_medium" min="0" max="50" value="0" placeholder="0">
                        </div>
                        <div class="level-item">
                            <label class="hard"><i class="fas fa-circle" style="color:#dc2626; font-size:0.6rem;"></i> Khó</label>
                            <input type="number" name="count_hard" min="0" max="50" value="0" placeholder="0">
                        </div>
                    </div>
                </div>

                <!-- UPLOAD SECTION -->
                <div id="section_upload" style="display:none;">
                    <div class="upload-area" id="dropZone">
                        <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <h4>Nhấp để chọn file hoặc kéo thả</h4>
                        <p>Hỗ trợ định dạng <strong>.pdf</strong> (Tối đa 10MB)</p>
                        <input type="file" id="file_input" name="quiz_file" accept=".pdf">
                        <div class="file-name" id="fileNameDisplay"></div>
                        <div class="upload-hint">
                            <i class="fas fa-info-circle"></i> File PDF sẽ được nhúng trực tiếp để học sinh xem và làm bài
                        </div>
                    </div>

                    <div class="info-box" style="margin-top:12px;">
                        <p style="font-size:0.85rem;">
                            <i class="fas fa-info-circle" style="color:#059669;"></i>
                            Hệ thống sẽ nhúng nguyên bản file PDF để học sinh xem. Bạn <strong>không cần</strong> bận tâm về cấu trúc định dạng sâu. Chức năng nhập đáp án trắc nghiệm A, B, C, D sẽ được cung cấp ở bước tiếp theo.
                        </p>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-prev" onclick="goToStep(1)"><i class="fas fa-arrow-left"></i> Quay lại</button>
                    <button type="submit" class="btn btn-submit" id="submitBtn"><i class="fas fa-check"></i> Tạo đề thi</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // ==========================================
            // 1. STEP NAVIGATION
            // ==========================================
            window.goToStep = function(step) {
                if (step === 2) {
                    const step1Inputs = document.getElementById('step1').querySelectorAll('input[required], select[required]');
                    let isValid = true;
                    
                    for (let input of step1Inputs) {
                        if (!input.checkValidity()) {
                            input.reportValidity();
                            isValid = false;
                            break;
                        }
                    }
                    
                    if (!isValid) return; 
                }

                document.querySelectorAll('.step').forEach(el => el.classList.remove('active'));
                document.getElementById('step' + step).classList.add('active');
                
                const indicators = document.querySelectorAll('.step-indicator');
                const fill = document.querySelector('.progress-fill');
                
                indicators.forEach((ind, index) => {
                    ind.classList.remove('active', 'completed');
                    if (index + 1 < step) {
                        ind.classList.add('completed');
                    } else if (index + 1 === step) {
                        ind.classList.add('active');
                    }
                });
                
                const progressPercent = ((step - 1) / 2) * 100;
                fill.style.width = progressPercent + '%';
                
                document.querySelector('.wizard-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
            };

            // ==========================================
            // 2. TOGGLE SECTIONS
            // ==========================================
            const methodRadios = document.querySelectorAll('input[name="input_method"]');
            const sections = {
                manual: document.getElementById('section_manual'),
                bank: document.getElementById('section_bank'),
                upload: document.getElementById('section_upload')
            };
            
            function toggleSections(value) {
                Object.keys(sections).forEach(key => {
                    sections[key].style.display = 'none';
                    sections[key].classList.remove('visible');
                });
                
                if (value === 'manual') {
                    sections.manual.style.display = 'block';
                } else if (value === 'bank') {
                    sections.bank.style.display = 'block';
                    sections.bank.classList.add('visible');
                } else if (value === 'upload') {
                    sections.upload.style.display = 'block';
                }
                
                document.querySelectorAll('.radio-card').forEach(card => {
                    card.classList.remove('selected');
                    const radio = card.querySelector('input[type="radio"]');
                    if (radio && radio.value === value) {
                        card.classList.add('selected');
                    }
                });
            }
            
            methodRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    toggleSections(this.value);
                    if (this.value !== 'upload') {
                        resetFileInput();
                    }
                });
            });
            
            const checkedRadio = document.querySelector('input[name="input_method"]:checked');
            if (checkedRadio) toggleSections(checkedRadio.value);

            // ==========================================
            // 3. CUSTOM SUBJECT
            // ==========================================
            const subjectSelect = document.getElementById('subject');
            const customWrapper = document.getElementById('custom_subject_wrapper');
            const customInput = document.getElementById('custom_subject');
            
            subjectSelect.addEventListener('change', function() {
                if (this.value === 'other') {
                    customWrapper.style.display = 'block';
                    customInput.setAttribute('required', 'required');
                } else {
                    customWrapper.style.display = 'none';
                    customInput.removeAttribute('required');
                    customInput.value = '';
                }
            });

            // ==========================================
            // 4. RADIO CARDS CLICK
            // ==========================================
            document.querySelectorAll('.radio-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        const parent = this.closest('.radio-group');
                        if (parent) {
                            parent.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
                            this.classList.add('selected');
                        }
                        const evt = new Event('change');
                        radio.dispatchEvent(evt);
                    }
                });
            });

            // ==========================================
            // 5. FILE UPLOAD HANDLING
            // ==========================================
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('file_input');
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            let selectedFile = null;
            
            function resetFileInput() {
                fileInput.value = '';
                selectedFile = null;
                fileNameDisplay.textContent = '';
                dropZone.classList.remove('has-file');
                dropZone.style.borderColor = 'rgba(15, 92, 107, 0.12)';
                dropZone.style.background = 'rgba(255, 255, 255, 0.4)';
            }
            
            function updateFileDisplay(file) {
                if (!file) { resetFileInput(); return; }
                
                if (!file.name.toLowerCase().endsWith('.pdf')) {
                    showError('Chỉ hỗ trợ file định dạng .pdf!');
                    resetFileInput();
                    return;
                }
                
                if (file.size > 10 * 1024 * 1024) {
                    showError('Dung lượng file vượt quá 10MB!');
                    resetFileInput();
                    return;
                }
                
                selectedFile = file;
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                fileNameDisplay.innerHTML = `
                    <div class="file-info">
                        <i class="fas fa-file-pdf"></i>
                        <span class="file-name">${file.name}</span>
                        <span class="file-size">(${sizeMB} MB)</span>
                        <button type="button" class="btn-remove-file" onclick="removeFile()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                dropZone.classList.add('has-file');
                dropZone.style.borderColor = '#059669';
                dropZone.style.background = 'rgba(5, 150, 105, 0.04)';
            }
            
            window.removeFile = function() {
                resetFileInput();
                document.getElementById('title').value = '';
                document.getElementById('subject').value = '';
                customWrapper.style.display = 'none';
                customInput.value = '';
                customInput.removeAttribute('required');
            };
            
            dropZone.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-file')) return;
                if (e.target.tagName !== 'INPUT') { fileInput.click(); }
            });
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    updateFileDisplay(this.files[0]);
                } else {
                    resetFileInput();
                }
            });
            
            dropZone.addEventListener('dragover', function(e) { 
                e.preventDefault(); 
                this.classList.add('dragover'); 
            });
            
            dropZone.addEventListener('dragleave', function(e) { 
                e.preventDefault(); 
                this.classList.remove('dragover'); 
            });
            
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    const file = e.dataTransfer.files[0];
                    if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
                        fileInput.files = e.dataTransfer.files;
                        updateFileDisplay(file);
                    } else {
                        showError('Vui lòng chỉ tải lên file PDF!');
                    }
                }
            });

            // ==========================================
            // 6. SHOW ERROR
            // ==========================================
            function showError(msg) {
                const errorEl = document.getElementById('errorMessage');
                const errorText = document.getElementById('errorText');
                errorText.textContent = msg;
                errorEl.classList.add('show');
                setTimeout(() => errorEl.classList.remove('show'), 5000);
            }

            // ==========================================
            // 7. FORM VALIDATION
            // ==========================================
            const form = document.getElementById('quizForm');
            
            // Bắt lỗi form để chuyển về bước đúng
            form.addEventListener('invalid', function(e) {
                e.preventDefault();
                const firstInvalid = e.target;
                const step = firstInvalid.closest('.step');
                
                if (step) {
                    const stepNum = parseInt(step.id.replace('step', ''));
                    if (stepNum !== 2) {
                        goToStep(stepNum);
                        setTimeout(() => {
                            firstInvalid.reportValidity();
                            firstInvalid.focus();
                        }, 300);
                    } else {
                        firstInvalid.reportValidity();
                    }
                }
            }, true);

            // Xử lý submit
            form.addEventListener('submit', function(e) {
                // Kiểm tra môn học tùy chỉnh
                const subjectVal = subjectSelect.value;
                if (subjectVal === 'other') {
                    const customVal = customInput.value.trim();
                    if (!customVal) {
                        e.preventDefault();
                        goToStep(1);
                        setTimeout(() => {
                            customInput.style.borderColor = '#dc2626';
                            customInput.focus();
                            showError('Vui lòng nhập tên môn học cụ thể!');
                        }, 300);
                        return false;
                    }
                }
                
                // Kiểm tra file PDF (nếu chọn upload)
                const method = document.querySelector('input[name="input_method"]:checked');
                if (method && method.value === 'upload') {
                    const file = fileInput.files[0];
                    if (!file) {
                        e.preventDefault();
                        showError('Vui lòng chọn file PDF để tải lên!');
                        dropZone.style.borderColor = '#dc2626';
                        return false;
                    }
                    if (file.size > 10 * 1024 * 1024) {
                        e.preventDefault();
                        showError('Dung lượng file vượt quá 10MB!');
                        return false;
                    }
                }

                // Đổi trạng thái nút submit
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tải file lên...';
                submitBtn.style.pointerEvents = 'none';
                submitBtn.style.opacity = '0.8';
            });
        });
    </script>
=======
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

>>>>>>> ab9a31091b98369af41f8f9bb34fe5bab4437cf8
</body>
</html>