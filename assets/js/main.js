/**
 * assets/js/main.js - MyFolio Frontend Logic
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Project Category Filter (No Page Reload)
    const filterBtns = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-item');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filterValue = btn.getAttribute('data-filter');

            projectCards.forEach(card => {
                const cardCat = card.getAttribute('data-category');
                if (filterValue === 'all' || cardCat === filterValue) {
                    card.style.display = 'block';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 30);
                } else {
                    card.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.96)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 200);
                }
            });
        });
    });

    // 2. Smooth Scrolling for Navigation
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId !== '#' && document.querySelector(targetId)) {
                e.preventDefault();
                const elem = document.querySelector(targetId);
                const offset = 80;
                const bodyRect = document.body.getBoundingClientRect().top;
                const elemRect = elem.getBoundingClientRect().top;
                const elemPosition = elemRect - bodyRect;
                const offsetPosition = elemPosition - offset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });

                // Close mobile navbar if open
                const navCollapse = document.getElementById('folioNav');
                if (navCollapse && navCollapse.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
                    if (bsCollapse) bsCollapse.hide();
                }
            }
        });
    });

    // 3. Contact Form Submission (AJAX)
    const contactForm = document.getElementById('contactForm');
    const feedbackBox = document.getElementById('contactFeedback');

    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Form validation
            const name = contactForm.querySelector('[name="sender_name"]').value.trim();
            const email = contactForm.querySelector('[name="sender_email"]').value.trim();
            const message = contactForm.querySelector('[name="message"]').value.trim();

            if (!name || !email || !message) {
                showFeedback('danger', 'Harap isi kolom nama, email, dan pesan.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

            const formData = new FormData(contactForm);

            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const data = await response.json();
                if (data.status === 'success') {
                    showFeedback('success', data.message || 'Pesan Anda berhasil dikirim!');
                    contactForm.reset();
                } else {
                    showFeedback('danger', data.message || 'Gagal mengirim pesan.');
                }
            } catch (err) {
                showFeedback('danger', 'Terjadi kesalahan sistem saat mengirim pesan.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    function showFeedback(type, text) {
        if (!feedbackBox) return;
        const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        feedbackBox.innerHTML = `
            <div class="alert alert-${type} d-flex align-items-center mb-4 fade show" role="alert">
                <i class="bi ${icon} me-2 fs-5"></i>
                <div>${text}</div>
            </div>
        `;
    }
});
