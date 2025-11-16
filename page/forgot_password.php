<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - WorkSnyc</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-icon">🔧</div>
                <h1 class="logo-text">WorkSnyc</h1>
            </div>

            <h2 class="welcome-text">Forgot Password?</h2>
            <p class="subtitle">Enter your email to reset your password</p>

            <form action="forgot_password_process.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your email"
                        required>
                </div>

                <button type="submit" class="btn-signin">Reset Password</button>
            </form>

            <div class="signup-section">
                <a href="login.php" class="signup-link">Back to Sign In</a>
            </div>
        </div>
    </div>
</body>

</html>