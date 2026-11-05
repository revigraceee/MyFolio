<?php
// includes/functions.php

/**
 * Escape HTML output for XSS prevention (Section 9.3)
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Set session flash message
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Get and clear session flash message
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Safe file upload handler (Section 9.6)
 */
function handle_file_upload(array $file, string $targetSubdir, array $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg']): ?string {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        return null;
    }

    $baseUploadDir = __DIR__ . '/../uploads/' . trim($targetSubdir, '/') . '/';
    if (!is_dir($baseUploadDir)) {
        mkdir($baseUploadDir, 0777, true);
    }

    $newFilename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $baseUploadDir . $newFilename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $newFilename;
    }

    return null;
}

/**
 * URL Slug Generator
 */
function generate_slug(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'project-' . time() : $text;
}

/**
 * Format Period Dates (e.g. "Agu 2022 - Sekarang")
 */
function format_period(?string $start, ?string $end, int|bool $is_current): string {
    $months = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];

    $startStr = '';
    if (!empty($start)) {
        $ts = strtotime($start);
        $m = (int)date('n', $ts);
        $startStr = ($months[$m] ?? '') . ' ' . date('Y', $ts);
    }

    if ($is_current) {
        return $startStr ? "{$startStr} — Sekarang" : 'Sekarang';
    }

    if (!empty($end)) {
        $ts = strtotime($end);
        $m = (int)date('n', $ts);
        $endStr = ($months[$m] ?? '') . ' ' . date('Y', $ts);
        return $startStr ? "{$startStr} — {$endStr}" : $endStr;
    }

    return $startStr ?: '—';
}

/**
 * Resolve displayable image URL
 */
function resolve_img(?string $filename, string $subdir = '', string $fallback = 'assets/img/placeholder.png', string $basePrefix = ''): string {
    if (empty($filename)) {
        return $basePrefix . $fallback;
    }
    if (str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://')) {
        return $filename;
    }

    // Check in uploads/{subdir}
    $cleanSubdir = trim($subdir, '/');
    $uploadPath = __DIR__ . '/../uploads/' . ($cleanSubdir ? $cleanSubdir . '/' : '') . $filename;
    if (file_exists($uploadPath)) {
        return $basePrefix . 'uploads/' . ($cleanSubdir ? $cleanSubdir . '/' : '') . $filename;
    }

    // Check in assets/img/
    if (file_exists(__DIR__ . '/../assets/img/' . $filename)) {
        return $basePrefix . 'assets/img/' . $filename;
    }

    return $basePrefix . $fallback;
}

