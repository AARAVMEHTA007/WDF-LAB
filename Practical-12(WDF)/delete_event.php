<?php
/**
 * Delete Event - Remove event and its poster
 * 
 * Features:
 * - Deletes poster file from uploads directory
 * - Logs deletion to audit trail (Advanced)
 * - Redirects with success/error message
 */

require_once 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php?error=" . urlencode("Invalid event ID."));
    exit();
}

// Fetch event data before deletion
$event = $conn->query("SELECT * FROM events WHERE id = $id")->fetch_assoc();

if (!$event) {
    header("Location: index.php?error=" . urlencode("Event not found."));
    exit();
}

$title = $event['title'];

// Log to audit trail BEFORE deletion (Advanced)
$old_values = json_encode([
    'title' => $event['title'],
    'description' => $event['description'],
    'category' => $event['category'],
    'event_date' => $event['event_date'],
    'venue' => $event['venue'],
    'status' => $event['status'],
    'poster_image' => $event['poster_image']
]);
$escaped_old = $conn->real_escape_string($old_values);
$conn->query("INSERT INTO audit_log (event_id, action, old_values) VALUES ($id, 'DELETE', '$escaped_old')");

// Delete poster file if exists
$upload_dir = 'uploads/';
if (!empty($event['poster_image']) && file_exists($upload_dir . $event['poster_image'])) {
    unlink($upload_dir . $event['poster_image']);
}

// Delete from database
$sql = "DELETE FROM events WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php?success=" . urlencode("Event '{$title}' deleted successfully!"));
} else {
    header("Location: index.php?error=" . urlencode("Failed to delete event: " . $conn->error));
}

$conn->close();
exit();
?>
