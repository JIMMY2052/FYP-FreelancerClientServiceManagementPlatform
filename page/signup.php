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

// Get error and form data from session
$hasError = isset($_SESSION['error']);
$error_message = $_SESSION['error'] ?? '';
$hasSuccess = isset($_SESSION['success']);
$success_message = $_SESSION['success'] ?? '';
$fieldErrors = $_SESSION['errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [
    'user_type' => 'freelancer',
    'email' => '',
    'first_name' => '',
    'last_name' => '',
    'company_name' => ''
];

// Clear session variables after retrieving them
if ($hasError) {
    unset($_SESSION['error']);
}
if ($hasSuccess) {
    unset($_SESSION['success']);
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
    <title>Sign Up - WorkSnyc</title>
    <link rel="icon" type="image/png" href="/images/tabLogo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/signup.css">
    <script src="/assets/js/signup.js" defer></script>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <img src="../images/logo.png" alt="WorkSnyc Logo" class="logo-icon">
            </div>

            <h2 class="welcome-text">Create Account</h2>
            <p class="subtitle">Sign up to get started</p>

            <?php if ($hasSuccess): ?>
                <div class="signup-success-modal" id="signupSuccessModal">
                    <div class="signup-success-content">
                        <div class="signup-success-title">Account Created</div>
                        <div class="signup-success-text"><?php echo htmlspecialchars($success_message); ?></div>
                        <button type="button" class="signup-success-button" onclick="window.location.href='login.php'">Go to Sign In</button>
                    </div>
                </div>
            <?php endif; ?>

            <form action="signup_process.php" method="POST" class="login-form">
                <?php if ($hasError): ?>
                    <div class="error-message">
                        <strong>⚠️ Sign Up Failed</strong><br>
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

                <input type="hidden" id="user_type_hidden" name="user_type_hidden" value="<?php echo htmlspecialchars($form_data['user_type']); ?>">

                <div id="freelancer-fields">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control<?php echo isset($fieldErrors['first_name']) ? ' error' : ''; ?>" placeholder="Enter your first name" value="<?php echo htmlspecialchars($form_data['first_name']); ?>">
                        <?php if (isset($fieldErrors['first_name'])): ?>
                            <small class="field-error"><?php echo htmlspecialchars($fieldErrors['first_name']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control<?php echo isset($fieldErrors['last_name']) ? ' error' : ''; ?>" placeholder="Enter your last name" value="<?php echo htmlspecialchars($form_data['last_name']); ?>">
                        <?php if (isset($fieldErrors['last_name'])): ?>
                            <small class="field-error"><?php echo htmlspecialchars($fieldErrors['last_name']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="client-fields" class="hidden">
                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <?php
                        $isClient = $form_data['user_type'] === 'client';
                        ?>
                        <input type="text" id="company_name" name="company_name" class="form-control<?php echo ($isClient && isset($fieldErrors['company_name'])) ? ' error' : ''; ?>" placeholder="Enter your company name" value="<?php echo htmlspecialchars($form_data['company_name']); ?>">
                        <?php if ($isClient && isset($fieldErrors['company_name'])): ?>
                            <small class="field-error"><?php echo htmlspecialchars($fieldErrors['company_name']); ?></small>
                        <?php endif; ?>
                    </div>
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
                    <small style="color: #6b7280; display: block; margin-bottom: 5px;">Minimum 8 characters, must include at least one special character (!@#$%^&* etc.)</small>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control<?php echo isset($fieldErrors['password']) ? ' error' : ''; ?>"
                        placeholder="Create a password"
                        required>
                    <?php if (isset($fieldErrors['password'])): ?>
                        <small class="field-error"><?php echo htmlspecialchars($fieldErrors['password']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control<?php echo isset($fieldErrors['confirm_password']) ? ' error' : ''; ?>"
                        placeholder="Confirm your password"
                        required>
                    <?php if (isset($fieldErrors['confirm_password'])): ?>
                        <small class="field-error"><?php echo htmlspecialchars($fieldErrors['confirm_password']); ?></small>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-signin">Sign up</button>
            </form>

            <div class="signup-section">
                <p>Already have an account?</p>
                <a href="login.php" class="signup-link">Sign in</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleInputs = document.querySelectorAll('input[name="user_type"]');
            const freelancerFields = document.getElementById('freelancer-fields');
            const clientFields = document.getElementById('client-fields');

            function updateFormFields() {
                const selectedRole = document.querySelector('input[name="user_type"]:checked').value;

                if (selectedRole === 'freelancer') {
                    freelancerFields.classList.remove('hidden');
                    clientFields.classList.add('hidden');
                } else {
                    freelancerFields.classList.add('hidden');
                    clientFields.classList.remove('hidden');
                }
            }

            // Initialize form on page load
            updateFormFields();

            // Listen for role changes
            roleInputs.forEach(input => {
                input.addEventListener('change', updateFormFields);
            });
        });
    </script>
</body>

</html>