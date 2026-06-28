<?php
session_start();
include '../../config/database.php';
if (!isset($_SESSION['username'])) { header("Location: ../login/login.php"); exit(); }
$username = $_SESSION['username'];
$query = "SELECT * FROM quizzes WHERE creator_username = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query); $stmt->bind_param("s", $username); $stmt->execute();
$my_quizzes = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thư viện của tôi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
</head>
<body>
    <div class="container">
        <div class="page-header flex-between">
            <div>
                <h2 class="page-title"><i class="fas fa-book"></i> Thư viện của tôi</h2>
                <a href="add_question_hub.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: inline-block; margin-top: 5px;">&larr; Về Trung tâm quản lý</a>
            </div>
            <a href="create_quiz/step1_create_quiz.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tạo đề thi mới</a>
        </div>

        <div class="table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Tên đề thi</th>
                        <th>Môn học</th>
                        <th>Số câu</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($my_quizzes->num_rows > 0): while($quiz = $my_quizzes->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight: 600; font-size: 1.05rem;"><?php echo htmlspecialchars($quiz['title']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['subject']); ?></td>
                        <td><?php echo $quiz['num_questions']; ?></td>
                        <td>
                            <?php if($quiz['status'] == 'completed'): ?>
                                <span class="badge badge-success">Đã xuất bản</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Bản nháp</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-success btn-icon" title="Làm thử"><i class="fas fa-play"></i></a>
                            <a href="create_quiz/step2_add_questions.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-primary btn-icon" style="background: var(--warning);" title="Sửa/Thêm câu hỏi"><i class="fas fa-edit"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="text-center" style="padding: 40px; color: var(--text-muted);">Bạn chưa tạo đề thi nào. Hãy tạo bộ đề đầu tiên!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>