<?php
require_once 'config.php';

// Clear session variables
unset($_SESSION['user_id']);
unset($_SESSION['user_type']);
unset($_SESSION['email']);

// Destroy session
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['worksnyc_remember'])) {
    setcookie('worksnyc_remember', '', time() - 3600, '/');
}

// Redirect to home page
header('Location: ../index.php');
exit();
?>

