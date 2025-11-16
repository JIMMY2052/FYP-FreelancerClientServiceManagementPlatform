<?php
require_once 'config.php';

// Clear admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_email']);
unset($_SESSION['is_admin']);

// Destroy session
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['worksnyc_admin_remember'])) {
    setcookie('worksnyc_admin_remember', '', time() - 3600, '/');
}

// Redirect to login page
header('Location: admin_login.php');
exit();
