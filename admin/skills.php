<?php
// admin/skills.php — Manage Skills + Kelola Kategori Keahlian
$pageTitle    = 'Keahlian (Skills)';
$pageSubtitle = 'Kelola daftar keahlian, kategori kompetensi, dan tingkat penguasaan';
$currentPage  = 'skills';

require_once __DIR__ . '/includes/admin_init.php';

// ══════════════════════════════════════════════════════════
//  POST HANDLERS — semua sebelum HTML
// ══════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: skills.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ─────────────────────────────────────────
    //  CRUD SKILL
    // ─────────────────────────────────────────
    if ($action === 'add_skill') {
        $name       = trim($_POST['skill_name'] ?? '');
        $category   = trim($_POST['category'] ?? '');
        $proficiency= trim($_POST['proficiency'] ?? 'Intermediate');
        $desc       = trim($_POST['description'] ?? '');
        $sortOrder  = (int)($_POST['sort_order'] ?? 0);
        try {
            $pdo->prepare("INSERT INTO skills (skill_name, category, proficiency, description, sort_order, created_at) VALUES (?,?,?,?,?,NOW())")
                ->execute([$name, $category, $proficiency, $desc, $sortOrder]);
            set_flash('success', "Keahlian \"" . htmlspecialchars($name) . "\" berhasil ditambahkan!");
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal menambah keahlian: ' . $e->getMessage());
        }
        header('Location: skills.php#tab-skills');
        exit;
    }

    if ($action === 'edit_skill') {
        $id         = (int)($_POST['skill_id'] ?? 0);
        $name       = trim($_POST['skill_name'] ?? '');
        $category   = trim($_POST['category'] ?? '');
        $proficiency= trim($_POST['proficiency'] ?? 'Intermediate');
        $desc       = trim($_POST['description'] ?? '');
        $sortOrder  = (int)($_POST['sort_order'] ?? 0);
        try {
            $pdo->prepare("UPDATE skills SET skill_name=?, category=?, proficiency=?, description=?, sort_order=? WHERE id=?")
                ->execute([$name, $category, $proficiency, $desc, $sortOrder, $id]);
            set_flash('success', 'Data keahlian berhasil diperbarui!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal memperbarui: ' . $e->getMessage());
        }
        header('Location: skills.php#tab-skills');
        exit;
    }

    if ($action === 'delete_skill') {
        $id = (int)($_POST['skill_id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM skills WHERE id=?")->execute([$id]);
            set_flash('success', 'Keahlian berhasil dihapus.');
        }
        header('Location: skills.php#tab-skills');
        exit;
    }

    // ─────────────────────────────────────────
    //  CRUD KATEGORI KEAHLIAN
    // ─────────────────────────────────────────
    if ($action === 'add_cat') {
        $name = trim($_POST['cat_name'] ?? '');
        if ($name === '') {
            set_flash('danger', 'Nama kategori tidak boleh kosong.');
        } else {
            $dup = $pdo->prepare("SELECT COUNT(*) FROM skill_categories WHERE category_name = ?");
            $dup->execute([$name]);
            if ((int)$dup->fetchColumn() > 0) {
                set_flash('danger', "Kategori \"" . htmlspecialchars($name) . "\" sudah ada.");
            } else {
                $ord = (int)$pdo->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM skill_categories")->fetchColumn();
                $pdo->prepare("INSERT INTO skill_categories (category_name, sort_order) VALUES (?,?)")->execute([$name, $ord]);
                set_flash('success', "Kategori \"" . htmlspecialchars($name) . "\" berhasil ditambahkan.");
            }
        }
        header('Location: skills.php#tab-kategori');
        exit;
    }

    if ($action === 'edit_cat') {
        $id   = (int)($_POST['cat_id'] ?? 0);
        $name = trim($_POST['cat_name'] ?? '');
        if ($id <= 0 || $name === '') {
            set_flash('danger', 'Data tidak valid.');
        } else {
            $dup = $pdo->prepare("SELECT COUNT(*) FROM skill_categories WHERE category_name = ? AND id != ?");
            $dup->execute([$name, $id]);
            if ((int)$dup->fetchColumn() > 0) {
                set_flash('danger', "Nama kategori sudah digunakan.");
            } else {
                // Sync nama di tabel skills juga
                $old = $pdo->prepare("SELECT category_name FROM skill_categories WHERE id=?");
                $old->execute([$id]);
                $oldName = $old->fetchColumn();
                $pdo->prepare("UPDATE skill_categories SET category_name=? WHERE id=?")->execute([$name, $id]);
                if ($oldName && $oldName !== $name) {
                    $pdo->prepare("UPDATE skills SET category=? WHERE category=?")->execute([$name, $oldName]);
                }
                set_flash('success', "Kategori berhasil diperbarui menjadi \"" . htmlspecialchars($name) . "\".");
            }
        }
        header('Location: skills.php#tab-kategori');
        exit;
    }

    if ($action === 'delete_cat') {
        $id = (int)($_POST['cat_id'] ?? 0);
        if ($id > 0) {
            $catRow = $pdo->prepare("SELECT category_name FROM skill_categories WHERE id = ?");
            $catRow->execute([$id]);
            $catName = $catRow->fetchColumn();

            $cnt = $pdo->prepare("SELECT COUNT(*) FROM skills WHERE category = ?");
            $cnt->execute([$catName]);
            $count = (int)$cnt->fetchColumn();

            if ($count > 0) {
                set_flash('danger', "Kategori tidak dapat dihapus — masih digunakan oleh <strong>$count keahlian</strong>.");
            } else {
                $pdo->prepare("DELETE FROM skill_categories WHERE id = ?")->execute([$id]);
                set_flash('success', 'Kategori keahlian berhasil dihapus.');
            }
        }
        header('Location: skills.php#tab-kategori');
        exit;
    }

    header('Location: skills.php');
    exit;
}

