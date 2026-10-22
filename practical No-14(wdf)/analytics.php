<?php
require_once 'config.php';
requireAdmin();

logAudit($conn, $_SESSION['user_id'], 'VIEW_ANALYTICS', 'Admin viewed analytics page');

// Registrations by month
$monthlyRegs = $conn->query("
    SELECT MONTH(registered_at) AS month, COUNT(*) AS count
    FROM registrations
    WHERE YEAR(registered_at) = YEAR(CURDATE())
    GROUP BY MONTH(registered_at)
    ORDER BY month
");
$monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$regLabels = [];
$regData = [];
while ($row = $monthlyRegs->fetch_assoc()) {
    $regLabels[] = $monthNames[(int)$row['month']];
    $regData[] = (int)$row['count'];
}

// Students per course
$courseDist = $conn->query("
    SELECT course, COUNT(*) AS count FROM students GROUP BY course ORDER BY count DESC
");
$courseLabels = [];
$courseData = [];
while ($row = $courseDist->fetch_assoc()) {
    $courseLabels[] = $row['course'];
    $courseData[] = (int)$row['count'];
}

// Events with registration counts
$eventRegs = $conn->query("
    SELECT e.title, COUNT(r.id) AS reg_count
    FROM events e
    LEFT JOIN registrations r ON e.id = r.event_id
    GROUP BY e.id
    ORDER BY reg_count DESC
");
$eventLabels = [];
$eventData = [];
while ($row = $eventRegs->fetch_assoc()) {
    $eventLabels[] = $row['title'];
    $eventData[] = (int)$row['reg_count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header mb-4">
            <h2><i class="fas fa-chart-bar me-2"></i>Analytics</h2>
            <p class="text-muted">Event registration analytics and insights</p>
        </div>

        <div class="row g-4 mb-4">
            <!-- Registrations by Month (Line Chart) -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius:15px;">
                    <div class="card-header bg-transparent border-bottom p-3">
                        <h5 class="mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Registrations by Month (<?= date('Y') ?>)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" height="130"></canvas>
                    </div>
                </div>
            </div>

            <!-- Students by Course (Pie Chart) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm" style="border-radius:15px;">
                    <div class="card-header bg-transparent border-bottom p-3">
                        <h5 class="mb-0"><i class="fas fa-chart-pie text-success me-2"></i>Students by Course</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="courseChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Event Registrations (Horizontal Bar) -->
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm" style="border-radius:15px;">
                    <div class="card-header bg-transparent border-bottom p-3">
                        <h5 class="mb-0"><i class="fas fa-chart-bar text-warning me-2"></i>Registrations per Event</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="eventChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Monthly Registrations Line Chart
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($regLabels) ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?= json_encode($regData) ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#667eea'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // Course Distribution Doughnut
        new Chart(document.getElementById('courseChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($courseLabels) ?>,
                datasets: [{
                    data: <?= json_encode($courseData) ?>,
                    backgroundColor: ['#667eea', '#38ef7d', '#f5576c', '#4facfe', '#f093fb', '#ffd93d'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
            }
        });

        // Event Registrations Horizontal Bar
        new Chart(document.getElementById('eventChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($eventLabels) ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?= json_encode($eventData) ?>,
                    backgroundColor: ['#667eea', '#38ef7d', '#f5576c', '#4facfe', '#f093fb'],
                    borderRadius: 8,
                    barThickness: 30
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
</body>
</html>
