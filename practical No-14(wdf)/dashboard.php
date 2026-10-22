<?php
require_once 'config.php';
requireAdmin();

// Log dashboard view
logAudit($conn, $_SESSION['user_id'], 'VIEW_DASHBOARD', 'Admin viewed the dashboard');

// Fetch statistics from database
$totalUsers = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch_assoc()['cnt'];
$totalStudents = $conn->query("SELECT COUNT(*) as cnt FROM students")->fetch_assoc()['cnt'];
$totalEvents = $conn->query("SELECT COUNT(*) as cnt FROM events")->fetch_assoc()['cnt'];
$totalRegistrations = $conn->query("SELECT COUNT(*) as cnt FROM registrations")->fetch_assoc()['cnt'];
$activeUsers = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE status='active'")->fetch_assoc()['cnt'];
$inactiveUsers = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE status='inactive'")->fetch_assoc()['cnt'];

// Latest 5 registrations
$latestRegs = $conn->query("
    SELECT r.id, s.name AS student_name, e.title AS event_title, r.registered_at
    FROM registrations r
    JOIN students s ON r.student_id = s.id
    JOIN events e ON r.event_id = e.id
    ORDER BY r.registered_at DESC
    LIMIT 5
");

// Latest 5 audit logs
$latestLogs = $conn->query("
    SELECT a.action, a.details, a.created_at, u.username
    FROM audit_logs a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 5
");

// Registration data by month for chart
$monthlyRegs = $conn->query("
    SELECT MONTH(registered_at) AS month, COUNT(*) AS count
    FROM registrations
    WHERE YEAR(registered_at) = YEAR(CURDATE())
    GROUP BY MONTH(registered_at)
    ORDER BY month
");
$chartLabels = [];
$chartData = [];
$monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
while ($row = $monthlyRegs->fetch_assoc()) {
    $chartLabels[] = $monthNames[(int)$row['month']];
    $chartData[] = (int)$row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .stat-card .card-body {
            padding: 25px;
        }
        .stat-card .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .stat-card h2 {
            font-weight: 700;
            font-size: 2rem;
        }
        .bg-gradient-primary { background: linear-gradient(135deg, #667eea, #764ba2); }
        .bg-gradient-success { background: linear-gradient(135deg, #11998e, #38ef7d); }
        .bg-gradient-warning { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .bg-gradient-info { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .page-header {
            margin-bottom: 30px;
        }
        .page-header h2 {
            font-weight: 700;
            color: #2d2d44;
        }
        .table-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .table-card .card-header {
            background: transparent;
            border-bottom: 1px solid #eee;
            padding: 20px;
        }
        .table-card .card-header h5 {
            margin: 0;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                <p class="text-muted mb-0">Welcome back, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</p>
            </div>
            <span class="badge bg-secondary fs-6"><i class="fas fa-clock me-1"></i><?= date('d M Y, h:i A') ?></span>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase fw-bold">Total Users</p>
                                <h2 class="mb-0"><?= $totalUsers ?></h2>
                                <small class="text-success"><i class="fas fa-check-circle"></i> <?= $activeUsers ?> active</small>
                            </div>
                            <div class="icon-box bg-gradient-primary text-white">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase fw-bold">Students</p>
                                <h2 class="mb-0"><?= $totalStudents ?></h2>
                                <small class="text-muted"><i class="fas fa-user-graduate"></i> enrolled</small>
                            </div>
                            <div class="icon-box bg-gradient-success text-white">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase fw-bold">Events</p>
                                <h2 class="mb-0"><?= $totalEvents ?></h2>
                                <small class="text-muted"><i class="fas fa-calendar"></i> scheduled</small>
                            </div>
                            <div class="icon-box bg-gradient-warning text-white">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase fw-bold">Registrations</p>
                                <h2 class="mb-0"><?= $totalRegistrations ?></h2>
                                <small class="text-muted"><i class="fas fa-clipboard-list"></i> total</small>
                            </div>
                            <div class="icon-box bg-gradient-info text-white">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Analytics Chart -->
            <div class="col-lg-8">
                <div class="card table-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-chart-line me-2 text-primary"></i>Event Registrations by Month</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="registrationsChart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <!-- User Status Summary -->
            <div class="col-lg-4">
                <div class="card table-card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie me-2 text-success"></i>User Status</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="userStatusChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Latest Registrations -->
            <div class="col-lg-7">
                <div class="card table-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-clipboard-list me-2 text-info"></i>Latest Registrations</h5>
                        <a href="registrations.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = $latestRegs->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $reg['id'] ?></td>
                                    <td><i class="fas fa-user-circle text-muted me-1"></i><?= htmlspecialchars($reg['student_name']) ?></td>
                                    <td><?= htmlspecialchars($reg['event_title']) ?></td>
                                    <td><small class="text-muted"><?= date('d M Y, h:i A', strtotime($reg['registered_at'])) ?></small></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Latest Audit Logs -->
            <div class="col-lg-5">
                <div class="card table-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-history me-2 text-warning"></i>Recent Activity</h5>
                        <a href="audit_logs.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php while ($log = $latestLogs->fetch_assoc()): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <strong class="text-primary"><?= htmlspecialchars($log['action']) ?></strong>
                                    <small class="text-muted"><?= date('d M, h:i A', strtotime($log['created_at'])) ?></small>
                                </div>
                                <small class="text-muted"><?= htmlspecialchars($log['details']) ?></small><br>
                                <small class="badge bg-light text-dark"><i class="fas fa-user"></i> <?= htmlspecialchars($log['username']) ?></small>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Registrations by Month Chart
        new Chart(document.getElementById('registrationsChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?= json_encode($chartData) ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.7)',
                    borderColor: '#667eea',
                    borderWidth: 2,
                    borderRadius: 8,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });

        // User Status Doughnut Chart
        new Chart(document.getElementById('userStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive'],
                datasets: [{
                    data: [<?= $activeUsers ?>, <?= $inactiveUsers ?>],
                    backgroundColor: ['#38ef7d', '#f5576c'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>
</html>
