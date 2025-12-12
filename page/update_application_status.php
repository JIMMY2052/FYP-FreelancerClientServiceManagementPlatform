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
$rejectionReason = isset($_POST['rejection_reason']) ? trim($_POST['rejection_reason']) : '';

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
    $verifySql = "SELECT ja.ApplicationID, ja.JobID, ja.FreelancerID, j.ClientID, j.Title as JobTitle, f.Email as FreelancerEmail, f.FirstName, f.LastName
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
    $freelancerId = $appData['FreelancerID'];
    $freelancerFirstName = $appData['FirstName'];
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
        
        // If rejected and there's a rejection reason, send it via message
        if ($status === 'rejected' && !empty($rejectionReason)) {
            try {
                // Create or get conversation with the freelancer
                $convSql = "SELECT ConversationID FROM conversation 
                           WHERE (User1ID = ? AND User1Type = 'client' AND User2ID = ? AND User2Type = 'freelancer')
                           OR (User1ID = ? AND User1Type = 'freelancer' AND User2ID = ? AND User2Type = 'client')
                           LIMIT 1";
                
                $convStmt = $conn->prepare($convSql);
                $convStmt->bind_param('iiii', $clientID, $freelancerId, $freelancerId, $clientID);
                $convStmt->execute();
                $convResult = $convStmt->get_result();
                
                $conversationID = null;
                if ($convResult->num_rows > 0) {
                    $convRow = $convResult->fetch_assoc();
                    $conversationID = $convRow['ConversationID'];
                } else {
                    // Create new conversation
                    $createConvSql = "INSERT INTO conversation (User1ID, User1Type, User2ID, User2Type, CreatedAt, Status) 
                                     VALUES (?, 'client', ?, 'freelancer', NOW(), 'active')";
                    $createConvStmt = $conn->prepare($createConvSql);
                    $createConvStmt->bind_param('ii', $clientID, $freelancerId);
                    $createConvStmt->execute();
                    $conversationID = $createConvStmt->insert_id;
                    $createConvStmt->close();
                }
                
                $convStmt->close();
                
                // Create the rejection message as a structured form
                $messageData = [
                    'type' => 'application_rejection',
                    'job_title' => $appData['JobTitle'],
                    'rejection_reason' => $rejectionReason,
                    'rejected_at' => date('Y-m-d H:i:s')
                ];
                $messageContent = json_encode($messageData);
                
                // Format sender and receiver IDs as per system format (c{id} for client, f{id} for freelancer)
                $senderID = 'c' . $clientID;
                $receiverID = 'f' . $freelancerId;
                
                // Insert message into the message table
                $msgSql = "INSERT INTO message (ConversationID, SenderID, ReceiverID, Content, Timestamp, Status) 
                          VALUES (?, ?, ?, ?, NOW(), 'unread')";
                
                $msgStmt = $conn->prepare($msgSql);
                $msgStmt->bind_param('isss', $conversationID, $senderID, $receiverID, $messageContent);
                
                if ($msgStmt->execute()) {
                    // Update conversation's last message timestamp
                    $updateConvSql = "UPDATE conversation SET LastMessageAt = NOW() WHERE ConversationID = ?";
                    $updateConvStmt = $conn->prepare($updateConvSql);
                    $updateConvStmt->bind_param('i', $conversationID);
                    $updateConvStmt->execute();
                    $updateConvStmt->close();
                    
                    error_log("[update_application_status] Rejection reason sent to freelancer $freelancerId in conversation $conversationID");
                } else {
                    error_log("[update_application_status] Failed to send rejection message: " . $msgStmt->error);
                }
                
                $msgStmt->close();
            } catch (Exception $e) {
                error_log("[update_application_status] Error sending rejection message: " . $e->getMessage());
                // Don't fail the application update if message fails
            }
        }
        
        // If accepted, send acceptance message to freelancer
        if ($status === 'accepted') {
            try {
                // Create or get conversation with the freelancer
                $convSql = "SELECT ConversationID FROM conversation 
                           WHERE (User1ID = ? AND User1Type = 'client' AND User2ID = ? AND User2Type = 'freelancer')
                           OR (User1ID = ? AND User1Type = 'freelancer' AND User2ID = ? AND User2Type = 'client')
                           LIMIT 1";
                
                $convStmt = $conn->prepare($convSql);
                $convStmt->bind_param('iiii', $clientID, $freelancerId, $freelancerId, $clientID);
                $convStmt->execute();
                $convResult = $convStmt->get_result();
                
                $conversationID = null;
                if ($convResult->num_rows > 0) {
                    $convRow = $convResult->fetch_assoc();
                    $conversationID = $convRow['ConversationID'];
                } else {
                    // Create new conversation
                    $createConvSql = "INSERT INTO conversation (User1ID, User1Type, User2ID, User2Type, CreatedAt, Status) 
                                     VALUES (?, 'client', ?, 'freelancer', NOW(), 'active')";
                    $createConvStmt = $conn->prepare($createConvSql);
                    $createConvStmt->bind_param('ii', $clientID, $freelancerId);
                    $createConvStmt->execute();
                    $conversationID = $createConvStmt->insert_id;
                    $createConvStmt->close();
                }
                
                $convStmt->close();
                
                // Create the acceptance message as a structured form
                $messageData = [
                    'type' => 'application_accepted',
                    'job_title' => $appData['JobTitle'],
                    'accepted_at' => date('Y-m-d H:i:s')
                ];
                $messageContent = json_encode($messageData);
                
                // Format sender and receiver IDs as per system format (c{id} for client, f{id} for freelancer)
                $senderID = 'c' . $clientID;
                $receiverID = 'f' . $freelancerId;
                
                // Insert message into the message table
                $msgSql = "INSERT INTO message (ConversationID, SenderID, ReceiverID, Content, Timestamp, Status) 
                          VALUES (?, ?, ?, ?, NOW(), 'unread')";
                
                $msgStmt = $conn->prepare($msgSql);
                $msgStmt->bind_param('isss', $conversationID, $senderID, $receiverID, $messageContent);
                
                if ($msgStmt->execute()) {
                    // Update conversation's last message timestamp
                    $updateConvSql = "UPDATE conversation SET LastMessageAt = NOW() WHERE ConversationID = ?";
                    $updateConvStmt = $conn->prepare($updateConvSql);
                    $updateConvStmt->bind_param('i', $conversationID);
                    $updateConvStmt->execute();
                    $updateConvStmt->close();
                    
                    error_log("[update_application_status] Acceptance message sent to freelancer $freelancerId in conversation $conversationID");
                } else {
                    error_log("[update_application_status] Failed to send acceptance message: " . $msgStmt->error);
                }
                
                $msgStmt->close();
            } catch (Exception $e) {
                error_log("[update_application_status] Error sending acceptance message: " . $e->getMessage());
                // Don't fail the application update if message fails
            }
        }
        
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
