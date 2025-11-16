<?php
session_start();
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get agreement ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$agreement_id = (int)$_GET['id'];

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    die('Database connection failed');
}

// Fetch agreement with related job and application details
$query = "SELECT 
            a.AgreementID, 
            a.ApplicationID,
            a.Terms, 
            a.SignedDate, 
            a.Status,
            ap.JobID,
            ap.FreelancerID,
            ap.ProposedBudget,
            j.ClientID,
            j.Title as JobTitle,
            j.Description as JobDescription,
            j.Budget,
            j.Deadline,
            c.CompanyName,
            f.FirstName,
            f.LastName
          FROM agreement a
          JOIN application ap ON a.ApplicationID = ap.ApplicationID
          JOIN job j ON ap.JobID = j.JobID
          JOIN client c ON j.ClientID = c.ClientID
          JOIN freelancer f ON ap.FreelancerID = f.FreelancerID
          WHERE a.AgreementID = ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $agreement_id);
$stmt->execute();
$result = $stmt->get_result();
$agreement = $result->fetch_assoc();
$stmt->close();

// Check if agreement exists
if (!$agreement) {
    header('Location: ../index.php');
    exit;
}

// Verify user ownership
$is_owner = ($user_type === 'freelancer' && $agreement['FreelancerID'] == $user_id) ||
    ($user_type === 'client' && $agreement['ClientID'] == $user_id);

if (!$is_owner) {
    header('Location: ../index.php');
    exit;
}

$freelancer_name = $agreement['FirstName'] . ' ' . $agreement['LastName'];
$conn->close();

