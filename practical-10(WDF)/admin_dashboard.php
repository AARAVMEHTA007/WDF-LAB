<?php
/**
 * admin_dashboard.php – Admin-only page (Leafy Green Theme with Accent)
 * Practical 10: Secure Login/Logout with Sessions, RBAC, Timeout, Remember-Me
 */
require 'db.php';
require 'auth.php';
require_login($pdo, 'admin');

$timeoutMins = SESSION_TIMEOUT / 60;
$prevLogin   = $_SESSION['prev_login'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – StudentHub | Practical 10</title>
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
            --a500: #6366f1; /* admin accent */
            --a600: #4f46e5;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0d2818 0%, #1a4a2a 40%, #0a2e18 70%, #162b18 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.2; pointer-events: none; }
        .orb-1 { width: 500px; height: 500px; background: #22c55e; top: -180px; left: -150px; }
        .orb-2 { width: 400px; height: 400px; background: #15803d; bottom: -150px; right: -100px; }
        .topnav { display: flex; align-items: center; justify-content: space-between; padding: 14px 40px; border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(10,25,15,0.6); backdrop-filter: blur(16px); }
        .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo { width: 36px; height: 36px; background: linear-gradient(135deg, var(--a500), var(--a600)); border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 1rem; box-shadow: 0 2px 10px rgba(99,102,241,0.4); }
        .brand-name { font-size: 0.95rem; font-weight: 700; color: #fff; }
        .brand-sub  { font-size: 0.56rem; color: var(--g300); text-transform: uppercase; letter-spacing: 0.1em; }
        .nav-links { display: flex; gap: 28px; }
        .nav-links a { color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.84rem; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover, .nav-links a.active { color: #fff; }
        .nav-links a.active { background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.25); padding: 4px 14px; border-radius: 7px; color: var(--g300); }
        .nav-right { display: flex; align-items: center; gap: 12px; }
        .nav-icon-btn { width: 34px; height: 34px; border: none; cursor: pointer; background: rgba(255,255,255,0.08); border-radius: 8px; color: rgba(255,255,255,0.65); font-size: 0.9rem; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
        .nav-icon-btn:hover { background: rgba(255,255,255,0.15); color: #fff; }
        .user-pill { display: flex; align-items: center; gap: 9px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; padding: 6px 14px 6px 8px; }
        .user-avatar { width: 28px; height: 28px; background: linear-gradient(135deg, var(--a500), var(--a600)); border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; color: #fff; text-transform: uppercase; }
        .user-name { font-size: 0.82rem; font-weight: 600; color: rgba(255,255,255,0.9); }
        .logout-btn { display: flex; align-items: center; gap: 6px; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; text-decoration: none; padding: 7px 16px; border-radius: 9px; font-size: 0.82rem; font-weight: 600; transition: all 0.2s; }
        .logout-btn:hover { background: rgba(239,68,68,0.28); color: #fff; }
        .wrapper { display: flex; flex: 1; position: relative; z-index: 5; }
        .sidebar { width: 240px; flex-shrink: 0; background: rgba(10,25,15,0.7); backdrop-filter: blur(16px); border-right: 1px solid rgba(255,255,255,0.07); padding: 28px 16px; display: flex; flex-direction: column; gap: 4px; }
        .sidebar-user { text-align: center; padding: 20px 12px 24px; margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.07); }
        .sb-avatar { width: 52px; height: 52px; margin: 0 auto 10px; background: linear-gradient(135deg, var(--a500), var(--a600)); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 800; color: #fff; text-transform: uppercase; box-shadow: 0 4px 14px rgba(99,102,241,0.35); }
        .sb-name { font-size: 0.9rem; font-weight: 700; color: #fff; }
        .sb-role { font-size: 0.7rem; color: var(--g300); margin-top: 3px; text-transform: uppercase; letter-spacing: 0.07em; }
        .sb-btn { margin-top: 12px; display: inline-flex; align-items: center; gap: 6px; background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.25); color: var(--g300); font-size: 0.73rem; font-weight: 600; padding: 6px 14px; border-radius: 7px; cursor: pointer; text-decoration: none; transition: background 0.2s; }
        .sb-btn:hover { background: rgba(99,102,241,0.25); }
        .sb-label { font-size: 0.62rem; font-weight: 700; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 0.1em; padding: 14px 12px 6px; }
        .sb-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 9px; cursor: pointer; color: rgba(255,255,255,0.6); font-size: 0.83rem; font-weight: 500; transition: all 0.2s; text-decoration: none; }
        .sb-item:hover { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.9); }
        .sb-item.active { background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.2); color: var(--g300); font-weight: 600; }
        .sb-item-icon { font-size: 1rem; width: 20px; text-align: center; }
        .sb-badge { margin-left: auto; background: rgba(99,102,241,0.2); border: 1px solid rgba(99,102,241,0.3); color: var(--g300); font-size: 0.62rem; font-weight: 700; padding: 2px 8px; border-radius: 999px; text-transform: uppercase; }
        .sidebar-footer { margin-top: auto; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.07); font-size: 0.7rem; color: rgba(255,255,255,0.25); padding-left: 12px; }
        .db-status { display: flex; align-items: center; gap: 6px; margin-top: 4px; }
        .db-dot { width: 7px; height: 7px; background: var(--a500); border-radius: 50%; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1}50%{opacity:0.4} }
        .main { flex: 1; padding: 28px 32px; overflow-y: auto; }
        .hero { background: linear-gradient(130deg, rgba(99,102,241,0.25) 0%, rgba(10,30,18,0.95) 100%); border: 1px solid rgba(99,102,241,0.2); border-radius: 18px; padding: 32px 36px; margin-bottom: 24px; position: relative; overflow: hidden; }
        .hero::before { content: '🛡️'; position: absolute; right: 30px; top: 50%; transform: translateY(-50%); font-size: 6rem; opacity: 0.08; }
        .hero-tag { display: inline-flex; align-items: center; gap: 5px; background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.25); color: var(--g300); font-size: 0.65rem; font-weight: 700; padding: 4px 10px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 12px; }
        .hero h1 { font-size: 1.65rem; font-weight: 800; color: #fff; margin-bottom: 6px; }
        .hero h1 em { font-family: 'Playfair Display', serif; color: var(--a600); font-style: italic; font-weight: 600; }
        .hero p { color: rgba(255,255,255,0.6); font-size: 0.85rem; line-height: 1.6; max-width: 520px; }
        .stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 22px; }
        .stat-card { background: rgba(10,25,15,0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 18px 20px; transition: border-color 0.2s, transform 0.2s; }
        .stat-card:hover { border-color: rgba(99,102,241,0.3); transform: translateY(-2px); }
        .stat-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px; }
        .stat-icon { width: 36px; height: 36px; background: rgba(99,102,241,0.15); border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
        .stat-badge { font-size: 0.62rem; font-weight: 700; padding: 3px 8px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-green { background: rgba(34,197,94,0.15); color: var(--g300); border: 1px solid rgba(34,197,94,0.25); }
        .badge-purple { background: rgba(99,102,241,0.15); color: var(--g300); border: 1px solid rgba(99,102,241,0.25); }
        .stat-label { font-size: 0.68rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.07em; font-weight: 600; }
        .stat-value { font-size: 1.8rem; font-weight: 800; color: #fff; margin: 3px 0; line-height: 1; }
        .stat-sub { font-size: 0.73rem; color: rgba(255,255,255,0.4); }
        .section-card { background: rgba(10,25,15,0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 24px 26px; margin-bottom: 20px; }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
        .section-title { font-size: 0.82rem; font-weight: 700; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.07em; }
        .section-tag { font-size: 0.62rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-green { background: rgba(34,197,94,0.15); color: var(--g300); border: 1px solid rgba(34,197,94,0.25); }
        .sess-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
        .sess-table td { padding: 10px 12px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sess-table tr:last-child td { border-bottom: none; }
        .sess-table td:first-child { color: rgba(255,255,255,0.45); width: 42%; font-weight: 500; font-size: 0.8rem; }
        .sess-table td:last-child { color: rgba(255,255,255,0.85); font-family: 'Courier New', monospace; font-size: 0.78rem; }
        .timeout-wrap { margin-top: 18px; }
        .timeout-label { font-size: 0.78rem; color: rgba(255,255,255,0.4); margin-bottom: 8px; }
        .progress { background: rgba(255,255,255,0.07); border-radius: 999px; height: 8px; overflow: hidden; }
        .progress-bar { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--a500), var(--a600)); animation: drain <?= SESSION_TIMEOUT ?>s linear forwards; box-shadow: 0 0 10px rgba(99,102,241,0.4); }
        @keyframes drain { from { width:100%; } to { width:0%; } }
        .note { background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.2); border-radius: 12px; padding: 14px 18px; font-size: 0.83rem; color: rgba(255,255,255,0.6); line-height: 1.6; }
        .note code { background: rgba(99,102,241,0.15); color: var(--g300); padding: 1px 6px; border-radius: 4px; font-family: monospace; font-size: 0.8rem; }
        .note strong { color: var(--g300); }
        @media (max-width: 768px) { .sidebar { display:none; } .stats { grid-template-columns: 1fr 1fr; } .main { padding:20px 16px; } }
    </style>
</head>
<body>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<nav class="topnav">
    <a href="#" class="brand">
        <div class="brand-logo">🛡️</div>
        <div>
            <span class="brand-name">StudentHub Admin</span>
            <span class="brand-sub">University Portal</span>
        </div>
    </a>
    <div class="nav-links">
        <a href="#">Home</a>
        <a href="#" class="active">Admin</a>
        <a href="#">Settings</a>
        <a href="#">Reports</a>
    </div>
    <div class="nav-right">
        <button class="nav-icon-btn">🌙</button>
        <button class="nav-icon-btn">🔔</button>
        <div class="user-pill">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'],0,2)) ?></div>
            <span class="user-name"><?= htmlspecialchars($_SESSION['username']) ?></span>
        </div>
        <a href="logout.php" class="logout-btn">⎋ Logout</a>
    </div>
</nav>
<div class="wrapper">
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="sb-avatar"><?= strtoupper(substr($_SESSION['username'],0,2)) ?></div>
            <div class="sb-name"><?= htmlspecialchars($_SESSION['username']) ?></div>
            <div class="sb-role">Admin · Verified</div>
            <a href="#" class="sb-btn">📋 View Profile</a>
        </div>
        <div class="sb-label">Navigation</div>
        <a href="#" class="sb-item active"><span class="sb-item-icon">🛡️</span> Dashboard <span class="sb-badge">Admin</span></a>
        <a href="#" class="sb-item"><span class="sb-item-icon">📊</span> System Metrics</a>
        <a href="#" class="sb-item"><span class="sb-item-icon">🗂️</span> User Management</a>
        <a href="#" class="sb-item"><span class="sb-item-icon">⚙️</span> Settings</a>
        <a href="#" class="sb-item"><span class="sb-item-icon">🚨</span> Security Logs</a>
        <div class="sidebar-footer">
            <div>Practical 10 · RBAC</div>
            <div class="db-status"><div class="db-dot"></div> Session Active</div>
        </div>
    </aside>
    <main class="main">
        <div class="hero">
            <div class="hero-tag">🛡️ Admin Dashboard · Secure Session</div>
            <h1>Welcome, <em><?= htmlspecialchars($_SESSION['username']) ?></em></h1>
            <p>Overview of system health, recent activities and quick actions for administrative tasks.</p>
        </div>
        <div class="stats">
            <div class="stat-card">
                <div class="stat-top"><div class="stat-icon">👥</div><span class="stat-badge badge-purple">Active</span></div>
                <div class="stat-label">Registered Users</div>
                <div class="stat-value">12</div>
                <div class="stat-sub">Total accounts</div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><div class="stat-icon">⚠️</div><span class="stat-badge badge-green">0</span></div>
                <div class="stat-label">Security Alerts</div>
                <div class="stat-value">0</div>
                <div class="stat-sub">No issues</div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><div class="stat-icon">⏱️</div><span class="stat-badge badge-green">Live</span></div>
                <div class="stat-label">Session Timeout</div>
                <div class="stat-value"><?= $timeoutMins ?></div>
                <div class="stat-sub">min<?= $timeoutMins != 1 ? 's' : '' ?> idle limit</div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><div class="stat-icon">📈</div><span class="stat-badge badge-purple">OK</span></div>
                <div class="stat-label">Server Load</div>
                <div class="stat-value">0.42</div>
                <div class="stat-sub">Average</div>
            </div>
        </div>
        <div class="section-card">
            <div class="section-header"><span class="section-title">📋 Current Session Details</span><span class="section-tag badge-green">Secure · HttpOnly</span></div>
            <table class="sess-table">
                <tr><td>Session ID</td><td><?= session_id() ?></td></tr>
                <tr><td>User ID</td><td><?= $_SESSION['user_id'] ?></td></tr>
                <tr><td>Username</td><td><?= htmlspecialchars($_SESSION['username']) ?></td></tr>
                <tr><td>Role</td><td><?= htmlspecialchars($_SESSION['role']) ?></td></tr>
                <tr><td>Last Activity</td><td><?= date('Y-m-d H:i:s', $_SESSION['last_activity']) ?></td></tr>
                <tr><td>Last Login</td><td><?= $prevLogin ? htmlspecialchars($prevLogin) : 'First login' ?></td></tr>
                <tr><td>Cookie HttpOnly</td><td>✅ Yes – JS cannot read session ID</td></tr>
                <tr><td>SameSite Policy</td><td>Strict</td></tr>
            </table>
            <div class="timeout-wrap">
                <div class="timeout-label">⏳ Session expires after <?= SESSION_TIMEOUT ?>s of inactivity (bar drains on page load)</div>
                <div class="progress"><div class="progress-bar"></div></div>
            </div>
        </div>
        <div class="note">
            💡 <strong>Access note:</strong>
            This page is restricted to the <code>admin</code> role only.
            Attempting to access <code>student_dashboard.php</code> with an admin account
            will simply redirect back to this dashboard.
        </div>
    </main>
</div>
</body>
</html>
