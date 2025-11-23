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

            <form action="signup_process.php" method="POST" class="login-form">
                <?php if ($hasError): ?>
                    <div class="error-message">
                        <strong>⚠️ Sign Up Failed</strong><br>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($hasSuccess): ?>
                    <div class="success-message">
                        <strong>✓ Success!</strong><br>
                        <?php echo htmlspecialchars($success_message); ?>
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
                        <input type="text" id="first_name" name="first_name" class="form-control<?php echo $hasError ? ' error' : ''; ?>" placeholder="Enter your first name" value="<?php echo htmlspecialchars($form_data['first_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control<?php echo $hasError ? ' error' : ''; ?>" placeholder="Enter your last name" value="<?php echo htmlspecialchars($form_data['last_name']); ?>">
                    </div>
                </div>

                <div id="client-fields" class="hidden">
                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" id="company_name" name="company_name" class="form-control<?php echo $hasError ? ' error' : ''; ?>" placeholder="Enter your company name" value="<?php echo htmlspecialchars($form_data['company_name']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control<?php echo $hasError ? ' error' : ''; ?>"
                        placeholder="Enter your email"
                        value="<?php echo htmlspecialchars($form_data['email']); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <small style="color: #6b7280; display: block; margin-bottom: 5px;">Minimum 8 characters, must include at least one special character (!@#$%^&* etc.)</small>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control<?php echo $hasError ? ' error' : ''; ?>"
                        placeholder="Create a password"
                        required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control<?php echo $hasError ? ' error' : ''; ?>"
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