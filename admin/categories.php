<?php
// admin/categories.php — CRUD Kategori Proyek
$pageTitle    = 'Kategori Proyek';
$pageSubtitle = 'Kelola kategori untuk filter dan pengelompokan proyek portofolio';
$currentPage  = 'categories';

require_once __DIR__ . '/includes/admin_init.php';

// ══════════════════════════════════════════════════════════
//  POST HANDLERS — semua sebelum HTML output
// ══════════════════════════════════════════════════════════

// Helper: generate slug unik
function unique_slug(PDO $pdo, string $text, ?int $excludeId = null): string {
    $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($text)), '-') ?: 'kategori';
    $slug = $base;
    $i    = 1;
    while (true) {
        $q = $pdo->prepare("SELECT COUNT(*) FROM project_categories WHERE slug = ? AND id != ?");
        $q->execute([$slug, $excludeId ?? 0]);
        if ((int)$q->fetchColumn() === 0) break;
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── TAMBAH ──────────────────────────────────────────────
    if ($action === 'add') {
        $name = trim($_POST['category_name'] ?? '');
        if ($name === '') {
            set_flash('danger', 'Nama kategori tidak boleh kosong.');
        } else {
            // Cek duplikat nama
            $dup = $pdo->prepare("SELECT COUNT(*) FROM project_categories WHERE category_name = ?");
            $dup->execute([$name]);
            if ((int)$dup->fetchColumn() > 0) {
                set_flash('danger', "Kategori \"" . htmlspecialchars($name) . "\" sudah ada.");
            } else {
                $slug      = unique_slug($pdo, $name);
                $sortOrder = (int)($pdo->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM project_categories")->fetchColumn());
                $ins       = $pdo->prepare("INSERT INTO project_categories (category_name, slug, sort_order) VALUES (?, ?, ?)");
                $ins->execute([$name, $slug, $sortOrder]);
                set_flash('success', "Kategori \"" . htmlspecialchars($name) . "\" berhasil ditambahkan.");
            }
        }
        header('Location: categories.php');
        exit;
    }

    // ── EDIT ────────────────────────────────────────────────
    if ($action === 'edit') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['category_name'] ?? '');
        if ($id <= 0 || $name === '') {
            set_flash('danger', 'Data tidak valid.');
        } else {
            $dup = $pdo->prepare("SELECT COUNT(*) FROM project_categories WHERE category_name = ? AND id != ?");
            $dup->execute([$name, $id]);
            if ((int)$dup->fetchColumn() > 0) {
                set_flash('danger', "Nama \"" . htmlspecialchars($name) . "\" sudah digunakan kategori lain.");
            } else {
                $slug = unique_slug($pdo, $name, $id);
                $upd  = $pdo->prepare("UPDATE project_categories SET category_name = ?, slug = ? WHERE id = ?");
                $upd->execute([$name, $slug, $id]);
                set_flash('success', "Kategori berhasil diperbarui menjadi \"" . htmlspecialchars($name) . "\".");
            }
        }
        header('Location: categories.php');
        exit;
    }

    // ── HAPUS ───────────────────────────────────────────────
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            set_flash('danger', 'ID kategori tidak valid.');
        } else {
            // Cek proyek yang masih menggunakan kategori ini
            $usedBy = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE category_id = ?");
            $usedBy->execute([$id]);
            $count = (int)$usedBy->fetchColumn();
            if ($count > 0) {
                set_flash('danger', "Kategori tidak dapat dihapus karena masih digunakan oleh <strong>$count proyek</strong>. Pindahkan proyek tersebut ke kategori lain terlebih dahulu.");
            } else {
                $del = $pdo->prepare("DELETE FROM project_categories WHERE id = ?");
                $del->execute([$id]);
                set_flash('success', 'Kategori berhasil dihapus.');
            }
        }
        header('Location: categories.php');
        exit;
    }

    // ── REORDER (sort_order naik/turun) ─────────────────────
    if ($action === 'move') {
        $id        = (int)($_POST['id'] ?? 0);
        $direction = $_POST['direction'] ?? '';
        if ($id > 0 && in_array($direction, ['up', 'down'])) {
            $op    = $direction === 'up' ? '<' : '>';
            $order = $direction === 'up' ? 'DESC' : 'ASC';
            $curr  = $pdo->prepare("SELECT sort_order FROM project_categories WHERE id = ?");
            $curr->execute([$id]);
            $currOrd = (int)$curr->fetchColumn();

            $neighbor = $pdo->prepare("SELECT id, sort_order FROM project_categories WHERE sort_order $op ? ORDER BY sort_order $order LIMIT 1");
            $neighbor->execute([$currOrd]);
            $nb = $neighbor->fetch();
            if ($nb) {
                $pdo->prepare("UPDATE project_categories SET sort_order = ? WHERE id = ?")->execute([$nb['sort_order'], $id]);
                $pdo->prepare("UPDATE project_categories SET sort_order = ? WHERE id = ?")->execute([$currOrd, $nb['id']]);
            }
        }
        header('Location: categories.php');
        exit;
    }
}

