<?php
// admin/social-links.php
require_once __DIR__ . '/includes/admin_init.php';

// Handle POST BEFORE header output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: social-links.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ADD LINK
    if ($action === 'add') {
        $platform  = trim($_POST['platform'] ?? '');
        $url       = trim($_POST['url'] ?? '');
        $isActive  = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        try {
            $stmt = $pdo->prepare("INSERT INTO social_links (platform, url, is_active, sort_order, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$platform, $url, $isActive, $sortOrder]);
            set_flash('success', 'Tautan media sosial berhasil ditambahkan!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal menambah tautan: ' . $e->getMessage());
        }
    }

    // EDIT LINK
    if ($action === 'edit') {
        $id        = (int)($_POST['link_id'] ?? 0);
        $platform  = trim($_POST['platform'] ?? '');
        $url       = trim($_POST['url'] ?? '');
        $isActive  = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        try {
            $stmt = $pdo->prepare("UPDATE social_links SET platform = ?, url = ?, is_active = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$platform, $url, $isActive, $sortOrder, $id]);
            set_flash('success', 'Tautan sosial berhasil diperbarui!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal memperbarui tautan: ' . $e->getMessage());
        }
    }

    // DELETE LINK
    if ($action === 'delete') {
        $id = (int)($_POST['link_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM social_links WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Tautan sosial berhasil dihapus.');
        }
    }

    header('Location: social-links.php');
    exit;
}

$socials = $pdo->query("SELECT * FROM social_links ORDER BY sort_order ASC, id ASC")->fetchAll();

$currentPage = 'social-links';
$pageTitle = 'Social Links';
$pageSubtitle = 'Kelola tautan media sosial profesional dan urutan penayangannya';

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0 text-dark">Daftar Tautan Media Sosial (<?= count($socials) ?>)</h5>
    </div>
    <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addSocialModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Tautan Baru
    </button>
</div>

<div class="admin-card">
    <?php if (empty($socials)): ?>
        <div class="text-center py-5 text-muted">Belum ada tautan media sosial.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th>Platform</th>
                        <th>URL Tautan</th>
                        <th>Status Tampil</th>
                        <th>Urutan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($socials as $soc): ?>
                        <tr>
                            <td class="fw-bold text-dark">
                                <i class="bi bi-link-45deg text-primary-olive me-1"></i>
                                <?= e($soc['platform']) ?>
                            </td>
                            <td>
                                <a href="<?= e($soc['url']) ?>" target="_blank" class="small text-muted text-decoration-none">
                                    <?= e($soc['url']) ?> <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </a>
                            </td>
                            <td>
                                <?php if ($soc['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$soc['sort_order'] ?></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm py-1 px-2.5 me-1"
                                    onclick="openEditSocial(<?= htmlspecialchars(json_encode($soc), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="social-links.php" class="d-inline" onsubmit="return confirm('Hapus tautan ini?');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="link_id" value="<?= $soc['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2.5">
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

<!-- Modal Add Social -->
<div class="modal fade" id="addSocialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <h5 class="modal-title fw-bold font-heading text-dark">Tambah Tautan Sosial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="social-links.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Nama Platform *</label>
                        <input type="text" name="platform" class="form-control form-folio" placeholder="LinkedIn, Instagram, GitHub, dll." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">URL Profil Lengkap *</label>
                        <input type="url" name="url" class="form-control form-folio" placeholder="https://..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Urutan (Sort Order)</label>
                        <input type="number" name="sort_order" class="form-control form-folio" value="0">
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="addActive" value="1" checked>
                        <label class="form-check-label text-dark small fw-medium" for="addActive">
                            Tampilkan di Halaman Portofolio
                        </label>
                    </div>
                </div>
                <div class="modal-footer p-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Simpan Tautan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Social -->
<div class="modal fade" id="editSocialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <h5 class="modal-title fw-bold font-heading text-dark">Edit Tautan Sosial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="social-links.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="link_id" id="editLinkId">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Nama Platform *</label>
                        <input type="text" name="platform" id="editPlatform" class="form-control form-folio" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">URL Profil *</label>
                        <input type="url" name="url" id="editUrl" class="form-control form-folio" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Urutan</label>
                        <input type="number" name="sort_order" id="editSort" class="form-control form-folio">
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="editActive" value="1">
                        <label class="form-check-label text-dark small fw-medium" for="editActive">
                            Tampilkan di Halaman Portofolio
                        </label>
                    </div>
                </div>
                <div class="modal-footer p-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Perbarui Tautan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditSocial(soc) {
    document.getElementById('editLinkId').value = soc.id;
    document.getElementById('editPlatform').value = soc.platform;
    document.getElementById('editUrl').value = soc.url;
    document.getElementById('editSort').value = soc.sort_order;
    document.getElementById('editActive').checked = (parseInt(soc.is_active) === 1);

    new bootstrap.Modal(document.getElementById('editSocialModal')).show();
}
</script>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
