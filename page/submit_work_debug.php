<?php
// DEBUG VERSION - Shows all steps of the submission process
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<!DOCTYPE html><html><head><title>Debug Submission</title></head><body>";
echo "<h2>Work Submission Debug Log</h2>";
echo "<pre>";

echo "=== STEP 1: Session Check ===\n";
echo "Session ID: " . session_id() . "\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "User Type: " . ($_SESSION['user_type'] ?? 'NOT SET') . "\n";

// Check if user is logged in and is a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    echo "ERROR: User not logged in or not a freelancer\n";
    echo "Redirecting to index...\n";
    die("</pre></body></html>");
}

echo "✓ User is authenticated as freelancer\n\n";

echo "=== STEP 2: Request Method Check ===\n";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "ERROR: Not a POST request\n";
    die("</pre></body></html>");
}

echo "✓ POST request confirmed\n\n";

require_once 'config.php';

echo "=== STEP 3: Form Data ===\n";
$freelancer_id = $_SESSION['user_id'];
$agreement_id = isset($_POST['agreement_id']) ? intval($_POST['agreement_id']) : null;
$submission_title = isset($_POST['submission_title']) ? trim($_POST['submission_title']) : '';
$submission_notes = isset($_POST['submission_notes']) ? trim($_POST['submission_notes']) : '';

echo "Freelancer ID: {$freelancer_id}\n";
echo "Agreement ID: {$agreement_id}\n";
echo "Submission Title: {$submission_title}\n";
echo "Submission Notes: " . substr($submission_notes, 0, 50) . "...\n";
echo "Files uploaded: " . (isset($_FILES['submission_files']) ? count($_FILES['submission_files']['name']) : 0) . "\n\n";

// Validation
if (!$agreement_id || empty($submission_title) || empty($submission_notes)) {
    echo "ERROR: Missing required fields\n";
    echo "Agreement ID valid: " . ($agreement_id ? "Yes" : "No") . "\n";
    echo "Title valid: " . (!empty($submission_title) ? "Yes" : "No") . "\n";
    echo "Notes valid: " . (!empty($submission_notes) ? "Yes" : "No") . "\n";
    die("</pre></body></html>");
}

echo "✓ All required fields present\n\n";

if (!isset($_FILES['submission_files']) || empty($_FILES['submission_files']['name'][0])) {
    echo "ERROR: No files uploaded\n";
    echo "FILES array:\n";
    print_r($_FILES);
    die("</pre></body></html>");
}

echo "✓ Files present in upload\n\n";

echo "=== STEP 4: Database Connection ===\n";
$conn = getDBConnection();
echo "✓ Database connected\n\n";

echo "=== STEP 5: Verify Agreement ===\n";
$sql = "SELECT AgreementID, ClientID, ProjectTitle FROM agreement 
        WHERE AgreementID = ? AND FreelancerID = ? AND Status = 'ongoing'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $agreement_id, $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "ERROR: Agreement not found or invalid\n";
    echo "SQL: {$sql}\n";
    echo "Params: AgreementID={$agreement_id}, FreelancerID={$freelancer_id}\n";
    
    // Check if agreement exists at all
    $check = $conn->query("SELECT AgreementID, FreelancerID, Status FROM agreement WHERE AgreementID = {$agreement_id}");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        echo "Agreement found but doesn't match criteria:\n";
        print_r($row);
    } else {
        echo "Agreement ID {$agreement_id} does not exist in database\n";
    }
    
    $stmt->close();
    $conn->close();
    die("</pre></body></html>");
}

$agreement = $result->fetch_assoc();
$client_id = $agreement['ClientID'];
echo "✓ Agreement verified\n";
echo "Client ID: {$client_id}\n";
echo "Project: {$agreement['ProjectTitle']}\n\n";
$stmt->close();

echo "=== STEP 6: File Upload ===\n";
$upload_dir = '../uploads/work_submissions/agreement_' . $agreement_id . '/';
echo "Upload directory: {$upload_dir}\n";

if (!is_dir($upload_dir)) {
    echo "Creating directory...\n";
    mkdir($upload_dir, 0755, true);
    echo "✓ Directory created\n";
} else {
    echo "✓ Directory exists\n";
}

