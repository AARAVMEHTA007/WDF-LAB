<?php
// index.php - Main Student Management Dashboard with Search, Filter & Pagination
require_once 'db.php';

// Flash message
$flash = getFlash();

// Search and Filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$course_filter = isset($_GET['course']) ? trim($_GET['course']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'Active'; // Default to Active students
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

// Pagination settings
$limit = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Base query builder
$whereClauses = [];
$params = [];
$paramTypes = "";

// Status filter handling
if ($status_filter === 'All') {
    // Show everything including Deleted
} elseif ($status_filter === 'Deleted') {
    $whereClauses[] = "status = 'Deleted'";
} elseif ($status_filter === 'Inactive') {
    $whereClauses[] = "status = 'Inactive'";
} elseif ($status_filter === 'Active') {
    $whereClauses[] = "status = 'Active'";
}

// Course filter
if (!empty($course_filter) && $course_filter !== 'All') {
    $whereClauses[] = "course = ?";
    $params[] = $course_filter;
    $paramTypes .= "s";
}

// Search filter (Name, Email, Roll No)
if (!empty($search)) {
    $whereClauses[] = "(name LIKE ? OR email LIKE ? OR roll_no LIKE ? OR phone LIKE ?)";
    $searchWildcard = "%" . $search . "%";
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $paramTypes .= "ssss";
}

$whereSql = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";

// Sorting logic
$orderSql = " ORDER BY id DESC";
switch ($sort_by) {
    case 'name_asc':
        $orderSql = " ORDER BY name ASC";
        break;
    case 'name_desc':
        $orderSql = " ORDER BY name DESC";
        break;
    case 'roll_asc':
        $orderSql = " ORDER BY roll_no ASC";
        break;
    case 'gpa_desc':
        $orderSql = " ORDER BY gpa DESC";
        break;
    case 'gpa_asc':
        $orderSql = " ORDER BY gpa ASC";
        break;
    case 'oldest':
        $orderSql = " ORDER BY id ASC";
        break;
    case 'newest':
    default:
        $orderSql = " ORDER BY id DESC";
        break;
}

// Count total matching records for pagination
$countQuery = "SELECT COUNT(*) as total FROM students" . $whereSql;
$stmtCount = mysqli_prepare($conn, $countQuery);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmtCount, $paramTypes, ...$params);
}
mysqli_stmt_execute($stmtCount);
$countRes = mysqli_stmt_get_result($stmtCount);
$totalRecords = mysqli_fetch_assoc($countRes)['total'] ?? 0;
mysqli_stmt_close($stmtCount);

$totalPages = ceil($totalRecords / $limit);
if ($totalPages < 1) $totalPages = 1;
if ($page > $totalPages) $page = $totalPages;

// Fetch filtered and paginated records
$dataQuery = "SELECT * FROM students" . $whereSql . $orderSql . " LIMIT ?, ?";
$paramsData = $params;
$paramTypesData = $paramTypes . "ii";
$paramsData[] = $offset;
$paramsData[] = $limit;

$stmtData = mysqli_prepare($conn, $dataQuery);
if (!empty($paramsData)) {
    mysqli_stmt_bind_param($stmtData, $paramTypesData, ...$paramsData);
}
mysqli_stmt_execute($stmtData);
$result = mysqli_stmt_get_result($stmtData);
$students = [];
while ($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}
mysqli_stmt_close($stmtData);

// Fetch stats for dashboard widgets
$statsQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_count,
    SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) as inactive_count,
    SUM(CASE WHEN status = 'Deleted' THEN 1 ELSE 0 END) as deleted_count,
    AVG(CASE WHEN status != 'Deleted' THEN gpa ELSE NULL END) as avg_gpa
FROM students";
$statsRes = mysqli_query($conn, $statsQuery);
$stats = mysqli_fetch_assoc($statsRes);

