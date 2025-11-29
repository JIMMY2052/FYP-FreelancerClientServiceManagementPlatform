<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: admin_login.php');
    exit();
}

$conn = getDBConnection();

// Handle dispute resolution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resolve_dispute') {
    $dispute_id = isset($_POST['dispute_id']) ? intval($_POST['dispute_id']) : null;
    $agreement_id = isset($_POST['agreement_id']) ? intval($_POST['agreement_id']) : null;
    $resolution = isset($_POST['resolution']) ? trim($_POST['resolution']) : '';
    $resolution_notes = isset($_POST['resolution_notes']) ? trim($_POST['resolution_notes']) : '';
    $admin_id = $_SESSION['admin_id'];

    if (!empty($dispute_id) && !empty($agreement_id) && !empty($resolution) && in_array($resolution, ['refund_client', 'release_to_freelancer', 'split_payment', 'rejected'])) {
        // Update dispute status to resolved
        $update_dispute_sql = "UPDATE dispute SET Status = 'resolved', ResolutionAction = ?, AdminNotesText = ?, ResolvedByAdminID = ?, ResolvedAt = NOW() WHERE DisputeID = ?";
        $update_dispute_stmt = $conn->prepare($update_dispute_sql);
        $update_dispute_stmt->bind_param('ssii', $resolution, $resolution_notes, $admin_id, $dispute_id);
        $update_dispute_stmt->execute();
        $update_dispute_stmt->close();

        // Get agreement details for escrow and wallet operations
        $agreement_sql = "SELECT * FROM agreement WHERE AgreementID = ?";
        $agreement_stmt = $conn->prepare($agreement_sql);
        $agreement_stmt->bind_param('i', $agreement_id);
        $agreement_stmt->execute();
        $agreement_result = $agreement_stmt->get_result();
        $agreement = $agreement_result->fetch_assoc();
        $agreement_stmt->close();

        if ($agreement) {
            $freelancer_id = $agreement['FreelancerID'];
            $client_id = $agreement['ClientID'];
            $amount = $agreement['PaymentAmount'];

            // Get escrow record
            $escrow_sql = "SELECT * FROM escrow WHERE OrderID = ? AND Status = 'hold'";
            $escrow_stmt = $conn->prepare($escrow_sql);
            $escrow_stmt->bind_param('i', $agreement_id);
            $escrow_stmt->execute();
            $escrow_result = $escrow_stmt->get_result();
            $escrow = $escrow_result->fetch_assoc();
            $escrow_stmt->close();

            if ($escrow) {
                $escrow_id = $escrow['EscrowID'];

                // Handle different resolutions
                if ($resolution === 'refund_client') {
                    // Refund money to client
                    $conn->begin_transaction();
                    try {
                        // Update escrow to refunded
                        $escrow_update_sql = "UPDATE escrow SET Status = 'refunded', ReleasedAt = NOW() WHERE EscrowID = ?";
                        $escrow_update_stmt = $conn->prepare($escrow_update_sql);
                        $escrow_update_stmt->bind_param('i', $escrow_id);
                        $escrow_update_stmt->execute();
                        $escrow_update_stmt->close();

                        // Get client wallet
                        $wallet_sql = "SELECT * FROM wallet WHERE UserID = ?";
                        $wallet_stmt = $conn->prepare($wallet_sql);
                        $wallet_stmt->bind_param('i', $client_id);
                        $wallet_stmt->execute();
                        $wallet_result = $wallet_stmt->get_result();
                        $wallet = $wallet_result->fetch_assoc();
                        $wallet_stmt->close();

                        if ($wallet) {
                            $new_balance = $wallet['Balance'] + $amount;
                            $new_locked = $wallet['LockedBalance'] - $amount;

                            $wallet_update_sql = "UPDATE wallet SET Balance = ?, LockedBalance = ? WHERE WalletID = ?";
                            $wallet_update_stmt = $conn->prepare($wallet_update_sql);
                            $wallet_update_stmt->bind_param('ddi', $new_balance, $new_locked, $wallet['WalletID']);
                            $wallet_update_stmt->execute();
                            $wallet_update_stmt->close();

                            // Record transaction
                            $trans_sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Status, Description, ReferenceID) VALUES (?, 'refund', ?, 'completed', ?, ?)";
                            $trans_stmt = $conn->prepare($trans_sql);
                            $trans_desc = "Dispute refund for agreement #$agreement_id: $resolution_notes";
                            $trans_ref = "dispute_refund_$agreement_id";
                            $trans_stmt->bind_param('ids', $wallet['WalletID'], $amount, $trans_desc, $trans_ref);
                            $trans_stmt->execute();
                            $trans_stmt->close();
                        }
                        $conn->commit();
                        $_SESSION['success'] = 'Dispute resolved: Funds refunded to client.';
                    } catch (Exception $e) {
                        $conn->rollback();
                        $_SESSION['error'] = 'Error processing refund: ' . $e->getMessage();
                    }
                } elseif ($resolution === 'release_to_freelancer') {
                    // Release money to freelancer
                    $conn->begin_transaction();
                    try {
                        // Update escrow to released
                        $escrow_update_sql = "UPDATE escrow SET Status = 'released', ReleasedAt = NOW() WHERE EscrowID = ?";
                        $escrow_update_stmt = $conn->prepare($escrow_update_sql);
                        $escrow_update_stmt->bind_param('i', $escrow_id);
                        $escrow_update_stmt->execute();
                        $escrow_update_stmt->close();

                        // Get freelancer wallet
                        $wallet_sql = "SELECT * FROM wallet WHERE UserID = ?";
                        $wallet_stmt = $conn->prepare($wallet_sql);
                        $wallet_stmt->bind_param('i', $freelancer_id);
                        $wallet_stmt->execute();
                        $wallet_result = $wallet_stmt->get_result();
                        $wallet = $wallet_result->fetch_assoc();
                        $wallet_stmt->close();

                        if ($wallet) {
                            $new_balance = $wallet['Balance'] + $amount;

                            $wallet_update_sql = "UPDATE wallet SET Balance = ? WHERE WalletID = ?";
                            $wallet_update_stmt = $conn->prepare($wallet_update_sql);
                            $wallet_update_stmt->bind_param('di', $new_balance, $wallet['WalletID']);
                            $wallet_update_stmt->execute();
                            $wallet_update_stmt->close();

                            // Record transaction
                            $trans_sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Status, Description, ReferenceID) VALUES (?, 'earning', ?, 'completed', ?, ?)";
                            $trans_stmt = $conn->prepare($trans_sql);
                            $trans_desc = "Dispute resolution: Payment released for agreement #$agreement_id: $resolution_notes";
                            $trans_ref = "dispute_release_$agreement_id";
                            $trans_stmt->bind_param('ids', $wallet['WalletID'], $amount, $trans_desc, $trans_ref);
                            $trans_stmt->execute();
                            $trans_stmt->close();
                        }
                        $conn->commit();
                        $_SESSION['success'] = 'Dispute resolved: Funds released to freelancer.';
                    } catch (Exception $e) {
                        $conn->rollback();
                        $_SESSION['error'] = 'Error processing payment: ' . $e->getMessage();
                    }
                } elseif ($resolution === 'split_payment') {
                    // Split payment 50-50
                    $conn->begin_transaction();
                    try {
                        $split_amount = $amount / 2;

                        // Update escrow to released
                        $escrow_update_sql = "UPDATE escrow SET Status = 'released', ReleasedAt = NOW() WHERE EscrowID = ?";
                        $escrow_update_stmt = $conn->prepare($escrow_update_sql);
                        $escrow_update_stmt->bind_param('i', $escrow_id);
                        $escrow_update_stmt->execute();
                        $escrow_update_stmt->close();

                        // Refund to client
                        $client_wallet_sql = "SELECT * FROM wallet WHERE UserID = ?";
                        $client_wallet_stmt = $conn->prepare($client_wallet_sql);
                        $client_wallet_stmt->bind_param('i', $client_id);
                        $client_wallet_stmt->execute();
                        $client_wallet_result = $client_wallet_stmt->get_result();
                        $client_wallet = $client_wallet_result->fetch_assoc();
                        $client_wallet_stmt->close();

                        if ($client_wallet) {
                            $client_new_balance = $client_wallet['Balance'] + $split_amount;
                            $client_new_locked = $client_wallet['LockedBalance'] - $amount;

                            $client_wallet_update_sql = "UPDATE wallet SET Balance = ?, LockedBalance = ? WHERE WalletID = ?";
                            $client_wallet_update_stmt = $conn->prepare($client_wallet_update_sql);
                            $client_wallet_update_stmt->bind_param('ddi', $client_new_balance, $client_new_locked, $client_wallet['WalletID']);
                            $client_wallet_update_stmt->execute();
                            $client_wallet_update_stmt->close();

                            $client_trans_sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Status, Description, ReferenceID) VALUES (?, 'refund', ?, 'completed', ?, ?)";
                            $client_trans_stmt = $conn->prepare($client_trans_sql);
                            $client_trans_desc = "Dispute resolution (50% split): Partial refund for agreement #$agreement_id";
                            $client_trans_ref = "dispute_split_client_$agreement_id";
                            $client_trans_stmt->bind_param('ids', $client_wallet['WalletID'], $split_amount, $client_trans_desc, $client_trans_ref);
                            $client_trans_stmt->execute();
                            $client_trans_stmt->close();
                        }

                        // Pay to freelancer
                        $freelancer_wallet_sql = "SELECT * FROM wallet WHERE UserID = ?";
                        $freelancer_wallet_stmt = $conn->prepare($freelancer_wallet_sql);
                        $freelancer_wallet_stmt->bind_param('i', $freelancer_id);
                        $freelancer_wallet_stmt->execute();
                        $freelancer_wallet_result = $freelancer_wallet_stmt->get_result();
                        $freelancer_wallet = $freelancer_wallet_result->fetch_assoc();
                        $freelancer_wallet_stmt->close();

                        if ($freelancer_wallet) {
                            $freelancer_new_balance = $freelancer_wallet['Balance'] + $split_amount;

                            $freelancer_wallet_update_sql = "UPDATE wallet SET Balance = ? WHERE WalletID = ?";
                            $freelancer_wallet_update_stmt = $conn->prepare($freelancer_wallet_update_sql);
                            $freelancer_wallet_update_stmt->bind_param('di', $freelancer_new_balance, $freelancer_wallet['WalletID']);
                            $freelancer_wallet_update_stmt->execute();
                            $freelancer_wallet_update_stmt->close();

                            $freelancer_trans_sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Status, Description, ReferenceID) VALUES (?, 'earning', ?, 'completed', ?, ?)";
                            $freelancer_trans_stmt = $conn->prepare($freelancer_trans_sql);
                            $freelancer_trans_desc = "Dispute resolution (50% split): Partial payment for agreement #$agreement_id";
                            $freelancer_trans_ref = "dispute_split_freelancer_$agreement_id";
                            $freelancer_trans_stmt->bind_param('ids', $freelancer_wallet['WalletID'], $split_amount, $freelancer_trans_desc, $freelancer_trans_ref);
                            $freelancer_trans_stmt->execute();
                            $freelancer_trans_stmt->close();
                        }
                        $conn->commit();
                        $_SESSION['success'] = 'Dispute resolved: Payment split 50-50 between both parties.';
                    } catch (Exception $e) {
                        $conn->rollback();
                        $_SESSION['error'] = 'Error processing split payment: ' . $e->getMessage();
                    }
                }
            }
        }
    }

    header('Location: admin_manage_disputes.php');
    exit();
}

