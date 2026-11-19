<?php
// index.php - MyFolio v2.0 Public Portfolio
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Handle Contact Form Submission (AJAX & POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    $name    = trim($_POST['sender_name'] ?? '');
    $email   = trim($_POST['sender_email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $msg = 'Harap isi semua kolom wajib (Nama, Email, dan Pesan).';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit;
        }
        set_flash('danger', $msg);
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Format alamat email tidak valid.';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit;
        }
        set_flash('danger', $msg);
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_name, sender_email, subject, message, is_read, sent_at) VALUES (?, ?, ?, ?, 0, NOW())");
            $stmt->execute([$name, $email, $subject ?: 'Pesan Baru dari Website Portofolio', $message]);

            $msg = 'Terima kasih! Pesan Anda telah berhasil terkirim.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => $msg]);
                exit;
            }
            set_flash('success', $msg);
        } catch (PDOException $e) {
            $msg = 'Gagal menyimpan pesan. Silakan coba kembali beberapa saat lagi.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $msg]);
                exit;
            }
            set_flash('danger', $msg);
        }
    }

    if (!$isAjax) {
        header('Location: index.php#contact');
        exit;
    }
}

// 1. Fetch Profile
$profile = $pdo->query("SELECT * FROM profile LIMIT 1")->fetch() ?: [];

