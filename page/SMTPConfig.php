<?php

/**
 * SMTP Configuration for Email Sending
 * Configure these settings to enable email functionality
 */

// Gmail SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'jimmychankahlok66@gmail.com');
define('SMTP_PASSWORD', 'kygf bpez eztp fdlp'); // Use App Password, not regular password
define('SMTP_FROM_EMAIL', 'jimmychankahlok66@gmail.com');
define('SMTP_FROM_NAME', 'WorkSnyc');

// TEST MODE - Set to TRUE to display OTP on screen instead of sending email (for development)
define('TEST_MODE', false);

// EMAIL_LOG - Set to TRUE to log sent emails to a file
define('EMAIL_LOG_FILE', '../uploads/email_logs.txt');

/**
 * Send email using direct socket connection to Gmail SMTP
 * This is a simple implementation without external libraries
 */
function sendEmailViaSMTP($to, $subject, $message, $headers = "")
{
    $default_headers = "MIME-Version: 1.0\r\n";
    $default_headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $default_headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $default_headers .= "Return-Path: " . SMTP_FROM_EMAIL . "\r\n";
    
    if ($headers) {
        $default_headers .= $headers;
    }
    
    return mail($to, $subject, $message, $default_headers);
}

/**
 * Log email to file for debugging
 */
function logEmail($to, $subject, $otp = null)
{
    $log_dir = dirname(EMAIL_LOG_FILE);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_entry = "[" . date('Y-m-d H:i:s') . "] To: $to | Subject: $subject";
    if ($otp) {
        $log_entry .= " | OTP: $otp";
    }
    $log_entry .= "\n";
    
    file_put_contents(EMAIL_LOG_FILE, $log_entry, FILE_APPEND);
}
