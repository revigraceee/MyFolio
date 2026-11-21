<?php
// admin/experiences.php — Manage Experience + Kelola Tipe Pengalaman
$currentPage  = 'experiences';
$pageTitle    = 'Riwayat Pengalaman';
$pageSubtitle = 'Kelola riwayat kerja magang, kepengurusan organisasi, kepanitiaan, lomba, dan tipe pengalaman';

require_once __DIR__ . '/includes/admin_init.php';

// ══════════════════════════════════════════════════════════
//  POST HANDLERS — semua sebelum HTML output
// ══════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: experiences.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ─────────────────────────────────────────
    //  CRUD PENGALAMAN (EXPERIENCE)
    // ─────────────────────────────────────────
    if ($action === 'add') {
        $type         = trim($_POST['type'] ?? 'Work');
        $title        = trim($_POST['title'] ?? '');
        $org          = trim($_POST['organization'] ?? '');
        $start        = !empty($_POST['period_start']) ? $_POST['period_start'] : null;
        $end          = !empty($_POST['period_end']) ? $_POST['period_end'] : null;
        $isCurrent    = isset($_POST['is_current']) ? 1 : 0;
        $desc         = trim($_POST['description'] ?? '');
        $achievements = trim($_POST['achievements'] ?? '');
        $sortOrder    = (int)($_POST['sort_order'] ?? 0);

        if ($isCurrent) $end = null;

        try {
            $stmt = $pdo->prepare("INSERT INTO experiences (type, title, organization, period_start, period_end, is_current, description, achievements, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$type, $title, $org, $start, $end, $isCurrent, $desc, $achievements, $sortOrder]);
            set_flash('success', 'Riwayat pengalaman baru berhasil ditambahkan!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal menambah pengalaman: ' . $e->getMessage());
        }
        header('Location: experiences.php#tab-experiences');
        exit;
    }

    if ($action === 'edit') {
        $id           = (int)($_POST['experience_id'] ?? 0);
        $type         = trim($_POST['type'] ?? 'Work');
        $title        = trim($_POST['title'] ?? '');
        $org          = trim($_POST['organization'] ?? '');
        $start        = !empty($_POST['period_start']) ? $_POST['period_start'] : null;
        $end          = !empty($_POST['period_end']) ? $_POST['period_end'] : null;
        $isCurrent    = isset($_POST['is_current']) ? 1 : 0;
        $desc         = trim($_POST['description'] ?? '');
        $achievements = trim($_POST['achievements'] ?? '');
        $sortOrder    = (int)($_POST['sort_order'] ?? 0);

        if ($isCurrent) $end = null;

        try {
            $stmt = $pdo->prepare("UPDATE experiences SET type = ?, title = ?, organization = ?, period_start = ?, period_end = ?, is_current = ?, description = ?, achievements = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$type, $title, $org, $start, $end, $isCurrent, $desc, $achievements, $sortOrder, $id]);
            set_flash('success', 'Data pengalaman berhasil diperbarui!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal memperbarui pengalaman: ' . $e->getMessage());
        }
        header('Location: experiences.php#tab-experiences');
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['experience_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM experiences WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Pengalaman berhasil dihapus.');
        }
        header('Location: experiences.php#tab-experiences');
        exit;
    }

    // ─────────────────────────────────────────
    //  CRUD TIPE PENGALAMAN (EXPERIENCE TYPES)
    // ─────────────────────────────────────────
    if ($action === 'add_type') {
        $name = trim($_POST['type_name'] ?? '');
        $sort = (int)($_POST['sort_order'] ?? 0);

        if ($name === '') {
            set_flash('danger', 'Nama tipe pengalaman tidak boleh kosong.');
        } else {
            $dup = $pdo->prepare("SELECT COUNT(*) FROM experience_types WHERE LOWER(type_name) = LOWER(?)");
            $dup->execute([$name]);
            if ((int)$dup->fetchColumn() > 0) {
                set_flash('danger', "Tipe pengalaman \"" . htmlspecialchars($name) . "\" sudah ada.");
            } else {
                if ($sort <= 0) {
                    $sort = (int)$pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM experience_types")->fetchColumn();
                }
                $pdo->prepare("INSERT INTO experience_types (type_name, sort_order) VALUES (?, ?)")->execute([$name, $sort]);
                set_flash('success', "Tipe pengalaman baru \"" . htmlspecialchars($name) . "\" berhasil ditambahkan!");
            }
        }
        header('Location: experiences.php#tab-tipe');
        exit;
    }

    if ($action === 'edit_type') {
        $id   = (int)($_POST['type_id'] ?? 0);
        $name = trim($_POST['type_name'] ?? '');
        $sort = (int)($_POST['sort_order'] ?? 0);

        if ($id <= 0 || $name === '') {
            set_flash('danger', 'Data tipe pengalaman tidak valid.');
        } else {
            $dup = $pdo->prepare("SELECT COUNT(*) FROM experience_types WHERE LOWER(type_name) = LOWER(?) AND id != ?");
            $dup->execute([$name, $id]);
            if ((int)$dup->fetchColumn() > 0) {
                set_flash('danger', "Nama tipe \"" . htmlspecialchars($name) . "\" sudah digunakan tipe lain.");
            } else {
                // Ambil nama tipe lama untuk cascade update ke tabel experiences
                $qOld = $pdo->prepare("SELECT type_name FROM experience_types WHERE id = ?");
                $qOld->execute([$id]);
                $oldName = $qOld->fetchColumn();

                $pdo->prepare("UPDATE experience_types SET type_name = ?, sort_order = ? WHERE id = ?")->execute([$name, $sort, $id]);

                // Cascade update pengalaman yang menggunakan tipe ini
                if ($oldName && $oldName !== $name) {
                    $pdo->prepare("UPDATE experiences SET type = ? WHERE type = ?")->execute([$name, $oldName]);
                }

                set_flash('success', "Tipe pengalaman berhasil diperbarui!");
            }
        }
        header('Location: experiences.php#tab-tipe');
        exit;
    }

    if ($action === 'delete_type') {
        $id = (int)($_POST['type_id'] ?? 0);
        if ($id > 0) {
            $qType = $pdo->prepare("SELECT type_name FROM experience_types WHERE id = ?");
            $qType->execute([$id]);
            $typeName = $qType->fetchColumn();

            // Cek apakah masih digunakan di tabel experiences
            $cnt = $pdo->prepare("SELECT COUNT(*) FROM experiences WHERE type = ?");
            $cnt->execute([$typeName]);
            $count = (int)$cnt->fetchColumn();

            if ($count > 0) {
                set_flash('danger', "Tipe <strong>" . htmlspecialchars($typeName) . "</strong> tidak dapat dihapus karena masih digunakan oleh <strong>$count riwayat pengalaman</strong>. Silakan ubah atau hapus pengalaman terkait terlebih dahulu.");
            } else {
                $pdo->prepare("DELETE FROM experience_types WHERE id = ?")->execute([$id]);
                set_flash('success', "Tipe pengalaman \"" . htmlspecialchars($typeName) . "\" berhasil dihapus.");
            }
        }
        header('Location: experiences.php#tab-tipe');
        exit;
    }

    header('Location: experiences.php');
    exit;
}

