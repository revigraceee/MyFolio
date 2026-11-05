<?php
// admin/settings.php
require_once __DIR__ . '/includes/admin_init.php';

// Handle Password & Username Update BEFORE header output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: settings.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // CHANGE USERNAME
    if ($action === 'update_username') {
        $newUsername = trim($_POST['username'] ?? '');
        if (empty($newUsername)) {
            set_flash('danger', 'Username tidak boleh kosong.');
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?");
                $stmt->execute([$newUsername, $_SESSION['admin_id'] ?? 1]);
                $_SESSION['admin_username'] = $newUsername;
                set_flash('success', 'Username admin berhasil diperbarui menjadi ' . e($newUsername));
            } catch (PDOException $e) {
                set_flash('danger', 'Gagal memperbarui username: Username mungkin sudah digunakan.');
            }
        }
    }

    // CHANGE PASSWORD
    if ($action === 'change_password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
            set_flash('danger', 'Harap isi semua kolom kata sandi.');
        } elseif ($newPass !== $confirmPass) {
            set_flash('danger', 'Konfirmasi kata sandi baru tidak cocok.');
        } elseif (strlen($newPass) < 6) {
            set_flash('danger', 'Kata sandi baru minimal harus 6 karakter.');
        } else {
            $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ? LIMIT 1");
            $stmt->execute([$_SESSION['admin_id'] ?? 1]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($currentPass, $admin['password_hash'])) {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                $update->execute([$newHash, $_SESSION['admin_id'] ?? 1]);
                set_flash('success', 'Kata sandi admin berhasil diperbarui!');
            } else {
                set_flash('danger', 'Kata sandi lama yang Anda masukkan tidak sesuai.');
            }
        }
    }

    header('Location: settings.php');
    exit;
}

// Fetch Admin Info
$adminInfo = $pdo->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
$adminInfo->execute([$_SESSION['admin_id'] ?? 1]);
$admin = $adminInfo->fetch();

$currentPage = 'settings';
$pageTitle = 'Settings & Security';
$pageSubtitle = 'Pengaturan akun administrator, keamanan kata sandi, dan username';

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="row g-4">
    <!-- Change Username -->
    <div class="col-lg-6">
        <div class="admin-card h-100">
            <h5 class="fw-bold mb-3 text-dark">Ubah Username Admin</h5>
            <p class="text-muted small mb-4">Gunakan username yang mudah diingat untuk login ke panel CMS.</p>

            <form method="POST" action="settings.php">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="update_username">

                <div class="mb-4">
                    <label class="form-label text-muted small fw-semibold">Username Baru</label>
                    <input type="text" name="username" class="form-control form-folio" value="<?= e($admin['username'] ?? 'admin') ?>" required>
                </div>

                <button type="submit" class="btn btn-admin-primary">
                    <i class="bi bi-person-check me-1"></i> Simpan Username
                </button>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div class="col-lg-6">
        <div class="admin-card h-100">
            <h5 class="fw-bold mb-3 text-dark">Ganti Kata Sandi (Password)</h5>
            <p class="text-muted small mb-4">Pastikan menggunakan kombinasi password yang kuat dan aman.</p>

            <form method="POST" action="settings.php">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="change_password">

                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold">Kata Sandi Saat Ini (Lama)</label>
                    <input type="password" name="current_password" class="form-control form-folio" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold">Kata Sandi Baru</label>
                    <input type="password" name="new_password" class="form-control form-folio" minlength="6" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small fw-semibold">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="confirm_password" class="form-control form-folio" minlength="6" required>
                </div>

                <button type="submit" class="btn btn-admin-primary">
                    <i class="bi bi-shield-lock me-1"></i> Perbarui Kata Sandi
                </button>
            </form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