// ── Load semua kategori + jumlah proyek per kategori ────
$categories = $pdo->query("
    SELECT pc.*,
           COUNT(p.id) AS project_count
    FROM project_categories pc
    LEFT JOIN projects p ON p.category_id = pc.id
    GROUP BY pc.id
    ORDER BY pc.sort_order ASC, pc.id ASC
")->fetchAll();

$totalCats = count($categories);

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- ══ PAGE ACTIONS ══ -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge rounded-pill px-3 py-2" style="background:var(--color-soft-olive);color:var(--color-dark);font-size:.8rem;">
            <i class="bi bi-tags me-1"></i> <?= $totalCats ?> Kategori
        </span>
    </div>
    <button class="btn btn-sm d-flex align-items-center gap-2"
            style="background:var(--color-primary);color:#fff;border-radius:var(--radius-button);"
            data-bs-toggle="modal" data-bs-target="#modalAdd">
        <i class="bi bi-plus-lg"></i> Tambah Kategori
    </button>
</div>

<!-- ══ TABLE ══ -->
<div class="card border-0 shadow-sm" style="border-radius:var(--radius-card);">
    <div class="card-body p-0">
        <?php if (empty($categories)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-tags fs-1 d-block mb-3 opacity-25"></i>
                <p class="mb-3">Belum ada kategori. Tambahkan kategori pertama Anda.</p>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
                    + Tambah Kategori
                </button>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:var(--color-background);">
                    <tr>
                        <th class="ps-4 py-3" style="width:48px;color:var(--color-muted);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">#</th>
                        <th class="py-3" style="color:var(--color-muted);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Nama Kategori</th>
                        <th class="py-3" style="color:var(--color-muted);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Slug URL</th>
                        <th class="py-3 text-center" style="color:var(--color-muted);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Proyek</th>
                        <th class="py-3 text-center" style="color:var(--color-muted);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Urutan</th>
                        <th class="py-3 text-end pe-4" style="color:var(--color-muted);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $i => $cat): ?>
                    <tr>
                        <td class="ps-4 text-muted" style="font-size:.85rem;"><?= $i + 1 ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-2"
                                      style="width:34px;height:34px;background:var(--color-soft-olive);color:var(--color-primary);font-size:.95rem;">
                                    <i class="bi bi-tag-fill"></i>
                                </span>
                                <span class="fw-semibold" style="font-size:.9rem;"><?= e($cat['category_name']) ?></span>
                            </div>
                        </td>
                        <td>
                            <code style="background:var(--color-background);padding:3px 8px;border-radius:6px;font-size:.8rem;color:var(--color-muted);">
                                <?= e($cat['slug']) ?>
                            </code>
                        </td>
                        <td class="text-center">
                            <?php if ($cat['project_count'] > 0): ?>
                                <span class="badge rounded-pill px-3"
                                      style="background:var(--color-soft-olive);color:var(--color-dark);font-weight:600;">
                                    <?= $cat['project_count'] ?> proyek
                                </span>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:.8rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <form method="POST" action="categories.php">
                                    <input type="hidden" name="action" value="move">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="btn btn-xs btn-light border" style="width:28px;height:28px;padding:0;line-height:1;"
                                            <?= $i === 0 ? 'disabled' : '' ?> title="Pindah ke atas">
                                        <i class="bi bi-chevron-up" style="font-size:.7rem;"></i>
                                    </button>
                                </form>
                                <form method="POST" action="categories.php">
                                    <input type="hidden" name="action" value="move">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="btn btn-xs btn-light border" style="width:28px;height:28px;padding:0;line-height:1;"
                                            <?= $i === $totalCats - 1 ? 'disabled' : '' ?> title="Pindah ke bawah">
                                        <i class="bi bi-chevron-down" style="font-size:.7rem;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <!-- Edit -->
                                <button class="btn btn-sm btn-outline-primary"
                                        style="border-radius:8px;font-size:.8rem;"
                                        onclick="openEdit(<?= $cat['id'] ?>, <?= json_encode($cat['category_name']) ?>, <?= json_encode($cat['slug']) ?>)">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </button>
                                <!-- Hapus -->
                                <form method="POST" action="categories.php"
                                      onsubmit="return confirmDelete(this, <?= json_encode($cat['category_name']) ?>, <?= (int)$cat['project_count'] ?>)">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            style="border-radius:8px;font-size:.8rem;">
                                        <i class="bi bi-trash me-1"></i>Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══ INFO CARD ══ -->
<div class="mt-4 p-3 rounded-3 d-flex align-items-start gap-3"
     style="background:#F0F4E8;border:1px solid #DCE3CB;">
    <i class="bi bi-info-circle-fill mt-1" style="color:var(--color-primary);font-size:1rem;flex-shrink:0;"></i>
    <div style="font-size:.85rem;color:var(--color-muted);">
        <strong style="color:var(--color-dark);">Tentang Slug URL:</strong>
        Slug di-<em>generate</em> otomatis dari nama kategori dan digunakan sebagai filter di halaman publik.
        Kategori yang masih memiliki proyek <strong>tidak dapat dihapus</strong> — pindahkan proyeknya terlebih dahulu.
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL TAMBAH
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalAdd" tabindex="-1" aria-labelledby="modalAddLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:16px;">
            <form method="POST" action="categories.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalAddLabel">Tambah Kategori Baru</h5>
                        <p class="text-muted small mb-0">Slug akan di-generate otomatis dari nama</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <label class="form-label small fw-semibold text-muted text-uppercase" style="letter-spacing:.4px;font-size:.75rem;">
                        Nama Kategori <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="addCatName" name="category_name" class="form-control"
                           style="border-radius:10px;" placeholder="cth: Mobile App, Blockchain, AR/VR"
                           maxlength="100" required autofocus>
                    <div class="mt-2">
                        <span class="text-muted small">Slug preview: </span>
                        <code id="addSlugPreview" style="font-size:.8rem;color:var(--color-primary);">—</code>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white px-4"
                            style="background:var(--color-primary);border-radius:10px;">
                        <i class="bi bi-plus-lg me-1"></i>Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL EDIT
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:16px;">
            <form method="POST" action="categories.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalEditLabel">Edit Kategori</h5>
                        <p class="text-muted small mb-0">Slug akan di-regenerate otomatis</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <label class="form-label small fw-semibold text-muted text-uppercase" style="letter-spacing:.4px;font-size:.75rem;">
                        Nama Kategori <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="editCatName" name="category_name" class="form-control"
                           style="border-radius:10px;" maxlength="100" required>
                    <div class="mt-2">
                        <span class="text-muted small">Slug saat ini: </span>
                        <code id="editSlugCurrent" style="font-size:.8rem;color:var(--color-muted);">—</code>
                    </div>
                    <div class="mt-1">
                        <span class="text-muted small">Slug baru: </span>
                        <code id="editSlugPreview" style="font-size:.8rem;color:var(--color-primary);">—</code>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius:10px;">
                        <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
// Generate slug dari nama (client-side preview)
function toSlug(text) {
    return text.toLowerCase()
               .replace(/[^a-z0-9\s-]/g, '')
               .trim()
               .replace(/[\s]+/g, '-')
               .replace(/-+/g, '-')
               .replace(/^-|-$/g, '')
               || '—';
}

// Live slug preview — modal Tambah
document.getElementById('addCatName').addEventListener('input', function () {
    document.getElementById('addSlugPreview').textContent = toSlug(this.value) || '—';
});

// Live slug preview — modal Edit
document.getElementById('editCatName').addEventListener('input', function () {
    document.getElementById('editSlugPreview').textContent = toSlug(this.value) || '—';
});

// Buka modal Edit dan isi data
function openEdit(id, name, slug) {
    document.getElementById('editId').value = id;
    document.getElementById('editCatName').value = name;
    document.getElementById('editSlugCurrent').textContent = slug;
    document.getElementById('editSlugPreview').textContent = toSlug(name);
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}

// Konfirmasi hapus
function confirmDelete(form, name, count) {
    if (count > 0) {
        alert('⚠️ Kategori "' + name + '" masih digunakan oleh ' + count + ' proyek.\nPindahkan proyek tersebut ke kategori lain terlebih dahulu.');
        return false;
    }
    return confirm('Hapus kategori "' + name + '"?\nTindakan ini tidak dapat dibatalkan.');
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>

