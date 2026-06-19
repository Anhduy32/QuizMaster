<?php
session_start();
include '../config/database.php'; 


$id = $_GET['id'] ?? 0;


if ($id > 0) {
    $sql_delete = "DELETE FROM subjects WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Xóa bộ môn thành công!'); window.location.href='manage_subjects.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi xóa bộ môn: " . $conn->error . "'); window.location.href='manage_subjects.php';</script>";
    }
} else {
    echo "<script>alert('ID không hợp lệ!'); window.location.href='manage_subjects.php';</script>";
}
?>
