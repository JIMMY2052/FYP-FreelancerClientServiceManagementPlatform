<?php
session_start();

// Prevent caching of this page
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

require_once '../config.php';
require_once '../../vendor/autoload.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['client', 'freelancer'])) {
    header('Location: ../login.php');
    exit();
}

// Stripe configuration
$stripe_secret_key = "sk_test_51SVTUkRTrldFvsR7kitkuJqbCONvPJmT8oZwGtnsaxNiWuLH8SsIRTsfO6WkLsF53B0XzXdvduGhYuZWgN2Fm64r00nawN4i0S";
\Stripe\Stripe::setApiKey($stripe_secret_key);

$success = false;
$error_message = '';
$amount = 0;

try {
    // Get session ID from URL
    if (!isset($_GET['session_id'])) {
        throw new Exception('Invalid payment session');
    }

    $session_id = $_GET['session_id'];
    
    // Check if this payment was already processed (prevent back button reprocessing)
    if (isset($_SESSION['processed_session_' . $session_id])) {
        // Already processed, redirect to wallet
        header('Location: wallet.php');
        exit();
    }

    // Retrieve the session from Stripe
    $session = \Stripe\Checkout\Session::retrieve($session_id);

    // Verify payment was successful
    if ($session->payment_status === 'paid') {
        $amount = $session->amount_total / 100; // Convert from cents
        $user_id = $_SESSION['user_id'];

        $conn = getDBConnection();

        // Start transaction
        $conn->begin_transaction();

        try {
            // Get or create wallet
            $sql = "SELECT WalletID, Balance FROM wallet WHERE UserID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $wallet = $result->fetch_assoc();
                $wallet_id = $wallet['WalletID'];
                $current_balance = $wallet['Balance'];
            } else {
                // Create wallet
                $sql = "INSERT INTO wallet (UserID, Balance) VALUES (?, 0.00)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $user_id);
                $stmt->execute();
                $wallet_id = $conn->insert_id;
                $current_balance = 0;
            }
            $stmt->close();

            // Update wallet balance
            $new_balance = $current_balance + $amount;
            $sql = "UPDATE wallet SET Balance = ? WHERE WalletID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('di', $new_balance, $wallet_id);
            $stmt->execute();
            $stmt->close();

            // Record transaction
            $description = "Wallet Top Up via Stripe";
            $transaction_type = "topup";
            $status = "completed";

            $sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Description, Status) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('isdss', $wallet_id, $transaction_type, $amount, $description, $status);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            $success = true;
            
            // Mark this session as processed to prevent back button reprocessing
            $_SESSION['processed_session_' . $session_id] = true;
            
            // Clear checkout token
            if (isset($_SESSION['checkout_token'])) {
                unset($_SESSION['checkout_token']);
                unset($_SESSION['checkout_time']);
            }

            // Clear session variables
            unset($_SESSION['topup_amount']);
            unset($_SESSION['topup_user_id']);

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        $conn->close();
    } else {
        throw new Exception('Payment not completed');
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log('Top-up error: ' . $error_message);
}

$_title = 'Payment Success - WorkSnyc Platform';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/client.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .success-container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 16px;
            padding: 50px 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(34, 197, 94, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }

        .success-icon i {
            font-size: 2.5rem;
            color: #22c55e;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }

        .error-icon i {
            font-size: 2.5rem;
            color: #ef4444;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 15px 0;
        }

        .amount {
            font-size: 2.5rem;
            font-weight: 700;
            color: #22c55e;
            margin: 20px 0;
        }

        p {
            color: #666;
            font-size: 1rem;
            line-height: 1.6;
            margin: 0 0 30px 0;
        }

        .btn-wallet {
            display: inline-block;
            padding: 14px 32px;
            background: rgb(159, 232, 112);
            color: #2c3e50;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-wallet:hover {
            background: rgb(140, 210, 90);
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
            transform: translateY(-2px);
        }

        .btn-secondary {
            display: inline-block;
            padding: 14px 32px;
            background: #f8fafc;
            color: #2c3e50;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background: white;
            border-color: rgb(159, 232, 112);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <?php if ($success): ?>
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h1>Top Up Successful!</h1>
            <div class="amount">+RM <?= number_format($amount, 2) ?></div>
            <p>Your wallet has been successfully topped up. The funds are now available in your account.</p>
            <?php
            $return_to = isset($_SESSION['topup_return_to']) ? $_SESSION['topup_return_to'] : 'wallet';
            $return_url = 'wallet.php';
            
            if ($return_to === 'payment_details' && isset($_SESSION['topup_gig_id']) && !empty($_SESSION['topup_gig_id'])) {
                $gig_id = $_SESSION['topup_gig_id'];
                $rush = isset($_SESSION['topup_rush']) ? $_SESSION['topup_rush'] : '';
                $extra_revisions = isset($_SESSION['topup_extra_revisions']) ? $_SESSION['topup_extra_revisions'] : '';
                $return_url = 'payment_details.php?gig_id=' . $gig_id . '&rush=' . $rush;
                if (!empty($extra_revisions)) {
                    $return_url .= '&extra_revisions=' . $extra_revisions;
                }
                // Clear the session variables after use
                unset($_SESSION['topup_return_to']);
                unset($_SESSION['topup_gig_id']);
                unset($_SESSION['topup_rush']);
                unset($_SESSION['topup_extra_revisions']);
            } elseif ($return_to === 'wallet') {
                $return_url = 'wallet.php';
                unset($_SESSION['topup_return_to']);
            }
            ?>
            <a href="<?= $return_url ?>" class="btn-wallet">
                <i class="fas fa-arrow-right"></i> Continue
            </a>
        <?php else: ?>
            <div class="error-icon">
                <i class="fas fa-times"></i>
            </div>
            <h1>Payment Failed</h1>
            <p><?= htmlspecialchars($error_message) ?></p>
            <a href="wallet.php" class="btn-wallet">Try Again</a>
        <?php endif; ?>
    </div>
    
    <script>
        // Prevent back navigation to payment processing page
        if (window.history && window.history.pushState) {
            // Replace current history state
            window.history.pushState(null, null, window.location.href);
            
            // Listen for back button
            window.onpopstate = function() {
                // Push state again to prevent going back
                window.history.pushState(null, null, window.location.href);
                // Optionally redirect to wallet
                window.location.href = 'wallet.php';
            };
        }
        
        // Disable browser cache for this page
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };
    </script>
</body>
</html>
