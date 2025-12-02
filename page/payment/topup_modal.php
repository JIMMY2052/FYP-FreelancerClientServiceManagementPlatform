<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['client', 'freelancer'])) {
    header('Location: ../login.php');
    exit();
}

$return_to = isset($_GET['return_to']) ? $_GET['return_to'] : 'wallet';
$gig_id = isset($_GET['gig_id']) ? $_GET['gig_id'] : '';
$rush = isset($_GET['rush']) ? $_GET['rush'] : '';
$extra_revisions = isset($_GET['extra_revisions']) ? $_GET['extra_revisions'] : '';

$_title = 'Top Up Wallet - WorkSnyc Platform';
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .topup-container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .topup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .topup-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgb(159, 232, 112) 0%, rgb(140, 210, 90) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .topup-icon i {
            font-size: 2rem;
            color: white;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }

        p {
            color: #666;
            font-size: 0.95rem;
            margin: 0;
        }

        .quick-amount-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 25px;
        }

        .quick-amount-btn {
            padding: 15px;
            background: #f8fafc;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .quick-amount-btn:hover,
        .quick-amount-btn.selected {
            border-color: rgb(159, 232, 112);
            background: rgba(159, 232, 112, 0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: rgb(159, 232, 112);
            box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, rgb(159, 232, 112) 0%, rgb(140, 210, 90) 100%);
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

        .btn-submit:hover {
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.4);
            transform: translateY(-2px);
        }

        .btn-cancel {
            width: 100%;
            padding: 14px;
            background: #f8fafc;
            color: #666;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 12px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-cancel:hover {
            background: white;
            border-color: #ddd;
        }

        .info-note {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 25px;
            font-size: 0.85rem;
            color: #0369a1;
            display: flex;
            gap: 10px;
            align-items: start;
        }

        .info-note i {
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="topup-container">
        <div class="topup-header">
            <div class="topup-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <h1>Top Up Wallet</h1>
            <p>Add funds to your wallet to continue</p>
        </div>

        <div class="info-note">
            <i class="fas fa-info-circle"></i>
            <div>You need to add funds to your wallet to proceed with this payment. Choose an amount below or enter a custom amount.</div>
        </div>

        <form method="POST" action="topup_checkout.php">
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($return_to) ?>">
            <input type="hidden" name="gig_id" value="<?= htmlspecialchars($gig_id) ?>">
            <input type="hidden" name="rush" value="<?= htmlspecialchars($rush) ?>">
            <input type="hidden" name="extra_revisions" value="<?= htmlspecialchars($extra_revisions) ?>">

            <div class="quick-amount-grid">
                <button type="button" class="quick-amount-btn" onclick="selectAmount(50)">RM 50</button>
                <button type="button" class="quick-amount-btn" onclick="selectAmount(100)">RM 100</button>
                <button type="button" class="quick-amount-btn" onclick="selectAmount(200)">RM 200</button>
                <button type="button" class="quick-amount-btn" onclick="selectAmount(500)">RM 500</button>
                <button type="button" class="quick-amount-btn" onclick="selectAmount(1000)">RM 1,000</button>
                <button type="button" class="quick-amount-btn" onclick="document.getElementById('amount').focus()">Custom</button>
            </div>

            <div class="form-group">
                <label for="amount">Amount (RM)</label>
                <input type="number" id="amount" name="amount" min="10" max="10000" step="0.01" required placeholder="Enter amount" value="">
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-credit-card"></i>
                Proceed to Payment
            </button>
        </form>

        <a href="<?= $return_to === 'payment_details' && !empty($gig_id) ? 'payment_details.php?gig_id=' . htmlspecialchars($gig_id) . '&rush=' . htmlspecialchars($rush) . (!empty($extra_revisions) ? '&extra_revisions=' . htmlspecialchars($extra_revisions) : '') : 'wallet.php' ?>" class="btn-cancel">
            Cancel
        </a>
    </div>

    <script>
        function selectAmount(amount) {
            document.getElementById('amount').value = amount;
            
            // Update button states
            document.querySelectorAll('.quick-amount-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            event.target.classList.add('selected');
        }
    </script>
</body>
</html>
