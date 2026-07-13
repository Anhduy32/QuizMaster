document.addEventListener('DOMContentLoaded', () => {
    // 1. Modal Auth Logic
    const authModal = document.getElementById('authModal');
    document.querySelectorAll('.check-auth-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (isLoggedIn) { window.location.href = this.getAttribute('data-target'); } 
            else { authModal.classList.add('active'); }
        });
    });

    // 2. Scroll Animation cho Menu
    const sections = document.querySelectorAll("section[id]");
    const navLinks = document.querySelectorAll(".nav-link:not(.btn-nav-dashboard)");
    window.addEventListener("scroll", () => {
        let scrollY = window.pageYOffset || document.documentElement.scrollTop;
        sections.forEach(current => {
            const sectionTop = current.offsetTop - 100;
            const sectionId = current.getAttribute("id");
            if (scrollY > sectionTop && scrollY <= sectionTop + current.offsetHeight) {
                navLinks.forEach(link => {
                    link.classList.remove("active");
                    if (link.getAttribute("href") === "#" + sectionId) link.classList.add("active");
                });
            }
        });
    });

    // 3. Stats Counter
    const counters = document.querySelectorAll('.stat-number');
    let counted = false;
    window.addEventListener('scroll', () => {
        const statsSection = document.getElementById('stats');
        if (statsSection && !counted && window.scrollY + window.innerHeight > statsSection.offsetTop + 100) {
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const update = () => {
                    const count = +counter.innerText;
                    const inc = target / 200;
                    if (count < target) {
                        counter.innerText = Math.ceil(count + inc);
                        setTimeout(update, 10);
                    } else { counter.innerText = target; }
                };
                update();
            });
            counted = true;
        }
    });
});

function closeAuthModal() { document.getElementById('authModal').classList.remove('active'); }