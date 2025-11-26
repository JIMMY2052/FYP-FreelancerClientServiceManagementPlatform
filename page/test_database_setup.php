<?php
// Test script to check if database tables exist for work submission
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

$conn = getDBConnection();

echo "<h2>Database Tables Check</h2>";

// Check work_submissions table
$result = $conn->query("SHOW TABLES LIKE 'work_submissions'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Table 'work_submissions' exists</p>";
    
    // Show structure
    $structure = $conn->query("DESCRIBE work_submissions");
    echo "<h3>work_submissions structure:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Table 'work_submissions' does NOT exist</p>";
    echo "<p><strong>Action needed:</strong> Run the SQL script to create tables.</p>";
}

echo "<br>";

// Check submission_files table
$result = $conn->query("SHOW TABLES LIKE 'submission_files'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Table 'submission_files' exists</p>";
    
    // Show structure
    $structure = $conn->query("DESCRIBE submission_files");
    echo "<h3>submission_files structure:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Table 'submission_files' does NOT exist</p>";
}

echo "<br>";

// Check notifications table
$result = $conn->query("SHOW TABLES LIKE 'notifications'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Table 'notifications' exists</p>";
} else {
    echo "<p style='color: red;'>✗ Table 'notifications' does NOT exist</p>";
}

echo "<br>";

// Check agreement table Status column
$result = $conn->query("SHOW COLUMNS FROM agreement LIKE 'Status'");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<h3>Agreement Status Column:</h3>";
    echo "<pre>";
    print_r($row);
    echo "</pre>";
    
    // Check if 'pending_review' is in the enum
    if (strpos($row['Type'], 'pending_review') !== false) {
        echo "<p style='color: green;'>✓ 'pending_review' status is available in agreement table</p>";
    } else {
        echo "<p style='color: red;'>✗ 'pending_review' status is NOT in agreement table enum</p>";
        echo "<p><strong>Action needed:</strong> Update agreement.Status enum to include 'pending_review'</p>";
        echo "<p>Run this SQL: <code>ALTER TABLE agreement MODIFY COLUMN Status ENUM('to_accept', 'ongoing', 'pending_review', 'completed', 'cancelled') DEFAULT 'to_accept';</code></p>";
    }
}

echo "<br><hr>";
echo "<h3>SQL Script to Create Missing Tables:</h3>";
echo "<p>If any tables are missing, run the SQL script located at:</p>";
echo "<p><strong>setup_work_submission_tables.sql</strong></p>";
echo "<p>You can execute it in phpMyAdmin or MySQL command line.</p>";

$conn->close();
?>
