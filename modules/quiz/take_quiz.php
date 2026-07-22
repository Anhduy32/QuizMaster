<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Truy vấn thông tin đề thi bao gồm cả quiz_type và file_path
$query_quiz = "SELECT * FROM quizzes WHERE id = ?";
$stmt_quiz = $conn->prepare($query_quiz);
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$quiz = $stmt_quiz->get_result()->fetch_assoc();

if (!$quiz) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>Đề thi không tồn tại! <a href='../../home.php'>Về trang chủ</a></div>");
}

$quiz_type = $quiz['quiz_type'] ?? 'multiple_choice';
$file_path = $quiz['file_path'] ?? '';

// ============================================================
// LOẠI 1: ĐỀ THI DẠNG FILE PDF / TỰ LUẬN
// ============================================================
if ($quiz_type === 'file_based'):
    $full_path = '../../../' . $file_path;
    $file_name = basename($file_path);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem đề: <?php echo htmlspecialchars($quiz['title']); ?> - QuizMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/WebTaoBoDeTuDong/assets/css/take_quiz.css">
    <style>
        .pdf-view-wrapper { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .instruction-box { background: #fffbeb; border: 1px solid #fde68a; padding: 20px; border-radius: 12px; margin-bottom: 20px; color: #92400e; }
        .instruction-box h4 { margin: 0 0 8px 0; display: flex; align-items: center; gap: 8px; }
        .pdf-viewer-container { border: 1px solid #cbd5e0; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .pdf-header { background: #f1f5f9; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #cbd5e0; flex-wrap: wrap; gap: 12px; }
        .pdf-header .file-info { display: flex; align-items: center; gap: 10px; }
        .pdf-header .file-info i { color: #dc2626; font-size: 1.4rem; }
        .pdf-header .file-info strong { color: #1a202c; }
        .pdf-header .file-info span { color: #64748b; font-weight: 400; font-size: 0.9rem; }
        .pdf-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-pdf { padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer; }
        .btn-pdf-download { background: #dc2626; color: white; }
        .btn-pdf-download:hover { background: #b91c1c; transform: translateY(-2px); }
        .btn-pdf-view { background: #fff; color: #4a5568; border: 1px solid #cbd5e0; }
        .btn-pdf-view:hover { background: #f8fafc; border-color: #a0aec0; }
        .btn-back-quiz { background: var(--primary-teal); color: white; }
        .btn-back-quiz:hover { background: var(--primary-hover); }
        .pdf-iframe { width: 100%; height: 750px; border: none; display: block; }
        @media (max-width: 768px) { .pdf-iframe { height: 500px; } }
    </style>
</head>
<body style="background: #f8fafc;">

    <header class="exam-header">
        <div class="exam-title">
            <i class="fas fa-file-pdf" style="color: #dc2626;"></i>
            <span><?php echo htmlspecialchars($quiz['title']); ?></span>
        </div>
        <a href="../../home.php" class="btn-exit" onclick="return confirm('Bạn có chắc chắn muốn thoát?');">
            Thoát <i class="fas fa-sign-out-alt"></i>
        </a>
    </header>

    <div class="pdf-view-wrapper">
        <div class="instruction-box">
            <h4><i class="fas fa-info-circle"></i> Hướng dẫn làm bài</h4>
            <p>Đây là đề thi dạng file tài liệu. Hệ thống <strong>không hỗ trợ tính điểm trực tuyến</strong> cho dạng đề này. Vui lòng tải file PDF về máy, in ra hoặc làm trực tiếp ra giấy.</p>
        </div>

        <?php if (!empty($file_path) && file_exists($full_path)): ?>
            <div class="pdf-viewer-container">
                <div class="pdf-header">
                    <div class="file-info">
                        <i class="fas fa-file-pdf"></i>
                        <div>
                            <strong><?php echo htmlspecialchars($file_name); ?></strong>
                            <span>(<?php echo number_format(filesize($full_path) / 1024, 1); ?> KB)</span>
                        </div>
                    </div>
                    <div class="pdf-actions">
                        <a href="<?php echo htmlspecialchars($full_path); ?>" target="_blank" class="btn-pdf btn-pdf-view">
                            <i class="fas fa-external-link-alt"></i> Xem mới
                        </a>
                        <a href="<?php echo htmlspecialchars($full_path); ?>" download class="btn-pdf btn-pdf-download">
                            <i class="fas fa-download"></i> Tải xuống
                        </a>
                    </div>
                </div>
                <iframe src="<?php echo htmlspecialchars($full_path); ?>#toolbar=1&navpanes=1" 
                        class="pdf-iframe" 
                        title="Xem đề thi PDF">
                    Trình duyệt của bạn không hỗ trợ đọc PDF. Hãy tải file 
                    <a href="<?php echo htmlspecialchars($full_path); ?>">tại đây</a>.
                </iframe>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; background: white; border-radius: 12px; border: 1px solid #fecaca;">
                <i class="fas fa-file-pdf" style="font-size: 3rem; color: #dc2626; margin-bottom: 16px; display: block;"></i>
                <p style="color: #dc2626; font-weight: 600;">Không tìm thấy file PDF đính kèm cho đề thi này trên máy chủ!</p>
                <p style="color: #64748b; font-size: 0.9rem;">Vui lòng liên hệ với người tạo đề thi để được hỗ trợ.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
<?php 
    exit(); // Dừng thực thi, không chạy code trắc nghiệm bên dưới
endif; 

// ============================================================
// LOẠI 2: ĐỀ THI TRẮC NGHIỆM (multiple_choice)
// ============================================================
// Truy vấn danh sách câu hỏi
$query_questions = "SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC";
$stmt_q = $conn->prepare($query_questions);
$stmt_q->bind_param("i", $quiz_id);
$stmt_q->execute();
$questions = $stmt_q->get_result();
$total_questions = $questions->num_rows;

// Nếu không có câu hỏi nào, chuyển hướng về trang chi tiết
if ($total_questions == 0) {
    header("Location: quiz_detail.php?id=" . $quiz_id . "&error=no_questions");
    exit();
}

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
    
    <style>
        /* CSS cho phần điều hướng */
        .quiz-nav-buttons {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 10px; padding-top: 25px; border-top: 2px dashed #e2e8f0;
            flex-wrap: wrap;
            gap: 12px;
        }
        .btn-nav {
            padding: 14px 28px; border-radius: 12px; font-weight: 700; font-size: 1rem;
            border: none; cursor: pointer; transition: all 0.3s ease;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-nav-prev { background: #e2e8f0; color: #475569; }
        .btn-nav-prev:hover { background: #cbd5e0; transform: translateX(-3px); }
        .btn-nav-next { background: var(--primary-teal); color: white; }
        .btn-nav-next:hover { background: var(--primary-hover); transform: translateX(3px); box-shadow: 0 4px 12px rgba(15,92,107,0.2); }
        .btn-nav-submit { background: #10b981; color: white; }
        .btn-nav-submit:hover { background: #059669; transform: translateY(-3px); box-shadow: 0 4px 12px rgba(16,185,129,0.25); }
        .page-indicator { font-weight: 700; color: #64748b; background: white; padding: 10px 20px; border-radius: 50px; border: 1px solid #e2e8f0; }
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
                    $page_num = ceil($q_num / $questions_per_page);
                ?>
                    <div class="question-card" id="question-<?php echo $q_num; ?>" data-page="<?php echo $page_num; ?>" style="display: none;">
                        <div class="q-number">Câu <?php echo $q_num; ?></div>
                        
                        <div class="q-content" style="white-space: pre-wrap; word-wrap: break-word;">
                            <?php echo htmlspecialchars($q['content']); ?>
                        </div>
                        
                        <?php if ($is_essay): ?>
                            <textarea name="answers[<?php echo $q['id']; ?>]" class="essay-input" placeholder="Nhập câu trả lời của bạn vào đây..." oninput="markDone(<?php echo $q_num; ?>)"></textarea>
                        <?php else: ?>
                            <div class="options-grid">
                                <?php foreach (['A' => 'opt_a', 'B' => 'opt_b', 'C' => 'opt_c', 'D' => 'opt_d'] as $key => $col): ?>
                                    <label class="option-label">
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

        document.addEventListener("DOMContentLoaded", () => {
            showPage(1);
        });

        function markDone(qNum) {
            document.getElementById('badge-' + qNum).classList.add('done');
        }

        function showPage(page) {
            document.querySelectorAll('.question-card').forEach(card => {
                card.style.display = 'none';
            });
            
            document.querySelectorAll(`.question-card[data-page="${page}"]`).forEach(card => {
                card.style.display = 'block';
            });

            document.getElementById('page-info').innerText = `Trang ${page} / ${totalPages}`;

            document.getElementById('btn-prev').style.visibility = (page === 1) ? 'hidden' : 'visible';
            
            if (page === totalPages) {
                document.getElementById('btn-next').style.display = 'none';
                document.getElementById('btn-submit-main').style.display = 'flex';
            } else {
                document.getElementById('btn-next').style.display = 'flex';
                document.getElementById('btn-submit-main').style.display = 'none';
            }

            // Scroll về đầu form
            const formTop = document.getElementById('quizForm').getBoundingClientRect().top + window.scrollY - 80;
            window.scrollTo({ top: formTop, behavior: 'smooth' });
        }

        function changePage(step) {
            let newPage = currentPage + step;
            if (newPage >= 1 && newPage <= totalPages) {
                currentPage = newPage;
                showPage(currentPage);
            }
        }

        function goToQuestion(qNum) {
            let targetPage = Math.ceil(qNum / questionsPerPage);
            
            if (currentPage !== targetPage) {
                currentPage = targetPage;
                showPage(currentPage);
            }
            
            setTimeout(() => {
                const targetEl = document.getElementById('question-' + qNum);
                const offset = 90; 
                const bodyRect = document.body.getBoundingClientRect().top;
                const elementRect = targetEl.getBoundingClientRect().top;
                const elementPosition = elementRect - bodyRect;
                const offsetPosition = elementPosition - offset;

                window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
                
                targetEl.style.boxShadow = "0 0 0 4px rgba(44, 156, 140, 0.3)";
                setTimeout(() => targetEl.style.boxShadow = "var(--glass-shadow)", 1500);
            }, 100);
        }

        function validateForm(e) {
            for (let i = 1; i <= totalQuestions; i++) {
                let badge = document.getElementById('badge-' + i);
                
                if (!badge.classList.contains('done')) {
                    e.preventDefault(); 
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