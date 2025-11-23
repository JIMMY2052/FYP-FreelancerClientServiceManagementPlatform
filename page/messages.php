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
$target_job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : null;
$job_quote = null;

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

    // If job_id is provided, fetch job details
    if ($target_job_id) {
        $job_sql = "SELECT JobID, Title, Description, Budget, Deadline FROM job WHERE JobID = ? AND ClientID = ?";
        $job_stmt = $conn->prepare($job_sql);
        $job_stmt->bind_param('ii', $target_job_id, $target_client_id);
        $job_stmt->execute();
        $job_result = $job_stmt->get_result();
        if ($job_result->num_rows > 0) {
            $job_quote = $job_result->fetch_assoc();
        }
        $job_stmt->close();
    }

    $stmt->close();
    $conn->close();

    // Store the target conversation ID in session
    $_SESSION['target_conversation_id'] = $conversation_id;
    $_SESSION['target_client_id'] = $target_client_id;
    if ($target_job_id) {
        $_SESSION['target_job_id'] = $target_job_id;
        $_SESSION['target_job_quote'] = $job_quote;
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

            <!-- Job Quote Display (if coming from Contact Me) -->
            <?php if ($job_quote): ?>
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
                            <button class="job-quote-btn job-quote-send" id="sendJobQuoteBtn" type="button">Send Quote to Client</button>
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
            window.autoLoadConversation = true;
        <?php endif; ?>
    </script>

    <script src="../assets/js/chat.js"></script>

    <script>
        // Job Quote functionality
        document.addEventListener('DOMContentLoaded', function() {
            const jobQuoteClose = document.getElementById('jobQuoteClose');
            const sendJobQuoteBtn = document.getElementById('sendJobQuoteBtn');

            if (jobQuoteClose) {
                jobQuoteClose.addEventListener('click', function() {
                    const jobQuoteContainer = document.querySelector('.job-quote-container');
                    if (jobQuoteContainer) {
                        jobQuoteContainer.style.display = 'none';
                    }
                });
            }

            if (sendJobQuoteBtn) {
                sendJobQuoteBtn.addEventListener('click', async function() {
                    if (!window.chatApp || !window.chatApp.currentChat) {
                        alert('Please select a conversation first');
                        return;
                    }

                    // Get job quote data from the DOM
                    const jobQuoteItems = document.querySelectorAll('.job-quote-item');
                    let jobTitle = '';
                    let jobBudget = '';
                    let jobDeadline = '';
                    let jobDescription = '';

                    // Extract data from quote container
                    const quoteContainer = document.querySelector('.job-quote-container');
                    if (quoteContainer) {
                        const items = quoteContainer.querySelectorAll('.job-quote-item');
                        items.forEach(item => {
                            const label = item.querySelector('.job-quote-label');
                            const value = item.querySelector('.job-quote-value');
                            if (label && value) {
                                const labelText = label.textContent.trim();
                                const valueText = value.textContent.trim();
                                if (labelText.includes('Title')) jobTitle = valueText;
                                if (labelText.includes('Budget')) jobBudget = valueText;
                                if (labelText.includes('Deadline')) jobDeadline = valueText;
                            }
                        });
                        const descItem = quoteContainer.querySelector('.job-quote-description');
                        if (descItem) {
                            const descText = descItem.querySelector('.job-quote-text');
                            if (descText) jobDescription = descText.textContent.trim();
                        }
                    }

                    // Get job ID from session (passed via window variable)
                    const jobId = <?php echo isset($_SESSION['target_job_id']) ? $_SESSION['target_job_id'] : 'null'; ?>;

                    if (!jobId) {
                        alert('Job ID not found');
                        return;
                    }

                    // Disable button and show loading state
                    sendJobQuoteBtn.disabled = true;
                    const originalText = sendJobQuoteBtn.textContent;
                    sendJobQuoteBtn.textContent = 'Sending...';

                    try {
                        const formData = new FormData();
                        formData.append('chatId', window.chatApp.currentChat);
                        formData.append('jobId', jobId);
                        formData.append('jobTitle', jobTitle);
                        formData.append('jobBudget', jobBudget);
                        formData.append('jobDeadline', jobDeadline);
                        formData.append('jobDescription', jobDescription);
                        formData.append('receiverId', window.chatApp.currentOtherId);
                        formData.append('receiverType', window.chatApp.currentOtherType);

                        const response = await fetch('../page/send_quote_message.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            console.log('Quote sent successfully');
                            // Hide the quote container
                            const jobQuoteContainer = document.querySelector('.job-quote-container');
                            if (jobQuoteContainer) {
                                jobQuoteContainer.style.display = 'none';
                            }
                            // Reload messages to display the new quote
                            setTimeout(() => {
                                window.chatApp.loadMessages();
                                window.chatApp.loadChatList();
                            }, 500);
                        } else {
                            alert('Error sending quote: ' + (result.error || 'Unknown error'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error sending quote: ' + error.message);
                    } finally {
                        sendJobQuoteBtn.disabled = false;
                        sendJobQuoteBtn.textContent = originalText;
                    }
                });
            }
        });

        // Auto-load conversation if coming from "Contact Me" button
        document.addEventListener('DOMContentLoaded', function() {
            if (window.autoLoadConversation && window.targetClientId) {
                // Wait for chat list to load, then find and click the target conversation
                const maxAttempts = 20; // Try for up to 10 seconds (20 * 500ms)
                let attempts = 0;

                const autoLoadInterval = setInterval(function() {
                    attempts++;

                    // Try to find by user ID (more reliable)
                    const conversationItem = document.querySelector(`[data-user-id="${window.targetClientId}"]`);

                    if (conversationItem) {
                        clearInterval(autoLoadInterval);
                        conversationItem.click();
                        conversationItem.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        console.log('Auto-loaded conversation with client ID:', window.targetClientId);
                    } else if (attempts >= maxAttempts) {
                        clearInterval(autoLoadInterval);
                        console.log('Could not find conversation with client ID:', window.targetClientId);
                    }
                }, 500);
            }
        });
    </script>

    <style>
        /* Job Quote Styling */
        .job-quote-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            border-left: 4px solid #1ab394;
            margin: 0;
            padding: 20px;
            border-radius: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .job-quote-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .job-quote-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .job-quote-close {
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s ease;
        }

        .job-quote-close:hover {
            color: #333;
        }

        .job-quote-content {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .job-quote-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
        }

        .job-quote-label {
            font-weight: 600;
            color: #555;
            min-width: 110px;
        }

        .job-quote-value {
            color: #2c3e50;
            flex: 1;
        }

        .job-quote-budget {
            background: rgb(159, 232, 112);
            color: #333;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 700;
            display: inline-block;
            width: fit-content;
        }

        .job-quote-description {
            margin-top: 8px;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .job-quote-description .job-quote-label {
            display: block;
            margin-bottom: 8px;
        }

        .job-quote-text {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .job-quote-actions {
            display: flex;
            gap: 10px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .job-quote-btn {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .job-quote-send {
            background: #1ab394;
            color: white;
            flex: 1;
        }

        .job-quote-send:hover {
            background: #158a74;
            box-shadow: 0 2px 8px rgba(26, 179, 148, 0.3);
        }

        .job-quote-send:active {
            transform: scale(0.98);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .job-quote-container {
                padding: 15px;
            }

            .job-quote-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }

            .job-quote-label {
                min-width: auto;
            }
        }
    </style>
</body>

</html>