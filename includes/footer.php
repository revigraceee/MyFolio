<?php
// includes/footer.php
?>
<footer class="footer-folio">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <div class="fw-bold font-heading fs-5 text-dark mb-1">
                    <?= e($profile['full_name'] ?? 'MyFolio') ?><span class="brand-dot"></span>
                </div>
                <div class="small text-muted">
                    &copy; <?= date('Y') ?> All rights reserved. Personal Portfolio for Digital Business Student.
                </div>
            </div>

            <div class="col-md-6 text-center text-md-end">
                <div class="d-flex flex-wrap justify-content-center justify-content-md-end align-items-center gap-3">
                    <?php if (!empty($socialLinks)): ?>
                        <?php foreach ($socialLinks as $soc): 
                            $iconClass = 'bi-link-45deg';
                            $p = strtolower($soc['platform']);
                            if (str_contains($p, 'linkedin')) $iconClass = 'bi-linkedin';
                            elseif (str_contains($p, 'instagram')) $iconClass = 'bi-instagram';
                            elseif (str_contains($p, 'github')) $iconClass = 'bi-github';
                            elseif (str_contains($p, 'twitter') || str_contains($p, 'x')) $iconClass = 'bi-twitter-x';
                            elseif (str_contains($p, 'tiktok')) $iconClass = 'bi-tiktok';
                        ?>
                            <a href="<?= e($soc['url']) ?>" target="_blank" class="text-muted hover-olive text-decoration-none fs-5" title="<?= e($soc['platform']) ?>">
                                <i class="bi <?= $iconClass ?>"></i>
                            </a>
                        <?php endforeach; ?>
                        <span class="text-border">|</span>
                    <?php endif; ?>

                    <a href="#hero" class="text-muted text-decoration-none small">
                        Ke Atas <i class="bi bi-arrow-up"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom Frontend JS -->
<script src="assets/js/main.js"></script>
</body>
</html>

