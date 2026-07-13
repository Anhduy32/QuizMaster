<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query_quiz = "SELECT * FROM quizzes WHERE id = ?";
$stmt_quiz = $conn->prepare($query_quiz);
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$quiz = $stmt_quiz->get_result()->fetch_assoc();

if (!$quiz) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>Đề thi không tồn tại! <a href='../../home.php'>Về trang chủ</a></div>");
}

$query_questions = "SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC";
$stmt_q = $conn->prepare($query_questions);
$stmt_q->bind_param("i", $quiz_id);
$stmt_q->execute();
$questions = $stmt_q->get_result();
$total_questions = $questions->num_rows;

// CẤU HÌNH SỐ CÂU HỎI TRÊN MỖI TRANG
$questions_per_page = 5;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Làm bài: <?php echo htmlspecialchars($quiz['title']); ?> - QuizMaster</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/WebTaoBoDeTuDong/assets/css/take_quiz.css">
    
    <!-- Tích hợp MathJax để Render Ma trận / Tích phân (LaTeX) -->
    <script>
        MathJax = {
            tex: { inlineMath: [['$', '$'], ['\\(', '\\)']], displayMath: [['$$', '$$'], ['\\[', '\\]']] },
            svg: { fontCache: 'global' }
        };
    </script>
    <script type="text/javascript" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"></script>

    <!-- CSS Bổ sung cho Thanh điều hướng phân trang -->
    <style>
        .quiz-nav-buttons {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 10px; padding-top: 25px; border-top: 2px dashed var(--border-light);
        }
        .btn-nav {
            padding: 14px 28px; border-radius: 12px; font-weight: 700; font-size: 1rem;
            border: none; cursor: pointer; transition: var(--transition);
            display: flex; align-items: center; gap: 8px;
        }
        .btn-nav-prev { background: #e2e8f0; color: #475569; }
        .btn-nav-prev:hover { background: #cbd5e0; transform: translateX(-3px); }
        .btn-nav-next { background: var(--primary-teal); color: white; }
        .btn-nav-next:hover { background: var(--primary-hover); transform: translateX(3px); box-shadow: 0 4px 12px rgba(15,92,107,0.2); }
        .btn-nav-submit { background: #10b981; color: white; }
        .btn-nav-submit:hover { background: #059669; transform: translateY(-3px); box-shadow: 0 4px 12px rgba(16,185,129,0.25); }
        .page-indicator { font-weight: 700; color: var(--text-muted); background: white; padding: 10px 20px; border-radius: 50px; border: 1px solid var(--border-light); }
        
        /* Chỉnh lại con trỏ cho Sidebar Badges */
        .q-badge { cursor: pointer; user-select: none; }
    </style>
</head>
<body>

    <header class="exam-header">
        <div class="exam-title">
            <i class="fas fa-laptop-code"></i>
            <span><?php echo htmlspecialchars($quiz['title']); ?></span>
        </div>
        <a href="../../home.php" class="btn-exit" onclick="return confirm('Bạn có chắc chắn muốn thoát? Bài làm sẽ không được lưu.');">
            Thoát <i class="fas fa-sign-out-alt"></i>
        </a>
    </header>

    <form action="result.php" method="POST" id="quizForm" onsubmit="return validateForm(event)">
        <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
        
        <div class="exam-container">
            <div class="main-content">
                <?php 
                $q_num = 1;
                while ($q = $questions->fetch_assoc()): 
                    $is_essay = empty($q['opt_b']);
                    // Xác định trang hiện tại của câu hỏi này
                    $page_num = ceil($q_num / $questions_per_page);
                ?>
                    <!-- Thêm data-page để JS nhận diện trang -->
                    <div class="question-card" id="question-<?php echo $q_num; ?>" data-page="<?php echo $page_num; ?>" style="display: none;">
                        <div class="q-number">Câu <?php echo $q_num; ?></div>
                        
                        <div class="q-content" style="white-space: pre-wrap; word-wrap: break-word;">
                            <?php echo htmlspecialchars($q['content']); ?>
                        </div>
                        
                        <?php if ($is_essay): ?>
                            <!-- Bỏ required để JS tự kiểm tra -->
                            <textarea name="answers[<?php echo $q['id']; ?>]" class="essay-input" placeholder="Nhập câu trả lời của bạn vào đây..." oninput="markDone(<?php echo $q_num; ?>)"></textarea>
                        <?php else: ?>
                            <div class="options-grid">
                                <?php foreach (['A' => 'opt_a', 'B' => 'opt_b', 'C' => 'opt_c', 'D' => 'opt_d'] as $key => $col): ?>
                                    <label class="option-label">
                                        <!-- Bỏ required để JS tự kiểm tra, không bị lỗi form ẩn -->
                                        <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $key; ?>" onchange="markDone(<?php echo $q_num; ?>)">
                                        <span class="custom-radio"></span>
                                        <span class="opt-text"><strong><?php echo $key; ?>.</strong> &nbsp; <span style="white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($q[$col]); ?></span></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php $q_num++; endwhile; ?>

                <!-- Thanh Điều Hướng Phân Trang -->
                <div class="quiz-nav-buttons">
                    <button type="button" id="btn-prev" class="btn-nav btn-nav-prev" onclick="changePage(-1)">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </button>
                    
                    <div class="page-indicator" id="page-info">Trang 1 / ?</div>
                    
                    <button type="button" id="btn-next" class="btn-nav btn-nav-next" onclick="changePage(1)">
                        Tiếp tục <i class="fas fa-arrow-right"></i>
                    </button>
                    
                    <button type="submit" id="btn-submit-main" class="btn-nav btn-nav-submit" style="display: none;">
                        <i class="fas fa-paper-plane"></i> Nộp bài
                    </button>
                </div>
            </div>

            <!-- SIDEBAR TIẾN ĐỘ -->
            <aside class="progress-sidebar">
                <div class="sidebar-title">
                    <i class="fas fa-tasks" style="color: var(--primary-teal);"></i> Tiến độ làm bài
                </div>
                
                <div class="progress-grid">
                    <?php for($i = 1; $i <= $total_questions; $i++): ?>
                        <!-- Đổi thẻ <a> thành thẻ <div> có sự kiện onclick để chuyển trang -->
                        <div class="q-badge" id="badge-<?php echo $i; ?>" onclick="goToQuestion(<?php echo $i; ?>)"><?php echo $i; ?></div>
                    <?php endfor; ?>
                </div>
                
                <hr style="border: 1px solid #e2e8f0; margin: 25px 0;">
                <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 15px; line-height: 1.5;">
                    <i class="fas fa-info-circle"></i> Bấm vào ô số để quay lại kiểm tra câu hỏi tương ứng.
                </p>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Nộp Bài Ngay
                </button>
            </aside>
        </div>
    </form>

    <script>
        const totalQuestions = <?php echo $total_questions; ?>;
        const questionsPerPage = <?php echo $questions_per_page; ?>;
        const totalPages = Math.ceil(totalQuestions / questionsPerPage);
        let currentPage = 1;

        // KHỞI TẠO: Hiển thị trang đầu tiên khi tải trang
        document.addEventListener("DOMContentLoaded", () => {
            showPage(1);
        });

        // Hàm Đổi Màu Huy Hiệu khi chọn đáp án
        function markDone(qNum) {
            document.getElementById('badge-' + qNum).classList.add('done');
        }

        // Hàm Hiển thị trang (Render Page)
        function showPage(page) {
            // 1. Ẩn tất cả câu hỏi
            document.querySelectorAll('.question-card').forEach(card => {
                card.style.display = 'none';
            });
            
            // 2. Hiện câu hỏi thuộc trang hiện tại
            document.querySelectorAll(`.question-card[data-page="${page}"]`).forEach(card => {
                card.style.display = 'block';
            });

            // 3. Cập nhật Text
            document.getElementById('page-info').innerText = `Trang ${page} / ${totalPages}`;

            // 4. Xử lý logic Ẩn/Hiện nút bấm
            document.getElementById('btn-prev').style.visibility = (page === 1) ? 'hidden' : 'visible';
            
            if (page === totalPages) {
                document.getElementById('btn-next').style.display = 'none';
                document.getElementById('btn-submit-main').style.display = 'flex';
            } else {
                document.getElementById('btn-next').style.display = 'flex';
                document.getElementById('btn-submit-main').style.display = 'none';
            }

            // 5. Tự động cuộn lên đầu bài làm cho học viên đỡ mỏi tay
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Hàm Chuyển trang (Bấm nút Tới/Lui)
        function changePage(step) {
            let newPage = currentPage + step;
            if (newPage >= 1 && newPage <= totalPages) {
                currentPage = newPage;
                showPage(currentPage);
            }
        }

        // Hàm Nhảy nhanh đến câu hỏi (Khi bấm vào Sidebar Badges)
        function goToQuestion(qNum) {
            let targetPage = Math.ceil(qNum / questionsPerPage);
            
            // Nếu câu hỏi nằm ở trang khác, phải chuyển trang trước
            if (currentPage !== targetPage) {
                currentPage = targetPage;
                showPage(currentPage);
            }
            
            // Đợi CSS render xong rồi mới scroll tới đúng thẻ đó
            setTimeout(() => {
                const targetEl = document.getElementById('question-' + qNum);
                const offset = 90; // Trừ hao thanh header cố định
                const bodyRect = document.body.getBoundingClientRect().top;
                const elementRect = targetEl.getBoundingClientRect().top;
                const elementPosition = elementRect - bodyRect;
                const offsetPosition = elementPosition - offset;

                window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
                
                // Nháy nhẹ viền để thu hút sự chú ý
                targetEl.style.boxShadow = "0 0 0 4px rgba(44, 156, 140, 0.3)";
                setTimeout(() => targetEl.style.boxShadow = "var(--glass-shadow)", 1500);
            }, 100);
        }

        // Hàm Kiểm tra Validate chống bỏ sót câu hỏi trước khi Nộp Bài
        function validateForm(e) {
            for (let i = 1; i <= totalQuestions; i++) {
                let badge = document.getElementById('badge-' + i);
                
                // Nếu tìm thấy 1 câu chưa làm (không có class 'done')
                if (!badge.classList.contains('done')) {
                    e.preventDefault(); // Chặn hành động nộp bài
                    alert(`Bạn chưa trả lời Câu số ${i}. Hệ thống sẽ chuyển đến câu hỏi này!`);
                    goToQuestion(i);
                    return false;
                }
            }
            
            return confirm('Bạn đã hoàn thành tất cả câu hỏi. Bạn có chắc chắn muốn nộp bài?');
        }
    </script>
</body>
</html>