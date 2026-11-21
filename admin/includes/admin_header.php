<?php
// admin/includes/admin_header.php
require_once __DIR__ . '/admin_init.php';

$currentPage = $currentPage ?? 'dashboard';
$flash = get_flash();

// Unread messages count for sidebar badge
$unreadCount = (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();

// Mapping: group → pages yang termasuk di dalamnya
$navGroups = [
    'portofolio'  => ['projects', 'categories', 'skills'],
    'profil'      => ['profile', 'experiences', 'education', 'social-links'],
    'komunikasi'  => ['messages'],
    'sistem'      => ['settings'],
];

// Tentukan grup mana yang aktif (auto-expand)
$activeGroup = '';
foreach ($navGroups as $group => $pages) {
    if (in_array($currentPage, $pages)) {
        $activeGroup = $group;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin CMS') ?> — MyFolio</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Admin Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="d-flex">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar p-3">

        <!-- ── Brand ── -->
        <div class="d-flex align-items-center gap-2 px-2 pt-2 pb-3">
            <span style="width:38px;height:38px;border-radius:10px;background:var(--color-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 3px 8px rgba(92,111,43,.25);">
                <i class="bi bi-person-workspace text-white" style="font-size:1.1rem;"></i>
            </span>
            <div>
                <div class="admin-brand">My<span style="color:var(--color-primary);">Folio</span></div>
                <div style="font-size:0.65rem;color:var(--color-muted);letter-spacing:.3px;">CMS Admin Panel v2.0</div>
            </div>
        </div>

        <!-- ── Admin Info ── -->
        <div style="margin:0 4px 10px;padding:10px 12px;background:#F4F6EE;border-radius:10px;border:1px solid #E1E6D5;">
            <div style="font-size:0.66rem;color:#788070;margin-bottom:3px;letter-spacing:.4px;font-weight:700;">LOGGED IN AS</div>
            <div class="d-flex align-items-center gap-2">
                <span style="width:28px;height:28px;border-radius:50%;background:var(--color-primary);display:flex;align-items:center;justify-content:center;font-size:.8rem;color:#fff;font-weight:700;flex-shrink:0;">
                    <?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?>
                </span>
                <span style="font-size:.875rem;font-weight:700;color:var(--color-dark);"><?= e($_SESSION['admin_username'] ?? 'admin') ?></span>
            </div>
        </div>

        <!-- ══════════════════════════════════════
             NAVIGATION — Collapsible Groups
        ══════════════════════════════════════ -->
        <nav class="nav flex-column flex-grow-1">

            <!-- ── MAIN (selalu tampil, tidak collapsible) ── -->
            <a href="dashboard.php" class="admin-nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <div style="height:6px;"></div>

            <!-- ── GROUP: PORTOFOLIO ── -->
            <?php $isOpen = $activeGroup === 'portofolio'; ?>
            <button class="nav-group-toggle <?= $isOpen ? 'open' : 'collapsed' ?>"
                    data-bs-toggle="collapse"
                    data-bs-target="#grpPortofolio"
                    aria-expanded="<?= $isOpen ? 'true' : 'false' ?>">
                <span class="d-flex align-items-center gap-2">
                    <i class="bi bi-collection"></i>
                    <span>Portofolio</span>
                </span>
                <i class="bi bi-chevron-right toggle-chevron"></i>
            </button>
            <div class="collapse nav-group-body <?= $isOpen ? 'show' : '' ?>" id="grpPortofolio">
                <a href="projects.php" class="admin-nav-link sub <?= $currentPage === 'projects' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2"></i><span>Proyek</span>
                </a>
                <a href="categories.php" class="admin-nav-link sub <?= $currentPage === 'categories' ? 'active' : '' ?>">
                    <i class="bi bi-tags"></i><span>Kategori Proyek</span>
                </a>
                <a href="skills.php" class="admin-nav-link sub <?= $currentPage === 'skills' ? 'active' : '' ?>">
                    <i class="bi bi-stars"></i><span>Keahlian (Skills)</span>
                </a>
            </div>

            <!-- ── GROUP: PROFIL ── -->
            <?php $isOpen = $activeGroup === 'profil'; ?>
            <button class="nav-group-toggle <?= $isOpen ? 'open' : 'collapsed' ?>"
                    data-bs-toggle="collapse"
                    data-bs-target="#grpProfil"
                    aria-expanded="<?= $isOpen ? 'true' : 'false' ?>">
                <span class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-lines-fill"></i>
                    <span>Profil</span>
                </span>
                <i class="bi bi-chevron-right toggle-chevron"></i>
            </button>
            <div class="collapse nav-group-body <?= $isOpen ? 'show' : '' ?>" id="grpProfil">
                <a href="profile.php" class="admin-nav-link sub <?= $currentPage === 'profile' ? 'active' : '' ?>">
                    <i class="bi bi-person-badge"></i><span>Data Pribadi</span>
                </a>
                <a href="experiences.php" class="admin-nav-link sub <?= $currentPage === 'experiences' ? 'active' : '' ?>">
                    <i class="bi bi-briefcase"></i><span>Pengalaman</span>
                </a>
                <a href="education.php" class="admin-nav-link sub <?= $currentPage === 'education' ? 'active' : '' ?>">
                    <i class="bi bi-mortarboard"></i><span>Pendidikan</span>
                </a>
                <a href="social-links.php" class="admin-nav-link sub <?= $currentPage === 'social-links' ? 'active' : '' ?>">
                    <i class="bi bi-link-45deg"></i><span>Social Links</span>
                </a>
            </div>

            <!-- ── GROUP: KOMUNIKASI ── -->
            <?php $isOpen = $activeGroup === 'komunikasi'; ?>
            <button class="nav-group-toggle <?= $isOpen ? 'open' : 'collapsed' ?>"
                    data-bs-toggle="collapse"
                    data-bs-target="#grpKomunikasi"
                    aria-expanded="<?= $isOpen ? 'true' : 'false' ?>">
                <span class="d-flex align-items-center gap-2">
                    <i class="bi bi-chat-dots"></i>
                    <span>Komunikasi</span>
                </span>
                <span class="d-flex align-items-center gap-1">
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-danger rounded-pill" style="font-size:.6rem;padding:3px 6px;"><?= $unreadCount ?></span>
                    <?php endif; ?>
                    <i class="bi bi-chevron-right toggle-chevron"></i>
                </span>
            </button>
            <div class="collapse nav-group-body <?= $isOpen ? 'show' : '' ?>" id="grpKomunikasi">
                <a href="messages.php" class="admin-nav-link sub <?= $currentPage === 'messages' ? 'active' : '' ?>">
                    <i class="bi bi-envelope"></i>
                    <span>Pesan Masuk</span>
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-auto" style="font-size:.6rem;"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- ── GROUP: SISTEM ── -->
            <?php $isOpen = $activeGroup === 'sistem'; ?>
            <button class="nav-group-toggle <?= $isOpen ? 'open' : 'collapsed' ?>"
                    data-bs-toggle="collapse"
                    data-bs-target="#grpSistem"
                    aria-expanded="<?= $isOpen ? 'true' : 'false' ?>">
                <span class="d-flex align-items-center gap-2">
                    <i class="bi bi-sliders"></i>
                    <span>Sistem</span>
                </span>
                <i class="bi bi-chevron-right toggle-chevron"></i>
            </button>
            <div class="collapse nav-group-body <?= $isOpen ? 'show' : '' ?>" id="grpSistem">
                <a href="settings.php" class="admin-nav-link sub <?= $currentPage === 'settings' ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i><span>Pengaturan</span>
                </a>
            </div>

        </nav>

        <!-- ── Footer Actions ── -->
        <hr class="sidebar-divider mt-3 mb-2">
        <div class="d-flex flex-column gap-1 pb-1">
            <a href="../index.php" target="_blank" class="admin-nav-link" style="color:var(--color-primary);font-size:.84rem;font-weight:600;">
                <i class="bi bi-box-arrow-up-right" style="color:var(--color-primary);"></i><span>Lihat Portofolio</span>
            </a>
            <a href="logout.php" class="admin-nav-link" style="color:#DC2626;font-size:.84rem;font-weight:600;">
                <i class="bi bi-power" style="color:#DC2626;"></i><span>Keluar</span>
            </a>
        </div>

    </aside>

    <!-- MAIN BODY -->
    <div class="flex-grow-1 d-flex flex-column overflow-auto" style="height: 100vh;">
        <!-- Top Header Bar -->
        <header class="admin-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold"><?= e($pageTitle ?? 'Dashboard') ?></h4>
                <div class="text-muted small"><?= e($pageSubtitle ?? 'Panel Pengelolaan Konten Portofolio') ?></div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="../index.php" target="_blank" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-eye"></i> Pratinjau Website
                </a>
            </div>
        </header>

        <!-- Content Area -->
        <main class="p-4 p-md-5 flex-grow-1">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2 fs-5"></i>
                    <div><?= e($flash['message']) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
