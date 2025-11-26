<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if user is logged in and is a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: ../index.php');
    exit();
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ongoing_projects.php');
    exit();
}

$freelancer_id = $_SESSION['user_id'];
$agreement_id = isset($_POST['agreement_id']) ? intval($_POST['agreement_id']) : null;
$submission_title = isset($_POST['submission_title']) ? trim($_POST['submission_title']) : '';
$submission_notes = isset($_POST['submission_notes']) ? trim($_POST['submission_notes']) : '';

// Validation
if (!$agreement_id || empty($submission_title) || empty($submission_notes)) {
    $_SESSION['error'] = 'All fields are required.';
    header('Location: submit_work.php?agreement_id=' . $agreement_id);
    exit();
}

if (!isset($_FILES['submission_files']) || empty($_FILES['submission_files']['name'][0])) {
    $_SESSION['error'] = 'Please upload at least one file.';
    header('Location: submit_work.php?agreement_id=' . $agreement_id);
    exit();
}

$conn = getDBConnection();

// Verify agreement exists and belongs to this freelancer with status 'ongoing'
$sql = "SELECT AgreementID, ClientID, ProjectTitle FROM agreement 
        WHERE AgreementID = ? AND FreelancerID = ? AND Status = 'ongoing'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $agreement_id, $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Invalid agreement or you do not have permission to submit work for this project.';
    $stmt->close();
    $conn->close();
    header('Location: ongoing_projects.php');
    exit();
}

$agreement = $result->fetch_assoc();
$client_id = $agreement['ClientID'];
$stmt->close();

// Create submission directory if it doesn't exist
$upload_dir = '../uploads/work_submissions/agreement_' . $agreement_id . '/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle file uploads
$uploaded_files = [];
$upload_errors = [];
$allowed_extensions = ['zip', 'rar', 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi'];
$max_file_size = 50 * 1024 * 1024; // 50MB

foreach ($_FILES['submission_files']['tmp_name'] as $key => $tmp_name) {
    if ($_FILES['submission_files']['error'][$key] === UPLOAD_ERR_OK) {
        $original_filename = $_FILES['submission_files']['name'][$key];
        $file_size = $_FILES['submission_files']['size'][$key];
        $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

        // Validate file extension
        if (!in_array($file_extension, $allowed_extensions)) {
            $upload_errors[] = "File '$original_filename' has an invalid extension.";
            continue;
        }

        // Validate file size
        if ($file_size > $max_file_size) {
            $upload_errors[] = "File '$original_filename' exceeds maximum size of 50MB.";
            continue;
        }

        // Generate unique filename
        $unique_filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $original_filename);
        $destination = $upload_dir . $unique_filename;

        // Move uploaded file
        if (move_uploaded_file($tmp_name, $destination)) {
            $uploaded_files[] = [
                'original_name' => $original_filename,
                'stored_name' => $unique_filename,
                'file_path' => 'uploads/work_submissions/agreement_' . $agreement_id . '/' . $unique_filename,
                'file_size' => $file_size,
                'file_type' => $file_extension
            ];
        } else {
            $upload_errors[] = "Failed to upload file '$original_filename'.";
        }
    }
}

// Check if any files were uploaded successfully
if (empty($uploaded_files)) {
    $_SESSION['error'] = 'No files were uploaded successfully. ' . implode(' ', $upload_errors);
    header('Location: submit_work.php?agreement_id=' . $agreement_id);
    exit();
}

// Begin transaction
$conn->begin_transaction();

try {
    // Create work submission record
    $sql = "INSERT INTO work_submissions (AgreementID, FreelancerID, ClientID, SubmissionTitle, SubmissionNotes, Status, SubmittedAt) 
            VALUES (?, ?, ?, ?, ?, 'pending_review', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiiss', $agreement_id, $freelancer_id, $client_id, $submission_title, $submission_notes);
    $stmt->execute();
    $submission_id = $stmt->insert_id;
    $stmt->close();

    // Insert file records
    $sql = "INSERT INTO submission_files (SubmissionID, OriginalFileName, StoredFileName, FilePath, FileSize, FileType, UploadedAt) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    foreach ($uploaded_files as $file) {
        $stmt->bind_param('isssis', 
            $submission_id, 
            $file['original_name'], 
            $file['stored_name'], 
            $file['file_path'], 
            $file['file_size'], 
            $file['file_type']
        );
        $stmt->execute();
    }
    $stmt->close();

    // Update agreement status to 'pending_review'
    $sql = "UPDATE agreement SET Status = 'pending_review' WHERE AgreementID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $agreement_id);
    $stmt->execute();
    $stmt->close();

    // Create notification for client
    $notification_message = "Freelancer has submitted work for '" . $agreement['ProjectTitle'] . "'. Please review the deliverables.";
    $sql = "INSERT INTO notifications (UserID, UserType, Message, RelatedType, RelatedID, CreatedAt, IsRead) 
            VALUES (?, 'client', ?, 'work_submission', ?, NOW(), 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isi', $client_id, $notification_message, $submission_id);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    $_SESSION['success'] = 'Work submitted successfully! The client will be notified to review your submission.';
    header('Location: ongoing_projects.php');
    exit();

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();

    // Delete uploaded files on error
    foreach ($uploaded_files as $file) {
        $file_path = '../' . $file['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Log detailed error for debugging
    error_log('Work submission error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    $_SESSION['error'] = 'Failed to submit work. Please try again. Error: ' . $e->getMessage();
    header('Location: submit_work.php?agreement_id=' . $agreement_id);
    exit();
} finally {
    $conn->close();
}
?>
