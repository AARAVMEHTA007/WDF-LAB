<?php
// Dynamic sidebar menu based on user role
if (!isset($_SESSION['role'])) return;
$role = $_SESSION['role'];
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<style>
    .sidebar {
        min-height: 100vh;
        background: linear-gradient(180deg, #1e1e2f 0%, #2d2d44 100%);
        padding-top: 0;
        position: fixed;
        width: 260px;
        z-index: 100;
        box-shadow: 4px 0 15px rgba(0,0,0,0.2);
    }
    .sidebar .brand {
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        background: rgba(0,0,0,0.2);
    }
    .sidebar .brand h4 {
        color: #fff;
        margin: 0;
        font-weight: 700;
    }
    .sidebar .brand small {
        color: rgba(255,255,255,0.5);
    }
    .sidebar .nav-link {
        color: rgba(255,255,255,0.7);
        padding: 12px 20px;
        margin: 2px 10px;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    .sidebar .nav-link:hover {
        color: #fff;
        background: rgba(255,255,255,0.1);
    }
    .sidebar .nav-link.active {
        color: #fff;
        background: linear-gradient(135deg, #667eea, #764ba2);
        box-shadow: 0 4px 15px rgba(102,126,234,0.4);
    }
    .sidebar .nav-link i {
        width: 25px;
        text-align: center;
        margin-right: 10px;
    }
    .sidebar .section-title {
        color: rgba(255,255,255,0.4);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 15px 20px 5px;
        font-weight: 600;
    }
    .main-content {
        margin-left: 260px;
        padding: 30px;
        background: #f4f6f9;
        min-height: 100vh;
    }
    .user-info {
        padding: 15px 20px;
        border-top: 1px solid rgba(255,255,255,0.1);
        position: absolute;
        bottom: 0;
        width: 100%;
        background: rgba(0,0,0,0.2);
    }
    .user-info .username {
        color: #fff;
        font-weight: 600;
    }
    .user-info .role-badge {
        font-size: 0.7rem;
    }
</style>

<nav class="sidebar d-flex flex-column">
    <div class="brand">
        <h4><i class="fas fa-shield-alt me-2"></i>AdminPanel</h4>
        <small>Practical 14 - WDF</small>
    </div>

    <div class="flex-grow-1 pt-3">
        <div class="section-title">Main</div>
        <ul class="nav flex-column">
            <!-- Dashboard - visible to all logged-in users, but only admin gets full access -->
            <?php if ($role === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <div class="section-title">Management</div>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'manage_users.php' ? 'active' : '' ?>" href="manage_users.php">
                        <i class="fas fa-users-cog"></i> Manage Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'manage_students.php' ? 'active' : '' ?>" href="manage_students.php">
                        <i class="fas fa-user-graduate"></i> Students
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'manage_events.php' ? 'active' : '' ?>" href="manage_events.php">
                        <i class="fas fa-calendar-alt"></i> Events
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'registrations.php' ? 'active' : '' ?>" href="registrations.php">
                        <i class="fas fa-clipboard-list"></i> Registrations
                    </a>
                </li>

                <div class="section-title">Monitoring</div>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'audit_logs.php' ? 'active' : '' ?>" href="audit_logs.php">
                        <i class="fas fa-history"></i> Audit Logs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'analytics.php' ? 'active' : '' ?>" href="analytics.php">
                        <i class="fas fa-chart-bar"></i> Analytics
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'student'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'student_home.php' ? 'active' : '' ?>" href="student_home.php">
                        <i class="fas fa-home"></i> My Home
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="user-info">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="username"><i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($_SESSION['username']) ?></span><br>
                <span class="badge <?= $role === 'admin' ? 'bg-danger' : 'bg-info' ?> role-badge mt-1">
                    <?= strtoupper($role) ?>
                </span>
            </div>
            <a href="logout.php" class="btn btn-outline-light btn-sm" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</nav>
