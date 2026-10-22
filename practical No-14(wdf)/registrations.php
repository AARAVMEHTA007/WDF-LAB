<?php
require_once 'config.php';
requireAdmin();

logAudit($conn, $_SESSION['user_id'], 'VIEW_REGISTRATIONS', 'Admin viewed registrations page');

$registrations = $conn->query("
    SELECT r.id, s.name AS student_name, s.email AS student_email, e.title AS event_title, 
           e.event_date, r.registered_at
    FROM registrations r
    JOIN students s ON r.student_id = s.id
    JOIN events e ON r.event_id = e.id
    ORDER BY r.registered_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrations - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header mb-4">
            <h2><i class="fas fa-clipboard-list me-2"></i>Registrations</h2>
            <p class="text-muted">Student event registrations</p>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius:15px;">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Event</th>
                            <th>Event Date</th>
                            <th>Registered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = $registrations->fetch_assoc()): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><i class="fas fa-user-circle text-primary me-1"></i><?= htmlspecialchars($r['student_name']) ?></td>
                            <td><?= htmlspecialchars($r['student_email']) ?></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($r['event_title']) ?></span></td>
                            <td><?= date('d M Y', strtotime($r['event_date'])) ?></td>
                            <td><small class="text-muted"><?= date('d M Y, h:i A', strtotime($r['registered_at'])) ?></small></td>
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
