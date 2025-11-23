<?php

/**
 * System Initialization & Background Jobs
 * 
 * This file runs essential system maintenance tasks
 * Should be included at the very beginning of application startup
 * 
 * Include this in config.php or index.php to run on every page load
 */

// Include the background jobs class
require_once __DIR__ . '/BackgroundJobs.php';

/**
 * Initialize System Maintenance
 * This function runs background jobs at configured intervals
 */
function initializeSystemMaintenance()
{
    try {
        // Check if we have a database connection
        if (!isset($GLOBALS['db_connection']) && !isset($GLOBALS['conn'])) {
            // If no connection in globals, we'll skip for now
            // This is handled when config.php is properly loaded
            return false;
        }

        // Get database connection from config
        $conn = isset($GLOBALS['conn']) ? $GLOBALS['conn'] : (isset($GLOBALS['db_connection']) ? $GLOBALS['db_connection'] : null);

        if (!$conn) {
            return false;
        }

        // Create background jobs instance
        $jobs = new BackgroundJobs($conn);

        // Run cleanup job every 10 minutes
        $result = $jobs->runCleanupExpiredPasswordsJob(10);

        // Log the result
        if ($result['success']) {
            error_log("[System Init] Background Job: Cleaned " . $result['total_deleted'] . " records");
        }

        return true;
    } catch (Exception $e) {
        error_log("[System Init] Error during system initialization: " . $e->getMessage());
        return false;
    }
}

/**
 * Run initialization on system startup
 * This will be called automatically when config.php is loaded
 */
function runSystemInitialization()
{
    // Check if we're in a valid application context
    if (php_sapi_name() === 'cli') {
        // Command line - skip automated maintenance
        return false;
    }

    // Run maintenance initialization
    // This is safe to call multiple times (once per page load)
    // The cleanup job uses file locking to run only every 10 minutes
    return initializeSystemMaintenance();
}
