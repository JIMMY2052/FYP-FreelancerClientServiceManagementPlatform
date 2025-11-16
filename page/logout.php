<?php
require_once 'config.php';

// Destroy session
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['worksnyc_remember'])) {
    setcookie('worksnyc_remember', '', time() - 3600, '/');
}

// Redirect to login page
header('Location: login.php');
exit();
?>

