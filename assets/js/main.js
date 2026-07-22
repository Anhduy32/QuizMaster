/**
 * QuizMaster - Main JavaScript
 * Version: 1.0.0
 */

(function() {
    'use strict';

    // ==================== CONFIGURATION ====================
    const CONFIG = {
        animationDelay: 200,
        scrollOffset: 100,
        counterSpeed: 200,
        toastDuration: 5000
    };

    // ==================== UTILITY FUNCTIONS ====================
    const Utils = {
        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Format number with commas
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },

        // Get element position
        getOffset: function(el) {
            const rect = el.getBoundingClientRect();
            return {
                top: rect.top + window.pageYOffset,
                left: rect.left + window.pageXOffset
            };
        },

        // Check if element is in viewport
        isInViewport: function(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
    };

    // ==================== MODULES ====================

    // 1. Navigation Module
    const Navigation = {
        init: function() {
            this.menuToggle = document.querySelector('.menu-toggle');
            this.navMenu = document.querySelector('.nav-menu');
            this.navLinks = document.querySelectorAll('.nav-link:not(.btn-nav-dashboard)');
            this.sections = document.querySelectorAll('section[id]');
            this.dropdowns = document.querySelectorAll('.dropdown');

            if (this.menuToggle && this.navMenu) {
                this.setupMobileMenu();
            }

            if (this.navLinks.length && this.sections.length) {
                this.setupScrollSpy();
            }

            if (this.dropdowns.length) {
                this.setupDropdowns();
            }

            this.setupSmoothScroll();
        },

        setupMobileMenu: function() {
            this.menuToggle.addEventListener('click', () => {
                this.navMenu.classList.toggle('active');
                this.menuToggle.classList.toggle('active');
                document.body.classList.toggle('menu-open');
            });

            // Close menu on link click (mobile)
            this.navMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        this.navMenu.classList.remove('active');
                        this.menuToggle.classList.remove('active');
                        document.body.classList.remove('menu-open');
                    }
                });
            });

            // Close menu on outside click
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    const isClickInside = this.navMenu.contains(e.target) || 
                                         this.menuToggle.contains(e.target);
                    if (!isClickInside) {
                        this.navMenu.classList.remove('active');
                        this.menuToggle.classList.remove('active');
                        document.body.classList.remove('menu-open');
                    }
                }
            });
        },

        setupScrollSpy: function() {
            const scrollHandler = Utils.debounce(() => {
                let scrollY = window.pageYOffset || document.documentElement.scrollTop;
                
                this.sections.forEach(section => {
                    const sectionTop = section.offsetTop - CONFIG.scrollOffset;
                    const sectionBottom = sectionTop + section.offsetHeight;
                    const sectionId = section.getAttribute('id');

                    if (scrollY >= sectionTop && scrollY < sectionBottom) {
                        this.navLinks.forEach(link => {
                            link.classList.remove('active');
                            if (link.getAttribute('href') === `#${sectionId}`) {
                                link.classList.add('active');
                            }
                        });
                    }
                });
            }, 100);

            window.addEventListener('scroll', scrollHandler);
            // Trigger once on load
            setTimeout(scrollHandler, 100);
        },

        setupDropdowns: function() {
            this.dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                if (toggle) {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const parent = this.closest('.dropdown');
                        if (parent) {
                            // Close other dropdowns
                            document.querySelectorAll('.dropdown').forEach(d => {
                                if (d !== parent) {
                                    d.classList.remove('active');
                                }
                            });
                            parent.classList.toggle('active');
                        }
                    });
                }
            });

            // Close dropdowns on outside click
            document.addEventListener('click', () => {
                this.dropdowns.forEach(d => d.classList.remove('active'));
            });
        },

        setupSmoothScroll: function() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && href !== '#') {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                            // Update URL without reload
                            history.pushState(null, null, href);
                        }
                    }
                });
            });
        }
    };

    // 2. Counter Module
    const Counter = {
        init: function() {
            this.counters = document.querySelectorAll('.stat-number');
            this.counterSection = document.getElementById('stats');
            this.counted = false;

            if (this.counters.length && this.counterSection) {
                this.setupCounterTrigger();
            }
        },

        setupCounterTrigger: function() {
            const triggerCounter = () => {
                if (!this.counted) {
                    const rect = this.counterSection.getBoundingClientRect();
                    const windowHeight = window.innerHeight || document.documentElement.clientHeight;
                    
                    if (rect.top <= windowHeight - 100) {
                        this.runCounters();
                        this.counted = true;
                    }
                }
            };

            window.addEventListener('scroll', Utils.debounce(triggerCounter, 100));
            // Trigger on load
            setTimeout(triggerCounter, 300);
        },

        runCounters: function() {
            this.counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target')) || 0;
                const duration = CONFIG.counterSpeed;
                let current = 0;
                const increment = target / duration;

                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.textContent = Math.ceil(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };

                updateCounter();
            });
        }
    };

    // 3. Animation Module (Fade In)
    const Animation = {
        init: function() {
            this.elements = document.querySelectorAll('.fade-in, .animate-on-scroll');
            
            if (this.elements.length) {
                this.setupObserver();
            }
        },

        setupObserver: function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        // Unobserve after animation
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            this.elements.forEach(el => observer.observe(el));
        }
    };

    // 4. Toast Notification Module
    const Toast = {
        init: function() {
            this.container = document.getElementById('toast-container');
            if (!this.container) {
                this.createContainer();
            }
        },

        createContainer: function() {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 400px;
                width: 100%;
            `;
            document.body.appendChild(this.container);
        },

        show: function(message, type = 'success') {
            const colors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };

            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };

            const toast = document.createElement('div');
            toast.className = 'toast-item';
            toast.style.cssText = `
                background: white;
                padding: 16px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border-left: 4px solid ${colors[type] || colors.success};
                animation: slideInRight 0.3s ease;
                display: flex;
                align-items: center;
                gap: 12px;
            `;

            toast.innerHTML = `
                <i class="fas ${icons[type] || icons.success}" 
                   style="color: ${colors[type] || colors.success}; font-size: 20px;"></i>
                <span style="flex: 1; color: #1f2937; font-size: 14px;">${message}</span>
                <button class="toast-close" 
                        style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 16px;">
                    <i class="fas fa-times"></i>
                </button>
            `;

            // Add close functionality
            toast.querySelector('.toast-close').addEventListener('click', () => {
                this.removeToast(toast);
            });

            this.container.appendChild(toast);

            // Auto remove
            setTimeout(() => {
                this.removeToast(toast);
            }, CONFIG.toastDuration);
        },

        removeToast: function(toast) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        },

        success: function(message) {
            this.show(message, 'success');
        },

        error: function(message) {
            this.show(message, 'error');
        },

        warning: function(message) {
            this.show(message, 'warning');
        },

        info: function(message) {
            this.show(message, 'info');
        }
    };

    // 5. Auth Module
    const Auth = {
        init: function() {
            this.modal = document.getElementById('authModal');
            this.isLoggedIn = document.body.dataset.loggedIn === 'true';

            if (this.modal) {
                this.setupModal();
            }

            this.setupAuthLinks();
        },

        setupModal: function() {
            // Close on backdrop click
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.closeModal();
                }
            });

            // Close on ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.modal.classList.contains('active')) {
                    this.closeModal();
                }
            });

            // Prevent scroll when modal is open
            this.modal.addEventListener('open', () => {
                document.body.style.overflow = 'hidden';
            });

            this.modal.addEventListener('close', () => {
                document.body.style.overflow = '';
            });
        },

        openModal: function() {
            if (this.modal) {
                this.modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        },

        closeModal: function() {
            if (this.modal) {
                this.modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        },

        setupAuthLinks: function() {
            document.querySelectorAll('.check-auth-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (this.isLoggedIn) {
                        window.location.href = link.getAttribute('data-target');
                    } else {
                        this.openModal();
                    }
                });
            });
        }
    };

    // 6. Search Module
    const Search = {
        init: function() {
            this.form = document.querySelector('.search-form');
            this.input = this.form ? this.form.querySelector('input[type="text"]') : null;

            if (this.form && this.input) {
                this.setupSearch();
            }
        },

        setupSearch: function() {
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                const query = this.input.value.trim();
                if (query) {
                    window.location.href = `/search?q=${encodeURIComponent(query)}`;
                }
            });

            // Auto submit on Enter (already handled by form)
        }
    };

    // ==================== INITIALIZATION ====================
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all modules
        Navigation.init();
        Counter.init();
        Animation.init();
        Toast.init();
        Auth.init();
        Search.init();

        // Expose Toast globally for use in inline scripts
        window.QuizMaster = {
            toast: Toast,
            auth: Auth,
            utils: Utils
        };

        console.log('✅ QuizMaster initialized successfully!');
    });

})();