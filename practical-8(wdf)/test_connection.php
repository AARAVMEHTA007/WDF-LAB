<?php


require_once 'db.php';

echo "<h2>Database Connection Test</h2>";
echo "<p style='color:green;'>Connected to <strong>studenthub</strong> database successfully!</p>";

$stmt = $pdo->prepare("SELECT * FROM students ORDER BY student_id");
$stmt->execute();
$students = $stmt->fetchAll();

echo "<h3>Students Table</h3>";
echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Department</th>
        <th>Enrolled On</th>
      </tr>";

foreach ($students as $row) {
    echo "<tr>
            <td>{$row['student_id']}</td>
            <td>{$row['full_name']}</td>
            <td>{$row['email']}</td>
            <td>{$row['phone']}</td>
            <td>{$row['department']}</td>
            <td>{$row['enrolled_on']}</td>
          </tr>";
}
echo "</table>";
?>
