<?php
require_once __DIR__.'/config.php';
function db(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host='.env('DB_HOST','127.0.0.1').';port='.env('DB_PORT','3306').';dbname='.env('DB_DATABASE').';charset=utf8mb4';
    $pdo = new PDO($dsn, env('DB_USERNAME'), env('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}