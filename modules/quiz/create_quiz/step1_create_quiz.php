<?php
session_start();
include '../../../config/database.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$error_message = isset($_GET['error']) ? $_GET['error'] : '';

// ================= PAGE CONFIG =================
$page_css = 'create_quiz.css'; 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
</body>
</html>