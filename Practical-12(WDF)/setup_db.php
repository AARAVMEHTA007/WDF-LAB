<?php
/**
 * Setup Database and Sample Data with Poster Images
 */

$host = 'localhost';
$username = 'root';
$password = '';

// Create connection without db
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error . "\nMake sure XAMPP MySQL is running!\n");
}

echo "Connected to MySQL server successfully.\n";

// Run SQL commands from sql/events.sql
$sqlFile = __DIR__ . '/sql/events.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found at: $sqlFile\n");
}

$queries = file_get_contents($sqlFile);

// Execute multi-query
if ($conn->multi_query($queries)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "Database `event_management` and tables initialized successfully!\n";
} else {
    echo "Error executing SQL schema: " . $conn->error . "\n";
}

$conn->close();

// Now connect to the new database
require_once __DIR__ . '/db.php';

// Generate sample poster images using PHP GD library if available
$uploadsDir = __DIR__ . '/uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

// Function to generate sample event poster
function createSamplePoster($filename, $title, $category, $bgColor, $accentColor) {
    global $uploadsDir;
    $filePath = $uploadsDir . '/' . $filename;
    
    $width = 800;
    $height = 500;
    $image = imagecreatetruecolor($width, $height);
    
    // Background
    list($r1, $g1, $b1) = $bgColor;
    $bg = imagecolorallocate($image, $r1, $g1, $b1);
    imagefilledrectangle($image, 0, 0, $width, $height, $bg);
    
    // Accent bar on top
    list($r2, $g2, $b2) = $accentColor;
    $accent = imagecolorallocate($image, $r2, $g2, $b2);
    imagefilledrectangle($image, 0, 0, $width, 18, $accent);
    imagefilledrectangle($image, 0, $height - 18, $width, $height, $accent);
    
    // Text color
    $white = imagecolorallocate($image, 255, 255, 255);
    $light = imagecolorallocate($image, 220, 225, 235);
    $accentTxt = imagecolorallocate($image, $r2, $g2, $b2);

    // Draw decorative patterns
    for ($i = 0; $i < 5; $i++) {
        $circleColor = imagecolorallocatealpha($image, $r2, $g2, $b2, 105);
        imagefilledellipse($image, 700 + ($i * 20), 100 + ($i * 30), 180, 180, $circleColor);
    }
    
    // Fallback simple text rendering with built-in fonts
    imagestring($image, 5, 40, 60, strtoupper($category) . " EVENT", $accentTxt);
    imagestring($image, 5, 40, 120, substr($title, 0, 35), $white);
    imagestring($image, 4, 40, 160, "Official Event Banner & Poster", $light);
    imagestring($image, 3, 40, 220, "Validated Poster Upload - Practical 12 WDF", $white);
    imagestring($image, 4, 40, 360, "VENUE: Conference Center / Hall A", $light);
    imagestring($image, 4, 40, 400, "DATE: Scheduled 2026 | Status: Confirmed", $accentTxt);
    
    imagejpeg($image, $filePath, 90);
    imagedestroy($image);
    return $filename;
}

// Check GD extension
if (extension_loaded('gd')) {
    echo "GD library is enabled. Generating sample event posters...\n";
    $p1 = createSamplePoster("event_tech_summit.jpg", "Tech Innovation Summit 2026", "Conference", [30, 41, 59], [79, 70, 229]);
    $p2 = createSamplePoster("event_web_bootcamp.jpg", "Web Development Bootcamp", "Workshop", [15, 23, 42], [16, 185, 129]);
    $p3 = createSamplePoster("event_ai_seminar.jpg", "AI & Machine Learning Seminar", "Seminar", [30, 27, 75], [236, 72, 153]);
    $p4 = createSamplePoster("event_music_fest.jpg", "Music Fest 2026", "Concert", [69, 10, 10], [245, 158, 11]);

    // Update existing sample events with posters
    $conn->query("UPDATE events SET poster_image = '$p1' WHERE id = 1");
    $conn->query("UPDATE events SET poster_image = '$p2' WHERE id = 2");
    $conn->query("UPDATE events SET poster_image = '$p3' WHERE id = 3");
    $conn->query("UPDATE events SET poster_image = '$p4' WHERE id = 4");
    
    echo "Sample posters created and linked to events successfully!\n";
} else {
    echo "GD library not available, skipping banner generation.\n";
}

echo "Database and environment setup complete!\n";
