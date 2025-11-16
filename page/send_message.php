<?php
header('Content-Type: application/json');
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$chat_id = $_POST['chatId'] ?? '';
$content = trim($_POST['content'] ?? '');

if (empty($chat_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Chat ID is required']);
    exit();
}

// Parse chat_id
$parts = explode('_', $chat_id, 2);
if (count($parts) !== 2) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid chat ID format']);
    exit();
}

$other_user_type = $parts[0];
$other_user_id = intval($parts[1]);

// Validate that at least content or files are provided
$has_files = isset($_FILES['files']) && !empty($_FILES['files']['name'][0]);
if (empty($content) && !$has_files) {
    http_response_code(400);
    echo json_encode(['error' => 'Message content or files are required']);
    exit();
}

$conn = getDBConnection();

// File upload handling
$attachment_path = null;
$attachment_type = null;

if ($has_files) {
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/messages/';

    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Process only the first file (if multiple were selected, we handle them one at a time)
    $file = $_FILES['files'];

    // Allowed file types
    $allowed_types = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    // Validate file
    if (!in_array($file['type'], $allowed_types)) {
        http_response_code(400);
        echo json_encode(['error' => 'File type not allowed']);
        exit();
    }

    if ($file['size'] > 10 * 1024 * 1024) { // 10MB
        http_response_code(400);
        echo json_encode(['error' => 'File size exceeds 10MB limit']);
        exit();
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'File upload failed']);
        exit();
    }

    // Generate unique filename
    $timestamp = time();
    $random_hash = bin2hex(random_bytes(4));
    $original_name = basename($file['name']);
    $file_ext = pathinfo($original_name, PATHINFO_EXTENSION);
    $new_filename = $timestamp . '_' . $random_hash . '.' . $file_ext;
    $file_path = $upload_dir . $new_filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save file']);
        exit();
    }

    // Store relative path
    $attachment_path = '/uploads/messages/' . $new_filename;
    $attachment_type = $file['type'];
}

// Insert message into database
$now = date('Y-m-d H:i:s');
$insert_query = "
    INSERT INTO message (SenderID, ReceiverID, Content, AttachmentPath, AttachmentType, Timestamp, Status)
    VALUES (?, ?, ?, ?, ?, ?, 'unread')
";

$stmt = $conn->prepare($insert_query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param(
    "iissss",
    $user_id,
    $other_user_id,
    $content_to_insert,
    $attachment_path,
    $attachment_type,
    $now
);

// Handle null values properly
$content_to_insert = !empty($content) ? $content : null;

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to insert message']);
    exit();
}

$message_id = $stmt->insert_id;
$stmt->close();
$conn->close();

// Return success response
echo json_encode([
    'success' => true,
    'messageId' => $message_id,
    'message' => [
        'id' => $message_id,
        'senderId' => $user_id,
        'senderType' => $user_type,
        'senderName' => $_SESSION['email'] ?? 'You',
        'content' => $content,
        'attachmentPath' => $attachment_path,
        'attachmentType' => $attachment_type,
        'timestamp' => $now
    ]
]);
