<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (preg_match('#^/play/([A-Za-z0-9_-]+)/?$#', $path, $m)) {
    $_GET['key'] = $m[1];
    require __DIR__.'/play/index.php';
    exit;
}

header('Location: /admin/login.php');
exit;
