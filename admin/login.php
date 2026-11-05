<?php
// admin/login.php — Professional split-screen login
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error      = '';
$success    = '';
$logged_out = isset($_GET['logged_out']);
$username_val = '';

// Handle POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $username_val = $username;

    if (empty($username) || empty($password)) {
        $error = 'Harap isi username dan password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in']  = true;
            $_SESSION['admin_id']         = $admin['id'];
            $_SESSION['admin_username']   = $admin['username'];

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* ── Design Tokens ── */
        :root {
            --olive:        #5C6F2B;
            --olive-dark:   #25291F;
            --olive-hover:  #495822;
            --olive-soft:   #DCE3CB;
            --olive-muted:  #8A9A5B;
            --bg:           #F4F5F1;
            --white:        #FFFFFF;
            --muted:        #686D60;
            --border:       #D8DAD2;
            --danger:       #DC3545;
            --info:         #0D6EFD;
            --radius:       14px;
            --radius-btn:   10px;
            --transition:   0.25s ease;
        }

        /* ── Reset / Base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--olive-dark);
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            overflow: hidden;
        }

        /* ══════════════════════════════════════
           LEFT PANEL — Branding
        ══════════════════════════════════════ */
        .login-left {
            flex: 1;
            background: linear-gradient(145deg, var(--olive-dark) 0%, #3A4318 60%, var(--olive) 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 56px 52px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative circles */
        .login-left::before,
        .login-left::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            opacity: 0.08;
        }
        .login-left::before {
            width: 420px; height: 420px;
            background: var(--olive-soft);
            top: -120px; right: -100px;
        }
        .login-left::after {
            width: 280px; height: 280px;
            background: var(--olive-muted);
            bottom: -80px; left: -60px;
        }

        /* Extra decorative ring */
        .deco-ring {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(220, 227, 203, 0.12);
        }
        .deco-ring-1 { width: 320px; height: 320px; top: 50%; left: 50%; transform: translate(-50%, -50%); }
        .deco-ring-2 { width: 480px; height: 480px; top: 50%; left: 50%; transform: translate(-50%, -50%); }
        .deco-ring-3 { width: 640px; height: 640px; top: 50%; left: 50%; transform: translate(-50%, -50%); }

        .left-top { position: relative; z-index: 2; }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .brand-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,0.12);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            color: var(--olive-soft);
            border: 1px solid rgba(255,255,255,0.15);
        }
        .brand-name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.3rem;
            color: #FFFFFF;
            letter-spacing: -0.3px;
        }
        .brand-name span { color: var(--olive-soft); }

        .left-middle {
            position: relative;
            z-index: 2;
        }

        .left-tagline {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            line-height: 1.25;
            color: #FFFFFF;
            letter-spacing: -0.5px;
            margin-bottom: 16px;
        }
        .left-tagline span {
            color: var(--olive-soft);
        }

        .left-desc {
            font-size: 0.95rem;
            color: rgba(255,255,255,0.6);
            line-height: 1.65;
            max-width: 320px;
        }

        /* Illustration */
        .login-illustration {
            margin-top: 32px;
            margin-bottom: 28px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        .login-illustration svg {
            width: 100%;
            max-width: 290px;
            height: auto;
            filter: drop-shadow(0 16px 36px rgba(0,0,0,0.3));
            animation: floatImg 4s ease-in-out infinite;
        }
        @keyframes floatImg {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-8px); }
        }

                .feature-list {
            list-style: none;
            padding: 0;
            margin-top: 0;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .feature-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.875rem;
            color: rgba(255,255,255,0.75);
        }
        .feature-icon {
            width: 32px; height: 32px;
            background: rgba(255,255,255,0.08);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: var(--olive-soft);
            font-size: 0.95rem;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,0.08);
        }

        .left-bottom {
            position: relative;
            z-index: 2;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.3);
        }

        /* ══════════════════════════════════════
           RIGHT PANEL — Login Form
        ══════════════════════════════════════ */
        .login-right {
            width: 480px;
            flex-shrink: 0;
            background: var(--white);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px 52px;
            overflow-y: auto;
        }

        /* Header */
        .form-header {
            margin-bottom: 36px;
        }
        .form-header .badge-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--olive-soft);
            color: var(--olive-dark);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 5px 12px;
            border-radius: 20px;
            margin-bottom: 14px;
        }
        .form-header h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 1.75rem;
            color: var(--olive-dark);
            letter-spacing: -0.4px;
            margin-bottom: 6px;
        }
        .form-header p {
            font-size: 0.9rem;
            color: var(--muted);
        }

        /* Divider */
        .form-divider {
            height: 1px;
            background: var(--border);
            margin-bottom: 28px;
        }

        /* Labels */
        .form-label-custom {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--muted);
            letter-spacing: 0.3px;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        /* Inputs */
        .input-wrap {
            position: relative;
        }
        .input-wrap .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 1rem;
            pointer-events: none;
            transition: color var(--transition);
        }
        .input-custom {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            color: var(--olive-dark);
            background: var(--white);
            transition: border-color var(--transition), box-shadow var(--transition);
            outline: none;
        }
        .input-custom::placeholder { color: #BABDB5; }
        .input-custom:focus {
            border-color: var(--olive);
            box-shadow: 0 0 0 4px rgba(92, 111, 43, 0.1);
        }
        .input-custom:focus + .input-icon,
        .input-wrap:focus-within .input-icon {
            color: var(--olive);
        }

        /* Password toggle */
        .input-custom.has-toggle {
            padding-right: 48px;
        }
        .toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            font-size: 1rem;
            padding: 4px;
            border-radius: 6px;
            display: flex; align-items: center;
            transition: color var(--transition), background var(--transition);
        }
        .toggle-pass:hover {
            color: var(--olive);
            background: var(--olive-soft);
        }

        /* Field groups */
        .field-group { margin-bottom: 20px; }

        /* Alerts */
        .alert-custom {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 16px;
            border-radius: var(--radius);
            font-size: 0.875rem;
            margin-bottom: 24px;
            border: 1px solid transparent;
        }
        .alert-custom.alert-err {
            background: #FEF2F2;
            border-color: #FECACA;
            color: #991B1B;
        }
        .alert-custom.alert-ok {
            background: #F0FDF4;
            border-color: #BBF7D0;
            color: #166534;
        }
        .alert-custom .alert-icon { flex-shrink: 0; font-size: 1rem; margin-top: 1px; }

        /* Submit Button */
        .btn-login {
            width: 100%;
            padding: 13px 24px;
            background: var(--olive);
            color: #FFFFFF;
            border: none;
            border-radius: var(--radius-btn);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.2px;
            cursor: pointer;
            transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }
        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0);
            transition: background var(--transition);
        }
        .btn-login:hover {
            background: var(--olive-hover);
            box-shadow: 0 6px 20px rgba(92, 111, 43, 0.35);
            transform: translateY(-1px);
        }
        .btn-login:active {
            transform: translateY(0);
            box-shadow: none;
        }

        /* Back to site link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--muted);
            font-size: 0.85rem;
            text-decoration: none;
            margin-top: 20px;
            transition: color var(--transition);
        }
        .back-link:hover { color: var(--olive); }

        /* Footer note */
        .form-footer {
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            font-size: 0.75rem;
            color: #BABDB5;
            text-align: center;
        }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            body { flex-direction: column; overflow: auto; }
            .login-left {
                min-height: 240px;
                padding: 36px 28px;
                flex: none;
            }
            .left-middle .feature-list { display: none; }
            .login-right {
                width: 100%;
                padding: 40px 28px;
            }
        }

        @media (max-width: 480px) {
            .login-left { min-height: 200px; padding: 28px 20px; }
            .login-right { padding: 32px 20px; }
        }

        /* ── Animate in ── */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .login-right > * { animation: slideUp 0.4s ease both; }
        .login-right > *:nth-child(1) { animation-delay: 0.05s; }
        .login-right > *:nth-child(2) { animation-delay: 0.1s; }
        .login-right > *:nth-child(3) { animation-delay: 0.15s; }
    </style>
