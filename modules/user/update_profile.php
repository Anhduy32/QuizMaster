<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../login/login.php');
    exit();
}

include '../../config/database.php';

$ten_dang_nhap = $_SESSION['username'];

// 1. LẤY THÔNG TIN CÁ NHÂN
$truy_van = "SELECT * FROM users WHERE username = ?";
$chuan_bi = $conn->prepare($truy_van);
$chuan_bi->bind_param('s', $ten_dang_nhap);
$chuan_bi->execute();
$nguoi_dung = $chuan_bi->get_result()->fetch_assoc();

$ho_va_ten = $nguoi_dung['full_name'] ?? $ten_dang_nhap;
$ngay_sinh = $nguoi_dung['birthdate'] ?? '';
$gioi_tinh = $nguoi_dung['gender'] ?? '';
$dia_chi   = $nguoi_dung['address'] ?? '';
$email     = $nguoi_dung['email'] ?? 'Chưa cập nhật Email';
$chuc_vu   = $nguoi_dung['role'] ?? 'user';
$mon_yeu_thich_str = $nguoi_dung['favorite_subjects'] ?? '';

$avatar_url = !empty($nguoi_dung['picture']) 
    ? $nguoi_dung['picture'] 
    : "https://ui-avatars.com/api/?name=" . urlencode($ho_va_ten) . "&background=0f5c6b&color=fff&size=150";

// 2. LẤY KHO ĐỀ THI ĐÃ TẠO
$truy_van_de_thi = "SELECT * FROM quizzes WHERE creator_username = ? ORDER BY created_at DESC";
$stmt_de_thi = $conn->prepare($truy_van_de_thi);
$stmt_de_thi->bind_param('s', $ten_dang_nhap);
$stmt_de_thi->execute();
$ket_qua_de_thi = $stmt_de_thi->get_result();
$tong_de_da_tao = $ket_qua_de_thi->num_rows;

// 3. LẤY LỊCH SỬ LÀM BÀI
$truy_van_lich_su = "
    SELECT q.title, q.subject, h.score, h.total_score, h.completed_at 
    FROM quiz_history h 
    JOIN quizzes q ON h.quiz_id = q.id 
    WHERE h.username = ? 
    ORDER BY h.completed_at DESC
    LIMIT 10";
$stmt_lich_su = $conn->prepare($truy_van_lich_su);
$stmt_lich_su->bind_param('s', $ten_dang_nhap);
$stmt_lich_su->execute();
$ket_qua_lich_su = $stmt_lich_su->get_result();
$tong_luot_thi = $ket_qua_lich_su->num_rows;

// Tính điểm trung bình
$diem_tb = 0;
$temp_lich_su = [];
if ($tong_luot_thi > 0) {
    $tong_diem = 0;
    while ($row = $ket_qua_lich_su->fetch_assoc()) {
        $tong_diem += $row['score'];
        $temp_lich_su[] = $row;
    }
    $diem_tb = round($tong_diem / $tong_luot_thi, 1);
}

// 4. XỬ LÝ FORM CẬP NHẬT
$thong_bao = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cap_nhat'])) {
    $ho_va_ten_moi = trim($_POST['full_name']);
    $ngay_sinh_moi = trim($_POST['birthdate']);
    $gioi_tinh_moi = trim($_POST['gender']);
    $dia_chi_moi   = trim($_POST['address']);
    $mon_yeu_thich_moi = trim($_POST['favorite_subjects']);

    $truy_van_cap_nhat = "UPDATE users SET full_name=?, birthdate=?, gender=?, address=?, favorite_subjects=? WHERE username=?";
    $chuan_bi_cap_nhat = $conn->prepare($truy_van_cap_nhat);
    $chuan_bi_cap_nhat->bind_param('ssssss', $ho_va_ten_moi, $ngay_sinh_moi, $gioi_tinh_moi, $dia_chi_moi, $mon_yeu_thich_moi, $ten_dang_nhap);

    if ($chuan_bi_cap_nhat->execute()) {
        $thong_bao = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Cập nhật hồ sơ thành công!</div>';
        // Cập nhật session
        $_SESSION['full_name'] = $ho_va_ten_moi;
        // Refresh lại trang để hiển thị thông tin mới
        echo "<script>setTimeout(function(){ window.location.href = window.location.pathname; }, 1500);</script>";
    } else {
        $thong_bao = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Lỗi: ' . htmlspecialchars($conn->error) . '</div>';
    }
}

