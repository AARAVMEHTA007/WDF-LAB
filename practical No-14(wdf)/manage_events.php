<?php
require_once 'config.php';
requireAdmin();

logAudit($conn, $_SESSION['user_id'], 'VIEW_EVENTS', 'Admin viewed events page');

$events = $conn->query("
    SELECT e.*, COUNT(r.id) as reg_count 
    FROM events e 
    LEFT JOIN registrations r ON e.id = r.event_id 
    GROUP BY e.id 
    ORDER BY e.event_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header mb-4">
            <h2><i class="fas fa-calendar-alt me-2"></i>Events</h2>
            <p class="text-muted">All scheduled events</p>
        </div>

        <div class="row g-4">
            <?php while ($e = $events->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius:15px;">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-calendar-check text-primary me-2"></i>
                            <?= htmlspecialchars($e['title']) ?>
                        </h5>
                        <p class="text-muted small"><?= htmlspecialchars($e['description']) ?></p>
                        <hr>
                        <p class="mb-1">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                            <?= htmlspecialchars($e['location']) ?>
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-calendar me-2 text-info"></i>
                            <?= date('d M Y', strtotime($e['event_date'])) ?>
                        </p>
                        <p class="mb-0">
                            <span class="badge bg-primary"><?= $e['reg_count'] ?> registrations</span>
                        </p>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
