<?php
/**
 * Edit Event - Update event details and poster
 * 
 * Features:
 * - Pre-filled form with existing data
 * - Replace existing poster with new upload
 * - File validation on update
 * - Audit logging for changes (Advanced)
 */

require_once 'db.php';

$errors = [];
$categories = ['Conference', 'Workshop', 'Seminar', 'Webinar', 'Meetup', 'Concert', 'Sports', 'Other'];

// Get event ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: index.php?error=" . urlencode("Invalid event ID."));
    exit();
}

// Fetch existing event
$event = $conn->query("SELECT * FROM events WHERE id = $id")->fetch_assoc();
if (!$event) {
    header("Location: index.php?error=" . urlencode("Event not found."));
    exit();
}

// Store old values for audit log
$old_values = json_encode([
    'title' => $event['title'],
    'description' => $event['description'],
    'category' => $event['category'],
    'event_date' => $event['event_date'],
    'venue' => $event['venue'],
    'status' => $event['status'],
    'poster_image' => $event['poster_image']
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $title = trim($conn->real_escape_string($_POST['title'] ?? ''));
    $description = trim($conn->real_escape_string($_POST['description'] ?? ''));
    $category = $conn->real_escape_string($_POST['category'] ?? 'Other');
    $event_date = $conn->real_escape_string($_POST['event_date'] ?? '');
    $venue = trim($conn->real_escape_string($_POST['venue'] ?? ''));
    $status = $conn->real_escape_string($_POST['status'] ?? 'Open');

    // Validation
    if (empty($title)) $errors[] = "Event title is required.";
    if (empty($event_date)) $errors[] = "Event date is required.";
    if (empty($venue)) $errors[] = "Venue is required.";
    if (!in_array($category, $categories)) $errors[] = "Invalid category.";
    if (!in_array($status, ['Open', 'Closed'])) $errors[] = "Invalid status.";

    // File upload handling
    $poster_image = $event['poster_image']; // Keep existing by default
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_file_size = 2 * 1024 * 1024;
    $upload_dir = 'uploads/';

    // Check if "remove poster" was requested
    if (isset($_POST['remove_poster']) && $_POST['remove_poster'] === '1') {
        if (!empty($event['poster_image']) && file_exists($upload_dir . $event['poster_image'])) {
            unlink($upload_dir . $event['poster_image']);
        }
        $poster_image = '';
    }

    // Handle new file upload
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['poster'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload failed. Error code: " . $file['error'];
        } else {
            $file_type = mime_content_type($file['tmp_name']);
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "Invalid file type! Only JPG, PNG, GIF, and WEBP allowed.";
            }
            if (!in_array($file_ext, $allowed_extensions)) {
                $errors[] = "Invalid file extension!";
            }
            if ($file['size'] > $max_file_size) {
                $errors[] = "File size exceeds 2MB limit.";
            }
            if (getimagesize($file['tmp_name']) === false) {
                $errors[] = "Not a valid image file.";
            }

            if (empty($errors)) {
                // Delete old poster
                if (!empty($event['poster_image']) && file_exists($upload_dir . $event['poster_image'])) {
                    unlink($upload_dir . $event['poster_image']);
                }

                // Save new poster (Advanced: renamed)
                $new_filename = 'event_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $poster_image = $new_filename;
                } else {
                    $errors[] = "Failed to save uploaded file.";
                }
            }
        }
    }

    // Update database
    if (empty($errors)) {
        $escaped_poster = $conn->real_escape_string($poster_image);
        $sql = "UPDATE events SET 
                    title = '$title', 
                    description = '$description', 
                    category = '$category', 
                    event_date = '$event_date', 
                    venue = '$venue', 
                    status = '$status', 
                    poster_image = '$escaped_poster' 
                WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            // Audit log (Advanced)
            $new_values = json_encode([
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'event_date' => $event_date,
                'venue' => $venue,
                'status' => $status,
                'poster_image' => $poster_image
            ]);
            $escaped_old = $conn->real_escape_string($old_values);
            $escaped_new = $conn->real_escape_string($new_values);
            $conn->query("INSERT INTO audit_log (event_id, action, old_values, new_values) VALUES ($id, 'UPDATE', '$escaped_old', '$escaped_new')");

            header("Location: index.php?success=" . urlencode("Event '{$title}' updated successfully!"));
            exit();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    } else {
        // Re-fetch to keep form data
        $event['title'] = $_POST['title'] ?? $event['title'];
        $event['description'] = $_POST['description'] ?? $event['description'];
        $event['category'] = $_POST['category'] ?? $event['category'];
        $event['event_date'] = $_POST['event_date'] ?? $event['event_date'];
        $event['venue'] = $_POST['venue'] ?? $event['venue'];
        $event['status'] = $_POST['status'] ?? $event['status'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4f46e5; --secondary: #7c3aed; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        .page-header {
            background: linear-gradient(135deg, #d97706 0%, #ea580c 100%);
            padding: 2rem 0; color: white; margin-bottom: 2rem;
        }
        .page-header h1 { font-weight: 700; }
        .form-card {
            background: white; border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; padding: 2rem;
        }
        .form-label { font-weight: 600; color: #374151; }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
        }
        .current-poster {
            border: 2px solid #e2e8f0; border-radius: 12px; overflow: hidden;
            position: relative; display: inline-block;
        }
        .current-poster img { max-height: 200px; display: block; }
        .current-poster .remove-overlay {
            position: absolute; top: 8px; right: 8px;
        }
        .upload-zone {
            border: 2px dashed #cbd5e1; border-radius: 12px; padding: 1.5rem;
            text-align: center; cursor: pointer; transition: all 0.3s; background: #f8fafc;
        }
        .upload-zone:hover { border-color: var(--primary); background: #eff6ff; }
        .upload-zone i { font-size: 2rem; color: #94a3b8; margin-bottom: 0.5rem; }
        .file-info {
            background: #f1f5f9; border-radius: 8px; padding: 0.75rem 1rem; margin-top: 0.75rem; display: none;
        }
        .preview-img {
            max-height: 200px; border-radius: 8px; margin-top: 1rem; display: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .btn-submit {
            background: linear-gradient(135deg, #d97706, #ea580c); border: none;
            color: white; padding: 0.75rem 2rem; border-radius: 10px; font-weight: 600; transition: all 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px); box-shadow: 0 6px 16px rgba(217,119,6,0.4); color: white;
        }
    </style>
</head>
<body>

    <div class="page-header">
        <div class="container">
            <a href="index.php" class="text-white text-decoration-none d-inline-flex align-items-center mb-2" style="opacity:0.8;">
                <i class="fas fa-arrow-left me-2"></i>Back to Events
            </a>
            <h1><i class="fas fa-edit me-2"></i>Edit Event</h1>
            <p class="mb-0 opacity-75">Updating: <strong><?php echo htmlspecialchars($event['title']); ?></strong></p>
        </div>
    </div>

    <div class="container pb-5">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" style="border-radius:10px; border:none;">
            <h6 class="alert-heading fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Validation Errors</h6>
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="remove_poster" id="removePoster" value="0">

                        <div class="mb-3">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading me-1 text-primary"></i>Event Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($event['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1 text-primary"></i>Description
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($event['description']); ?></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="category" class="form-label">
                                    <i class="fas fa-tag me-1 text-primary"></i>Category <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="category" name="category" required>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo ($event['category'] === $cat) ? 'selected' : ''; ?>>
                                        <?php echo $cat; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="event_date" class="form-label">
                                    <i class="fas fa-calendar me-1 text-primary"></i>Event Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="event_date" name="event_date" 
                                       value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-8">
                                <label for="venue" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1 text-primary"></i>Venue <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="venue" name="venue" 
                                       value="<?php echo htmlspecialchars($event['venue']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">
                                    <i class="fas fa-toggle-on me-1 text-primary"></i>Status
                                </label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Open" <?php echo ($event['status'] === 'Open') ? 'selected' : ''; ?>>🟢 Open</option>
                                    <option value="Closed" <?php echo ($event['status'] === 'Closed') ? 'selected' : ''; ?>>🔴 Closed</option>
                                </select>
                            </div>
                        </div>

                        <!-- Current Poster -->
                        <div class="mt-4">
                            <label class="form-label">
                                <i class="fas fa-image me-1 text-primary"></i>Event Poster
                            </label>

                            <?php if (!empty($event['poster_image']) && file_exists('uploads/' . $event['poster_image'])): ?>
                            <div class="mb-3" id="currentPosterSection">
                                <p class="small text-muted mb-2">Current poster:</p>
                                <div class="current-poster">
                                    <img src="uploads/<?php echo htmlspecialchars($event['poster_image']); ?>" 
                                         alt="Current Poster">
                                    <div class="remove-overlay">
                                        <button type="button" class="btn btn-danger btn-sm rounded-pill" onclick="removePoster()">
                                            <i class="fas fa-trash me-1"></i>Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="upload-zone" onclick="document.getElementById('poster').click();">
                                <i class="fas fa-cloud-upload-alt d-block"></i>
                                <h6 class="fw-semibold text-secondary mb-1">Upload New Poster</h6>
                                <p class="text-muted small mb-0">JPG, PNG, GIF, WEBP — Max 2MB</p>
                            </div>
                            <input type="file" class="d-none" id="poster" name="poster" accept="image/*">

                            <div class="file-info" id="fileInfo">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <i class="fas fa-file-image text-primary me-2"></i>
                                        <span id="fileName" class="fw-medium"></span>
                                        <span id="fileSize" class="text-muted ms-2"></span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="text-center">
                                <img id="imagePreview" class="preview-img" alt="Preview">
                            </div>
                        </div>

                        <div class="d-flex gap-3 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-save me-2"></i>Update Event
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary px-4" style="border-radius:10px;">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-card mb-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Event Info</h5>
                    <ul class="list-unstyled small mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Event ID</span>
                            <span class="fw-semibold">#<?php echo $event['id']; ?></span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Created</span>
                            <span class="fw-semibold"><?php echo date('d M Y, H:i', strtotime($event['created_at'])); ?></span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Last Updated</span>
                            <span class="fw-semibold"><?php echo date('d M Y, H:i', strtotime($event['updated_at'])); ?></span>
                        </li>
                    </ul>
                </div>

                <div class="form-card">
                    <h5 class="fw-bold mb-3"><i class="fas fa-shield-alt text-primary me-2"></i>Upload Rules</h5>
                    <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:1rem;">
                        <ul class="mb-0 ps-3 small">
                            <li><strong>Allowed:</strong> JPG, JPEG, PNG, GIF, WEBP</li>
                            <li><strong>Max size:</strong> 2 MB</li>
                            <li>Old poster is automatically replaced</li>
                            <li>Files renamed for security</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const posterInput = document.getElementById('poster');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const imagePreview = document.getElementById('imagePreview');

        posterInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                fileName.textContent = file.name;
                fileSize.textContent = '(' + (file.size / (1024*1024)).toFixed(2) + ' MB)';
                fileInfo.style.display = 'block';

                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);

                const maxSize = 2 * 1024 * 1024;
                if (file.size > maxSize) {
                    fileInfo.style.background = '#fee2e2';
                } else {
                    fileInfo.style.background = '#dcfce7';
                }
            }
        });

        function clearFile() {
            posterInput.value = '';
            fileInfo.style.display = 'none';
            imagePreview.style.display = 'none';
        }

        function removePoster() {
            if (confirm('Are you sure you want to remove the current poster?')) {
                document.getElementById('removePoster').value = '1';
                document.getElementById('currentPosterSection').style.display = 'none';
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
