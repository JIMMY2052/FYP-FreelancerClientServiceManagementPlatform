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
// Determine recipient: prefer explicit posted receiverId/receiverType, then legacy "type_id" chatId, then numeric conversationId lookup
$other_user_type = null;
$other_user_id = null;

// Log session and incoming chatId
error_log("[send_message] SESSION user_id=" . ($_SESSION['user_id'] ?? 'null') . " user_type=" . ($_SESSION['user_type'] ?? 'null'));
error_log("[send_message] POST chatId=" . ($chat_id ?? ''));
if (!empty($_POST)) {
    $posted_keys = array_keys($_POST);
    error_log("[send_message] POST keys=" . implode(',', $posted_keys));
}

// 1) PREFERRED: If client provided explicit receiver fields, use them directly (no parsing needed)
if (isset($_POST['receiverId']) && intval($_POST['receiverId']) > 0 && !empty($_POST['receiverType'])) {
    $other_user_id = intval($_POST['receiverId']);
    $other_user_type = trim($_POST['receiverType']);
    error_log("[send_message] USING DIRECT FIELDS: receiverId={$other_user_id}, receiverType={$other_user_type}");
    error_log("[send_message] SENDER: user_id={$user_id}, user_type={$user_type}");
} elseif (is_string($chat_id) && strlen($chat_id) > 1 && ($chat_id[0] === 'c' || $chat_id[0] === 'f')) {
    // 2) New compact format: c1, f2, etc.
    $other_user_type = ($chat_id[0] === 'c') ? 'client' : 'freelancer';
    $other_user_id = intval(substr($chat_id, 1));
    error_log("[send_message] PARSING NEW FORMAT: chatId '{$chat_id}' -> type={$other_user_type} id={$other_user_id}");
} elseif (is_string($chat_id) && strpos($chat_id, '_') !== false) {
    // 3) Fallback: Legacy format like 'client_5' or 'freelancer_3'
    $parts = explode('_', $chat_id, 2);
    if (count($parts) === 2) {
        $other_user_type = $parts[0];
        $other_user_id = intval($parts[1]);
        error_log("[send_message] FALLBACK PARSING LEGACY: chatId '{$chat_id}' -> type={$other_user_type} id={$other_user_id}");
    }
}

// If still null, it's an invalid chat id
if (empty($other_user_type) || empty($other_user_id)) {
    error_log("[send_message] invalid or unresolved recipient: chatId={$chat_id}");
    http_response_code(400);
    echo json_encode(['error' => 'Invalid chat ID or recipient not found']);
    exit();
}

// Construct composite sender and receiver IDs (f1, c1, etc.)
$prefix_sender = ($user_type === 'freelancer') ? 'f' : 'c';
$prefix_receiver = ($other_user_type === 'freelancer') ? 'f' : 'c';
$composite_sender_id = $prefix_sender . $user_id;
$composite_receiver_id = $prefix_receiver . $other_user_id;

// Log final resolved recipient
error_log("[send_message] RESOLVED: sender={$composite_sender_id} (type: {$user_type}) sending to receiver={$composite_receiver_id} (type: {$other_user_type})");

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

// Handle null values properly - MUST be done BEFORE bind_param
$content_to_insert = !empty($content) ? $content : null;

// Find or create Conversation
$conversationId = null;

// Look for existing conversation between these two users (bi-directional)
$convLookupSql = "SELECT ConversationID FROM Conversation WHERE 
    (User1ID = ? AND User1Type = ? AND User2ID = ? AND User2Type = ?) 
    OR 
    (User1ID = ? AND User1Type = ? AND User2ID = ? AND User2Type = ?) 
    LIMIT 1";

$stmtConv = $conn->prepare($convLookupSql);
if ($stmtConv) {
    $stmtConv->bind_param(
        "isisisis",
        $user_id,
        $user_type,
        $other_user_id,
        $other_user_type,
        $other_user_id,
        $other_user_type,
        $user_id,
        $user_type
    );
    $stmtConv->execute();
    $resConv = $stmtConv->get_result();
    if ($rowConv = $resConv->fetch_assoc()) {
        $conversationId = $rowConv['ConversationID'];
        error_log("[send_message] Found existing conversation: ConversationID={$conversationId}");
    }
    $stmtConv->close();
}

// If no conversation exists, create one
if (!$conversationId) {
    $convInsertSql = "INSERT INTO Conversation (User1ID, User1Type, User2ID, User2Type, CreatedAt, LastMessageAt, Status) 
        VALUES (?, ?, ?, ?, NOW(), NOW(), 'active')";

    $stmtConvInsert = $conn->prepare($convInsertSql);
    if ($stmtConvInsert) {
        $stmtConvInsert->bind_param(
            "isis",
            $user_id,
            $user_type,
            $other_user_id,
            $other_user_type
        );
        if ($stmtConvInsert->execute()) {
            $conversationId = $stmtConvInsert->insert_id;
            error_log("[send_message] Created new conversation: ConversationID={$conversationId}");
        } else {
            error_log("[send_message] Failed to create conversation: " . $stmtConvInsert->error);
        }
        $stmtConvInsert->close();
    }
}

// Insert message with ConversationID
$insert_query = "
    INSERT INTO message (ConversationID, SenderID, ReceiverID, Content, AttachmentPath, AttachmentType, Timestamp, Status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'unread')
";

$stmt = $conn->prepare($insert_query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param(
    "issssss",
    $conversationId,
    $composite_sender_id,
    $composite_receiver_id,
    $content_to_insert,
    $attachment_path,
    $attachment_type,
    $now
);

// Debug: Log the values used for DB insert (AFTER bind but BEFORE execute)
error_log("[send_message] BEFORE INSERT: ConversationID={$conversationId}, SenderID={$composite_sender_id}, ReceiverID={$composite_receiver_id}, ContentLen=" . strlen($content_to_insert ?? '') . ", Attachment=" . ($attachment_path ?? 'null') . ", Timestamp={$now}");

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to insert message']);
    exit();
}

$message_id = $stmt->insert_id;
$stmt->close();

// Query the inserted row to verify what was actually stored
$verifyQuery = "SELECT MessageID, ConversationID, SenderID, ReceiverID, Content FROM message WHERE MessageID = ? LIMIT 1";
$stmtVerify = $conn->prepare($verifyQuery);
if ($stmtVerify) {
    $stmtVerify->bind_param('i', $message_id);
    $stmtVerify->execute();
    $resVerify = $stmtVerify->get_result();
    if ($rowVerify = $resVerify->fetch_assoc()) {
        error_log("[send_message] AFTER INSERT VERIFICATION: MessageID={$rowVerify['MessageID']}, ConversationID={$rowVerify['ConversationID']}, SenderID={$rowVerify['SenderID']}, ReceiverID={$rowVerify['ReceiverID']}, Content={$rowVerify['Content']}");
    }
    $stmtVerify->close();
}

// Update Conversation.LastMessageAt
if ($conversationId) {
    $updateConvSql = "UPDATE Conversation SET LastMessageAt = ? WHERE ConversationID = ?";
    $stmtUpdateConv = $conn->prepare($updateConvSql);
    if ($stmtUpdateConv) {
        $stmtUpdateConv->bind_param('si', $now, $conversationId);
        $stmtUpdateConv->execute();
        $stmtUpdateConv->close();
    }
}

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
