<?php
/**
 * Database Connection - Event Management System
 * Practical 12: Event Management CRUD with Poster Upload
 */

$host = 'localhost';
$dbname = 'event_management';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
    }

    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>");
}
?>
