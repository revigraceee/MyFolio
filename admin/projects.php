<?php
// admin/projects.php
require_once __DIR__ . '/includes/admin_init.php';

// Handle Actions (POST) BEFORE header output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: projects.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ADD PROJECT
    if ($action === 'add') {
        $title       = trim($_POST['title'] ?? '');
        $slug        = trim($_POST['slug'] ?? '') ?: generate_slug($title);
        $category    = trim($_POST['category'] ?? 'Business Planning');
        $shortDesc   = trim($_POST['short_description'] ?? '');
        $desc        = trim($_POST['description'] ?? '');
        $role        = trim($_POST['role'] ?? '');
        $process     = trim($_POST['process'] ?? '');
        $result      = trim($_POST['result'] ?? '');
        $tools       = trim($_POST['tools'] ?? '');
        $projUrl     = trim($_POST['project_url'] ?? '');
        $repoUrl     = trim($_POST['repository_url'] ?? '');
        $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;
        $status      = $_POST['status'] === 'draft' ? 'draft' : 'published';
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);
        $thumbnail   = 'placeholder.png';

        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
            $up = handle_file_upload($_FILES['thumbnail_file'], 'projects');
            if ($up) $thumbnail = $up;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO projects (title, slug, category, short_description, description, role, process, result, tools, thumbnail, project_url, repository_url, is_featured, status, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $slug, $category, $shortDesc, $desc, $role, $process, $result, $tools, $thumbnail, $projUrl, $repoUrl, $isFeatured, $status, $sortOrder]);
            set_flash('success', 'Proyek baru berhasil ditambahkan!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal menambah proyek: ' . $e->getMessage());
        }
    }

    // EDIT PROJECT
    if ($action === 'edit') {
        $id          = (int)($_POST['project_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $slug        = trim($_POST['slug'] ?? '') ?: generate_slug($title);
        $category    = trim($_POST['category'] ?? 'Business Planning');
        $shortDesc   = trim($_POST['short_description'] ?? '');
        $desc        = trim($_POST['description'] ?? '');
        $role        = trim($_POST['role'] ?? '');
        $process     = trim($_POST['process'] ?? '');
        $result      = trim($_POST['result'] ?? '');
        $tools       = trim($_POST['tools'] ?? '');
        $projUrl     = trim($_POST['project_url'] ?? '');
        $repoUrl     = trim($_POST['repository_url'] ?? '');
        $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;
        $status      = $_POST['status'] === 'draft' ? 'draft' : 'published';
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);
        $thumbnail   = $_POST['current_thumbnail'] ?? 'placeholder.png';

        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
            $up = handle_file_upload($_FILES['thumbnail_file'], 'projects');
            if ($up) $thumbnail = $up;
        }

        try {
            $stmt = $pdo->prepare("UPDATE projects SET title = ?, slug = ?, category = ?, short_description = ?, description = ?, role = ?, process = ?, result = ?, tools = ?, thumbnail = ?, project_url = ?, repository_url = ?, is_featured = ?, status = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$title, $slug, $category, $shortDesc, $desc, $role, $process, $result, $tools, $thumbnail, $projUrl, $repoUrl, $isFeatured, $status, $sortOrder, $id]);
            set_flash('success', 'Data proyek berhasil diperbarui!');
        } catch (PDOException $e) {
            set_flash('danger', 'Gagal memperbarui proyek: ' . $e->getMessage());
        }
    }

    // DELETE PROJECT
    if ($action === 'delete') {
        $id = (int)($_POST['project_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Proyek berhasil dihapus.');
        }
    }

    header('Location: projects.php');
    exit;
}

// Fetch All Projects
$projects = $pdo->query("SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC")->fetchAll();

$currentPage = 'projects';
$pageTitle = 'Manage Projects';
$pageSubtitle = 'Kelola portofolio karya, detail studi kasus, status draft/published, dan unggulan';

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0 text-dark">Daftar Portofolio Proyek (<?= count($projects) ?>)</h5>
    </div>
    <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Proyek Baru
    </button>
</div>

<div class="admin-card">
    <?php if (empty($projects)): ?>
        <div class="text-center py-5 text-muted">Belum ada proyek yang ditambahkan.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">Thumbnail</th>
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
                    ?>
                        <tr>
                            <td>
                                <img src="<?= e($thumbPath) ?>" alt="" class="rounded-2" style="width: 70px; height: 48px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($pj['title']) ?></div>
                                <span class="badge bg-light text-muted border" style="font-size: 0.72rem;"><?= e($pj['category']) ?></span>
                            </td>
                            <td>
                                <div class="small text-dark fw-medium"><?= e($pj['role'] ?: '—') ?></div>
                                <div class="small text-muted text-truncate" style="max-width: 220px;"><?= e($pj['tools'] ?: '—') ?></div>
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
                                    <span class="badge bg-warning-subtle text-warning-emphasis fw-bold"><i class="bi bi-star-fill me-1"></i>Yes</span>
                                <?php else: ?>
                                    <span class="text-muted small">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm py-1 px-2.5 me-1"
                                    onclick="openEditProject(<?= htmlspecialchars(json_encode($pj), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="projects.php" class="d-inline" onsubmit="return confirm('Hapus proyek ini secara permanen?');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="project_id" value="<?= $pj['id'] ?>">
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

