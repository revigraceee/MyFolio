<?php
// admin/projects.php
$pageTitle    = 'Manage Projects';
$pageSubtitle = 'Kelola portofolio karya, detail studi kasus, status draft/published, dan unggulan';
$currentPage  = 'projects';

require_once __DIR__ . '/includes/admin_init.php';

// ══════════════════════════════════════════════════════════
//  POST HANDLERS — semua sebelum HTML output
// ══════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: projects.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ── TAMBAH PROYEK ────────────────────────────────────────
    if ($action === 'add') {
        $title       = trim($_POST['title'] ?? '');
        $slug        = trim($_POST['slug'] ?? '') ?: generate_slug($title);
        $categoryId  = (int)($_POST['category_id'] ?? 0) ?: null;   // FK ke project_categories
        $shortDesc   = trim($_POST['short_description'] ?? '');
        $desc        = trim($_POST['description'] ?? '');
        $role        = trim($_POST['role'] ?? '');
        $process     = trim($_POST['process'] ?? '');
        $result      = trim($_POST['result'] ?? '');
        $tools       = trim($_POST['tools'] ?? '');
        $projUrl     = trim($_POST['project_url'] ?? '');
        $repoUrl     = trim($_POST['repository_url'] ?? '');
        $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;
        $status      = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);
        $thumbnail   = 'placeholder.png';

        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
            $up = handle_file_upload($_FILES['thumbnail_file'], 'projects');
            if ($up) $thumbnail = $up;
        }

        // Dapatkan nama kategori untuk kolom category (backward-compat)
        $catName = '';
        if ($categoryId) {
            $catRow = $pdo->prepare("SELECT category_name FROM project_categories WHERE id = ?");
            $catRow->execute([$categoryId]);
            $catName = $catRow->fetchColumn() ?: '';
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO projects
                    (title, slug, category_id, category, short_description, description,
                     role, process, result, tools, thumbnail, project_url, repository_url,
                     is_featured, status, sort_order, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $title, $slug, $categoryId, $catName,
                $shortDesc, $desc, $role, $process, $result, $tools,
                $thumbnail, $projUrl, $repoUrl,
                $isFeatured, $status, $sortOrder
            ]);
            $newProjId = (int)$pdo->lastInsertId();

            // Handle image_2 (Slide 2)
            if ($newProjId > 0 && isset($_FILES['image_2']) && $_FILES['image_2']['error'] === UPLOAD_ERR_OK) {
                $img2 = handle_file_upload($_FILES['image_2'], 'projects');
                if ($img2) {
                    $pdo->prepare("INSERT INTO project_images (project_id, image_path, sort_order) VALUES (?, ?, 1)")
                        ->execute([$newProjId, $img2]);
                }
            }

            // Handle optional multiple gallery photos
            if ($newProjId > 0 && !empty($_FILES['gallery_files'])) {
                $galleryUploads = handle_multiple_file_uploads($_FILES['gallery_files'], 'projects');
                if (!empty($galleryUploads)) {
                    $insGal = $pdo->prepare("INSERT INTO project_images (project_id, image_path, sort_order) VALUES (?, ?, ?)");
                    foreach ($galleryUploads as $idx => $gImg) {
                        $insGal->execute([$newProjId, $gImg, $idx + 2]);
                    }
                }
            }

            set_flash('success', 'Proyek baru berhasil ditambahkan!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal menambah proyek: ' . $e->getMessage());
        }

        header('Location: projects.php');
        exit;
    }

    // ── EDIT PROYEK ──────────────────────────────────────────
    if ($action === 'edit') {
        $id          = (int)($_POST['project_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $slug        = trim($_POST['slug'] ?? '') ?: generate_slug($title);
        $categoryId  = (int)($_POST['category_id'] ?? 0) ?: null;
        $shortDesc   = trim($_POST['short_description'] ?? '');
        $desc        = trim($_POST['description'] ?? '');
        $role        = trim($_POST['role'] ?? '');
        $process     = trim($_POST['process'] ?? '');
        $result      = trim($_POST['result'] ?? '');
        $tools       = trim($_POST['tools'] ?? '');
        $projUrl     = trim($_POST['project_url'] ?? '');
        $repoUrl     = trim($_POST['repository_url'] ?? '');
        $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;
        $status      = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);
        $thumbnail   = $_POST['current_thumbnail'] ?? 'placeholder.png';

        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
            $up = handle_file_upload($_FILES['thumbnail_file'], 'projects');
            if ($up) $thumbnail = $up;
        }

        // Sync kolom category (teks) agar backward-compat
        $catName = '';
        if ($categoryId) {
            $catRow = $pdo->prepare("SELECT category_name FROM project_categories WHERE id = ?");
            $catRow->execute([$categoryId]);
            $catName = $catRow->fetchColumn() ?: '';
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE projects
                SET title = ?, slug = ?, category_id = ?, category = ?,
                    short_description = ?, description = ?,
                    role = ?, process = ?, result = ?, tools = ?,
                    thumbnail = ?, project_url = ?, repository_url = ?,
                    is_featured = ?, status = ?, sort_order = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $title, $slug, $categoryId, $catName,
                $shortDesc, $desc, $role, $process, $result, $tools,
                $thumbnail, $projUrl, $repoUrl,
                $isFeatured, $status, $sortOrder, $id
            ]);

            // Handle image_2 (Slide 2)
            if ($id > 0 && isset($_FILES['image_2']) && $_FILES['image_2']['error'] === UPLOAD_ERR_OK) {
                $img2 = handle_file_upload($_FILES['image_2'], 'projects');
                if ($img2) {
                    $img2Id = (int)($_POST['image_2_id'] ?? 0);
                    if ($img2Id > 0) {
                        $oldImg = $pdo->query("SELECT image_path FROM project_images WHERE id = $img2Id")->fetchColumn();
                        if ($oldImg && file_exists(__DIR__ . '/../uploads/projects/' . $oldImg)) {
                            @unlink(__DIR__ . '/../uploads/projects/' . $oldImg);
                        }
                        $pdo->prepare("UPDATE project_images SET image_path = ? WHERE id = ?")->execute([$img2, $img2Id]);
                    } else {
                        $pdo->prepare("INSERT INTO project_images (project_id, image_path, sort_order) VALUES (?, ?, 1)")
                            ->execute([$id, $img2]);
                    }
                }
            }

            // Handle additional gallery photos upload
            if ($id > 0 && !empty($_FILES['gallery_files'])) {
                $galleryUploads = handle_multiple_file_uploads($_FILES['gallery_files'], 'projects');
                if (!empty($galleryUploads)) {
                    $maxOrder = (int)$pdo->query("SELECT COALESCE(MAX(sort_order), 0) FROM project_images WHERE project_id = $id")->fetchColumn();
                    $insGal = $pdo->prepare("INSERT INTO project_images (project_id, image_path, sort_order) VALUES (?, ?, ?)");
                    foreach ($galleryUploads as $idx => $gImg) {
                        $insGal->execute([$id, $gImg, $maxOrder + $idx + 1]);
                    }
                }
            }

            set_flash('success', 'Data proyek berhasil diperbarui!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal memperbarui proyek: ' . $e->getMessage());
        }

        header('Location: projects.php');
        exit;
    }

    // ── HAPUS FOTO GALERI (AJAX & POST) ───────────────────────
    if ($action === 'delete_gallery_image') {
        $imgId  = (int)($_POST['image_id'] ?? 0);
        $projId = (int)($_POST['project_id'] ?? 0);
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($imgId > 0) {
            $q = $pdo->prepare("SELECT image_path FROM project_images WHERE id = ?");
            $q->execute([$imgId]);
            $path = $q->fetchColumn();
            if ($path) {
                $fileOnDisk = __DIR__ . '/../uploads/projects/' . $path;
                if (file_exists($fileOnDisk)) {
                    @unlink($fileOnDisk);
                }
                $pdo->prepare("DELETE FROM project_images WHERE id = ?")->execute([$imgId]);
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Foto galeri berhasil dihapus']);
                exit;
            }
            set_flash('success', 'Foto galeri berhasil dihapus.');
        }
        header('Location: projects.php');
        exit;
    }

    // ── HAPUS PROYEK ─────────────────────────────────────────
    if ($action === 'delete') {
        $id = (int)($_POST['project_id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
            set_flash('success', 'Proyek berhasil dihapus.');
        }
        header('Location: projects.php');
        exit;
    }
}

