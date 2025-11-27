<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: ../login.php');
    exit();
}

$_title = 'My Wallet - WorkSnyc Platform';
require_once '../config.php';

$conn = getDBConnection();
$user_type = $_SESSION['user_type'];
$user_id = $_SESSION['user_id'];

// Get wallet information
$wallet = null;
$wallet_id = null;

$sql = "SELECT * FROM wallet WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $wallet = $result->fetch_assoc();
    $wallet_id = $wallet['WalletID'];
} else {
    // Create wallet if doesn't exist
    $sql = "INSERT INTO wallet (UserID, Balance) VALUES (?, 0.00)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $wallet_id = $conn->insert_id;
    
    // Fetch the newly created wallet
    $sql = "SELECT * FROM wallet WHERE WalletID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $wallet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $wallet = $result->fetch_assoc();
}
$stmt->close();

// Get transaction history
$transactions = [];
$sql = "SELECT * FROM wallet_transactions WHERE WalletID = ? ORDER BY CreatedAt DESC LIMIT 50";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $wallet_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();
$conn->close();
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
        }

        .wallet-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .wallet-header {
            margin-bottom: 30px;
        }

        .wallet-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }

        .wallet-header p {
            color: #666;
            font-size: 1rem;
        }

        .wallet-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .wallet-card {
            background: linear-gradient(135deg, rgb(159, 232, 112) 0%, rgb(140, 210, 90) 100%);
            border-radius: 16px;
            padding: 30px;
            color: white;
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
        }

        .wallet-card-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .wallet-balance {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .wallet-actions {
            display: flex;
            gap: 10px;
        }

        .btn-topup {
            padding: 12px 24px;
            background: white;
            color: #2c3e50;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-topup:hover {
            background: #f8f9fa;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .btn-withdraw {
            padding: 12px 24px;
            background: transparent;
            color: white;
            border: 2px solid white;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-withdraw:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .quick-actions-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .quick-actions-card h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 20px 0;
        }

        .quick-amount-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
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

        .quick-amount-btn:hover {
            border-color: rgb(159, 232, 112);
            background: rgba(159, 232, 112, 0.1);
        }

        .transactions-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .transactions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .transactions-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
        }

        .filter-tab {
            padding: 8px 16px;
            background: transparent;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-tab.active {
            background: rgb(159, 232, 112);
            color: white;
            border-color: rgb(159, 232, 112);
        }

        .transaction-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .transaction-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .transaction-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .transaction-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .transaction-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .transaction-icon.credit {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .transaction-icon.debit {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .transaction-info h4 {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 5px 0;
        }

        .transaction-info p {
            font-size: 0.85rem;
            color: #999;
            margin: 0;
        }

        .transaction-amount {
            text-align: right;
        }

        .transaction-amount .amount {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .transaction-amount .amount.credit {
            color: #22c55e;
        }

        .transaction-amount .amount.debit {
            color: #ef4444;
        }

        .transaction-amount .status {
            font-size: 0.85rem;
            color: #999;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin: 0;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            margin-bottom: 25px;
        }

        .modal-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }

        .modal-header p {
            color: #666;
            margin: 0;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #999;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: rgb(159, 232, 112);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: rgb(159, 232, 112);
            color: #2c3e50;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: rgb(140, 210, 90);
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
        }

        @media (max-width: 768px) {
            .wallet-grid {
                grid-template-columns: 1fr;
            }

            .wallet-balance {
                font-size: 2rem;
            }

            .quick-amount-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filter-tabs {
                flex-wrap: wrap;
            }

            .transaction-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .transaction-amount {
                text-align: left;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php require_once '../../_head.php'; ?>

    <div class="wallet-container">
        <div class="wallet-header">
            <h1><i class="fas fa-wallet"></i> My Wallet</h1>
            <p>Manage your balance and transactions</p>
        </div>

        <div class="wallet-grid">
            <!-- Wallet Balance Card -->
            <div class="wallet-card">
                <div class="wallet-card-label">Available Balance</div>
                <div class="wallet-balance">RM <?= number_format($wallet['Balance'], 2) ?></div>
                <div class="wallet-actions">
                    <button class="btn-withdraw" onclick="openWithdrawModal()">
                        <i class="fas fa-arrow-up"></i>
                        Withdraw
                    </button>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="quick-actions-card">
                <h3>Quick Top Up</h3>
                <div class="quick-amount-grid">
                    <button class="quick-amount-btn" onclick="quickTopup(50)">RM 50</button>
                    <button class="quick-amount-btn" onclick="quickTopup(100)">RM 100</button>
                    <button class="quick-amount-btn" onclick="quickTopup(200)">RM 200</button>
                    <button class="quick-amount-btn" onclick="quickTopup(500)">RM 500</button>
                    <button class="quick-amount-btn" onclick="quickTopup(1000)">RM 1,000</button>
                    <button class="quick-amount-btn" onclick="openTopupModal()">Custom</button>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="transactions-section">
            <div class="transactions-header">
                <h2>Transaction History</h2>
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all">All</button>
                    <button class="filter-tab" data-filter="credit">Credit</button>
                    <button class="filter-tab" data-filter="debit">Debit</button>
                </div>
            </div>

            <div class="transaction-list">
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <?php 
                            $isCredit = in_array($transaction['Type'], ['topup', 'refund', 'earning']);
                            $icon = $isCredit ? 'fa-arrow-down' : 'fa-arrow-up';
                            $typeClass = $isCredit ? 'credit' : 'debit';
                            $sign = $isCredit ? '+' : '-';
                        ?>
                        <div class="transaction-item" data-type="<?= $typeClass ?>">
                            <div class="transaction-left">
                                <div class="transaction-icon <?= $typeClass ?>">
                                    <i class="fas <?= $icon ?>"></i>
                                </div>
                                <div class="transaction-info">
                                    <h4><?= htmlspecialchars($transaction['Description']) ?></h4>
                                    <p><?= date('M j, Y \a\t g:i A', strtotime($transaction['CreatedAt'])) ?></p>
                                </div>
                            </div>
                            <div class="transaction-amount">
                                <div class="amount <?= $typeClass ?>">
                                    <?= $sign ?>RM <?= number_format($transaction['Amount'], 2) ?>
                                </div>
                                <div class="status"><?= ucfirst($transaction['Status']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <p>No transactions yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top Up Modal -->
    <div class="modal" id="topupModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Top Up Wallet</h3>
                <p>Add funds to your wallet using Stripe payment gateway</p>
            </div>
            <form id="topupForm" method="POST" action="topup_checkout.php">
                <div class="form-group">
                    <label for="topup_amount">Amount (RM)</label>
                    <input type="number" id="topup_amount" name="amount" min="10" step="0.01" required placeholder="Enter amount">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-credit-card"></i>
                    Proceed to Payment
                </button>
            </form>
            <button class="modal-close" onclick="closeTopupModal()">&times;</button>
        </div>
    </div>

    <!-- Withdraw Modal -->
    <div class="modal" id="withdrawModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Withdraw Funds</h3>
                <p>Withdraw funds from your wallet to your bank account</p>
            </div>
            <form id="withdrawForm" method="POST" action="withdraw_process.php">
                <div class="form-group">
                    <label for="withdraw_amount">Amount (RM)</label>
                    <input type="number" id="withdraw_amount" name="amount" min="10" max="<?= number_format($wallet['Balance'], 2, '.', '') ?>" step="0.01" required placeholder="Enter amount">
                    <div style="font-size: 0.85rem; color: #666; margin-top: 5px;">
                        Available balance: RM <?= number_format($wallet['Balance'], 2) ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="bank_name">Bank Name</label>
                    <select id="bank_name" name="bank_name" required>
                        <option value="" disabled selected>Select your bank</option>
                        <option value="Maybank">Maybank</option>
                        <option value="CIMB Bank">CIMB Bank</option>
                        <option value="Public Bank">Public Bank</option>
                        <option value="RHB Bank">RHB Bank</option>
                        <option value="Hong Leong Bank">Hong Leong Bank</option>
                        <option value="AmBank">AmBank</option>
                        <option value="Bank Islam">Bank Islam</option>
                        <option value="OCBC Bank">OCBC Bank</option>
                        <option value="HSBC Bank">HSBC Bank</option>
                        <option value="Standard Chartered">Standard Chartered</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="account_number">Account Number</label>
                    <input type="text" id="account_number" name="account_number" required placeholder="Enter your account number" pattern="[0-9]+" title="Please enter numbers only">
                </div>
                <div class="form-group">
                    <label for="account_holder">Account Holder Name</label>
                    <input type="text" id="account_holder" name="account_holder" required placeholder="As per bank account">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-university"></i>
                    Request Withdrawal
                </button>
            </form>
            <button class="modal-close" onclick="closeWithdrawModal()">&times;</button>
        </div>
    </div>

    <?php require_once '../../_foot.php'; ?>

    <script>
        // Filter transactions
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;
                const transactions = document.querySelectorAll('.transaction-item');

                transactions.forEach(item => {
                    if (filter === 'all') {
                        item.style.display = 'flex';
                    } else {
                        if (item.dataset.type === filter) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    }
                });
            });
        });

        // Modal functions
        function openTopupModal() {
            document.getElementById('topupModal').classList.add('active');
        }

        function closeTopupModal() {
            document.getElementById('topupModal').classList.remove('active');
        }

        function quickTopup(amount) {
            document.getElementById('topup_amount').value = amount;
            openTopupModal();
        }

        function openWithdrawModal() {
            document.getElementById('withdrawModal').classList.add('active');
        }

        function closeWithdrawModal() {
            document.getElementById('withdrawModal').classList.remove('active');
        }

        // Close modal on outside click
        document.getElementById('topupModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTopupModal();
            }
        });

        document.getElementById('withdrawModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeWithdrawModal();
            }
        });
    </script>
</body>
</html>
