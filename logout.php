<?php
require_once 'header.php';
require_once 'auth.php';

$wasLoggedIn = isLoggedIn();

logout();

if ($wasLoggedIn) {
    $_SESSION['logout_success'] = true;
}
header('Location: login.php');
exit();