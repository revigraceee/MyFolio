<?php
// admin/login.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$logged_out = isset($_GET['logged_out']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Harap isi username dan password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            set_flash('success', 'Selamat datang di Admin Dashboard MyFolio!');
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah. Silakan coba kembali.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator — MyFolio CMS</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background-color: var(--color-background);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            width: 100%;
            max-width: 420px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="login-box mx-auto">
        <div class="text-center mb-4">
            <span class="p-2.5 rounded-3 bg-primary-olive text-white fs-4 d-inline-block mb-2">
                <i class="bi bi-person-workspace"></i>
            </span>
            <h2 class="fw-bold font-heading text-dark mb-1">My<span class="text-primary-olive">Folio</span> CMS</h2>
            <p class="text-muted small">Panel Administrasi Portofolio Bisnis Digital</p>
        </div>

        <div class="folio-card p-4 p-md-5">
            <?php if ($logged_out): ?>
                <div class="alert alert-info small d-flex align-items-center mb-4">
                    <i class="bi bi-check-circle me-2"></i> Anda telah berhasil keluar (logout).
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger small d-flex align-items-center mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold">Username Admin</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control form-folio border-start-0 ps-0" placeholder="admin" required autofocus value="<?= isset($username) ? e($username) : '' ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label text-muted small fw-semibold mb-0">Password</label>
                        <span class="text-muted small" style="font-size: 0.75rem;">Default: admin123</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="loginPass" class="form-control form-folio border-start-0 ps-0" placeholder="••••••••" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass()">
                            <i class="bi bi-eye" id="passEye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-olive w-100 py-2.5 justify-content-center mb-3">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Masuk ke Dashboard</span>
                </button>

                <div class="text-center">
                    <a href="../index.php" class="text-muted small text-decoration-none hover-olive">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Website
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePass() {
    const input = document.getElementById('loginPass');
    const eye = document.getElementById('passEye');
    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.remove('bi-eye');
        eye.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        eye.classList.remove('bi-eye-slash');
        eye.classList.add('bi-eye');
    }
}
</script>
</body>
</html>
