<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
function env(string $key, ?string $default = null): ?string {
    static $loaded = false, $vars = [];
    if (!$loaded) {
        $file = dirname(__DIR__) . '/.env';
        if (is_file($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
                [$k,$v] = explode('=', $line, 2); $v = trim($v);
                if ((str_starts_with($v,'"') && str_ends_with($v,'"')) || (str_starts_with($v,"'") && str_ends_with($v,"'"))) $v = substr($v,1,-1);
                $vars[trim($k)] = $v;
            }
        }
        $loaded = true;
    }
    return $vars[$key] ?? getenv($key) ?: $default;
}
function app_url(string $path=''): string { return rtrim((string)env('APP_URL',''), '/') . '/' . ltrim($path,'/'); }
function csrf_token(): string { if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32)); return $_SESSION['_csrf']; }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="'.htmlspecialchars(csrf_token(), ENT_QUOTES).'">'; }
function verify_csrf(): void { if (!hash_equals((string)($_SESSION['_csrf'] ?? ''), (string)($_POST['_csrf'] ?? ''))) { http_response_code(419); exit('CSRF validation failed'); } }