<?php
// db.php - Database connection and auto-initialization module
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'practical11_db';

// 1. Initial connection to MySQL server
$conn = @mysqli_connect($db_host, $db_user, $db_pass);

if (!$conn) {
    die("<div style='font-family:sans-serif;padding:30px;background:#ffebee;color:#c62828;border-radius:8px;max-width:700px;margin:50px auto;border:1px solid #ef9a9a;'>
            <h2>⚠️ Database Connection Error</h2>
            <p>Could not connect to MySQL server at <strong>$db_host</strong>.</p>
            <p><strong>Troubleshooting:</strong></p>
            <ol>
                <li>Open the <strong>XAMPP Control Panel</strong>.</li>
                <li>Make sure the <strong>MySQL</strong> module is started (green state).</li>
                <li>Verify your MySQL port (default 3306).</li>
            </ol>
            <p><em>MySQL Error: " . mysqli_connect_error() . "</em></p>
         </div>");
}

// 2. Auto-create database if not exists
$createDbSql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!mysqli_query($conn, $createDbSql)) {
    die("Database creation failed: " . mysqli_error($conn));
}

// 3. Select practical11_db
mysqli_select_db($conn, $db_name);
mysqli_set_charset($conn, 'utf8mb4');

// 4. Ensure students table exists
$createTableSql = "CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `roll_no` VARCHAR(30) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `course` VARCHAR(50) NOT NULL,
    `semester` INT NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `dob` DATE DEFAULT NULL,
    `gpa` DECIMAL(3,2) DEFAULT 0.00,
    `status` ENUM('Active', 'Inactive', 'Deleted') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

mysqli_query($conn, $createTableSql);

// 5. Auto-seed if students table is empty
$countRes = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM `students`");
if ($countRes) {
    $row = mysqli_fetch_assoc($countRes);
    if ($row['cnt'] == 0) {
        $seedSql = "INSERT INTO `students` (`roll_no`, `name`, `email`, `phone`, `course`, `semester`, `gender`, `dob`, `gpa`, `status`) VALUES
        ('CS2023001', 'Aarav Sharma', 'aarav.sharma@example.com', '9876543210', 'Computer Engineering', 4, 'Male', '2004-03-15', 8.85, 'Active'),
        ('CS2023002', 'Priya Patel', 'priya.patel@example.com', '9876543211', 'Computer Engineering', 4, 'Female', '2004-07-22', 9.10, 'Active'),
        ('IT2023003', 'Rohan Verma', 'rohan.verma@example.com', '9876543212', 'Information Technology', 4, 'Male', '2003-11-05', 7.95, 'Active'),
        ('DS2023004', 'Ananya Gupta', 'ananya.gupta@example.com', '9876543213', 'Data Science', 2, 'Female', '2005-01-18', 8.60, 'Active'),
        ('AI2023005', 'Kabir Singh', 'kabir.singh@example.com', '9876543214', 'Artificial Intelligence', 2, 'Male', '2004-09-30', 8.40, 'Active'),
        ('CS2023006', 'Isha Desai', 'isha.desai@example.com', '9876543215', 'Computer Engineering', 6, 'Female', '2003-05-12', 9.45, 'Active'),
        ('IT2023007', 'Aditya Joshi', 'aditya.joshi@example.com', '9876543216', 'Information Technology', 6, 'Male', '2003-02-28', 7.80, 'Inactive'),
        ('EC2023008', 'Sneha Kulkarni', 'sneha.kulkarni@example.com', '9876543217', 'Electronics & Comm.', 4, 'Female', '2004-08-14', 8.15, 'Active'),
        ('ME2023009', 'Manish Reddy', 'manish.reddy@example.com', '9876543218', 'Mechanical Engineering', 6, 'Male', '2003-04-19', 7.30, 'Active'),
        ('CE2023010', 'Divya Shah', 'divya.shah@example.com', '9876543219', 'Civil Engineering', 4, 'Female', '2004-12-01', 8.70, 'Active'),
        ('CS2023011', 'Varun Kapoor', 'varun.kapoor@example.com', '9876543220', 'Computer Engineering', 2, 'Male', '2005-06-25', 8.20, 'Active'),
        ('DS2023012', 'Tanvi Mehta', 'tanvi.mehta@example.com', '9876543221', 'Data Science', 4, 'Female', '2004-04-10', 9.25, 'Active'),
        ('AI2023013', 'Yash Singhal', 'yash.singhal@example.com', '9876543222', 'Artificial Intelligence', 4, 'Male', '2004-10-08', 8.55, 'Active'),
        ('IT2023014', 'Kavita Nair', 'kavita.nair@example.com', '9876543223', 'Information Technology', 2, 'Female', '2005-03-03', 7.90, 'Active'),
        ('CS2023015', 'Rahul Bhatia', 'rahul.bhatia@example.com', '9876543224', 'Computer Engineering', 6, 'Male', '2003-08-17', 8.05, 'Active'),
        ('EC2023016', 'Meera Iyer', 'meera.iyer@example.com', '9876543225', 'Electronics & Comm.', 2, 'Female', '2005-02-14', 8.75, 'Active'),
        ('ME2023017', 'Harsh Trivedi', 'harsh.trivedi@example.com', '9876543226', 'Mechanical Engineering', 4, 'Male', '2004-06-11', 7.45, 'Inactive'),
        ('CE2023018', 'Pooja Choudhary', 'pooja.choudhary@example.com', '9876543227', 'Civil Engineering', 6, 'Female', '2003-10-29', 8.35, 'Active'),
        ('CS2023019', 'Nikhil Jain', 'nikhil.jain@example.com', '9876543228', 'Computer Engineering', 4, 'Male', '2004-01-09', 9.00, 'Active'),
        ('IT2023020', 'Riya Sen', 'riya.sen@example.com', '9876543229', 'Information Technology', 4, 'Female', '2004-05-24', 8.65, 'Active'),
        ('DS2023021', 'Aryan Mishra', 'aryan.mishra@example.com', '9876543230', 'Data Science', 6, 'Male', '2003-07-07', 8.90, 'Active'),
        ('AI2023022', 'Kriti Malhotra', 'kriti.malhotra@example.com', '9876543231', 'Artificial Intelligence', 2, 'Female', '2005-09-16', 9.30, 'Active'),
        ('CS2023023', 'Siddharth Rao', 'siddharth.rao@example.com', '9876543232', 'Computer Engineering', 2, 'Male', '2005-08-21', 8.10, 'Active'),
        ('IT2023024', 'Neha Saxena', 'neha.saxena@example.com', '9876543233', 'Information Technology', 6, 'Female', '2003-12-12', 8.40, 'Active'),
        ('EC2023025', 'Gaurav Ghosh', 'gaurav.ghosh@example.com', '9876543234', 'Electronics & Comm.', 4, 'Male', '2004-04-02', 7.65, 'Deleted')";
        mysqli_query($conn, $seedSql);
    }
}

// Flash message utility functions
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
