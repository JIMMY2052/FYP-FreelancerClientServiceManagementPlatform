<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'freelancer') {
        header('Location: ../freelancer_home.php');
    } else {
        header('Location: ../client_home.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - WorkSnyc</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-icon">🔧</div>
                <h1 class="logo-text">WorkSnyc</h1>
            </div>

            <h2 class="welcome-text">Welcome Back!</h2>
            <p class="subtitle">Sign in to continue to your dashboard</p>

            <form action="login_process.php" method="POST" class="login-form">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="user_type">Login as</label>
                    <select name="user_type" id="user_type" class="form-control" required>
                        <option value="freelancer" selected>Freelancer</option>
                        <option value="client">Client</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your email"
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