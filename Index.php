<?php
session_start();
include 'config/database.php';

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
    if ($nguoi_dung) { $ho_va_ten = $nguoi_dung['full_name'] ?? $ten_dang_nhap; }
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizMaster - Nền Tảng Học Tập & Thi Trắc Nghiệm</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css?v=<?php echo time(); ?>">
</head>
<body>

    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">
                <i class="fa-solid fa-graduation-cap"></i> QUIZMASTER
            </a>
            <nav class="nav-menu">
                <a href="#home" class="nav-link">Trang Chủ</a>
                <a href="#recent-docs" class="nav-link">Đề Thi Mới</a>
                <a href="#features" class="nav-link">Khám Phá</a>
                <a href="#stats" class="nav-link">Thống Kê</a>
                
                <?php if ($is_logged_in): ?>
                    <a href="home.php" class="btn-nav-dashboard"><i class="fas fa-home"></i> Bảng điều khiển</a>
                    <a href="modules/user/update_profile.php" class="nav-link"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($ho_va_ten); ?></a>
                    <a href="modules/auth/logout.php" class="btn-logout" title="Đăng xuất"><i class="fa-solid fa-right-from-bracket"></i></a>
                <?php else: ?>
                    <a href="modules/auth/login.php" class="btn-nav-login">Đăng nhập</a>
                    <a href="modules/auth/register.php" class="btn-nav-register">Đăng ký</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <section id="home" class="hero-section">
        <div class="hero-background"></div>
        <div class="hero-content text-center">
            <h1 class="hero-title">
                Học tập & Đánh giá năng lực<br><span>không giới hạn</span>
            </h1>
            <p class="hero-desc">Luyện thi, tự tạo bài kiểm tra và chia sẻ kiến thức với cộng đồng hàng ngàn người dùng trên toàn quốc.</p>
            
            <div class="hero-search-box">
                <form action="explore.php" method="GET" class="search-form">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" placeholder="Tìm kiếm đề thi, môn học, hoặc từ khóa..." class="search-input">
                    <button type="submit" class="search-btn">Tìm Kiếm</button>
                </form>
            </div>
            
            <div class="hero-tags">
                <span>Xu hướng:</span>
                <a href="#" class="tag">Thi THPT Quốc Gia</a>
                <a href="#" class="tag">Tiếng Anh Giao Tiếp</a>
                <a href="#" class="tag">Tin học văn phòng</a>
                <a href="#" class="tag">Lịch sử Đảng</a>
            </div>
        </div>
    </section>

    <section id="recent-docs" class="section recent-docs-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Đề thi được cập nhật gần đây</h2>
                <a href="#" class="view-all-link check-auth-link" data-target="explore.php">Xem tất cả <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="docs-grid">
                <?php if ($ket_qua_de_moi && $ket_qua_de_moi->num_rows > 0): ?>
                    <?php while ($de_thi = $ket_qua_de_moi->fetch_assoc()): 
                        $style = getSubjectIcon($de_thi['subject']);
                        $ngay_tao = strtotime($de_thi['created_at']);
                        $is_new = ($ngay_tao > strtotime('-7 days'));
                    ?>
                        <div class="doc-card">
                            <div class="doc-icon" style="color: <?php echo $style['color']; ?>;">
                                <i class="fa-solid <?php echo $style['icon']; ?>"></i>
                            </div>
                            <div class="doc-details">
                                <h3 class="doc-title"><?php echo htmlspecialchars($de_thi['title']); ?></h3>
                                <p class="doc-meta">Môn: <?php echo htmlspecialchars($de_thi['subject']); ?> • Đăng bởi: <?php echo htmlspecialchars($de_thi['creator_name']); ?></p>
                                <div class="doc-tags">
                                    <span class="doc-badge"><?php echo $de_thi['num_questions']; ?> Câu</span>
                                    <?php if ($is_new): ?>
                                        <span class="doc-badge badge-new" style="background: #e6f4ea; color: #1e8e3e;">Mới</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="#" class="doc-action-btn check-auth-link" data-target="modules/quiz/take_quiz.php?id=<?php echo $de_thi['id']; ?>"><i class="fas fa-play"></i></a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #718096; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                        <h3 style="font-size: 1.2rem;">Hệ thống chưa có đề thi nào</h3>
                        <p style="margin-top: 5px;">Hãy trở thành người đầu tiên đóng góp bộ đề cho cộng đồng!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="features" class="section features-section">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-tag">DÀNH CHO TẤT CẢ MỌI NGƯỜI</span>
                <h2 class="section-title">Học tập hiệu quả - Chia sẻ dễ dàng</h2>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="card-icon"><i class="fa-solid fa-pen-to-square"></i></div>
                    <h3>Luyện Thi Trực Tuyến</h3>
                    <p>Thử sức với hàng ngàn đề thi phong phú. Hệ thống chấm điểm tự động và theo dõi tiến độ học tập của bạn.</p>
                    <a href="#" class="card-link check-auth-link" data-target="explore.php">Luyện tập ngay <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="feature-card">
                    <div class="card-icon"><i class="fa-solid fa-magic"></i></div>
                    <h3>Tạo Đề Thi Của Riêng Bạn</h3>
                    <p>Bạn có tài liệu hay? Hãy số hóa chúng thành bài trắc nghiệm và chia sẻ cho bạn bè, học sinh hoặc cộng đồng.</p>
                    <a href="#" class="card-link check-auth-link" data-target="modules/quiz/create_step1.php">Tạo đề thi <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="feature-card">
                    <div class="card-icon"><i class="fa-solid fa-globe"></i></div>
                    <h3>Ngân Hàng Kiến Thức</h3>
                    <p>Đóng góp câu hỏi và khám phá kho tàng tri thức khổng lồ được xây dựng bởi hàng ngàn người dùng.</p>
                    <a href="#" class="card-link check-auth-link" data-target="explore.php">Khám phá <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <section id="stats" class="section stats-section">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title text-white">Cộng đồng học tập lớn mạnh mỗi ngày</h2>
            </div>
            <div class="stats-box">
                <div class="stat-item">
                    <div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div>
                    <span class="stat-number" data-target="<?php echo htmlspecialchars($total_questions); ?>">0</span>
                    <span class="stat-label">Câu Hỏi</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fa-solid fa-file-lines"></i></div>
                    <span class="stat-number" data-target="<?php echo htmlspecialchars($total_quizzes); ?>">0</span>
                    <span class="stat-label">Đề Thi</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                    <span class="stat-number" data-target="<?php echo htmlspecialchars($total_members); ?>">0</span>
                    <span class="stat-label">Thành Viên</span>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <div class="cta-box">
                <h2>Sẵn sàng nâng tầm kiến thức của bạn?</h2>
                <p>Gia nhập cộng đồng QuizMaster ngay hôm nay để trải nghiệm môi trường học tập không giới hạn.</p>
                <?php if (!$is_logged_in): ?>
                    <a href="modules/auth/register.php" class="btn-primary cta-btn">Tham gia hoàn toàn miễn phí</a>
                <?php else: ?>
                    <a href="home.php" class="btn-primary cta-btn">Vào Bảng điều khiển ngay</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <i class="fa-solid fa-graduation-cap"></i> QUIZMASTER
                <p>Nền tảng tạo đề thi và quản lý ngân hàng câu hỏi thông minh.</p>
            </div>
            <div class="footer-links">
                <p>&copy; 2026 Bản quyền thuộc về WebTaoBoDeTuDong.</p>
            </div>
        </div>
    </footer>

    <div id="authModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-close" onclick="closeAuthModal()">&times;</div>
            <div class="modal-icon"><i class="fas fa-lock"></i></div>
            <h3>Yêu cầu đăng nhập</h3>
            <p>Vui lòng đăng nhập hoặc tạo tài khoản để có thể làm bài thi và sử dụng các công cụ học tập.</p>
            <div class="modal-buttons">
                <a href="modules/auth/login.php" class="btn-modal-login">Đăng nhập ngay</a>
                <a href="modules/auth/register.php" class="btn-modal-register">Tạo tài khoản mới</a>
            </div>
        </div>
    </div>

    <script>
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

        document.querySelectorAll('.check-auth-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if (isLoggedIn) { window.location.href = this.getAttribute('data-target'); } 
                else { document.getElementById('authModal').classList.add('active'); }
            });
        });

        function closeAuthModal() { document.getElementById('authModal').classList.remove('active'); }

        const sections = document.querySelectorAll("section[id]");
        const navLinks = document.querySelectorAll(".nav-link:not(.btn-nav-dashboard)");

        function activateMenu() {
            let scrollY = window.pageYOffset || document.documentElement.scrollTop;
            sections.forEach(current => {
                const sectionHeight = current.offsetHeight;
                const sectionTop = current.offsetTop - 100;
                const sectionId = current.getAttribute("id");
                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    navLinks.forEach(link => {
                        link.classList.remove("active");
                        if (link.getAttribute("href") === "#" + sectionId) { link.classList.add("active"); }
                    });
                }
            });
        }
        window.addEventListener("scroll", activateMenu);

        const counters = document.querySelectorAll('.stat-number');
        const speed = 200; 

        const runCounters = () => {
            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-target');
                    const count = +counter.innerText;
                    const inc = target / speed;
                    if (count < target) {
                        counter.innerText = Math.ceil(count + inc);
                        setTimeout(updateCount, 10);
                    } else { counter.innerText = target; }
                };
                updateCount();
            });
        }

        let counted = false;
        window.addEventListener('scroll', () => {
            const statsSection = document.getElementById('stats');
            if (!counted && window.scrollY + window.innerHeight > statsSection.offsetTop + 100) {
                runCounters(); counted = true;
            }
        });
    </script>
</body>
</html>