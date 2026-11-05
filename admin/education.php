<?php
// admin/education.php
require_once __DIR__ . '/includes/admin_init.php';

// Handle POST BEFORE header output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: education.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ADD EDUCATION
    if ($action === 'add') {
        $inst      = trim($_POST['institution'] ?? '');
        $degree    = trim($_POST['degree'] ?? '');
        $major     = trim($_POST['major'] ?? '');
        $start     = !empty($_POST['period_start']) ? $_POST['period_start'] : null;
        $end       = !empty($_POST['period_end']) ? $_POST['period_end'] : null;
        $isCurrent = isset($_POST['is_current']) ? 1 : 0;
        $desc      = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($isCurrent) $end = null;

        try {
            $stmt = $pdo->prepare("INSERT INTO education (institution, degree, major, period_start, period_end, is_current, description, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$inst, $degree, $major, $start, $end, $isCurrent, $desc, $sortOrder]);
            set_flash('success', 'Riwayat pendidikan berhasil ditambahkan!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal menambah pendidikan: ' . $e->getMessage());
        }
    }

    // EDIT EDUCATION
    if ($action === 'edit') {
        $id        = (int)($_POST['education_id'] ?? 0);
        $inst      = trim($_POST['institution'] ?? '');
        $degree    = trim($_POST['degree'] ?? '');
        $major     = trim($_POST['major'] ?? '');
        $start     = !empty($_POST['period_start']) ? $_POST['period_start'] : null;
        $end       = !empty($_POST['period_end']) ? $_POST['period_end'] : null;
        $isCurrent = isset($_POST['is_current']) ? 1 : 0;
        $desc      = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($isCurrent) $end = null;

        try {
            $stmt = $pdo->prepare("UPDATE education SET institution = ?, degree = ?, major = ?, period_start = ?, period_end = ?, is_current = ?, description = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$inst, $degree, $major, $start, $end, $isCurrent, $desc, $sortOrder, $id]);
            set_flash('success', 'Data pendidikan berhasil diperbarui!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal memperbarui pendidikan: ' . $e->getMessage());
        }
    }

    // DELETE EDUCATION
    if ($action === 'delete') {
        $id = (int)($_POST['education_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM education WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Data pendidikan berhasil dihapus.');
        }
    }

    header('Location: education.php');
    exit;
}

$educations = $pdo->query("SELECT * FROM education ORDER BY sort_order ASC, period_start DESC")->fetchAll();

$currentPage = 'education';
$pageTitle = 'Manage Education';
$pageSubtitle = 'Kelola riwayat pendidikan formal dan program studi';

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0 text-dark">Daftar Pendidikan (<?= count($educations) ?>)</h5>
    </div>
    <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addEduModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Pendidikan Baru
    </button>
</div>

<div class="admin-card">
    <?php if (empty($educations)): ?>
        <div class="text-center py-5 text-muted">Belum ada data pendidikan.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th>Jenjang</th>
                        <th>Program Studi / Jurusan</th>
                        <th>Institusi / Universitas</th>
                        <th>Periode</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($educations as $ed): 
                        $pEdu = format_period($ed['period_start'], $ed['period_end'], $ed['is_current']);
                    ?>
                        <tr>
                            <td><span class="badge bg-light text-muted border"><?= e($ed['degree']) ?></span></td>
                            <td class="fw-bold text-dark"><?= e($ed['major']) ?></td>
                            <td class="text-dark fw-medium"><?= e($ed['institution']) ?></td>
                            <td class="small text-muted"><?= e($pEdu) ?></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm py-1 px-2.5 me-1"
                                    onclick="openEditEdu(<?= htmlspecialchars(json_encode($ed), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="education.php" class="d-inline" onsubmit="return confirm('Hapus data pendidikan ini?');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="education_id" value="<?= $ed['id'] ?>">
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

<!-- Modal Add Education -->
<div class="modal fade" id="addEduModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <h5 class="modal-title fw-bold font-heading text-dark">Tambah Pendidikan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="education.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Nama Institusi / Universitas *</label>
                            <input type="text" name="institution" class="form-control form-folio" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Jenjang Pendidikan *</label>
                            <input type="text" name="degree" class="form-control form-folio" placeholder="cth: Sarjana Bisnis Digital (S.Bns)" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-muted small fw-semibold">Program Studi / Jurusan *</label>
                            <input type="text" name="major" class="form-control form-folio" placeholder="cth: Bisnis Digital" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Urutan</label>
                            <input type="number" name="sort_order" class="form-control form-folio" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tanggal Mulai</label>
                            <input type="date" name="period_start" class="form-control form-folio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tanggal Selesai</label>
                            <input type="date" name="period_end" id="addEduEnd" class="form-control form-folio">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="is_current" id="addEduCurrent" value="1"
                                    onchange="document.getElementById('addEduEnd').disabled = this.checked;">
                                <label class="form-check-label text-muted small" for="addEduCurrent">Sedang ditempuh (Current)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">Deskripsi Tambahan (IPK, Fokus Minat, Prestasi)</label>
                            <textarea name="description" rows="3" class="form-control form-folio"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Simpan Pendidikan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Education -->
<div class="modal fade" id="editEduModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <h5 class="modal-title fw-bold font-heading text-dark">Edit Pendidikan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="education.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="education_id" id="editEduId">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Nama Institusi *</label>
                            <input type="text" name="institution" id="editEduInst" class="form-control form-folio" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Jenjang Pendidikan *</label>
                            <input type="text" name="degree" id="editEduDegree" class="form-control form-folio" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-muted small fw-semibold">Program Studi *</label>
                            <input type="text" name="major" id="editEduMajor" class="form-control form-folio" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Urutan</label>
                            <input type="number" name="sort_order" id="editEduSort" class="form-control form-folio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tanggal Mulai</label>
                            <input type="date" name="period_start" id="editEduStart" class="form-control form-folio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tanggal Selesai</label>
                            <input type="date" name="period_end" id="editEduEnd" class="form-control form-folio">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="is_current" id="editEduCurrent" value="1"
                                    onchange="document.getElementById('editEduEnd').disabled = this.checked;">
                                <label class="form-check-label text-muted small" for="editEduCurrent">Sedang ditempuh (Current)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">Deskripsi</label>
                            <textarea name="description" id="editEduDesc" rows="3" class="form-control form-folio"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Perbarui Pendidikan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditEdu(ed) {
    document.getElementById('editEduId').value = ed.id;
    document.getElementById('editEduInst').value = ed.institution;
    document.getElementById('editEduDegree').value = ed.degree;
    document.getElementById('editEduMajor').value = ed.major;
    document.getElementById('editEduStart').value = ed.period_start || '';
    document.getElementById('editEduEnd').value = ed.period_end || '';
    document.getElementById('editEduCurrent').checked = (parseInt(ed.is_current) === 1);
    document.getElementById('editEduEnd').disabled = (parseInt(ed.is_current) === 1);
    document.getElementById('editEduDesc').value = ed.description || '';
    document.getElementById('editEduSort').value = ed.sort_order;

    new bootstrap.Modal(document.getElementById('editEduModal')).show();
}
</script>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