// 2. Fetch Projects (Only Published, ordered by is_featured DESC, sort_order ASC, created_at DESC)
$projects = $pdo->query("
    SELECT p.*, COALESCE(pc.category_name, p.category) AS display_category
    FROM projects p
    LEFT JOIN project_categories pc ON p.category_id = pc.id
    WHERE p.status = 'published'
    ORDER BY p.is_featured DESC, p.sort_order ASC, p.created_at DESC
")->fetchAll();

// Extract unique categories for filter pills
$categories = [];
foreach ($projects as $pj) {
    $cat = $pj['display_category'] ?: 'Lainnya';
    if (!empty($cat) && !in_array($cat, $categories)) {
        $categories[] = $cat;
    }
}

// 3. Fetch Experiences (ordered by sort_order ASC, period_start DESC)
$experiences = $pdo->query("SELECT * FROM experiences ORDER BY sort_order ASC, period_start DESC")->fetchAll();

// 4. Fetch Education (ordered by sort_order ASC, period_start DESC)
$educations = $pdo->query("SELECT * FROM education ORDER BY sort_order ASC, period_start DESC")->fetchAll();

// 5. Fetch Skills (ordered by category sort_order ASC, skills.sort_order ASC)
$skills = $pdo->query("
    SELECT s.*
    FROM skills s
    LEFT JOIN skill_categories sc ON sc.category_name = s.category
    ORDER BY COALESCE(sc.sort_order, 999) ASC, s.sort_order ASC, s.id ASC
")->fetchAll();
$groupedSkills = [];
foreach ($skills as $sk) {
    $cat = $sk['category'] ?: 'Lainnya';
    if (!isset($groupedSkills[$cat])) {
        $groupedSkills[$cat] = [];
    }
    $groupedSkills[$cat][] = $sk;
}

// 6. Fetch Active Social Links
$socialLinks = $pdo->query("SELECT * FROM social_links WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();

$flash = get_flash();
$avatarUrl = resolve_img($profile['avatar'] ?? null, 'profile', 'assets/img/avatar-default.png');

// Include Header & Navbar
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- HERO SECTION (Section 4.2) -->
<section id="hero" class="hero-section">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7 order-2 order-lg-1">
                <div class="pe-lg-4 text-center text-lg-start">
                    <span class="section-label">
                        <i class="bi bi-mortarboard me-1"></i> Mahasiswa Bisnis Digital
                    </span>
                    
                    <h1 class="display-3 fw-bold mb-3 text-dark">
                        Hai, Saya <span class="text-primary-olive text-decoration-underline"><?= e($profile['full_name'] ?? 'Nadia Saraswati') ?></span>
                    </h1>

                    <h2 class="h4 text-muted fw-semibold mb-4" style="line-height: 1.5;">
                        <?= e($profile['headline'] ?? 'Turning Ideas Into Meaningful Digital Experiences') ?>
                    </h2>

                    <p class="lead text-muted mb-4 fs-6" style="max-width: 600px; line-height: 1.8;">
                        <?= nl2br(e($profile['about_me'] ?? '')) ?>
                    </p>

                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start mb-5">
                        <a href="#projects" class="btn btn-primary-olive">
                            <span>Explore My Work</span>
                            <i class="bi bi-arrow-down-right"></i>
                        </a>
                        <a href="#contact" class="btn btn-outline-olive">
                            <span>Contact Me</span>
                            <i class="bi bi-chat-dots"></i>
                        </a>
                    </div>

                    <!-- Location & Socials -->
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-4 pt-3 border-top border-secondary-subtle">
                        <?php if (!empty($profile['location'])): ?>
                            <div class="d-flex align-items-center gap-2 small text-muted">
                                <i class="bi bi-geo-alt text-primary-olive"></i>
                                <span><?= e($profile['location']) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($socialLinks as $soc): ?>
                            <a href="<?= e($soc['url']) ?>" target="_blank" class="text-muted text-decoration-none small d-flex align-items-center gap-1 hover-olive">
                                <i class="bi bi-arrow-up-right-circle text-primary-olive"></i>
                                <span><?= e($soc['platform']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 order-1 order-lg-2 text-center">
                <div class="hero-avatar-container">
                    <img src="<?= $avatarUrl ?>" alt="<?= e($profile['full_name'] ?? 'Avatar') ?>" class="hero-avatar">
                    <div class="hero-floating-badge d-flex align-items-center gap-3">
                        <div class="stat-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="text-start">
                            <div class="fw-bold small text-dark">Digital Business</div>
                            <div class="text-muted" style="font-size: 0.75rem;">Strategy & Product UI/UX</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT ME SECTION (Section 4.3) -->
<section id="about" class="bg-white">
    <div class="container">
        <div class="row gy-5 align-items-center">
            <div class="col-lg-5">
                <span class="section-label">About Me</span>
                <h2 class="section-heading">Memadukan Visi Bisnis & Kreativitas Digital</h2>
                <p class="text-muted mb-4" style="line-height: 1.8;">
                    Sebagai pembelajar di bidang Bisnis Digital, saya berfokus pada titik temu antara <strong>strategi pasar terukur</strong>, <strong>pertumbuhan digital</strong>, dan <strong>desain pengalaman pengguna</strong> yang berorientasi solusi nyata bagi masyarakat dan pelaku usaha.
                </p>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="badge bg-soft-olive text-primary-olive py-2 px-3 fw-semibold">Business Planning</span>
                    <span class="badge bg-soft-olive text-primary-olive py-2 px-3 fw-semibold">Growth Marketing</span>
                    <span class="badge bg-soft-olive text-primary-olive py-2 px-3 fw-semibold">UI/UX Research</span>
                    <span class="badge bg-soft-olive text-primary-olive py-2 px-3 fw-semibold">Data Modeling</span>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="folio-card p-4 h-100">
                            <div class="stat-icon mb-3"><i class="bi bi-briefcase"></i></div>
                            <h4 class="h5 fw-bold mb-2">Market & Business Plan</h4>
                            <p class="small text-muted mb-0">Merumuskan proyeksi finansial, validasi unit economics, dan penyusunan strategi pitch deck teruji.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="folio-card p-4 h-100">
                            <div class="stat-icon mb-3"><i class="bi bi-megaphone"></i></div>
                            <h4 class="h5 fw-bold mb-2">Digital Marketing</h4>
                            <p class="small text-muted mb-0">Eksekusi kampanye Meta Ads terukur, optimasi funnel pemasaran, dan strategi konten brand storytelling.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="folio-card p-4 h-100">
                            <div class="stat-icon mb-3"><i class="bi bi-palette"></i></div>
                            <h4 class="h5 fw-bold mb-2">User Experience Design</h4>
                            <p class="small text-muted mb-0">Riset kebutuhan pengguna nyata, wireframing, dan interactive prototype dengan standar desain modern.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="folio-card p-4 h-100">
                            <div class="stat-icon mb-3"><i class="bi bi-bar-chart-line"></i></div>
                            <h4 class="h5 fw-bold mb-2">Analytics & Data</h4>
                            <p class="small text-muted mb-0">Menganalisis performa konversi website, data pelanggan, dan tren kompetitor berbasis fakta.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PROJECTS SHOWCASE SECTION (Section 4.4, FR-02, FR-03) -->
<section id="projects">
    <div class="container">
        <div class="text-center">
            <span class="section-label">Portfolio Works</span>
            <h2 class="section-heading">Featured Projects</h2>
            <p class="section-subheading">Kumpulan proyek terpilih dalam perancangan strategi bisnis, pemasaran digital, riset pasar, dan desain produk digital.</p>

            <!-- Dynamic Category Filter Buttons (No Page Reload) -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
                <button class="filter-btn active" data-filter="all">Semua Kategori</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="filter-btn" data-filter="<?= e($cat) ?>"><?= e($cat) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Project Grid -->
        <div class="row g-4" id="projectContainer">
            <?php if (empty($projects)): ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                    Belum ada proyek yang dipublikasikan.
                </div>
            <?php else: ?>
                <?php foreach ($projects as $pj): 
                    $thumbUrl = resolve_img($pj['thumbnail'], 'projects', 'assets/img/placeholder.png');
                    $catDisplay = $pj['display_category'] ?: ($pj['category'] ?: 'Lainnya');
                ?>
                    <div class="col-md-6 col-lg-4 project-item" data-category="<?= e($catDisplay) ?>">
                        <div class="project-card">
                            <div class="project-thumb-box">
                                <img src="<?= $thumbUrl ?>" alt="<?= e($pj['title']) ?>" loading="lazy">
                                <span class="project-cat-pill"><?= e($catDisplay) ?></span>
                                <?php if (!empty($pj['is_featured'])): ?>
                                    <span class="project-featured-pill">
                                        <i class="bi bi-star-fill me-1"></i> Featured
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="project-body">
                                <h3 class="h5 fw-bold text-dark mb-2"><?= e($pj['title']) ?></h3>
                                <p class="text-muted small mb-4 flex-grow-1" style="line-height: 1.6;">
                                    <?= e($pj['short_description']) ?>
                                </p>

                                <?php if (!empty($pj['tools'])): ?>
                                    <div class="mb-4">
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach (explode(',', $pj['tools']) as $t): ?>
                                                <span class="badge bg-light text-muted border border-secondary-subtle font-monospace" style="font-size: 0.72rem;"><?= e(trim($t)) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <button type="button" class="btn btn-outline-olive btn-sm w-100 justify-content-center"
                                    onclick="openProjectModal(<?= htmlspecialchars(json_encode($pj), ENT_QUOTES, 'UTF-8') ?>, '<?= $thumbUrl ?>')">
                                    <span>View Details</span>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- EXPERIENCE SECTION (Section 4.5, FR-04) -->
<section id="experience" class="bg-white">
    <div class="container">
        <div class="text-center">
            <span class="section-label">Track Record</span>
            <h2 class="section-heading">Pengalaman & Rekam Jejak</h2>
            <p class="section-subheading">Aktivitas magang, kepengurusan organisasi, kepanitiaan, dan pencapaian kompetisi.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <?php if (empty($experiences)): ?>
                    <p class="text-center text-muted py-4">Belum ada riwayat pengalaman.</p>
                <?php else: ?>
                    <div class="timeline-modern">
                        <?php foreach ($experiences as $exp): 
                            $periodStr = format_period($exp['period_start'], $exp['period_end'], $exp['is_current']);
                        ?>
                            <div class="timeline-node">
                                <div class="timeline-marker"></div>
                                <div class="folio-card p-4">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                        <span class="badge bg-soft-olive text-primary-olive px-2.5 py-1 rounded-pill small fw-semibold">
                                            <?= e($exp['type']) ?>
                                        </span>
                                        <span class="small text-muted fw-medium">
                                            <i class="bi bi-calendar3 me-1"></i><?= e($periodStr) ?>
                                        </span>
                                    </div>

                                    <h3 class="h5 fw-bold text-dark mb-1"><?= e($exp['title']) ?></h3>
                                    <div class="text-primary-olive fw-semibold small mb-3">
                                        <i class="bi bi-building me-1"></i><?= e($exp['organization']) ?>
                                    </div>

                                    <p class="text-muted small mb-3" style="line-height: 1.7;">
                                        <?= nl2br(e($exp['description'])) ?>
                                    </p>

                                    <?php if (!empty($exp['achievements'])): ?>
                                        <div class="p-3 bg-light rounded-3 border border-secondary-subtle">
                                            <div class="text-dark small fw-bold mb-1">
                                                <i class="bi bi-trophy text-warning me-1"></i> Pencapaian & Kontribusi:
                                            </div>
                                            <div class="text-muted small"><?= nl2br(e($exp['achievements'])) ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- EDUCATION SECTION (Section 4.6, FR-05) -->
<section id="education">
    <div class="container">
        <div class="text-center">
            <span class="section-label">Academic Background</span>
            <h2 class="section-heading">Riwayat Pendidikan</h2>
            <p class="section-subheading">Fondasi keilmuan dan latar belakang pendidikan formal yang ditempuh.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <?php if (empty($educations)): ?>
                    <p class="text-center text-muted py-4">Belum ada data pendidikan.</p>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($educations as $edu): 
                            $periodEdu = format_period($edu['period_start'], $edu['period_end'], $edu['is_current']);
                        ?>
                            <div class="col-12">
                                <div class="folio-card p-4 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                                    <div>
                                        <span class="badge bg-soft-olive text-primary-olive mb-2"><?= e($edu['degree']) ?></span>
                                        <h3 class="h5 fw-bold text-dark mb-1"><?= e($edu['major']) ?></h3>
                                        <div class="text-muted fw-medium mb-3"><?= e($edu['institution']) ?></div>
                                        <?php if (!empty($edu['description'])): ?>
                                            <p class="text-muted small mb-0" style="line-height: 1.7;">
                                                <?= nl2br(e($edu['description'])) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-nowrap small text-muted fw-semibold">
                                        <i class="bi bi-calendar-event me-1"></i><?= e($periodEdu) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- SKILLS SECTION (Section 4.7, FR-06) -->
<section id="skills" class="bg-white">
    <div class="container">
        <div class="text-center">
            <span class="section-label">Competencies</span>
            <h2 class="section-heading">Keahlian & Kemampuan</h2>
            <p class="section-subheading">Keterampilan teknis digital, pemahaman bisnis, pengolahan data, dan kemampuan komunikasi.</p>
        </div>

        <div class="row g-4">
            <?php if (empty($groupedSkills)): ?>
                <p class="text-center text-muted py-4">Belum ada skill yang ditambahkan.</p>
            <?php else: ?>
                <?php foreach ($groupedSkills as $catName => $skillList): ?>
                    <div class="col-lg-6">
                        <div class="folio-card p-4 h-100">
                            <h3 class="h5 fw-bold mb-4 text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-check2-circle text-primary-olive"></i>
                                <span><?= e($catName) ?></span>
                            </h3>

                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($skillList as $sk): 
                                    $prof = $sk['proficiency'] ?? 'Intermediate';
                                    $profClass = 'proficiency-intermediate';
                                    if (strcasecmp($prof, 'Advanced') === 0) $profClass = 'proficiency-advanced';
                                    elseif (strcasecmp($prof, 'Beginner') === 0) $profClass = 'proficiency-beginner';
                                ?>
                                    <div class="p-3 rounded-3 border border-secondary-subtle bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold text-dark"><?= e($sk['skill_name']) ?></div>
                                            <?php if (!empty($sk['description'])): ?>
                                                <div class="text-muted small"><?= e($sk['description']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($prof)): ?>
                                            <span class="proficiency-badge <?= $profClass ?>"><?= e($prof) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CONTACT / CTA SECTION (Section 4.8, FR-07) -->
<section id="contact">
    <div class="container">
        <div class="text-center">
            <span class="section-label">Get in Touch</span>
            <h2 class="section-heading">Have an Idea? Let's Make It Happen.</h2>
            <p class="section-subheading">Terbuka untuk kesempatan magang, proyek kolaboratif bisnis digital, maupun diskusi inovasi produk.</p>
        </div>

        <div class="row g-5 justify-content-center">
            <!-- Contact Details -->
            <div class="col-lg-5">
                <div class="folio-card p-4 p-md-5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h3 class="h4 fw-bold text-dark mb-3">Mari Berkolaborasi!</h3>
                        <p class="text-muted small mb-4" style="line-height: 1.8;">
                            Jangan ragu untuk menyapa atau mendiskusikan peluang kemitraan. Saya akan membalas pesan Anda sesegera mungkin.
                        </p>

                        <div class="d-flex flex-column gap-3 mb-4">
                            <?php if (!empty($profile['email'])): ?>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon"><i class="bi bi-envelope"></i></div>
                                    <div>
                                        <div class="text-muted small">Email</div>
                                        <a href="mailto:<?= e($profile['email']) ?>" class="text-dark fw-semibold text-decoration-none small">
                                            <?= e($profile['email']) ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($profile['phone'])): ?>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon"><i class="bi bi-whatsapp"></i></div>
                                    <div>
                                        <div class="text-muted small">WhatsApp / Telepon</div>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $profile['phone']) ?>" target="_blank" class="text-dark fw-semibold text-decoration-none small">
                                            <?= e($profile['phone']) ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($profile['location'])): ?>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon"><i class="bi bi-geo-alt"></i></div>
                                    <div>
                                        <div class="text-muted small">Lokasi</div>
                                        <span class="text-dark fw-semibold small"><?= e($profile['location']) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border border-secondary-subtle">
                        <span class="small text-muted d-flex align-items-center gap-2">
                            <i class="bi bi-clock-history text-primary-olive"></i>
                            <span>Respon pesan rata-rata dalam 1x24 jam kerja.</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="folio-card p-4 p-md-5">
                    <div id="contactFeedback">
                        <?php if ($flash): ?>
                            <div class="alert alert-<?= e($flash['type']) ?> d-flex align-items-center mb-4 fade show" role="alert">
                                <i class="bi bi-info-circle me-2 fs-5"></i>
                                <div><?= e($flash['message']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form id="contactForm" method="POST" action="index.php">
                        <input type="hidden" name="action" value="send_message">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Nama Lengkap *</label>
                                <input type="text" name="sender_name" class="form-control form-folio" placeholder="Nama Anda" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Alamat Email *</label>
                                <input type="email" name="sender_email" class="form-control form-folio" placeholder="email@domain.com" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label text-muted small fw-semibold">Subjek Pesan (Opsional)</label>
                                <input type="text" name="subject" class="form-control form-folio" placeholder="cth: Peluang Kerjasama / Tawaran Magang">
                            </div>

                            <div class="col-12">
                                <label class="form-label text-muted small fw-semibold">Isi Pesan *</label>
                                <textarea name="message" rows="5" class="form-control form-folio" placeholder="Tuliskan pesan Anda secara lengkap..." required></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary-olive w-100 py-3 justify-content-center">
                                    <i class="bi bi-send-fill"></i>
                                    <span>Kirim Pesan</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MODAL DETAIL PROYEK (Section 4.4, TC-04) -->
<div class="modal fade" id="projectDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom p-4">
                <div>
                    <span class="badge bg-soft-olive text-primary-olive mb-1" id="mProjCategory"></span>
                    <h4 class="modal-title font-heading fw-bold text-dark" id="mProjTitle"></h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="rounded-3 overflow-hidden mb-4" style="max-height: 320px; background-color: #eee;">
                    <img id="mProjThumb" src="" alt="" class="w-100 h-100 object-fit-cover">
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="small text-muted fw-bold text-uppercase mb-1">Peran / Tanggung Jawab</div>
                        <div class="text-dark fw-medium" id="mProjRole">—</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted fw-bold text-uppercase mb-1">Tools / Teknologi</div>
                        <div class="text-dark fw-medium" id="mProjTools">—</div>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold text-dark fs-6 mb-2">Deskripsi Proyek</h5>
                    <p class="text-muted small" id="mProjDesc" style="line-height: 1.8;"></p>
                </div>

                <div class="mb-4" id="mProjProcessWrap">
                    <h5 class="fw-bold text-dark fs-6 mb-2">Proses Pengerjaan</h5>
                    <p class="text-muted small" id="mProjProcess" style="line-height: 1.8;"></p>
                </div>

                <div class="mb-4 p-3 bg-light rounded-3 border" id="mProjResultWrap">
                    <h5 class="fw-bold text-dark fs-6 mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> Hasil & Dampak</h5>
                    <p class="text-muted small mb-0" id="mProjResult" style="line-height: 1.7;"></p>
                </div>

                <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                    <a href="#" id="mProjUrl" target="_blank" class="btn btn-primary-olive btn-sm d-none">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Tautan Proyek / Demo
                    </a>
                    <a href="#" id="mProjRepo" target="_blank" class="btn btn-outline-olive btn-sm d-none">
                        <i class="bi bi-github me-1"></i> Repositori
                    </a>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function openProjectModal(project, thumbUrl) {
    document.getElementById('mProjTitle').innerText = project.title;
    document.getElementById('mProjCategory').innerText = project.display_category || project.category;
    document.getElementById('mProjThumb').src = thumbUrl;
    document.getElementById('mProjRole').innerText = project.role || '—';
    document.getElementById('mProjTools').innerText = project.tools || '—';
    document.getElementById('mProjDesc').innerText = project.description || project.short_description;

    const pWrap = document.getElementById('mProjProcessWrap');
    if (project.process) {
        document.getElementById('mProjProcess').innerText = project.process;
        pWrap.classList.remove('d-none');
    } else {
        pWrap.classList.add('d-none');
    }

    const rWrap = document.getElementById('mProjResultWrap');
    if (project.result) {
        document.getElementById('mProjResult').innerText = project.result;
        rWrap.classList.remove('d-none');
    } else {
        rWrap.classList.add('d-none');
    }

    const urlBtn = document.getElementById('mProjUrl');
    if (project.project_url && project.project_url !== '#') {
        urlBtn.href = project.project_url;
        urlBtn.classList.remove('d-none');
    } else {
        urlBtn.classList.add('d-none');
    }

    const repoBtn = document.getElementById('mProjRepo');
    if (project.repository_url && project.repository_url !== '#') {
        repoBtn.href = project.repository_url;
        repoBtn.classList.remove('d-none');
    } else {
        repoBtn.classList.add('d-none');
    }

    new bootstrap.Modal(document.getElementById('projectDetailModal')).show();
}
</script>

<?php
// Include Footer
require_once __DIR__ . '/includes/footer.php';
?>
