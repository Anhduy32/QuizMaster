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

<!-- ================= MAIN.JS ================= -->
<script src="js/main.js"></script>

<script>
    // Khởi tạo các chức năng chính
    document.addEventListener('DOMContentLoaded', function() {
        // Auth Modal
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        const authModal = document.getElementById('authModal');
        
        // Xử lý link yêu cầu đăng nhập
        document.querySelectorAll('.check-auth-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if (isLoggedIn) {
                    window.location.href = this.getAttribute('data-target');
                } else {
                    if (authModal) {
                        authModal.classList.add('active');
                    }
                }
            });
        });

        // Đóng modal khi click outside
        if (authModal) {
            authModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        }

        // Menu active trên scroll
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link:not(.btn-nav-dashboard)');

        function activateMenu() {
            let scrollY = window.pageYOffset || document.documentElement.scrollTop;
            sections.forEach(current => {
                const sectionHeight = current.offsetHeight;
                const sectionTop = current.offsetTop - 100;
                const sectionId = current.getAttribute('id');
                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === '#' + sectionId) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        }
        window.addEventListener('scroll', activateMenu);

        // Counter animation cho stats
        const counters = document.querySelectorAll('.stat-number');
        const speed = 200;
        let counted = false;

        function runCounters() {
            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-target');
                    const count = +counter.innerText;
                    const inc = target / speed;
                    if (count < target) {
                        counter.innerText = Math.ceil(count + inc);
                        setTimeout(updateCount, 10);
                    } else {
                        counter.innerText = target;
                    }
                };
                updateCount();
            });
        }

        // Trigger counter khi scroll đến stats section
        window.addEventListener('scroll', () => {
            const statsSection = document.getElementById('stats');
            if (!counted && statsSection && window.scrollY + window.innerHeight > statsSection.offsetTop + 100) {
                runCounters();
                counted = true;
            }
        });

        // Mobile menu toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', function() {
                navMenu.classList.toggle('active');
                this.classList.toggle('active');
            });
        }

        // Dropdown toggle cho mobile
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.closest('.dropdown');
                if (parent) {
                    parent.classList.toggle('active');
                }
            });
        });

        // Smooth scroll cho anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Hiệu ứng fade-in khi scroll
        const fadeElements = document.querySelectorAll('.fade-in');
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        fadeElements.forEach(el => observer.observe(el));

        // Xử lý form search nếu có
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const input = this.querySelector('input[type="text"]');
                if (input && input.value.trim()) {
                    window.location.href = '/search?q=' + encodeURIComponent(input.value.trim());
                }
            });
        }

        // Toast notification system
        window.showToast = function(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                const container = document.createElement('div');
                container.id = 'toast-container';
                container.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                `;
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            const colors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };

            toast.style.cssText = `
                background: white;
                padding: 16px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border-left: 4px solid ${colors[type] || colors.success};
                animation: slideInRight 0.3s ease;
                min-width: 300px;
                max-width: 400px;
            `;
            toast.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}" 
                       style="color: ${colors[type] || colors.success}; font-size: 20px;"></i>
                    <span style="flex: 1; color: #1f2937; font-size: 14px;">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" 
                            style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 16px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            document.getElementById('toast-container').appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        };

        // Thêm style cho animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            .fade-in {
                opacity: 0;
                transform: translateY(30px);
                transition: all 0.6s ease;
            }
            .fade-in.visible {
                opacity: 1;
                transform: translateY(0);
            }
        `;
        document.head.appendChild(style);

        console.log('QuizMaster initialized successfully!');
    });
</script>