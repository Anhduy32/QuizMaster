<?php
session_start();

// $root_path CHỈ dùng để include các file hệ thống (database)
$root_path = dirname(__DIR__, 2);
include $root_path . '/config/database.php';

// SỬA LỖI 1: Dùng đường dẫn tương đối cho header (giả sử file này ở /modules/user/)
if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit();
}

// ===== XỬ LÝ THÔNG BÁO FLASH (Gọn gàng hơn, không gây lỗi Undefined) =====
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$ten_dang_nhap = $_SESSION['username'];
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ===== LẤY THÔNG TIN NGƯỜI DÙNG =====
$truy_van = "SELECT * FROM users WHERE username = ?";
$chuan_bi = $conn->prepare($truy_van);
$chuan_bi->bind_param('s', $ten_dang_nhap);
$chuan_bi->execute();
$nguoi_dung = $chuan_bi->get_result()->fetch_assoc();

$ho_va_ten = $nguoi_dung['full_name'] ?? $ten_dang_nhap;
$ten_ngan_gon = explode(' ', trim($ho_va_ten));
$ten_goi = end($ten_ngan_gon);
$avatar_url = !empty($nguoi_dung['picture']) ? $nguoi_dung['picture'] : "https://ui-avatars.com/api/?name=" . urlencode($ho_va_ten) . "&background=0f5c6b&color=fff&size=150";

// ===== LẤY DANH SÁCH ĐỀ THI =====
$query = "SELECT q.*, 
          (SELECT COUNT(*) FROM quiz_history WHERE quiz_id = q.id) as total_attempts,
          (SELECT AVG(score) FROM quiz_history WHERE quiz_id = q.id) as avg_score
          FROM quizzes q 
          WHERE q.creator_username = ? 
          ORDER BY q.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param('s', $ten_dang_nhap);
$stmt->execute();
$result = $stmt->get_result();

// SỬA LỖI 2: Tạo mảng lưu dữ liệu 1 lần duy nhất, vừa tính thống kê vừa in ra
$quizzes = [];
$total_questions = 0;
$total_attempts = 0;
$published_count = 0;

while ($row = $result->fetch_assoc()) {
    $total_questions += (int)$row['num_questions'];
    $total_attempts += (int)$row['total_attempts'];
    if ($row['status'] === 'published') $published_count++;
    $quizzes[] = $row; // Lưu vào mảng
}
$total_quizzes = count($quizzes);


function getStatusBadge($status) {
    $map = [
        'draft'     => ['label' => 'Bản nháp', 'class' => 'status-draft'],
        'pending'   => ['label' => 'Chờ duyệt', 'class' => 'status-pending'],
        'approved'  => ['label' => 'Đã duyệt', 'class' => 'status-approved'],
        'rejected'  => ['label' => 'Từ chối', 'class' => 'status-rejected'],
        'published' => ['label' => 'Đã xuất bản', 'class' => 'status-published'],
        'archived'  => ['label' => 'Đã lưu trữ', 'class' => 'status-archived']
    ];
    return $map[$status] ?? ['label' => 'Khác', 'class' => 'status-other'];
}

function getSubjectStyle($subject) {
    $sub = mb_strtolower($subject, 'UTF-8');
    if (strpos($sub, 'toán') !== false) return ['bg' => 'linear-gradient(135deg, #6366f1, #8b5cf6)', 'icon' => 'fa-square-root-alt'];
    if (strpos($sub, 'lý') !== false)   return ['bg' => 'linear-gradient(135deg, #ec4899, #f472b6)', 'icon' => 'fa-magnet'];
    if (strpos($sub, 'hóa') !== false)  return ['bg' => 'linear-gradient(135deg, #10b981, #34d399)', 'icon' => 'fa-flask'];
    if (strpos($sub, 'anh') !== false)  return ['bg' => 'linear-gradient(135deg, #3b82f6, #60a5fa)', 'icon' => 'fa-language'];
    if (strpos($sub, 'tin') !== false)  return ['bg' => 'linear-gradient(135deg, #f59e0b, #fbbf24)', 'icon' => 'fa-laptop-code'];
    if (strpos($sub, 'sử') !== false)   return ['bg' => 'linear-gradient(135deg, #14b8a6, #2dd4bf)', 'icon' => 'fa-landmark'];
    if (strpos($sub, 'địa') !== false)  return ['bg' => 'linear-gradient(135deg, #06b6d4, #22d3ee)', 'icon' => 'fa-globe-asia'];
    return ['bg' => 'linear-gradient(135deg, #a78bfa, #c4b5fd)', 'icon' => 'fa-book'];
}