// Calculate payment breakdown (35-35-30 split)
$total_budget = $agreement['Budget'];
$payment_phases = [
    [
        'phase' => 'Phase 1: Initial Setup',
        'percentage' => 35,
        'amount' => $total_budget * 0.35,
        'due_date' => $agreement['Deadline']
    ],
    [
        'phase' => 'Phase 2: Development',
        'percentage' => 35,
        'amount' => $total_budget * 0.35,
        'due_date' => date('Y-m-d', strtotime($agreement['Deadline'] . ' - 1 month'))
    ],
    [
        'phase' => 'Phase 3: Final Delivery',
        'percentage' => 30,
        'amount' => $total_budget * 0.30,
        'due_date' => $agreement['Deadline']
    ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agreement - <?php echo htmlspecialchars($agreement['JobTitle']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .agreement-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .agreement-header {
            background: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .back-link {
            color: #0066cc;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .header-content h1 {
            font-size: 32px;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .header-subtitle {
            color: #666;
            font-size: 15px;
            line-height: 1.6;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .card {
            background: white;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-body {
            padding: 30px;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .info-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 15px;
        }

        .description-text {
            color: #666;
            line-height: 1.6;
            font-size: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            color: #999;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .info-value {
            color: #1a1a1a;
            font-size: 16px;
            font-weight: 600;
        }

        .budget-value {
            color: #0066cc;
            font-size: 28px;
        }

        .milestone-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .milestone-item {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 18px;
            border-radius: 8px;
            border-left: 4px solid #0066cc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .milestone-info h4 {
            font-size: 15px;
            color: #1a1a1a;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .milestone-date {
            font-size: 13px;
            color: #999;
        }

        .milestone-amount {
            text-align: right;
        }

        .amount-value {
            font-size: 18px;
            font-weight: 700;
            color: #0066cc;
            margin-bottom: 4px;
        }

        .amount-percent {
            font-size: 13px;
            color: #999;
        }

        .payment-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .summary-row:last-child {
            margin-bottom: 0;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 18px;
            font-weight: 700;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 25px;
        }

        .signature-box {
            text-align: center;
            border: 2px dashed #ddd;
            padding: 30px;
            border-radius: 8px;
        }

        .signature-placeholder {
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            font-size: 50px;
            margin-bottom: 20px;
        }

        .signature-placeholder.signed {
            color: #28a745;
        }

        .signature-line {
            border-top: 2px solid #333;
            margin: 20px 0;
        }

        .signature-name {
            font-size: 14px;
            color: #1a1a1a;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .signature-date-text {
            font-size: 12px;
            color: #999;
        }

        .signature-status {
            display: inline-block;
            margin-top: 12px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-signed {
            background: #d4edda;
            color: #155724;
        }

        .status-unsigned {
            background: #fff3cd;
            color: #856404;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #0066cc;
            color: white;
        }

        .btn-primary:hover {
            background: #0052a3;
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .print-link {
            background: none;
            border: 1px solid #ddd;
            color: #666;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        .print-link:hover {
            background: #f5f5f5;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 12px;
            max-width: 400px;
            text-align: center;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 12px;
        }

        .modal-message {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .agreement-header {
                padding: 25px;
            }

            .header-top {
                flex-direction: column;
                gap: 15px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .signature-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .action-buttons,
            .print-link {
                display: none;
            }

            .agreement-container {
                max-width: 100%;
            }

            .card {
                page-break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="agreement-container">
        <!-- Header -->
        <div class="agreement-header">
            <div class="header-top">
                <a href="javascript:history.back()" class="back-link">← Back</a>
            </div>
            <div class="header-content">
                <h1><?php echo htmlspecialchars($agreement['JobTitle']); ?></h1>
                <p class="header-subtitle">
                    Agreement between <strong><?php echo htmlspecialchars($agreement['CompanyName']); ?></strong> and <strong><?php echo htmlspecialchars($freelancer_name); ?></strong>
                </p>
            </div>
            <div style="text-align: right;">
                <span class="status-badge status-<?php echo strtolower($agreement['Status']); ?>">
                    <?php echo ucfirst($agreement['Status']); ?>
                </span>
            </div>
        </div>

        <!-- Project Details Card -->
        <div class="card">
            <div class="card-header">📋 Project Details</div>
            <div class="card-body">
                <div class="info-section">
                    <p class="section-title">Project Scope</p>
                    <p class="description-text"><?php echo nl2br(htmlspecialchars($agreement['JobDescription'])); ?></p>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Client</span>
                        <span class="info-value"><?php echo htmlspecialchars($agreement['CompanyName']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Freelancer</span>
                        <span class="info-value"><?php echo htmlspecialchars($freelancer_name); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Budget</span>
                        <span class="info-value budget-value">RM <?php echo number_format($agreement['Budget'], 2); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Deadline</span>
                        <span class="info-value"><?php echo date('M d, Y', strtotime($agreement['Deadline'])); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Terms Card -->
        <div class="card">
            <div class="card-header">💰 Payment Terms</div>
            <div class="card-body">
                <div class="info-section">
                    <p class="section-title">Project Phases</p>
                    <div class="milestone-list">
                        <?php foreach ($payment_phases as $phase): ?>
                            <div class="milestone-item">
                                <div class="milestone-info">
                                    <h4><?php echo $phase['phase']; ?></h4>
                                    <div class="milestone-date">Due: <?php echo date('M d, Y', strtotime($phase['due_date'])); ?></div>
                                </div>
                                <div class="milestone-amount">
                                    <div class="amount-value">RM <?php echo number_format($phase['amount'], 2); ?></div>
                                    <div class="amount-percent">(<?php echo $phase['percentage']; ?>%)</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="payment-summary">
                        <div class="summary-row">
                            <span>Payment Structure:</span>
                            <span>Milestone-based</span>
                        </div>
                        <div class="summary-row">
                            <span>Total Project Value</span>
                            <span>RM <?php echo number_format($agreement['Budget'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agreement Terms Card -->
        <div class="card">
            <div class="card-header">📄 Agreement Terms</div>
            <div class="card-body">
                <div class="description-text" style="white-space: pre-wrap;">
                    <?php echo htmlspecialchars($agreement['Terms']); ?>
                </div>
            </div>
        </div>

        <!-- Signature Section Card -->
        <div class="card">
            <div class="card-header">✍️ Signatures</div>
            <div class="card-body">
                <p style="color: #666; margin-bottom: 25px;">Please sign below to accept this agreement.</p>

                <div class="signature-grid">
                    <!-- Client Signature -->
                    <div class="signature-box">
                        <div class="signature-placeholder <?php echo $agreement['SignedDate'] ? 'signed' : ''; ?>">
                            <?php echo $agreement['SignedDate'] ? '✓' : ''; ?>
                        </div>
                        <div class="signature-line"></div>
                        <div class="signature-name"><?php echo htmlspecialchars($agreement['CompanyName']); ?></div>
                        <div class="signature-date-text"><?php echo $agreement['SignedDate'] ? 'Signed on ' . date('M d, Y', strtotime($agreement['SignedDate'])) : 'Date'; ?></div>
                        <div class="signature-status <?php echo $agreement['SignedDate'] ? 'status-signed' : 'status-unsigned'; ?>">
                            <?php echo $agreement['SignedDate'] ? '✓ SIGNED' : '○ PENDING'; ?>
                        </div>
                    </div>

                    <!-- Freelancer Signature -->
                    <div class="signature-box">
                        <div class="signature-placeholder <?php echo $agreement['SignedDate'] ? 'signed' : ''; ?>">
                            <?php echo $agreement['SignedDate'] ? '✓' : ''; ?>
                        </div>
                        <div class="signature-line"></div>
                        <div class="signature-name"><?php echo htmlspecialchars($freelancer_name); ?></div>
                        <div class="signature-date-text"><?php echo $agreement['SignedDate'] ? 'Signed on ' . date('M d, Y', strtotime($agreement['SignedDate'])) : 'Date'; ?></div>
                        <div class="signature-status <?php echo $agreement['SignedDate'] ? 'status-signed' : 'status-unsigned'; ?>">
                            <?php echo $agreement['SignedDate'] ? '✓ SIGNED' : '○ PENDING'; ?>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="print-link" onclick="window.print();">📥 Download PDF</button>
                    <?php if (!$agreement['SignedDate']): ?>
                        <button class="btn btn-secondary" onclick="openRejectModal()">Request Changes</button>
                        <button class="btn btn-primary" onclick="openSignModal()">Accept & Sign</button>
                    <?php else: ?>
                        <span style="color: #28a745; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                            ✓ Agreement Signed
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sign Modal -->
    <div id="signModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title">Confirm Signature</h3>
            <p class="modal-message">By signing this agreement, you accept all terms and conditions. This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="closeModal('signModal')">Cancel</button>
                <button class="btn btn-primary" onclick="signAgreement()">Sign Agreement</button>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title">Request Changes</h3>
            <p class="modal-message">Are you sure you want to request changes to this agreement?</p>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="closeModal('rejectModal')">Cancel</button>
                <button class="btn btn-danger" onclick="rejectAgreement()">Request Changes</button>
            </div>
        </div>
    </div>

    <script>
        function openSignModal() {
            document.getElementById('signModal').classList.add('active');
        }

        function openRejectModal() {
            document.getElementById('rejectModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function signAgreement() {
            const agreementId = <?php echo $agreement_id; ?>;

            fetch('sign_agreement.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        agreement_id: agreementId,
                        action: 'sign'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Agreement signed successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'Failed to sign agreement'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
        }

        function rejectAgreement() {
            const agreementId = <?php echo $agreement_id; ?>;

            fetch('sign_agreement.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        agreement_id: agreementId,
                        action: 'reject'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Request submitted');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'Failed to process request'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>

</html>