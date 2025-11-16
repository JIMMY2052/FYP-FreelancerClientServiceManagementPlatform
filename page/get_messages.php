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
$chat_id = $_GET['chatId'] ?? '';

if (empty($chat_id)) {
    echo json_encode([]);
    exit();
}

// Parse chat_id to get the other user's type and id
$other_user_type = null;
$other_user_id = null;

// Try new compact format: c1, f2
if (strlen($chat_id) > 1 && ($chat_id[0] === 'c' || $chat_id[0] === 'f')) {
    $other_user_type = ($chat_id[0] === 'c') ? 'client' : 'freelancer';
    $other_user_id = intval(substr($chat_id, 1));
}
// Try legacy format: client_5, freelancer_3
else if (strpos($chat_id, '_') !== false) {
    $parts = explode('_', $chat_id, 2);
    if (count($parts) === 2) {
        $other_user_type = $parts[0];
        $other_user_id = intval($parts[1]);
    }
}

if (!$other_user_type || !$other_user_id) {
    echo json_encode([]);
    exit();
}

$conn = getDBConnection();

// Get other user's name
if ($other_user_type === 'freelancer') {
    $query = "SELECT CONCAT(FirstName, ' ', LastName) as name FROM freelancer WHERE FreelancerID = ?";
} else {
    $query = "SELECT CompanyName as name FROM client WHERE ClientID = ?";
}

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $other_user_id);
$stmt->execute();
$result = $stmt->get_result();
$other_user = $result->fetch_assoc();
$other_user_name = $other_user['name'] ?? 'Unknown';
$stmt->close();

// Build composite sender/receiver IDs
$prefix_current = ($user_type === 'freelancer') ? 'f' : 'c';
$prefix_other = ($other_user_type === 'freelancer') ? 'f' : 'c';
$composite_current_id = $prefix_current . $user_id;
$composite_other_id = $prefix_other . $other_user_id;

// Fetch messages
$messages_query = "
    SELECT 
        m.MessageID,
        m.SenderID,
        m.ReceiverID,
        m.Content,
        m.AttachmentPath,
        m.AttachmentType,
        m.Timestamp
    FROM message m
    WHERE 
        (m.SenderID = ? AND m.ReceiverID = ?)
        OR
        (m.SenderID = ? AND m.ReceiverID = ?)
    ORDER BY m.Timestamp ASC
    LIMIT 200
";

$stmt = $conn->prepare($messages_query);
$stmt->bind_param("ssss", $composite_current_id, $composite_other_id, $composite_other_id, $composite_current_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    // Determine who sent this message (compare by composite ID)
    if ($row['SenderID'] === $composite_current_id) {
        $sender_type = $user_type;
        $sender_name = $_SESSION['email'] ?? 'You';
    } else {
        $sender_type = $other_user_type;
        $sender_name = $other_user_name;
    }

    $messages[] = [
        'id' => $row['MessageID'],
        'senderId' => $row['SenderID'],
        'senderType' => $sender_type,
        'senderName' => $sender_name,
        'content' => $row['Content'],
        'attachmentPath' => $row['AttachmentPath'],
        'attachmentType' => $row['AttachmentType'],
        'timestamp' => $row['Timestamp']
    ];
}

$stmt->close();
$conn->close();

echo json_encode($messages);
