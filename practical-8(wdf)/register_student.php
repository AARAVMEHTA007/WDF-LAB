<?php

require_once 'db.php';

$message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int) $_POST['student_id'];
    $event_id   = (int) $_POST['event_id'];

  
    $check = $pdo->prepare(
        "SELECT reg_id FROM registrations WHERE student_id = ? AND event_id = ?"
    );
    $check->execute([$student_id, $event_id]);

    if ($check->rowCount() > 0) {
        $message = "<p style='color:orange;'>Student is already registered for this event.</p>";
    } else {
     
        $insert = $pdo->prepare(
            "INSERT INTO registrations (student_id, event_id, status) VALUES (?, ?, 'confirmed')"
        );
        $insert->execute([$student_id, $event_id]);
        $message = "<p style='color:green;'>Registration successful!</p>";
    }
}


$students = $pdo->query("SELECT student_id, full_name FROM students ORDER BY full_name")->fetchAll();


$events = $pdo->query("SELECT event_id, event_name FROM events ORDER BY event_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register for Event - StudentHub</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f4f4; }
        h2   { color: #333; }
        form { background: #fff; padding: 20px; width: 400px; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        label  { display: block; margin-top: 12px; font-weight: bold; }
        select { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; }
        button { margin-top: 16px; padding: 10px 20px; background: #4CAF50; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
        a { display: inline-block; margin-top: 20px; color: #0066cc; text-decoration: none; }
    </style>
</head>
<body>

<h2>StudentHub - Event Registration</h2>

<?= $message ?>

<form method="POST" action="">
    <label for="student_id">Select Student:</label>
    <select name="student_id" id="student_id" required>
        <option value="">-- Choose Student --</option>
        <?php foreach ($students as $s): ?>
            <option value="<?= $s['student_id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="event_id">Select Event:</label>
    <select name="event_id" id="event_id" required>
        <option value="">-- Choose Event --</option>
        <?php foreach ($events as $e): ?>
            <option value="<?= $e['event_id'] ?>"><?= htmlspecialchars($e['event_name']) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Register</button>
</form>

<a href="view_registrations.php">View All Registrations</a>

</body>
</html>
