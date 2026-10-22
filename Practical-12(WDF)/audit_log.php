<?php
/**
 * Audit Log - View all event changes (Advanced Extension)
 * 
 * Tracks CREATE, UPDATE, DELETE actions with timestamps
 */

require_once 'db.php';

$logs = $conn->query("
    SELECT al.*, e.title as event_title 
    FROM audit_log al 
    LEFT JOIN events e ON al.event_id = e.id 
    ORDER BY al.changed_at DESC 
    LIMIT 100
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4f46e5; --secondary: #7c3aed; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        .page-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 2rem 0; color: white; margin-bottom: 2rem;
        }
        .page-header h1 { font-weight: 700; }
        .log-table {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .log-table table { margin-bottom: 0; }
        .log-table th {
            background: #f8fafc;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
            padding: 1rem;
        }
        .log-table td { 
            padding: 0.875rem 1rem; 
            vertical-align: middle;
            font-size: 0.9rem;
        }
        .badge-create { background: #dcfce7; color: #166534; }
        .badge-update { background: #fef3c7; color: #92400e; }
        .badge-delete { background: #fee2e2; color: #991b1b; }
        .json-preview {
            max-width: 300px;
            font-size: 0.75rem;
            background: #f1f5f9;
            padding: 0.5rem;
            border-radius: 6px;
            max-height: 80px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-all;
            font-family: 'Courier New', monospace;
        }
        .empty-state {
            text-align: center; padding: 4rem 2rem; color: #94a3b8;
        }
        .empty-state i { font-size: 4rem; margin-bottom: 1rem; opacity: 0.3; }
    </style>
</head>
<body>

    <div class="page-header">
        <div class="container">
            <a href="index.php" class="text-white text-decoration-none d-inline-flex align-items-center mb-2" style="opacity:0.8;">
                <i class="fas fa-arrow-left me-2"></i>Back to Events
            </a>
            <h1><i class="fas fa-history me-2"></i>Audit Log</h1>
            <p class="mb-0 opacity-75">Track all changes made to events — creates, updates, and deletions.</p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="log-table">
            <?php if ($logs && $logs->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Event</th>
                            <th>Old Values</th>
                            <th>New Values</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-semibold"><?php echo $log['id']; ?></td>
                            <td>
                                <?php
                                $badge_class = 'badge-' . strtolower($log['action']);
                                $icon = $log['action'] === 'CREATE' ? 'fa-plus' : ($log['action'] === 'UPDATE' ? 'fa-edit' : 'fa-trash');
                                ?>
                                <span class="badge rounded-pill <?php echo $badge_class; ?> px-3 py-2">
                                    <i class="fas <?php echo $icon; ?> me-1"></i><?php echo $log['action']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($log['event_title']): ?>
                                    <a href="view_event.php?id=<?php echo $log['event_id']; ?>" class="text-decoration-none fw-semibold">
                                        <?php echo htmlspecialchars($log['event_title']); ?>
                                    </a>
                                <?php elseif ($log['event_id']): ?>
                                    <span class="text-muted">Event #<?php echo $log['event_id']; ?> (deleted)</span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($log['old_values']): ?>
                                    <?php 
                                    $old = json_decode($log['old_values'], true);
                                    if ($old): ?>
                                    <div class="json-preview">
                                        <?php foreach ($old as $k => $v): ?>
                                            <strong><?php echo $k; ?>:</strong> <?php echo htmlspecialchars($v ?: '—'); ?>
<?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($log['new_values']): ?>
                                    <?php 
                                    $new = json_decode($log['new_values'], true);
                                    if ($new): ?>
                                    <div class="json-preview">
                                        <?php foreach ($new as $k => $v): ?>
                                            <strong><?php echo $k; ?>:</strong> <?php echo htmlspecialchars($v ?: '—'); ?>
<?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-muted small">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('d M Y', strtotime($log['changed_at'])); ?>
                                    <br>
                                    <?php echo date('H:i:s', strtotime($log['changed_at'])); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list d-block"></i>
                <h4 class="text-secondary">No Audit Entries Yet</h4>
                <p>Changes will appear here once you create, update, or delete events.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
