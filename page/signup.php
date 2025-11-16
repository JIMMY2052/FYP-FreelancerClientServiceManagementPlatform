<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'freelancer') {
        header('Location: freelancer_dashboard.php');
    } else {
        header('Location: client_dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - WorkSnyc</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="assets/js/signup.js" defer></script>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-icon">🔧</div>
                <h1 class="logo-text">WorkSnyc</h1>
            </div>

            <h2 class="welcome-text">Create Account</h2>
            <p class="subtitle">Sign up to get started</p>

            <form action="signup_process.php" method="POST" class="login-form">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message">
                        <?php
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="user_type">Sign up as</label>
                    <select name="user_type" id="user_type" class="form-control" required>
                        <option value="freelancer" selected>Freelancer</option>
                        <option value="client">Client</option>
                    </select>
                </div>

                <div id="freelancer-fields">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Enter your first name">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Enter your last name">
                    </div>
                </div>

                <div id="client-fields" class="hidden">
                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" id="company_name" name="company_name" class="form-control" placeholder="Enter your company name">
                    </div>
                </div>

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

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Create a password"
                        required
                        minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                        placeholder="Confirm your password"
                        required>
                </div>

                <button type="submit" class="btn-signin">Sign up</button>
            </form>

            <div class="signup-section">
                <p>Already have an account?</p>
                <a href="login.php" class="signup-link">Sign in</a>
            </div>
        </div>
    </div>
</body>

</html>