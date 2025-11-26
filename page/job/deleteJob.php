<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../login.php');
    exit();
}

require_once '../config.php';

// Get job ID from URL
$jobID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$clientID = $_SESSION['user_id'];

if (!$jobID) {
    $_SESSION['error'] = 'Invalid job ID.';
    header('Location: ../my_jobs.php');
    exit();
}

$conn = getDBConnection();

// Verify job belongs to this client and get current status
$sql = "SELECT JobID, Status FROM job WHERE JobID = ? AND ClientID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $jobID, $clientID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Job not found or you do not have permission to delete it.';
    $stmt->close();
    $conn->close();
    header('Location: ../my_jobs.php');
    exit();
}

$job = $result->fetch_assoc();
$stmt->close();

// Check if job can be deleted (only available or closed jobs can be deleted)
if ($job['Status'] === 'processing') {
    $_SESSION['error'] = 'Cannot delete a job that is currently being processed.';
    $conn->close();
    header('Location: client_job_details.php?id=' . $jobID);
    exit();
}

// Update job status to deleted
$sql = "UPDATE job SET Status = 'deleted' WHERE JobID = ? AND ClientID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $jobID, $clientID);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Job deleted successfully.';
    $stmt->close();
    $conn->close();
    header('Location: ../my_jobs.php');
    exit();
} else {
    $_SESSION['error'] = 'Failed to delete job. Please try again.';
    $stmt->close();
    $conn->close();
    header('Location: client_job_details.php?id=' . $jobID);
    exit();
}
?>
