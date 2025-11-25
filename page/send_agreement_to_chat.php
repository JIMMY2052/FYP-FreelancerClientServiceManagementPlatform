<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
include 'config.php';

header('Content-Type: application/json');

// Verify user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Check if agreement ID is provided
if (!isset($_POST['agreement_id'])) {
    echo json_encode(['success' => false, 'message' => 'Agreement ID is required']);
    exit();
}

$agreement_id = intval($_POST['agreement_id']);
$sender_id = $_SESSION['user_id'];
$sender_type = $_SESSION['user_type'];

// Get database connection
$conn = getDBConnection();

// Fetch agreement data from database
$sql = "SELECT * FROM agreement WHERE AgreementID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $agreement_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Agreement not found']);
    exit();
}

$agreement = $result->fetch_assoc();
$stmt->close();

// Determine receiver based on sender type and agreement
if ($sender_type === 'freelancer') {
    // Sender is freelancer, need to find client to send to
    // Check if there's a target client in the conversation context
    if (isset($_SESSION['target_client_id'])) {
        $receiver_id = $_SESSION['target_client_id'];
        $receiver_type = 'client';
    } else {
        echo json_encode(['success' => false, 'message' => 'Client ID not found']);
        exit();
    }
} else {
    // Sender is client, need to find freelancer to send to
    // This would be the freelancer who created the agreement
    // For now, we'll need the freelancer ID from POST or session
    if (isset($_POST['receiver_id'])) {
        $receiver_id = intval($_POST['receiver_id']);
        $receiver_type = 'freelancer';
    } else {
        echo json_encode(['success' => false, 'message' => 'Freelancer ID not found']);
        exit();
    }
}

// Find or create conversation
$conv_sql = "SELECT ConversationID FROM conversation 
            WHERE (User1ID = ? AND User1Type = ? AND User2ID = ? AND User2Type = ?)
            OR (User1ID = ? AND User1Type = ? AND User2ID = ? AND User2Type = ?)";

$conv_stmt = $conn->prepare($conv_sql);
$conv_stmt->bind_param(
    'isisisis',
    $sender_id,
    $sender_type,
    $receiver_id,
    $receiver_type,
    $receiver_id,
    $receiver_type,
    $sender_id,
    $sender_type
);
$conv_stmt->execute();
$conv_result = $conv_stmt->get_result();

$conversation_id = null;

if ($conv_result->num_rows > 0) {
    // Conversation exists
    $conv_row = $conv_result->fetch_assoc();
    $conversation_id = $conv_row['ConversationID'];
} else {
    // Create new conversation
    $create_conv_sql = "INSERT INTO conversation (User1ID, User1Type, User2ID, User2Type, CreatedAt) 
                       VALUES (?, ?, ?, ?, NOW())";
    $create_conv_stmt = $conn->prepare($create_conv_sql);
    $create_conv_stmt->bind_param('isis', $sender_id, $sender_type, $receiver_id, $receiver_type);

    if ($create_conv_stmt->execute()) {
        $conversation_id = $create_conv_stmt->insert_id;
    }
    $create_conv_stmt->close();
}

$conv_stmt->close();

if (!$conversation_id) {
    echo json_encode(['success' => false, 'message' => 'Failed to create/find conversation']);
    exit();
}

// Use the saved PDF path from the database
// The PDF was already generated and saved when the agreement was created
if (empty($agreement['SignaturePath'])) {
    echo json_encode(['success' => false, 'message' => 'Agreement PDF not found. Please save the agreement first.']);
    exit();
}

// Use the signature path as the PDF attachment (assuming it's stored with the agreement)
// If you need a separate PDF path, update the agreement table schema to include a PDFPath column
// For now, we'll use the agreement's signature path directory to locate the PDF
$pdf_directory = __DIR__ . '/../uploads/signatures/';
$pdf_filename = 'agreement_' . $agreement_id . '.pdf';
$pdf_file_path = $pdf_directory . $pdf_filename;

// Check if the PDF exists
if (!file_exists($pdf_file_path)) {
    echo json_encode(['success' => false, 'message' => 'Agreement PDF file not found.']);
    exit();
}

// Create web-accessible path for the attachment
$attachment_web_path = '/uploads/signatures/' . $pdf_filename;

// Build composite sender/receiver IDs (e.g., 'f1', 'c5')
$prefix_sender = ($sender_type === 'freelancer') ? 'f' : 'c';
$prefix_receiver = ($receiver_type === 'freelancer') ? 'f' : 'c';
$composite_sender_id = $prefix_sender . $sender_id;
$composite_receiver_id = $prefix_receiver . $receiver_id;

// Create message content with agreement info
$message_content = "📋 Project Agreement: " . $agreement['ProjectTitle'] . "\nAmount: RM " . number_format($agreement['PaymentAmount'], 2);

// Insert message into database with PDF attachment
$msg_sql = "INSERT INTO message (ConversationID, SenderID, ReceiverID, Content, AttachmentPath, AttachmentType, Timestamp, Status) 
           VALUES (?, ?, ?, ?, ?, ?, NOW(), 'unread')";

$msg_stmt = $conn->prepare($msg_sql);
$attachment_type = 'application/pdf';

$msg_stmt->bind_param('isssss', $conversation_id, $composite_sender_id, $composite_receiver_id, $message_content, $attachment_web_path, $attachment_type);

if ($msg_stmt->execute()) {
    $message_id = $msg_stmt->insert_id;

    // Update conversation last message timestamp
    $update_conv_sql = "UPDATE conversation SET LastMessageAt = NOW() WHERE ConversationID = ?";
    $update_conv_stmt = $conn->prepare($update_conv_sql);
    $update_conv_stmt->bind_param('i', $conversation_id);
    $update_conv_stmt->execute();
    $update_conv_stmt->close();

    // Store agreement ID in session for reference
    $_SESSION['last_sent_agreement_id'] = $agreement_id;
    $_SESSION['last_conversation_id'] = $conversation_id;

    echo json_encode([
        'success' => true,
        'message' => 'Agreement sent successfully',
        'conversation_id' => $conversation_id,
        'message_id' => $message_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send agreement']);
}

$msg_stmt->close();
$conn->close();
