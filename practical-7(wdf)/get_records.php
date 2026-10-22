<?php
/**
 * Practical 7: Get Stored Submissions Endpoint
 * Serves stored JSON and CSV file records for display on the webpage.
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// Ensure CSRF token is available for session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'json';
$dataDir = __DIR__ . '/data';

if ($format === 'csv') {
    $csvFilePath = $dataDir . '/submissions.csv';
    if (!file_exists($csvFilePath)) {
        echo json_encode(['success' => true, 'format' => 'csv', 'data' => []]);
        exit;
    }

    $rows = [];
    if (($handle = fopen($csvFilePath, 'r')) !== FALSE) {
        $header = fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) === count($header)) {
                $rows[] = array_combine($header, $data);
            }
        }
        fclose($handle);
    }
    echo json_encode([
        'success' => true,
        'format' => 'csv',
        'csrfToken' => $_SESSION['csrf_token'],
        'data' => $rows
    ]);
    exit;
} else {
    $jsonFilePath = $dataDir . '/submissions.json';
    $records = [];
    if (file_exists($jsonFilePath)) {
        $content = file_get_contents($jsonFilePath);
        if (!empty($content)) {
            $records = json_decode($content, true) ?? [];
        }
    }
    echo json_encode([
        'success' => true,
        'format' => 'json',
        'csrfToken' => $_SESSION['csrf_token'],
        'data' => $records
    ]);
    exit;
}
