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
$mon_yeu_thich_str = $nguoi_dung['favorite_subjects'] ?? ''; // Chuỗi môn học

$avatar_url = !empty($nguoi_dung['picture']) ? $nguoi_dung['picture'] : "https://ui-avatars.com/api/?name=" . urlencode($ho_va_ten) . "&background=2a5d6a&color=fff&size=150";

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
    ORDER BY h.completed_at DESC";
$stmt_lich_su = $conn->prepare($truy_van_lich_su);
$stmt_lich_su->bind_param('s', $ten_dang_nhap);
$stmt_lich_su->execute();
$ket_qua_lich_su = $stmt_lich_su->get_result();
$tong_luot_thi = $ket_qua_lich_su->num_rows;

// Tính điểm trung bình
$diem_tb = 0;
if ($tong_luot_thi > 0) {
    $tong_diem = 0;
    $temp_lich_su = []; // Lưu tạm để vòng lặp HTML bên dưới dùng lại
    while ($row = $ket_qua_lich_su->fetch_assoc()) {
        $tong_diem += $row['score'];
        $temp_lich_su[] = $row;
    }
    $diem_tb = round($tong_diem / $tong_luot_thi, 1);
}

// 4. XỬ LÝ FORM CẬP NHẬT
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cap_nhat'])) {
    $ho_va_ten_moi = trim($_POST['full_name']);
    $ngay_sinh_moi = trim($_POST['birthdate']);
    $gioi_tinh_moi = trim($_POST['gender']);
    $dia_chi_moi   = trim($_POST['address']);
    $mon_yeu_thich_moi = trim($_POST['favorite_subjects']); // Lấy môn học mới

    $truy_van_cap_nhat = "UPDATE users SET full_name=?, birthdate=?, gender=?, address=?, favorite_subjects=? WHERE username=?";
    $chuan_bi_cap_nhat = $conn->prepare($truy_van_cap_nhat);
    $chuan_bi_cap_nhat->bind_param('ssssss', $ho_va_ten_moi, $ngay_sinh_moi, $gioi_tinh_moi, $dia_chi_moi, $mon_yeu_thich_moi, $ten_dang_nhap);

    if ($chuan_bi_cap_nhat->execute()) {
        echo "<script>alert('Cập nhật hồ sơ thành công!'); window.location.href=window.location.pathname;</script>";
        exit();
    } else {
        echo "<script>alert('Lỗi: " . addslashes($conn->error) . "');</script>";
    }
}

