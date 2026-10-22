<?php
/**
 * seed.php  – run once to insert demo users
 * Visit:  http://localhost/practical-10(WDF)/seed.php
 */
require 'db.php';

$users = [
    ['admin',   'Admin@123',   'admin'],
    ['student1','Student@123', 'student'],
    ['student2','Pass@456',    'student'],
];

$stmt = $pdo->prepare(
    "INSERT IGNORE INTO users (username, password_hash, role)
     VALUES (?, ?, ?)"
);

foreach ($users as [$uname, $plain, $role]) {
    $hash = password_hash($plain, PASSWORD_BCRYPT);
    $stmt->execute([$uname, $hash, $role]);
}

echo "<pre style='font-family:monospace;padding:20px'>";
echo "✅  Seeding complete!\n\n";
echo "Demo credentials\n";
echo "─────────────────────────────────\n";
echo " Role     | Username  | Password\n";
echo "──────────|───────────|──────────\n";
echo " admin    | admin     | Admin\@123\n";
echo " student  | student1  | Student\@123\n";
echo " student  | student2  | Pass\@456\n";
echo "─────────────────────────────────\n\n";
echo '<a href="login.php">→ Go to Login</a>';
echo "</pre>";
