<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config/database.php';

$ten_dang_nhap = $_SESSION['username'];
$truy_van = "SELECT * FROM users WHERE username = ?";
$chuan_bi = $conn->prepare($truy_van);
$chuan_bi->bind_param('s', $ten_dang_nhap);
$chuan_bi->execute();
$ket_qua = $chuan_bi->get_result();
$nguoi_dung = $ket_qua->fetch_assoc();

$ho_va_ten = $nguoi_dung['full_name'] ?? '';
$chuc_vu = $nguoi_dung['role'] ?? ''; 
$quyen_quan_ly_bomon = ($chuc_vu === 'hieutruong' || $chuc_vu === 'hieupho');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <!-- FontAwesome icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><i class="fa-solid fa-book"></i> Quản lý Quiz</h2>
        <a href="funsion/update_profile.php"><i class="fa-solid fa-user"></i> Cập nhật thông tin</a>
        <a href="funsion/Add_Question.php"><i class="fa-solid fa-plus"></i> Thêm câu hỏi</a>
        <a href="funsion/Sum_question.php"><i class="fa-solid fa-list"></i> Số lượng câu hỏi đã có</a>
        <a href="funsion/Creates_Question.php"><i class="fa-solid fa-file-circle-plus"></i> Tạo đề trắc nghiệm</a>
        <?php if ($chuc_vu === 'giaovien') { echo '<a href="#"><i class="fa-solid fa-pen"></i> Quản lý câu hỏi</a>'; } ?>
        <?php if ($quyen_quan_ly_bomon) { ?>
            <div class="dropdown">
                <a href="javascript:void(0);" onclick="toggleDropdown()"><i class="fa-solid fa-layer-group"></i> Quản lý bộ môn</a>
                <div class="dropdown-content" id="dropdown-menu">
                    <a href="manage/manage_subjects.php">Bộ môn</a>
                </div>
            </div>
        <?php } ?>
        <a href="funsion/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
    </div>
    
    <!-- Nội dung chính -->
    <div class="main-content">
        <h1>Chào mừng, <?php echo htmlspecialchars($ho_va_ten); ?>!</h1>

        <!-- Box tạo mới -->
        <div class="content-box">
            <p>Chọn một trong các tùy chọn bên dưới để bắt đầu:</p>
            <div class="options">
                <div class="card">
                    <h3>Tạo Quiz</h3>
                    <p>Tạo bộ câu hỏi trắc nghiệm mới.</p>
                    <button class="btn"><a href="funsion/Creates_Question.php">Tạo ngay</a></button>
                </div>
                <div class="card">
                    <h3>Thêm câu hỏi</h3>
                    <p>Thêm câu hỏi vào hệ thống.</p>
                    <button class="btn"><a href="funsion/Add_Question.php">Thêm câu hỏi</a></button>
                </div>
            </div>
        </div>



    <script>
        function toggleDropdown() {
            var dropdown = document.getElementById("dropdown-menu");
            dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
        }
    </script>
</body>
</html>