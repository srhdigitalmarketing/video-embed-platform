<?php
require_once __DIR__.'/database.php';
require_once __DIR__.'/helpers.php';

function is_logged_in(): bool {
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void {
    if (!is_logged_in()) {
        header('Location: '.app_url('admin/login.php'));
        exit;
    }
}

function login_admin(string $email, string $password): bool {
    $st = db()->prepare('SELECT * FROM users WHERE email=? AND status=1 LIMIT 1');
    $st->execute([$email]);
    $u = $st->fetch();

    if ($u && password_verify($password, $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $u['id'];
        $_SESSION['admin_email'] = $u['email'];
        return true;
    }

    return false;
}

function logout_admin(): void {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $p['path'],
            'domain' => $p['domain'],
            'secure' => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?? 'Lax'
        ]);
    }

    session_destroy();
}

function log_action(string $action, string $details=''): void {
    if (!is_logged_in()) return;

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $hash = hash_hmac('sha256', $ip, (string)env('APP_KEY', 'change-me'));

    db()->prepare(
        'INSERT INTO system_logs(user_id,action,details,ip_hash) VALUES(?,?,?,?)'
    )->execute([
        $_SESSION['admin_id'],
        $action,
        $details,
        $hash
    ]);
}