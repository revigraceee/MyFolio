<?php
// admin/skills.php
require_once __DIR__ . '/includes/admin_init.php';

// Handle POST BEFORE header output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: skills.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ADD SKILL
    if ($action === 'add') {
        $name        = trim($_POST['skill_name'] ?? '');
        $category    = trim($_POST['category'] ?? 'Digital Tools');
        $proficiency = trim($_POST['proficiency'] ?? 'Intermediate');
        $desc        = trim($_POST['description'] ?? '');
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);

        try {
            $stmt = $pdo->prepare("INSERT INTO skills (skill_name, category, proficiency, description, sort_order, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $category, $proficiency, $desc, $sortOrder]);
            set_flash('success', 'Keahlian baru berhasil ditambahkan!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal menambah keahlian: ' . $e->getMessage());
        }
    }

    // EDIT SKILL
    if ($action === 'edit') {
        $id          = (int)($_POST['skill_id'] ?? 0);
        $name        = trim($_POST['skill_name'] ?? '');
        $category    = trim($_POST['category'] ?? 'Digital Tools');
        $proficiency = trim($_POST['proficiency'] ?? 'Intermediate');
        $desc        = trim($_POST['description'] ?? '');
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);

        try {
            $stmt = $pdo->prepare("UPDATE skills SET skill_name = ?, category = ?, proficiency = ?, description = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $category, $proficiency, $desc, $sortOrder, $id]);
            set_flash('success', 'Data keahlian berhasil diperbarui!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal memperbarui keahlian: ' . $e->getMessage());
        }
    }

    // DELETE SKILL
    if ($action === 'delete') {
        $id = (int)($_POST['skill_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM skills WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Keahlian berhasil dihapus.');
        }
    }

    header('Location: skills.php');
    exit;
}

$skills = $pdo->query("SELECT * FROM skills ORDER BY category ASC, sort_order ASC")->fetchAll();

$currentPage = 'skills';
$pageTitle = 'Manage Skills';
$pageSubtitle = 'Kelola daftar keahlian, kategori kompetensi, dan tingkat penguasaan (proficiency)';

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0 text-dark">Daftar Keahlian (<?= count($skills) ?>)</h5>
    </div>
    <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addSkillModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Skill Baru
    </button>
</div>

<div class="admin-card">
    <?php if (empty($skills)): ?>
        <div class="text-center py-5 text-muted">Belum ada data skill yang ditambahkan.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th>Nama Skill</th>
                        <th>Kategori</th>
                        <th>Proficiency (Level)</th>
                        <th>Deskripsi</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($skills as $sk): 
                        $prof = $sk['proficiency'] ?? 'Intermediate';
                        $badgeClass = 'bg-secondary-subtle text-secondary';
                        if ($prof === 'Advanced') $badgeClass = 'bg-success-subtle text-success fw-bold';
                        elseif ($prof === 'Beginner') $badgeClass = 'bg-warning-subtle text-warning-emphasis';
                    ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= e($sk['skill_name']) ?></td>
                            <td><span class="badge bg-light text-muted border"><?= e($sk['category']) ?></span></td>
                            <td><span class="badge <?= $badgeClass ?> px-2.5 py-1 rounded-pill"><?= e($prof) ?></span></td>
                            <td class="small text-muted text-truncate" style="max-width: 250px;"><?= e($sk['description']) ?></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm py-1 px-2.5 me-1"
                                    onclick="openEditSkill(<?= htmlspecialchars(json_encode($sk), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="skills.php" class="d-inline" onsubmit="return confirm('Hapus skill ini?');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="skill_id" value="<?= $sk['id'] ?>">
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

<!-- Modal Add Skill -->
<div class="modal fade" id="addSkillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <h5 class="modal-title fw-bold font-heading text-dark">Tambah Skill Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="skills.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Nama Skill *</label>
                        <input type="text" name="skill_name" class="form-control form-folio" placeholder="cth: Meta Ads Manager" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Kategori *</label>
                        <select name="category" class="form-select form-folio" required>
                            <option value="Digital Tools">Digital Tools</option>
                            <option value="Business & Entrepreneurship">Business & Entrepreneurship</option>
                            <option value="Administration & Data">Administration & Data</option>
                            <option value="Communication">Communication</option>
                            <option value="Personal / Soft Skills">Personal / Soft Skills</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Tingkat Penguasaan (Proficiency) *</label>
                        <select name="proficiency" class="form-select form-folio" required>
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate" selected>Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Deskripsi / Contoh Penerapan</label>
                        <input type="text" name="description" class="form-control form-folio" placeholder="cth: Perencanaan kampanye dan optimasi ROAS">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Urutan (Sort Order)</label>
                        <input type="number" name="sort_order" class="form-control form-folio" value="0">
                    </div>
                </div>
                <div class="modal-footer p-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Simpan Skill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Skill -->
<div class="modal fade" id="editSkillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header p-4 border-bottom">
                <h5 class="modal-title fw-bold font-heading text-dark">Edit Skill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="skills.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="skill_id" id="editSkillId">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Nama Skill *</label>
                        <input type="text" name="skill_name" id="editSkillName" class="form-control form-folio" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Kategori *</label>
                        <select name="category" id="editSkillCategory" class="form-select form-folio" required>
                            <option value="Digital Tools">Digital Tools</option>
                            <option value="Business & Entrepreneurship">Business & Entrepreneurship</option>
                            <option value="Administration & Data">Administration & Data</option>
                            <option value="Communication">Communication</option>
                            <option value="Personal / Soft Skills">Personal / Soft Skills</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Tingkat Penguasaan *</label>
                        <select name="proficiency" id="editSkillProf" class="form-select form-folio" required>
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Deskripsi</label>
                        <input type="text" name="description" id="editSkillDesc" class="form-control form-folio">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Urutan</label>
                        <input type="number" name="sort_order" id="editSkillSort" class="form-control form-folio">
                    </div>
                </div>
                <div class="modal-footer p-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary">Perbarui Skill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditSkill(sk) {
    document.getElementById('editSkillId').value = sk.id;
    document.getElementById('editSkillName').value = sk.skill_name;
    document.getElementById('editSkillCategory').value = sk.category;
    document.getElementById('editSkillProf').value = sk.proficiency || 'Intermediate';
    document.getElementById('editSkillDesc').value = sk.description || '';
    document.getElementById('editSkillSort').value = sk.sort_order;

    new bootstrap.Modal(document.getElementById('editSkillModal')).show();
}
</script>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
