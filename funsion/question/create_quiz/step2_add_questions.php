<?php
session_start();
include '../../../config/database.php';

if (!isset($_SESSION['username'])) { header('Location: ../login/login.php'); exit(); }

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
if (!$quiz_id) { header('Location: step1_create_quiz.php'); exit(); }

$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ? AND creator_username = ?");
$stmt->bind_param('is', $quiz_id, $_SESSION['username']);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
if (!$quiz) { header('Location: step1_create_quiz.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_questions'])) {
    $cau_hoi_arr = $_POST['questions'] ?? [];
    $so_cau = count($cau_hoi_arr);
    
    $conn->begin_transaction();
    try {
        $conn->query("UPDATE quizzes SET num_questions = $so_cau, status = 'completed' WHERE id = $quiz_id");
        // Xóa sạch câu hỏi cũ trong DB để đồng bộ lại với mảng mới nhất từ Form
        $conn->query("DELETE FROM questions WHERE quiz_id = $quiz_id");
        
        $stmt_q = $conn->prepare("INSERT INTO questions (quiz_id, content, opt_a, opt_b, opt_c, opt_d, correct_opt) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($cau_hoi_arr as $q) {
            if (!empty(trim($q['content']))) {
                $stmt_q->bind_param('issssss', $quiz_id, trim($q['content']), trim($q['opt_a']), trim($q['opt_b']), trim($q['opt_c']), trim($q['opt_d']), $q['correct']);
                $stmt_q->execute();
            }
        }
        $conn->commit();
        header("Location: success.php?quiz_id=$quiz_id");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        // Xử lý lỗi
    }
}

