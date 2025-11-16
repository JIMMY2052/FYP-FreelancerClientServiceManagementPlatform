<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: admin_login.php');
        exit();
    }

    $conn = getDBConnection();

    // Query admin table for credentials
    $stmt = $conn->prepare("SELECT AdminID, Email, Password, Status FROM admin WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $admin['Password'])) {
            // Check if admin account is active
            if ($admin['Status'] === 'active' || $admin['Status'] === null || $admin['Status'] === '') {
                // Set session variables
                $_SESSION['admin_id'] = $admin['AdminID'];
                $_SESSION['admin_email'] = $admin['Email'];
                $_SESSION['is_admin'] = true;

                // Set remember me cookie if checked
                if ($remember_me) {
                    $cookie_value = base64_encode($admin['AdminID'] . ':admin');
                    setcookie('worksnyc_admin_remember', $cookie_value, time() + (86400 * 30), '/'); // 30 days
                }

                // Redirect to admin dashboard
                header('Location: admin_dashboard.php');
                exit();
            } else {
                $_SESSION['error'] = 'Your admin account is not active. Please contact support.';
            }
        } else {
            $_SESSION['error'] = 'Invalid email or password.';
        }
    } else {
        $_SESSION['error'] = 'Invalid email or password.';
    }

    $stmt->close();
    $conn->close();

    header('Location: admin_login.php');
    exit();
}
