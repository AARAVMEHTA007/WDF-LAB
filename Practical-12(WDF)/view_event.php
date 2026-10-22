<?php
/**
 * View Event - Detailed event view with poster display
 */

require_once 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: index.php?error=" . urlencode("Invalid event ID."));
    exit();
}

$event = $conn->query("SELECT * FROM events WHERE id = $id")->fetch_assoc();
if (!$event) {
    header("Location: index.php?error=" . urlencode("Event not found."));
    exit();
}

// Get recent audit logs for this event
$audit_logs = $conn->query("SELECT * FROM audit_log WHERE event_id = $id ORDER BY changed_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> - Event Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4f46e5; --secondary: #7c3aed; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        
        .event-hero {
            position: relative;
            height: 350px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        .event-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.9;
        }
        .event-hero .overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 2rem;
            color: white;
        }
        .event-hero .placeholder-hero {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.7);
        }
        .event-hero .placeholder-hero i { font-size: 4rem; margin-bottom: 1rem; }
        .event-hero .back-btn {
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 10;
        }
        .event-hero .action-btns {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 10;
        }
        
        .detail-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .detail-card h5 { font-weight: 700; color: #1e293b; }
        
        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 0.875rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .info-row .info-label {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-row .info-value {
            font-weight: 600;
            color: #1e293b;
        }
        
        .badge-open-lg {
            background: #dcfce7;
            color: #166534;
            padding: 0.5rem 1.25rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .badge-closed-lg {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.5rem 1.25rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .audit-timeline {
            position: relative;
            padding-left: 2rem;
        }
        .audit-timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }
        .audit-item {
            position: relative;
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid #f8fafc;
        }
        .audit-item::before {
            content: '';
            position: absolute;
            left: -1.625rem;
            top: 4px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid var(--primary);
            background: white;
        }
        .audit-item.create::before { border-color: #059669; background: #dcfce7; }
        .audit-item.update::before { border-color: #d97706; background: #fef3c7; }
        .audit-item.delete::before { border-color: #dc2626; background: #fee2e2; }
    </style>
</head>
<body>

    <!-- Event Hero -->
    <div class="event-hero">
        <?php if (!empty($event['poster_image']) && file_exists('uploads/' . $event['poster_image'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($event['poster_image']); ?>" 
                 alt="<?php echo htmlspecialchars($event['title']); ?>">
        <?php else: ?>
            <div class="placeholder-hero">
                <i class="fas fa-image"></i>
                <h4>No Poster Image</h4>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="back-btn">
            <a href="index.php" class="btn btn-light rounded-pill px-3 shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>

        <!-- Action Buttons -->
        <div class="action-btns d-flex gap-2">
            <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-warning rounded-pill px-3 shadow-sm">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="delete_event.php?id=<?php echo $event['id']; ?>" 
               class="btn btn-danger rounded-pill px-3 shadow-sm"
               onclick="return confirm('Delete this event?');">
                <i class="fas fa-trash me-1"></i>Delete
            </a>
        </div>

        <!-- Title Overlay -->
        <div class="overlay">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-2">
                    <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($event['category']); ?>
                </span>
                <span class="<?php echo ($event['status'] === 'Open') ? 'badge-open-lg' : 'badge-closed-lg'; ?>">
                    <i class="fas <?php echo ($event['status'] === 'Open') ? 'fa-unlock' : 'fa-lock'; ?> me-1"></i>
                    <?php echo $event['status']; ?>
                </span>
            </div>
            <h1 class="fw-bold mb-0" style="font-size:1.75rem;">
                <?php echo htmlspecialchars($event['title']); ?>
            </h1>
        </div>
    </div>

    <!-- Content -->
    <div class="container py-4">
        <div class="row g-4">
            <!-- Main Details -->
            <div class="col-lg-8">
                <!-- Description -->
                <div class="detail-card">
                    <h5><i class="fas fa-align-left text-primary me-2"></i>Description</h5>
                    <p class="text-secondary mb-0" style="line-height:1.8;">
                        <?php echo !empty($event['description']) ? nl2br(htmlspecialchars($event['description'])) : '<em class="text-muted">No description provided.</em>'; ?>
                    </p>
                </div>

                <!-- Audit Log History (Advanced) -->
                <?php if ($audit_logs && $audit_logs->num_rows > 0): ?>
                <div class="detail-card">
                    <h5><i class="fas fa-history text-primary me-2"></i>Change History</h5>
                    <div class="audit-timeline mt-3">
                        <?php while ($log = $audit_logs->fetch_assoc()): ?>
                        <div class="audit-item <?php echo strtolower($log['action']); ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-<?php echo $log['action'] === 'CREATE' ? 'success' : ($log['action'] === 'UPDATE' ? 'warning' : 'danger'); ?> rounded-pill">
                                        <?php echo $log['action']; ?>
                                    </span>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('d M Y, H:i:s', strtotime($log['changed_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="detail-card">
                    <h5><i class="fas fa-info-circle text-primary me-2"></i>Event Details</h5>
                    
                    <div class="info-row">
                        <div class="info-icon" style="background:#eff6ff; color:#2563eb;">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div>
                            <div class="info-label">Date</div>
                            <div class="info-value"><?php echo date('l, d F Y', strtotime($event['event_date'])); ?></div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon" style="background:#fef3c7; color:#d97706;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <div class="info-label">Venue</div>
                            <div class="info-value"><?php echo htmlspecialchars($event['venue']); ?></div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon" style="background:#f3e8ff; color:#7c3aed;">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div>
                            <div class="info-label">Category</div>
                            <div class="info-value"><?php echo htmlspecialchars($event['category']); ?></div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon" style="background:<?php echo ($event['status'] === 'Open') ? '#dcfce7' : '#fee2e2'; ?>; color:<?php echo ($event['status'] === 'Open') ? '#166534' : '#991b1b'; ?>;">
                            <i class="fas <?php echo ($event['status'] === 'Open') ? 'fa-door-open' : 'fa-door-closed'; ?>"></i>
                        </div>
                        <div>
                            <div class="info-label">Status</div>
                            <div class="info-value"><?php echo $event['status']; ?></div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon" style="background:#dcfce7; color:#059669;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="info-label">Created</div>
                            <div class="info-value"><?php echo date('d M Y, H:i', strtotime($event['created_at'])); ?></div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon" style="background:#fef3c7; color:#d97706;">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div>
                            <div class="info-label">Last Updated</div>
                            <div class="info-value"><?php echo date('d M Y, H:i', strtotime($event['updated_at'])); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Poster Info -->
                <div class="detail-card">
                    <h5><i class="fas fa-image text-primary me-2"></i>Poster</h5>
                    <?php if (!empty($event['poster_image']) && file_exists('uploads/' . $event['poster_image'])): ?>
                        <p class="small text-muted mb-2">
                            <i class="fas fa-file me-1"></i>
                            <?php echo htmlspecialchars($event['poster_image']); ?>
                        </p>
                        <img src="uploads/<?php echo htmlspecialchars($event['poster_image']); ?>" 
                             class="img-fluid rounded-3 shadow-sm" alt="Event Poster">
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-image text-muted d-block mb-2" style="font-size:2rem; opacity:0.3;"></i>
                            <span class="text-muted small">No poster uploaded</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
