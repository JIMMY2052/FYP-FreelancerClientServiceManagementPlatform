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
    <style>
        /* Settings Page Styles */
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .settings-header {
            margin-bottom: 40px;
        }

        .settings-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .settings-header h1 i {
            color: rgb(159, 232, 112);
            font-size: 32px;
        }

        .settings-header p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        /* Alert Messages */
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #6ee7b7;
            color: #065f46;
        }

        .alert-success i {
            color: #10b981;
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
            color: #7f1d1d;
        }

        .alert-error i {
            color: #ef4444;
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* Settings Card */
        .settings-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .settings-card-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, #ffffff 0%, #f8fafb 100%);
        }

        .settings-card-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .settings-card-header i {
            color: rgb(159, 232, 112);
            font-size: 20px;
        }

        .settings-card-body {
            padding: 30px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: rgb(159, 232, 112);
            box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
            background-color: #fafafa;
        }

        .form-group input::placeholder {
            color: var(--text-secondary);
        }

        .password-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 16px;
            padding: 6px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--text-primary);
        }

        .form-group input[type="password"],
        .form-group input[type="text"] {
            padding-right: 40px;
        }

        /* Helper Text */
        .form-helper {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 6px;
        }

        /* Buttons */
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: linear-gradient(135deg, rgb(159, 232, 112) 0%, rgb(139, 212, 92) 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(159, 232, 112, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background-color: white;
            color: var(--text-primary);
            border: 1.5px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: var(--light-bg);
            border-color: rgb(159, 232, 112);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-container {
                padding: 20px 15px;
            }

            .settings-card-body {
                padding: 20px;
            }

            .settings-header h1 {
                font-size: 24px;
            }

            .admin-info-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
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
                                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('current_password')">
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
                                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('new_password')">
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
                                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirm_password')">
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

            if (field.type === 'password') {
                field.type = 'text';
                btn.innerHTML = '<i class="fas fa-eye"></i>';
            } else {
                field.type = 'password';
                btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
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