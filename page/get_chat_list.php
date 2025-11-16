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

if ($user_type === 'freelancer') {
    // Get conversations with clients
    $query = "
        SELECT DISTINCT 
            c.ClientID as user_id,
            c.CompanyName as name,
            m.Content as lastMessage,
            m.AttachmentPath,
            m.Timestamp as lastMessageTime
        FROM client c
        LEFT JOIN message m ON (
            (m.SenderID = ? AND m.ReceiverID = c.ClientID)
            OR
            (m.SenderID = c.ClientID AND m.ReceiverID = ?)
        )
        WHERE c.Status = 'active'
        GROUP BY c.ClientID
        ORDER BY m.Timestamp DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $preview = $row['lastMessage'];
        if ($row['AttachmentPath'] && !$row['lastMessage']) {
            $preview = '📎 ' . basename($row['AttachmentPath']);
        }

        $chats[] = [
            'id' => 'client_' . $row['user_id'],
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
        SELECT DISTINCT 
            f.FreelancerID as user_id,
            CONCAT(f.FirstName, ' ', f.LastName) as name,
            m.Content as lastMessage,
            m.AttachmentPath,
            m.Timestamp as lastMessageTime
        FROM freelancer f
        LEFT JOIN message m ON (
            (m.SenderID = ? AND m.ReceiverID = f.FreelancerID)
            OR
            (m.SenderID = f.FreelancerID AND m.ReceiverID = ?)
        )
        WHERE f.Status = 'active'
        GROUP BY f.FreelancerID
        ORDER BY m.Timestamp DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $preview = $row['lastMessage'];
        if ($row['AttachmentPath'] && !$row['lastMessage']) {
            $preview = '📎 ' . basename($row['AttachmentPath']);
        }

        $chats[] = [
            'id' => 'freelancer_' . $row['user_id'],
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
