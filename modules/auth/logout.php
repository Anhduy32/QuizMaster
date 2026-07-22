<?php
session_start();
session_unset();
session_destroy();
// Đi lùi 2 cấp (từ modules/auth/) để về gốc thư mục dự án
header("Location: ../../index.php"); 
exit();
?>