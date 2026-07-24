<?php
// Bật thông báo lỗi để dễ dàng gỡ lỗi
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

$root_path = dirname(__DIR__, 2);
include $root_path . '/config/database.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Xử lý thông báo Flash
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$ten_dang_nhap = $_SESSION['username'];
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Bọc an toàn để bắt lỗi cơ sở dữ liệu nếu có
try {
    // ===== LẤY THÔNG TIN NGƯỜI DÙNG =====
    $truy_van = "SELECT * FROM users WHERE username = ?";
    $chuan_bi = $conn->prepare($truy_van);
    if (!$chuan_bi) throw new Exception("Lỗi prepare SQL Users: " . $conn->error);
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
    if (!$stmt) throw new Exception("Lỗi prepare SQL Quizzes: " . $conn->error);
    $stmt->bind_param('s', $ten_dang_nhap);
    $stmt->execute();
    $result = $stmt->get_result();

    $quizzes = [];
    $total_questions = 0;
    $total_attempts = 0;
    $published_count = 0;

    while ($row = $result->fetch_assoc()) {
        $total_questions += (int)($row['num_questions'] ?? 0);
        $total_attempts += (int)($row['total_attempts'] ?? 0);
        if (isset($row['status']) && $row['status'] === 'published') $published_count++;
        $quizzes[] = $row;
    }
    $total_quizzes = count($quizzes);

} catch (Exception $e) {
    die("<div style='background: #fee2e2; color: #991b1b; padding: 20px; border-radius: 8px; font-family: sans-serif; margin: 20px;'>
            <h3>⚠️ Đã xảy ra lỗi cơ sở dữ liệu:</h3>
            <p>" . $e->getMessage() . "</p>
            <p><i>Hãy kiểm tra lại cấu trúc các bảng (users, quizzes, quiz_history) trong CSDL của bạn.</i></p>
         </div>");
}

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
    $sub = mb_strtolower($subject ?? '', 'UTF-8');
    if (strpos($sub, 'toán') !== false) return ['bg' => 'linear-gradient(135deg, #6366f1, #8b5cf6)', 'icon' => 'fa-square-root-alt'];
    if (strpos($sub, 'lý') !== false)   return ['bg' => 'linear-gradient(135deg, #ec4899, #f472b6)', 'icon' => 'fa-magnet'];
    if (strpos($sub, 'hóa') !== false)  return ['bg' => 'linear-gradient(135deg, #10b981, #34d399)', 'icon' => 'fa-flask'];
    if (strpos($sub, 'anh') !== false)  return ['bg' => 'linear-gradient(135deg, #3b82f6, #60a5fa)', 'icon' => 'fa-language'];
    if (strpos($sub, 'tin') !== false)  return ['bg' => 'linear-gradient(135deg, #f59e0b, #fbbf24)', 'icon' => 'fa-laptop-code'];
    if (strpos($sub, 'sử') !== false)   return ['bg' => 'linear-gradient(135deg, #14b8a6, #2dd4bf)', 'icon' => 'fa-landmark'];
    if (strpos($sub, 'địa') !== false)  return ['bg' => 'linear-gradient(135deg, #06b6d4, #22d3ee)', 'icon' => 'fa-globe-asia'];
    return ['bg' => 'linear-gradient(135deg, #a78bfa, #c4b5fd)', 'icon' => 'fa-book'];
}

$page_title = 'Thư viện của tôi - QuizMaster';
$page_css = '../../assets/css/my_library.css'; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?php echo $page_css; ?>">
</head>
<body>

