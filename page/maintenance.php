#!/usr/bin/env php
<?php
/**
 * Database Maintenance CLI Script
 * 
 * Run from command line:
 * php page/maintenance.php
 * 
 * This script can be:
 * 1. Run manually for immediate cleanup
 * 2. Scheduled with cron job for periodic cleanup
 * 3. Used for maintenance monitoring
 */

// Set environment
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/DatabaseMaintenance.php';
require_once __DIR__ . '/BackgroundJobs.php';

// Get database connection
$conn = getDBConnection();

// Create maintenance instance
$maintenance = new DatabaseMaintenance($conn);

// Create background jobs instance
$backgroundJobs = new BackgroundJobs($conn);

// Parse command line arguments
$action = isset($argv[1]) ? $argv[1] : 'help';

switch ($action) {
    case 'cleanup':
    case 'clean':
        // Clean expired records
        echo "═══════════════════════════════════════\n";
        echo "Database Maintenance - Cleanup Expired Records\n";
        echo "═══════════════════════════════════════\n\n";

        $results = $maintenance->cleanExpiredPasswordResets();

        if ($results['success']) {
            echo "✅ SUCCESS\n";
            echo "Message: " . $results['message'] . "\n";
            echo "Records Deleted: " . $results['deleted_count'] . "\n";
        } else {
            echo "❌ FAILED\n";
            echo "Error: " . $results['message'] . "\n";
        }

        echo "Timestamp: " . $results['timestamp'] . "\n\n";
        break;

    case 'cleanup-old':
    case 'clean-old':
        // Clean old used records
        echo "═══════════════════════════════════════\n";
        echo "Database Maintenance - Clean Old Used Records\n";
        echo "═══════════════════════════════════════\n\n";

        $results = $maintenance->cleanOldUsedRecords();

        if ($results['success']) {
            echo "✅ SUCCESS\n";
            echo "Message: " . $results['message'] . "\n";
            echo "Records Deleted: " . $results['deleted_count'] . "\n";
        } else {
            echo "❌ FAILED\n";
            echo "Error: " . $results['message'] . "\n";
        }

        echo "Timestamp: " . $results['timestamp'] . "\n\n";
        break;

    case 'full':
    case 'all':
        // Run all maintenance tasks
        echo "═══════════════════════════════════════\n";
        echo "Database Maintenance - Full Cleanup\n";
        echo "═══════════════════════════════════════\n\n";

        $results = $maintenance->runAllMaintenanceTasks();

        echo "📊 Results:\n";
        echo "─────────────────────────────────────\n";

        foreach ($results['tasks'] as $taskName => $taskResult) {
            $status = $taskResult['success'] ? '✅' : '❌';
            echo "$status $taskName: " . $taskResult['deleted_count'] . " records deleted\n";
        }

        echo "\n📈 Status:\n";
        echo "─────────────────────────────────────\n";
        echo "Total Records: " . $results['status']['total_records'] . "\n";
        echo "Expired Records: " . $results['status']['expired_records'] . "\n";
        echo "Timestamp: " . $results['timestamp'] . "\n\n";
        break;

    case 'status':
        // Get maintenance status
        echo "═══════════════════════════════════════\n";
        echo "Database Maintenance - Status\n";
        echo "═══════════════════════════════════════\n\n";

        $status = $maintenance->getMaintenanceStatus();

        echo "Total Records: " . $status['total_records'] . "\n";
        echo "Expired Records: " . $status['expired_records'] . "\n";
        echo "Needs Cleanup: " . ($status['needs_cleanup'] ? 'Yes' : 'No') . "\n";
        echo "Timestamp: " . $status['timestamp'] . "\n\n";
        break;

    case 'job-status':
        // Get background job status
        echo "═══════════════════════════════════════\n";
        echo "Background Job Status\n";
        echo "═══════════════════════════════════════\n\n";

        $jobStatus = $backgroundJobs->getJobStatus('cleanup_expired_passwords');

        echo "Job Name: " . $jobStatus['job_name'] . "\n";
        echo "Status: " . $jobStatus['status'] . "\n";
        echo "Last Run: " . $jobStatus['last_run'] . "\n";

        if ($jobStatus['status'] === 'active') {
            echo "Minutes Since Run: " . $jobStatus['minutes_since_run'] . "\n";
            echo "Locked: " . ($jobStatus['locked'] ? 'Yes' : 'No') . "\n";
        }

        echo "\nNext Run In: 10 minutes (from last run)\n\n";
        break;

    case 'job-run':
        // Force run background job
        echo "═══════════════════════════════════════\n";
        echo "Running Background Cleanup Job\n";
        echo "═══════════════════════════════════════\n\n";

        $result = $backgroundJobs->runCleanupExpiredPasswordsJob(0);

        echo "Job: " . $result['job_name'] . "\n";
        echo "Status: " . ($result['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
        echo "Message: " . $result['message'] . "\n";

        if ($result['success']) {
            echo "Expired Deleted: " . $result['expired_deleted'] . "\n";
            echo "Used Deleted: " . $result['used_deleted'] . "\n";
            echo "Total Deleted: " . $result['total_deleted'] . "\n";
        }

        echo "Timestamp: " . $result['timestamp'] . "\n\n";
        break;

    case 'help':
    default:
        // Display help
        echo "╔═══════════════════════════════════════════════════╗\n";
        echo "║  Database Maintenance CLI Tool                    ║\n";
        echo "║  FYP - Freelancer Client Service Management      ║\n";
        echo "╚═══════════════════════════════════════════════════╝\n\n";

        echo "USAGE:\n";
        echo "  php page/maintenance.php [command]\n\n";

        echo "COMMANDS:\n";
        echo "  cleanup, clean         Clean expired password reset records\n";
        echo "  cleanup-old, clean-old Clean used password reset records\n";
        echo "  full, all              Run all maintenance tasks\n";
        echo "  status                 Get database maintenance status\n";
        echo "  job-status             Get background job status\n";
        echo "  job-run                Force run background cleanup job\n";
        echo "  help                   Display this help message\n\n";

        echo "EXAMPLES:\n";
        echo "  php page/maintenance.php cleanup\n";
        echo "  php page/maintenance.php full\n";
        echo "  php page/maintenance.php status\n";
        echo "  php page/maintenance.php job-status\n";
        echo "  php page/maintenance.php job-run\n\n";

        echo "SCHEDULING (Cron Job):\n";
        echo "  Add to crontab to run daily cleanup:\n";
        echo "  0 2 * * * cd /path/to/project && php page/maintenance.php cleanup\n\n";

        break;
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}

exit(0);
