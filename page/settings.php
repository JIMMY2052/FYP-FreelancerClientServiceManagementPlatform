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

// Get user data from database
$conn = getDBConnection();

if ($user_type === 'freelancer') {
    $sql = "SELECT * FROM freelancer WHERE FreelancerID = ?";
} else {
    $sql = "SELECT * FROM client WHERE ClientID = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Handle form submissions
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'password') {
        $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_msg = "All password fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $error_msg = "New passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $error_msg = "Password must be at least 8 characters long.";
        } else {
            // Verify current password
            if ($user_type === 'freelancer') {
                $check_sql = "SELECT Password FROM freelancer WHERE FreelancerID = ?";
            } else {
                $check_sql = "SELECT Password FROM client WHERE ClientID = ?";
            }

            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param('i', $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $check_data = $check_result->fetch_assoc();
            $check_stmt->close();

            if (!password_verify($current_password, $check_data['Password'])) {
                $error_msg = "Current password is incorrect.";
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                if ($user_type === 'freelancer') {
                    $update_sql = "UPDATE freelancer SET Password = ? WHERE FreelancerID = ?";
                } else {
                    $update_sql = "UPDATE client SET Password = ? WHERE ClientID = ?";
                }

                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param('si', $hashed_password, $user_id);

                if ($update_stmt->execute()) {
                    $success_msg = "Password changed successfully.";
                } else {
                    $error_msg = "Failed to change password. Please try again.";
                }
                $update_stmt->close();
            }
        }
    } elseif ($action === 'notifications') {
        // Handle notification settings
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $message_notifications = isset($_POST['message_notifications']) ? 1 : 0;

        // Store in session or database (implement based on your needs)
        $_SESSION['email_notifications'] = $email_notifications;
        $_SESSION['message_notifications'] = $message_notifications;

        $success_msg = "Notification preferences updated.";
    } elseif ($action === 'privacy') {
        // Handle privacy settings
        $profile_visibility = isset($_POST['profile_visibility']) ? $_POST['profile_visibility'] : 'public';

        // Store in session or database
        $_SESSION['profile_visibility'] = $profile_visibility;

        $success_msg = "Privacy settings updated.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - WorkSync</title>
    <link rel="stylesheet" href="../assets/css/settings.css">
</head>

<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f5f3f7; color: #1f2937;">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-icon">⚙️</span>
            <p class="logo-text">Settings</p>
        </div>

        <nav class="sidebar-nav">
            <a href="#account" class="sidebar-nav-item active" data-section="account">
                <span class="nav-icon">👤</span>
                <span class="nav-text">Account</span>
            </a>
            <a href="#notifications" class="sidebar-nav-item" data-section="notifications">
                <span class="nav-icon">🔔</span>
                <span class="nav-text">Notifications</span>
            </a>
            <a href="#privacy" class="sidebar-nav-item" data-section="privacy">
                <span class="nav-icon">🔒</span>
                <span class="nav-text">Privacy</span>
            </a>
            <a href="#help" class="sidebar-nav-item" data-section="help">
                <span class="nav-icon">❓</span>
                <span class="nav-text">Help & Support</span>
            </a>
        </nav>

        <div style="margin-top: 40px; padding: 0 20px; border-top: 1px solid #e0e0e0; padding-top: 20px;">
            <a href="<?php echo $user_type === 'freelancer' ? '../freelancer_home.php' : '../client_home.php'; ?>" class="sidebar-nav-item" style="margin-bottom: 10px;">
                <span class="nav-icon">←</span>
                <span class="nav-text">Back to Home</span>
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="profile-container">

            <!-- Messages -->
            <?php if ($success_msg): ?>
                <div class="message success-message" id="successMsg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <?php echo htmlspecialchars($success_msg); ?>
                </div>
                <script>
                    setTimeout(() => {
                        const msg = document.getElementById('successMsg');
                        if (msg) msg.style.display = 'none';
                    }, 5000);
                </script>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="message error-message" id="errorMsg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <?php echo htmlspecialchars($error_msg); ?>
                </div>
                <script>
                    setTimeout(() => {
                        const msg = document.getElementById('errorMsg');
                        if (msg) msg.style.display = 'none';
                    }, 5000);
                </script>
            <?php endif; ?>

            <!-- ACCOUNT SECTION -->
            <section id="account" class="settings-section active">
                <h2>Account Settings</h2>

                <!-- Change Password -->
                <div class="settings-card">
                    <h3>Change Password</h3>
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="password">

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required placeholder="Enter your current password">
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required placeholder="Enter new password (min 8 characters)">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm new password">
                        </div>

                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>

                <!-- Account Info -->
                <div class="settings-card">
                    <h3>Account Information</h3>
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value"><?php echo htmlspecialchars($user_email); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Account Type</span>
                        <span class="value"><?php echo ucfirst($user_type); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Member Since</span>
                        <span class="value"><?php echo isset($user_data['JoinedDate']) ? date('F d, Y', strtotime($user_data['JoinedDate'])) : 'N/A'; ?></span>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="settings-card danger-zone">
                    <h3>Danger Zone</h3>
                    <p>Once you delete your account, there is no going back. Please be certain.</p>
                    <button class="btn btn-danger" onclick="if(confirm('Are you sure you want to delete your account? This cannot be undone.')) window.location.href='delete_account.php';">Delete Account</button>
                </div>
            </section>

            <!-- NOTIFICATIONS SECTION -->
            <section id="notifications" class="settings-section">
                <h2>Notification Settings</h2>

                <div class="settings-card">
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="notifications">

                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="email_notifications" <?php echo isset($_SESSION['email_notifications']) && $_SESSION['email_notifications'] ? 'checked' : ''; ?>>
                                <span class="checkbox-label">Email Notifications</span>
                                <span class="checkbox-description">Receive email notifications for important updates</span>
                            </label>
                        </div>

                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="message_notifications" <?php echo isset($_SESSION['message_notifications']) && $_SESSION['message_notifications'] ? 'checked' : ''; ?>>
                                <span class="checkbox-label">Message Notifications</span>
                                <span class="checkbox-description">Get notified when you receive new messages</span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                    </form>
                </div>
            </section>

            <!-- PRIVACY SECTION -->
            <section id="privacy" class="settings-section">
                <h2>Privacy Settings</h2>

                <div class="settings-card">
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="privacy">

                        <div class="form-group">
                            <label for="profile_visibility">Profile Visibility</label>
                            <select id="profile_visibility" name="profile_visibility" class="form-select">
                                <option value="public" <?php echo isset($_SESSION['profile_visibility']) && $_SESSION['profile_visibility'] === 'public' ? 'selected' : ''; ?>>Public - Visible to everyone</option>
                                <option value="registered" <?php echo isset($_SESSION['profile_visibility']) && $_SESSION['profile_visibility'] === 'registered' ? 'selected' : ''; ?>>Registered Users Only</option>
                                <option value="private" <?php echo isset($_SESSION['profile_visibility']) && $_SESSION['profile_visibility'] === 'private' ? 'selected' : ''; ?>>Private - Only visible to you</option>
                            </select>
                        </div>

                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" checked disabled>
                                <span class="checkbox-label">Show Email Address</span>
                                <span class="checkbox-description">Always kept private for your security</span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Privacy Settings</button>
                    </form>
                </div>
            </section>

            <!-- HELP SECTION -->
            <section id="help" class="settings-section">
                <h2>Help & Support</h2>

                <div class="settings-card">
                    <h3>Frequently Asked Questions</h3>
                    <div class="faq-item">
                        <h4>How do I change my profile information?</h4>
                        <p>Click on your profile from the home page and select "Edit Profile" to update your information.</p>
                    </div>
                    <div class="faq-item">
                        <h4>How do I reset my password?</h4>
                        <p>Go to the "Account" section in settings and use the "Change Password" form. You can also use the "Forgot Password" link on the login page.</p>
                    </div>
                    <div class="faq-item">
                        <h4>How can I contact support?</h4>
                        <p>Visit our Contact Us page or email us at support@worksync.com for assistance.</p>
                    </div>
                </div>

                <div class="settings-card">
                    <h3>Contact Support</h3>
                    <p>Need help? Reach out to our support team:</p>
                    <ul class="support-links">
                        <li><a href="../contact_us.php">📧 Contact Us Form</a></li>
                        <li><a href="messages.php">💬 Send Message</a></li>
                        <li><a href="mailto:support@worksync.com">📨 Email Support</a></li>
                    </ul>
                </div>
            </section>

        </div>
    </div>

    <script>
        // Section navigation
        document.querySelectorAll('.sidebar-nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const section = item.getAttribute('data-section');
                if (!section) return;

                e.preventDefault();

                // Remove active from all
                document.querySelectorAll('.sidebar-nav-item').forEach(nav => nav.classList.remove('active'));
                document.querySelectorAll('.settings-section').forEach(sec => sec.classList.remove('active'));

                // Add active to clicked
                item.classList.add('active');
                document.getElementById(section).classList.add('active');
            });
        });
    </script>

</body>

</html>