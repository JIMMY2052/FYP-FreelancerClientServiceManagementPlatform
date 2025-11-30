<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../index.php');
    exit();
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ongoing_projects.php');
    exit();
}

$client_id = $_SESSION['user_id'];
$submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : null;
$agreement_id = isset($_POST['agreement_id']) ? intval($_POST['agreement_id']) : null;
$freelancer_id = isset($_POST['freelancer_id']) ? intval($_POST['freelancer_id']) : null;
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$review_notes = isset($_POST['review_notes']) ? trim($_POST['review_notes']) : '';

// Validation
if (!$submission_id || !$agreement_id || !$freelancer_id || !in_array($action, ['approve', 'reject'])) {
    $_SESSION['error'] = 'Invalid request parameters.';
    header('Location: ongoing_projects.php');
    exit();
}

if ($action === 'reject' && empty($review_notes)) {
    $_SESSION['error'] = 'Please provide review notes when requesting revisions.';
    header('Location: review_work.php?submission_id=' . $submission_id);
    exit();
}

$conn = getDBConnection();

// Verify submission belongs to this client and is pending review
$sql = "SELECT ws.SubmissionID, ws.Status, a.PaymentAmount, a.ProjectTitle, a.FreelancerID, a.RemainingRevisions
        FROM work_submissions ws
        JOIN agreement a ON ws.AgreementID = a.AgreementID
        WHERE ws.SubmissionID = ? AND ws.ClientID = ? AND ws.Status = 'pending_review'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $submission_id, $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Submission not found, already reviewed, or you do not have permission.';
    $stmt->close();
    $conn->close();
    header('Location: ongoing_projects.php');
    exit();
}

$submission = $result->fetch_assoc();
$payment_amount = $submission['PaymentAmount'];
$project_title = $submission['ProjectTitle'];
$agreement_freelancer_id = $submission['FreelancerID'];
$remaining_revisions = $submission['RemainingRevisions'];
$is_unlimited = ($remaining_revisions === null);
$stmt->close();

// Check if client is trying to request revision but no revisions remaining (and not unlimited)
if ($action === 'reject' && !$is_unlimited && $remaining_revisions <= 0) {
    $_SESSION['error'] = 'No revisions remaining. You can only accept the work.';
    $conn->close();
    header('Location: review_work.php?submission_id=' . $submission_id);
    exit();
}

// Get JobID from job_application table
$job_id = null;
$sql = "SELECT ja.JobID 
        FROM job_application ja
        JOIN job j ON ja.JobID = j.JobID
        WHERE ja.FreelancerID = ? AND ja.Status = 'accepted' AND j.ClientID = ?
        ORDER BY ja.AppliedAt DESC
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $agreement_freelancer_id, $client_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $job_data = $result->fetch_assoc();
    $job_id = $job_data['JobID'];
}
$stmt->close();

// Begin transaction
$conn->begin_transaction();

