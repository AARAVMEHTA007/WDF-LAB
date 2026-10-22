<?php
require_once 'db.php';
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $roll_no   = trim($_POST['roll_no'] ?? '');
    $name      = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $course    = trim($_POST['course'] ?? '');
    $semester  = intval($_POST['semester'] ?? 0);
    $gender    = $_POST['gender'] ?? '';
    $dob       = $_POST['dob'] ?? null;
    $gpa       = floatval($_POST['gpa'] ?? 0);

    // Basic validation
    if (empty($roll_no) || empty($name) || empty($email) || empty($phone) || empty($course) || $semester < 1 || empty($gender) || empty($dob)) {
        setFlash('danger', 'Please fill in all required fields.');
        header('Location: add_student.php');
        exit;
    }

    // Prepare insert statement
    $stmt = mysqli_prepare($conn, "INSERT INTO students (roll_no, name, email, phone, course, semester, gender, dob, gpa, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssisds", $roll_no, $name, $email, $phone, $course, $semester, $gender, $dob, $gpa);
        if (mysqli_stmt_execute($stmt)) {
            setFlash('success', 'Student added successfully.');
            header('Location: index.php');
            exit;
        } else {
            setFlash('danger', 'Database error: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        setFlash('danger', 'Failed to prepare statement.');
    }
    // On failure, fall back to the form page
    header('Location: add_student.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/add_student.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary-gradient);">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Student Management</a>
        </div>
    </nav>
    <div class="container mt-4">
        <h2 class="mb-4"><i class="bi bi-person-plus-fill me-2"></i>Add New Student</h2>
        <div id="alert-placeholder"></div>
        <form id="addStudentForm" class="row g-3 needs-validation" novalidate>
            <div class="col-md-6">
                <label for="roll_no" class="form-label">Roll No</label>
                <input type="text" class="form-control" id="roll_no" name="roll_no" required>
                <div class="invalid-feedback">Please provide a roll number.</div>
            </div>
            <div class="col-md-6">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback">Please provide the student's name.</div>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback">Please provide a valid email.</div>
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
                <div class="invalid-feedback">Please provide a phone number.</div>
            </div>
            <div class="col-md-4">
                <label for="course" class="form-label">Course</label>
                <input type="text" class="form-control" id="course" name="course" required>
                <div class="invalid-feedback">Please provide the course name.</div>
            </div>
            <div class="col-md-2">
                <label for="semester" class="form-label">Semester</label>
                <input type="number" class="form-control" id="semester" name="semester" min="1" max="12" required>
                <div class="invalid-feedback">Enter a valid semester.</div>
            </div>
            <div class="col-md-4">
                <label for="gender" class="form-label">Gender</label>
                <select class="form-select" id="gender" name="gender" required>
                    <option value="" selected disabled>Choose...</option>
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                </select>
                <div class="invalid-feedback">Select gender.</div>
            </div>
            <div class="col-md-4">
                <label for="dob" class="form-label">Date of Birth</label>
                <input type="date" class="form-control" id="dob" name="dob" required>
                <div class="invalid-feedback">Select date of birth.</div>
            </div>
            <div class="col-md-4">
                <label for="gpa" class="form-label">GPA</label>
                <input type="number" step="0.01" min="0" max="10" class="form-control" id="gpa" name="gpa" required>
                <div class="invalid-feedback">Enter a GPA between 0.00 and 10.00.</div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Student</button>
                <a href="index.php" class="btn btn-secondary ms-2"><i class="bi bi-arrow-left me-1"></i>Back to List</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/add_student.js"></script>
</body>
</html>
