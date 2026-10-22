<?php
/**
 * Practical 12: Event Management CRUD with Poster Upload and File Validation
 * 
 * Index Page - View All Events with Category & Status Filters
 * Technologies: PHP, MySQL, Bootstrap 5, Font Awesome
 */

require_once 'db.php';

// Get filter parameters
$category_filter = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

// Build query with filters
$sql = "SELECT * FROM events WHERE 1=1";
if (!empty($category_filter)) {
    $sql .= " AND category = '$category_filter'";
}
if (!empty($status_filter)) {
    $sql .= " AND status = '$status_filter'";
}
$sql .= " ORDER BY event_date DESC";
$result = $conn->query($sql);

// Get categories for filter dropdown
$categories = ['Conference', 'Workshop', 'Seminar', 'Webinar', 'Meetup', 'Concert', 'Sports', 'Other'];

// Flash messages
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System - Practical 12</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #7c3aed;
            --success: #059669;
            --danger: #dc2626;
            --warning: #d97706;
            --bg-light: #f8fafc;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
            --card-shadow-hover: 0 10px 25px rgba(0,0,0,0.1), 0 6px 10px rgba(0,0,0,0.08);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: #1e293b;
        }

        /* Hero/Header Section */
        .hero-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 2.5rem 0;
            color: white;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .hero-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .hero-header::after {
            content: '';
            position: absolute;
            bottom: -60%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .hero-header h1 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .hero-header p {
            opacity: 0.9;
            font-size: 1.05rem;
        }

        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: var(--card-shadow);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e2e8f0;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-shadow-hover);
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .stat-card .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
        }
        .stat-card .stat-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }

        /* Event Cards */
        .event-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-shadow-hover);
        }
        .event-card .poster-container {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .event-card .poster-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .event-card:hover .poster-container img {
            transform: scale(1.05);
        }
        .event-card .poster-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.8);
        }
        .event-card .poster-placeholder i {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        .event-card .poster-placeholder span {
            font-size: 0.85rem;
            font-weight: 500;
        }
        .event-card .status-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .event-card .category-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(0,0,0,0.5);
            color: white;
            backdrop-filter: blur(4px);
        }
        .badge-open {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-closed {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .event-card .card-body {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .event-card .event-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .event-card .event-desc {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }
        .event-card .event-meta {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            margin-bottom: 1rem;
        }
        .event-card .event-meta span {
            font-size: 0.825rem;
            color: #475569;
        }
        .event-card .event-meta i {
            width: 18px;
            color: var(--primary);
            margin-right: 6px;
        }
        .event-card .card-actions {
            display: flex;
            gap: 0.5rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }

        /* Buttons */
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .btn-primary-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
            color: white;
        }
        .btn-sm-action {
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            border: none;
            transition: all 0.2s;
        }
        .btn-view {
            background: #eff6ff;
            color: #2563eb;
        }
        .btn-view:hover {
            background: #2563eb;
            color: white;
        }
        .btn-edit {
            background: #fef3c7;
            color: #d97706;
        }
        .btn-edit:hover {
            background: #d97706;
            color: white;
        }
        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }
        .btn-delete:hover {
            background: #dc2626;
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .empty-state h4 {
            color: #64748b;
            font-weight: 600;
        }

        /* Alert custom styling */
        .alert-custom {
            border-radius: 10px;
            border: none;
            font-weight: 500;
        }

        /* Footer */
        .footer {
            background: #1e293b;
            color: #94a3b8;
            padding: 1.5rem 0;
            margin-top: 3rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-header h1 { font-size: 1.5rem; }
            .event-card .poster-container { height: 160px; }
        }
    </style>
</head>
<body>

    <!-- ==================== HEADER ==================== -->
    <div class="hero-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1><i class="fas fa-calendar-alt me-2"></i>Event Management System</h1>
                    <p class="mb-0">Practical 12 — CRUD with Poster Upload & File Validation</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="add_event.php" class="btn btn-light btn-lg rounded-pill px-4">
                        <i class="fas fa-plus me-2"></i>Add New Event
                    </a>
                    <a href="audit_log.php" class="btn btn-outline-light btn-lg rounded-pill px-4">
                        <i class="fas fa-history me-2"></i>Audit Log
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">

        <!-- ==================== ALERTS ==================== -->
        <?php if ($success): ?>
        <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- ==================== STATS CARDS ==================== -->
        <?php
        $total_events = $conn->query("SELECT COUNT(*) as cnt FROM events")->fetch_assoc()['cnt'];
        $open_events = $conn->query("SELECT COUNT(*) as cnt FROM events WHERE status='Open'")->fetch_assoc()['cnt'];
        $closed_events = $conn->query("SELECT COUNT(*) as cnt FROM events WHERE status='Closed'")->fetch_assoc()['cnt'];
        $with_posters = $conn->query("SELECT COUNT(*) as cnt FROM events WHERE poster_image IS NOT NULL AND poster_image != ''")->fetch_assoc()['cnt'];
        ?>
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $total_events; ?></div>
                        <div class="stat-label">Total Events</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: #dcfce7; color: #166534;">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $open_events; ?></div>
                        <div class="stat-label">Open Events</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-door-closed"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $closed_events; ?></div>
                        <div class="stat-label">Closed Events</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="fas fa-image"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $with_posters; ?></div>
                        <div class="stat-label">With Posters</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== FILTERS ==================== -->
        <div class="filter-section">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-tag me-1 text-primary"></i>Filter by Category
                    </label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo ($category_filter === $cat) ? 'selected' : ''; ?>>
                            <?php echo $cat; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-toggle-on me-1 text-primary"></i>Filter by Status
                    </label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Open" <?php echo ($status_filter === 'Open') ? 'selected' : ''; ?>>🟢 Open</option>
                        <option value="Closed" <?php echo ($status_filter === 'Closed') ? 'selected' : ''; ?>>🔴 Closed</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom flex-fill">
                        <i class="fas fa-search me-1"></i>Apply Filters
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- ==================== EVENTS GRID ==================== -->
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($event = $result->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <div class="event-card">
                    <!-- Poster Image -->
                    <div class="poster-container">
                        <?php if (!empty($event['poster_image']) && file_exists('uploads/' . $event['poster_image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($event['poster_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($event['title']); ?> Poster">
                        <?php else: ?>
                            <div class="poster-placeholder">
                                <i class="fas fa-image"></i>
                                <span>No Poster Uploaded</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Status Badge -->
                        <span class="status-badge <?php echo ($event['status'] === 'Open') ? 'badge-open' : 'badge-closed'; ?>">
                            <i class="fas <?php echo ($event['status'] === 'Open') ? 'fa-unlock' : 'fa-lock'; ?> me-1"></i>
                            <?php echo $event['status']; ?>
                        </span>
                        
                        <!-- Category Badge -->
                        <span class="category-badge">
                            <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($event['category']); ?>
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">
                        <h5 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                        <p class="event-desc"><?php echo htmlspecialchars($event['description']); ?></p>
                        
                        <div class="event-meta">
                            <span>
                                <i class="fas fa-calendar"></i>
                                <?php echo date('d M Y', strtotime($event['event_date'])); ?>
                            </span>
                            <span>
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($event['venue']); ?>
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card-actions">
                            <a href="view_event.php?id=<?php echo $event['id']; ?>" class="btn btn-sm-action btn-view flex-fill text-center">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                            <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-sm-action btn-edit flex-fill text-center">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            <a href="delete_event.php?id=<?php echo $event['id']; ?>" 
                               class="btn btn-sm-action btn-delete flex-fill text-center"
                               onclick="return confirm('Are you sure you want to delete this event?');">
                                <i class="fas fa-trash me-1"></i>Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-xmark d-block"></i>
            <h4>No Events Found</h4>
            <p>There are no events matching your criteria. Try adjusting filters or add a new event.</p>
            <a href="add_event.php" class="btn btn-primary-custom mt-2">
                <i class="fas fa-plus me-2"></i>Add Your First Event
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- ==================== FOOTER ==================== -->
    <div class="footer text-center">
        <div class="container">
            <p class="mb-1">
                <i class="fas fa-code me-1"></i>
                <strong>Practical 12</strong> — Event Management CRUD with Poster Upload & File Validation
            </p>
            <p class="mb-0" style="font-size: 0.85rem;">
                Built with PHP, MySQL, Bootstrap 5 | &copy; <?php echo date('Y'); ?>
            </p>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
