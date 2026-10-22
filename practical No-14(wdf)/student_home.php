<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// If admin tries to access student home, redirect to dashboard
if (isAdmin()) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header mb-4">
            <h2><i class="fas fa-home me-2"></i>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
            <p class="text-muted">Student Dashboard</p>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm" style="border-radius:15px;">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-user-graduate fa-5x text-primary mb-4"></i>
                        <h3>Hello, <?= htmlspecialchars($_SESSION['username']) ?></h3>
                        <p class="text-muted fs-5">You are logged in as a <strong>Student</strong>.</p>
                        <p class="text-muted">You have limited access. Admin-only features like the dashboard, user management, audit logs, and analytics are restricted.</p>
                        <hr class="my-4">
                        <div class="alert alert-info d-inline-block">
                            <i class="fas fa-info-circle me-2"></i>
                            Contact the administrator if you need elevated access.
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm" style="border-radius:15px;">
                    <div class="card-header bg-transparent border-bottom p-3">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Your Info</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Username:</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>
                        <p><strong>Role:</strong> <span class="badge bg-info">Student</span></p>
                        <p><strong>Session Started:</strong> <?= date('d M Y, h:i A') ?></p>
                        <a href="logout.php" class="btn btn-outline-danger w-100 mt-3">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
