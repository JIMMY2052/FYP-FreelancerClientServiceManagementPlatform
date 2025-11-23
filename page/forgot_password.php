<?php
require_once 'config.php';

// Get messages from session
$hasError = isset($_SESSION['error']);
$error_message = $_SESSION['error'] ?? '';
$hasSuccess = isset($_SESSION['success']);
$success_message = $_SESSION['success'] ?? '';

if ($hasError) unset($_SESSION['error']);
if ($hasSuccess) unset($_SESSION['success']);

// Determine which step of recovery we're on
$step = $_GET['step'] ?? 'email'; // 'email', 'otp', 'newpassword'
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - WorkSnyc</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .recovery-progress {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            position: relative;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            flex: 1;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e5e7eb;
            border: 2px solid #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6b7280;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }

        .progress-step.active .step-number {
            background-color: #22c55e;
            border-color: #16a34a;
            color: white;
        }

        .progress-step.completed .step-number {
            background-color: #22c55e;
            border-color: #16a34a;
            color: white;
        }

        .step-name {
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }

        .progress-step.active .step-name {
            color: #22c55e;
            font-weight: 600;
        }

        .progress-line {
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background-color: #e5e7eb;
            z-index: -1;
        }

        .progress-step:first-child .progress-line {
            display: none;
        }

        .progress-step.completed .progress-line {
            background-color: #22c55e;
        }

        .otp-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
        }

        .otp-input {
            width: 50px;
            height: 50px;
            font-size: 24px;
            text-align: center;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .otp-input:focus {
            border-color: #22c55e;
            outline: none;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }

        .otp-input.filled {
            background-color: #f0fde8;
            border-color: #22c55e;
        }

        .resend-otp {
            text-align: center;
            margin-top: 20px;
        }

        .resend-button {
            background: none;
            border: none;
            color: #22c55e;
            cursor: pointer;
            text-decoration: underline;
            font-size: 14px;
        }

        .resend-button:hover {
            color: #16a34a;
        }

        .resend-button:disabled {
            color: #d1d5db;
            cursor: not-allowed;
            text-decoration: none;
        }

        .timer {
            color: #6b7280;
            font-size: 14px;
            margin-left: 10px;
        }

        .password-strength {
            margin-top: 10px;
        }

        .strength-bar {
            height: 4px;
            background-color: #e5e7eb;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-fill.weak {
            width: 33%;
            background-color: #ef4444;
        }

        .strength-fill.medium {
            width: 66%;
            background-color: #f59e0b;
        }

        .strength-fill.strong {
            width: 100%;
            background-color: #22c55e;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <h1 class="logo-text">WorkSnyc</h1>
            </div>

            <h2 class="welcome-text">Password Recovery</h2>

            <!-- Progress Steps -->
            <div class="recovery-progress">
                <div class="progress-step <?php echo in_array($step, ['email', 'otp', 'newpassword']) ? 'completed' : ''; ?> <?php echo $step === 'email' ? 'active' : ''; ?>">
                    <div class="step-number">1</div>
                    <div class="step-name">Email</div>
                    <div class="progress-line"></div>
                </div>
                <div class="progress-step <?php echo in_array($step, ['otp', 'newpassword']) ? ($step === 'otp' ? 'active' : 'completed') : ''; ?>">
                    <div class="step-number">2</div>
                    <div class="step-name">OTP</div>
                    <div class="progress-line"></div>
                </div>
                <div class="progress-step <?php echo $step === 'newpassword' ? 'active' : ''; ?>">
                    <div class="step-number">3</div>
                    <div class="step-name">New Password</div>
                </div>
            </div>

            <!-- Step 1: Email Entry -->
            <?php if ($step === 'email'): ?>
                <p class="subtitle">Enter your email to receive an OTP</p>

                <form action="forgot_password_process.php" method="POST" class="login-form">
                    <input type="hidden" name="action" value="send_otp">

                    <?php if ($hasError): ?>
                        <div class="error-message">
                            <strong>⚠️ Error</strong><br>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

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

                    <button type="submit" class="btn-signin">Send OTP</button>
                </form>

                <!-- Step 2: OTP Verification -->
            <?php elseif ($step === 'otp'): ?>
                <p class="subtitle">Enter the OTP sent to your email</p>

                <form action="forgot_password_process.php" method="POST" class="login-form">
                    <input type="hidden" name="action" value="verify_otp">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['recovery_email'] ?? ''); ?>">

                    <?php if ($hasError): ?>
                        <div class="error-message">
                            <strong>⚠️ Error</strong><br>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($hasSuccess): ?>
                        <div class="success-message">
                            <strong>✓ Success!</strong><br>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Enter 6-Digit OTP</label>
                        <div class="otp-inputs" id="otp-container">
                            <input type="text" class="otp-input" id="otp1" maxlength="1" inputmode="numeric" required>
                            <input type="text" class="otp-input" id="otp2" maxlength="1" inputmode="numeric" required>
                            <input type="text" class="otp-input" id="otp3" maxlength="1" inputmode="numeric" required>
                            <input type="text" class="otp-input" id="otp4" maxlength="1" inputmode="numeric" required>
                            <input type="text" class="otp-input" id="otp5" maxlength="1" inputmode="numeric" required>
                            <input type="text" class="otp-input" id="otp6" maxlength="1" inputmode="numeric" required>
                        </div>
                        <input type="hidden" id="otp_full" name="otp" value="">
                    </div>

                    <button type="submit" class="btn-signin">Verify OTP</button>

                    <div class="resend-otp">
                        <button type="button" class="resend-button" id="resend-btn" disabled>Resend OTP <span class="timer" id="timer">60s</span></button>
                    </div>
                </form>

                <!-- Step 3: New Password -->
            <?php elseif ($step === 'newpassword'): ?>
                <p class="subtitle">Create a new password for your account</p>

                <form action="forgot_password_process.php" method="POST" class="login-form">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['recovery_email'] ?? ''); ?>">

                    <?php if ($hasError): ?>
                        <div class="error-message">
                            <strong>⚠️ Error</strong><br>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <small style="color: #6b7280; display: block; margin-bottom: 5px;">Minimum 8 characters, must include at least one special character (!@#$%^&* etc.)</small>
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            class="form-control"
                            placeholder="Create a new password"
                            required>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strength-indicator"></div>
                            </div>
                            <small id="strength-text" style="color: #6b7280;"></small>
                        </div>
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

                    <button type="submit" class="btn-signin">Reset Password</button>
                </form>

            <?php endif; ?>

            <div class="signup-section">
                <a href="login.php" class="signup-link">Back to Sign In</a>
            </div>
        </div>
    </div>

    <script>
        <?php if ($step === 'otp'): ?>
            // OTP Input Handling
            const otpInputs = document.querySelectorAll('.otp-input');
            const otpFull = document.getElementById('otp_full');

            otpInputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    if (this.value.length > 0) {
                        this.classList.add('filled');
                        if (index < otpInputs.length - 1) {
                            otpInputs[index + 1].focus();
                        }
                    } else {
                        this.classList.remove('filled');
                    }
                    updateOTPFull();
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                        otpInputs[index - 1].focus();
                    }
                });
            });

            function updateOTPFull() {
                const otp = Array.from(otpInputs).map(input => input.value).join('');
                otpFull.value = otp;
            }

            // Timer for resend button
            let timeLeft = 60;
            const resendBtn = document.getElementById('resend-btn');
            const timer = document.getElementById('timer');

            setInterval(() => {
                if (timeLeft > 0) {
                    timeLeft--;
                    timer.textContent = timeLeft + 's';
                } else {
                    resendBtn.disabled = false;
                }
            }, 1000);

            resendBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Implement resend OTP functionality
                location.reload();
            });

        <?php elseif ($step === 'newpassword'): ?>
            // Password strength checker
            const passwordInput = document.getElementById('new_password');
            const strengthIndicator = document.getElementById('strength-indicator');
            const strengthText = document.getElementById('strength-text');

            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;

                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                if (/\d/.test(password)) strength++;
                if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;

                strengthIndicator.className = 'strength-fill';
                if (strength < 2) {
                    strengthIndicator.classList.add('weak');
                    strengthText.textContent = 'Weak password';
                } else if (strength < 4) {
                    strengthIndicator.classList.add('medium');
                    strengthText.textContent = 'Medium password';
                } else {
                    strengthIndicator.classList.add('strong');
                    strengthText.textContent = 'Strong password';
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>