// Hàm hỗ trợ render giao diện động (Màu sắc icon theo tên môn)
function getSubjectStyle($subject) {
    $sub = mb_strtolower($subject, 'UTF-8');
    if (strpos($sub, 'toán') !== false) return ['bg' => 'bg-math', 'badge' => 'badge-blue', 'icon' => 'fa-square-root-alt'];
    if (strpos($sub, 'lý') !== false || strpos($sub, 'địa') !== false) return ['bg' => 'bg-history', 'badge' => 'badge-orange', 'icon' => 'fa-landmark'];
    return ['bg' => 'bg-science', 'badge' => 'badge-green', 'icon' => 'fa-flask'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/profile.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="container">
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
                        <p><i class="fas fa-check-circle" style="color: var(--accent-teal);"></i> 
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
                </div>
            </div>
        </div>

        <div class="profile-body">
            
            <div class="glass-panel sidebar-card">
                <h3 class="sidebar-title">Liên hệ & Cá nhân</h3>
                <ul class="info-list">
                    <li><div class="info-icon"><i class="fas fa-envelope"></i></div> <?php echo htmlspecialchars($email); ?></li>
                    <li><div class="info-icon"><i class="fas fa-birthday-cake"></i></div> <?php echo !empty($ngay_sinh) ? date('d/m/Y', strtotime($ngay_sinh)) : 'Chưa cập nhật'; ?></li>
                    <li><div class="info-icon"><i class="fas fa-venus-mars"></i></div> <?php echo !empty($gioi_tinh) ? htmlspecialchars($gioi_tinh) : 'Chưa thiết lập'; ?></li>
                    <li><div class="info-icon"><i class="fas fa-map-marked-alt"></i></div> <?php echo !empty($dia_chi) ? htmlspecialchars($dia_chi) : 'Chưa cập nhật địa chỉ'; ?></li>
                </ul>

                <h3 class="sidebar-title" style="margin-top: 40px;">Môn học yêu thích</h3>
                <div class="skill-tags">
                    <?php 
                        if (!empty($mon_yeu_thich_str)) {
                            $mon_hocs = explode(',', $mon_yeu_thich_str);
                            foreach ($mon_hocs as $mon) {
                                echo '<span class="tag">'. htmlspecialchars(trim($mon)) .'</span>';
                            }
                        } else {
                            echo '<span style="font-size: 0.9rem; color: #718096;">Chưa có môn học yêu thích.</span>';
                        }
                    ?>
                </div>

                <button class="btn-edit-profile" onclick="openTab(event, 'update')">
                    <i class="fas fa-sliders-h"></i> Cài đặt tài khoản
                </button>

                <a href="../../index.php" class="btn-edit-profile" style="background: transparent; color: var(--primary-teal); border: 2px solid var(--primary-teal); margin-top: 15px;">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>

                <a href="../login/logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Thoát
                </a>
            </div>

            <div class="glass-panel content-card">
                <div class="tabs-header">
                    <button class="tab-btn active" onclick="openTab(event, 'created')"><i class="fas fa-layer-group"></i> Kho đề thi (<?php echo $tong_de_da_tao; ?>)</button>
                    <button class="tab-btn" onclick="openTab(event, 'history')"><i class="fas fa-chart-line"></i> Lịch sử học tập (<?php echo $tong_luot_thi; ?>)</button>
                    <button class="tab-btn" id="tabUpdateBtn" onclick="openTab(event, 'update')" style="display: none;">Cập nhật</button>
                </div>

                <div id="created" class="tab-content active">
                    <?php if ($tong_de_da_tao > 0): ?>
                        <?php while ($quiz = $ket_qua_de_thi->fetch_assoc()): 
                            $style = getSubjectStyle($quiz['subject']); 
                        ?>
                            <div class="quiz-item">
                                <div class="quiz-info-group">
                                    <div class="quiz-thumb <?php echo $style['bg']; ?>"><i class="fas <?php echo $style['icon']; ?>"></i></div>
                                    <div>
                                        <div class="quiz-tags">
                                            <span class="q-badge <?php echo $style['badge']; ?>"><?php echo htmlspecialchars($quiz['subject']); ?></span>
                                        </div>
                                        <a href="#" class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></a>
                                        <div class="quiz-meta">
                                            <span><i class="fas fa-question-circle"></i> <?php echo $quiz['num_questions']; ?> Câu</span>
                                            <span><i class="fas fa-clock"></i> <?php echo $quiz['time_limit']; ?> Phút</span>
                                            <span><i class="fas fa-users"></i> <?php echo $quiz['views']; ?> Lượt</span>
                                        </div>
                                    </div>
                                </div>
                                <a href="#" class="btn-outline">Sửa đề</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: #718096;">
                            <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                            <h3>Bạn chưa tạo đề thi nào.</h3>
                            <p>Hãy đóng góp kiến thức cho cộng đồng ngay hôm nay!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="history" class="tab-content">
                    <?php if ($tong_luot_thi > 0): ?>
                        <?php foreach ($temp_lich_su as $history): 
                            $style = getSubjectStyle($history['subject']);
                        ?>
                            <div class="quiz-item">
                                <div class="quiz-info-group">
                                    <div class="quiz-thumb <?php echo $style['bg']; ?>"><i class="fas <?php echo $style['icon']; ?>"></i></div>
                                    <div>
                                        <div class="quiz-tags"><span class="q-badge badge-green">Hoàn thành</span></div>
                                        <a href="#" class="quiz-title"><?php echo htmlspecialchars($history['title']); ?></a>
                                        <div class="quiz-meta">
                                            <span><i class="fas fa-calendar-check"></i> <?php echo date('d/m/Y H:i', strtotime($history['completed_at'])); ?></span>
                                            <span style="color: #38a169; font-weight: 800;"><i class="fas fa-star"></i> Điểm: <?php echo $history['score'] . '/' . $history['total_score']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <a href="#" class="btn-outline">Xem bài giải</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: #718096;">
                            <i class="fas fa-history" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                            <h3>Chưa có dữ liệu học tập.</h3>
                            <p>Các bài kiểm tra bạn tham gia sẽ xuất hiện ở đây.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="update" class="tab-content">
                    <h2 style="color: var(--primary-teal); margin-bottom: 25px; font-weight: 800;">Chỉnh sửa thông tin cá nhân</h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="cap_nhat" value="1">

                        <div class="update-grid">
                            <div class="form-group full-width">
                                <label class="form-label" for="full_name">Họ và tên hiển thị</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($ho_va_ten); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="birthdate">Ngày tháng năm sinh</label>
                                <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?php echo htmlspecialchars($ngay_sinh); ?>" max="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="gender">Giới tính</label>
                                <select id="gender" name="gender" class="form-control">
                                    <option value="" disabled <?php echo empty($gioi_tinh) ? 'selected' : ''; ?>>Chọn giới tính</option>
                                    <option value="Nam" <?php echo $gioi_tinh === 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo $gioi_tinh === 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="Khác" <?php echo $gioi_tinh === 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label class="form-label" for="address">Nơi ở hiện tại (Địa chỉ)</label>
                                <input type="text" id="address" name="address" class="form-control" placeholder="Ví dụ: Quận 1, TP. Hồ Chí Minh" value="<?php echo htmlspecialchars($dia_chi); ?>">
                            </div>

                            <div class="form-group full-width" style="margin-bottom: 10px;">
                                <label class="form-label" for="favorite_subjects">Môn học yêu thích (Ngăn cách bằng dấu phẩy)</label>
                                <input type="text" id="favorite_subjects" name="favorite_subjects" class="form-control" placeholder="Ví dụ: Toán, Lý, Hóa, Tiếng Anh" value="<?php echo htmlspecialchars($mon_yeu_thich_str); ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn-edit-profile" style="width: auto; padding: 15px 40px;">
                            <i class="fas fa-save"></i> Lưu thông tin
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabName).style.display = "block";
            setTimeout(() => document.getElementById(tabName).classList.add("active"), 10);
            if(evt.currentTarget.classList.contains('tab-btn')) {
                evt.currentTarget.classList.add("active");
            }
        }
    </script>
</body>
</html>