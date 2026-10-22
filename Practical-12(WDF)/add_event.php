<?php
/**
 * Add New Event - With Poster Upload & File Validation
 * 
 * Validates:
 * - File type: Only JPG, JPEG, PNG, GIF, WEBP allowed
 * - File size: Max 2MB
 * - Image renaming for security (Advanced)
 */

require_once 'db.php';

$errors = [];
$success = '';

// Category options
$categories = ['Conference', 'Workshop', 'Seminar', 'Webinar', 'Meetup', 'Concert', 'Sports', 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $title = trim($conn->real_escape_string($_POST['title'] ?? ''));
    $description = trim($conn->real_escape_string($_POST['description'] ?? ''));
    $category = $conn->real_escape_string($_POST['category'] ?? 'Other');
    $event_date = $conn->real_escape_string($_POST['event_date'] ?? '');
    $venue = trim($conn->real_escape_string($_POST['venue'] ?? ''));
    $status = $conn->real_escape_string($_POST['status'] ?? 'Open');

    // ==================== VALIDATION ====================
    if (empty($title)) {
        $errors[] = "Event title is required.";
    }
    if (empty($event_date)) {
        $errors[] = "Event date is required.";
    }
    if (empty($venue)) {
        $errors[] = "Venue is required.";
    }
    if (!in_array($category, $categories)) {
        $errors[] = "Invalid category selected.";
    }
    if (!in_array($status, ['Open', 'Closed'])) {
        $errors[] = "Invalid status selected.";
    }

    // ==================== FILE UPLOAD VALIDATION ====================
    $poster_image = '';
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_file_size = 2 * 1024 * 1024; // 2MB
    $upload_dir = 'uploads/';

    if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['poster'];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload failed. Error code: " . $file['error'];
        } else {
            // Validate file type (MIME)
            $file_type = mime_content_type($file['tmp_name']);
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "Invalid file type! Only JPG, PNG, GIF, and WEBP images are allowed. Uploaded type: " . htmlspecialchars($file_type);
            }

            // Validate file extension
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_extensions)) {
                $errors[] = "Invalid file extension! Allowed: " . implode(', ', $allowed_extensions);
            }

            // Validate file size
            if ($file['size'] > $max_file_size) {
                $size_mb = round($file['size'] / (1024 * 1024), 2);
                $errors[] = "File size ({$size_mb}MB) exceeds the maximum allowed size of 2MB.";
            }

            // Validate it's actually an image
            $image_info = getimagesize($file['tmp_name']);
            if ($image_info === false) {
                $errors[] = "The uploaded file is not a valid image.";
            }

            // If no errors, process upload
            if (empty($errors)) {
                // Advanced: Rename image for security
                $new_filename = 'event_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $poster_image = $new_filename;
                } else {
                    $errors[] = "Failed to save uploaded file. Check directory permissions.";
                }
            }
        }
    }

    // ==================== INSERT INTO DATABASE ====================
    if (empty($errors)) {
        $sql = "INSERT INTO events (title, description, category, event_date, venue, status, poster_image) 
                VALUES ('$title', '$description', '$category', '$event_date', '$venue', '$status', '$poster_image')";

        if ($conn->query($sql) === TRUE) {
            $event_id = $conn->insert_id;

            // Log to audit trail (Advanced Extension)
            $new_values = json_encode([
                'title' => $title,
                'category' => $category,
                'event_date' => $event_date,
                'venue' => $venue,
                'status' => $status,
                'poster_image' => $poster_image
            ]);
            $audit_sql = "INSERT INTO audit_log (event_id, action, new_values) VALUES ($event_id, 'CREATE', '$new_values')";
            $conn->query($audit_sql);

            header("Location: index.php?success=" . urlencode("Event '{$title}' created successfully!"));
            exit();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event - Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #7c3aed;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 2rem 0;
            color: white;
            margin-bottom: 2rem;
        }
        .page-header h1 { font-weight: 700; }
        .form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            padding: 2rem;
        }
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.4rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }
        .upload-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8fafc;
        }
        .upload-zone:hover {
            border-color: var(--primary);
            background: #eff6ff;
        }
        .upload-zone.dragover {
            border-color: var(--primary);
            background: #eff6ff;
        }
        .upload-zone i {
            font-size: 2.5rem;
            color: #94a3b8;
            margin-bottom: 0.75rem;
        }
        .upload-zone h5 {
            color: #475569;
            font-weight: 600;
        }
        .upload-zone p {
            color: #94a3b8;
            font-size: 0.875rem;
        }
        .file-info {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-top: 1rem;
            display: none;
        }
        .preview-img {
            max-height: 200px;
            border-radius: 8px;
            margin-top: 1rem;
            display: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .validation-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 1rem 1.25rem;
        }
        .validation-info ul {
            margin: 0;
            padding-left: 1.25rem;
        }
        .validation-info li {
            font-size: 0.85rem;
            color: #475569;
            margin-bottom: 0.25rem;
        }
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
            color: white;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="page-header">
        <div class="container">
            <a href="index.php" class="text-white text-decoration-none d-inline-flex align-items-center mb-2" style="opacity:0.8;">
                <i class="fas fa-arrow-left me-2"></i>Back to Events
            </a>
            <h1><i class="fas fa-plus-circle me-2"></i>Add New Event</h1>
            <p class="mb-0 opacity-75">Fill in the details to create a new event with an optional poster image.</p>
        </div>
    </div>

    <div class="container pb-5">
        <!-- Error Messages -->
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
            <!-- Form -->
            <div class="col-lg-8">
                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data" id="eventForm">
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading me-1 text-primary"></i>Event Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="title" name="title" 
                                   placeholder="e.g., Tech Innovation Summit 2026" 
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1 text-primary"></i>Description
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Describe the event details, agenda, speakers, etc."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="row g-3">
                            <!-- Category -->
                            <div class="col-md-6">
                                <label for="category" class="form-label">
                                    <i class="fas fa-tag me-1 text-primary"></i>Category <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="category" name="category" required>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" 
                                            <?php echo (($_POST['category'] ?? '') === $cat) ? 'selected' : ''; ?>>
                                        <?php echo $cat; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Event Date -->
                            <div class="col-md-6">
                                <label for="event_date" class="form-label">
                                    <i class="fas fa-calendar me-1 text-primary"></i>Event Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="event_date" name="event_date" 
                                       value="<?php echo htmlspecialchars($_POST['event_date'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <!-- Venue -->
                            <div class="col-md-8">
                                <label for="venue" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1 text-primary"></i>Venue <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="venue" name="venue" 
                                       placeholder="e.g., Convention Center, Mumbai"
                                       value="<?php echo htmlspecialchars($_POST['venue'] ?? ''); ?>" required>
                            </div>

                            <!-- Status -->
                            <div class="col-md-4">
                                <label for="status" class="form-label">
                                    <i class="fas fa-toggle-on me-1 text-primary"></i>Status
                                </label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Open" <?php echo (($_POST['status'] ?? 'Open') === 'Open') ? 'selected' : ''; ?>>🟢 Open</option>
                                    <option value="Closed" <?php echo (($_POST['status'] ?? '') === 'Closed') ? 'selected' : ''; ?>>🔴 Closed</option>
                                </select>
                            </div>
                        </div>

                        <!-- Poster Upload -->
                        <div class="mt-4">
                            <label class="form-label">
                                <i class="fas fa-image me-1 text-primary"></i>Event Poster (Optional)
                            </label>
                            <div class="upload-zone" id="uploadZone" onclick="document.getElementById('poster').click();">
                                <i class="fas fa-cloud-upload-alt d-block"></i>
                                <h5>Click to upload or drag & drop</h5>
                                <p class="mb-0">JPG, PNG, GIF, WEBP — Max 2MB</p>
                            </div>
                            <input type="file" class="d-none" id="poster" name="poster" accept="image/*">
                            
                            <!-- File Info -->
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
                            
                            <!-- Image Preview -->
                            <div class="text-center">
                                <img id="imagePreview" class="preview-img" alt="Preview">
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-flex gap-3 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-save me-2"></i>Create Event
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary px-4" style="border-radius:10px;">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-lg-4">
                <!-- Upload Rules -->
                <div class="form-card mb-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-shield-alt text-primary me-2"></i>Upload Rules</h5>
                    <div class="validation-info">
                        <ul>
                            <li><strong>Allowed types:</strong> JPG, JPEG, PNG, GIF, WEBP</li>
                            <li><strong>Max file size:</strong> 2 MB</li>
                            <li><strong>Validation:</strong> MIME type + extension checked</li>
                            <li><strong>Security:</strong> Files are renamed on upload</li>
                            <li><strong>Protection:</strong> Upload directory blocks script execution</li>
                        </ul>
                    </div>
                </div>

                <!-- Tips -->
                <div class="form-card">
                    <h5 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning me-2"></i>Tips</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex gap-2 mb-2">
                            <i class="fas fa-check-circle text-success mt-1"></i>
                            <span class="small">Use a clear, descriptive title for better visibility.</span>
                        </li>
                        <li class="d-flex gap-2 mb-2">
                            <i class="fas fa-check-circle text-success mt-1"></i>
                            <span class="small">Upload a high-quality poster to attract attendees.</span>
                        </li>
                        <li class="d-flex gap-2 mb-2">
                            <i class="fas fa-check-circle text-success mt-1"></i>
                            <span class="small">Set status to "Closed" when registrations are full.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="fas fa-check-circle text-success mt-1"></i>
                            <span class="small">All changes are tracked in the audit log.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload preview
        const posterInput = document.getElementById('poster');
        const uploadZone = document.getElementById('uploadZone');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const imagePreview = document.getElementById('imagePreview');

        posterInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                fileName.textContent = file.name;
                fileSize.textContent = '(' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)';
                fileInfo.style.display = 'block';

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);

                // Validation feedback
                const maxSize = 2 * 1024 * 1024;
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                
                if (file.size > maxSize) {
                    fileInfo.style.background = '#fee2e2';
                    fileSize.innerHTML = '<span class="text-danger fw-bold">(' + (file.size / (1024 * 1024)).toFixed(2) + ' MB - EXCEEDS 2MB LIMIT!)</span>';
                } else if (!allowedTypes.includes(file.type)) {
                    fileInfo.style.background = '#fee2e2';
                    fileName.innerHTML += ' <span class="text-danger fw-bold">- Invalid type!</span>';
                } else {
                    fileInfo.style.background = '#dcfce7';
                }
            }
        });

        function clearFile() {
            posterInput.value = '';
            fileInfo.style.display = 'none';
            imagePreview.style.display = 'none';
            fileInfo.style.background = '#f1f5f9';
        }

        // Drag & Drop
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        uploadZone.addEventListener('dragleave', function() {
            this.classList.remove('dragover');
        });
        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            posterInput.files = e.dataTransfer.files;
            posterInput.dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
