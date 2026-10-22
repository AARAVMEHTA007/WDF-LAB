<?php
/**
 * auth.php – Shared authentication helpers
 * Practical 10: Secure Login/Logout with Sessions, RBAC, Timeout, Remember-Me
 */

const SESSION_TIMEOUT = 300;   // 5 minutes inactivity (change to 30 for quick demo)
const REMEMBER_DAYS   = 30;

/* ─────────────────────────────────────────────────────────
   Session helpers
───────────────────────────────────────────────────────── */

function start_secure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,                            // browser-session cookie
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),    // HTTPS-only in production
            'httponly' => true,                         // JS cannot read it
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

function destroy_session_completely(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function redirect_by_role(string $role): never {
    header('Location: ' . ($role === 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php'));
    exit;
}

/* ─────────────────────────────────────────────────────────
   Remember-me (selector : hashed-validator scheme)
───────────────────────────────────────────────────────── */

function set_remember_cookie(string $value, int $expires): void {
    setcookie('remember_me', $value, [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

function issue_remember_token(PDO $pdo, int $userId): void {
    $selector  = bin2hex(random_bytes(12));   // 24-char hex selector (public)
    $validator = bin2hex(random_bytes(32));   // 64-char hex validator (secret)
    $expires   = time() + REMEMBER_DAYS * 86400;

    $pdo->prepare(
        "INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at)
         VALUES (?, ?, ?, ?)"
    )->execute([
        $userId,
        $selector,
        hash('sha256', $validator),           // only the HASH stored in DB
        date('Y-m-d H:i:s', $expires),
    ]);

    set_remember_cookie($selector . ':' . $validator, $expires);
}

function login_from_remember_cookie(PDO $pdo): bool {
    if (empty($_COOKIE['remember_me']) || !str_contains($_COOKIE['remember_me'], ':')) {
        return false;
    }
    [$selector, $validator] = explode(':', $_COOKIE['remember_me'], 2);

    $stmt = $pdo->prepare(
        "SELECT rt.id, rt.token_hash, u.id AS uid, u.username, u.role, u.last_login
         FROM remember_tokens rt
         JOIN users u ON u.id = rt.user_id
         WHERE rt.selector = ? AND rt.expires_at > NOW()"
    );
    $stmt->execute([$selector]);
    $row = $stmt->fetch();

    if ($row && hash_equals($row['token_hash'], hash('sha256', $validator))) {
        // Rotate token on each use (prevents token theft replay)
        $pdo->prepare("DELETE FROM remember_tokens WHERE id = ?")->execute([$row['id']]);
        issue_remember_token($pdo, (int)$row['uid']);

        session_regenerate_id(true);
        $_SESSION['user_id']       = $row['uid'];
        $_SESSION['username']      = $row['username'];
        $_SESSION['role']          = $row['role'];
        $_SESSION['last_activity'] = time();
        $_SESSION['prev_login']    = $row['last_login'];
        return true;
    }

    // Forged / expired cookie: clear it
    set_remember_cookie('', time() - 3600);
    return false;
}

/* ─────────────────────────────────────────────────────────
   Access control (call at top of every protected page)
───────────────────────────────────────────────────────── */

function require_login(PDO $pdo, ?string $requiredRole = null): void {
    start_secure_session();

    // ① Inactivity timeout check
    if (isset($_SESSION['user_id'], $_SESSION['last_activity'])
        && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        destroy_session_completely();
        header('Location: login.php?msg=timeout');
        exit;
    }

    // ② Not logged in → try remember-me, else redirect
    if (!isset($_SESSION['user_id'])) {
        if (!login_from_remember_cookie($pdo)) {
            header('Location: login.php?msg=unauthorized');
            exit;
        }
    }

    // ③ Refresh activity timestamp
    $_SESSION['last_activity'] = time();

    // ④ Role (RBAC) check
    if ($requiredRole !== null && $_SESSION['role'] !== $requiredRole) {
        http_response_code(403);
        header('Location: login.php?msg=forbidden');
        exit;
    }
}