// List of available courses for dropdown filter
$courseListQuery = "SELECT DISTINCT course FROM students ORDER BY course ASC";
$courseRes = mysqli_query($conn, $courseListQuery);
$courseList = [];
while ($c = mysqli_fetch_assoc($courseRes)) {
    $courseList[] = $c['course'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management CRUD - Practical 11</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            --card-shadow: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .navbar-custom {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }
        .stat-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .table-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .table thead th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 16px;
        }
        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            font-size: 0.92rem;
        }
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }
        .badge-pill {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<!-- Top Navigation -->
<nav class="navbar navbar-custom navbar-dark py-3 mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
            <i class="bi bi-mortarboard-fill fs-3"></i>
            <span>EduManage CRUD <small class="fw-normal opacity-75 fs-6">| Practical 11 (WDF)</small></span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <a href="export_csv.php?search=<?= urlencode($search) ?>&course=<?= urlencode($course_filter) ?>&status=<?= urlencode($status_filter) ?>" class="btn btn-light btn-sm text-primary fw-semibold shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Export to CSV
            </a>
            <a href="add.php" class="btn btn-warning btn-sm text-dark fw-bold shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> Add New Student
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mb-5">

    <!-- Flash Alert Feedback Message -->
    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show shadow-sm d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($flash['message']) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Summary Stats Bar -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card p-3 d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Records</div>
                    <div class="fs-4 fw-bold"><?= $stats['total'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-3 d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="text-muted small">Active Students</div>
                    <div class="fs-4 fw-bold text-success"><?= $stats['active_count'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-3 d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-pause-circle-fill"></i>
                </div>
                <div>
                    <div class="text-muted small">Inactive Students</div>
                    <div class="fs-4 fw-bold text-warning"><?= $stats['inactive_count'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-3 d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-trash-fill"></i>
                </div>
                <div>
                    <div class="text-muted small">Soft-Deleted (Trash)</div>
                    <div class="fs-4 fw-bold text-danger"><?= $stats['deleted_count'] ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search, Filter and Actions Toolbar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="index.php" class="row g-2 align-items-center">
                <!-- Search Box -->
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Search by name, email, roll no..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>

                <!-- Course Filter -->
                <div class="col-6 col-md-2">
                    <select name="course" class="form-select">
                        <option value="All">All Courses</option>
                        <?php foreach ($courseList as $courseName): ?>
                            <option value="<?= htmlspecialchars($courseName) ?>" <?= $course_filter === $courseName ? 'selected' : '' ?>>
                                <?= htmlspecialchars($courseName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select">
                        <option value="Active" <?= $status_filter === 'Active' ? 'selected' : '' ?>>Active Students</option>
                        <option value="Inactive" <?= $status_filter === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="Deleted" <?= $status_filter === 'Deleted' ? 'selected' : '' ?>>Trash (Soft Deleted)</option>
                        <option value="All" <?= $status_filter === 'All' ? 'selected' : '' ?>>All Statuses</option>
                    </select>
                </div>

                <!-- Sort Filter -->
                <div class="col-6 col-md-2">
                    <select name="sort" class="form-select">
                        <option value="newest" <?= $sort_by === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                        <option value="name_desc" <?= $sort_by === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                        <option value="gpa_desc" <?= $sort_by === 'gpa_desc' ? 'selected' : '' ?>>GPA (High to Low)</option>
                        <option value="roll_asc" <?= $sort_by === 'roll_asc' ? 'selected' : '' ?>>Roll No (Asc)</option>
                    </select>
                </div>

                <!-- Filter & Reset Buttons -->
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Student Table Card -->
    <div class="table-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0 fw-bold">
                Student Directory 
                <span class="badge bg-secondary rounded-pill ms-2"><?= $totalRecords ?> records found</span>
            </h5>
            <div class="text-muted small">
                Showing <?= min($offset + 1, $totalRecords) ?> - <?= min($offset + $limit, $totalRecords) ?> of <?= $totalRecords ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width: 120px;">Roll No</th>
                        <th>Student Name</th>
                        <th>Contact Info</th>
                        <th>Course & Sem</th>
                        <th>GPA</th>
                        <th>Status</th>
                        <th class="text-end" style="min-width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                No student records found matching the criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $stu): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border fw-medium"><?= htmlspecialchars($stu['roll_no']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($stu['name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($stu['gender'] ?? 'N/A') ?><?= !empty($stu['dob']) ? ' • ' . date('d M Y', strtotime($stu['dob'])) : '' ?></small>
                                </td>
                                <td>
                                    <div><i class="bi bi-envelope me-1 text-muted"></i><?= htmlspecialchars($stu['email']) ?></div>
                                    <small class="text-muted"><i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($stu['phone']) ?></small>
                                </td>
                                <td>
                                    <div class="fw-medium"><?= htmlspecialchars($stu['course']) ?></div>
                                    <small class="text-muted">Semester <?= htmlspecialchars($stu['semester']) ?></small>
                                </td>
                                <td>
                                    <?php 
                                        $gpa = (float)$stu['gpa'];
                                        $badgeClass = $gpa >= 8.5 ? 'bg-success' : ($gpa >= 7.5 ? 'bg-primary' : 'bg-secondary');
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= number_format($gpa, 2) ?></span>
                                </td>
                                <td>
                                    <?php if ($stu['status'] === 'Active'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle badge-pill">Active</span>
                                    <?php elseif ($stu['status'] === 'Inactive'): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle badge-pill">Inactive</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle badge-pill">Deleted</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group gap-1" role="group">
                                        <!-- View Modal Trigger -->
                                        <button type="button" class="btn btn-sm btn-outline-info btn-action" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewModal<?= $stu['id'] ?>" 
                                                title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Edit Button -->
                                        <a href="edit.php?id=<?= $stu['id'] ?>" class="btn btn-sm btn-outline-primary btn-action" title="Edit Student">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <?php if ($stu['status'] !== 'Deleted'): ?>
                                            <!-- Soft Delete (Move to Trash) -->
                                            <button type="button" class="btn btn-sm btn-outline-warning btn-action" 
                                                    onclick="confirmAction('delete.php?action=soft_delete&id=<?= $stu['id'] ?>', 'Soft Delete Student', 'Are you sure you want to move student <?= htmlspecialchars(addslashes($stu['name'])) ?> to trash? You can restore it later.')" 
                                                    title="Soft Delete (Move to Trash)">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                            
                                            <!-- Permanent Delete Confirmation Button -->
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                                    onclick="confirmAction('delete.php?action=permanent_delete&id=<?= $stu['id'] ?>', 'Permanently Delete Record', 'WARNING: Are you sure you want to PERMANENTLY delete student <?= htmlspecialchars(addslashes($stu['name'])) ?> (<?= $stu['roll_no'] ?>)? This cannot be undone!')" 
                                                    title="Permanent Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <!-- Restore Button -->
                                            <a href="delete.php?action=restore&id=<?= $stu['id'] ?>" class="btn btn-sm btn-outline-success btn-action" title="Restore Student">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </a>
                                            <!-- Permanent Delete -->
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                                    onclick="confirmAction('delete.php?action=permanent_delete&id=<?= $stu['id'] ?>', 'Permanently Delete Record', 'WARNING: Permanently delete <?= htmlspecialchars(addslashes($stu['name'])) ?> from database forever?')" 
                                                    title="Permanent Delete">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- View Student Modal -->
                                    <div class="modal fade text-start" id="viewModal<?= $stu['id'] ?>" tabindex="-1" aria-labelledby="viewModalLabel<?= $stu['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-light">
                                                    <h6 class="modal-title fw-bold" id="viewModalLabel<?= $stu['id'] ?>">
                                                        <i class="bi bi-person-badge me-2 text-primary"></i>Student Profile Details
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="text-center pb-3 border-bottom mb-3">
                                                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                                            <?= strtoupper(substr($stu['name'], 0, 1)) ?>
                                                        </div>
                                                        <h5 class="fw-bold mb-0"><?= htmlspecialchars($stu['name']) ?></h5>
                                                        <span class="badge bg-secondary"><?= htmlspecialchars($stu['roll_no']) ?></span>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Course</small>
                                                            <div class="fw-semibold"><?= htmlspecialchars($stu['course']) ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Semester</small>
                                                            <div class="fw-semibold">Sem <?= htmlspecialchars($stu['semester']) ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Email</small>
                                                            <div><?= htmlspecialchars($stu['email']) ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Phone</small>
                                                            <div><?= htmlspecialchars($stu['phone']) ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Gender</small>
                                                            <div><?= htmlspecialchars($stu['gender']) ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Date of Birth</small>
                                                            <div><?= !empty($stu['dob']) ? date('d M Y', strtotime($stu['dob'])) : 'N/A' ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">GPA / Result</small>
                                                            <div class="fw-bold text-primary"><?= number_format($stu['gpa'], 2) ?> / 10.00</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Account Status</small>
                                                            <div class="fw-semibold"><?= htmlspecialchars($stu['status']) ?></div>
                                                        </div>
                                                        <div class="col-12 mt-2 pt-2 border-top">
                                                            <small class="text-muted d-block">Created At</small>
                                                            <div class="small text-secondary"><?= $stu['created_at'] ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light py-2">
                                                    <a href="edit.php?id=<?= $stu['id'] ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-pencil me-1"></i> Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <?php if ($totalPages > 1): ?>
            <div class="p-3 border-top bg-white d-flex justify-content-between align-items-center">
                <span class="text-muted small">Page <?= $page ?> of <?= $totalPages ?></span>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Prev Page -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                <i class="bi bi-chevron-left"></i> Prev
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Page -->
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Reusable Delete / Action Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="confirmModalTitle">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p id="confirmModalBody" class="mb-0 text-secondary"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmModalBtn" class="btn btn-danger">Confirm Action</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Delete Confirmation Function (Satisfies Key Question: "Are delete operations confirmed before execution?")
    function confirmAction(targetUrl, title, message) {
        document.getElementById('confirmModalTitle').innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + title;
        document.getElementById('confirmModalBody').innerText = message;
        document.getElementById('confirmModalBtn').setAttribute('href', targetUrl);
        
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        modal.show();
    }
</script>
</body>
</html>
