<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // Validate input
    if (empty($user_type) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        $_SESSION['form_data'] = ['email' => $email, 'user_type' => $user_type];
        header('Location: login.php');
        exit();
    }

    // Validate user type
    if (!in_array($user_type, ['freelancer', 'client'])) {
        $_SESSION['error'] = 'Invalid user type.';
        $_SESSION['form_data'] = ['email' => $email, 'user_type' => $user_type];
        header('Location: login.php');
        exit();
    }

    $conn = getDBConnection();

    // Determine table and ID column based on user type
    if ($user_type === 'freelancer') {
        $table = 'freelancer';
        $id_column = 'FreelancerID';
    } else {
        $table = 'client';
        $id_column = 'ClientID';
    }

    // Prepare and execute query
    // Note: this assumes a FailedLoginAttempts column exists on both tables
    $stmt = $conn->prepare("SELECT $id_column, Email, Password, Status, isDelete, FailedLoginAttempts FROM $table WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        $userId   = $user[$id_column];
        $status   = $user['Status'];
        $isDelete = $user['isDelete'];
        $attempts = isset($user['FailedLoginAttempts']) ? (int)$user['FailedLoginAttempts'] : 0;

        // Check if user is deleted
        if ($isDelete == 1) {
            $_SESSION['error'] = 'Your account has been deleted. Please contact support.';
        }
        // Check if user already blocked
        elseif (strtolower((string)$status) === 'blocked') {
            $_SESSION['error'] = 'Your account has been blocked due to multiple failed login attempts. Please contact support.';
        }
        // Verify password
        elseif (password_verify($password, $user['Password'])) {
            // Check if user is active (or status not set)
            if ($status === 'active' || $status === null || $status === '') {
                // Reset failed login attempts on successful login
                $resetStmt = $conn->prepare("UPDATE $table SET FailedLoginAttempts = 0 WHERE $id_column = ?");
                if ($resetStmt) {
                    $resetStmt->bind_param("i", $userId);
                    $resetStmt->execute();
                    $resetStmt->close();
                }

                // Set session variables
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_type'] = $user_type;
                $_SESSION['email'] = $user['Email'];

                // Set remember me cookie if checked
                if ($remember_me) {
                    $cookie_value = base64_encode($userId . ':' . $user_type);
                    setcookie('worksnyc_remember', $cookie_value, time() + (86400 * 30), '/'); // 30 days
                }

                // Redirect back to login page
                $_SESSION['success'] = 'Login successful! Welcome back.';
                header('Location: login.php');
                exit();
            } else {
                $_SESSION['error'] = 'Your account is not active. Please contact support.';
            }
        } else {
            // Wrong password: increase attempts and maybe block
            $attempts++;

            if ($attempts >= 3) {
                $updateStmt = $conn->prepare("UPDATE $table SET FailedLoginAttempts = ?, Status = 'blocked' WHERE $id_column = ?");
            } else {
                $updateStmt = $conn->prepare("UPDATE $table SET FailedLoginAttempts = ? WHERE $id_column = ?");
            }

            if ($updateStmt) {
                $updateStmt->bind_param("ii", $attempts, $userId);
                $updateStmt->execute();
                $updateStmt->close();
            }

            if ($attempts >= 3) {
                $_SESSION['error'] = 'Your account has been blocked after 3 failed login attempts. Please contact support.';
            } else {
                $remaining = 3 - $attempts;
                $_SESSION['error'] = 'Invalid email or password. You have ' . $remaining . ' attempt(s) remaining.';
            }
        }
    } else {
        // No user found with that email
        $_SESSION['error'] = 'Invalid email or password.';
    }

    // Preserve form data for re-display
    $_SESSION['form_data'] = ['email' => $email, 'user_type' => $user_type];

    $stmt->close();
    $conn->close();

    header('Location: login.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