try {
    if ($action === 'approve') {
        // ========== APPROVE WORK ==========
        
        // 1. Update submission status to approved
        $sql = "UPDATE work_submissions 
                SET Status = 'approved', ReviewNotes = ?, ReviewedAt = NOW() 
                WHERE SubmissionID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $review_notes, $submission_id);
        $stmt->execute();
        $stmt->close();
        
        // 2. Update agreement status to completed
        $sql = "UPDATE agreement SET Status = 'completed' WHERE AgreementID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $agreement_id);
        $stmt->execute();
        $stmt->close();
        
        // 2b. Update job status to 'completed' if JobID exists
        if (!empty($job_id)) {
            $sql = "UPDATE job SET Status = 'completed' WHERE JobID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $job_id);
            if (!$stmt->execute()) {
                error_log("Failed to update job status to completed: " . $stmt->error);
            }
            $stmt->close();
        }
        
        // 3. Release escrow funds to freelancer
        // Get escrow record
        $sql = "SELECT EscrowID, Amount FROM escrow 
                WHERE OrderID = ? AND PayerID = ? AND PayeeID = ? AND Status = 'hold'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iii', $agreement_id, $client_id, $freelancer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            throw new Exception("No escrow record found for this agreement. Agreement ID: {$agreement_id}, Client ID: {$client_id}, Freelancer ID: {$freelancer_id}");
        }
        
        $escrow = $result->fetch_assoc();
        $escrow_id = $escrow['EscrowID'];
        $escrow_amount = $escrow['Amount'];
        $stmt->close();
        
        // Update escrow status to released
        $sql = "UPDATE escrow SET Status = 'released', ReleasedAt = NOW() WHERE EscrowID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $escrow_id);
        $stmt->execute();
        $stmt->close();
        
        // Check if freelancer wallet exists, create if not
        $sql = "SELECT WalletID FROM wallet WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $freelancer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            // Create wallet for freelancer
            $sql = "INSERT INTO wallet (UserID, Balance, LockedBalance, LastUpdated) VALUES (?, 0, 0, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $freelancer_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt->close();
        }
        
        // Update client wallet - reduce locked balance
        $sql = "UPDATE wallet SET LockedBalance = LockedBalance - ? WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('di', $escrow_amount, $client_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        if ($affected === 0) {
            throw new Exception("Failed to update client wallet. Client ID: {$client_id}");
        }
        
        // Update freelancer wallet - add to balance
        $sql = "UPDATE wallet SET Balance = Balance + ? WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('di', $escrow_amount, $freelancer_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        if ($affected === 0) {
            throw new Exception("Failed to update freelancer wallet. Freelancer ID: {$freelancer_id}");
        }
        
        // Record freelancer wallet transaction
        $transaction_desc = "Payment received for '{$project_title}' (Agreement #{$agreement_id})";
        $sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Description, CreatedAt) 
                SELECT WalletID, 'credit', ?, ?, NOW() FROM wallet WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('dsi', $escrow_amount, $transaction_desc, $freelancer_id);
        $stmt->execute();
        $stmt->close();
        
        // Record client wallet transaction (unlock)
        $transaction_desc = "Payment released for '{$project_title}' (Agreement #{$agreement_id})";
        $sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Description, CreatedAt) 
                SELECT WalletID, 'payment', ?, ?, NOW() FROM wallet WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('dsi', $escrow_amount, $transaction_desc, $client_id);
        $stmt->execute();
        $stmt->close();
        
        // 4. Create notification for freelancer
        $notification_msg = "Your work for '{$project_title}' has been approved! Payment of RM " . number_format($payment_amount, 2) . " has been released to your wallet.";
        $sql = "INSERT INTO notifications (UserID, UserType, Message, RelatedType, RelatedID, CreatedAt, IsRead) 
                VALUES (?, 'freelancer', ?, 'work_approval', ?, NOW(), 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isi', $freelancer_id, $notification_msg, $submission_id);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['success'] = 'Work approved successfully! Payment has been released to the freelancer.';
        
    } else {
        // ========== REJECT WORK (Request Revision) ==========
        
        // 1. Update submission status to rejected
        $sql = "UPDATE work_submissions 
                SET Status = 'rejected', ReviewNotes = ?, ReviewedAt = NOW() 
                WHERE SubmissionID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $review_notes, $submission_id);
        $stmt->execute();
        $stmt->close();
        
        // 2. Decrement remaining revisions in agreement (only if not unlimited)
        if (!$is_unlimited) {
            $sql = "UPDATE agreement SET RemainingRevisions = RemainingRevisions - 1 WHERE AgreementID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $agreement_id);
            $stmt->execute();
            $stmt->close();
        }
        
        // 3. Update agreement status back to ongoing
        $sql = "UPDATE agreement SET Status = 'ongoing' WHERE AgreementID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $agreement_id);
        $stmt->execute();
        $stmt->close();
        
        // 4. Create notification for freelancer
        if ($is_unlimited) {
            $revision_notice = "";
            $notification_msg = "Your work submission for '{$project_title}' needs revisions. Please check the client's feedback and resubmit.";
        } else {
            $revisions_left = $remaining_revisions - 1;
            $revision_notice = $revisions_left > 0 ? " You have {$revisions_left} revision(s) remaining." : " This is your final revision.";
            $notification_msg = "Your work submission for '{$project_title}' needs revisions. Please check the client's feedback and resubmit.{$revision_notice}";
        }
        $sql = "INSERT INTO notifications (UserID, UserType, Message, RelatedType, RelatedID, CreatedAt, IsRead) 
                VALUES (?, 'freelancer', ?, 'work_revision', ?, NOW(), 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isi', $freelancer_id, $notification_msg, $submission_id);
        $stmt->execute();
        $stmt->close();
        
        if ($is_unlimited) {
            $_SESSION['success'] = "Revision requested. The freelancer has been notified.";
        } else {
            $revisions_left = $remaining_revisions - 1;
            $_SESSION['success'] = "Revision requested. You have {$revisions_left} revision(s) remaining. The freelancer has been notified.";
        }
    }
    
    // Commit transaction
    $conn->commit();
    header('Location: ongoing_projects.php');
    exit();
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    error_log('Review work error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    $_SESSION['error'] = 'Failed to process review. Please try again. Error: ' . $e->getMessage();
    header('Location: review_work.php?submission_id=' . $submission_id);
    exit();
    
} finally {
    $conn->close();
}
?>
