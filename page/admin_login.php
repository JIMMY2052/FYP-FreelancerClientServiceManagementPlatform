<?php
require_once 'config.php';

// Redirect if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign In - WorkSnyc</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-icon">🔐</div>
                <h1 class="logo-text">WorkSnyc Admin</h1>
            </div>

            <h2 class="welcome-text">Admin Portal</h2>
            <p class="subtitle">Sign in to access admin dashboard</p>

            <form action="admin_login_process.php" method="POST" class="login-form">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="email">Admin Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter admin email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot_password.php" class="forgot-link">Forgot your password?</a>
                </div>

                <button type="submit" class="btn-signin">Sign in as Admin</button>
            </form>

            <div class="signup-section">
                <p>Back to user login?</p>
                <a href="login.php" class="signup-link">User Sign In</a>
            </div>
        </div>
    </div>
</body>

</html>