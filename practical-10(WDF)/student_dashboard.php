<?php
/**
 * student_dashboard.php – Student-only page (Light Theme)
 * Practical 10: Secure Login/Logout with Sessions, RBAC, Timeout, Remember-Me
 */
require 'db.php';
require 'auth.php';
require_login($pdo, 'student');

$timeoutMins = SESSION_TIMEOUT / 60;
$prevLogin   = $_SESSION['prev_login'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard – Practical 10</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0fdf4;
            color: #1e293b;
            min-height: 100vh;
        }

        /* ── Top Nav ── */
        nav {
            background: #ffffff;
            border-bottom: 1px solid #d1fae5;
            padding: 14px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
        }

        nav .brand {
            font-size: 1.1rem;
            font-weight: 700;
            color: #059669;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        nav .user-info { font-size: .9rem; color: #64748b; }
        nav .user-info strong { color: #1e293b; }

        nav a.logout-btn {
            display: inline-block;
            background: #ef4444;
            color: #fff;
            text-decoration: none;
            padding: 7px 20px;
            border-radius: 7px;
            font-size: .875rem;
            font-weight: 600;
            transition: background .2s;
        }
        nav a.logout-btn:hover { background: #dc2626; }

        /* ── Layout ── */
        .container { max-width: 960px; margin: 36px auto; padding: 0 20px; }

        /* ── Welcome Banner ── */
        .welcome-banner {
            background: linear-gradient(135deg, #059669, #10b981);
            border-radius: 14px;
            padding: 28px 32px;
            margin-bottom: 28px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .welcome-banner h1 { font-size: 1.55rem; font-weight: 700; }
        .welcome-banner p  { font-size: .9rem; opacity: .85; margin-top: 4px; }

        .badge {
            display: inline-block;
            background: rgba(255,255,255,.25);
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-left: 10px;
            vertical-align: middle;
            border: 1px solid rgba(255,255,255,.4);
        }

        /* ── Info Cards ── */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px 22px;
            border: 1px solid #d1fae5;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }

        .card .card-icon  { font-size: 1.7rem; margin-bottom: 10px; }
        .card .card-label {
            font-size: .75rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 600;
        }
        .card .card-value {
            font-size: 1rem;
            color: #1e293b;
            margin-top: 4px;
            font-weight: 600;
        }

        /* ── Session Table ── */
        .session-box {
            background: #ffffff;
            border-radius: 12px;
            padding: 24px 28px;
            border: 1px solid #d1fae5;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
            margin-bottom: 24px;
        }

        .session-box h3 {
            font-size: .95rem;
            color: #475569;
            margin-bottom: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .session-box table { width: 100%; border-collapse: collapse; font-size: .88rem; }

        .session-box td {
            padding: 9px 12px;
            border-bottom: 1px solid #f0fdf4;
        }

        .session-box tr:last-child td { border-bottom: none; }
        .session-box td:first-child { color: #64748b; width: 45%; font-weight: 500; }
        .session-box td:last-child  { color: #1e293b; font-family: 'Courier New', monospace; font-size: .83rem; }

        /* ── Timeout Bar ── */
        .timeout-wrap { margin-top: 20px; }

        .timeout-label {
            font-size: .82rem;
            color: #64748b;
            margin-bottom: 8px;
        }

        .progress {
            background: #d1fae5;
            border-radius: 999px;
            height: 9px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #34d399);
            border-radius: 999px;
            animation: drain <?= SESSION_TIMEOUT ?>s linear forwards;
        }

        @keyframes drain { from { width: 100%; } to { width: 0%; } }

        /* ── Note ── */
        .note {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 10px;
            padding: 14px 18px;
            font-size: .85rem;
            color: #065f46;
            line-height: 1.6;
        }

        .note code {
            background: #d1fae5;
            padding: 1px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: .82rem;
        }
    </style>
</head>
<body>

<nav>
    <span class="brand">🎓 Student Portal</span>
    <span class="user-info">Logged in as <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
    <a href="logout.php" class="logout-btn">Logout</a>
</nav>

<div class="container">

    <div class="welcome-banner">
        <div>
            <h1>
                Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!
                <span class="badge">Student</span>
            </h1>
            <p>You are securely logged in to the Student Portal.</p>
        </div>
    </div>

    <div class="cards">
        <div class="card">
            <div class="card-icon">👤</div>
            <div class="card-label">Logged in as</div>
            <div class="card-value"><?= htmlspecialchars($_SESSION['username']) ?></div>
        </div>
        <div class="card">
            <div class="card-icon">🎓</div>
            <div class="card-label">Role</div>
            <div class="card-value"><?= htmlspecialchars($_SESSION['role']) ?></div>
        </div>
        <div class="card">
            <div class="card-icon">📅</div>
            <div class="card-label">Last Login</div>
            <div class="card-value"><?= $prevLogin ? htmlspecialchars($prevLogin) : 'First login' ?></div>
        </div>
        <div class="card">
            <div class="card-icon">⏱️</div>
            <div class="card-label">Session Timeout</div>
            <div class="card-value"><?= $timeoutMins ?> min<?= $timeoutMins != 1 ? 's' : '' ?> idle</div>
        </div>
    </div>

    <div class="session-box">
        <h3>📋 Current Session Details</h3>
        <table>
            <tr><td>Session ID</td>       <td><?= session_id() ?></td></tr>
            <tr><td>User ID</td>          <td><?= $_SESSION['user_id'] ?></td></tr>
            <tr><td>Username</td>         <td><?= htmlspecialchars($_SESSION['username']) ?></td></tr>
            <tr><td>Role</td>             <td><?= htmlspecialchars($_SESSION['role']) ?></td></tr>
            <tr><td>Last Activity</td>    <td><?= date('Y-m-d H:i:s', $_SESSION['last_activity']) ?></td></tr>
            <tr><td>Cookie HttpOnly</td>  <td>✅ Yes – JS cannot read session ID</td></tr>
            <tr><td>SameSite Policy</td>  <td>Strict</td></tr>
        </table>

        <div class="timeout-wrap">
            <div class="timeout-label">⏳ Session expires after <?= SESSION_TIMEOUT ?>s of inactivity (bar drains on page load)</div>
            <div class="progress"><div class="progress-bar"></div></div>
        </div>
    </div>

    <div class="note">
        💡 <strong>Access note:</strong>
        This page is restricted to the <code>student</code> role only.
        Attempting to access <code>admin_dashboard.php</code> with a student account
        will result in a <strong>403 Forbidden</strong> redirect back to the login page.
    </div>

</div>
</body>
</html>