// Get filter and search parameters
$filter_status = $_GET['status'] ?? 'open';
$search_query = $_GET['search'] ?? '';

// Build query for disputed agreements
$disputes_sql = "SELECT 
    d.DisputeID,
    d.AgreementID,
    d.InitiatorID,
    d.InitiatorType,
    d.ReasonText,
    d.EvidenceFile,
    d.Status,
    d.CreatedAt,
    a.ProjectTitle,
    a.PaymentAmount,
    a.FreelancerID,
    a.ClientID,
    a.agreeementPath,
    CONCAT(f.FirstName, ' ', f.LastName) as FreelancerName,
    f.Email as FreelancerEmail,
    c.CompanyName as ClientName,
    c.Email as ClientEmail
FROM dispute d
JOIN agreement a ON d.AgreementID = a.AgreementID
JOIN freelancer f ON a.FreelancerID = f.FreelancerID
JOIN client c ON a.ClientID = c.ClientID
WHERE 1=1";

if ($filter_status === 'disputed') {
    $disputes_sql .= " AND d.Status = 'open'";
} elseif ($filter_status === 'resolved') {
    $disputes_sql .= " AND d.Status = 'resolved'";
} elseif ($filter_status === 'under_review') {
    $disputes_sql .= " AND d.Status = 'under_review'";
}

