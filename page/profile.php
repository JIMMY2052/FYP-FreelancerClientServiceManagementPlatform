<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}

// Redirect to appropriate profile page based on user type
if ($_SESSION['user_type'] === 'freelancer') {
    header('Location: freelancer_profile.php');
} else {
    header('Location: client_profile.php');
}
exit();
?>

