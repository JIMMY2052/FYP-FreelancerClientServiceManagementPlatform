<?php

/**
 * Background Jobs Manager
 * 
 * Manages background tasks like periodic cleanup
 * Uses file-based locking to prevent duplicate runs
 */

class BackgroundJobs
{

    private $conn;
    private $jobLockDir = __DIR__ . '/../uploads/.job_locks';

    /**
     * Constructor
     * 
     * @param mysqli $connection Database connection
     */
    public function __construct($connection)
    {
        $this->conn = $connection;
        $this->ensureLockDirExists();
    }

    /**
     * Ensure lock directory exists
     */
    private function ensureLockDirExists()
    {
        if (!is_dir($this->jobLockDir)) {
            @mkdir($this->jobLockDir, 0755, true);
        }
    }

    /**
     * Get lock file path for a job
     * 
     * @param string $jobName Job name
     * @return string Lock file path
     */
    private function getLockFilePath($jobName)
    {
        return $this->jobLockDir . '/' . $jobName . '.lock';
    }

    /**
     * Check if job is locked (already running)
     * 
     * @param string $jobName Job name
     * @param int $ttl Time-to-live in seconds (default: 10 minutes)
     * @return bool True if job is locked
     */
    private function isJobLocked($jobName, $ttl = 600)
    {
        $lockFile = $this->getLockFilePath($jobName);

        if (!file_exists($lockFile)) {
            return false;
        }

        $lastRun = filemtime($lockFile);
        $currentTime = time();

        // If lock file is older than TTL, it's stale, so not locked
        if (($currentTime - $lastRun) > $ttl) {
            @unlink($lockFile);
            return false;
        }

        return true;
    }

    /**
     * Lock a job (prevent duplicate runs)
     * 
     * @param string $jobName Job name
     * @return bool True if successfully locked
     */
    private function lockJob($jobName)
    {
        $lockFile = $this->getLockFilePath($jobName);

        try {
            return @file_put_contents($lockFile, time()) !== false;
        } catch (Exception $e) {
            error_log("[Background Jobs] Error locking job $jobName: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Unlock a job
     * 
     * @param string $jobName Job name
     */
    private function unlockJob($jobName)
    {
        $lockFile = $this->getLockFilePath($jobName);
        @unlink($lockFile);
    }

    /**
     * Check if job should run (every N minutes)
     * 
     * @param string $jobName Job name
     * @param int $intervalMinutes Interval in minutes
     * @return bool True if job should run
     */
    public function shouldJobRun($jobName, $intervalMinutes = 10)
    {
        $lockFile = $this->getLockFilePath($jobName);

        // If lock file doesn't exist, job should run
        if (!file_exists($lockFile)) {
            return true;
        }

        $lastRun = filemtime($lockFile);
        $currentTime = time();
        $intervalSeconds = $intervalMinutes * 60;

        // If interval has passed, job should run
        return ($currentTime - $lastRun) >= $intervalSeconds;
    }

    /**
     * Run cleanup expired passwords job
     * Runs only if interval has passed and not already running
     * 
     * @param int $intervalMinutes Interval in minutes (default: 10)
     * @return array Result of the job
     */
    public function runCleanupExpiredPasswordsJob($intervalMinutes = 10)
    {
        $jobName = 'cleanup_expired_passwords';

        // Check if job is locked (already running)
        if ($this->isJobLocked($jobName, $intervalMinutes * 60)) {
            return [
                'success' => false,
                'message' => 'Job already ran recently',
                'job_name' => $jobName,
                'interval_minutes' => $intervalMinutes,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        // Check if enough time has passed since last run
        if (!$this->shouldJobRun($jobName, $intervalMinutes)) {
            return [
                'success' => false,
                'message' => 'Interval not reached yet',
                'job_name' => $jobName,
                'interval_minutes' => $intervalMinutes,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        // Lock the job
        if (!$this->lockJob($jobName)) {
            return [
                'success' => false,
                'message' => 'Failed to acquire job lock',
                'job_name' => $jobName,
                'interval_minutes' => $intervalMinutes,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        try {
            // Create maintenance instance
            require_once __DIR__ . '/DatabaseMaintenance.php';
            $maintenance = new DatabaseMaintenance($this->conn);

            // Clean expired records
            $cleanExpired = $maintenance->cleanExpiredPasswordResets();

            // Clean used records
            $cleanUsed = $maintenance->cleanOldUsedRecords();

            $result = [
                'success' => true,
                'message' => 'Cleanup job completed successfully',
                'job_name' => $jobName,
                'interval_minutes' => $intervalMinutes,
                'expired_deleted' => $cleanExpired['deleted_count'],
                'used_deleted' => $cleanUsed['deleted_count'],
                'total_deleted' => $cleanExpired['deleted_count'] + $cleanUsed['deleted_count'],
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Log the result
            error_log("[Background Jobs] $jobName: Cleaned " . $result['total_deleted'] . " records");

            return $result;
        } catch (Exception $e) {
            error_log("[Background Jobs] Error running $jobName: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'job_name' => $jobName,
                'interval_minutes' => $intervalMinutes,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } finally {
            // Always unlock the job
            $this->unlockJob($jobName);
        }
    }

    /**
     * Get job status
     * 
     * @param string $jobName Job name
     * @return array Job status
     */
    public function getJobStatus($jobName)
    {
        $lockFile = $this->getLockFilePath($jobName);

        if (!file_exists($lockFile)) {
            return [
                'job_name' => $jobName,
                'status' => 'idle',
                'last_run' => 'never',
                'locked' => false
            ];
        }

        $lastRun = filemtime($lockFile);
        $lastRunTime = date('Y-m-d H:i:s', $lastRun);
        $minutesAgo = (time() - $lastRun) / 60;

        return [
            'job_name' => $jobName,
            'status' => 'active',
            'last_run' => $lastRunTime,
            'minutes_since_run' => round($minutesAgo, 2),
            'locked' => $this->isJobLocked($jobName)
        ];
    }
}
