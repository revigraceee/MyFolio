<?php
// admin/profile.php
require_once __DIR__ . '/includes/admin_init.php';

// Handle Profile Update BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: profile.php');
        exit;
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $aboutMe  = trim($_POST['about_me'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $currentAvatar = $_POST['current_avatar'] ?? '';
    $currentCv = $_POST['current_cv_path'] ?? '';

    // Handle Avatar Upload
    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
        $uploadedAvatar = handle_file_upload($_FILES['avatar_file'], 'profile', ['jpg', 'jpeg', 'png', 'webp', 'svg']);
        if ($uploadedAvatar) {
            $currentAvatar = $uploadedAvatar;
        }
    }

    // Handle CV Upload
    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
        $uploadedCv = handle_file_upload($_FILES['cv_file'], 'cv', ['pdf', 'doc', 'docx']);
        if ($uploadedCv) {
            $currentCv = $uploadedCv;
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE profile SET full_name = ?, headline = ?, about_me = ?, email = ?, phone = ?, location = ?, avatar = ?, cv_path = ? WHERE id = 1");
        $stmt->execute([$fullName, $headline, $aboutMe, $email, $phone, $location, $currentAvatar, $currentCv]);
        set_flash('success', 'Profil berhasil diperbarui!');
    } catch (PDOException $e) {
        set_flash('danger', 'Gagal memperbarui profil: ' . $e->getMessage());
    }

    header('Location: profile.php');
    exit;
}

// Fetch Current Profile
$profile = $pdo->query("SELECT * FROM profile LIMIT 1")->fetch() ?: [];
$avatarPreview = resolve_img($profile['avatar'] ?? null, 'profile', '../assets/img/avatar-default.png', '../');

// Page Header metadata
$currentPage = 'profile';
$pageTitle = 'Manage Profile';
$pageSubtitle = 'Kelola identitas diri, headline, deskripsi, foto avatar, dan berkas CV';

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-card">
            <h5 class="fw-bold mb-4 text-dark">Informasi Profil Pemilik</h5>

            <form method="POST" action="profile.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="current_avatar" value="<?= e($profile['avatar'] ?? '') ?>">
                <input type="hidden" name="current_cv_path" value="<?= e($profile['cv_path'] ?? '') ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Nama Lengkap & Gelar</label>
                        <input type="text" name="full_name" class="form-control form-folio" value="<?= e($profile['full_name'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Headline Profesional</label>
                        <input type="text" name="headline" class="form-control form-folio" value="<?= e($profile['headline'] ?? '') ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Deskripsi Diri (About Me)</label>
                        <textarea name="about_me" rows="4" class="form-control form-folio" required><?= e($profile['about_me'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Alamat Email Publik</label>
                        <input type="email" name="email" class="form-control form-folio" value="<?= e($profile['email'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Nomor WhatsApp / Telepon</label>
                        <input type="text" name="phone" class="form-control form-folio" value="<?= e($profile['phone'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Domisili / Lokasi</label>
                        <input type="text" name="location" class="form-control form-folio" value="<?= e($profile['location'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Unggah File CV Baru (.pdf / .docx)</label>
                        <input type="file" name="cv_file" class="form-control form-folio" accept=".pdf,.doc,.docx">
                        <?php if (!empty($profile['cv_path']) && $profile['cv_path'] !== '#'): ?>
                            <div class="small text-muted mt-1">CV saat ini: <code><?= e($profile['cv_path']) ?></code></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Unggah Foto Profil (Avatar)</label>
                        <input type="file" name="avatar_file" class="form-control form-folio" accept="image/*">
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-admin-primary px-4 py-2.5">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-card text-center">
            <h6 class="fw-bold text-dark mb-3">Foto Profil Saat Ini</h6>
            <div class="rounded-4 overflow-hidden border p-2 bg-light mb-3 d-inline-block">
                <img src="<?= $avatarPreview ?>" alt="Avatar" class="rounded-3" style="width: 180px; height: 220px; object-fit: cover;">
            </div>
            <div class="fw-bold text-dark"><?= e($profile['full_name'] ?? '') ?></div>
            <div class="text-muted small"><?= e($profile['headline'] ?? '') ?></div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
