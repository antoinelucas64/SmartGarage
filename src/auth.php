<?php
// src/auth.php — session + login/logout + rate limiting

const MAX_ATTEMPTS = 5;
const ATTEMPTS_WINDOW_SECONDS = 600; // 10 minutes

function start_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']),
        ]);
        session_name('SGARAGESESS');
        session_start();
    }
}

function current_user(PDO $pdo): ?array {
    start_session();
    $uid = $_SESSION['user_id'] ?? null;
    if (!$uid) return null;
    $stmt = $pdo->prepare('SELECT id, username, language FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function require_login(PDO $pdo): array {
    $u = current_user($pdo);
    if (!$u) {
        header('Location: ?p=login');
        exit;
    }
    return $u;
}

function login(PDO $pdo, string $username, string $password): bool {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }
    start_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    return true;
}

function logout(): void {
    start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function create_user(PDO $pdo, string $username, string $password, string $lang = 'en'): int {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, language) VALUES (?,?,?)');
    $stmt->execute([$username, $hash, $lang]);
    return (int)$pdo->lastInsertId();
}

// --- Rate limiting ---

function client_ip(): string {
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function record_failed_attempt(PDO $pdo): void {
    $stmt = $pdo->prepare('INSERT INTO login_attempts (ip) VALUES (?)');
    $stmt->execute([client_ip()]);
    // cleanup old entries (best-effort)
    $pdo->exec("DELETE FROM login_attempts WHERE attempted_at < datetime('now', '-1 hour')");
}

function is_rate_limited(PDO $pdo): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM login_attempts
        WHERE ip = ?
          AND attempted_at > datetime('now', '-' || ? || ' seconds')
    ");
    $stmt->execute([client_ip(), ATTEMPTS_WINDOW_SECONDS]);
    return (int)$stmt->fetchColumn() >= MAX_ATTEMPTS;
}

function clear_attempts(PDO $pdo): void {
    $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE ip = ?');
    $stmt->execute([client_ip()]);
}
