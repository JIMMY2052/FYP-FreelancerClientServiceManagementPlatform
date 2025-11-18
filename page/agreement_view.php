<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
include 'config.php';

// Check if agreement ID is provided
if (!isset($_GET['id'])) {
    header("Location: agreement.php");
    exit();
}

$agreement_id = intval($_GET['id']);
$conn = getDBConnection();

// Fetch agreement data
$sql = "SELECT * FROM agreement WHERE AgreementID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $agreement_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Agreement not found.";
    header("Location: agreement.php");
    exit();
}

$agreement = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Get freelancer and client names for display
$freelancer_name = "Freelancer Name";
$client_name = "Client Name";

if (isset($_SESSION['freelancer_id'])) {
    $conn = getDBConnection();
    $freelancer_id = $_SESSION['freelancer_id'];
    $sql = "SELECT FirstName, LastName FROM freelancer WHERE FreelancerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $freelancer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $freelancer = $result->fetch_assoc();
        $freelancer_name = $freelancer['FirstName'] . ' ' . $freelancer['LastName'];
    }
    $stmt->close();
    $conn->close();
}

if (isset($_SESSION['client_id'])) {
    $conn = getDBConnection();
    $client_id = $_SESSION['client_id'];
    $sql = "SELECT CompanyName FROM client WHERE ClientID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $client = $result->fetch_assoc();
        $client_name = $client['CompanyName'];
    }
    $stmt->close();
    $conn->close();
}

// Get success message from redirect
$showSuccess = isset($_GET['status']) && $_GET['status'] === 'created';
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Agreement</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .header {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            justify-content: center;
        }

        .btn {
            padding: 12px 28px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #1ab394;
            color: white;
            box-shadow: 0 2px 8px rgba(26, 179, 148, 0.2);
        }

        .btn-primary:hover {
            background: #158a74;
            box-shadow: 0 4px 12px rgba(26, 179, 148, 0.3);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f0f1f3;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e0e2e8;
        }

        /* PREVIEW DESIGN - MATCHES agreement.php */
        .preview-header {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .preview-header-left h3 {
            font-size: 1.8rem;
            color: #1a1a1a;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .preview-header-left p {
            font-size: 0.95rem;
            color: #7b8fa3;
        }

        .preview-header-right {
            text-align: right;
            font-size: 0.9rem;
        }

        .preview-header-right .label {
            color: #7b8fa3;
            display: block;
            margin-bottom: 4px;
            font-size: 0.85rem;
        }

        .preview-header-right .value {
            color: #1a1a1a;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }

        .preview-section {
            margin-bottom: 32px;
        }

        .section-number {
            font-size: 1.3rem;
            color: #1a1a1a;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        .section-number span {
            display: inline-block;
            width: 32px;
            height: 32px;
            line-height: 32px;
            text-align: center;
            background: transparent;
            border-radius: 50%;
            margin-right: 12px;
            font-weight: 700;
            color: #4b5563;
        }

        .section-title {
            font-size: 1.05rem;
            color: #4b5563;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .section-content {
            color: #5a6b7d;
            font-size: 0.95rem;
            line-height: 1.7;
            word-wrap: break-word;
            white-space: pre-wrap;
        }

        .payment-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            margin-top: 12px;
        }

        .payment-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .payment-label {
            color: #5a6b7d;
            font-weight: 500;
        }

        .payment-amount {
            font-size: 1.5rem;
            color: #1ab394;
            font-weight: 700;
        }

        @media (max-width: 640px) {
            .container {
                padding: 20px;
            }

            .preview-header {
                flex-direction: column;
            }

            .preview-header-right {
                text-align: left;
                margin-top: 16px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Agreement View</h1>
    </div>

    <?php if ($showSuccess): ?>
        <div class="success-message">
            ✓ Agreement created successfully! You can now download it as PDF or share it with the other party.
        </div>
    <?php endif; ?>

    <div class="container">

        <div class="button-group">
            <a href="agreement_pdf.php?id=<?php echo $agreement_id; ?>" class="btn btn-primary" target="_blank">
                📥 Download as PDF
            </a>
            <a href="agreement.php" class="btn btn-secondary">
                ← Back to Create Agreement
            </a>
        </div>

        <!-- PREVIEW SECTION -->
        <div class="preview-header">
            <div class="preview-header-left">
                <h3><?php echo htmlspecialchars($agreement['ProjectTitle']); ?></h3>
                <p><?php echo htmlspecialchars($agreement['ProjectDetail']); ?></p>
            </div>
            <div class="preview-header-right">
                <span class="label">Offer from:</span>
                <span class="value"><?php echo htmlspecialchars($freelancer_name); ?></span>
                <span class="label" style="margin-top: 12px;">To:</span>
                <span class="value"><?php echo htmlspecialchars($client_name); ?></span>
                <span class="label" style="margin-top: 12px;">Date:</span>
                <span class="value"><?php echo date('F j, Y', strtotime($agreement['SignedDate'])); ?></span>
            </div>
        </div>

        <!-- SECTION 1: SCOPE OF WORK -->
        <div class="preview-section">
            <div class="section-number">
                <span>1</span>
                <div class="section-title">Scope of Work</div>
            </div>
            <div class="section-content">
                <?php echo htmlspecialchars($agreement['Scope']); ?>
            </div>
        </div>

        <!-- SECTION 2: DELIVERABLES & TIMELINE -->
        <div class="preview-section">
            <div class="section-number">
                <span>2</span>
                <div class="section-title">Deliverables & Timeline</div>
            </div>
            <div class="section-content">
                <?php echo htmlspecialchars($agreement['Deliverables']); ?>
            </div>
        </div>

        <!-- SECTION 3: PAYMENT TERMS -->
        <div class="preview-section">
            <div class="section-number">
                <span>3</span>
                <div class="section-title">Payment Terms</div>
            </div>
            <div class="section-content">
                <div class="payment-box">
                    <div class="payment-total">
                        <span class="payment-label">Total Project Price:</span>
                        <span class="payment-amount">RM <?php echo number_format($agreement['PaymentAmount'], 2); ?></span>
                    </div>
                    <p style="color: #5a6b7d; font-size: 0.95rem;">Payment will be released in milestones upon completion of deliverables.</p>
                </div>
            </div>
        </div>

        <!-- SECTION 4: TERMS & CONDITIONS -->
        <div class="preview-section">
            <div class="section-number">
                <span>4</span>
                <div class="section-title">Terms & Conditions</div>
            </div>
            <div class="section-content">
                <?php echo htmlspecialchars($agreement['Terms']); ?>
            </div>
        </div>

        <!-- AGREEMENT INFO -->
        <div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid #e5e7eb; text-align: center;">
            <p style="color: #999; font-size: 0.9rem;">
                Agreement ID: <strong><?php echo $agreement_id; ?></strong> |
                Status: <strong><?php echo ucfirst($agreement['Status']); ?></strong> |
                Created: <strong><?php echo date('F j, Y', strtotime($agreement['SignedDate'])); ?></strong>
            </p>
        </div>

    </div>

</body>

</html>