<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once 'config.php';

// Get POST parameters
$applicationId = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

// Validate inputs
if ($applicationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    exit();
}

if (!in_array($status, ['pending', 'accepted', 'rejected', 'withdrawn'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

$clientID = $_SESSION['user_id'];

try {
    $conn = getDBConnection();
    
    // Verify that this application belongs to a job owned by the client
    $verifySql = "SELECT ja.ApplicationID, ja.JobID, j.ClientID, j.Title as JobTitle, f.Email as FreelancerEmail
                  FROM job_application ja
                  INNER JOIN job j ON ja.JobID = j.JobID
                  INNER JOIN freelancer f ON ja.FreelancerID = f.FreelancerID
                  WHERE ja.ApplicationID = ? AND j.ClientID = ?";
    
    $verifyStmt = $conn->prepare($verifySql);
    $verifyStmt->bind_param('ii', $applicationId, $clientID);
    $verifyStmt->execute();
    $result = $verifyStmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Application not found or unauthorized']);
        exit();
    }
    
    $appData = $result->fetch_assoc();
    $jobId = $appData['JobID'];
    $verifyStmt->close();
    
    // If accepting an application, reject all other pending applications for the same job
    if ($status === 'accepted') {
        // First, reject all other pending applications for this job
        $rejectOthersSql = "UPDATE job_application 
                           SET Status = 'rejected', UpdatedAt = NOW() 
                           WHERE JobID = ? 
                           AND ApplicationID != ? 
                           AND Status = 'pending'";
        $rejectStmt = $conn->prepare($rejectOthersSql);
        $rejectStmt->bind_param('ii', $jobId, $applicationId);
        $rejectStmt->execute();
        $rejectedCount = $rejectStmt->affected_rows;
        $rejectStmt->close();
        
        error_log("[update_application_status] Rejected $rejectedCount other pending applications for job $jobId");
    }
    
    // Update application status
    $updateSql = "UPDATE job_application SET Status = ?, UpdatedAt = NOW() WHERE ApplicationID = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('si', $status, $applicationId);
    
    if ($updateStmt->execute()) {
        // Log the action
        error_log("[update_application_status] Client $clientID updated application $applicationId to status: $status");
        
        // TODO: Send notification to freelancer (optional)
        // You can implement email notification here if needed
        
        echo json_encode([
            'success' => true, 
            'message' => 'Application status updated successfully',
            'new_status' => $status
        ]);
    } else {
        error_log("[update_application_status] Failed to update application $applicationId: " . $updateStmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to update application status']);
    }
    
    $updateStmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("[update_application_status] Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating the application']);
}
?>
