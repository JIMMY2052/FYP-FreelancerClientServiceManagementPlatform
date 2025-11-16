<?php
require_once 'config.php';

echo "<h2>Database Connection Test</h2>";

try {
    $conn = getDBConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        echo "<p><strong>Database:</strong> " . DB_NAME . "</p>";
        echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
        
        // Test query to list tables
        $result = $conn->query("SHOW TABLES");
        
        if ($result) {
            echo "<h3>Tables in database:</h3>";
            echo "<ul>";
            while ($row = $result->fetch_array()) {
                echo "<li>" . $row[0] . "</li>";
            }
            echo "</ul>";
        }
        
        // Test query to check if tables exist
        $tables = ['client', 'freelancer', 'job', 'application'];
        echo "<h3>Table Status:</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            $check = $conn->query("SHOW TABLES LIKE '$table'");
            if ($check && $check->num_rows > 0) {
                $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc();
                echo "<li style='color: green;'>✓ $table exists (" . $count['count'] . " rows)</li>";
            } else {
                echo "<li style='color: red;'>✗ $table does not exist</li>";
            }
        }
        echo "</ul>";
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database credentials in config.php</p>";
}
?>

