<?php
require_once 'config.php';
requireAdmin();

// Handle status toggle (active/inactive)
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $userId = (int)$_GET['toggle_status'];
    
    // Don't allow toggling own status
    if ($userId !== $_SESSION['user_id']) {
        $user = $conn->query("SELECT username, status FROM users WHERE id = $userId")->fetch_assoc();
        if ($user) {
            $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $newStatus, $userId);
            $stmt->execute();
            $stmt->close();
            
            logAudit($conn, $_SESSION['user_id'], 'TOGGLE_USER_STATUS', 
                     "Changed {$user['username']} status from {$user['status']} to $newStatus");
        }
    }
    header("Location: manage_users.php");
    exit();
}

logAudit($conn, $_SESSION['user_id'], 'VIEW_USERS', 'Admin viewed user management page');

// Fetch all users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header mb-4">
            <h2><i class="fas fa-users-cog me-2"></i>Manage Users</h2>
            <p class="text-muted">Toggle user active/inactive status</p>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius:15px;">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td>
                                <i class="fas fa-user-circle text-muted me-1"></i>
                                <?= htmlspecialchars($user['username']) ?>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span class="badge bg-primary">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-info' ?>">
                                    <?= strtoupper($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $user['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                    <i class="fas <?= $user['status'] === 'active' ? 'fa-check-circle' : 'fa-ban' ?> me-1"></i>
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </td>
                            <td><small><?= date('d M Y, h:i A', strtotime($user['created_at'])) ?></small></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="manage_users.php?toggle_status=<?= $user['id'] ?>" 
                                       class="btn btn-sm <?= $user['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                       onclick="return confirm('Are you sure you want to <?= $user['status'] === 'active' ? 'deactivate' : 'activate' ?> this user?')">
                                        <i class="fas <?= $user['status'] === 'active' ? 'fa-user-slash' : 'fa-user-check' ?> me-1"></i>
                                        <?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted"><small>N/A</small></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
