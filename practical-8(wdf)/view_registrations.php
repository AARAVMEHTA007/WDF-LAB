<?php


require_once 'db.php';


$stmt = $pdo->prepare(
    "SELECT r.reg_id, s.full_name, s.department, e.event_name, e.event_date,
            r.status, r.reg_date
     FROM registrations r
     JOIN students s ON r.student_id = s.student_id
     JOIN events   e ON r.event_id   = e.event_id
     ORDER BY r.reg_date DESC"
);
$stmt->execute();
$registrations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Registrations - StudentHub</title>
    <style>
        body  { font-family: Arial, sans-serif; margin: 30px; background: #f9f9f9; }
        h2    { color: #333; }
        table { border-collapse: collapse; width: 100%; background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        th    { background: #4CAF50; color: #fff; padding: 10px; text-align: left; }
        td    { padding: 9px 10px; border-bottom: 1px solid #ddd; }
        tr:hover td { background: #f1f1f1; }
        .confirmed  { color: green;  font-weight: bold; }
        .pending    { color: orange; font-weight: bold; }
        .cancelled  { color: red;   font-weight: bold; }
        a { display: inline-block; margin-top: 20px; color: #0066cc; text-decoration: none; }
    </style>
</head>
<body>

<h2>StudentHub - All Event Registrations</h2>

<?php if (count($registrations) === 0): ?>
    <p>No registrations found.</p>
<?php else: ?>
<table>
    <tr>
        <th>#</th>
        <th>Student Name</th>
        <th>Department</th>
        <th>Event</th>
        <th>Event Date</th>
        <th>Status</th>
        <th>Registered On</th>
    </tr>
    <?php foreach ($registrations as $i => $row): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= htmlspecialchars($row['full_name']) ?></td>
        <td><?= htmlspecialchars($row['department']) ?></td>
        <td><?= htmlspecialchars($row['event_name']) ?></td>
        <td><?= $row['event_date'] ?></td>
        <td class="<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></td>
        <td><?= $row['reg_date'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<a href="register_student.php">+ Register a Student</a>

</body>
</html>
