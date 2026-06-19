<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once 'config/database.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT full_name, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (empty($user['full_name']) || empty($user['role'])) {
    echo "<script>
        alert('Vui lòng cập nhật thông tin trước khi sử dụng chức năng này!');
        window.location.href = 'http://localhost/DoAnThucTap/funsion/update_profile.php';
    </script>";
    exit();
}
?>