function getSubjectStyle($subject) {
    $sub = mb_strtolower($subject, 'UTF-8');
    if (strpos($sub, 'toán') !== false) return ['bg' => 'bg-math', 'badge' => 'badge-blue', 'icon' => 'fa-square-root-alt'];
    if (strpos($sub, 'lý') !== false || strpos($sub, 'vật') !== false) return ['bg' => 'bg-physics', 'badge' => 'badge-orange', 'icon' => 'fa-atom'];
    if (strpos($sub, 'hóa') !== false) return ['bg' => 'bg-chemistry', 'badge' => 'badge-green', 'icon' => 'fa-flask'];
    if (strpos($sub, 'anh') !== false) return ['bg' => 'bg-english', 'badge' => 'badge-red', 'icon' => 'fa-language'];
    if (strpos($sub, 'văn') !== false) return ['bg' => 'bg-literature', 'badge' => 'badge-purple', 'icon' => 'fa-book-open'];
    if (strpos($sub, 'sử') !== false || strpos($sub, 'địa') !== false) return ['bg' => 'bg-history', 'badge' => 'badge-orange', 'icon' => 'fa-landmark'];
    return ['bg' => 'bg-science', 'badge' => 'badge-green', 'icon' => 'fa-flask'];
}

// ================= PAGE CONFIG =================
$page_title = 'Hồ sơ cá nhân - QuizMaster';
$page_css = 'profile.css';

require_once '../../includes/layouts/header.php';
?>

