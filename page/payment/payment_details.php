<?php
session_start();
require_once '../config.php';

// Check if user is logged in as client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../login.php');
    exit();
}

// Get order details from session or URL
$gigId = isset($_GET['gig_id']) ? intval($_GET['gig_id']) : 0;
$rushDelivery = isset($_GET['rush']) && $_GET['rush'] == '1';

if (!$gigId) {
    header('Location: ../gig/browse_gigs.php');
    exit();
}

// Database connection
if (!function_exists('getPDOConnection')) {
    function getPDOConnection(): PDO {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            return new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Database connection error: ' . $e->getMessage());
        }
    }
}

$pdo = getPDOConnection();

// Fetch gig and freelancer details
try {
    $sql = "SELECT g.*, 
                   f.FreelancerID, f.FirstName, f.LastName, f.Email as FreelancerEmail,
                   c.ClientID, c.CompanyName, c.Email as ClientEmail
            FROM gig g
            INNER JOIN freelancer f ON g.FreelancerID = f.FreelancerID
            CROSS JOIN client c
            WHERE g.GigID = :gigId AND c.ClientID = :clientId AND g.Status = 'active'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':gigId' => $gigId,
        ':clientId' => $_SESSION['user_id']
    ]);
    $orderData = $stmt->fetch();
    
    if (!$orderData) {
        $_SESSION['error'] = 'Gig not found or unavailable.';
        header('Location: ../gig/browse_gigs.php');
        exit();
    }
} catch (PDOException $e) {
    error_log('[payment_details] Fetch failed: ' . $e->getMessage());
    die('Database error');
}

// Fetch client wallet balance
try {
    $sql = "SELECT Balance FROM wallet WHERE UserID = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':userId' => $_SESSION['user_id']]);
    $wallet = $stmt->fetch();
    $walletBalance = $wallet ? floatval($wallet['Balance']) : 0;
} catch (PDOException $e) {
    $walletBalance = 0;
}

// Calculate pricing
$basePrice = floatval($orderData['Price']);
$rushFee = ($rushDelivery && !empty($orderData['RushDeliveryPrice'])) ? floatval($orderData['RushDeliveryPrice']) : 0;
$totalAmount = $basePrice + $rushFee;
$deliveryTime = $rushDelivery && !empty($orderData['RushDelivery']) ? intval($orderData['RushDelivery']) : intval($orderData['DeliveryTime']);

