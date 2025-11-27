<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once 'config.php';

// Handle dispute filing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agreement_id = isset($_POST['agreement_id']) ? intval($_POST['agreement_id']) : null;
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    $reason_text = isset($_POST['reason_text']) ? trim($_POST['reason_text']) : '';
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];

    // Validate inputs
    if (!$agreement_id || empty($reason) || empty($reason_text)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }

    $conn = getDBConnection();

    // Verify agreement exists and user has access
    $verify_sql = "SELECT a.AgreementID, a.FreelancerID, a.ClientID, a.Status, a.ProjectTitle
                   FROM agreement a
                   WHERE a.AgreementID = ? AND a.Status = 'ongoing'";

    if ($user_type === 'freelancer') {
        $verify_sql .= " AND a.FreelancerID = ?";
    } else {
        $verify_sql .= " AND a.ClientID = ?";
    }

    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param('ii', $agreement_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Agreement not found or you do not have permission']);
        $verify_stmt->close();
        $conn->close();
        exit();
    }

    $agreement = $verify_result->fetch_assoc();
    $verify_stmt->close();

    // Handle file upload
    $evidence_file = null;
    if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['evidence_file'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB

        // Validate file
        if (!in_array($file['type'], $allowed_types)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and PDF are allowed']);
            $conn->close();
            exit();
        }

        if ($file['size'] > $max_size) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
            $conn->close();
            exit();
        }

        // Create uploads directory if it doesn't exist
        $base_dir = dirname(dirname(__FILE__));
        $uploads_dir = $base_dir . '/uploads/disputes/';

        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0755, true);
        }

        // Generate unique filename
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'dispute_' . $agreement_id . '_' . time() . '.' . $file_ext;
        $file_path = $uploads_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            $conn->close();
            exit();
        }

        $evidence_file = '/uploads/disputes/' . $filename;
    }

    // Insert dispute record
    $dispute_sql = "INSERT INTO dispute (AgreementID, InitiatorID, InitiatorType, ReasonText, EvidenceFile, Status, CreatedAt) 
                    VALUES (?, ?, ?, ?, ?, 'open', NOW())";

    $dispute_stmt = $conn->prepare($dispute_sql);

    if (!$dispute_stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        $conn->close();
        exit();
    }

    // Combine reason and reason_text for storage
    $full_reason = $reason . (empty($reason_text) ? '' : "\n\n" . $reason_text);

    $dispute_stmt->bind_param('iisss', $agreement_id, $user_id, $user_type, $full_reason, $evidence_file);

    if (!$dispute_stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to file dispute: ' . $dispute_stmt->error]);
        $dispute_stmt->close();
        $conn->close();
        exit();
    }

    $dispute_id = $conn->insert_id;
    $dispute_stmt->close();

    // Update agreement status to 'disputed'
    $update_agreement_sql = "UPDATE agreement SET Status = 'disputed' WHERE AgreementID = ?";
    $update_stmt = $conn->prepare($update_agreement_sql);
    $update_stmt->bind_param('i', $agreement_id);
    $update_stmt->execute();
    $update_stmt->close();

    $conn->close();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Dispute filed successfully. Admin will review shortly.',
        'dispute_id' => $dispute_id
    ]);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
