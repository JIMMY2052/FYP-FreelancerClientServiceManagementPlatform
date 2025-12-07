<?php
// Set timezone to Malaysia (UTC+8)
date_default_timezone_set('Asia/Kuala_Lumpur');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fyp');

// Create database connection
if (!function_exists('getDBConnection')) {
    function getDBConnection()
    {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $conn->set_charset("utf8mb4");
            return $conn;
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== SYSTEM INITIALIZATION & MAINTENANCE =====
// Include system initialization for automatic database cleanup
require_once __DIR__ . '/SystemInit.php';

// Store database connection in globals for maintenance access
$conn = getDBConnection();
$GLOBALS['conn'] = $conn;

// Run system initialization (database cleanup, etc.)
runSystemInitialization();
