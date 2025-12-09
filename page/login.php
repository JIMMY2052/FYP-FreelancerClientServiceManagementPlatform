<?php
require_once 'config.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'freelancer') {
        header('Location: ../freelancer_home.php');
    } else {
        header('Location: ../client_home.php');
    }
    exit();
}

// Get form data and error from session
$hasError = isset($_SESSION['error']);
$error_message = $_SESSION['error'] ?? '';
$fieldErrors = $_SESSION['errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? ['email' => '', 'user_type' => 'freelancer'];

// Clear session variables after retrieving them
if ($hasError) {
    unset($_SESSION['error']);
}
if (isset($_SESSION['errors'])) {
    unset($_SESSION['errors']);
}
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WorkSnyc</title>
    <link rel="icon" type="image/png" href="/images/tabLogo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .role-selection {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            justify-content: center;
        }

        .role-option {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .role-input {
            display: none;
        }

        .role-button {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 40px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background-color: #ffffff;
            transition: all 0.3s ease;
            min-width: 150px;
            gap: 10px;
        }

        .role-input:checked+.role-button {
            border-color: #22c55e;
            background-color: #f0fde8;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.2);
        }

        .role-option:hover .role-button {
            border-color: #16a34a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .role-label {
            font-weight: 600;
            color: #1f2937;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <img src="../images/logo.png" alt="WorkSnyc Logo" class="logo-icon">
            </div>

            <h2 class="welcome-text">Welcome Back!</h2>
            <p class="subtitle">Sign in to continue to your dashboard</p>

            <form action="login_process.php" method="POST" class="login-form">
                <?php if ($hasError): ?>
                    <div class="error-message">
                        <strong>Login Failed</strong><br>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Role Selection Buttons -->
                <div class="role-selection">
                    <label class="role-option">
                        <input type="radio" name="user_type" value="freelancer" <?php echo $form_data['user_type'] === 'freelancer' ? 'checked' : ''; ?> class="role-input" required>
                        <span class="role-button">
                            <span class="role-label">Freelancer</span>
                        </span>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="user_type" value="client" <?php echo $form_data['user_type'] === 'client' ? 'checked' : ''; ?> class="role-input" required>
                        <span class="role-button">
                            <span class="role-label">Client</span>
                        </span>
                    </label>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control<?php echo isset($fieldErrors['email']) ? ' error' : ''; ?>"
                        placeholder="Enter your email"
                        value="<?php echo htmlspecialchars($form_data['email']); ?>"
                        required>
                    <?php if (isset($fieldErrors['email'])): ?>
                        <small class="field-error"><?php echo htmlspecialchars($fieldErrors['email']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control<?php echo isset($fieldErrors['password']) ? ' error' : ''; ?>"
                        placeholder="Enter your password"
                        required>
                    <?php if (isset($fieldErrors['password'])): ?>
                        <small class="field-error"><?php echo htmlspecialchars($fieldErrors['password']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot_password.php" class="forgot-link">Forgot your password?</a>
                </div>

                <button type="submit" class="btn-signin">Sign in</button>
            </form>

            <div class="signup-section">
                <p>Don't have an account?</p>
                <a href="signup.php" class="signup-link">Sign up</a>
            </div>
        </div>
    </div>
</body>

</html>