<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}

// Check if user is deleted
require_once 'checkUserStatus.php';

// Redirect to appropriate edit profile page based on user type
if ($_SESSION['user_type'] === 'freelancer') {
    header('Location: edit_freelancer_profile.php');
} else {
    header('Location: edit_client_profile.php');
}
exit();
