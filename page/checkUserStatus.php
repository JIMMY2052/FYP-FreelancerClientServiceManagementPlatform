<?php

/**
 * Check User Status - Validates if user account is deleted
 * Include this file after session_start() in all protected pages
 * This will automatically logout users whose accounts have been deleted
 */

// Ensure config is loaded for database connection
if (!function_exists('getDBConnection')) {
    require_once 'config.php';
}

if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    $conn = getDBConnection();

    if ($conn) {
        $user_id = $_SESSION['user_id'];
        $user_type = $_SESSION['user_type'];

        // Determine table and ID column based on user type
        $table = ($user_type === 'freelancer') ? 'freelancer' : 'client';
        $id_column = ($user_type === 'freelancer') ? 'FreelancerID' : 'ClientID';

        // Check if user is deleted
        $stmt = $conn->prepare("SELECT isDelete FROM $table WHERE $id_column = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // If user is deleted, logout immediately
                if ($user['isDelete'] == 1) {
                    // Clear all session variables
                    $_SESSION = array();

                    // Destroy the session
                    if (ini_get("session.use_cookies")) {
                        $params = session_get_cookie_params();
                        setcookie(
                            session_name(),
                            '',
                            time() - 42000,
                            $params["path"],
                            $params["domain"],
                            $params["secure"],
                            $params["httponly"]
                        );
                    }

                    session_destroy();

                    // Redirect to login with message
                    $_SESSION = array();
                    session_start();
                    $_SESSION['error'] = 'Your account has been deleted. You have been logged out.';
                    header('Location: ../page/login.php');
                    exit();
                }
            }

            $stmt->close();
        }

        $conn->close();
    }
}
