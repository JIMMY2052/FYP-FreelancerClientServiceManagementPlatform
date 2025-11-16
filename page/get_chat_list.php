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
$conn = getDBConnection();

$chats = [];
$prefix_user = ($user_type === 'freelancer') ? 'f' : 'c';
$composite_user_id = $prefix_user . $user_id;

if ($user_type === 'freelancer') {
    // Get conversations with clients
    $query = "
        SELECT 
            c.ClientID as user_id,
            c.CompanyName as name,
            'client' as user_type,
            (SELECT m.Content FROM message m 
             WHERE (m.SenderID = ? AND m.ReceiverID = CONCAT('c', c.ClientID))
                OR (m.SenderID = CONCAT('c', c.ClientID) AND m.ReceiverID = ?)
             ORDER BY m.Timestamp DESC LIMIT 1) as lastMessage,
            (SELECT m.AttachmentPath FROM message m 
             WHERE (m.SenderID = ? AND m.ReceiverID = CONCAT('c', c.ClientID))
                OR (m.SenderID = CONCAT('c', c.ClientID) AND m.ReceiverID = ?)
             ORDER BY m.Timestamp DESC LIMIT 1) as lastAttachmentPath,
            (SELECT m.Timestamp FROM message m 
             WHERE (m.SenderID = ? AND m.ReceiverID = CONCAT('c', c.ClientID))
                OR (m.SenderID = CONCAT('c', c.ClientID) AND m.ReceiverID = ?)
             ORDER BY m.Timestamp DESC LIMIT 1) as lastMessageTime
        FROM client c
        WHERE c.Status = 'active'
        ORDER BY lastMessageTime DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $composite_user_id, $composite_user_id, $composite_user_id, $composite_user_id, $composite_user_id, $composite_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $preview = $row['lastMessage'];
        if ($row['lastAttachmentPath'] && !$row['lastMessage']) {
            $preview = '📎 ' . basename($row['lastAttachmentPath']);
        }

        $chats[] = [
            'id' => 'c' . $row['user_id'],
            'userId' => $row['user_id'],
            'userType' => 'client',
            'name' => $row['name'],
            'lastMessage' => $preview ?? 'No messages yet',
            'lastMessageTime' => $row['lastMessageTime'],
            'unreadCount' => 0
        ];
    }
    $stmt->close();
} else {
    // Get conversations with freelancers
    $query = "
        SELECT 
            f.FreelancerID as user_id,
            CONCAT(f.FirstName, ' ', f.LastName) as name,
            'freelancer' as user_type,
            (SELECT m.Content FROM message m 
             WHERE (m.SenderID = ? AND m.ReceiverID = CONCAT('f', f.FreelancerID))
                OR (m.SenderID = CONCAT('f', f.FreelancerID) AND m.ReceiverID = ?)
             ORDER BY m.Timestamp DESC LIMIT 1) as lastMessage,
            (SELECT m.AttachmentPath FROM message m 
             WHERE (m.SenderID = ? AND m.ReceiverID = CONCAT('f', f.FreelancerID))
                OR (m.SenderID = CONCAT('f', f.FreelancerID) AND m.ReceiverID = ?)
             ORDER BY m.Timestamp DESC LIMIT 1) as lastAttachmentPath,
            (SELECT m.Timestamp FROM message m 
             WHERE (m.SenderID = ? AND m.ReceiverID = CONCAT('f', f.FreelancerID))
                OR (m.SenderID = CONCAT('f', f.FreelancerID) AND m.ReceiverID = ?)
             ORDER BY m.Timestamp DESC LIMIT 1) as lastMessageTime
        FROM freelancer f
        WHERE f.Status = 'active'
        ORDER BY lastMessageTime DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $composite_user_id, $composite_user_id, $composite_user_id, $composite_user_id, $composite_user_id, $composite_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $preview = $row['lastMessage'];
        if ($row['lastAttachmentPath'] && !$row['lastMessage']) {
            $preview = '📎 ' . basename($row['lastAttachmentPath']);
        }

        $chats[] = [
            'id' => 'f' . $row['user_id'],
            'userId' => $row['user_id'],
            'userType' => 'freelancer',
            'name' => $row['name'],
            'lastMessage' => $preview ?? 'No messages yet',
            'lastMessageTime' => $row['lastMessageTime'],
            'unreadCount' => 0
        ];
    }
    $stmt->close();
}

$conn->close();

// Truncate and sanitize
foreach ($chats as &$chat) {
    if (strlen($chat['lastMessage']) > 50) {
        $chat['lastMessage'] = substr($chat['lastMessage'], 0, 50) . '...';
    }
}

echo json_encode($chats);
