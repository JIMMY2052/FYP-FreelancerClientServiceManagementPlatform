<?php
// Auto-create database tables for work submission
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

$conn = getDBConnection();

echo "<h2>Creating Work Submission Tables</h2>";

// Create work_submissions table
$sql1 = "CREATE TABLE IF NOT EXISTS work_submissions (
    SubmissionID INT PRIMARY KEY AUTO_INCREMENT,
    AgreementID INT NOT NULL,
    FreelancerID INT NOT NULL,
    ClientID INT NOT NULL,
    SubmissionTitle VARCHAR(255) NOT NULL,
    SubmissionNotes TEXT,
    Status ENUM('pending_review', 'approved', 'rejected', 'revision_requested') DEFAULT 'pending_review',
    ReviewNotes TEXT,
    ReviewedAt DATETIME,
    SubmittedAt DATETIME NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_agreement (AgreementID),
    INDEX idx_freelancer (FreelancerID),
    INDEX idx_client (ClientID),
    INDEX idx_status (Status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql1)) {
    echo "<p style='color: green;'>✓ Table 'work_submissions' created successfully (or already exists)</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating work_submissions table: " . $conn->error . "</p>";
}

// Create submission_files table
$sql2 = "CREATE TABLE IF NOT EXISTS submission_files (
    FileID INT PRIMARY KEY AUTO_INCREMENT,
    SubmissionID INT NOT NULL,
    OriginalFileName VARCHAR(255) NOT NULL,
    StoredFileName VARCHAR(255) NOT NULL,
    FilePath VARCHAR(500) NOT NULL,
    FileSize BIGINT NOT NULL,
    FileType VARCHAR(50),
    UploadedAt DATETIME NOT NULL,
    INDEX idx_submission (SubmissionID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql2)) {
    echo "<p style='color: green;'>✓ Table 'submission_files' created successfully (or already exists)</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating submission_files table: " . $conn->error . "</p>";
}

// Create notifications table
$sql3 = "CREATE TABLE IF NOT EXISTS notifications (
    NotificationID INT PRIMARY KEY AUTO_INCREMENT,
    UserID INT NOT NULL,
    UserType ENUM('client', 'freelancer', 'admin') NOT NULL,
    Message TEXT NOT NULL,
    RelatedType VARCHAR(50),
    RelatedID INT,
    CreatedAt DATETIME NOT NULL,
    IsRead TINYINT(1) DEFAULT 0,
    ReadAt DATETIME,
    INDEX idx_user (UserID, UserType),
    INDEX idx_read (IsRead),
    INDEX idx_created (CreatedAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql3)) {
    echo "<p style='color: green;'>✓ Table 'notifications' created successfully (or already exists)</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating notifications table: " . $conn->error . "</p>";
}

// Check agreement Status column
$checkStatus = $conn->query("SHOW COLUMNS FROM agreement LIKE 'Status'");
if ($checkStatus && $checkStatus->num_rows > 0) {
    $row = $checkStatus->fetch_assoc();
    
    if (strpos($row['Type'], 'pending_review') === false) {
        echo "<p style='color: orange;'>⚠ Updating agreement Status enum to include 'pending_review'...</p>";
        
        // Update the enum
        $sql4 = "ALTER TABLE agreement MODIFY COLUMN Status ENUM('to_accept', 'ongoing', 'pending_review', 'completed', 'cancelled') DEFAULT 'to_accept'";
        
        if ($conn->query($sql4)) {
            echo "<p style='color: green;'>✓ Agreement Status column updated successfully</p>";
        } else {
            echo "<p style='color: red;'>✗ Error updating agreement Status: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ Agreement Status column already includes 'pending_review'</p>";
    }
}

echo "<br><hr>";
echo "<h3>Setup Complete!</h3>";
echo "<p>All required tables have been created. You can now submit work.</p>";
echo "<p><a href='ongoing_projects.php'>Go to Ongoing Projects</a></p>";

$conn->close();
?>
