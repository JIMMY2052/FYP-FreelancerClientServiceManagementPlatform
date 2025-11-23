<?php

require_once '../../vendor/autoload.php';

$stripe_secret_key = "sk_test_51SVTUkRTrldFvsR7kitkuJqbCONvPJmT8oZwGtnsaxNiWuLH8SsIRTsfO6WkLsF53B0XzXdvduGhYuZWgN2Fm64r00nawN4i0S";
\Stripe\Stripe::setApiKey($stripe_secret_key);

$checkout_session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'myr',
            'product_data' => [
                'name' => 'Example Product',
            ],
            'unit_amount' => 2000,
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => 'http://localhost/success.php',
    'cancel_url' => 'https://yourdomain.com/cancel.html',
]);

http_response_code(303);
header("Location: " . $checkout_session->url);
?>