// ── Load data ─────────────────────────────────────────────
$skills     = $pdo->query("SELECT * FROM skills ORDER BY category ASC, sort_order ASC")->fetchAll();
$skillCats  = $pdo->query("SELECT sc.*, (SELECT COUNT(*) FROM skills s WHERE s.category = sc.category_name) AS skill_count FROM skill_categories sc ORDER BY sc.sort_order ASC, sc.category_name ASC")->fetchAll();
$catOptions = array_column($skillCats, 'category_name'); // untuk dropdown

// Kelompokkan skills per kategori untuk tampilan
$skillsByCategory = [];
foreach ($skills as $sk) {
    $skillsByCategory[$sk['category']][] = $sk;
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- ══ TAB NAVIGATION ══ -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <ul class="nav nav-pills gap-1" id="skillsTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active px-4" id="tab-skills-btn"
                    data-bs-toggle="pill" data-bs-target="#tab-skills" type="button">
                <i class="bi bi-stars me-1"></i> Daftar Keahlian
                <span class="badge rounded-pill ms-1" style="background:rgba(92,111,43,.15);color:var(--color-primary);font-size:.7rem;"><?= count($skills) ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link px-4" id="tab-kategori-btn"
                    data-bs-toggle="pill" data-bs-target="#tab-kategori" type="button">
                <i class="bi bi-tags me-1"></i> Kelola Kategori
                <span class="badge rounded-pill ms-1" style="background:rgba(92,111,43,.15);color:var(--color-primary);font-size:.7rem;"><?= count($skillCats) ?></span>
            </button>
        </li>
    </ul>

    <!-- Tombol aksi (kontekstual per tab) -->
    <div id="btn-skills-action">
        <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addSkillModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Keahlian
        </button>
    </div>
    <div id="btn-kategori-action" style="display:none;">
        <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addCatModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
        </button>
    </div>
</div>

<!-- ══ TAB CONTENT ══ -->
<div class="tab-content">

    <!-- ═══════════════════════════════════════════
         TAB 1 — DAFTAR KEAHLIAN
    ═══════════════════════════════════════════ -->
    <div class="tab-pane fade show active" id="tab-skills">
        <?php if (empty($skills)): ?>
            <div class="admin-card text-center py-5 text-muted">
                <i class="bi bi-stars fs-1 d-block mb-3 opacity-25"></i>
                Belum ada keahlian. Tambahkan skill pertama Anda.
            </div>
        <?php else: ?>
            <?php foreach ($skillsByCategory as $catName => $catSkills): ?>
            <div class="mb-4">
                <!-- Group header -->
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge px-3 py-2 rounded-pill fw-semibold"
                          style="background:var(--color-soft-olive);color:var(--color-dark);font-size:.78rem;">
                        <i class="bi bi-tag me-1"></i><?= e($catName) ?>
                    </span>
                    <span class="text-muted small"><?= count($catSkills) ?> keahlian</span>
                </div>

                <div class="admin-card p-0 overflow-hidden mb-0">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Nama Keahlian</th>
                                    <th>Level / Proficiency</th>
                                    <th>Deskripsi</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($catSkills as $sk):
                                    $prof = $sk['proficiency'] ?? 'Intermediate';
                                    $profStyle = match($prof) {
                                        'Advanced'     => 'background:#DCFCE7;color:#166534;',
                                        'Beginner'     => 'background:#FEF9C3;color:#854D0E;',
                                        default        => 'background:#F1F5F9;color:#475569;',
                                    };
                                    $profIcon = match($prof) {
                                        'Advanced' => 'bi-patch-check-fill',
                                        'Beginner' => 'bi-hourglass-split',
                                        default    => 'bi-bar-chart-fill',
                                    };
                                ?>
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark"><?= e($sk['skill_name']) ?></td>
                                    <td>
                                        <span class="badge rounded-pill px-3 py-1 fw-semibold"
                                              style="font-size:.75rem;<?= $profStyle ?>">
                                            <i class="bi <?= $profIcon ?> me-1"></i><?= e($prof) ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted text-truncate" style="max-width:260px;">
                                        <?= e($sk['description'] ?: '—') ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-outline-secondary btn-sm py-1 px-2 me-1"
                                                onclick="openEditSkill(<?= htmlspecialchars(json_encode($sk), ENT_QUOTES, 'UTF-8') ?>)"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="skills.php" class="d-inline"
                                              onsubmit="return confirm('Hapus keahlian ini?');">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                            <input type="hidden" name="action" value="delete_skill">
                                            <input type="hidden" name="skill_id" value="<?= $sk['id'] ?>">
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
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ═══════════════════════════════════════════
         TAB 2 — KELOLA KATEGORI
    ═══════════════════════════════════════════ -->
    <div class="tab-pane fade" id="tab-kategori">
        <?php if (empty($skillCats)): ?>
            <div class="admin-card text-center py-5 text-muted">
                <i class="bi bi-tags fs-1 d-block mb-3 opacity-25"></i>
                Belum ada kategori keahlian.
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm" style="border-radius:var(--radius-card);">
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead style="background:var(--color-background);">
                            <tr>
                                <th class="ps-4 py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:var(--color-muted);">#</th>
                                <th class="py-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:var(--color-muted);">Nama Kategori</th>
                                <th class="py-3 text-center" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:var(--color-muted);">Jumlah Keahlian</th>
                                <th class="py-3 text-end pe-4" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:var(--color-muted);">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($skillCats as $i => $cat): ?>
                            <tr>
                                <td class="ps-4 text-muted" style="font-size:.85rem;"><?= $i + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-2"
                                              style="width:32px;height:32px;background:var(--color-soft-olive);color:var(--color-primary);">
                                            <i class="bi bi-tag-fill" style="font-size:.85rem;"></i>
                                        </span>
                                        <span class="fw-semibold" style="font-size:.9rem;"><?= e($cat['category_name']) ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($cat['skill_count'] > 0): ?>
                                        <span class="badge rounded-pill px-3"
                                              style="background:var(--color-soft-olive);color:var(--color-dark);font-weight:600;">
                                            <?= $cat['skill_count'] ?> keahlian
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:.8rem;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary me-1"
                                            style="border-radius:8px;font-size:.8rem;"
                                            onclick="openEditCat(<?= $cat['id'] ?>, <?= json_encode($cat['category_name']) ?>)">
                                        <i class="bi bi-pencil me-1"></i>Edit
                                    </button>
                                    <form method="POST" action="skills.php" class="d-inline"
                                          onsubmit="return confirmDeleteCat(this, <?= json_encode($cat['category_name']) ?>, <?= (int)$cat['skill_count'] ?>)">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="action" value="delete_cat">
                                        <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:8px;font-size:.8rem;">
                                            <i class="bi bi-trash me-1"></i>Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Info -->
            <div class="mt-3 p-3 rounded-3 d-flex align-items-start gap-3"
                 style="background:#F0F4E8;border:1px solid #DCE3CB;">
                <i class="bi bi-info-circle-fill mt-1" style="color:var(--color-primary);flex-shrink:0;"></i>
                <div style="font-size:.84rem;color:var(--color-muted);">
                    <strong style="color:var(--color-dark);">Catatan:</strong>
                    Mengedit nama kategori akan otomatis memperbarui semua keahlian yang menggunakan kategori tersebut.
                    Kategori yang masih memiliki keahlian <strong>tidak dapat dihapus</strong>.
                </div>
            </div>
        <?php endif; ?>
    </div>

</div><!-- /.tab-content -->


<!-- ══════════════════════════════════════════════════════
     MODAL — TAMBAH SKILL
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="addSkillModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold">Tambah Keahlian Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="skills.php">
                <div class="modal-body px-4 pt-0 pb-3">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add_skill">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Nama Keahlian *</label>
                        <input type="text" name="skill_name" class="form-control form-folio"
                               placeholder="cth: Meta Ads Manager, Excel, Canva" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold d-flex align-items-center gap-2">
                            Kategori *
                            <button type="button" class="btn btn-link p-0 text-decoration-none"
                                    style="font-size:.75rem;color:var(--color-primary);"
                                    onclick="switchToKategoriTab()">
                                <i class="bi bi-plus-circle"></i> Tambah kategori baru
                            </button>
                        </label>
                        <select name="category" class="form-select form-folio" required>
                            <option value="">— Pilih Kategori —</option>
                            <?php foreach ($skillCats as $cat): ?>
                                <option value="<?= e($cat['category_name']) ?>"><?= e($cat['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($skillCats)): ?>
                            <div class="form-text text-danger">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                Belum ada kategori. <a href="#" onclick="switchToKategoriTab()">Tambah dulu</a>.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Tingkat Penguasaan *</label>
                        <select name="proficiency" class="form-select form-folio">
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate" selected>Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Deskripsi / Contoh Penerapan</label>
                        <input type="text" name="description" class="form-control form-folio"
                               placeholder="cth: Perencanaan kampanye dan optimasi ROAS">
                    </div>

                    <div class="mb-1">
                        <label class="form-label text-muted small fw-semibold">Urutan (Sort Order)</label>
                        <input type="number" name="sort_order" class="form-control form-folio" value="0">
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Simpan Keahlian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL — EDIT SKILL
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="editSkillModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold">Edit Keahlian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="skills.php">
                <div class="modal-body px-4 pt-0 pb-3">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="edit_skill">
                    <input type="hidden" name="skill_id" id="editSkillId">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Nama Keahlian *</label>
                        <input type="text" name="skill_name" id="editSkillName" class="form-control form-folio" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Kategori *</label>
                        <select name="category" id="editSkillCategory" class="form-select form-folio" required>
                            <option value="">— Pilih Kategori —</option>
                            <?php foreach ($skillCats as $cat): ?>
                                <option value="<?= e($cat['category_name']) ?>"><?= e($cat['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Tingkat Penguasaan *</label>
                        <select name="proficiency" id="editSkillProf" class="form-select form-folio">
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Deskripsi</label>
                        <input type="text" name="description" id="editSkillDesc" class="form-control form-folio">
                    </div>

                    <div class="mb-1">
                        <label class="form-label text-muted small fw-semibold">Urutan</label>
                        <input type="number" name="sort_order" id="editSkillSort" class="form-control form-folio">
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Perbarui Keahlian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL — TAMBAH KATEGORI
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="addCatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 p-4 pb-2">
                <div>
                    <h5 class="modal-title fw-bold mb-0">Tambah Kategori</h5>
                    <p class="text-muted small mb-0">Kategori keahlian baru</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="skills.php">
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add_cat">
                    <label class="form-label text-muted small fw-semibold">Nama Kategori *</label>
                    <input type="text" name="cat_name" class="form-control form-folio"
                           placeholder="cth: Design Tools, Programming" required autofocus maxlength="100">
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary"><i class="bi bi-plus-lg me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL — EDIT KATEGORI
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="editCatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 p-4 pb-2">
                <div>
                    <h5 class="modal-title fw-bold mb-0">Edit Kategori</h5>
                    <p class="text-muted small mb-0">Perubahan berlaku ke semua keahlian</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="skills.php">
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="edit_cat">
                    <input type="hidden" name="cat_id" id="editCatId">
                    <label class="form-label text-muted small fw-semibold">Nama Kategori *</label>
                    <input type="text" name="cat_name" id="editCatName" class="form-control form-folio"
                           required maxlength="100">
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══ Scripts ══ -->
<script>
// ── Tombol Tambah kontekstual per tab ──
const tabSkillsBtn   = document.getElementById('tab-skills-btn');
const tabKategoriBtn = document.getElementById('tab-kategori-btn');
const btnSkillsAct   = document.getElementById('btn-skills-action');
const btnKategoriAct = document.getElementById('btn-kategori-action');

function showSkillsAction() {
    btnSkillsAct.style.display   = '';
    btnKategoriAct.style.display = 'none';
}

function showKategoriAction() {
    btnSkillsAct.style.display   = 'none';
    btnKategoriAct.style.display = '';
}

tabSkillsBtn.addEventListener('shown.bs.tab', showSkillsAction);
tabKategoriBtn.addEventListener('shown.bs.tab', showKategoriAction);
tabSkillsBtn.addEventListener('click', showSkillsAction);
tabKategoriBtn.addEventListener('click', showKategoriAction);

// Switch ke tab Kategori dari link dalam modal
function switchToKategoriTab() {
    const addModalEl = document.getElementById('addSkillModal');
    const modalInstance = bootstrap.Modal.getInstance(addModalEl) || new bootstrap.Modal(addModalEl);
    modalInstance.hide();

    setTimeout(() => {
        const tabTrigger = new bootstrap.Tab(tabKategoriBtn);
        tabTrigger.show();
        showKategoriAction();

        setTimeout(() => {
            const addCatModal = new bootstrap.Modal(document.getElementById('addCatModal'));
            addCatModal.show();
        }, 200);
    }, 300);
}

// ── Edit Skill ──
function openEditSkill(sk) {
    document.getElementById('editSkillId').value        = sk.id;
    document.getElementById('editSkillName').value      = sk.skill_name || '';
    document.getElementById('editSkillCategory').value  = sk.category || '';
    document.getElementById('editSkillProf').value      = sk.proficiency || 'Intermediate';
    document.getElementById('editSkillDesc').value      = sk.description || '';
    document.getElementById('editSkillSort').value      = sk.sort_order || 0;
    new bootstrap.Modal(document.getElementById('editSkillModal')).show();
}

// ── Edit Kategori ──
function openEditCat(id, name) {
    document.getElementById('editCatId').value   = id;
    document.getElementById('editCatName').value = name;
    new bootstrap.Modal(document.getElementById('editCatModal')).show();
}

// ── Konfirmasi Hapus Kategori ──
function confirmDeleteCat(form, name, count) {
    if (count > 0) {
        alert('⚠️ Kategori "' + name + '" masih digunakan oleh ' + count + ' keahlian.\nHapus atau pindahkan keahlian tersebut terlebih dahulu.');
        return false;
    }
    return confirm('Hapus kategori "' + name + '"?\nTindakan ini tidak dapat dibatalkan.');
}

// ── Auto-aktivasi tab dari URL hash ──
document.addEventListener('DOMContentLoaded', function () {
    if (window.location.hash === '#tab-kategori') {
        const tabTrigger = new bootstrap.Tab(tabKategoriBtn);
        tabTrigger.show();
        showKategoriAction();
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
