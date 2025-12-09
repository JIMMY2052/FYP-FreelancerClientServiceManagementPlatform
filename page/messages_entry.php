<?php
session_start();
require_once 'config.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: messages.php');
    exit();
}

// Accept IDs from POST and store in session, then redirect to messages.php without query string
if (isset($_POST['client_id'])) {
    $_SESSION['target_client_id'] = (int) $_POST['client_id'];
}
if (isset($_POST['freelancer_id'])) {
    $_SESSION['target_freelancer_id'] = (int) $_POST['freelancer_id'];
}
if (isset($_POST['job_id'])) {
    $_SESSION['target_job_id'] = (int) $_POST['job_id'];
    // Reset any previous dismissal when a fresh job is targeted
    unset($_SESSION['quote_dismissed']);
}

header('Location: messages.php');
exit();
