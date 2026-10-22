<?php
require_once 'config.php';

if (isLoggedIn()) {
    logAudit($conn, $_SESSION['user_id'], 'LOGOUT', $_SESSION['username'] . ' logged out');
}

session_unset();
session_destroy();

header("Location: login.php");
exit();
?>