$_title = 'Payment Details - WorkSnyc Platform';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        .payment-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }

        .page-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .payment-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        /* Left Side - Agreement Preview */
        .agreement-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .agreement-section h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 20px 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .agreement-content {
            background: #f8fafc;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 25px;
            max-height: 500px;
            overflow-y: auto;
        }

        .agreement-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        .agreement-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 8px 0;
        }

        .agreement-header p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        .agreement-parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .party-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .party-label {
            font-size: 0.75rem;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .party-name {
            font-size: 1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .party-email {
            font-size: 0.85rem;
            color: #666;
        }

        .agreement-details {
            margin-bottom: 25px;
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
            font-weight: 600;
            color: #666;
            font-size: 0.9rem;
        }

        .detail-value {
            font-weight: 700;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .agreement-terms {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
        }

        .agreement-terms h4 {
            font-size: 1rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 15px 0;
        }

        .agreement-terms ul {
            margin: 0;
            padding-left: 20px;
        }

        .agreement-terms li {
            color: #555;
            font-size: 0.85rem;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .agreement-note {
            background: #fff9e6;
            border: 1px solid #ffe066;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            gap: 12px;
            align-items: start;
        }

        .agreement-note i {
            color: #ffa500;
            font-size: 1.2rem;
            margin-top: 2px;
        }

        .agreement-note-text {
            flex: 1;
            font-size: 0.85rem;
            color: #666;
            line-height: 1.5;
        }

        /* Right Side - Order Summary */
        .order-summary-section {
            position: sticky;
            top: 20px;
        }

        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .summary-card h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 20px 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .gig-info {
            margin-bottom: 20px;
        }

        .gig-title {
            font-size: 1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .gig-seller {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .seller-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgb(159, 232, 112);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
        }

        .seller-name {
            font-size: 0.9rem;
            color: #666;
        }

        .price-breakdown {
            margin-bottom: 20px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .price-label {
            color: #666;
        }

        .price-value {
            font-weight: 600;
            color: #2c3e50;
        }

        .price-total {
            border-top: 2px solid #e9ecef;
            padding-top: 15px;
            margin-top: 15px;
        }

        .price-total .price-label {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .price-total .price-value {
            font-weight: 700;
            color: rgb(159, 232, 112);
            font-size: 1.3rem;
        }

        .wallet-info {
            background: #f8fafc;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .wallet-balance {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .wallet-label {
            font-size: 0.9rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .wallet-label i {
            color: rgb(159, 232, 112);
        }

        .wallet-amount {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .wallet-sufficient {
            color: rgb(159, 232, 112);
        }

        .wallet-insufficient {
            color: #dc3545;
        }

        .wallet-status {
            font-size: 0.8rem;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
            font-weight: 600;
        }

        .wallet-status.sufficient {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .wallet-status.insufficient {
            background: #ffebee;
            color: #c62828;
        }

        .topup-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgb(159, 232, 112);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 8px;
        }

        .topup-link:hover {
            text-decoration: underline;
        }

        .consent-checkbox {
            background: #f8fafc;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .consent-checkbox label {
            display: flex;
            align-items: start;
            gap: 12px;
            cursor: pointer;
        }

        .consent-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            cursor: pointer;
            accent-color: rgb(159, 232, 112);
            flex-shrink: 0;
        }

        .consent-text {
            font-size: 0.85rem;
            color: #555;
            line-height: 1.5;
        }

        .payment-btn {
            width: 100%;
            padding: 16px;
            background: rgb(159, 232, 112);
            color: #2c3e50;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .payment-btn:hover:not(:disabled) {
            background: rgb(140, 210, 90);
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
            transform: translateY(-2px);
        }

        .payment-btn:disabled {
            background: #e9ecef;
            color: #999;
            cursor: not-allowed;
            transform: none;
        }

        .security-note {
            text-align: center;
            color: #999;
            font-size: 0.8rem;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .security-note i {
            color: rgb(159, 232, 112);
        }

        @media (max-width: 1024px) {
            .payment-layout {
                grid-template-columns: 1fr;
            }

            .order-summary-section {
                position: static;
                order: -1;
            }

            .agreement-content {
                max-height: none;
            }
        }

        @media (max-width: 768px) {
            .payment-container {
                padding: 0 15px;
                margin: 20px auto;
            }

            .agreement-section,
            .summary-card {
                padding: 20px;
            }

            .agreement-parties {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php require_once '../../_head.php'; ?>

    <div class="payment-container">
        <div class="page-header">
            <h1>Review & Pay</h1>
            <p>Review the agreement and complete your order</p>
        </div>

        <div class="payment-layout">
            <!-- Left Side: Agreement Preview -->
            <div class="agreement-section">
                <h2><i class="fas fa-file-contract"></i> Service Agreement Preview</h2>
                
                <div class="agreement-content">
                    <div class="agreement-header">
                        <h3>Service Agreement</h3>
                        <p>Agreement Date: <?= date('F d, Y') ?></p>
                    </div>

                    <div class="agreement-parties">
                        <div class="party-info">
                            <div class="party-label">Client</div>
                            <div class="party-name"><?= htmlspecialchars($orderData['CompanyName']) ?></div>
                            <div class="party-email"><?= htmlspecialchars($orderData['ClientEmail']) ?></div>
                        </div>
                        <div class="party-info">
                            <div class="party-label">Freelancer</div>
                            <div class="party-name"><?= htmlspecialchars($orderData['FirstName'] . ' ' . $orderData['LastName']) ?></div>
                            <div class="party-email"><?= htmlspecialchars($orderData['FreelancerEmail']) ?></div>
                        </div>
                    </div>

                    <div class="agreement-details">
                        <div class="detail-row">
                            <span class="detail-label">Service Title</span>
                            <span class="detail-value"><?= htmlspecialchars($orderData['Title']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Service Amount</span>
                            <span class="detail-value">RM <?= number_format($basePrice, 2) ?></span>
                        </div>
                        <?php if ($rushDelivery && $rushFee > 0): ?>
                        <div class="detail-row">
                            <span class="detail-label">Rush Delivery Fee</span>
                            <span class="detail-value">RM <?= number_format($rushFee, 2) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <span class="detail-label">Delivery Time</span>
                            <span class="detail-value"><?= $deliveryTime ?> day(s)</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Revisions Included</span>
                            <span class="detail-value"><?= htmlspecialchars($orderData['RevisionCount']) ?></span>
                        </div>
                    </div>

                    <div class="agreement-terms">
                        <h4>Terms & Conditions</h4>
                        <ul>
                            <li>The freelancer agrees to deliver the service as described within <?= $deliveryTime ?> day(s) from the order date.</li>
                            <li>The client agrees to pay the total amount of RM <?= number_format($totalAmount, 2) ?> upon order confirmation.</li>
                            <li>The service includes <?= htmlspecialchars($orderData['RevisionCount']) ?> revision(s) as specified.</li>
                            <li>Payment will be held in escrow and released to the freelancer upon successful delivery and client approval.</li>
                            <li>Both parties agree to communicate professionally and resolve any disputes amicably.</li>
                            <li>Cancellation policy: Orders can be cancelled within 24 hours for a full refund. After work has begun, cancellation terms will be discussed between both parties.</li>
                        </ul>
                    </div>

                    <div class="agreement-note">
                        <i class="fas fa-info-circle"></i>
                        <div class="agreement-note-text">
                            By proceeding with the payment, you agree to the terms and conditions outlined in this agreement. A formal agreement document will be generated and signed digitally by both parties after payment confirmation.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Order Summary & Payment -->
            <div class="order-summary-section">
                <div class="summary-card">
                    <h3>Order Summary</h3>
                    
                    <div class="gig-info">
                        <div class="gig-title"><?= htmlspecialchars($orderData['Title']) ?></div>
                        <div class="gig-seller">
                            <div class="seller-avatar">
                                <?= strtoupper(substr($orderData['FirstName'], 0, 1) . substr($orderData['LastName'], 0, 1)) ?>
                            </div>
                            <span class="seller-name">by <?= htmlspecialchars($orderData['FirstName'] . ' ' . $orderData['LastName']) ?></span>
                        </div>
                    </div>

                    <div class="price-breakdown">
                        <div class="price-row">
                            <span class="price-label">Gig Price</span>
                            <span class="price-value">RM <?= number_format($basePrice, 2) ?></span>
                        </div>
                        <?php if ($rushDelivery && $rushFee > 0): ?>
                        <div class="price-row">
                            <span class="price-label"><i class="fas fa-bolt"></i> Rush Delivery</span>
                            <span class="price-value">RM <?= number_format($rushFee, 2) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="price-row">
                            <span class="price-label">Delivery</span>
                            <span class="price-value"><?= $deliveryTime ?> day(s)</span>
                        </div>
                        <div class="price-row price-total">
                            <span class="price-label">Total</span>
                            <span class="price-value">RM <?= number_format($totalAmount, 2) ?></span>
                        </div>
                    </div>

                    <div class="wallet-info">
                        <div class="wallet-balance">
                            <span class="wallet-label">
                                <i class="fas fa-wallet"></i>
                                Wallet Balance
                            </span>
                            <span class="wallet-amount <?= $walletBalance >= $totalAmount ? 'wallet-sufficient' : 'wallet-insufficient' ?>">
                                RM <?= number_format($walletBalance, 2) ?>
                            </span>
                        </div>
                        <?php if ($walletBalance >= $totalAmount): ?>
                            <div class="wallet-status sufficient">
                                <i class="fas fa-check-circle"></i> Sufficient balance
                            </div>
                        <?php else: ?>
                            <div class="wallet-status insufficient">
                                <i class="fas fa-exclamation-circle"></i> Insufficient balance - Please top up
                            </div>
                            <a href="wallet.php" class="topup-link">
                                <i class="fas fa-plus-circle"></i>
                                Top up wallet
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="consent-checkbox">
                        <label for="agreeTerms">
                            <input type="checkbox" id="agreeTerms" onchange="togglePaymentButton()">
                            <span class="consent-text">
                                I have reviewed and agree to the service agreement terms and conditions outlined above.
                            </span>
                        </label>
                    </div>

                    <button class="payment-btn" id="paymentBtn" disabled onclick="processPayment()">
                        <i class="fas fa-lock"></i>
                        Pay with Wallet
                    </button>

                    <div class="security-note">
                        <i class="fas fa-shield-alt"></i>
                        Secure payment · Your money is protected
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../_foot.php'; ?>

    <script>
        const totalAmount = <?= $totalAmount ?>;
        const walletBalance = <?= $walletBalance ?>;

        function togglePaymentButton() {
            const checkbox = document.getElementById('agreeTerms');
            const paymentBtn = document.getElementById('paymentBtn');
            
            if (checkbox.checked && walletBalance >= totalAmount) {
                paymentBtn.disabled = false;
            } else {
                paymentBtn.disabled = true;
            }
        }

        function processPayment() {
            if (!document.getElementById('agreeTerms').checked) {
                alert('Please agree to the terms and conditions first.');
                return;
            }

            if (walletBalance < totalAmount) {
                alert('Insufficient wallet balance. Please top up your wallet.');
                window.location.href = 'wallet.php';
                return;
            }

            // Show loading state
            const paymentBtn = document.getElementById('paymentBtn');
            paymentBtn.disabled = true;
            paymentBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            // TODO: Implement actual payment processing
            // For now, redirect to a success page or process the order
            setTimeout(() => {
                alert('Payment will be processed. Redirecting...');
                // window.location.href = 'payment_success.php?gig_id=<?= $gigId ?>&rush=<?= $rushDelivery ? '1' : '0' ?>';
            }, 1500);
        }
    </script>
</body>
</html>
