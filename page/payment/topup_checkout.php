<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['client', 'freelancer'])) {
    header('Location: ../login.php');
    exit();
}

// Check if amount is provided
if (!isset($_POST['amount']) || empty($_POST['amount'])) {
    $_SESSION['error'] = 'Please enter a valid amount.';
    header('Location: wallet.php');
    exit();
}

$amount = floatval($_POST['amount']);
$return_to = isset($_POST['return_to']) ? $_POST['return_to'] : (isset($_GET['return_to']) ? $_GET['return_to'] : 'wallet');
$gig_id = isset($_POST['gig_id']) ? $_POST['gig_id'] : (isset($_GET['gig_id']) ? $_GET['gig_id'] : '');
$rush = isset($_POST['rush']) ? $_POST['rush'] : (isset($_GET['rush']) ? $_GET['rush'] : '');
$extra_revisions = isset($_POST['extra_revisions']) ? $_POST['extra_revisions'] : (isset($_GET['extra_revisions']) ? $_GET['extra_revisions'] : '');

// Validate amount
if ($amount < 10) {
    $_SESSION['error'] = 'Minimum top-up amount is RM 10.00';
    header('Location: wallet.php');
    exit();
}

if ($amount > 10000) {
    $_SESSION['error'] = 'Maximum top-up amount is RM 10,000.00';
    header('Location: wallet.php');
    exit();
}

// Store amount and return info in session for success callback
$_SESSION['topup_amount'] = $amount;
$_SESSION['topup_user_id'] = $_SESSION['user_id'];
$_SESSION['topup_return_to'] = $return_to;
$_SESSION['topup_gig_id'] = $gig_id;
$_SESSION['topup_rush'] = $rush;
$_SESSION['topup_extra_revisions'] = $extra_revisions;

require_once '../../vendor/autoload.php';
require_once '../config.php';

// Stripe configuration
$stripe_secret_key = "sk_test_51SVTUkRTrldFvsR7kitkuJqbCONvPJmT8oZwGtnsaxNiWuLH8SsIRTsfO6WkLsF53B0XzXdvduGhYuZWgN2Fm64r00nawN4i0S";
\Stripe\Stripe::setApiKey($stripe_secret_key);

try {
    // Create Stripe Checkout Session
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'myr',
                'product_data' => [
                    'name' => 'Wallet Top Up',
                    'description' => 'Add funds to your WorkSnyc wallet',
                ],
                'unit_amount' => intval($amount * 100), // Convert to cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost:8000/page/payment/topup_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost:8000/page/payment/wallet.php?canceled=1',
        'metadata' => [
            'user_id' => $_SESSION['user_id'],
            'user_type' => $_SESSION['user_type'],
            'amount' => $amount,
            'transaction_type' => 'wallet_topup'
        ]
    ]);

    // Redirect to Stripe Checkout
    http_response_code(303);
    header("Location: " . $checkout_session->url);
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Payment gateway error: ' . $e->getMessage();
    header('Location: wallet.php');
    exit();
}
?>
