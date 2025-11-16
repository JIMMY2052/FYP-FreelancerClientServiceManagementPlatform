<?php
session_start();
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Check if request is POST and JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get JSON payload
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['agreement_id']) || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$agreement_id = (int)$data['agreement_id'];
$action = $data['action'];
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get database connection
$conn = getDBConnection();

// Verify agreement exists and user owns it
$verify_query = "SELECT 
                    a.AgreementID,
                    ap.FreelancerID,
                    j.ClientID
                FROM agreement a
                JOIN application ap ON a.ApplicationID = ap.ApplicationID
                JOIN job j ON ap.JobID = j.JobID
                WHERE a.AgreementID = ?";

$stmt = $conn->prepare($verify_query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}

$stmt->bind_param("i", $agreement_id);
$stmt->execute();
$result = $stmt->get_result();
$agreement_data = $result->fetch_assoc();
$stmt->close();

if (!$agreement_data) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Agreement not found']);
    exit;
}

// Verify user ownership
$is_owner = ($user_type === 'freelancer' && $agreement_data['FreelancerID'] == $user_id) ||
    ($user_type === 'client' && $agreement_data['ClientID'] == $user_id);

if (!$is_owner) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not authorized to modify this agreement']);
    exit;
}

// Handle actions
if ($action === 'sign') {
    $sign_query = "UPDATE agreement SET SignedDate = NOW(), Status = 'active' WHERE AgreementID = ?";
    $stmt = $conn->prepare($sign_query);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }

    $stmt->bind_param("i", $agreement_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Agreement signed successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to sign agreement']);
    }
    $stmt->close();
} elseif ($action === 'reject') {
    $reject_query = "UPDATE agreement SET Status = 'rejected' WHERE AgreementID = ?";
    $stmt = $conn->prepare($reject_query);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }

    $stmt->bind_param("i", $agreement_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Agreement rejected']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to reject agreement']);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
