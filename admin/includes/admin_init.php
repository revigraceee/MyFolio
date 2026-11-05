<?php
// admin/includes/admin_init.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/auth.php';

// Protect all admin pages
check_auth();

$csrf_token = get_csrf_token();

