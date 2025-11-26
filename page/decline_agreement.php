<?php
session_start();

// Check if user is logged in and is a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['agreement_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Agreement ID is required']);
    exit();
}

$agreement_id = intval($input['agreement_id']);
$freelancer_id = $_SESSION['user_id'];

require_once 'config.php';

try {
    $conn = getDBConnection();

    // Verify agreement exists and belongs to the freelancer
    $verify_sql = "SELECT AgreementID, Status FROM agreement WHERE AgreementID = ? AND FreelancerID = ?";
    $verify_stmt = $conn->prepare($verify_sql);

    if (!$verify_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $verify_stmt->bind_param('ii', $agreement_id, $freelancer_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        $verify_stmt->close();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Agreement not found']);
        exit();
    }

    $agreement = $verify_result->fetch_assoc();
    $verify_stmt->close();

    // Check if agreement is in 'to_accept' status
    if ($agreement['Status'] !== 'to_accept') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Only agreements pending acceptance can be declined']);
        exit();
    }

    // Update agreement status to 'declined'
    $update_sql = "UPDATE agreement SET Status = 'declined', FreelancerSignedDate = NOW() WHERE AgreementID = ?";
    $update_stmt = $conn->prepare($update_sql);

    if (!$update_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $update_stmt->bind_param('i', $agreement_id);

    if (!$update_stmt->execute()) {
        throw new Exception("Execute failed: " . $update_stmt->error);
    }

    $update_stmt->close();
    $conn->close();

    // Send response
    echo json_encode(['success' => true, 'message' => 'Agreement declined successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit();
}
