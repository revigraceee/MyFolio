<?php
// admin/includes/admin_header.php
require_once __DIR__ . '/admin_init.php';

$currentPage = $currentPage ?? 'dashboard';
$flash = get_flash();

// Unread messages count for sidebar badge
$unreadCount = (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
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
    <!-- SIDEBAR (Section 5.2) -->
    <aside class="admin-sidebar d-flex flex-column p-3">
        <div class="d-flex align-items-center gap-2 mb-4 px-2 pt-2">
            <span class="p-2 rounded-3 bg-primary-olive text-white"><i class="bi bi-person-workspace fs-5"></i></span>
            <div>
                <span class="admin-brand tracking-tight">My<span style="color: #BAC2AE;">Folio</span></span>
                <div class="text-muted" style="font-size: 0.72rem;">CMS Admin Panel v2.0</div>
            </div>
        </div>

        <div class="px-2 mb-3 text-muted small">
            Admin: <strong class="text-light"><?= e($_SESSION['admin_username'] ?? 'admin') ?></strong>
        </div>

        <nav class="nav flex-column mb-auto">
            <a href="dashboard.php" class="admin-nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="profile.php" class="admin-nav-link <?= $currentPage === 'profile' ? 'active' : '' ?>">
                <i class="bi bi-person-badge"></i>
                <span>Manage Profile</span>
            </a>
            <a href="projects.php" class="admin-nav-link <?= $currentPage === 'projects' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2"></i>
                <span>Manage Projects</span>
            </a>
            <a href="experiences.php" class="admin-nav-link <?= $currentPage === 'experiences' ? 'active' : '' ?>">
                <i class="bi bi-briefcase"></i>
                <span>Manage Experience</span>
            </a>
            <a href="education.php" class="admin-nav-link <?= $currentPage === 'education' ? 'active' : '' ?>">
                <i class="bi bi-mortarboard"></i>
                <span>Manage Education</span>
            </a>
            <a href="skills.php" class="admin-nav-link <?= $currentPage === 'skills' ? 'active' : '' ?>">
                <i class="bi bi-tools"></i>
                <span>Manage Skills</span>
            </a>
            <a href="messages.php" class="admin-nav-link <?= $currentPage === 'messages' ? 'active' : '' ?>">
                <i class="bi bi-inbox"></i>
                <span>Messages / Inbox</span>
                <?php if ($unreadCount > 0): ?>
                    <span class="badge bg-danger rounded-pill ms-auto"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>
            <a href="social-links.php" class="admin-nav-link <?= $currentPage === 'social-links' ? 'active' : '' ?>">
                <i class="bi bi-share"></i>
                <span>Social Links</span>
            </a>
            <a href="settings.php" class="admin-nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
        </nav>

        <hr class="border-secondary my-3 opacity-25">

        <div class="d-flex flex-column gap-2">
            <a href="../index.php" target="_blank" class="btn btn-outline-light btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-arrow-up-right-square"></i>
                <span>Lihat Portofolio</span>
            </a>
            <a href="logout.php" class="btn btn-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
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