<!-- Modal Add Project -->
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
                        <div class="col-md-8">
                            <label class="form-label text-muted small fw-semibold">Judul Proyek *</label>
                            <input type="text" name="title" class="form-control form-folio" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Kategori *</label>
                            <select name="category" class="form-select form-folio" required>
                                <option value="Business Planning">Business Planning</option>
                                <option value="Digital Marketing">Digital Marketing</option>
                                <option value="Website & Application">Website & Application</option>
                                <option value="UI/UX Design">UI/UX Design</option>
                                <option value="Research & Community Projects">Research & Community Projects</option>
                                <option value="Other Projects">Other Projects</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">Ringkasan Singkat (Short Description) *</label>
                            <textarea name="short_description" rows="2" class="form-control form-folio" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">Deskripsi Lengkap / Latar Belakang Masalah *</label>
                            <textarea name="description" rows="3" class="form-control form-folio" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Peran / Tanggung Jawab</label>
                            <input type="text" name="role" class="form-control form-folio" placeholder="cth: Lead Business Strategist">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tools yang Digunakan</label>
                            <input type="text" name="tools" class="form-control form-folio" placeholder="Figma, Meta Ads, Excel, Python">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Proses Pengerjaan</label>
                            <textarea name="process" rows="2" class="form-control form-folio"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Hasil & Dampak</label>
                            <textarea name="result" rows="2" class="form-control form-folio"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tautan Proyek / Demo</label>
                            <input type="text" name="project_url" class="form-control form-folio" placeholder="https://...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tautan Repositori / File</label>
                            <input type="text" name="repository_url" class="form-control form-folio" placeholder="https://github.com/...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Unggah Thumbnail</label>
                            <input type="file" name="thumbnail_file" class="form-control form-folio" accept="image/*">
                        </div>
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
                        <div class="col-12">
                            <div class="form-check mt-2">
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

<!-- Modal Edit Project -->
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
                        <div class="col-md-8">
                            <label class="form-label text-muted small fw-semibold">Judul Proyek *</label>
                            <input type="text" name="title" id="editTitle" class="form-control form-folio" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Kategori *</label>
                            <select name="category" id="editCategory" class="form-select form-folio" required>
                                <option value="Business Planning">Business Planning</option>
                                <option value="Digital Marketing">Digital Marketing</option>
                                <option value="Website & Application">Website & Application</option>
                                <option value="UI/UX Design">UI/UX Design</option>
                                <option value="Research & Community Projects">Research & Community Projects</option>
                                <option value="Other Projects">Other Projects</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">Ringkasan Singkat *</label>
                            <textarea name="short_description" id="editShortDesc" rows="2" class="form-control form-folio" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">Deskripsi Lengkap *</label>
                            <textarea name="description" id="editDesc" rows="3" class="form-control form-folio" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Peran / Tanggung Jawab</label>
                            <input type="text" name="role" id="editRole" class="form-control form-folio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tools yang Digunakan</label>
                            <input type="text" name="tools" id="editTools" class="form-control form-folio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Proses Pengerjaan</label>
                            <textarea name="process" id="editProcess" rows="2" class="form-control form-folio"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Hasil & Dampak</label>
                            <textarea name="result" id="editResult" rows="2" class="form-control form-folio"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tautan Proyek / Demo</label>
                            <input type="text" name="project_url" id="editProjUrl" class="form-control form-folio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Tautan Repositori</label>
                            <input type="text" name="repository_url" id="editRepoUrl" class="form-control form-folio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Ganti Thumbnail</label>
                            <input type="file" name="thumbnail_file" class="form-control form-folio" accept="image/*">
                        </div>
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
                        <div class="col-12">
                            <div class="form-check mt-2">
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

<script>
function openEditProject(p) {
    document.getElementById('editProjId').value = p.id;
    document.getElementById('editTitle').value = p.title;
    document.getElementById('editCategory').value = p.category;
    document.getElementById('editShortDesc').value = p.short_description;
    document.getElementById('editDesc').value = p.description;
    document.getElementById('editRole').value = p.role || '';
    document.getElementById('editTools').value = p.tools || '';
    document.getElementById('editProcess').value = p.process || '';
    document.getElementById('editResult').value = p.result || '';
    document.getElementById('editProjUrl').value = p.project_url || '';
    document.getElementById('editRepoUrl').value = p.repository_url || '';
    document.getElementById('editCurrentThumb').value = p.thumbnail || 'placeholder.png';
    document.getElementById('editStatus').value = p.status;
    document.getElementById('editSortOrder').value = p.sort_order;
    document.getElementById('editFeatured').checked = (parseInt(p.is_featured) === 1);

    new bootstrap.Modal(document.getElementById('editProjectModal')).show();
}
</script>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
