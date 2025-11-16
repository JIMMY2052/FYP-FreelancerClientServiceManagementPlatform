<?php

session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: /index.php');
    exit();
}

// Include database config
require_once __DIR__ . '/../../page/config.php';

// Get form data
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$budget = isset($_POST['budget']) ? floatval($_POST['budget']) : 0;
$postDate = isset($_POST['postDate']) ? trim($_POST['postDate']) : '';
$postTime = isset($_POST['postTime']) ? trim($_POST['postTime']) : '';
$deadline = isset($_POST['deadline']) ? trim($_POST['deadline']) : '';

// Combine postDate and postTime into a single datetime
$postDateTime = $postDate . ' ' . $postTime;

// Validate input
if (empty($title) || empty($description) || empty($budget) || empty($deadline) || empty($postDate) || empty($postTime)) {
    $_SESSION['error'] = 'Please fill in all required fields.';
    header('Location: /job/create/createJob.php?error=missing_fields');
    exit();
}

try {
    $conn = getDBConnection();
    
    // Insert job into database
    $clientID = $_SESSION['user_id'];
    $status = 'active'; // Default status when job is created
    
    $stmt = $conn->prepare("INSERT INTO job (ClientID, Title, Description, Budget, Deadline, Status, PostDate) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdsss", $clientID, $title, $description, $budget, $deadline, $status, $postDateTime);
    
    if ($stmt->execute()) {
        $jobID = $stmt->insert_id;
        $_SESSION['success'] = 'Job posted successfully!';
        $_SESSION['new_job_id'] = $jobID;
        $stmt->close();
        $conn->close();
        
        // Redirect to my jobs page
        header('Location: /page/my_jobs.php');
        exit();
    } else {
        $_SESSION['error'] = 'Error creating job. Please try again.';
        $stmt->close();
        $conn->close();
        header('Location: /job/create/createJob.php?error=insert_failed');
        exit();
    }
    
} catch (Exception $e) {
    // Log error (optional)
    error_log('Database error: ' . $e->getMessage());
    
    $_SESSION['error'] = 'Database error. Please try again.';
    header('Location: /job/create/createJob.php?error=db_error');
    exit();
}

?>