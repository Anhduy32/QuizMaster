<?php
// Lấy thông tin từ biến môi trường trên Render
$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: "5432";
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASS');

try {
    // Chuỗi kết nối dành cho PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    
    // Thiết lập chế độ báo lỗi của PDO thành Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>