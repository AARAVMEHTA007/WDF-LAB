<?php


require_once 'db.php';


$pdo->exec("DROP PROCEDURE IF EXISTS GetStudentRegistrations");

$pdo->exec("
CREATE PROCEDURE GetStudentRegistrations(IN sid INT)
BEGIN
    SELECT s.full_name, e.event_name, e.event_date, r.status
    FROM registrations r
    JOIN students s ON r.student_id = s.student_id
    JOIN events   e ON r.event_id   = e.event_id
    WHERE r.student_id = sid;
END
");

$sid  = 1;   // student_id = 1 (Aarav Mehta)
$stmt = $pdo->prepare("CALL GetStudentRegistrations(?)");
$stmt->execute([$sid]);
$results = $stmt->fetchAll();

echo "<h2>Stored Procedure - Registrations for Student ID: $sid</h2>";
echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr><th>Student</th><th>Event</th><th>Event Date</th><th>Status</th></tr>";
foreach ($results as $row) {
    echo "<tr>
            <td>{$row['full_name']}</td>
            <td>{$row['event_name']}</td>
            <td>{$row['event_date']}</td>
            <td>{$row['status']}</td>
          </tr>";
}
echo "</table>";
?>
