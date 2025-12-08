<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['client', 'freelancer'])) {
    header('Location: ../login.php');
    exit();
}

// Check if withdrawal details exist
if (!isset($_SESSION['withdrawal_details'])) {
    $_SESSION['error'] = 'No withdrawal request found.';
    header('Location: wallet.php');
    exit();
}

$details = $_SESSION['withdrawal_details'];
$amount = $details['amount'];
$bank_name = $details['bank_name'];
$account_number = $details['account_number'];
$account_holder = $details['account_holder'];
$transaction_id = $details['transaction_id'];

// Clear session data
unset($_SESSION['withdrawal_details']);

$_title = 'Withdrawal Request - WorkSnyc Platform';
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
            max-width: 600px;
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
            background: rgba(59, 130, 246, 0.1);
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
        
        .success-icon {
            background: rgba(34, 197, 94, 0.1);
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

        .details-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }

        .details-box h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 15px 0;
            text-align: center;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #666;
            font-size: 0.9rem;
        }

        .detail-value {
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .info-notice {
            background: rgba(59, 130, 246, 0.1);
            border-left: 4px solid #3b82f6;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }

        .info-notice p {
            margin: 0;
            font-size: 0.9rem;
            color: #2c3e50;
        }

        .info-notice i {
            color: #3b82f6;
            margin-right: 8px;
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

        @media (max-width: 768px) {
            .success-container {
                padding: 40px 30px;
            }

            .amount {
                font-size: 2rem;
            }

            .btn-wallet,
            .btn-secondary {
                display: block;
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1>Withdrawal Successful</h1>
        <div class="amount">RM <?= number_format($amount, 2) ?></div>
        <p>Your withdrawal has been completed successfully.</p>

        <div class="details-box">
            <h3>Withdrawal Details</h3>
            <div class="detail-row">
                <span class="detail-label">Amount</span>
                <span class="detail-value">RM <?= number_format($amount, 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Bank</span>
                <span class="detail-value"><?= htmlspecialchars($bank_name) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Account Number</span>
                <span class="detail-value">****<?= substr($account_number, -4) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Account Holder</span>
                <span class="detail-value"><?= htmlspecialchars($account_holder) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Transaction ID</span>
                <span class="detail-value">#<?= $transaction_id ?></span>
            </div>
        </div>

        <a href="wallet.php" class="btn-wallet">
            <i class="fas fa-wallet"></i> View Wallet
        </a>
        <a href="<?= $_SESSION['user_type'] === 'client' ? '/client_home.php' : '/freelancer_home.php' ?>" class="btn-secondary">
            <i class="fas fa-home"></i> Go Home
        </a>
    </div>
</body>
</html>
