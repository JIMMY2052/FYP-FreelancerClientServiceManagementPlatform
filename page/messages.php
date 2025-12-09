<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}

// Check if user is deleted
require_once 'checkUserStatus.php';

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$user_email = $_SESSION['email'] ?? '';

// Check if client_id / freelancer_id / job_id is passed via URL or stored in session (from POST entry)
$target_client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : ($_SESSION['target_client_id'] ?? null);
$target_freelancer_id = isset($_GET['freelancer_id']) ? intval($_GET['freelancer_id']) : ($_SESSION['target_freelancer_id'] ?? null);
$target_job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : ($_SESSION['target_job_id'] ?? null);
$job_quote = null;
$show_quote = false; // Only show quote for the specific client conversation

// If the user has already closed or sent the quote in this session,
// do not show it again (these flags are set from frontend).
if (isset($_SESSION['quote_dismissed']) && $_SESSION['quote_dismissed'] === true) {
    $target_job_id = null;
}

// If a freelancer_id is provided and user is a client, create/open conversation with that freelancer
if ($target_freelancer_id && $user_type === 'client') {
    $conn = getDBConnection();

    // Check if conversation already exists
    $sql = "SELECT ConversationID FROM conversation 
            WHERE (User1ID = ? AND User1Type = 'client' AND User2ID = ? AND User2Type = 'freelancer')
            OR (User1ID = ? AND User1Type = 'freelancer' AND User2ID = ? AND User2Type = 'client')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiii', $user_id, $target_freelancer_id, $target_freelancer_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Conversation exists, get its ID
        $row = $result->fetch_assoc();
        $conversation_id = $row['ConversationID'];
    } else {
        // Create new conversation
        $insert_sql = "INSERT INTO conversation (User1ID, User1Type, User2ID, User2Type, CreatedAt, Status) 
                       VALUES (?, 'client', ?, 'freelancer', NOW(), 'active')";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param('ii', $user_id, $target_freelancer_id);
        $insert_stmt->execute();
        $conversation_id = $insert_stmt->insert_id;
        $insert_stmt->close();
    }

    $stmt->close();
    $conn->close();

    // Store the target conversation ID in session
    $_SESSION['target_conversation_id'] = $conversation_id;
    $_SESSION['target_freelancer_id'] = $target_freelancer_id;
}
// If a client_id is provided and user is a freelancer, create/open conversation with that client
elseif ($target_client_id && $user_type === 'freelancer') {
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

    // If job_id is provided, fetch job details
    if ($target_job_id) {
        $job_sql = "SELECT JobID, Title, Description, Budget, Deadline FROM job WHERE JobID = ? AND ClientID = ?";
        $job_stmt = $conn->prepare($job_sql);
        $job_stmt->bind_param('ii', $target_job_id, $target_client_id);
        $job_stmt->execute();
        $job_result = $job_stmt->get_result();
        if ($job_result->num_rows > 0) {
            $job_quote = $job_result->fetch_assoc();
            $show_quote = true; // Show quote only when coming from Contact Me with a job
        }
        $job_stmt->close();
    }

    $stmt->close();
    $conn->close();

    // Store the target conversation ID in session
    $_SESSION['target_conversation_id'] = $conversation_id;
    $_SESSION['target_client_id'] = $target_client_id;
    $_SESSION['target_quote_client_id'] = $target_client_id; // Track which client this quote is for
    if ($target_job_id) {
        $_SESSION['target_job_id'] = $target_job_id;
        $_SESSION['target_job_quote'] = $job_quote;
        // Reset dismissal flag when coming in with a fresh job quote
        unset($_SESSION['quote_dismissed']);
    }
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
            <a href="<?php echo $user_type === 'freelancer' ? '../freelancer_home.php' : '../client_home.php'; ?>">
                <img src="../images/logo.png" alt="WorkSnyc Logo" class="top-logo">
            </a>
        </div>
        <div class="nav-actions">
            <svg class="notification-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <div class="profile-dropdown">
                <div class="profile-avatar" id="profileAvatar" style="width: 36px; height: 36px; border-radius: 50%; background-color: #22c55e; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; cursor: pointer; overflow: hidden; flex-shrink: 0;">
                    <?php
                    // Display user profile picture from database
                    if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
                        $user_id = $_SESSION['user_id'];
                        $user_type = $_SESSION['user_type'];

                        // Get database connection
                        $conn = getDBConnection();

                        // Determine table and columns based on user type
                        if ($user_type === 'freelancer') {
                            $table = 'freelancer';
                            $id_column = 'FreelancerID';
                            $name_col1 = 'FirstName';
                            $name_col2 = 'LastName';
                        } else {
                            $table = 'client';
                            $id_column = 'ClientID';
                            $name_col1 = 'CompanyName';
                            $name_col2 = null;
                        }

                        $query = "SELECT ProfilePicture, $name_col1" . ($name_col2 ? ", $name_col2" : "") . " FROM $table WHERE $id_column = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param('i', $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result && $result->num_rows > 0) {
                            $user = $result->fetch_assoc();
                            $profilePicture = $user['ProfilePicture'] ?? null;
                            $name1 = $user[$name_col1] ?? '';
                            $name2 = $name_col2 ? ($user[$name_col2] ?? '') : '';

                            // Display profile picture if exists
                            if (!empty($profilePicture) && file_exists('../' . $profilePicture)) {
                                echo '<img src="/' . htmlspecialchars($profilePicture) . '" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;">';
                            } else {
                                // Show initials fallback
                                if ($user_type === 'freelancer') {
                                    $initials = strtoupper(substr($name1 ?: 'F', 0, 1) . substr($name2 ?: 'L', 0, 1));
                                } else {
                                    $initials = strtoupper(substr($name1 ?: 'C', 0, 1));
                                }
                                echo $initials;
                            }
                        } else {
                            echo '👤';
                        }

                        $stmt->close();
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

            <!-- Job Quote Display (if coming from Contact Me with specific job) -->
            <?php if ($show_quote && $job_quote): ?>
                <div class="job-quote-container">
                    <div class="job-quote-header">
                        <h3 class="job-quote-title">Project Quote</h3>
                        <button class="job-quote-close" id="jobQuoteClose" type="button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="job-quote-content">
                        <div class="job-quote-item">
                            <span class="job-quote-label">Project Title:</span>
                            <span class="job-quote-value"><?php echo htmlspecialchars($job_quote['Title']); ?></span>
                        </div>
                        <div class="job-quote-item">
                            <span class="job-quote-label">Budget:</span>
                            <span class="job-quote-value job-quote-budget">$<?php echo number_format($job_quote['Budget'], 2); ?></span>
                        </div>
                        <div class="job-quote-item">
                            <span class="job-quote-label">Deadline:</span>
                            <span class="job-quote-value"><?php echo date('M d, Y', strtotime($job_quote['Deadline'])); ?></span>
                        </div>
                        <div class="job-quote-description">
                            <span class="job-quote-label">Description:</span>
                            <p class="job-quote-text"><?php echo nl2br(htmlspecialchars(mb_strimwidth($job_quote['Description'], 0, 150, '...'))); ?></p>
                        </div>
                        <div class="job-quote-actions">
                            <button class="job-quote-btn job-quote-send" id="sendJobQuoteBtn" type="button">Send Quote</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

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
                        <!-- Plus Button with Popup Menu -->
                        <div class="attachment-menu-wrapper">
                            <button type="button" class="input-action-btn plus-btn" id="attachMenuBtn" title="Add attachment">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                            <div class="attachment-menu" id="attachmentMenu">
                                <button type="button" class="attachment-option" id="uploadPhotoBtn" title="Upload photo">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                    <span>Photos</span>
                                </button>
                                <button type="button" class="attachment-option" id="uploadFileBtn" title="Upload file">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                    <span>Files</span>
                                </button>

                            </div>
                        </div>
                        <input
                            type="file"
                            id="fileInput"
                            class="file-input-hidden"
                            accept="image/*,.pdf,.doc,.docx">
                        <input
                            type="file"
                            id="photoInput"
                            class="file-input-hidden"
                            accept="image/*">
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

    <!-- Center Warning Modal for file upload rules -->
    <div id="fileWarningModal" class="modal file-warning-modal" style="display:none;">
        <div class="modal-overlay"></div>
        <div class="modal-content file-warning-content">
            <div class="file-warning-icon">!</div>
            <h3 class="file-warning-title">File Upload Notice</h3>
            <p id="fileWarningText" class="file-warning-text"></p>
            <button type="button" id="fileWarningClose" class="file-warning-button">OK</button>
        </div>
    </div>

    <!-- Hidden element to store current user data -->
    <script>
        window.currentUserData = {
            id: <?php echo $user_id; ?>,
            type: '<?php echo $user_type; ?>',
            email: '<?php echo $user_email; ?>'
        };

        // Store quote settings - only show quote for specific client
        <?php if ($show_quote && isset($_SESSION['target_quote_client_id'])): ?>
            window.quoteClientId = <?php echo $_SESSION['target_quote_client_id']; ?>;
            window.showJobQuote = true;
        <?php else: ?>
            window.showJobQuote = false;
        <?php endif; ?>

        // If targeting a specific client from Contact Me button or freelancer from my_applications
        <?php if (isset($_SESSION['target_conversation_id'])): ?>
            window.targetConversationId = <?php echo $_SESSION['target_conversation_id']; ?>;
            <?php if (isset($_SESSION['target_client_id'])): ?>
                window.targetClientId = <?php echo $_SESSION['target_client_id']; ?>;
            <?php endif; ?>
            <?php if (isset($_SESSION['target_freelancer_id'])): ?>
                window.targetFreelancerId = <?php echo $_SESSION['target_freelancer_id']; ?>;
            <?php endif; ?>
            window.autoLoadConversation = true;
        <?php endif; ?>
    </script>

    <script src="../assets/js/chat.js"></script>
    <script src="../assets/js/messages-page.js"></script>

</body>

</html>