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
    // Get conversations for freelancer - only conversations they're part of
    // Handles both conversation patterns:
    // 1. User1ID = freelancer, User2ID = client (old pattern)
    // 2. User1ID = client, User2ID = freelancer (new pattern from agreements)
    $query = "
        SELECT 
            c.ClientID as user_id,
            c.CompanyName as name,
            'client' as user_type,
            conv.ConversationID as conversation_id,
            (SELECT m.Content FROM message m 
             WHERE m.ConversationID = conv.ConversationID
             ORDER BY m.Timestamp DESC LIMIT 1) as lastMessage,
            (SELECT m.AttachmentPath FROM message m 
             WHERE m.ConversationID = conv.ConversationID
             ORDER BY m.Timestamp DESC LIMIT 1) as lastAttachmentPath,
            COALESCE((SELECT m.Timestamp FROM message m 
             WHERE m.ConversationID = conv.ConversationID
             ORDER BY m.Timestamp DESC LIMIT 1), conv.LastMessageAt, conv.CreatedAt) as lastMessageTime
        FROM conversation conv
        INNER JOIN client c ON 
            (c.ClientID = conv.User2ID AND conv.User2Type = 'client') OR
            (c.ClientID = conv.User1ID AND conv.User1Type = 'client')
        WHERE 
            (conv.User1ID = ? AND conv.User1Type = 'freelancer') OR
            (conv.User2ID = ? AND conv.User2Type = 'freelancer')
        AND (conv.Status = 'active' OR conv.Status IS NULL)
        ORDER BY lastMessageTime DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
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
    // Get conversations for client - only conversations they're part of
    // Handles both conversation patterns:
    // 1. User1ID = freelancer, User2ID = client (old pattern)
    // 2. User1ID = client, User2ID = freelancer (new pattern from agreements)
    $query = "
        SELECT 
            f.FreelancerID as user_id,
            CONCAT(f.FirstName, ' ', f.LastName) as name,
            'freelancer' as user_type,
            conv.ConversationID as conversation_id,
            (SELECT m.Content FROM message m 
             WHERE m.ConversationID = conv.ConversationID
             ORDER BY m.Timestamp DESC LIMIT 1) as lastMessage,
            (SELECT m.AttachmentPath FROM message m 
             WHERE m.ConversationID = conv.ConversationID
             ORDER BY m.Timestamp DESC LIMIT 1) as lastAttachmentPath,
            COALESCE((SELECT m.Timestamp FROM message m 
             WHERE m.ConversationID = conv.ConversationID
             ORDER BY m.Timestamp DESC LIMIT 1), conv.LastMessageAt, conv.CreatedAt) as lastMessageTime
        FROM conversation conv
        INNER JOIN freelancer f ON 
            (f.FreelancerID = conv.User1ID AND conv.User1Type = 'freelancer') OR
            (f.FreelancerID = conv.User2ID AND conv.User2Type = 'freelancer')
        WHERE 
            (conv.User2ID = ? AND conv.User2Type = 'client') OR
            (conv.User1ID = ? AND conv.User1Type = 'client')
        AND (conv.Status = 'active' OR conv.Status IS NULL)
        ORDER BY lastMessageTime DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
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
