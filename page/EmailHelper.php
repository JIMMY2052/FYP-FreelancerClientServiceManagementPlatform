<?php

/**
 * Send OTP via Email Helper Function using PHPMailer
 * Sends OTP to user's email for password recovery
 */

require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load SMTP configuration
require_once 'SMTPConfig.php';

function sendOTPEmail($email, $otp, $userName = null)
{
    $to = $email;
    $subject = "WorkSnyc - Password Recovery OTP";

    // HTML email template
    $htmlMessage = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f9fafb;
            }
            .email-card {
                background-color: white;
                border-radius: 12px;
                padding: 30px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #22c55e;
                padding-bottom: 20px;
            }
            .logo {
                font-size: 28px;
                font-weight: bold;
                color: #22c55e;
            }
            h1 {
                color: #1f2937;
                font-size: 24px;
                margin: 20px 0 10px 0;
            }
            .otp-section {
                background-color: #f0fde8;
                border: 2px solid #22c55e;
                border-radius: 8px;
                padding: 25px;
                text-align: center;
                margin: 25px 0;
            }
            .otp-code {
                font-size: 36px;
                font-weight: bold;
                color: #22c55e;
                letter-spacing: 5px;
                font-family: 'Courier New', monospace;
            }
            .otp-label {
                color: #6b7280;
                font-size: 14px;
                margin-top: 10px;
            }
            .expiry-warning {
                background-color: #fef3c7;
                border-left: 4px solid #f59e0b;
                padding: 12px 15px;
                border-radius: 4px;
                margin: 20px 0;
                color: #92400e;
            }
            .instructions {
                background-color: #f3f4f6;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                color: #374151;
            }
            .instructions li {
                margin: 8px 0;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #e5e7eb;
                color: #6b7280;
                font-size: 12px;
            }
            .button {
                display: inline-block;
                background-color: #22c55e;
                color: white;
                padding: 12px 30px;
                border-radius: 6px;
                text-decoration: none;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='email-card'>
                <div class='header'>
                    <div class='logo'>WorkSnyc</div>
                    <p style='color: #6b7280; margin: 10px 0 0 0;'>Secure Password Recovery</p>
                </div>

                <h1>Password Recovery Request</h1>
                <p>Hello" . ($userName ? ", <strong>" . htmlspecialchars($userName) . "</strong>" : "") . ",</p>
                
                <p>We received a request to reset your WorkSnyc account password. Use the OTP (One-Time Password) below to proceed with your password recovery.</p>

                <div class='otp-section'>
                    <div class='otp-code'>" . htmlspecialchars($otp) . "</div>
                    <div class='otp-label'>Enter this code on the recovery page</div>
                </div>

                <div class='expiry-warning'>
                    <strong>⚠️ Important:</strong> This OTP will expire in 15 minutes. If you didn't request this, please ignore this email.
                </div>

                <div class='instructions'>
                    <p><strong>How to recover your password:</strong></p>
                    <ol>
                        <li>Go to the WorkSnyc password recovery page</li>
                        <li>Enter this OTP code</li>
                        <li>Create a new password</li>
                        <li>Sign in with your new password</li>
                    </ol>
                </div>

                <p style='color: #6b7280;'><strong>Security Note:</strong> Never share this OTP with anyone. WorkSnyc support staff will never ask for your OTP.</p>

                <div class='footer'>
                    <p>This is an automated email. Please don't reply to this message.</p>
                    <p>&copy; 2025 WorkSnyc. All rights reserved.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    // Email headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Return-Path: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHPMailer/" . phpversion();

    // Log the email attempt
    logEmail($email, $subject, $otp);

    // Send email using PHPMailer
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->Port = SMTP_PORT;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlMessage;

        // Send
        $emailSent = $mail->send();
        error_log("[OTP Email] Successfully sent OTP to: $email via PHPMailer");
        
    } catch (Exception $e) {
        error_log("[OTP Email] Failed to send OTP to: $email. Error: " . $mail->ErrorInfo);
        $emailSent = false;
    }

    return $emailSent;
}

/**
 * Generate a random 6-digit OTP
 */
function generateOTP()
{
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Verify OTP hasn't expired (15 minutes)
 */
function isOTPValid($createdAt, $expiryMinutes = 15)
{
    $createdTime = strtotime($createdAt);
    $currentTime = time();
    $ageInMinutes = ($currentTime - $createdTime) / 60;

    return $ageInMinutes <= $expiryMinutes;
}