<div class="app-wrapper">

    <header class="top-header">
        <div class="header-left">
            <div class="header-icon"><i class="fas fa-book-open"></i></div>
            <div class="header-title">
                <h1>Thư viện của tôi</h1>
                <p>Quản lý tất cả đề thi đã tạo</p>
            </div>
        </div>
        <div class="header-actions">
            <a href="../quiz/create_quiz/step1_create_quiz.php" class="btn-primary">
                <i class="fas fa-plus-circle"></i> Tạo đề mới
            </a>
            <a href="../user/update_profile.php" class="user-profile">
                <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Avatar">
                <span><?php echo htmlspecialchars($ten_goi); ?></span>
            </a>
        </div>
    </header>

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

    <div class="quizzes-container">
        <?php if (!empty($quizzes)): ?>
            <div class="quizzes-grid" id="quizzesGrid">
                <?php foreach ($quizzes as $quiz): 
                    $style = getSubjectStyle($quiz['subject'] ?? '');
                    $status = getStatusBadge($quiz['status'] ?? 'draft');
                    $avg_score = isset($quiz['avg_score']) && $quiz['avg_score'] !== null ? round($quiz['avg_score'], 1) : '–';
                ?>
                    <div class="quiz-card"
                         data-status="<?php echo htmlspecialchars($quiz['status'] ?? ''); ?>"
                         data-title="<?php echo strtolower(htmlspecialchars($quiz['title'] ?? '')); ?>"
                         data-id="<?php echo (int)$quiz['id']; ?>"
                         data-attempts="<?php echo (int)($quiz['total_attempts'] ?? 0); ?>"
                         data-score="<?php echo $avg_score !== '–' ? $avg_score : 0; ?>"
                         data-date="<?php echo strtotime($quiz['created_at'] ?? 'now'); ?>">

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
                                <a href="quiz_detail.php?id=<?php echo (int)$quiz['id']; ?>">
                                    <?php echo htmlspecialchars($quiz['title'] ?? 'Không có tiêu đề'); ?>
                                </a>
                            </h3>
                            <div class="card-meta">
                                <span><i class="fas fa-book-open"></i> <?php echo htmlspecialchars($quiz['subject'] ?? 'Chung'); ?></span>
                                <span class="divider">•</span>
                                <span><i class="fas fa-layer-group"></i> <?php echo (int)($quiz['num_questions'] ?? 0); ?> câu</span>
                                <?php if (!empty($quiz['file_path'])): ?>
                                    <span class="divider">•</span>
                                    <span class="pdf-tag"><i class="fas fa-file-pdf"></i> PDF</span>
                                <?php endif; ?>
                                <span class="divider">•</span>
                                <span><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($quiz['created_at'] ?? 'now')); ?></span>
                            </div>
                            <?php if (!empty($quiz['description'])): ?>
                                <p class="card-description">
                                    <?php echo htmlspecialchars(substr($quiz['description'], 0, 100)) . (strlen($quiz['description']) > 100 ? '...' : ''); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer">
                            <div class="card-stats">
                                <span><i class="fas fa-users"></i> <?php echo (int)($quiz['total_attempts'] ?? 0); ?> lượt</span>
                                <span><i class="fas fa-star"></i> <?php echo $avg_score; ?>/10</span>
                            </div>
                            <div class="card-actions">
                                <a href="edit_quiz.php?id=<?php echo (int)$quiz['id']; ?>" class="btn-action edit" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="quiz_detail.php?id=<?php echo (int)$quiz['id']; ?>" class="btn-action view" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn-action delete" title="Xóa" onclick="confirmDelete(<?php echo (int)$quiz['id']; ?>)">
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
                <a href="create_quiz/step1_create_quiz.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Tạo đề thi ngay
                </a>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
function confirmDelete(quizId) {
    Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa?',
        text: "Hành động này sẽ xóa vĩnh viễn đề thi và không thể khôi phục!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e53e3e', 
        cancelButtonColor: '#64748b', 
        confirmButtonText: '<i class="fas fa-trash"></i> Vâng, xóa ngay!',
        cancelButtonText: 'Hủy bỏ'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('delete_quiz.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete&id=' + encodeURIComponent(quizId)
            })
            .then(response => response.json()) 
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Đã xóa!',
                        text: 'Đề thi của bạn đã được xóa khỏi hệ thống.',
                        icon: 'success',
                        confirmButtonColor: '#0f5c6b'
                    }).then(() => {
                        const card = document.querySelector(`.quiz-card[data-id="${quizId}"]`);
                        if (card) {
                            card.style.transform = 'scale(0.8)';
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 300);
                        }
                    });
                } else {
                    Swal.fire('Lỗi!', data.message || 'Không thể xóa đề thi lúc này.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Lỗi kết nối!', 'Không thể kết nối đến máy chủ.', 'error');
            });
        }
    });
}

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
                case 'oldest': return parseInt(a.dataset.date) - parseInt(b.dataset.date);
                case 'popular': return parseInt(b.dataset.attempts) - parseInt(a.dataset.attempts);
                case 'highest': return parseFloat(b.dataset.score) - parseFloat(a.dataset.score);
                default: return 0;
            }
        });
        cardsArray.forEach(card => grid.appendChild(card));
    }
}

document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('searchInput').focus();
        document.getElementById('searchInput').select();
    }
});
</script>

</body>
</html>