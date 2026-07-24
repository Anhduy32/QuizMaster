<?php
// Lấy thông tin kết nối từ biến môi trường (nếu có trên Render), nếu không thì dùng mặc định cho local
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') ?: "";
$database = getenv('DB_NAME') ?: "quiz_management";
$port = getenv('DB_PORT') ?: 3306; // Mặc định MySQL là 3306, nếu dùng PostgreSQL trên Render có thể khác

// Kết nối đến Database (Thêm port nếu cần)
$conn = new mysqli($servername, $username, $password, $database, $port);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>