if (!empty($search_query)) {
    $search_term = '%' . $search_query . '%';
    $disputes_sql .= " AND (a.ProjectTitle LIKE ? OR f.FirstName LIKE ? OR f.LastName LIKE ? OR c.CompanyName LIKE ?)";
}

$disputes_sql .= " ORDER BY d.CreatedAt DESC";

$disputes = [];
if (!empty($search_query)) {
    $stmt = $conn->prepare($disputes_sql);
    $stmt->bind_param('ssss', $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($disputes_sql);
    $stmt = null;
}

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $disputes[] = $row;
    }
}

if ($stmt) {
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Disputes - WorkSnyc Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Momo+Trust+Display&display=swap">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body {
            font-family: 'Momo Trust Display', sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Momo Trust Display', sans-serif;
            font-weight: 500;
        }

        p,
        .error-message,
        .form-control,
        select,
        input[type="text"],
        button,
        a {
            font-family: 'Inter', sans-serif;
        }

        table {
            font-family: 'Inter', sans-serif;
        }

        .table-header h2 {
            font-family: 'Momo Trust Display', sans-serif;
            font-weight: 500;
        }

        .filter-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafb 100%);
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .filter-form {
            display: flex;
            gap: 16px;
            width: 100%;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-input-group {
            flex: 1;
            min-width: 200px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-input-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-input {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1.5px solid #e5e7eb;
            font-size: 0.9375rem;
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            transition: all 0.3s ease;
            color: #374151;
            width: 100%;
        }

        .filter-input::placeholder {
            color: #9ca3af;
        }

        .filter-input:focus {
            outline: none;
            border-color: rgb(159, 232, 112);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
        }

        .filter-select {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1.5px solid #e5e7eb;
            font-size: 0.9375rem;
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            transition: all 0.3s ease;
            color: #374151;
            cursor: pointer;
            min-width: 160px;
        }

        .filter-select:focus {
            outline: none;
            border-color: rgb(159, 232, 112);
            box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
        }

        .filter-select:hover {
            border-color: rgb(159, 232, 112);
        }

        .filter-button {
            padding: 10px 24px;
            background: linear-gradient(135deg, rgb(159, 232, 112) 0%, rgb(139, 212, 92) 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.9375rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
            white-space: nowrap;
        }

        .filter-button:hover {
            background: linear-gradient(135deg, rgb(139, 212, 92) 0%, rgb(119, 192, 72) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(159, 232, 112, 0.4);
        }

        .dispute-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .badge-disputed {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-under-review {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-resolved {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-rejected {
            background-color: #f3e8e8;
            color: #7c2d12;
        }

        .resolve-btn {
            padding: 8px 16px;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .resolve-btn:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
        }

        .details-btn {
            padding: 8px 16px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .details-btn:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 32px;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            margin-bottom: 24px;
        }

        .modal-header h2 {
            margin: 0 0 8px;
            font-size: 1.5rem;
            color: #1f2937;
        }

        .modal-header p {
            margin: 0;
            color: #6b7280;
            font-size: 0.9375rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 0.9375rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9375rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: rgb(159, 232, 112);
            box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
        }

        .modal-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .btn-cancel {
            background-color: #e5e7eb;
            color: #374151;
        }

        .btn-cancel:hover {
            background-color: #d1d5db;
        }

        .btn-submit {
            background: linear-gradient(135deg, rgb(159, 232, 112) 0%, rgb(139, 212, 92) 100%);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
        }

        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }

            .filter-input-group {
                width: 100%;
                min-width: unset;
            }

            .filter-select {
                width: 100%;
                min-width: unset;
            }

            .filter-button {
                width: 100%;
            }

            .filter-section {
                padding: 16px;
            }

            .modal-content {
                padding: 20px;
            }
        }
    </style>
</head>

<body class="admin-layout">
    <div class="admin-sidebar">
        <?php include '../includes/admin_sidebar.php'; ?>
    </div>

    <div class="admin-layout-wrapper">
        <?php include '../includes/admin_header.php'; ?>

        <main class="admin-main-content">
            <div class="dashboard-container">
                <div class="dashboard-header">
                    <div>
                        <h1>Manage Disputes</h1>
                        <p>Review and resolve agreement disputes between clients and freelancers</p>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="error-message" style="background-color: #d1fae5; border-color: #6ee7b7; color: #065f46;">
                        <?php
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="GET" action="admin_manage_disputes.php" class="filter-form">
                        <div class="filter-input-group" style="flex: 2;">
                            <label class="filter-input-label">Search Project</label>
                            <input
                                type="text"
                                name="search"
                                placeholder="Search by project title, freelancer, or client..."
                                class="filter-input"
                                value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>

                        <div class="filter-input-group">
                            <label class="filter-input-label">Status</label>
                            <select name="status" class="filter-select">
                                <option value="open" <?php echo $filter_status === 'open' ? 'selected' : ''; ?>>Open</option>
                                <option value="under_review" <?php echo $filter_status === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                                <option value="resolved" <?php echo $filter_status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All</option>
                            </select>
                        </div>

                        <button type="submit" class="filter-button"><i class="fas fa-search"></i> Filter</button>
                    </form>
                </div>

                <!-- Disputes Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>Disputes (<?php echo count($disputes); ?>)</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Freelancer</th>
                                <th>Client</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($disputes) > 0): ?>
                                <?php foreach ($disputes as $dispute): ?>
                                    <tr>
                                        <td>
                                            <strong><a href="<?php echo htmlspecialchars($dispute['agreeementPath'] ?? '#'); ?>" target="_blank" style="color: #3b82f6; text-decoration: none; cursor: pointer;">
                                                <?php echo htmlspecialchars($dispute['ProjectTitle']); ?>
                                                <i class="fas fa-external-link-alt" style="font-size: 0.75rem; margin-left: 4px;"></i>
                                            </a></strong>
                                            <div style="font-size: 0.875rem; color: #6b7280;">Agreement #<?php echo $dispute['AgreementID']; ?></div>
                                        </td>
                                        <td>
                                            <div>
                                                <div style="font-weight: 600;"><?php echo htmlspecialchars($dispute['FreelancerName']); ?></div>
                                                <div style="font-size: 0.875rem; color: #6b7280;"><?php echo htmlspecialchars($dispute['FreelancerEmail']); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div style="font-weight: 600;"><?php echo htmlspecialchars($dispute['ClientName']); ?></div>
                                                <div style="font-size: 0.875rem; color: #6b7280;"><?php echo htmlspecialchars($dispute['ClientEmail']); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>RM <?php echo number_format($dispute['PaymentAmount'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <span class="dispute-badge badge-<?php echo str_replace('_', '-', $dispute['Status']); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $dispute['Status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($dispute['CreatedAt'])); ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 8px;">
                                                <button type="button" class="resolve-btn" style="background-color: #3b82f6;" data-dispute-id="<?php echo $dispute['DisputeID']; ?>" data-reason="<?php echo htmlspecialchars($dispute['ReasonText'] ?? '', ENT_QUOTES); ?>" data-evidence="<?php echo htmlspecialchars($dispute['EvidenceFile'] ?? '', ENT_QUOTES); ?>" onclick="openDetailModalFromButton(this)">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                                <?php if (in_array($dispute['Status'], ['open', 'under_review'])): ?>
                                                    <button type="button" class="resolve-btn" onclick="openResolveModal(<?php echo $dispute['DisputeID']; ?>, <?php echo $dispute['AgreementID']; ?>, '<?php echo htmlspecialchars(str_replace("'", "\\'", $dispute['ProjectTitle']), ENT_QUOTES); ?>', <?php echo $dispute['PaymentAmount']; ?>)">
                                                        Resolve
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 30px;">
                                        <p style="color: #9ca3af;">No disputes found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Dispute Details Modal -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Dispute Details</h2>
                <p>View dispute reason and evidence</p>
            </div>

            <div class="form-group">
                <label>Dispute Reason</label>
                <div id="dispute_reason_display" style="background: #f3f4f6; padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb; min-height: 80px; white-space: pre-wrap; word-break: break-word;"></div>
            </div>

            <div class="form-group">
                <label>Evidence File</label>
                <div id="evidence_display" style="background: #f3f4f6; padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb; min-height: 60px;">
                    <span id="evidence_text">No evidence file</span>
                </div>
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeDetailModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Resolve Dispute Modal -->
    <div id="resolveModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Resolve Dispute</h2>
                <p>Choose a resolution for this dispute</p>
            </div>

            <form method="POST" action="admin_manage_disputes.php">
                <input type="hidden" name="action" value="resolve_dispute">
                <input type="hidden" name="dispute_id" id="dispute_id">
                <input type="hidden" name="agreement_id" id="dispute_agreement_id">

                <div class="form-group">
                    <label>Project</label>
                    <input type="text" id="dispute_project_title" readonly>
                </div>

                <div class="form-group">
                    <label>Amount</label>
                    <input type="text" id="dispute_amount" readonly>
                </div>

                <div class="form-group">
                    <label for="resolution">Resolution Type *</label>
                    <select name="resolution" id="resolution" required>
                        <option value="">-- Select Resolution --</option>
                        <option value="refund_client">Refund Client (Full Amount)</option>
                        <option value="release_to_freelancer">Release to Freelancer (Full Amount)</option>
                        <option value="split_payment">Split Payment (50-50)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="resolution_notes">Resolution Notes</label>
                    <textarea name="resolution_notes" id="resolution_notes" placeholder="Explain the reason for this resolution..."></textarea>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeResolveModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Resolve Dispute</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const detailModal = document.getElementById('detailModal');
            const resolveModal = document.getElementById('resolveModal');

            if (detailModal) {
                detailModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeDetailModal();
                    }
                });
            }

            if (resolveModal) {
                resolveModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeResolveModal();
                    }
                });
            }
        });

        function openDetailModalFromButton(button) {
            const reasonText = button.getAttribute('data-reason');
            const evidenceFile = button.getAttribute('data-evidence');

            const detailModal = document.getElementById('detailModal');
            if (!detailModal) {
                console.error('Detail modal not found');
                return;
            }

            document.getElementById('dispute_reason_display').textContent = reasonText || 'No reason provided';

            const evidenceDisplay = document.getElementById('evidence_text');
            if (evidenceFile && evidenceFile.trim() !== '') {
                const fileName = evidenceFile.split('/').pop();
                evidenceDisplay.innerHTML = `<i class="fas fa-file"></i> <a href="${evidenceFile}" target="_blank" style="color: #3b82f6; text-decoration: underline;">${fileName}</a>`;
            } else {
                evidenceDisplay.textContent = 'No evidence file';
            }

            detailModal.classList.add('show');
        }

        function closeDetailModal() {
            const detailModal = document.getElementById('detailModal');
            if (detailModal) {
                detailModal.classList.remove('show');
            }
        }

        function openResolveModal(disputeId, agreementId, projectTitle, amount) {
            document.getElementById('dispute_id').value = disputeId;
            document.getElementById('dispute_agreement_id').value = agreementId;
            document.getElementById('dispute_project_title').value = projectTitle;
            document.getElementById('dispute_amount').value = 'RM ' + parseFloat(amount).toFixed(2);
            document.getElementById('resolveModal').classList.add('show');
        }

        function closeResolveModal() {
            document.getElementById('resolveModal').classList.remove('show');
        }
    </script>
</body>

</html>