// ================= PAGE CONFIG =================
$page_title = 'Thư viện của tôi - QuizMaster';
$page_css = '../../assets/css/my_library.css'; // Chú ý chỉnh đúng đường dẫn CSS
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $page_css; ?>">
</head>
<body>

<div class="app-wrapper">

    <!-- ===== TOP HEADER ===== -->
    <header class="top-header">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="header-title">
                <h1>Thư viện của tôi</h1>
                <p>Quản lý tất cả đề thi đã tạo</p>
            </div>
        </div>
        <div class="header-actions">
            <!-- Đã loại bỏ $root_path gây lỗi, thay bằng URL tương đối -->
            <a href="../quiz/create_quiz/step1_create_quiz.php" class="btn-primary">
                <i class="fas fa-plus-circle"></i> Tạo đề mới
            </a>
            <a href="update_profile.php" class="user-profile">
                <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Avatar">
                <span><?php echo htmlspecialchars($ten_goi); ?></span>
            </a>
        </div>
    </header>

    <!-- ===== FLASH MESSAGES ===== -->
    <?php if ($success_message): ?>
        <div class="flash-message success" id="flashMessage">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($success_message); ?></span>
            <button class="close-btn" onclick="this.parentElement.remove()">✕</button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="flash-message error" id="flashMessage">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error_message); ?></span>
            <button class="close-btn" onclick="this.parentElement.remove()">✕</button>
        </div>
    <?php endif; ?>

    <!-- ===== STATS ===== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-file-alt"></i></div>
            <div class="stat-info">
                <div class="number"><?php echo $total_quizzes; ?></div>
                <div class="label">Tổng đề thi</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-question-circle"></i></div>
            <div class="stat-info">
                <div class="number"><?php echo $total_questions; ?></div>
                <div class="label">Tổng câu hỏi</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <div class="number"><?php echo $total_attempts; ?></div>
                <div class="label">Lượt làm bài</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="number"><?php echo $published_count; ?></div>
                <div class="label">Đã xuất bản</div>
            </div>
        </div>
    </div>

    <!-- ===== TOOLBAR ===== -->
    <div class="toolbar">
        <div class="search-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Tìm kiếm đề thi..." onkeyup="filterQuizzes()">
            <kbd>Ctrl+K</kbd>
        </div>
        <div class="filter-group">
            <select id="statusFilter" class="filter-select" onchange="filterQuizzes()">
                <option value="all"> Tất cả trạng thái</option>
                <option value="draft"> Bản nháp</option>
                <option value="pending"> Chờ duyệt</option>
                <option value="approved"> Đã duyệt</option>
                <option value="published"> Đã xuất bản</option>
                <option value="archived"> Đã lưu trữ</option>
                <option value="rejected"> Từ chối</option>
            </select>
            <select id="sortFilter" class="filter-select" onchange="filterQuizzes()">
                <option value="newest"> Mới nhất</option>
                <option value="oldest"> Cũ nhất</option>
                <option value="popular"> Nhiều lượt làm</option>
                <option value="highest"> Điểm cao nhất</option>
            </select>
        </div>
    </div>

    <!-- ===== QUIZZES LIST ===== -->
    <div class="quizzes-container">
        <?php if (!empty($quizzes)): ?>
            <div class="quizzes-grid" id="quizzesGrid">
                <?php foreach ($quizzes as $quiz): 
                    $style = getSubjectStyle($quiz['subject']);
                    $status = getStatusBadge($quiz['status']);
                    $avg_score = $quiz['avg_score'] ? round($quiz['avg_score'], 1) : '–';
                ?>
                    <div class="quiz-card"
                         data-status="<?php echo $quiz['status']; ?>"
                         data-title="<?php echo strtolower(htmlspecialchars($quiz['title'])); ?>"
                         data-id="<?php echo $quiz['id']; ?>"
                         data-attempts="<?php echo (int)$quiz['total_attempts']; ?>"
                         data-score="<?php echo $avg_score !== '–' ? $avg_score : 0; ?>"
                         data-date="<?php echo strtotime($quiz['created_at']); ?>">

                        <div class="card-top">
                            <div class="card-subject-icon" style="background: <?php echo $style['bg']; ?>">
                                <i class="fas <?php echo $style['icon']; ?>"></i>
                            </div>
                            <span class="status-badge <?php echo $status['class']; ?>">
                                <span class="status-dot"></span>
                                <?php echo $status['label']; ?>
                            </span>
                        </div>

                        <div class="card-body">
                            <h3 class="card-title">
                                <!-- Đã thay bằng link tương đối -->
                                <a href="../quiz/quiz_detail.php?id=<?php echo $quiz['id']; ?>">
                                    <?php echo htmlspecialchars($quiz['title']); ?>
                                </a>
                            </h3>
                            <div class="card-meta">
                                <span><i class="fas fa-book-open"></i> <?php echo htmlspecialchars($quiz['subject']); ?></span>
                                <span class="divider">•</span>
                                <span><i class="fas fa-layer-group"></i> <?php echo (int)$quiz['num_questions']; ?> câu</span>
                                <?php if (!empty($quiz['file_path'])): ?>
                                    <span class="divider">•</span>
                                    <span class="pdf-tag"><i class="fas fa-file-pdf"></i> PDF</span>
                                <?php endif; ?>
                                <span class="divider">•</span>
                                <span><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($quiz['created_at'])); ?></span>
                            </div>
                            <?php if (!empty($quiz['description'])): ?>
                                <p class="card-description">
                                    <?php echo htmlspecialchars(substr($quiz['description'], 0, 100)) . (strlen($quiz['description']) > 100 ? '...' : ''); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer">
                            <div class="card-stats">
                                <span><i class="fas fa-users"></i> <?php echo (int)$quiz['total_attempts']; ?> lượt</span>
                                <span><i class="fas fa-star"></i> <?php echo $avg_score; ?>/10</span>
                            </div>
                            <div class="card-actions">
                                <!-- Đã thay bằng link tương đối -->
                                <a href="../quiz/edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-action edit" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="../quiz/quiz_detail.php?id=<?php echo $quiz['id']; ?>" class="btn-action view" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn-action delete" title="Xóa" onclick="confirmDelete(<?php echo $quiz['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-library">
                <div class="empty-icon"><i class="fas fa-book-open"></i></div>
                <h3>Chưa có đề thi nào</h3>
                <p>Bạn chưa tạo đề thi nào. Hãy bắt đầu tạo đề thi đầu tiên của bạn!</p>
                <a href="../quiz/create_quiz/step1_create_quiz.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Tạo đề thi ngay
                </a>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ===== TOAST CONTAINER ===== -->
<div id="toast-container"></div>

<script>
    // ============================================================
    // CONFIRM DELETE
    // ============================================================
    function confirmDelete(quizId) {
        if (confirm('Bạn có chắc chắn muốn xóa đề thi này?\nHành động này không thể hoàn tác.')) {
            // Thay đổi đường dẫn cho đúng nếu file xóa nằm trong thư mục quiz
            window.location.href = '../quiz/delete_quiz.php?id=' + quizId + '&action=delete';
        }
    }

    // ============================================================
    // FILTER & SEARCH
    // ============================================================
    function filterQuizzes() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const sortFilter = document.getElementById('sortFilter').value;
        const cards = document.querySelectorAll('.quiz-card');

        let visibleCards = [];

        cards.forEach(card => {
            const title = card.dataset.title || '';
            const status = card.dataset.status || '';

            let show = true;
            if (searchTerm && !title.includes(searchTerm)) show = false;
            if (statusFilter !== 'all' && status !== statusFilter) show = false;

            if (show) {
                card.style.display = '';
                visibleCards.push(card);
            } else {
                card.style.display = 'none';
            }
        });

        if (sortFilter !== 'newest') {
            const grid = document.getElementById('quizzesGrid');
            const cardsArray = Array.from(visibleCards);

            cardsArray.sort((a, b) => {
                switch (sortFilter) {
                    case 'oldest':
                        return parseInt(a.dataset.date) - parseInt(b.dataset.date);
                    case 'popular':
                        return parseInt(b.dataset.attempts) - parseInt(a.dataset.attempts);
                    case 'highest':
                        return parseFloat(b.dataset.score) - parseFloat(a.dataset.score);
                    default:
                        return 0;
                }
            });
            cardsArray.forEach(card => grid.appendChild(card));
        }
    }

    // ============================================================
    // KEYBOARD SHORTCUT - Ctrl+K
    // ============================================================
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('searchInput').focus();
            document.getElementById('searchInput').select();
        }
    });

    // ============================================================
    // AUTO HIDE FLASH MESSAGE
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        const flashMessage = document.getElementById('flashMessage');
        if (flashMessage) {
            setTimeout(function() {
                flashMessage.style.transition = 'all 0.5s ease';
                flashMessage.style.opacity = '0';
                flashMessage.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    if (flashMessage.parentElement) {
                        flashMessage.remove();
                    }
                }, 500);
            }, 5000);
        }

        // Animation on scroll
        const cards = document.querySelectorAll('.quiz-card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 60);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '20px' });

        cards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(24px)';
            card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            observer.observe(card);
        });
    });
</script>

</body>
</html>