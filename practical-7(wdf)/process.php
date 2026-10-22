<?php
/**
 * Practical 7: PHP Form Processing with Server-Side Validation & CSV/JSON Storage
 * Student Hub Portal - Form Processor Script
 */

session_start();

// Set JSON response header for AJAX requests
header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'errors' => [],
    'message' => ''
];

// 1. Check if form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method. Form must be submitted via POST.';
    echo json_encode($response);
    exit;
}

// 2. Advanced Extension: Validate CSRF Token
$tokenSubmitted = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $tokenSubmitted)) {
    http_response_code(403);
    $response['errors']['csrf'] = 'Invalid or expired CSRF token. Please refresh the page and try again.';
    $response['message'] = 'Security validation failed.';
    echo json_encode($response);
    exit;
}

// Helper function to sanitize input strings
function sanitize_input($data) {
    $data = trim($data);
    $data = strip_tags($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// 3. Extract and Sanitize Form Inputs
$fullName   = isset($_POST['fullName']) ? sanitize_input($_POST['fullName']) : '';
$email      = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$rollNo     = isset($_POST['rollNo']) ? sanitize_input($_POST['rollNo']) : '';
$department = isset($_POST['department']) ? sanitize_input($_POST['department']) : '';
$courseYear = isset($_POST['courseYear']) ? sanitize_input($_POST['courseYear']) : '';
$phone      = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
$city       = isset($_POST['city']) ? sanitize_input($_POST['city']) : '';
$message    = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';

$errors = [];

// 4. Server-Side Validation Rules
// Full Name Validation
if (empty($fullName)) {
    $errors['fullName'] = 'Full Name is required.';
} elseif (!preg_match("/^[a-zA-Z\s]{2,50}$/", $fullName)) {
    $errors['fullName'] = 'Full Name must contain only letters and spaces (2 to 50 characters).';
}

// Email Validation
if (empty($email)) {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

// Roll Number Validation
if (empty($rollNo)) {
    $errors['rollNo'] = 'Roll Number is required.';
} elseif (!preg_match("/^[a-zA-Z0-9\-]{3,20}$/", $rollNo)) {
    $errors['rollNo'] = 'Roll Number must be alphanumeric (3 to 20 characters).';
}

// Department Validation
if (empty($department) || $department === 'Select Department') {
    $errors['department'] = 'Please select a department.';
}

// Course Year Validation
if (empty($courseYear)) {
    $errors['courseYear'] = 'Please select your academic year.';
}

// Phone Number Validation
if (empty($phone)) {
    $errors['phone'] = 'Phone number is required.';
} elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
    $errors['phone'] = 'Phone number must be exactly 10 digits.';
}

// City Validation
if (empty($city)) {
    $errors['city'] = 'City is required.';
}

// Message Validation
if (empty($message)) {
    $errors['message'] = 'Message/Reason for registration is required.';
} elseif (strlen($message) < 5 || strlen($message) > 500) {
    $errors['message'] = 'Message must be between 5 and 500 characters.';
}

// Return validation errors if any rule fails
if (!empty($errors)) {
    http_response_code(400);
    $response['errors'] = $errors;
    $response['message'] = 'Form submission failed. Please fix the highlighted errors.';
    echo json_encode($response);
    exit;
}

// 5. Safe File Storage Handling
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$jsonFilePath = $dataDir . '/submissions.json';
$csvFilePath  = $dataDir . '/submissions.csv';

// Read existing JSON data
$existingData = [];
if (file_exists($jsonFilePath)) {
    $jsonContent = file_get_contents($jsonFilePath);
    if (!empty($jsonContent)) {
        $decoded = json_decode($jsonContent, true);
        if (is_array($decoded)) {
            $existingData = $decoded;
        }
    }
}

// Calculate next record ID
$newId = 1;
if (!empty($existingData)) {
    $lastRecord = end($existingData);
    $newId = (isset($lastRecord['id']) ? intval($lastRecord['id']) : 0) + 1;
}

$submissionTime = date('Y-m-d H:i:s');
$userIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Formatted Submission Record
$newRecord = [
    'id'          => $newId,
    'fullName'    => $fullName,
    'email'       => $email,
    'rollNo'      => $rollNo,
    'department'  => $department,
    'courseYear'  => $courseYear,
    'phone'       => $phone,
    'city'        => $city,
    'message'     => $message,
    'submittedAt' => $submissionTime,
    'ipAddress'   => $userIp
];

// Append to JSON file with safe locking
$existingData[] = $newRecord;
$jsonHandle = fopen($jsonFilePath, 'w');
if ($jsonHandle && flock($jsonHandle, LOCK_EX)) {
    fwrite($jsonHandle, json_encode($existingData, JSON_PRETTY_PRINT));
    fflush($jsonHandle);
    flock($jsonHandle, LOCK_UN);
    fclose($jsonHandle);
} else {
    if ($jsonHandle) fclose($jsonHandle);
    http_response_code(500);
    $response['message'] = 'Failed to write data safely to JSON storage.';
    echo json_encode($response);
    exit;
}

// Append to CSV file safely
$csvFileExists = file_exists($csvFilePath);
$csvHandle = fopen($csvFilePath, 'a');
if ($csvHandle && flock($csvHandle, LOCK_EX)) {
    // If new CSV file, write header row first
    if (!$csvFileExists || filesize($csvFilePath) === 0) {
        fputcsv($csvHandle, ['ID', 'Full Name', 'Email', 'Roll No', 'Department', 'Course Year', 'Phone', 'City', 'Message', 'Submitted At', 'IP Address']);
    }
    
    // Write submission data row
    fputcsv($csvHandle, [
        $newId,
        $fullName,
        $email,
        $rollNo,
        $department,
        $courseYear,
        $phone,
        $city,
        $message,
        $submissionTime,
        $userIp
    ]);
    
    fflush($csvHandle);
    flock($csvHandle, LOCK_UN);
    fclose($csvHandle);
}

// Regenerate CSRF Token for future submissions
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// 6. Success Response
$response['success'] = true;
$response['message'] = "Registration submitted successfully! Record #{$newId} saved to CSV and JSON storage.";
$response['record'] = $newRecord;
$response['newCsrfToken'] = $_SESSION['csrf_token'];

echo json_encode($response);
exit;
