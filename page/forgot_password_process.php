<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $_SESSION['error'] = 'Please enter your email address.';
        header('Location: forgot_password.php');
        exit();
    }
    
    $conn = getDBConnection();
    
    // Check in both tables
    $stmt = $conn->prepare("SELECT FreelancerID, Email FROM freelancer WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_type = 'freelancer';
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $stmt = $conn->prepare("SELECT ClientID, Email FROM client WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_type = 'client';
    }
    
    if ($result->num_rows > 0) {
        // In a real application, you would send a password reset email here
        // For now, we'll just show a success message
        $_SESSION['success'] = 'If an account exists with this email, a password reset link has been sent.';
    } else {
        // Don't reveal if email exists for security
        $_SESSION['success'] = 'If an account exists with this email, a password reset link has been sent.';
    }
    
    $stmt->close();
    $conn->close();
    header('Location: forgot_password.php');
    exit();
} else {
    header('Location: forgot_password.php');
    exit();
}
?>