</head>
<body>

<!-- ══════ LEFT PANEL ══════ -->
<div class="login-left">
    <!-- Decorative rings -->
    <div class="deco-ring deco-ring-1"></div>
    <div class="deco-ring deco-ring-2"></div>
    <div class="deco-ring deco-ring-3"></div>

    <!-- Brand -->
    <div class="left-top">
        <a href="../index.php" class="brand-logo">
            <div class="brand-icon">
                <i class="bi bi-person-workspace"></i>
            </div>
            <span class="brand-name">My<span>Folio</span></span>
        </a>
    </div>

    <!-- Main Copy -->
    <div class="left-middle">
        <h2 class="left-tagline">
            Kelola Portofolio<br>
            <span>Bisnis Digital</span><br>
            Kamu dengan Mudah
        </h2>
        <p class="left-desc">
            Panel administrasi terpusat untuk mengelola profil, proyek, pengalaman, dan keahlian secara profesional.
        </p>


        <!-- Illustration -->
        <div class="login-illustration">
            <svg viewBox="0 0 400 320" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <!-- Background card / laptop screen -->
                <rect x="60" y="40" width="280" height="180" rx="16" fill="rgba(255,255,255,0.07)" stroke="rgba(255,255,255,0.15)" stroke-width="1.5"/>
                <!-- Screen inner -->
                <rect x="76" y="56" width="248" height="148" rx="10" fill="rgba(37,41,31,0.6)"/>
                <!-- Top bar dots -->
                <circle cx="96" cy="68" r="4" fill="#ff6b6b" opacity="0.8"/>
                <circle cx="110" cy="68" r="4" fill="#ffd93d" opacity="0.8"/>
                <circle cx="124" cy="68" r="4" fill="#6bcb77" opacity="0.8"/>

                <!-- Code lines simulation -->
                <rect x="92" y="84" width="80" height="6" rx="3" fill="rgba(220,227,203,0.5)"/>
                <rect x="92" y="98" width="120" height="6" rx="3" fill="rgba(138,154,91,0.6)"/>
                <rect x="92" y="112" width="60" height="6" rx="3" fill="rgba(220,227,203,0.3)"/>
                <rect x="92" y="126" width="100" height="6" rx="3" fill="rgba(138,154,91,0.5)"/>
                <rect x="92" y="140" width="90" height="6" rx="3" fill="rgba(220,227,203,0.4)"/>
                <rect x="92" y="154" width="110" height="6" rx="3" fill="rgba(138,154,91,0.3)"/>
                <rect x="92" y="168" width="70" height="6" rx="3" fill="rgba(220,227,203,0.5)"/>
                <rect x="92" y="182" width="130" height="6" rx="3" fill="rgba(138,154,91,0.6)"/>

                <!-- Right card / chart -->
                <rect x="210" y="82" width="104" height="118" rx="10" fill="rgba(220,227,203,0.08)" stroke="rgba(220,227,203,0.2)" stroke-width="1"/>
                <!-- Bar chart -->
                <rect x="223" y="140" width="14" height="40" rx="4" fill="rgba(138,154,91,0.5)"/>
                <rect x="242" y="125" width="14" height="55" rx="4" fill="rgba(220,227,203,0.6)"/>
                <rect x="261" y="130" width="14" height="50" rx="4" fill="rgba(138,154,91,0.5)"/>
                <rect x="280" y="115" width="14" height="65" rx="4" fill="rgba(220,227,203,0.7)"/>
                <!-- Chart label -->
                <rect x="223" y="96" width="80" height="6" rx="3" fill="rgba(220,227,203,0.4)"/>
                <rect x="223" y="107" width="55" height="5" rx="2.5" fill="rgba(138,154,91,0.4)"/>

                <!-- Laptop base -->
                <rect x="30" y="218" width="340" height="12" rx="6" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>
                <rect x="130" y="228" width="140" height="6" rx="3" fill="rgba(255,255,255,0.05)"/>

                <!-- Floating badge top-right -->
                <rect x="295" y="22" width="90" height="32" rx="10" fill="rgba(220,227,203,0.15)" stroke="rgba(220,227,203,0.3)" stroke-width="1"/>
                <text x="313" y="42" font-family="Inter, sans-serif" font-size="11" fill="rgba(220,227,203,0.9)" font-weight="600">✦ Portfolio</text>

                <!-- Floating badge bottom-left -->
                <rect x="18" y="250" width="100" height="32" rx="10" fill="rgba(92,111,43,0.3)" stroke="rgba(138,154,91,0.4)" stroke-width="1"/>
                <text x="30" y="270" font-family="Inter, sans-serif" font-size="11" fill="rgba(220,227,203,0.85)" font-weight="600">📊 Analytics</text>

                <!-- Star decorations -->
                <text x="52" y="38" font-size="12" fill="rgba(220,227,203,0.4)">✦</text>
                <text x="355" y="195" font-size="10" fill="rgba(220,227,203,0.3)">✦</text>
                <text x="22" y="135" font-size="8" fill="rgba(220,227,203,0.25)">✦</text>
            </svg>
        </div>

        <ul class="feature-list">
            <li>
                <span class="feature-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                Dashboard analitik real-time
            </li>
            <li>
                <span class="feature-icon"><i class="bi bi-collection-fill"></i></span>
                Manajemen proyek & portofolio
            </li>
            <li>
                <span class="feature-icon"><i class="bi bi-shield-lock-fill"></i></span>
                Keamanan data terjamin
            </li>
            <li>
                <span class="feature-icon"><i class="bi bi-envelope-at-fill"></i></span>
                Pesan masuk dari pengunjung
            </li>
        </ul>
    </div>

    <!-- Footer -->
    <div class="left-bottom">
        &copy; <?= date('Y') ?> MyFolio CMS &mdash; v2.0
    </div>