// ── Load semua proyek + nama kategori (JOIN) ──────────────
$projects = $pdo->query("
    SELECT p.*, pc.category_name AS cat_label, pc.slug AS cat_slug
    FROM projects p
    LEFT JOIN project_categories pc ON p.category_id = pc.id
    ORDER BY p.sort_order ASC, p.created_at DESC
")->fetchAll();

// ── Load semua gallery images indexed by project_id ───────
$galleryImages = $pdo->query("SELECT * FROM project_images ORDER BY sort_order ASC, id ASC")->fetchAll();
$projectGalleries = [];
foreach ($galleryImages as $img) {
    $projectGalleries[$img['project_id']][] = [
        'id'         => (int)$img['id'],
        'image_path' => $img['image_path'],
        'url'        => resolve_img($img['image_path'], 'projects', '../assets/img/placeholder.png', '../')
    ];
}

// Attach gallery ke setiap proyek
foreach ($projects as &$pj) {
    $pj['gallery'] = $projectGalleries[$pj['id']] ?? [];
}
unset($pj);

// ── Load semua kategori untuk dropdown ───────────────────
$categories = $pdo->query("
    SELECT id, category_name FROM project_categories ORDER BY sort_order ASC, category_name ASC
")->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- ══ PAGE ACTIONS ══ -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge rounded-pill px-3 py-2"
              style="background:var(--color-soft-olive);color:var(--color-dark);font-size:.8rem;">
            <i class="bi bi-grid-1x2 me-1"></i> <?= count($projects) ?> Proyek
        </span>
    </div>
    <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Proyek Baru
    </button>
</div>

<!-- ══ TABLE ══ -->
<div class="admin-card">
    <?php if (empty($projects)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-grid-1x2 fs-1 d-block mb-3 opacity-25"></i>
            Belum ada proyek yang ditambahkan.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th style="width:80px;">Thumbnail</th>
                        <th>Judul & Kategori</th>
                        <th>Peran & Tools</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $pj):
                        $thumbPath = resolve_img($pj['thumbnail'], 'projects', '../assets/img/placeholder.png', '../');
                        // Label kategori: ambil dari JOIN, fallback ke kolom teks lama
                        $catLabel  = $pj['cat_label'] ?: ($pj['category'] ?: '—');
                    ?>
                        <tr>
                            <td>
                                <img src="<?= e($thumbPath) ?>" alt=""
                                     class="rounded-2" style="width:70px;height:48px;object-fit:cover;">
                            </td>
                            <td>
                                <div class="fw-bold text-dark d-flex align-items-center flex-wrap gap-1">
                                    <span><?= e($pj['title']) ?></span>
                                    <?php if (!empty($pj['gallery'])): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.68rem;" title="<?= count($pj['gallery']) ?> foto galeri">
                                            <i class="bi bi-images me-1"></i>+<?= count($pj['gallery']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-light text-muted border" style="font-size:.72rem;">
                                    <i class="bi bi-tag me-1"></i><?= e($catLabel) ?>
                                </span>
                            </td>
                            <td>
                                <div class="small text-dark fw-medium"><?= e($pj['role'] ?: '—') ?></div>
                                <div class="small text-muted text-truncate" style="max-width:220px;">
                                    <?= e($pj['tools'] ?: '—') ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($pj['status'] === 'published'): ?>
                                    <span class="badge-published"><i class="bi bi-check-circle me-1"></i>Published</span>
                                <?php else: ?>
                                    <span class="badge-draft"><i class="bi bi-hourglass me-1"></i>Draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($pj['is_featured']): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis fw-bold">
                                        <i class="bi bi-star-fill me-1"></i>Yes
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm py-1 px-2 me-1"
                                        onclick="openEditProject(<?= htmlspecialchars(json_encode($pj), ENT_QUOTES, 'UTF-8') ?>)"
                                        title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="projects.php" class="d-inline"
                                      onsubmit="return confirm('Hapus proyek ini secara permanen?');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="project_id" value="<?= $pj['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL TAMBAH PROYEK
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <h5 class="modal-title fw-bold font-heading text-dark">Tambah Proyek Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="projects.php" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="row g-3">
                        <!-- Judul -->
                        <div class="col-md-8">
                            <label class="form-label text-muted small fw-semibold">Judul Proyek *</label>
                            <input type="text" name="title" class="form-control form-folio" required>
                        </div>

                        <!-- Kategori DINAMIS dari DB -->
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">
                                Kategori *
                                <a href="categories.php" target="_blank"
                                   class="ms-1 text-decoration-none" style="font-size:.75rem;"
                                   title="Kelola kategori">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </label>
                            <select name="category_id" class="form-select form-folio" required>
                                <option value="">— Pilih Kategori —</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($categories)): ?>
                                <div class="form-text text-danger">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    Belum ada kategori. <a href="categories.php">Tambah kategori dulu</a>.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Short Description -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Ringkasan Singkat</label>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" onclick="clearField('addShortDesc')">
                                    <i class="bi bi-x-circle me-1"></i>Hapus Isi
                                </button>
                            </div>
                            <textarea name="short_description" id="addShortDesc" rows="2" class="form-control form-folio" placeholder="Ringkasan singkat proyek..."></textarea>
                        </div>
                        <!-- Deskripsi Lengkap -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Deskripsi Lengkap / Latar Belakang</label>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" onclick="clearField('addDesc')">
                                    <i class="bi bi-x-circle me-1"></i>Hapus Isi
                                </button>
                            </div>
                            <textarea name="description" id="addDesc" rows="3" class="form-control form-folio" placeholder="Deskripsi lengkap atau latar belakang masalah..."></textarea>
                        </div>
                        <!-- Peran & Tools -->
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Peran / Tanggung Jawab</label>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" onclick="clearField('addRole')">
                                    <i class="bi bi-x-circle me-1"></i>Hapus Isi
                                </button>
                            </div>
                            <input type="text" name="role" id="addRole" class="form-control form-folio" placeholder="cth: Lead Business Strategist">
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Tools yang Digunakan</label>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" onclick="clearField('addTools')">
                                    <i class="bi bi-x-circle me-1"></i>Hapus Isi
                                </button>
                            </div>
                            <input type="text" name="tools" id="addTools" class="form-control form-folio" placeholder="Figma, Meta Ads, Excel">
                        </div>
                        <!-- Proses & Hasil -->
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Proses Pengerjaan</label>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" onclick="clearField('addProcess')">
                                    <i class="bi bi-x-circle me-1"></i>Hapus Isi
                                </button>
                            </div>
                            <textarea name="process" id="addProcess" rows="2" class="form-control form-folio" placeholder="Proses atau metodologi pengerjaan..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Hasil & Dampak</label>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" onclick="clearField('addResult')">
                                    <i class="bi bi-x-circle me-1"></i>Hapus Isi
                                </button>
                            </div>
                            <textarea name="result" id="addResult" rows="2" class="form-control form-folio" placeholder="Hasil, dampak, atau pencapaian..."></textarea>
                        </div>
                        <!-- URL -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tautan Proyek / Demo</label>
                            <input type="text" name="project_url" class="form-control form-folio" placeholder="https://...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tautan Repositori / File</label>
                            <input type="text" name="repository_url" class="form-control form-folio" placeholder="https://github.com/...">
                        </div>
                        <!-- 2 Foto Proyek (Slide Gambar) -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <label class="form-label text-dark fw-bold mb-1">
                                    <i class="bi bi-images text-primary-olive me-1"></i> Foto Proyek (Slide Gambar)
                                </label>
                                <p class="text-muted small mb-3" style="font-size: 0.78rem;">
                                    Tambahkan hingga 2 foto. Jika mengunggah 2 foto, kartu proyek dan modal pratinjau dapat di-slide ke kiri dan kanan.
                                </p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small fw-semibold">
                                            <span class="badge bg-primary-olive text-white me-1">1</span> Gambar 1 (Foto Utama / Slide 1) *
                                        </label>
                                        <input type="file" name="thumbnail_file" class="form-control form-folio" accept="image/*" onchange="previewImgLocal(this, 'addImg1Preview', 'addImg1Wrap')">
                                        <div id="addImg1Wrap" class="mt-2 text-center d-none p-1 bg-white rounded border">
                                            <img id="addImg1Preview" src="" class="rounded" style="max-height: 80px; max-width: 100%; object-fit: contain;">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small fw-semibold">
                                            <span class="badge bg-secondary text-white me-1">2</span> Gambar 2 (Slide 2 / Tambahan)
                                        </label>
                                        <input type="file" name="image_2" class="form-control form-folio" accept="image/*" onchange="previewImgLocal(this, 'addImg2Preview', 'addImg2Wrap')">
                                        <div id="addImg2Wrap" class="mt-2 text-center d-none p-1 bg-white rounded border">
                                            <img id="addImg2Preview" src="" class="rounded" style="max-height: 80px; max-width: 100%; object-fit: contain;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Status & Urutan -->
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">Status *</label>
                            <select name="status" class="form-select form-folio">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">Urutan (Sort Order)</label>
                            <input type="number" name="sort_order" class="form-control form-folio" value="0">
                        </div>
                        <!-- Featured -->
                        <div class="col-12">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="addFeatured" value="1">
                                <label class="form-check-label text-dark fw-medium" for="addFeatured">
                                    Tandai sebagai Proyek Unggulan (Featured)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Simpan Proyek</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL EDIT PROYEK
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <h5 class="modal-title fw-bold font-heading text-dark">Edit Proyek</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="projects.php" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="project_id" id="editProjId">
                    <input type="hidden" name="current_thumbnail" id="editCurrentThumb">

                    <div class="row g-3">
                        <!-- Judul -->
                        <div class="col-md-8">
                            <label class="form-label text-muted small fw-semibold">Judul Proyek *</label>
                            <input type="text" name="title" id="editTitle" class="form-control form-folio" required>
                        </div>

                        <!-- Kategori DINAMIS dari DB -->
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">
                                Kategori *
                                <a href="categories.php" target="_blank"
                                   class="ms-1 text-decoration-none" style="font-size:.75rem;"
                                   title="Kelola kategori">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </label>
                            <select name="category_id" id="editCategoryId" class="form-select form-folio" required>
                                <option value="">— Pilih Kategori —</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Short Description -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Ringkasan Singkat</label>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" onclick="clearField('editShortDesc')">
                                    <i class="bi bi-x-circle me-1"></i>Hapus Isi
                                </button>
                            </div>
                            <textarea name="short_description" id="editShortDesc" rows="2" class="form-control form-folio" placeholder="Ringkasan singkat proyek..."></textarea>
                        </div>
                        <!-- Deskripsi Lengkap -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Deskripsi Lengkap</label>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" onclick="clearField('editDesc')">
                                    <i class="bi bi-x-circle me-1"></i>Hapus Isi
                                </button>
                            </div>
                            <textarea name="description" id="editDesc" rows="3" class="form-control form-folio" placeholder="Deskripsi lengkap atau latar belakang masalah..."></textarea>
                        </div>
                        <!-- Peran & Tools -->
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Peran / Tanggung Jawab</label>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" onclick="clearField('editRole')">
                                    <i class="bi bi-x-circle me-1"></i>Hapus Isi
                                </button>
                            </div>
                            <input type="text" name="role" id="editRole" class="form-control form-folio" placeholder="cth: Lead Business Strategist">
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Tools yang Digunakan</label>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" onclick="clearField('editTools')">
                                    <i class="bi bi-x-circle me-1"></i>Hapus Isi
                                </button>
                            </div>
                            <input type="text" name="tools" id="editTools" class="form-control form-folio" placeholder="Figma, Meta Ads, Excel">
                        </div>
                        <!-- Proses & Hasil -->
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Proses Pengerjaan</label>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" onclick="clearField('editProcess')">
                                    <i class="bi bi-x-circle me-1"></i>Hapus Isi
                                </button>
                            </div>
                            <textarea name="process" id="editProcess" rows="2" class="form-control form-folio" placeholder="Proses atau metodologi pengerjaan..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Hasil & Dampak</label>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" onclick="clearField('editResult')">
                                    <i class="bi bi-x-circle me-1"></i>Hapus Isi
                                </button>
                            </div>
                            <textarea name="result" id="editResult" rows="2" class="form-control form-folio" placeholder="Hasil, dampak, atau pencapaian..."></textarea>
                        </div>
                        <!-- URL -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tautan Proyek / Demo</label>
                            <input type="text" name="project_url" id="editProjUrl" class="form-control form-folio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tautan Repositori</label>
                            <input type="text" name="repository_url" id="editRepoUrl" class="form-control form-folio">
                        </div>
                        <!-- 2 Foto Proyek (Slide Gambar) -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <label class="form-label text-dark fw-bold mb-1">
                                    <i class="bi bi-images text-primary-olive me-1"></i> Foto Proyek (Slide Gambar)
                                </label>
                                <p class="text-muted small mb-3" style="font-size: 0.78rem;">
                                    Atur Gambar 1 dan Gambar 2 untuk proyek ini. Jika memiliki 2 gambar, foto dapat di-slide ke kiri dan kanan pada kartu proyek dan detail pratinjau.
                                </p>
                                <div class="row g-3">
                                    <!-- Slot Gambar 1 (Slide 1 / Utama) -->
                                    <div class="col-md-6">
                                        <div class="p-2.5 bg-white rounded-2 border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label text-muted small fw-semibold mb-0">
                                                    <span class="badge bg-primary-olive text-white me-1">1</span> Gambar 1 (Slide 1 / Utama)
                                                </label>
                                            </div>
                                            <!-- Preview Slot 1 -->
                                            <div class="mb-2 text-center p-1 bg-light rounded border" style="min-height: 90px; display: flex; align-items: center; justify-content: center;">
                                                <img id="editImg1Preview" src="" class="rounded" style="max-height: 85px; max-width: 100%; object-fit: contain;">
                                            </div>
                                            <label class="form-label text-muted small mb-1" style="font-size:0.75rem;">Ganti Gambar 1:</label>
                                            <input type="file" name="thumbnail_file" id="editImg1File" class="form-control form-folio form-control-sm" accept="image/*" onchange="previewImgLocal(this, 'editImg1Preview')">
                                            <div class="form-text text-muted" id="editThumbInfo" style="font-size:0.7rem;"></div>
                                        </div>
                                    </div>

                                    <!-- Slot Gambar 2 (Slide 2) -->
                                    <div class="col-md-6">
                                        <div class="p-2.5 bg-white rounded-2 border h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label text-muted small fw-semibold mb-0">
                                                    <span class="badge bg-secondary text-white me-1">2</span> Gambar 2 (Slide 2)
                                                </label>
                                                <!-- Delete button for Image 2 -->
                                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 d-none" id="editImg2DeleteBtn" style="font-size:0.72rem;" onclick="deleteImage2()">
                                                    <i class="bi bi-trash-fill me-1"></i>Hapus Gambar 2
                                                </button>
                                            </div>
                                            <!-- Preview Slot 2 -->
                                            <div class="mb-2 text-center p-1 bg-light rounded border position-relative" style="min-height: 90px; display: flex; align-items: center; justify-content: center;">
                                                <img id="editImg2Preview" src="" class="rounded d-none" style="max-height: 85px; max-width: 100%; object-fit: contain;">
                                                <span id="editImg2EmptyNotice" class="text-muted small fst-italic" style="font-size:0.75rem;">Belum ada Gambar 2</span>
                                            </div>
                                            <label class="form-label text-muted small mb-1" style="font-size:0.75rem;">Unggah / Ganti Gambar 2:</label>
                                            <input type="file" name="image_2" id="editImg2File" class="form-control form-folio form-control-sm" accept="image/*" onchange="previewImgLocal(this, 'editImg2Preview', null, 'editImg2EmptyNotice')">
                                            <input type="hidden" name="image_2_id" id="editImg2Id" value="">
                                            <div class="form-text text-muted" style="font-size:0.7rem;">Pilih foto baru untuk mengisi atau mengganti Gambar 2.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Status & Urutan -->
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">Status *</label>
                            <select name="status" id="editStatus" class="form-select form-folio">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">Urutan</label>
                            <input type="number" name="sort_order" id="editSortOrder" class="form-control form-folio">
                        </div>
                        <!-- Featured -->
                        <div class="col-12">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="editFeatured" value="1">
                                <label class="form-check-label text-dark fw-medium" for="editFeatured">
                                    Tandai sebagai Proyek Unggulan (Featured)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Perbarui Proyek</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══ Scripts ══ -->
<script>
let currentEditingProject = null;

function clearField(fieldId) {
    const el = document.getElementById(fieldId);
    if (el) {
        el.value = '';
        el.focus();
    }
}

function previewImgLocal(input, previewImgId, wrapId, hideNoticeId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(previewImgId);
            if (img) {
                img.src = e.target.result;
                img.classList.remove('d-none');
            }
            if (wrapId) {
                const wrap = document.getElementById(wrapId);
                if (wrap) wrap.classList.remove('d-none');
            }
            if (hideNoticeId) {
                const notice = document.getElementById(hideNoticeId);
                if (notice) notice.classList.add('d-none');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function deleteImage2() {
    const imgId = document.getElementById('editImg2Id').value;
    const projId = document.getElementById('editProjId').value;
    if (!imgId) return;
    if (!confirm('Hapus Gambar 2 dari proyek ini?')) return;

    const formData = new FormData();
    formData.append('csrf_token', '<?= $csrf_token ?>');
    formData.append('action', 'delete_gallery_image');
    formData.append('image_id', imgId);
    formData.append('project_id', projId);

    fetch('projects.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('editImg2Preview').src = '';
            document.getElementById('editImg2Preview').classList.add('d-none');
            document.getElementById('editImg2EmptyNotice').classList.remove('d-none');
            document.getElementById('editImg2DeleteBtn').classList.add('d-none');
            document.getElementById('editImg2Id').value = '';
            document.getElementById('editImg2File').value = '';
            if (currentEditingProject && currentEditingProject.gallery) {
                currentEditingProject.gallery = currentEditingProject.gallery.filter(g => g.id != imgId);
            }
        } else {
            alert(data.message || 'Gagal menghapus gambar.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat menghapus gambar.');
    });
}

function renderEditGallery(gallery, projectId) {
    // Gallery helper if needed
}

function deleteGalleryImage(imgId, projId) {
    if (!confirm('Hapus foto ini dari galeri proyek?')) return;

    const formData = new FormData();
    formData.append('csrf_token', '<?= $csrf_token ?>');
    formData.append('action', 'delete_gallery_image');
    formData.append('image_id', imgId);
    formData.append('project_id', projId);

    fetch('projects.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const el = document.getElementById('gallery-item-' + imgId);
            if (el) el.remove();
            if (currentEditingProject && currentEditingProject.gallery) {
                currentEditingProject.gallery = currentEditingProject.gallery.filter(g => g.id !== imgId);
            }
        } else {
            alert(data.message || 'Gagal menghapus foto.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat menghapus foto.');
    });
}

function openEditProject(p) {
    currentEditingProject = p;
    document.getElementById('editProjId').value         = p.id;
    document.getElementById('editTitle').value          = p.title || '';
    document.getElementById('editShortDesc').value      = p.short_description || '';
    document.getElementById('editDesc').value           = p.description || '';
    document.getElementById('editRole').value           = p.role || '';
    document.getElementById('editTools').value          = p.tools || '';
    document.getElementById('editProcess').value        = p.process || '';
    document.getElementById('editResult').value         = p.result || '';
    document.getElementById('editProjUrl').value        = p.project_url || '';
    document.getElementById('editRepoUrl').value        = p.repository_url || '';
    document.getElementById('editCurrentThumb').value   = p.thumbnail || 'placeholder.png';
    document.getElementById('editStatus').value         = p.status || 'published';
    document.getElementById('editSortOrder').value      = p.sort_order || 0;
    document.getElementById('editFeatured').checked     = (parseInt(p.is_featured) === 1);

    // Set info thumbnail lama
    const thumbInfo = document.getElementById('editThumbInfo');
    if (p.thumbnail && p.thumbnail !== 'placeholder.png') {
        thumbInfo.textContent = 'File saat ini: ' + p.thumbnail;
    } else {
        thumbInfo.textContent = '';
    }

    // Set Preview Gambar 1 (Slide 1 / Sampul)
    const img1Prev = document.getElementById('editImg1Preview');
    const thumbPath = p.thumbnail && p.thumbnail !== 'placeholder.png' ? '../uploads/projects/' + p.thumbnail : '../assets/img/placeholder.png';
    img1Prev.src = thumbPath;

    // Reset input file Gambar 1 & Gambar 2
    document.getElementById('editImg1File').value = '';
    document.getElementById('editImg2File').value = '';

    // Set Preview Gambar 2 (Slide 2)
    const img2Prev = document.getElementById('editImg2Preview');
    const img2Notice = document.getElementById('editImg2EmptyNotice');
    const img2Del = document.getElementById('editImg2DeleteBtn');
    const img2IdInput = document.getElementById('editImg2Id');

    if (p.gallery && p.gallery.length > 0) {
        const firstGal = p.gallery[0];
        img2Prev.src = firstGal.url;
        img2Prev.classList.remove('d-none');
        img2Notice.classList.add('d-none');
        img2Del.classList.remove('d-none');
        img2IdInput.value = firstGal.id;
    } else {
        img2Prev.src = '';
        img2Prev.classList.add('d-none');
        img2Notice.classList.remove('d-none');
        img2Del.classList.add('d-none');
        img2IdInput.value = '';
    }

    // Set dropdown kategori berdasarkan category_id (FK)
    const sel = document.getElementById('editCategoryId');
    const catId = p.category_id ? p.category_id.toString() : '';
    // Coba set by category_id
    let matched = false;
    for (let opt of sel.options) {
        if (opt.value === catId) {
            sel.value = catId;
            matched = true;
            break;
        }
    }
    // Fallback: cari by teks (untuk data lama yang belum punya category_id)
    if (!matched && p.category) {
        for (let opt of sel.options) {
            if (opt.text.trim().toLowerCase() === p.category.trim().toLowerCase()) {
                sel.value = opt.value;
                break;
            }
        }
    }

    new bootstrap.Modal(document.getElementById('editProjectModal')).show();
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
