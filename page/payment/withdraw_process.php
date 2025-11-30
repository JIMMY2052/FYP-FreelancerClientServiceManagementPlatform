<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['client', 'freelancer'])) {
    header('Location: ../login.php');
    exit();
}

// Check if form data is provided
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: wallet.php');
    exit();
}

// Validate form inputs
$amount = floatval($_POST['amount'] ?? 0);
$bank_name = trim($_POST['bank_name'] ?? '');
$account_number = trim($_POST['account_number_raw'] ?? ''); // Use raw value without spaces
$account_holder = trim($_POST['account_holder'] ?? '');

// Validate amount
if ($amount < 10) {
    $_SESSION['error'] = 'Minimum withdrawal amount is RM 10.00';
    header('Location: wallet.php');
    exit();
}

if ($amount > 50000) {
    $_SESSION['error'] = 'Maximum withdrawal amount is RM 50,000.00';
    header('Location: wallet.php');
    exit();
}

// Validate bank details
if (empty($bank_name) || empty($account_number) || empty($account_holder)) {
    $_SESSION['error'] = 'Please fill in all bank account details.';
    header('Location: wallet.php');
    exit();
}

// Validate account number (must be exactly 16 digits)
if (!preg_match('/^[0-9]{16}$/', $account_number)) {
    $_SESSION['error'] = 'Account number must be exactly 16 digits.';
    header('Location: wallet.php');
    exit();
}

require_once '../config.php';
require_once '../../vendor/autoload.php';

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

try {
    // Start transaction
    $conn->begin_transaction();

    // Get wallet information
    $sql = "SELECT WalletID, Balance FROM wallet WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Wallet not found.');
    }

    $wallet = $result->fetch_assoc();
    $wallet_id = $wallet['WalletID'];
    $current_balance = floatval($wallet['Balance']);
    $stmt->close();

    // For freelancers, calculate reserved balance from ongoing agreements
    $reserved_balance = 0;
    if ($_SESSION['user_type'] === 'freelancer') {
        // Only count from agreements to avoid double counting
        // (Agreements are created when job applications are accepted)
        $sql = "SELECT COALESCE(SUM(PaymentAmount), 0) as reserved_amount 
                FROM agreement 
                WHERE FreelancerID = ? 
                AND Status IN ('pending', 'ongoing', 'signed')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $agreement_data = $result->fetch_assoc();
        $reserved_balance = floatval($agreement_data['reserved_amount']);
        $stmt->close();
    }

    // Calculate available balance (current balance minus reserved amount)
    $available_balance = $current_balance - $reserved_balance;

    // Check if user has sufficient available balance
    if ($available_balance < $amount) {
        $error_msg = 'Insufficient available balance. ';
        $error_msg .= 'Current Balance: RM ' . number_format($current_balance, 2);
        if ($reserved_balance > 0) {
            $error_msg .= ' | Reserved for ongoing jobs: RM ' . number_format($reserved_balance, 2);
            $error_msg .= ' | Available for withdrawal: RM ' . number_format($available_balance, 2);
        }
        throw new Exception($error_msg);
    }

    // Stripe configuration
    $stripe_secret_key = "sk_test_51SVTUkRTrldFvsR7kitkuJqbCONvPJmT8oZwGtnsaxNiWuLH8SsIRTsfO6WkLsF53B0XzXdvduGhYuZWgN2Fm64r00nawN4i0S";
    \Stripe\Stripe::setApiKey($stripe_secret_key);

    // In a production environment, you would create a Stripe Transfer or Payout
    // For testing purposes, we'll simulate the withdrawal process
    
    // Note: In production, you'd typically need to:
    // 1. Verify the bank account with Stripe
    // 2. Create a Stripe Transfer or use Stripe Connect
    // 3. Handle webhook confirmations
    
    // For now, we'll process this as a pending withdrawal
    $new_balance = $current_balance - $amount;

    // Update wallet balance
    $sql = "UPDATE wallet SET Balance = ? WHERE WalletID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('di', $new_balance, $wallet_id);
    $stmt->execute();
    $stmt->close();

    // Record withdrawal transaction
    $description = "Withdrawal to {$bank_name} - {$account_holder} (****" . substr($account_number, -4) . ")";
    $transaction_type = "withdrawal";
    $status = "processing"; // Would be "processing" until confirmed by Stripe

    $sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Description, Status) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isdss', $wallet_id, $transaction_type, $amount, $description, $status);
    $stmt->execute();
    $transaction_id = $conn->insert_id;
    $stmt->close();

    // Store withdrawal request details (you might want to create a separate table for this)
    // For now, we'll store it in session for the success page
    $_SESSION['withdrawal_details'] = [
        'amount' => $amount,
        'bank_name' => $bank_name,
        'account_number' => $account_number,
        'account_holder' => $account_holder,
        'transaction_id' => $transaction_id
    ];

    // Commit transaction
    $conn->commit();
    $conn->close();

    // Redirect to success page
    header('Location: withdraw_success.php');
    exit();

} catch (Exception $e) {
    // Rollback on error
    if (isset($conn)) {
        $conn->rollback();
        $conn->close();
    }
    
    $_SESSION['error'] = $e->getMessage();
    error_log('Withdrawal error: ' . $e->getMessage());
    header('Location: wallet.php');
    exit();
}
?>