// ── Load data ─────────────────────────────────────────────
$experiences = $pdo->query("SELECT * FROM experiences ORDER BY sort_order ASC, period_start DESC")->fetchAll();
$expTypes    = $pdo->query("
    SELECT et.*, 
           (SELECT COUNT(*) FROM experiences e WHERE e.type = et.type_name) AS exp_count 
    FROM experience_types et 
    ORDER BY et.sort_order ASC, et.type_name ASC
")->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- ══ TAB NAVIGATION ══ -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <ul class="nav nav-pills gap-1" id="expTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active px-4" id="tab-exp-btn"
                    data-bs-toggle="pill" data-bs-target="#tab-experiences" type="button">
                <i class="bi bi-briefcase me-1"></i> Daftar Pengalaman
                <span class="badge rounded-pill ms-1" style="background:rgba(92,111,43,.15);color:var(--color-primary);font-size:.7rem;"><?= count($experiences) ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link px-4" id="tab-tipe-btn"
                    data-bs-toggle="pill" data-bs-target="#tab-tipe" type="button">
                <i class="bi bi-tags me-1"></i> Kelola Tipe Pengalaman
                <span class="badge rounded-pill ms-1" style="background:rgba(92,111,43,.15);color:var(--color-primary);font-size:.7rem;"><?= count($expTypes) ?></span>
            </button>
        </li>
    </ul>

    <!-- Tombol aksi dinamis per tab -->
    <div id="btn-exp-action">
        <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addExpModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Pengalaman Baru
        </button>
    </div>
    <div id="btn-tipe-action" style="display:none;">
        <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Tipe Baru
        </button>
    </div>
</div>

<!-- ══ TAB CONTENT ══ -->
<div class="tab-content">

    <!-- ═══════════════════════════════════════════
         TAB 1 — DAFTAR PENGALAMAN
    ═══════════════════════════════════════════ -->
    <div class="tab-pane fade show active" id="tab-experiences">
        <div class="admin-card">
            <?php if (empty($experiences)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-briefcase fs-1 d-block mb-3 opacity-25"></i>
                    Belum ada data pengalaman. Klik tombol di atas untuk menambah riwayat pengalaman pertama.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead>
                            <tr>
                                <th>Tipe</th>
                                <th>Posisi / Peran</th>
                                <th>Organisasi / Institusi</th>
                                <th>Periode</th>
                                <th>Urutan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($experiences as $ex): 
                                $pStr = format_period($ex['period_start'], $ex['period_end'], $ex['is_current']);
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge px-2.5 py-1 rounded-pill fw-semibold" style="background:var(--color-soft-olive);color:var(--color-dark);font-size:0.75rem;">
                                            <i class="bi bi-tag me-1"></i><?= e($ex['type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($ex['title']) ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 280px;"><?= e($ex['description']) ?></div>
                                    </td>
                                    <td class="text-dark fw-medium"><?= e($ex['organization']) ?></td>
                                    <td class="small text-muted"><?= e($pStr) ?></td>
                                    <td>
                                        <span class="badge bg-light text-muted border"><?= (int)$ex['sort_order'] ?></span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-outline-secondary btn-sm py-1 px-2.5 me-1"
                                            onclick="openEditExp(<?= htmlspecialchars(json_encode($ex), ENT_QUOTES, 'UTF-8') ?>)"
                                            title="Edit Pengalaman">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="experiences.php" class="d-inline" onsubmit="return confirm('Hapus riwayat pengalaman ini?');">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="experience_id" value="<?= $ex['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2.5" title="Hapus">
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
    </div>

    <!-- ═══════════════════════════════════════════
         TAB 2 — KELOLA TIPE PENGALAMAN
    ═══════════════════════════════════════════ -->
    <div class="tab-pane fade" id="tab-tipe">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-0">Daftar Tipe / Kategori Pengalaman</h6>
                    <p class="text-muted small mb-0">Tipe-tipe ini akan muncul pada pilihan dropdown saat menambah atau mengedit pengalaman.</p>
                </div>
            </div>

            <?php if (empty($expTypes)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-tags fs-1 d-block mb-3 opacity-25"></i>
                    Belum ada tipe pengalaman. Klik tombol "Tambah Tipe Baru" untuk membuat tipe pertama.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Nama Tipe Pengalaman</th>
                                <th>Jumlah Pengalaman Terkait</th>
                                <th>Urutan Tampil</th>
                                <th class="text-end" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expTypes as $idx => $t): ?>
                                <tr>
                                    <td class="text-muted small"><?= $idx + 1 ?></td>
                                    <td>
                                        <span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background:var(--color-soft-olive);color:var(--color-dark);font-size:0.8rem;">
                                            <i class="bi bi-tag-fill me-1 text-primary-olive"></i><?= e($t['type_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ((int)$t['exp_count'] > 0): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1">
                                                <i class="bi bi-briefcase me-1"></i><?= $t['exp_count'] ?> Pengalaman
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">0 (Belum digunakan)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-muted border"><?= (int)$t['sort_order'] ?></span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-outline-secondary btn-sm py-1 px-2.5 me-1"
                                                onclick="openEditType(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8') ?>)"
                                                title="Edit Tipe">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="experiences.php" class="d-inline"
                                              onsubmit="return confirm('Hapus tipe pengalaman &quot;<?= e(addslashes($t['type_name'])) ?>&quot;?');">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                            <input type="hidden" name="action" value="delete_type">
                                            <input type="hidden" name="type_id" value="<?= $t['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2.5"
                                                    title="Hapus Tipe"
                                                    <?= ((int)$t['exp_count'] > 0) ? 'data-bs-toggle="tooltip" data-bs-title="Tipe masih digunakan"' : '' ?>>
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
    </div>
</div>

<!-- ═══════════════════════════════════════════
     MODAL TAMBAH PENGALAMAN
═══════════════════════════════════════════ -->
<div class="modal fade" id="addExpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <h5 class="modal-title fw-bold font-heading text-dark">Tambah Pengalaman Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="experiences.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="row g-3">
                        <!-- Tipe Pengalaman (Dinamis dari DB) -->
                        <div class="col-md-5">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Tipe / Kategori *</label>
                                <a href="#tab-tipe" onclick="switchToTypeTab()" class="text-primary-olive small text-decoration-none" style="font-size:0.75rem;">
                                    <i class="bi bi-gear me-1"></i>Kelola Tipe
                                </a>
                            </div>
                            <select name="type" id="addExpType" class="form-select form-folio" required onchange="handleTypeSelectChange(this)">
                                <?php foreach ($expTypes as $t): ?>
                                    <option value="<?= e($t['type_name']) ?>"><?= e($t['type_name']) ?></option>
                                <?php endforeach; ?>
                                <option value="__new__" class="fw-bold text-primary-olive">+ Tambah Tipe Baru...</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label text-muted small fw-semibold">Posisi / Peran *</label>
                            <input type="text" name="title" class="form-control form-folio" placeholder="cth: Digital Marketing Intern" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-muted small fw-semibold">Organisasi / Perusahaan *</label>
                            <input type="text" name="organization" class="form-control form-folio" placeholder="cth: PT Inovasi Niaga Digital" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Urutan (Sort Order)</label>
                            <input type="number" name="sort_order" class="form-control form-folio" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tanggal / Periode Mulai</label>
                            <input type="date" name="period_start" class="form-control form-folio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tanggal Selesai</label>
                            <input type="date" name="period_end" id="addExpEnd" class="form-control form-folio">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="is_current" id="addExpCurrent" value="1"
                                    onchange="document.getElementById('addExpEnd').disabled = this.checked;">
                                <label class="form-check-label text-muted small" for="addExpCurrent">Masih berlangsung (Current)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">Deskripsi Peran & Tanggung Jawab *</label>
                            <textarea name="description" rows="3" class="form-control form-folio" placeholder="Uraikan tanggung jawab dan aktivitas utama..." required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">Pencapaian / Kontribusi</label>
                            <textarea name="achievements" rows="2" class="form-control form-folio" placeholder="cth: Meningkatkan ROAS iklan sebesar 3.8x"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Simpan Pengalaman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     MODAL EDIT PENGALAMAN
═══════════════════════════════════════════ -->
<div class="modal fade" id="editExpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <h5 class="modal-title fw-bold font-heading text-dark">Edit Pengalaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="experiences.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="experience_id" id="editExpId">

                    <div class="row g-3">
                        <!-- Tipe Pengalaman (Dinamis dari DB) -->
                        <div class="col-md-5">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-semibold mb-0">Tipe / Kategori *</label>
                                <a href="#tab-tipe" onclick="switchToTypeTab()" class="text-primary-olive small text-decoration-none" style="font-size:0.75rem;">
                                    <i class="bi bi-gear me-1"></i>Kelola Tipe
                                </a>
                            </div>
                            <select name="type" id="editExpType" class="form-select form-folio" required onchange="handleTypeSelectChange(this)">
                                <?php foreach ($expTypes as $t): ?>
                                    <option value="<?= e($t['type_name']) ?>"><?= e($t['type_name']) ?></option>
                                <?php endforeach; ?>
                                <option value="__new__" class="fw-bold text-primary-olive">+ Tambah Tipe Baru...</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label text-muted small fw-semibold">Posisi / Peran *</label>
                            <input type="text" name="title" id="editExpTitle" class="form-control form-folio" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-muted small fw-semibold">Organisasi / Institusi *</label>
                            <input type="text" name="organization" id="editExpOrg" class="form-control form-folio" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Urutan</label>
                            <input type="number" name="sort_order" id="editExpSort" class="form-control form-folio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tanggal Mulai</label>
                            <input type="date" name="period_start" id="editExpStart" class="form-control form-folio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tanggal Selesai</label>
                            <input type="date" name="period_end" id="editExpEnd" class="form-control form-folio">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="is_current" id="editExpCurrent" value="1"
                                    onchange="document.getElementById('editExpEnd').disabled = this.checked;">
                                <label class="form-check-label text-muted small" for="editExpCurrent">Masih berlangsung (Current)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">Deskripsi *</label>
                            <textarea name="description" id="editExpDesc" rows="3" class="form-control form-folio" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">Pencapaian</label>
                            <textarea name="achievements" id="editExpAch" rows="2" class="form-control form-folio"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Perbarui Pengalaman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     MODAL TAMBAH TIPE PENGALAMAN
═══════════════════════════════════════════ -->
<div class="modal fade" id="addTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <h5 class="modal-title fw-bold font-heading text-dark">
                    <i class="bi bi-tag-fill text-primary-olive me-1"></i> Tambah Tipe Pengalaman Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="experiences.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add_type">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Nama Tipe Pengalaman *</label>
                        <input type="text" name="type_name" class="form-control form-folio"
                               placeholder="cth: Magang / Internship, Studi Independen, Project Based" required autofocus>
                        <div class="form-text text-muted" style="font-size:0.75rem;">Nama tipe akan muncul di kartu pengalaman publik dan pilihan dropdown.</div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted small fw-semibold">Urutan (Sort Order)</label>
                        <input type="number" name="sort_order" class="form-control form-folio" value="0">
                        <div class="form-text text-muted" style="font-size:0.75rem;">Nilai lebih kecil tampil lebih dulu.</div>
                    </div>
                </div>
                <div class="modal-footer p-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Simpan Tipe Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     MODAL EDIT TIPE PENGALAMAN
═══════════════════════════════════════════ -->
<div class="modal fade" id="editTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <h5 class="modal-title fw-bold font-heading text-dark">
                    <i class="bi bi-pencil-square text-primary-olive me-1"></i> Edit Tipe Pengalaman
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="experiences.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="edit_type">
                    <input type="hidden" name="type_id" id="editTypeId">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Nama Tipe Pengalaman *</label>
                        <input type="text" name="type_name" id="editTypeName" class="form-control form-folio" required>
                        <div class="form-text text-muted" style="font-size:0.75rem;">Mengubah nama tipe akan otomatis memperbarui semua riwayat pengalaman terkait.</div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted small fw-semibold">Urutan (Sort Order)</label>
                        <input type="number" name="sort_order" id="editTypeSort" class="form-control form-folio">
                    </div>
                </div>
                <div class="modal-footer p-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Perbarui Tipe</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══ SCRIPTS ══ -->
<script>
// Switch tabs between Experiences and Types
function updateActionButton(targetTab) {
    const btnExp  = document.getElementById('btn-exp-action');
    const btnTipe = document.getElementById('btn-tipe-action');
    if (targetTab === '#tab-tipe') {
        btnExp.style.display  = 'none';
        btnTipe.style.display = 'block';
    } else {
        btnExp.style.display  = 'block';
        btnTipe.style.display = 'none';
    }
}

function switchToTypeTab() {
    // Tutup modal yang sedang terbuka jika ada
    const addModal = bootstrap.Modal.getInstance(document.getElementById('addExpModal'));
    if (addModal) addModal.hide();
    const editModal = bootstrap.Modal.getInstance(document.getElementById('editExpModal'));
    if (editModal) editModal.hide();

    const tipeTabBtn = document.getElementById('tab-tipe-btn');
    if (tipeTabBtn) {
        bootstrap.Tab.getOrCreateInstance(tipeTabBtn).show();
        updateActionButton('#tab-tipe');
        window.location.hash = 'tab-tipe';
    }
}

function handleTypeSelectChange(sel) {
    if (sel.value === '__new__') {
        // Reset select ke opsi pertama
        if (sel.options.length > 1) {
            sel.selectedIndex = 0;
        }
        // Buka modal tambah tipe
        new bootstrap.Modal(document.getElementById('addTypeModal')).show();
    }
}

function openEditExp(ex) {
    document.getElementById('editExpId').value = ex.id;
    document.getElementById('editExpType').value = ex.type;
    document.getElementById('editExpTitle').value = ex.title;
    document.getElementById('editExpOrg').value = ex.organization;
    document.getElementById('editExpStart').value = ex.period_start || '';
    document.getElementById('editExpEnd').value = ex.period_end || '';
    document.getElementById('editExpCurrent').checked = (parseInt(ex.is_current) === 1);
    document.getElementById('editExpEnd').disabled = (parseInt(ex.is_current) === 1);
    document.getElementById('editExpDesc').value = ex.description;
    document.getElementById('editExpAch').value = ex.achievements || '';
    document.getElementById('editExpSort').value = ex.sort_order;

    new bootstrap.Modal(document.getElementById('editExpModal')).show();
}

function openEditType(t) {
    document.getElementById('editTypeId').value   = t.id;
    document.getElementById('editTypeName').value = t.type_name;
    document.getElementById('editTypeSort').value = t.sort_order;

    new bootstrap.Modal(document.getElementById('editTypeModal')).show();
}

// Tab click listener
document.addEventListener('DOMContentLoaded', () => {
    const tabBtns = document.querySelectorAll('#expTab button[data-bs-toggle="pill"]');
    tabBtns.forEach(btn => {
        btn.addEventListener('shown.bs.tab', (e) => {
            const target = e.target.getAttribute('data-bs-target');
            updateActionButton(target);
            if (history.replaceState) {
                history.replaceState(null, null, target);
            }
        });
    });

    // Hash navigation on load
    const hash = window.location.hash;
    if (hash === '#tab-tipe') {
        const btn = document.getElementById('tab-tipe-btn');
        if (btn) {
            bootstrap.Tab.getOrCreateInstance(btn).show();
            updateActionButton('#tab-tipe');
        }
    } else {
        updateActionButton('#tab-experiences');
    }
});
</script>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
