<?php
/**
 * login.php – Secure Login Page (Leafy Green Theme)
 * Practical 10: Secure Login/Logout with Sessions, RBAC, Timeout, Remember-Me
 */
require 'db.php';
require 'auth.php';
start_secure_session();

// Already logged in? Redirect to dashboard
if (isset($_SESSION['user_id']) || login_from_remember_cookie($pdo)) {
    redirect_by_role($_SESSION['role']);
}

$error = '';
$messages = [
    'timeout'      => '⏱ Session expired due to inactivity. Please log in again.',
    'unauthorized' => '🔒 Please log in to access that page.',
    'forbidden'    => '🚫 You do not have permission to access that page.',
    'loggedout'    => '✅ You have been logged out successfully.',
];
$info = $messages[$_GET['msg'] ?? ''] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);

        $_SESSION['user_id']       = $user['id'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['role']          = $user['role'];
        $_SESSION['last_activity'] = time();
        $_SESSION['prev_login']    = $user['last_login'];

        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")
            ->execute([$user['id']]);

        if (!empty($_POST['remember'])) {
            issue_remember_token($pdo, (int)$user['id']);
        }

        redirect_by_role($user['role']);
    }

    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – StudentHub | Practical 10</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --g50:  #f0fdf4;
            --g100: #dcfce7;
            --g200: #bbf7d0;
            --g300: #86efac;
            --g400: #4ade80;
            --g500: #22c55e;
            --g600: #16a34a;
            --g700: #15803d;
            --g800: #166534;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0d2818 0%, #1a4a2a 40%, #0a2e18 70%, #162b18 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .orb { position: fixed; border-radius: 50%; pointer-events: none; filter: blur(80px); opacity: 0.2; }
        .orb-1 { width: 500px; height: 500px; background: #22c55e; top: -180px; left: -150px; }
        .orb-2 { width: 400px; height: 400px; background: #15803d; bottom: -150px; right: -100px; }

        /* ── Top Navigation ── */
        .topnav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 40px; border-bottom: 1px solid rgba(255,255,255,0.08);
            position: relative; z-index: 10;
            background: rgba(10,25,15,0.6); backdrop-filter: blur(16px);
        }

        .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--g500), var(--g700));
            border-radius: 9px; display: flex; align-items: center; justify-content: center;
            font-size: 1rem; box-shadow: 0 2px 10px rgba(34,197,94,0.4);
        }
        .brand-name { font-size: 0.95rem; font-weight: 700; color: #fff; display: block; }
        .brand-sub  { font-size: 0.56rem; color: var(--g300); text-transform: uppercase; letter-spacing: 0.1em; display: block; }

        .nav-links { display: flex; gap: 28px; }
        .nav-links a { color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.84rem; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover { color: #fff; }

        .nav-actions { display: flex; align-items: center; gap: 12px; }
        .nav-icon-btn {
            width: 34px; height: 34px; border: none; cursor: pointer;
            background: rgba(255,255,255,0.08); border-radius: 8px;
            color: rgba(255,255,255,0.65); font-size: 0.9rem;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .nav-icon-btn:hover { background: rgba(255,255,255,0.15); color: #fff; }
        .btn-nav-outline {
            background: transparent; border: 1px solid rgba(255,255,255,0.25);
            color: #fff; padding: 7px 18px; border-radius: 8px;
            font-size: 0.84rem; font-weight: 500; cursor: pointer;
            transition: background 0.2s; font-family: 'Inter', sans-serif;
        }
        .btn-nav-outline:hover { background: rgba(255,255,255,0.08); }
        .btn-nav-fill {
            background: var(--g600); border: none; color: #fff;
            padding: 7px 18px; border-radius: 8px; font-size: 0.84rem;
            font-weight: 600; cursor: pointer; transition: background 0.2s;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 2px 10px rgba(22,163,74,0.4);
        }
        .btn-nav-fill:hover { background: var(--g700); }

        /* ── Main ── */
        .main-content {
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 40px 20px; position: relative; z-index: 5;
        }

        .login-wrapper {
            display: flex;
            background: rgba(10,25,15,0.5);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            box-shadow: 0 32px 80px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.08);
        }

        /* LEFT */
        .login-left {
            width: 46%;
            background: linear-gradient(170deg, rgba(34,197,94,0.18) 0%, rgba(10,30,18,0.98) 100%);
            padding: 44px 36px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid rgba(255,255,255,0.08);
            position: relative;
            overflow: hidden;
        }
        .login-left::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(34,197,94,0.15) 0%, transparent 70%);
            bottom: -80px; right: -80px;
            pointer-events: none;
        }
        .left-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3);
            color: var(--g300); font-size: 0.68rem; font-weight: 700;
            padding: 5px 12px; border-radius: 999px; width: fit-content;
            text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 20px;
        }
        .left-title { font-family: 'Playfair Display', serif; color: #fff; font-size: 1.9rem; line-height: 1.25; margin-bottom: 12px; }
        .left-title em { color: var(--g400); font-style: italic; }
        .left-sub { color: rgba(255,255,255,0.6); font-size: 0.83rem; line-height: 1.65; margin-bottom: 26px; }
        .feature-list { display: flex; flex-direction: column; gap: 10px; }
        .feature-item { display: flex; align-items: center; gap: 11px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.09); border-radius: 10px; padding: 10px 13px; color: rgba(255,255,255,0.8); font-size: 0.8rem; font-weight: 500; }
        .fi-icon { width: 28px; height: 28px; background: rgba(34,197,94,0.18); border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0; }
        .left-quote { margin-top: 22px; padding-top: 18px; border-top: 1px solid rgba(255,255,255,0.08); color: rgba(255,255,255,0.45); font-size: 0.77rem; font-style: italic; line-height: 1.65; }
        .left-quote cite { display: block; margin-top: 6px; color: var(--g400); font-style: normal; font-size: 0.7rem; font-weight: 600; }

        /* RIGHT */
        .login-right {
            flex: 1; background: #fff;
            padding: 44px 42px; display: flex; flex-direction: column; justify-content: center;
        }
        .form-tag { color: var(--g700); font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 10px; display: block; }
        .form-title { font-size: 1.7rem; font-weight: 800; color: #111827; line-height: 1.2; margin-bottom: 6px; }
        .form-title em { font-family: 'Playfair Display', serif; color: var(--g700); font-style: italic; font-weight: 600; }
        .form-sub { color: #6b7280; font-size: 0.84rem; line-height: 1.55; margin-bottom: 26px; }
        .alert { padding: 11px 14px; border-radius: 10px; font-size: 0.83rem; margin-bottom: 16px; font-weight: 500; display: flex; align-items: flex-start; gap: 8px; }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5; }
        .alert-info  { background: var(--g50); color: var(--g800); border: 1px solid var(--g300); }
        .alert-warn  { background: #fffbeb; color: #92400e; border: 1px solid #fcd34d; }
        .field-group { margin-bottom: 16px; }
        .field-label { display: block; font-size: 0.81rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .field-wrap { position: relative; display: flex; align-items: center; }
        .field-icon { position: absolute; left: 12px; color: #9ca3af; font-size: 0.9rem; pointer-events: none; }
        .field-wrap input { width: 100%; padding: 11px 14px 11px 37px; border: 1.5px solid #d1d5db; border-radius: 10px; font-size: 0.88rem; color: #111827; background: #f9fafb; font-family: 'Inter', sans-serif; transition: border-color 0.2s, box-shadow 0.2s, background 0.2s; }
        .field-wrap input:focus { outline: none; border-color: var(--g500); background: #fff; box-shadow: 0 0 0 3px rgba(34,197,94,0.14); }
        .eye-btn { position: absolute; right: 11px; background: none; border: none; cursor: pointer; color: #9ca3af; font-size: 0.95rem; display: flex; align-items: center; padding: 2px; }
        .eye-btn:hover { color: #4b5563; }
        .remember-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; font-size: 0.83rem; }
        .rem-left { display: flex; align-items: center; gap: 7px; color: #4b5563; }
        .rem-left input { accent-color: var(--g600); cursor: pointer; }
        .rem-left label { cursor: pointer; }
        .forgot { color: var(--g700); font-weight: 600; text-decoration: none; font-size: 0.82rem; }
        .forgot:hover { text-decoration: underline; }
        .btn-submit { width: 100%; padding: 13px; background: linear-gradient(135deg, var(--g600), var(--g700)); color: #fff; border: none; border-radius: 10px; font-size: 0.93rem; font-weight: 700; cursor: pointer; letter-spacing: 0.02em; font-family: 'Inter', sans-serif; transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s; box-shadow: 0 4px 16px rgba(22,163,74,0.42); display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-submit:hover  { opacity: 0.91; box-shadow: 0 6px 22px rgba(22,163,74,0.52); }
        .btn-submit:active { transform: scale(0.98); }
        .reg-link { text-align: center; margin-top: 16px; font-size: 0.82rem; color: #6b7280; }
        .reg-link a { color: var(--g700); font-weight: 700; text-decoration: none; }
        .reg-link a:hover { text-decoration: underline; }
        .demo-box { margin-top: 20px; background: var(--g50); border: 1px dashed var(--g300); border-radius: 10px; padding: 12px 15px; font-size: 0.78rem; color: #4b5563; }
        .demo-box strong { display: block; margin-bottom: 5px; color: var(--g800); }
        .demo-box code { background: var(--g100); color: var(--g800); padding: 1px 6px; border-radius: 4px; font-family: monospace; }
        .footer { text-align: center; padding: 16px; color: rgba(255,255,255,0.3); font-size: 0.74rem; position: relative; z-index: 5; }
        @media (max-width: 640px) { .login-left { display: none; } .topnav { padding: 14px 18px; } .nav-links { display: none; } .login-right { padding: 32px 22px; } }
    </style>
</head>
<body>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<nav class="topnav">
    <a href="#" class="brand">
        <div class="brand-logo">🌿</div>
        <div class="brand-text">
            <span class="brand-name">StudentHub</span>
            <span class="brand-sub">University Portal</span>
        </div>
    </a>
    <div class="nav-links">
        <a href="#">Home</a><a href="#">About</a><a href="#">FAQ</a><a href="#">Contact</a>
    </div>
    <div class="nav-actions">
        <button class="nav-icon-btn">🌙</button>
        <button class="nav-icon-btn">🔔</button>
        <button class="btn-nav-outline">Login</button>
        <button class="btn-nav-fill">Register</button>
    </div>
</nav>
<div class="main-content">
    <div class="login-wrapper">
        <div class="login-left">
            <div>
                <div class="left-badge">🛡️ StudentHub Portal Access</div>
                <h1 class="left-title">Welcome to the<br>Academic <em>Sanctuary.</em></h1>
                <p class="left-sub">Access your synchronized academic dashboard, registered campus symposia, and scholarly collaboration spaces.</p>
                <div class="feature-list">
                    <div class="feature-item"><div class="fi-icon">🎓</div>Verified Student Credentials &amp; Profiles</div>
                    <div class="feature-item"><div class="fi-icon">⚡</div>Real-Time Symposium &amp; Workshop Alerts</div>
                    <div class="feature-item"><div class="fi-icon">🌿</div>Collaborative Academic Resource Hub</div>
                </div>
            </div>
            <div class="left-quote">
                "Empowering scholars with effortless access to student milestones, department records, and daily campus life."
                <cite>◆ University Academic Affairs, 2026</cite>
            </div>
        </div>
        <div class="login-right">
            <span class="form-tag">PRACTICAL 10: SECURE SESSION LOGIN</span>
            <h2 class="form-title">Welcome Back to <em>StudentHub</em></h2>
            <p class="form-sub">Enter your academic credentials to authenticate and access your protected portal pages.</p>
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($info):
                $cls = str_contains($info, 'expired') ? 'alert-warn' : 'alert-info'; ?>
                <div class="alert <?= $cls ?>"><?= htmlspecialchars($info) ?></div>
            <?php endif; ?>
            <form method="POST" autocomplete="off">
                <div class="field-group">
                    <label class="field-label" for="username">Email ID or Student Code *</label>
                    <div class="field-wrap">
                        <span class="field-icon">📧</span>
                        <input type="text" id="username" name="username" placeholder="e.g. STU-001 or alex.ce23@charusat.edu.in" required autofocus>
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label" for="password">Password *</label>
                    <div class="field-wrap">
                        <span class="field-icon">🔒</span>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="eye-btn" onclick="togglePwd()">👁️</button>
                    </div>
                </div>
                <div class="remember-row">
                    <div class="rem-left">
                        <input type="checkbox" id="remember" name="remember" value="1">
                        <label for="remember">Remember me (Secure 30-day cookie)</label>
                    </div>
                    <a href="#" class="forgot">Forgot password?</a>
                </div>
                <button type="submit" class="btn-submit">Authenticate &amp; Login →</button>
            </form>
            <p class="reg-link">Don't have an account yet? <a href="#">Register here (Practical 9)</a></p>
            <div class="demo-box">
                <strong>🧪 Demo Credentials</strong>
                Admin &nbsp;&nbsp;: <code>admin</code> / <code>Admin@123</code><br><br>
                Student : <code>student1</code> / <code>Student@123</code>
            </div>
        </div>
    </div>
</div>
<div class="footer">© 2026 StudentHub University Portal · Practical 10: Secure Session Authentication</div>
<script>
    function togglePwd() { const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password'; }
</script>
</body>
</html>