<main class="main-wrapper">
    <div class="profile-wrapper">
        
        <!-- ===== PROFILE HEADER ===== -->
        <div class="glass-panel">
            <div class="profile-cover"></div>
            <div class="profile-main-info">
                <div class="user-identity">
                    <div class="avatar-wrapper">
                        <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Avatar" class="profile-avatar">
                        <div class="avatar-badge"><i class="fas fa-gem"></i></div>
                    </div>
                    <div class="user-text">
                        <h1><?php echo htmlspecialchars($ho_va_ten); ?></h1>
                        <p>
                            <i class="fas fa-check-circle" style="color: var(--accent-teal);"></i> 
                            <?php 
                                if($chuc_vu == 'admin') echo 'Quản trị viên hệ thống';
                                elseif($chuc_vu == 'giaovien') echo 'Giảng viên chuyên môn';
                                else echo 'Thành viên học tập tích cực';
                            ?>
                        </p>
                    </div>
                </div>

                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-num"><?php echo $tong_luot_thi; ?></span>
                        <span class="stat-label">Lượt thi</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-num"><?php echo $diem_tb; ?></span>
                        <span class="stat-label">Điểm TB</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-num"><?php echo $tong_de_da_tao; ?></span>
                        <span class="stat-label">Đề đã tạo</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== PROFILE BODY ===== -->
        <div class="profile-body">
            
            <!-- SIDEBAR -->
            <div class="glass-panel sidebar-card">
                <h3 class="sidebar-title"><i class="fas fa-address-card" style="color: var(--primary-teal);"></i> Thông tin liên hệ</h3>
                <ul class="info-list">
                    <li><span class="info-icon"><i class="fas fa-envelope"></i></span> <?php echo htmlspecialchars($email); ?></li>
                    <li><span class="info-icon"><i class="fas fa-birthday-cake"></i></span> <?php echo !empty($ngay_sinh) ? date('d/m/Y', strtotime($ngay_sinh)) : 'Chưa cập nhật'; ?></li>
                    <li><span class="info-icon"><i class="fas fa-venus-mars"></i></span> <?php echo !empty($gioi_tinh) ? htmlspecialchars($gioi_tinh) : 'Chưa thiết lập'; ?></li>
                    <li><span class="info-icon"><i class="fas fa-map-marked-alt"></i></span> <?php echo !empty($dia_chi) ? htmlspecialchars($dia_chi) : 'Chưa cập nhật địa chỉ'; ?></li>
                </ul>

                <h3 class="sidebar-title" style="margin-top: 30px;"><i class="fas fa-star" style="color: #f59e0b;"></i> Môn học yêu thích</h3>
                <div class="skill-tags">
                    <?php if (!empty($mon_yeu_thich_str)): 
                        $mon_hocs = explode(',', $mon_yeu_thich_str);
                        foreach ($mon_hocs as $mon): ?>
                            <span class="tag"><?php echo htmlspecialchars(trim($mon)); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="font-size: 0.9rem; color: #718096;">Chưa có môn học yêu thích.</span>
                    <?php endif; ?>
                </div>

                <button class="btn-edit-profile" onclick="openTab(event, 'update')">
                    <i class="fas fa-sliders-h"></i> Cài đặt tài khoản
                </button>

                <a href="../../home.php" class="btn-edit-profile btn-edit-profile-outline" style="margin-top: 10px;">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>

                <a href="../login/logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </div>

            <!-- CONTENT -->
            <div class="glass-panel content-card">
                <div class="tabs-header">
                    <button class="tab-btn active" onclick="openTab(event, 'created')">
                        <i class="fas fa-layer-group"></i> Kho đề thi (<?php echo $tong_de_da_tao; ?>)
                    </button>
                    <button class="tab-btn" onclick="openTab(event, 'history')">
                        <i class="fas fa-chart-line"></i> Lịch sử (<?php echo $tong_luot_thi; ?>)
                    </button>
                    <button class="tab-btn" id="tabUpdateBtn" onclick="openTab(event, 'update')">
                        <i class="fas fa-user-edit"></i> Cập nhật
                    </button>
                </div>

                <!-- TAB: KHO ĐỀ THI -->
                <div id="created" class="tab-content active">
                    <?php if ($tong_de_da_tao > 0): 
                        // Reset result set để duyệt lại
                        $ket_qua_de_thi->data_seek(0);
                        while ($quiz = $ket_qua_de_thi->fetch_assoc()): 
                            $style = getSubjectStyle($quiz['subject']); 
                    ?>
                        <div class="quiz-item">
                            <div class="quiz-info-group">
                                <div class="quiz-thumb <?php echo $style['bg']; ?>">
                                    <i class="fas <?php echo $style['icon']; ?>"></i>
                                </div>
                                <div>
                                    <div class="quiz-tags">
                                        <span class="q-badge <?php echo $style['badge']; ?>">
                                            <?php echo htmlspecialchars($quiz['subject']); ?>
                                        </span>
                                        <span class="q-badge <?php echo $quiz['status'] === 'completed' ? 'badge-green' : 'badge-orange'; ?>">
                                            <?php echo $quiz['status'] === 'completed' ? '✅ Đã xuất bản' : '📝 Bản nháp'; ?>
                                        </span>
                                    </div>
                                    <a href="#" class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></a>
                                    <div class="quiz-meta">
                                        <span><i class="fas fa-question-circle"></i> <?php echo (int)$quiz['num_questions']; ?> câu</span>
                                        <span><i class="fas fa-clock"></i> <?php echo $quiz['time_limit'] ?? 15; ?> phút</span>
                                        <span><i class="fas fa-users"></i> <?php echo (int)$quiz['views']; ?> lượt</span>
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="btn-outline">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                        </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            <h3>Bạn chưa tạo đề thi nào</h3>
                            <p>Hãy đóng góp kiến thức cho cộng đồng ngay hôm nay!</p>
                            <a href="create_quiz/step1_create_quiz.php" class="btn-edit-profile" style="width: auto; padding: 12px 30px; margin-top: 16px;">
                                <i class="fas fa-plus"></i> Tạo đề thi mới
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB: LỊCH SỬ -->
                <div id="history" class="tab-content">
                    <?php if ($tong_luot_thi > 0): ?>
                        <?php foreach ($temp_lich_su as $history): 
                            $style = getSubjectStyle($history['subject']);
                        ?>
                            <div class="quiz-item">
                                <div class="quiz-info-group">
                                    <div class="quiz-thumb <?php echo $style['bg']; ?>">
                                        <i class="fas <?php echo $style['icon']; ?>"></i>
                                    </div>
                                    <div>
                                        <div class="quiz-tags">
                                            <span class="q-badge badge-green"><i class="fas fa-check-circle"></i> Hoàn thành</span>
                                        </div>
                                        <a href="#" class="quiz-title"><?php echo htmlspecialchars($history['title']); ?></a>
                                        <div class="quiz-meta">
                                            <span><i class="fas fa-calendar-check"></i> <?php echo date('d/m/Y H:i', strtotime($history['completed_at'])); ?></span>
                                            <span style="color: #38a169; font-weight: 700;">
                                                <i class="fas fa-star"></i> Điểm: <?php echo $history['score'] . '/' . $history['total_score']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <a href="#" class="btn-outline">
                                    <i class="fas fa-eye"></i> Xem bài giải
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-history"></i>
                            <h3>Chưa có dữ liệu học tập</h3>
                            <p>Các bài kiểm tra bạn tham gia sẽ xuất hiện ở đây.</p>
                            <a href="../quiz/take_quiz.php" class="btn-edit-profile" style="width: auto; padding: 12px 30px; margin-top: 16px; background: #f59e0b;">
                                <i class="fas fa-play"></i> Bắt đầu thi thử
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB: CẬP NHẬT -->
                <div id="update" class="tab-content">
                    <h2 style="color: var(--primary-teal); margin-bottom: 8px; font-weight: 800; font-size: 1.3rem;">
                        <i class="fas fa-user-edit"></i> Chỉnh sửa thông tin cá nhân
                    </h2>
                    <p style="color: var(--text-muted); margin-bottom: 24px; font-size: 0.95rem;">
                        Cập nhật thông tin của bạn để nhận được trải nghiệm tốt nhất.
                    </p>
                    
                    <?php echo $thong_bao; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="cap_nhat" value="1">

                        <div class="update-grid">
                            <div class="form-group full-width">
                                <label class="form-label" for="full_name">Họ và tên hiển thị <span style="color: #e53e3e;">*</span></label>
                                <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($ho_va_ten); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="birthdate">Ngày tháng năm sinh</label>
                                <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?php echo htmlspecialchars($ngay_sinh); ?>" max="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="gender">Giới tính</label>
                                <select id="gender" name="gender" class="form-control">
                                    <option value="" <?php echo empty($gioi_tinh) ? 'selected' : ''; ?>>Chọn giới tính</option>
                                    <option value="Nam" <?php echo $gioi_tinh === 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo $gioi_tinh === 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="Khác" <?php echo $gioi_tinh === 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label class="form-label" for="address">Nơi ở hiện tại (Địa chỉ)</label>
                                <input type="text" id="address" name="address" class="form-control" placeholder="Ví dụ: Quận 1, TP. Hồ Chí Minh" value="<?php echo htmlspecialchars($dia_chi); ?>">
                            </div>

                            <div class="form-group full-width">
                                <label class="form-label" for="favorite_subjects">Môn học yêu thích <span style="font-weight: 400; color: var(--text-muted); font-size: 0.8rem;">(Ngăn cách bằng dấu phẩy)</span></label>
                                <input type="text" id="favorite_subjects" name="favorite_subjects" class="form-control" placeholder="Ví dụ: Toán, Lý, Hóa, Tiếng Anh" value="<?php echo htmlspecialchars($mon_yeu_thich_str); ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn-edit-profile" style="width: auto; padding: 14px 40px;">
                            <i class="fas fa-save"></i> Lưu thông tin
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</main>

