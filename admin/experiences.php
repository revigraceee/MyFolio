<?php
// admin/experiences.php
require_once __DIR__ . '/includes/admin_init.php';

// Handle POST BEFORE header output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: experiences.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ADD EXPERIENCE
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
    }

    // EDIT EXPERIENCE
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
    }

    // DELETE EXPERIENCE
    if ($action === 'delete') {
        $id = (int)($_POST['experience_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM experiences WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Pengalaman berhasil dihapus.');
        }
    }

    header('Location: experiences.php');
    exit;
}

$experiences = $pdo->query("SELECT * FROM experiences ORDER BY sort_order ASC, period_start DESC")->fetchAll();

$currentPage = 'experiences';
$pageTitle = 'Manage Experience';
$pageSubtitle = 'Kelola riwayat kerja magang, kepengurusan organisasi, kepanitiaan, dan lomba';

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0 text-dark">Daftar Pengalaman (<?= count($experiences) ?>)</h5>
    </div>
    <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addExpModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Pengalaman Baru
    </button>
</div>

<div class="admin-card">
    <?php if (empty($experiences)): ?>
        <div class="text-center py-5 text-muted">Belum ada data pengalaman.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th>Tipe</th>
                        <th>Posisi / Peran</th>
                        <th>Organisasi / Institusi</th>
                        <th>Periode</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($experiences as $ex): 
                        $pStr = format_period($ex['period_start'], $ex['period_end'], $ex['is_current']);
                    ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-muted border"><?= e($ex['type']) ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($ex['title']) ?></div>
                                <div class="small text-muted text-truncate" style="max-width: 280px;"><?= e($ex['description']) ?></div>
                            </td>
                            <td class="text-dark fw-medium"><?= e($ex['organization']) ?></td>
                            <td class="small text-muted"><?= e($pStr) ?></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm py-1 px-2.5 me-1"
                                    onclick="openEditExp(<?= htmlspecialchars(json_encode($ex), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="experiences.php" class="d-inline" onsubmit="return confirm('Hapus riwayat pengalaman ini?');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="experience_id" value="<?= $ex['id'] ?>">
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

<!-- Modal Add Experience -->
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
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Kategori Pengalaman *</label>
                            <select name="type" class="form-select form-folio" required>
                                <option value="Work">Work Experience</option>
                                <option value="Organization">Organization Experience</option>
                                <option value="Committee">Committee Experience</option>
                                <option value="Competition">Competition & Events</option>
                                <option value="Volunteer">Volunteer / Community</option>
                            </select>
                        </div>
                        <div class="col-md-8">
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
                            <textarea name="description" rows="3" class="form-control form-folio" required></textarea>
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

<!-- Modal Edit Experience -->
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
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Kategori *</label>
                            <select name="type" id="editExpType" class="form-select form-folio" required>
                                <option value="Work">Work Experience</option>
                                <option value="Organization">Organization Experience</option>
                                <option value="Committee">Committee Experience</option>
                                <option value="Competition">Competition & Events</option>
                                <option value="Volunteer">Volunteer / Community</option>
                            </select>
                        </div>
                        <div class="col-md-8">
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

<script>
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
</script>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
