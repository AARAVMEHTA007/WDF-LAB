<?php
require_once 'config.php';
requireAdmin();

logAudit($conn, $_SESSION['user_id'], 'VIEW_AUDIT_LOG', 'Admin viewed audit logs');

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

$totalLogs = $conn->query("SELECT COUNT(*) as cnt FROM audit_logs")->fetch_assoc()['cnt'];
$totalPages = ceil($totalLogs / $perPage);

$logs = $conn->query("
    SELECT a.*, u.username 
    FROM audit_logs a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT $perPage OFFSET $offset
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header mb-4">
            <h2><i class="fas fa-history me-2"></i>Audit Logs</h2>
            <p class="text-muted">Track all admin actions for traceability (<?= $totalLogs ?> total entries)</p>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius:15px;">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td>
                                <span class="badge bg-primary"><?= htmlspecialchars($log['username']) ?></span>
                            </td>
                            <td>
                                <?php
                                $actionColors = [
                                    'LOGIN' => 'success', 'LOGOUT' => 'secondary',
                                    'VIEW_DASHBOARD' => 'info', 'VIEW_AUDIT_LOG' => 'info',
                                    'TOGGLE_USER_STATUS' => 'warning', 'VIEW_USERS' => 'info',
                                    'VIEW_STUDENTS' => 'info', 'VIEW_EVENTS' => 'info',
                                    'VIEW_REGISTRATIONS' => 'info', 'VIEW_ANALYTICS' => 'info'
                                ];
                                $color = $actionColors[$log['action']] ?? 'dark';
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= htmlspecialchars($log['action']) ?></span>
                            </td>
                            <td><small><?= htmlspecialchars($log['details']) ?></small></td>
                            <td><code><?= htmlspecialchars($log['ip_address']) ?></code></td>
                            <td><small class="text-muted"><?= date('d M Y, h:i:s A', strtotime($log['created_at'])) ?></small></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-transparent">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