<script>
    function openTab(evt, tabName) {
        // Ẩn tất cả tab content
        var tabcontent = document.getElementsByClassName("tab-content");
        for (var i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.remove("active");
            tabcontent[i].style.display = "none";
        }
        
        // Xóa active của tất cả tab button
        var tablinks = document.getElementsByClassName("tab-btn");
        for (var i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        
        // Hiển thị tab được chọn
        var activeTab = document.getElementById(tabName);
        if (activeTab) {
            activeTab.style.display = "block";
            setTimeout(function() {
                activeTab.classList.add("active");
            }, 10);
        }
        
        // Active tab button
        if (evt && evt.currentTarget) {
            evt.currentTarget.classList.add("active");
        } else {
            // Nếu không có event (gọi từ bên ngoài), tìm button tương ứng
            var buttons = document.getElementsByClassName("tab-btn");
            for (var i = 0; i < buttons.length; i++) {
                if (buttons[i].getAttribute("onclick") && buttons[i].getAttribute("onclick").includes(tabName)) {
                    buttons[i].classList.add("active");
                    break;
                }
            }
        }
    }

    // Mặc định mở tab đầu tiên
    document.addEventListener('DOMContentLoaded', function() {
        // Kiểm tra nếu có tham số tab trên URL
        var urlParams = new URLSearchParams(window.location.search);
        var tab = urlParams.get('tab');
        if (tab && document.getElementById(tab)) {
            openTab(null, tab);
        } else {
            // Mặc định mở tab "created"
            var defaultTab = document.querySelector('.tab-btn.active');
            if (defaultTab) {
                var tabName = defaultTab.getAttribute('onclick');
                if (tabName) {
                    var match = tabName.match(/openTab\(event,\s*['"]([^'"]+)['"]\)/);
                    if (match) {
                        openTab(null, match[1]);
                    }
                }
            }
        }
    });
</script>
