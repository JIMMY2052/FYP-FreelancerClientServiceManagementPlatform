<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$error_message = '';
$success_message = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'New password and confirm password do not match.';
    } elseif (strlen($new_password) < 6) {
        $error_message = 'New password must be at least 6 characters long.';
    } else {
        // Verify current password
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT Password FROM admin WHERE AdminID = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($current_password, $admin['Password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password
            $update_stmt = $conn->prepare("UPDATE admin SET Password = ? WHERE AdminID = ?");
            $update_stmt->bind_param("si", $hashed_password, $admin_id);

            if ($update_stmt->execute()) {
                $success_message = 'Password changed successfully!';
            } else {
                $error_message = 'Failed to update password. Please try again.';
            }
            $update_stmt->close();
        } else {
            $error_message = 'Current password is incorrect.';
        }

        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - WorkSync</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin_settings.css">
</head>

<body class="admin-layout">
    <div class="admin-sidebar">
        <?php include '../includes/admin_sidebar.php'; ?>
    </div>

    <div class="admin-layout-wrapper">
        <?php include '../includes/admin_header.php'; ?>

        <main class="admin-main-content">
            <div class="settings-container">
                <div class="settings-header">
                    <h1>
                        <i class="fas fa-cog"></i>
                        Settings
                    </h1>
                    <p>Manage your admin account settings and security preferences</p>
                </div>

                <!-- Alert Messages -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div><?php echo htmlspecialchars($success_message); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?php echo htmlspecialchars($error_message); ?></div>
                    </div>
                <?php endif; ?>

                <!-- Change Password Card -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2>
                            <i class="fas fa-lock"></i>
                            Change Password
                        </h2>
                    </div>
                    <div class="settings-card-body">
                        <form method="POST" action="" id="passwordForm">
                            <input type="hidden" name="action" value="change_password">

                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <div class="password-input-wrapper">
                                    <input
                                        type="password"
                                        id="current_password"
                                        name="current_password"
                                        placeholder="Enter your current password"
                                        required>
                                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('current_password'); return false;">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <div class="password-input-wrapper">
                                    <input
                                        type="password"
                                        id="new_password"
                                        name="new_password"
                                        placeholder="Enter your new password"
                                        required>
                                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('new_password'); return false;">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </div>
                                <div class="form-helper">
                                    Password must be at least 6 characters long
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <div class="password-input-wrapper">
                                    <input
                                        type="password"
                                        id="confirm_password"
                                        name="confirm_password"
                                        placeholder="Confirm your new password"
                                        required>
                                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirm_password'); return false;">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="button-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i>
                                    Update Password
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle password visibility
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const btn = event.target.closest('.password-toggle');

            if (!btn) return; // Exit if click wasn't on the button

            event.preventDefault();
            event.stopPropagation();

            if (field.type === 'password') {
                field.type = 'text';
                btn.querySelector('i').className = 'fas fa-eye';
            } else {
                field.type = 'password';
                btn.querySelector('i').className = 'fas fa-eye-slash';
            }
        }

        // Form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (!currentPassword || !newPassword || !confirmPassword) {
                e.preventDefault();
                alert('All fields are required');
                return false;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New password and confirm password do not match');
                return false;
            }

            if (newPassword.length < 6) {
                e.preventDefault();
                alert('New password must be at least 6 characters long');
                return false;
            }

            return true;
        });

        // Auto-hide success message after 5 seconds
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.animation = 'slideDown 0.3s ease reverse';
                setTimeout(() => {
                    successAlert.remove();
                }, 300);
            }, 5000);
        }
    </script>
</body>

</html>