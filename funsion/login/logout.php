<?php
session_start();
session_destroy(); // Xóa sạch bộ nhớ đăng nhập
header("Location: ../../index.php"); // Quay về trang chủ viết thường
exit();
?>