// KÉO DANH SÁCH CÂU HỎI CŨ LÊN
$existing_questions = [];
$res = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY id");
while ($row = $res->fetch_assoc()) { 
    $existing_questions[] = $row; 
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bước 2: Soạn câu hỏi - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/create_quiz.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="creator-header">
        <div class="container header-wrapper">
            <a href="step1_create_quiz.php" class="back-link"><i class="fas fa-arrow-left"></i> Cấu hình lại</a>
            <div class="steps">
                <div class="step completed"><div class="step-number"><i class="fas fa-check"></i></div><div class="step-label">Thông tin</div></div>
                <div class="step-line active"></div>
                <div class="step active"><div class="step-number">2</div><div class="step-label">Câu hỏi</div></div>
                <div class="step-line"></div>
                <div class="step"><div class="step-number">3</div><div class="step-label">Hoàn tất</div></div>
            </div>
        </div>
    </div>

    <div class="creator-main">
        <div class="container">
            <div class="quiz-info-banner">
                <div class="quiz-info-content">
                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                    <div class="quiz-meta">
                        <span><i class="fas fa-book"></i> Môn: <?php echo htmlspecialchars($quiz['subject']); ?></span>
                        <span><i class="fas fa-clock"></i> <?php echo $quiz['time_limit']; ?> phút</span>
                    </div>
                </div>
                <button type="button" class="btn-add-question" onclick="addQuestion()">
                    <i class="fas fa-plus"></i> Câu hỏi mới
                </button>
            </div>

            <form method="POST" id="questionsForm">
                <div id="questions-container">
                    
                    <?php foreach ($existing_questions as $idx => $q): ?>
                        <div class="question-card" id="q-card-<?php echo $idx; ?>">
                            <div class="question-header">
                                <div class="question-number">Câu hỏi <span class="q-num-text"><?php echo $idx + 1; ?></span></div>
                                <button type="button" class="btn-delete" onclick="removeQuestion(<?php echo $idx; ?>)" title="Xóa"><i class="fas fa-trash"></i></button>
                            </div>
                            <textarea name="questions[<?php echo $idx; ?>][content]" class="question-content" placeholder="Nhập nội dung câu hỏi..." required><?php echo htmlspecialchars($q['content']); ?></textarea>
                            
                            <div class="options-grid">
                                <?php foreach(['A', 'B', 'C', 'D'] as $opt): 
                                    $is_correct = ($q['correct_opt'] == $opt) ? 'checked' : '';
                                    $opt_value = htmlspecialchars($q['opt_' . strtolower($opt)]);
                                ?>
                                    <div class="option-item">
                                        <label class="option-radio">
                                            <input type="radio" name="questions[<?php echo $idx; ?>][correct]" value="<?php echo $opt; ?>" <?php echo $is_correct; ?> required>
                                            <span><?php echo $opt; ?></span>
                                        </label>
                                        <input type="text" name="questions[<?php echo $idx; ?>][opt_<?php echo strtolower($opt); ?>]" class="option-input" placeholder="Nhập đáp án <?php echo $opt; ?>" value="<?php echo $opt_value; ?>" required>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

                <div class="form-actions-sticky">
                    <div style="font-weight: 700; color: var(--text-muted);">
                        Tổng số: <span id="q-counter" style="color: var(--primary-teal); font-size: 1.2rem;">0</span> câu
                    </div>
                    <button type="submit" name="save_questions" class="btn-primary" style="width: auto; padding: 14px 40px;">
                        Lưu Đề & Hoàn Tất <i class="fas fa-check-circle"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // CẬP NHẬT: Cho JS biết đã có sẵn bao nhiêu câu từ DB
        let questionCount = <?php echo count($existing_questions); ?>;
        
        function addQuestion() {
            const container = document.getElementById('questions-container');
            const idx = questionCount;
            
            const card = document.createElement('div');
            card.className = 'question-card';
            card.id = `q-card-${idx}`;
            
            card.innerHTML = `
                <div class="question-header">
                    <div class="question-number">Câu hỏi <span class="q-num-text">${idx + 1}</span></div>
                    <button type="button" class="btn-delete" onclick="removeQuestion(${idx})" title="Xóa"><i class="fas fa-trash"></i></button>
                </div>
                <textarea name="questions[${idx}][content]" class="question-content" placeholder="Nhập nội dung câu hỏi..." required></textarea>
                <div class="options-grid">
                    ${['A', 'B', 'C', 'D'].map(opt => `
                        <div class="option-item">
                            <label class="option-radio">
                                <input type="radio" name="questions[${idx}][correct]" value="${opt}" required>
                                <span>${opt}</span>
                            </label>
                            <input type="text" name="questions[${idx}][opt_${opt.toLowerCase()}]" class="option-input" placeholder="Nhập đáp án ${opt}" required>
                        </div>
                    `).join('')}
                </div>
            `;
            
            container.appendChild(card);
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            questionCount++;
            updateCounter();
        }

        function removeQuestion(idx) {
            const card = document.getElementById(`q-card-${idx}`);
            if(card && confirm('Bạn muốn xóa câu hỏi này?')) {
                card.style.transform = 'scale(0.95)';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    questionCount--;
                    updateCounter();
                    reindexQuestions();
                }, 300);
            }
        }

        function reindexQuestions() {
            const cards = document.querySelectorAll('.question-card');
            cards.forEach((card, index) => {
                card.querySelector('.q-num-text').textContent = index + 1;
                card.querySelector('.question-content').name = `questions[${index}][content]`;
                card.querySelectorAll('input[type="radio"]').forEach(radio => radio.name = `questions[${index}][correct]`);
                ['a','b','c','d'].forEach(opt => {
                    card.querySelector(`input[placeholder="Nhập đáp án ${opt.toUpperCase()}"]`).name = `questions[${index}][opt_${opt}]`;
                });
            });
        }

        function updateCounter() {
            document.getElementById('q-counter').innerText = questionCount;
        }

        window.onload = () => { 
            updateCounter();
            // Chỉ tự động tạo câu hỏi trống nếu đề chưa có câu nào
            if(questionCount === 0) addQuestion(); 
        }
    </script>
</body>
</html>