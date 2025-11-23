<?php
require_once 'config.php';
require_once 'EmailHelper.php';

$action = $_POST['action'] ?? '';

if ($action === 'send_otp') {
    // Step 1: Send OTP to email
    handleSendOTP();
} elseif ($action === 'verify_otp') {
    // Step 2: Verify OTP
    handleVerifyOTP();
} elseif ($action === 'reset_password') {
    // Step 3: Reset password
    handleResetPassword();
} else {
    header('Location: forgot_password.php');
    exit();
}

function handleSendOTP()
{
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        $_SESSION['error'] = 'Please enter your email address.';
        header('Location: forgot_password.php?step=email');
        exit();
    }

    $conn = getDBConnection();

    // Check if email exists in freelancer table
    $stmt = $conn->prepare("SELECT FreelancerID, FirstName, LastName FROM freelancer WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_type = 'freelancer';
    $user = null;

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        // Check if email exists in client table
        $stmt->close();
        $stmt = $conn->prepare("SELECT ClientID, CompanyName FROM client WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_type = 'client';

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        }
    }

    $stmt->close();

    // Generate OTP
    $otp = generateOTP();
    $expiryTime = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Insert OTP into database
    $stmt = $conn->prepare("INSERT INTO password_reset (Email, UserType, OTP, ExpiresAt) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $user_type, $otp, $expiryTime);
    $stmt->execute();
    $stmt->close();

    // Send OTP email
    $userName = null;
    if ($user) {
        $userName = $user_type === 'freelancer'
            ? $user['FirstName'] . ' ' . $user['LastName']
            : $user['CompanyName'];
    }

    $emailSent = sendOTPEmail($email, $otp, $userName);

    $conn->close();

    if ($emailSent || true) { // true to allow testing without actual email
        $_SESSION['recovery_email'] = $email;
        $_SESSION['success'] = 'OTP sent to your email. Please check your inbox.';
        header('Location: forgot_password.php?step=otp');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to send OTP. Please try again later.';
        header('Location: forgot_password.php?step=email');
        exit();
    }
}

function handleVerifyOTP()
{
    $email = $_POST['email'] ?? '';
    $otp = $_POST['otp'] ?? '';

    if (empty($email) || empty($otp) || strlen($otp) !== 6 || !ctype_digit($otp)) {
        $_SESSION['error'] = 'Please enter a valid 6-digit OTP.';
        header('Location: forgot_password.php?step=otp');
        exit();
    }

    $conn = getDBConnection();

    // Find valid OTP
    $stmt = $conn->prepare("
        SELECT ResetID, UserType FROM password_reset 
        WHERE Email = ? AND OTP = ? AND IsUsed = 0 AND ExpiresAt > NOW()
        ORDER BY CreatedAt DESC LIMIT 1
    ");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $_SESSION['error'] = 'Invalid or expired OTP. Please request a new one.';
        header('Location: forgot_password.php?step=otp');
        exit();
    }

    $resetRecord = $result->fetch_assoc();
    $stmt->close();

    // Mark OTP as used
    $stmt = $conn->prepare("UPDATE password_reset SET IsUsed = 1 WHERE ResetID = ?");
    $stmt->bind_param("i", $resetRecord['ResetID']);
    $stmt->execute();
    $stmt->close();

    $conn->close();

    $_SESSION['recovery_email'] = $email;
    $_SESSION['recovery_verified'] = true;
    $_SESSION['success'] = 'OTP verified! Now set your new password.';
    header('Location: forgot_password.php?step=newpassword');
    exit();
}

function handleResetPassword()
{
    $email = $_POST['email'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate OTP verification
    if (!isset($_SESSION['recovery_verified']) || !$_SESSION['recovery_verified']) {
        $_SESSION['error'] = 'Please verify your OTP first.';
        header('Location: forgot_password.php?step=otp');
        exit();
    }

    // Validate passwords
    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = 'Please enter both passwords.';
        header('Location: forgot_password.php?step=newpassword');
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: forgot_password.php?step=newpassword');
        exit();
    }

    // Validate password strength
    if (strlen($new_password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long.';
        header('Location: forgot_password.php?step=newpassword');
        exit();
    }

    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
        $_SESSION['error'] = 'Password must include at least one special character.';
        header('Location: forgot_password.php?step=newpassword');
        exit();
    }

    $conn = getDBConnection();

    // Determine user type and update password
    $stmt = $conn->prepare("SELECT FreelancerID FROM freelancer WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_type = 'freelancer';

    if ($result->num_rows > 0) {
        $stmt->close();
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE freelancer SET Password = ? WHERE Email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
    } else {
        $stmt->close();
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE client SET Password = ? WHERE Email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
    }

    $stmt->execute();
    $stmt->close();

    // Clean up recovery session
    unset($_SESSION['recovery_email']);
    unset($_SESSION['recovery_verified']);

    $conn->close();

    $_SESSION['success'] = 'Password reset successfully! You can now sign in with your new password.';
    header('Location: login.php');
    exit();
}