</div>

<!-- ══════ RIGHT PANEL ══════ -->
<div class="login-right">
    <!-- Header -->
    <div class="form-header">
        <div class="badge-label">
            <i class="bi bi-shield-check"></i>
            Administrator
        </div>
        <h1>Masuk ke Panel</h1>
        <p>Gunakan kredensial admin Anda untuk mengakses dashboard.</p>
    </div>

    <div class="form-divider"></div>

    <!-- Alerts -->
    <?php if ($logged_out): ?>
        <div class="alert-custom alert-ok" role="alert">
            <i class="bi bi-check-circle-fill alert-icon"></i>
            <div>Anda telah berhasil keluar dari sistem.</div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert-custom alert-err" role="alert">
            <i class="bi bi-exclamation-circle-fill alert-icon"></i>
            <div><?= e($error) ?></div>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="login.php" novalidate>

        <!-- Username -->
        <div class="field-group">
            <label class="form-label-custom" for="username">Username</label>
            <div class="input-wrap">
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="input-custom"
                    placeholder="Masukkan username"
                    required
                    autofocus
                    autocomplete="username"
                    value="<?= e($username_val) ?>"
                >
                <i class="bi bi-person input-icon"></i>
            </div>
        </div>

        <!-- Password -->
        <div class="field-group" style="margin-bottom: 28px;">
            <label class="form-label-custom" for="loginPass">Password</label>
            <div class="input-wrap">
                <input
                    type="password"
                    id="loginPass"
                    name="password"
                    class="input-custom has-toggle"
                    placeholder="Masukkan password"
                    required
                    autocomplete="current-password"
                >
                <i class="bi bi-lock input-icon"></i>
                <button class="toggle-pass" type="button" onclick="togglePass()" aria-label="Tampilkan password">
                    <i class="bi bi-eye" id="passEye"></i>
                </button>
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-login">
            <i class="bi bi-box-arrow-in-right"></i>
            Masuk ke Dashboard
        </button>

    </form>

    <!-- Back link -->
    <div class="text-center">
        <a href="../index.php" class="back-link">
            <i class="bi bi-arrow-left"></i>
            Kembali ke Website
        </a>
    </div>

    <!-- Footer -->
    <div class="form-footer">
        Lupa akses? Hubungi administrator sistem &mdash;
        <strong>MyFolio CMS &copy; <?= date('Y') ?></strong>
    </div>
</div>

<!-- Scripts -->
<script>
function togglePass() {
    const input = document.getElementById('loginPass');
    const eye   = document.getElementById('passEye');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    eye.classList.toggle('bi-eye',       !isHidden);
    eye.classList.toggle('bi-eye-slash',  isHidden);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
