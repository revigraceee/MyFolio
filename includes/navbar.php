<?php
// includes/navbar.php
?>
<nav class="navbar navbar-expand-lg fixed-top navbar-folio py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#hero">
            <span class="navbar-brand-name"><?= e($profile['full_name'] ?? 'MyFolio') ?></span>
            <span class="brand-dot"></span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#folioNav">
            <i class="bi bi-list fs-2 text-dark"></i>
        </button>

        <div class="collapse navbar-collapse" id="folioNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <li class="nav-item"><a class="nav-link nav-link-folio" href="#hero">Home</a></li>
                <li class="nav-item"><a class="nav-link nav-link-folio" href="#about">About</a></li>
                <li class="nav-item"><a class="nav-link nav-link-folio" href="#projects">Projects</a></li>
                <li class="nav-item"><a class="nav-link nav-link-folio" href="#experience">Experience</a></li>
                <li class="nav-item"><a class="nav-link nav-link-folio" href="#education">Education</a></li>
                <li class="nav-item"><a class="nav-link nav-link-folio" href="#skills">Skills</a></li>
                <li class="nav-item"><a class="nav-link nav-link-folio" href="#contact">Contact</a></li>

                <li class="nav-item ms-lg-3 mt-3 mt-lg-0 d-flex align-items-center gap-2">
                    <?php if (!empty($profile['cv_path']) && $profile['cv_path'] !== '#'): ?>
                        <a href="<?= resolve_img($profile['cv_path'], 'cv', '#') ?>" target="_blank" class="btn btn-outline-olive btn-sm py-2 px-3">
                            <i class="bi bi-download"></i>
                            <span>Download CV</span>
                        </a>
                    <?php else: ?>
                        <a href="#contact" class="btn btn-primary-olive btn-sm py-2 px-3">
                            <i class="bi bi-send"></i>
                            <span>Let's Talk</span>
                        </a>
                    <?php endif; ?>
                    
                    <a href="admin/login.php" class="btn btn-link text-muted p-2" title="Admin Login">
                        <i class="bi bi-shield-lock fs-5"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

