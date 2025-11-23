<?php

/**
 * Database Maintenance Utility
 * 
 * This class handles automatic database cleanup tasks
 * such as removing expired password reset records
 */

class DatabaseMaintenance
{

    private $conn;

    /**
     * Constructor
     * 
     * @param mysqli $connection Database connection
     */
    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    /**
     * Clean up expired password reset records
     * Removes all password reset records where ExpiresAt < NOW()
     * 
     * @return array Result with count of deleted records
     */
    public function cleanExpiredPasswordResets()
    {
        try {
            // SQL to delete expired records
            $sql = "DELETE FROM `password_reset` 
                    WHERE `ExpiresAt` < NOW()";

            $result = $this->conn->query($sql);

            if (!$result) {
                throw new Exception("Error deleting expired records: " . $this->conn->error);
            }

            $deletedCount = $this->conn->affected_rows;

            return [
                'success' => true,
                'message' => "Cleaned up $deletedCount expired password reset records",
                'deleted_count' => $deletedCount,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("[Database Maintenance] Error cleaning expired records: " . $e->getMessage());

            return [
                'success' => false,
                'message' => "Error: " . $e->getMessage(),
                'deleted_count' => 0,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Clean up all used password reset records
     * Removes all records that have been used (IsUsed = 1)
     * 
     * @return array Result with count of deleted records
     */
    public function cleanOldUsedRecords()
    {
        try {
            // SQL to delete all used records
            $sql = "DELETE FROM `password_reset` 
                    WHERE `IsUsed` = 1";

            $result = $this->conn->query($sql);

            if (!$result) {
                throw new Exception("Error deleting used records: " . $this->conn->error);
            }

            $deletedCount = $this->conn->affected_rows;

            return [
                'success' => true,
                'message' => "Cleaned up $deletedCount used password reset records",
                'deleted_count' => $deletedCount,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("[Database Maintenance] Error cleaning used records: " . $e->getMessage());

            return [
                'success' => false,
                'message' => "Error: " . $e->getMessage(),
                'deleted_count' => 0,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Get count of expired records (without deleting)
     * Useful for monitoring
     * 
     * @return int Count of expired records
     */
    public function getExpiredRecordCount()
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM `password_reset` 
                    WHERE `ExpiresAt` < NOW()";

            $result = $this->conn->query($sql);

            if (!$result) {
                throw new Exception("Error counting expired records: " . $this->conn->error);
            }

            $row = $result->fetch_assoc();
            return $row['count'] ?? 0;
        } catch (Exception $e) {
            error_log("[Database Maintenance] Error getting expired record count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get count of all password reset records
     * Useful for monitoring
     * 
     * @return int Total count of records
     */
    public function getTotalRecordCount()
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM `password_reset`";

            $result = $this->conn->query($sql);

            if (!$result) {
                throw new Exception("Error counting total records: " . $this->conn->error);
            }

            $row = $result->fetch_assoc();
            return $row['count'] ?? 0;
        } catch (Exception $e) {
            error_log("[Database Maintenance] Error getting total record count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Run all maintenance tasks
     * This is the main function to call for complete cleanup
     * 
     * @return array Results from all maintenance tasks
     */
    public function runAllMaintenanceTasks()
    {
        $results = [
            'timestamp' => date('Y-m-d H:i:s'),
            'tasks' => []
        ];

        // Clean expired password reset records
        $expiredCleanup = $this->cleanExpiredPasswordResets();
        $results['tasks']['expired_password_resets'] = $expiredCleanup;

        // Clean old used records
        $oldUsedCleanup = $this->cleanOldUsedRecords();
        $results['tasks']['old_used_records'] = $oldUsedCleanup;

        // Get status
        $results['status'] = [
            'total_records' => $this->getTotalRecordCount(),
            'expired_records' => $this->getExpiredRecordCount()
        ];

        return $results;
    }

    /**
     * Get database maintenance status
     * Returns current state of password_reset table
     * 
     * @return array Status information
     */
    public function getMaintenanceStatus()
    {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_records' => $this->getTotalRecordCount(),
            'expired_records' => $this->getExpiredRecordCount(),
            'needs_cleanup' => $this->getExpiredRecordCount() > 0
        ];
    }
}