$uploaded_files = [];
$upload_errors = [];
$allowed_extensions = ['zip', 'rar', 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi'];
$max_file_size = 50 * 1024 * 1024;

echo "\nProcessing files:\n";
foreach ($_FILES['submission_files']['tmp_name'] as $key => $tmp_name) {
    echo "\nFile #{$key}:\n";
    
    if ($_FILES['submission_files']['error'][$key] !== UPLOAD_ERR_OK) {
        echo "  Upload error code: " . $_FILES['submission_files']['error'][$key] . "\n";
        continue;
    }
    
    $original_filename = $_FILES['submission_files']['name'][$key];
    $file_size = $_FILES['submission_files']['size'][$key];
    $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    
    echo "  Name: {$original_filename}\n";
    echo "  Size: " . number_format($file_size) . " bytes\n";
    echo "  Extension: {$file_extension}\n";
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo "  ERROR: Invalid extension\n";
        $upload_errors[] = "File '{$original_filename}' has an invalid extension.";
        continue;
    }
    
    if ($file_size > $max_file_size) {
        echo "  ERROR: File too large\n";
        $upload_errors[] = "File '{$original_filename}' exceeds maximum size.";
        continue;
    }
    
    $unique_filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $original_filename);
    $destination = $upload_dir . $unique_filename;
    
    echo "  Destination: {$destination}\n";
    
    if (move_uploaded_file($tmp_name, $destination)) {
        echo "  ✓ Uploaded successfully\n";
        $uploaded_files[] = [
            'original_name' => $original_filename,
            'stored_name' => $unique_filename,
            'file_path' => 'uploads/work_submissions/agreement_' . $agreement_id . '/' . $unique_filename,
            'file_size' => $file_size,
            'file_type' => $file_extension
        ];
    } else {
        echo "  ERROR: Failed to move file\n";
        $upload_errors[] = "Failed to upload file '{$original_filename}'.";
    }
}

echo "\n\nUpload summary:\n";
echo "Successful uploads: " . count($uploaded_files) . "\n";
echo "Errors: " . count($upload_errors) . "\n";

if (count($upload_errors) > 0) {
    echo "Error messages:\n";
    foreach ($upload_errors as $error) {
        echo "  - {$error}\n";
    }
}

if (empty($uploaded_files)) {
    echo "\nERROR: No files were uploaded successfully\n";
    die("</pre></body></html>");
}

echo "\n✓ Files uploaded successfully\n\n";

echo "=== STEP 7: Database Tables Check ===\n";
$tables_check = [
    'work_submissions' => $conn->query("SHOW TABLES LIKE 'work_submissions'"),
    'submission_files' => $conn->query("SHOW TABLES LIKE 'submission_files'"),
    'notifications' => $conn->query("SHOW TABLES LIKE 'notifications'")
];

foreach ($tables_check as $table => $result) {
    if ($result && $result->num_rows > 0) {
        echo "✓ Table '{$table}' exists\n";
    } else {
        echo "ERROR: Table '{$table}' DOES NOT EXIST\n";
        echo "\nYou need to create the tables first!\n";
        echo "Visit: http://localhost/FYP-FreelancerClientServiceManagementPlatform/page/create_tables.php\n";
        die("</pre></body></html>");
    }
}

echo "\n=== STEP 8: Database Transaction ===\n";
$conn->begin_transaction();
echo "✓ Transaction started\n\n";

try {
    echo "Inserting into work_submissions...\n";
    $sql = "INSERT INTO work_submissions (AgreementID, FreelancerID, ClientID, SubmissionTitle, SubmissionNotes, Status, SubmittedAt) 
            VALUES (?, ?, ?, ?, ?, 'pending_review', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiiss', $agreement_id, $freelancer_id, $client_id, $submission_title, $submission_notes);
    $stmt->execute();
    $submission_id = $stmt->insert_id;
    echo "✓ Submission record created (ID: {$submission_id})\n\n";
    $stmt->close();
    
    echo "Inserting files...\n";
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
        echo "  ✓ File inserted: {$file['original_name']}\n";
    }
    $stmt->close();
    
    echo "\nUpdating agreement status...\n";
    $sql = "UPDATE agreement SET Status = 'pending_review' WHERE AgreementID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $agreement_id);
    $stmt->execute();
    echo "✓ Agreement status updated\n\n";
    $stmt->close();
    
    echo "Creating notification...\n";
    $notification_message = "Freelancer has submitted work for '" . $agreement['ProjectTitle'] . "'. Please review the deliverables.";
    $sql = "INSERT INTO notifications (UserID, UserType, Message, RelatedType, RelatedID, CreatedAt, IsRead) 
            VALUES (?, 'client', ?, 'work_submission', ?, NOW(), 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isi', $client_id, $notification_message, $submission_id);
    $stmt->execute();
    echo "✓ Notification created\n\n";
    $stmt->close();
    
    $conn->commit();
    echo "✓ Transaction committed\n\n";
    
    echo "=== SUCCESS ===\n";
    echo "Work submitted successfully!\n";
    echo "Submission ID: {$submission_id}\n";
    echo "Files uploaded: " . count($uploaded_files) . "\n";
    
    $_SESSION['success'] = 'Work submitted successfully! The client will be notified to review your submission.';
    
    echo "\n<a href='ongoing_projects.php'>Go to Ongoing Projects</a>\n";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "ERROR in transaction: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    // Delete uploaded files
    foreach ($uploaded_files as $file) {
        $file_path = '../' . $file['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
            echo "Deleted uploaded file: {$file_path}\n";
        }
    }
}

$conn->close();
echo "</pre></body></html>";
?>
