<?php
require 'db.php';
require 'auth.php';
start_secure_session();

// Remove remember-me token from DB and browser
if (!empty($_COOKIE['remember_me']) && str_contains($_COOKIE['remember_me'], ':')) {
    [$selector] = explode(':', $_COOKIE['remember_me'], 2);
    $pdo->prepare("DELETE FROM remember_tokens WHERE selector = ?")->execute([$selector]);
}
set_remember_cookie('', time() - 3600);

destroy_session_completely();

header('Location: login.php?msg=loggedout');
exit;