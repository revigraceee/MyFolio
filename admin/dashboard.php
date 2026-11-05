<?php
// admin/dashboard.php
$currentPage = 'dashboard';
$pageTitle = 'Dashboard Overview';
$pageSubtitle = 'Ringkasan metrik konten portofolio dan aktivitas terbaru';

require_once __DIR__ . '/includes/admin_header.php';

// Calculate Database Metrics (Section 5.3)
$totalProjects   = (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'published'")->fetchColumn();
$totalDrafts     = (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'draft'")->fetchColumn();
$totalExperience = (int)$pdo->query("SELECT COUNT(*) FROM experiences")->fetchColumn();
$totalEducation  = (int)$pdo->query("SELECT COUNT(*) FROM education")->fetchColumn();
$totalSkills     = (int)$pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn();
$totalMessages   = (int)$pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$unreadMessages  = (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();

// Fetch Recent Messages
$recentMessages = $pdo->query("SELECT * FROM messages ORDER BY sent_at DESC LIMIT 5")->fetchAll();
?>

<!-- Metrics Grid (Section 5.3) -->
<div class="row g-4 mb-5">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-semibold">Proyek Published</span>
                <div class="stat-icon"><i class="bi bi-folder-check"></i></div>
            </div>
            <div class="h3 fw-bold text-dark mb-1"><?= $totalProjects ?></div>
            <div class="small text-muted">
                <?= $totalDrafts ?> Proyek draft tersimpan
            </div>
            <a href="projects.php" class="small text-primary-olive text-decoration-none fw-semibold mt-2 d-inline-block">Kelola Proyek &rarr;</a>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-semibold">Pengalaman & Karir</span>
                <div class="stat-icon"><i class="bi bi-briefcase"></i></div>
            </div>
            <div class="h3 fw-bold text-dark mb-1"><?= $totalExperience ?></div>
            <div class="small text-muted">Work, Org, Committee, etc.</div>
            <a href="experiences.php" class="small text-primary-olive text-decoration-none fw-semibold mt-2 d-inline-block">Kelola Pengalaman &rarr;</a>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-semibold">Riwayat Pendidikan</span>
                <div class="stat-icon"><i class="bi bi-mortarboard"></i></div>
            </div>
            <div class="h3 fw-bold text-dark mb-1"><?= $totalEducation ?></div>
            <div class="small text-muted">Jenjang akademik formal</div>
            <a href="education.php" class="small text-primary-olive text-decoration-none fw-semibold mt-2 d-inline-block">Kelola Pendidikan &rarr;</a>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-semibold">Keahlian (Skills)</span>
                <div class="stat-icon"><i class="bi bi-tools"></i></div>
            </div>
            <div class="h3 fw-bold text-dark mb-1"><?= $totalSkills ?></div>
            <div class="small text-muted">Tools, Business, Soft Skills</div>
            <a href="skills.php" class="small text-primary-olive text-decoration-none fw-semibold mt-2 d-inline-block">Kelola Skill &rarr;</a>
        </div>
    </div>
</div>

<!-- Shortcuts & Recent Inbox -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Pesan Masuk Terbaru</h5>
                    <span class="text-muted small"><?= $unreadMessages ?> pesan baru belum dibaca</span>
                </div>
                <a href="messages.php" class="btn btn-outline-secondary btn-sm">Buka Inbox Lengkap</a>
            </div>

            <?php if (empty($recentMessages)): ?>
                <div class="text-center py-4 text-muted small">Belum ada pesan yang masuk.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead>
                            <tr>
                                <th>Pengirim</th>
                                <th>Subjek</th>
                                <th>Waktu</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentMessages as $msg): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($msg['sender_name']) ?></div>
                                        <div class="small text-muted"><?= e($msg['sender_email']) ?></div>
                                    </td>
                                    <td class="text-dark fw-medium"><?= e($msg['subject']) ?></td>
                                    <td class="small text-muted"><?= date('d M Y, H:i', strtotime($msg['sent_at'])) ?></td>
                                    <td>
                                        <?php if ($msg['is_read']): ?>
                                            <span class="badge bg-secondary-subtle text-secondary">Dibaca</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger fw-semibold">Baru</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-card">
            <h5 class="fw-bold mb-3 text-dark">Aksi Cepat</h5>
            <p class="text-muted small mb-4">Pintasan praktis untuk memperbarui portofolio Anda.</p>

            <div class="d-flex flex-column gap-2">
                <a href="projects.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-between py-2.5">
                    <span><i class="bi bi-plus-circle me-2 text-primary-olive"></i> Tambah Proyek Baru</span>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>
                <a href="experiences.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-between py-2.5">
                    <span><i class="bi bi-plus-circle me-2 text-primary-olive"></i> Tambah Pengalaman</span>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>
                <a href="education.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-between py-2.5">
                    <span><i class="bi bi-plus-circle me-2 text-primary-olive"></i> Tambah Pendidikan</span>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>
                <a href="skills.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-between py-2.5">
                    <span><i class="bi bi-plus-circle me-2 text-primary-olive"></i> Tambah Skill</span>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>
                <a href="profile.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-between py-2.5">
                    <span><i class="bi bi-person-gear me-2 text-primary-olive"></i> Edit Profil & Foto</span>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
