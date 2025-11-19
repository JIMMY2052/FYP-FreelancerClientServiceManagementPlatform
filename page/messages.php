<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$user_email = $_SESSION['email'] ?? '';

// Check if client_id is passed (from Contact Me button)
$target_client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;

// If a client_id is provided and user is a freelancer, create/open conversation with that client
if ($target_client_id && $user_type === 'freelancer') {
    $conn = getDBConnection();

    // Check if conversation already exists
    $sql = "SELECT ConversationID FROM conversation 
            WHERE (User1ID = ? AND User1Type = 'freelancer' AND User2ID = ? AND User2Type = 'client')
            OR (User1ID = ? AND User1Type = 'client' AND User2ID = ? AND User2Type = 'freelancer')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiii', $user_id, $target_client_id, $target_client_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Conversation exists, get its ID
        $row = $result->fetch_assoc();
        $conversation_id = $row['ConversationID'];
    } else {
        // Create new conversation
        $insert_sql = "INSERT INTO conversation (User1ID, User1Type, User2ID, User2Type, CreatedAt) 
                       VALUES (?, 'freelancer', ?, 'client', NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param('ii', $user_id, $target_client_id);
        $insert_stmt->execute();
        $conversation_id = $insert_stmt->insert_id;
        $insert_stmt->close();
    }

    $stmt->close();
    $conn->close();

    // Store the target conversation ID in session
    $_SESSION['target_conversation_id'] = $conversation_id;
    $_SESSION['target_client_id'] = $target_client_id;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - WorkSync</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/chat.css">
</head>

<body class="chat-page">
    <!-- Top Right Navigation -->
    <div class="chat-top-nav">
        <div class="logo-section">
            <img src="../images/logo.png" alt="WorkSnyc Logo" class="top-logo">
        </div>
        <div class="nav-actions">
            <svg class="notification-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <div class="profile-dropdown">
                <div class="profile-avatar" id="profileAvatar">
                    <?php
                    if (isset($_SESSION['email'])) {
                        $email = $_SESSION['email'];
                        $name_parts = explode(' ', $email);
                        $initials = strtoupper(substr($name_parts[0], 0, 1));
                        if (isset($name_parts[1])) {
                            $initials .= strtoupper(substr($name_parts[1], 0, 1));
                        }
                        echo $initials;
                    } else {
                        echo '👤';
                    }
                    ?>
                </div>
                <div class="dropdown-menu">
                    <a href="edit_profile.php" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                        Edit Profile
                    </a>
                    <a href="logout.php" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="chat-container">
        <!-- Left Sidebar - Chat List -->
        <div class="chat-sidebar">
            <div class="chat-sidebar-header">
                <div class="sidebar-top">
                    <h1 class="sidebar-title">Messages</h1>
                    <button class="sidebar-menu-btn" title="Menu">⋮</button>
                </div>
                <div class="sidebar-search-wrapper">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input
                        type="text"
                        id="chatSearch"
                        placeholder="Search conversations..."
                        class="search-input">
                </div>
            </div>
            <div class="chat-list-wrapper">
                <ul class="chat-list" id="chatList">
                    <li class="loading-state">
                        <div class="loading-spinner"></div>
                        <span>Loading conversations...</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Panel - Messages -->
        <div class="chat-main">
            <!-- Header with User Info -->
            <div class="chat-messages-header">
                <div class="header-user-info">
                    <div class="header-avatar" id="headerAvatar">👤</div>
                    <div class="header-text">
                        <h2 id="headerName">Select a conversation</h2>
                        <p id="headerStatus" class="header-status">No conversation selected</p>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="header-menu">
                        <button class="header-action-btn header-menu-btn" title="More options" id="headerMenuBtn">⋮</button>
                        <div class="header-menu-dropdown" id="headerMenuDropdown">
                            <button class="header-menu-item" id="viewProfileBtn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                View Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="messages-container" id="messagesContainer">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No conversations yet</h3>
                    <p class="empty-state-text">Select a conversation to start messaging</p>
                </div>
            </div>

            <!-- Input Area -->
            <div class="chat-input-area">
                <div id="filePreview" class="file-preview-container"></div>

                <div class="input-row">
                    <div class="input-wrapper">
                        <button type="button" class="input-action-btn" id="attachBtn" title="Attach files">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                            </svg>
                        </button>
                        <input
                            type="file"
                            id="fileInput"
                            class="file-input-hidden"
                            multiple
                            accept="image/*,.pdf,.doc,.docx">
                        <textarea
                            id="messageInput"
                            class="message-input"
                            placeholder="Type a message..."
                            rows="1"></textarea>
                    </div>
                    <button id="sendBtn" class="send-btn" title="Send message">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M16.6915026,12.4744748 L3.50612381,13.2599618 C3.19218622,13.2599618 3.03521743,13.4170592 3.03521743,13.5741566 L1.15159189,20.0151496 C0.8376543,20.8006365 0.99,21.89 1.77946707,22.52 C2.41,22.99 3.50612381,23.1 4.13399899,22.9429026 L21.714504,14.0454487 C22.6563168,13.5741566 23.1272231,12.6315722 22.9702544,11.6889879 L4.13399899,1.16346272 C3.34915502,0.9 2.40734225,1.00636533 1.77946707,1.4776575 C0.994623095,2.10604706 0.837654326,3.0486314 1.15159189,3.99 L3.03521743,10.4309931 C3.03521743,10.5880905 3.34915502,10.7451879 3.50612381,10.7451879 L16.6915026,11.5306748 C16.6915026,11.5306748 17.1624089,11.5306748 17.1624089,12.0019669 C17.1624089,12.4744748 16.6915026,12.4744748 16.6915026,12.4744748 Z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <button id="modalClose" class="modal-close">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <img id="modalImage" class="modal-image" src="" alt="Preview">
        </div>
    </div>

    <!-- Hidden element to store current user data -->
    <script>
        window.currentUserData = {
            id: <?php echo $user_id; ?>,
            type: '<?php echo $user_type; ?>',
            email: '<?php echo $user_email; ?>'
        };

        // If targeting a specific client from Contact Me button
        <?php if (isset($_SESSION['target_conversation_id'])): ?>
            window.targetConversationId = <?php echo $_SESSION['target_conversation_id']; ?>;
            window.targetClientId = <?php echo $_SESSION['target_client_id']; ?>;
        <?php endif; ?>
    </script>

    <script src="../assets/js/chat.js"></script>
</body>

</html>