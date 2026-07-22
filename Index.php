<?php
session_start();
include 'config/database.php';

// ================= DATA =================
$is_logged_in = isset($_SESSION['username']);
$ho_va_ten = '';

if ($is_logged_in) {
    $ten_dang_nhap = $_SESSION['username'];
    $truy_van = "SELECT full_name FROM users WHERE username = ?";
    $chuan_bi = $conn->prepare($truy_van);
    $chuan_bi->bind_param('s', $ten_dang_nhap);
    $chuan_bi->execute();
    $ket_qua = $chuan_bi->get_result();
    $nguoi_dung = $ket_qua->fetch_assoc();
    
    if ($nguoi_dung) {
        $ho_va_ten = $nguoi_dung['full_name'] ?? $ten_dang_nhap;
    }
}

// THỐNG KÊ
$query_members = "SELECT COUNT(id) AS total FROM users";
$result_members = $conn->query($query_members);
$total_members = ($result_members && $result_members->num_rows > 0) ? $result_members->fetch_assoc()['total'] : 0;

$query_quizzes = "SELECT COUNT(id) AS total FROM quizzes WHERE status = 'completed'";
$result_quizzes = $conn->query($query_quizzes);
$total_quizzes = ($result_quizzes && $result_quizzes->num_rows > 0) ? $result_quizzes->fetch_assoc()['total'] : 0;

$query_questions = "SELECT COUNT(id) AS total FROM questions WHERE status = 'approved'";
$result_questions = $conn->query($query_questions);
$total_questions = ($result_questions && $result_questions->num_rows > 0) ? $result_questions->fetch_assoc()['total'] : 0;

$truy_van_de_moi = "SELECT q.id, q.title, q.subject, q.num_questions, q.created_at, u.full_name AS creator_name 
                    FROM quizzes q JOIN users u ON q.creator_username = u.username 
                    WHERE q.status = 'completed' ORDER BY q.created_at DESC LIMIT 4";
$ket_qua_de_moi = $conn->query($truy_van_de_moi);

function getSubjectIcon($subject) {
    $sub = mb_strtolower($subject, 'UTF-8');
    if (strpos($sub, 'toán') !== false) return ['icon' => 'fa-calculator', 'color' => '#3182ce'];
    if (strpos($sub, 'lý') !== false) return ['icon' => 'fa-magnet', 'color' => '#dd6b20'];
    if (strpos($sub, 'hóa') !== false) return ['icon' => 'fa-flask', 'color' => '#38a169'];
    if (strpos($sub, 'anh') !== false) return ['icon' => 'fa-language', 'color' => '#e53e3e'];
    if (strpos($sub, 'tin') !== false) return ['icon' => 'fa-laptop-code', 'color' => '#805ad5'];
    if (strpos($sub, 'sử') !== false) return ['icon' => 'fa-landmark', 'color' => '#c05621'];
    return ['icon' => 'fa-file-alt', 'color' => '#718096'];
}

// ================= PAGE CONFIG =================
$page_title = 'QuizMaster - Nền Tảng Học Tập & Thi Trắc Nghiệm';
$page_css = 'index.css';
$show_auth_modal = true; // Hiển thị modal đăng nhập

// ================= HEADER =================
require_once 'includes/layouts/header.php';
?>

<!-- ================= NAVBAR ================= -->
<?php require_once 'includes/sections/navbar.php'; ?>

<!-- ================= HERO ================= -->
<?php require_once 'includes/sections/hero.php'; ?>

<!-- ================= RECENT DOCS ================= -->
<?php require_once 'includes/sections/recent_docs.php'; ?>

<!-- ================= FEATURES ================= -->
<?php require_once 'includes/sections/features.php'; ?>

<!-- ================= STATS ================= -->
<?php require_once 'includes/sections/stats.php'; ?>

<!-- ================= CTA ================= -->
<?php require_once 'includes/sections/cta.php'; ?>

<!-- ================= FOOTER ================= -->
<?php require_once 'includes/layouts/footer.php'; ?>

<script>
    window.APP_CONFIG = {
        // Trình duyệt sẽ dịch dòng này thành true hoặc false trước khi gọi file JS
        isLoggedIn: <?php echo $is_logged_in ? 'true' : 'false'; ?>
    };
</script>

<script src="assets/js/main.js"></script>
</body>
</html>