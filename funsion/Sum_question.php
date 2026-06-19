<?php
session_start();
include '../config/database.php';
include '../require_profile_update.php';

$nganh = $_GET['nganh'] ?? '';
$subject_id = $_GET['subject_id'] ?? '';
$so_luong_cau = ['Dễ' => 0, 'Khá' => 0, 'Khó' => 0];
$sql_nganh = "SELECT DISTINCT department FROM subjects";
$ds_nganh = $conn->query($sql_nganh);
$ds_bo_mon = [];

if ($nganh) {
    $sql_subjects = "SELECT id, name FROM subjects WHERE department = ?";
    $stmt = $conn->prepare($sql_subjects);
    if (!$stmt) {
        die("Lỗi khi truy vấn bộ môn: " . $conn->error);
    }
    $stmt->bind_param('s', $nganh);
    $stmt->execute();
    $ds_bo_mon = $stmt->get_result();
}

if ($subject_id) {
    $sql_dem = "SELECT difficulty, COUNT(*) as tong FROM create_questions WHERE subject_id = ? GROUP BY difficulty";
    $stmt2 = $conn->prepare($sql_dem);
    if (!$stmt2) {
        die("Lỗi thống kê câu hỏi: " . $conn->error);
    }
    $stmt2->bind_param('i', $subject_id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    while ($row = $result->fetch_assoc()) {
        $so_luong_cau[$row['difficulty']] = $row['tong'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê số câu hỏi theo Bộ môn</title>
    <link rel="stylesheet" href="../css/style.css?v=<?= time(); ?>">   
    <script>
        function filterQuestionsByDifficulty() {
            var difficulty = document.getElementById("difficulty").value;
            var questions = document.querySelectorAll(".question-item");

            questions.forEach(function(question) {
                question.style.display = 'none';
            });

            if (difficulty) {
                document.querySelectorAll(".question-item[data-difficulty='" + difficulty + "']").forEach(function(question) {
                    question.style.display = 'block';
                });
            } else {
                questions.forEach(function(question) {
                    question.style.display = 'block';
                });
            }
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Thống kê số câu hỏi theo Bộ môn</h1>

    <form method="GET">
        <label for="nganh">Chọn ngành:</label>
        <select name="nganh" id="nganh" onchange="this.form.submit()">
            <option value="">-- Chọn ngành --</option>
            <?php while ($row = $ds_nganh->fetch_assoc()): ?>
                <option value="<?= $row['department'] ?>" <?= ($row['department'] === $nganh) ? 'selected' : '' ?>>
                    <?= $row['department'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <?php if ($nganh && $ds_bo_mon): ?>
            <label for="subject_id">Chọn bộ môn:</label>
            <select name="subject_id" id="subject_id" onchange="this.form.submit()">
                <option value="">-- Chọn bộ môn --</option>
                <?php while ($row = $ds_bo_mon->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= ($row['id'] == $subject_id) ? 'selected' : '' ?>>
                        <?= $row['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        <?php endif; ?>
    </form>

    <?php if ($subject_id): ?>
        <div class="thong-ke">
            <h3>Thống kê câu hỏi đã có:</h3>
            <ul>
                <li>Dễ: <?= $so_luong_cau['Dễ'] ?> câu</li>
                <li>Khá: <?= $so_luong_cau['Khá'] ?> câu</li>
                <li>Khó: <?= $so_luong_cau['Khó'] ?> câu</li>
            </ul>

            <label for="difficulty">Lọc câu hỏi theo độ khó:</label>
            <select id="difficulty" onchange="filterQuestionsByDifficulty()">
                <option value="">-- Tất cả --</option>
                <option value="Dễ">Dễ</option>
                <option value="Khá">Khá</option>
                <option value="Khó">Khó</option>
            </select>

            <h3>Danh sách câu hỏi:</h3>
            <?php
            $sql_questions = "SELECT id, question_text, difficulty, image FROM create_questions WHERE subject_id = ?";
            $stmt_questions = $conn->prepare($sql_questions);
            if (!$stmt_questions) {
                die("Lỗi truy vấn câu hỏi: " . $conn->error);
            }
            $stmt_questions->bind_param('i', $subject_id);
            $stmt_questions->execute();
            $questions_result = $stmt_questions->get_result();
            while ($question = $questions_result->fetch_assoc()) {
                echo "<div class='question-item' data-difficulty='" . htmlspecialchars($question['difficulty']) . "'>";
                echo "<p><strong>Câu hỏi:</strong> " . htmlspecialchars($question['question_text']) . "</p>";

                // ✅ Hiển thị ảnh nếu có
                if (!empty($question['image'])) {
                    echo "<p><img src='" . htmlspecialchars($question['image']) . "' alt='Ảnh minh họa' style='max-width:300px; height:auto; border:1px solid #ccc; margin:10px 0;'></p>";
                }

                echo "<a href='edit_question.php?id=" . $question['id'] . "'><button>Xem đáp án</button></a>";
                echo "</div><hr>";
            }
            ?>
        </div>
    <?php endif; ?>

    <br>
    <a href="../Index.php"><button>Quay lại</button></a>
</div>
</body>
</html>
