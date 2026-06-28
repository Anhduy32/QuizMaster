<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: ../login/login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung tâm Quản lý - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/dashboard.css"> <style>
        /* CSS riêng đặc thù cho trang Hub */
        .hub-icon { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px; transition: transform 0.3s; }
        .card:hover .hub-icon { transform: scale(1.1); }
        .icon-blue { background: #e0e7ff; color: var(--primary); }
        .icon-green { background: #d1fae5; color: var(--success); }
        .icon-purple { background: #f3e8ff; color: #9333ea; }
        .hub-link { text-decoration: none; color: inherit; display: block; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <div class="text-center mb-4">
            <h2 class="page-title">Không gian làm việc</h2>
            <p class="page-desc">Chọn một hành động để bắt đầu xây dựng kho tàng kiến thức của bạn</p>
        </div>

        <div class="grid-3 mt-4">
            <a href="create_quiz/step1_create_quiz.php" class="hub-link">
                <div class="card text-center">
                    <div class="hub-icon icon-blue"><i class="fas fa-magic"></i></div>
                    <h3>Tạo Đề Thi Mới</h3>
                    <p class="page-desc">Khởi tạo bộ đề bằng thủ công hoặc tự động đọc từ file PDF/Word.</p>
                </div>
            </a>
            
            <a href="my_library.php" class="hub-link">
                <div class="card text-center">
                    <div class="hub-icon icon-green"><i class="fas fa-book"></i></div>
                    <h3>Thư Viện Của Tôi</h3>
                    <p class="page-desc">Quản lý, chỉnh sửa và theo dõi các đề thi bạn đã xuất bản.</p>
                </div>
            </a>
            
            <a href="sum_question.php" class="hub-link">
                <div class="card text-center">
                    <div class="hub-icon icon-purple"><i class="fas fa-globe"></i></div>
                    <h3>Khám Phá Cộng Đồng</h3>
                    <p class="page-desc">Tìm kiếm đề thi từ các giáo viên và học viên khác trên toàn quốc.</p>
                </div>
            </a>
        </div>
        
        <div class="text-center mt-4" style="padding-top: 30px;">
            <a href="../../home.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Trở về Bảng Điều Khiển</a>
        </div>
    </div>
</body>
</html>