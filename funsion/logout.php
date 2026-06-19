<?php
session_start(); // Bắt đầu phiên làm việc
session_unset(); // Xóa tất cả các biến session
session_destroy();  // Hủy session  
header('Location: http://localhost/DoAnThucTap/funsion/login.php');  // Chuyển hướng về trang đăng nhập
exit();
?>
