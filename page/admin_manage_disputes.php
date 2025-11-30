<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: admin_login.php');
    exit();
}

$conn = getDBConnection();

// Handle dispute resolution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'unresolve_dispute') {
        // Unresolve a dispute back to open status and reverse the wallet transactions
        $dispute_id = isset($_POST['dispute_id']) ? intval($_POST['dispute_id']) : null;
        $agreement_id = isset($_POST['agreement_id']) ? intval($_POST['agreement_id']) : null;

        if (!empty($dispute_id) && !empty($agreement_id)) {
            $conn->begin_transaction();
            try {
                // Get the previous resolution to reverse it
                $dispute_sql = "SELECT ResolutionAction FROM dispute WHERE DisputeID = ?";
                $dispute_stmt = $conn->prepare($dispute_sql);
                $dispute_stmt->bind_param('i', $dispute_id);
                $dispute_stmt->execute();
                $dispute_result = $dispute_stmt->get_result();
                $dispute_data = $dispute_result->fetch_assoc();
                $dispute_stmt->close();

                $previous_resolution = $dispute_data['ResolutionAction'] ?? null;

                // Get agreement details
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

                    // Reverse the previous resolution
                    if ($previous_resolution === 'resume_ongoing') {
                        // Minor dispute was dismissed, just revert agreement status back to disputed
                        $agreement_update_sql = "UPDATE agreement SET Status = 'disputed' WHERE AgreementID = ?";
                        $agreement_update_stmt = $conn->prepare($agreement_update_sql);
                        $agreement_update_stmt->bind_param('i', $agreement_id);
                        $agreement_update_stmt->execute();
                        $agreement_update_stmt->close();
                        // No wallet changes needed for this resolution type
                    } elseif ($previous_resolution === 'refund_client') {
                        // Client received refund, deduct it back
                        $wallet_sql = "SELECT * FROM wallet WHERE UserID = ?";
                        $wallet_stmt = $conn->prepare($wallet_sql);
                        $wallet_stmt->bind_param('i', $client_id);
                        $wallet_stmt->execute();
                        $wallet_result = $wallet_stmt->get_result();
                        $wallet = $wallet_result->fetch_assoc();
                        $wallet_stmt->close();

                        if ($wallet) {
                            $new_balance = max(0, $wallet['Balance'] - $amount); // Prevent negative balance
                            $wallet_update_sql = "UPDATE wallet SET Balance = ? WHERE WalletID = ?";
                            $wallet_update_stmt = $conn->prepare($wallet_update_sql);
                            $wallet_update_stmt->bind_param('di', $new_balance, $wallet['WalletID']);
                            $wallet_update_stmt->execute();
                            $wallet_update_stmt->close();

                            // Record reversal transaction
                            $trans_sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Status, Description, ReferenceID) VALUES (?, 'deduction', ?, 'completed', ?, ?)";
                            $trans_stmt = $conn->prepare($trans_sql);
                            $wallet_id = $wallet['WalletID'];
                            $trans_desc = "Dispute reversal: Refund deducted for agreement #$agreement_id";
                            $trans_ref = "dispute_reverse_refund_$agreement_id";
                            $trans_stmt->bind_param('idss', $wallet_id, $amount, $trans_desc, $trans_ref);
                            $trans_stmt->execute();
                            $trans_stmt->close();
                        }
                    } elseif ($previous_resolution === 'release_to_freelancer') {
                        // Freelancer received payment, deduct it back
                        $wallet_sql = "SELECT * FROM wallet WHERE UserID = ?";
                        $wallet_stmt = $conn->prepare($wallet_sql);
                        $wallet_stmt->bind_param('i', $freelancer_id);
                        $wallet_stmt->execute();
                        $wallet_result = $wallet_stmt->get_result();
                        $wallet = $wallet_result->fetch_assoc();
                        $wallet_stmt->close();

                        if ($wallet) {
                            $new_balance = max(0, $wallet['Balance'] - $amount); // Prevent negative balance
                            $wallet_update_sql = "UPDATE wallet SET Balance = ? WHERE WalletID = ?";
                            $wallet_update_stmt = $conn->prepare($wallet_update_sql);
                            $wallet_update_stmt->bind_param('di', $new_balance, $wallet['WalletID']);
                            $wallet_update_stmt->execute();
                            $wallet_update_stmt->close();

                            // Record reversal transaction
                            $trans_sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Status, Description, ReferenceID) VALUES (?, 'deduction', ?, 'completed', ?, ?)";
                            $trans_stmt = $conn->prepare($trans_sql);
                            $wallet_id = $wallet['WalletID'];
                            $trans_desc = "Dispute reversal: Payment deducted for agreement #$agreement_id";
                            $trans_ref = "dispute_reverse_release_$agreement_id";
                            $trans_stmt->bind_param('idss', $wallet_id, $amount, $trans_desc, $trans_ref);
                            $trans_stmt->execute();
                            $trans_stmt->close();
                        }
                    } else {
                        // For other resolution types (refund_client, release_to_freelancer, split_payment, etc.)
                        // Revert agreement back to disputed status
                        $agreement_update_sql = "UPDATE agreement SET Status = 'disputed' WHERE AgreementID = ?";
                        $agreement_update_stmt = $conn->prepare($agreement_update_sql);
                        $agreement_update_stmt->bind_param('i', $agreement_id);
                        $agreement_update_stmt->execute();
                        $agreement_update_stmt->close();
                    }
                }

                // Revert escrow status back to hold if it exists
                $escrow_sql = "UPDATE escrow SET Status = 'hold', ReleasedAt = NULL WHERE OrderID = ? AND Status IN ('released', 'refunded')";
                $escrow_stmt = $conn->prepare($escrow_sql);
                $escrow_stmt->bind_param('i', $agreement_id);
                $escrow_stmt->execute();
                $escrow_stmt->close();

                // Update dispute status to open
                $unresolve_sql = "UPDATE dispute SET Status = 'open', ResolutionAction = NULL, AdminNotesText = NULL, ResolvedByAdminID = NULL, ResolvedAt = NULL WHERE DisputeID = ?";
                $unresolve_stmt = $conn->prepare($unresolve_sql);
                $unresolve_stmt->bind_param('i', $dispute_id);
                $unresolve_stmt->execute();
                $unresolve_stmt->close();

                $conn->commit();
                $_SESSION['success'] = 'Dispute reverted to open status. Funds have been deducted back. You can now apply a new resolution.';
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['error'] = 'Error reverting dispute: ' . $e->getMessage();
            }
        }

        header('Location: admin_manage_disputes.php');
        exit();
    }

    if ($_POST['action'] === 'resolve_dispute') {
        $dispute_id = isset($_POST['dispute_id']) ? intval($_POST['dispute_id']) : null;
        $agreement_id = isset($_POST['agreement_id']) ? intval($_POST['agreement_id']) : null;
        $resolution = isset($_POST['resolution']) ? trim($_POST['resolution']) : '';
        $resolution_notes = isset($_POST['resolution_notes']) ? trim($_POST['resolution_notes']) : '';
        $admin_id = $_SESSION['admin_id'];

        if (!empty($dispute_id) && !empty($agreement_id) && !empty($resolution) && in_array($resolution, ['refund_client', 'release_to_freelancer', 'split_payment', 'rejected', 'resume_ongoing'])) {
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

                // Handle different resolutions (escrow check only needed for refund/release/split)
                if ($resolution === 'resume_ongoing') {
                    // Resume agreement as ongoing (minor dispute, no money changes)
                    try {
                        // Keep escrow as is (still holding the money)
                        // Just update the agreement status back to ongoing
                        $agreement_update_sql = "UPDATE agreement SET Status = 'ongoing' WHERE AgreementID = ?";
                        $agreement_update_stmt = $conn->prepare($agreement_update_sql);
                        $agreement_update_stmt->bind_param('i', $agreement_id);
                        $agreement_update_stmt->execute();
                        $agreement_update_stmt->close();

                        // Note: The resolution action is already stored by the initial UPDATE dispute statement above
                        $_SESSION['success'] = 'Dispute dismissed: Agreement resumed as ongoing. The dispute was considered a minor case.';
                    } catch (Exception $e) {
                        $_SESSION['error'] = 'Error resuming agreement: ' . $e->getMessage();
                    }
                } elseif ($escrow) {
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
                                $wallet_id = $wallet['WalletID'];
                                $trans_desc = "Dispute refund for agreement #$agreement_id: $resolution_notes";
                                $trans_ref = "dispute_refund_$agreement_id";
                                $trans_stmt->bind_param('idss', $wallet_id, $amount, $trans_desc, $trans_ref);
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
                                $wallet_id = $wallet['WalletID'];
                                $trans_desc = "Dispute resolution: Payment released for agreement #$agreement_id: $resolution_notes";
                                $trans_ref = "dispute_release_$agreement_id";
                                $trans_stmt->bind_param('idss', $wallet_id, $amount, $trans_desc, $trans_ref);
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
                                $client_wallet_id = $client_wallet['WalletID'];
                                $client_trans_desc = "Dispute resolution (50% split): Partial refund for agreement #$agreement_id";
                                $client_trans_ref = "dispute_split_client_$agreement_id";
                                $client_trans_stmt->bind_param('idss', $client_wallet_id, $split_amount, $client_trans_desc, $client_trans_ref);
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
                                $freelancer_wallet_id = $freelancer_wallet['WalletID'];
                                $freelancer_trans_desc = "Dispute resolution (50% split): Partial payment for agreement #$agreement_id";
                                $freelancer_trans_ref = "dispute_split_freelancer_$agreement_id";
                                $freelancer_trans_stmt->bind_param('idss', $freelancer_wallet_id, $split_amount, $freelancer_trans_desc, $freelancer_trans_ref);
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
}

// Get filter and search parameters
$filter_status = $_GET['status'] ?? 'open';
$search_query = $_GET['search'] ?? '';

// Count disputes by status for tabs
$count_sql = "SELECT Status, COUNT(*) as count FROM dispute GROUP BY Status";
$count_result = $conn->query($count_sql);
$status_counts = ['all' => 0, 'open' => 0, 'resolved' => 0];

while ($row = $count_result->fetch_assoc()) {
    if ($row['Status'] === 'open') {
        $status_counts['open'] += $row['count'];
    } elseif ($row['Status'] === 'resolved') {
        $status_counts['resolved'] += $row['count'];
    }
    $status_counts['all'] += $row['count'];
}

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
    d.ResolutionAction,
    d.AdminNotesText,
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

if ($filter_status === 'open') {
    $disputes_sql .= " AND d.Status = 'open'";
} elseif ($filter_status === 'resolved') {
    $disputes_sql .= " AND d.Status = 'resolved'";
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
    <link rel="stylesheet" href="../assets/css/disputes.css">
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

                <!-- Filter Tabs -->
                <div style="display: flex; gap: 15px; margin: 30px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 0;">
                    <a href="?status=all" style="padding: 12px 20px; text-decoration: none; font-size: 14px; font-weight: 600; color: <?php echo $filter_status === 'all' ? '#22c55e' : '#7f8c8d'; ?>; border-bottom: 3px solid <?php echo $filter_status === 'all' ? '#22c55e' : 'transparent'; ?>; transition: all 0.3s ease;">
                        All <span style="background: #e9ecef; color: #2c3e50; border-radius: 12px; padding: 2px 8px; margin-left: 6px; font-size: 12px; font-weight: 700;"><?php echo $status_counts['all']; ?></span>
                    </a>
                    <a href="?status=open" style="padding: 12px 20px; text-decoration: none; font-size: 14px; font-weight: 600; color: <?php echo $filter_status === 'open' ? '#22c55e' : '#7f8c8d'; ?>; border-bottom: 3px solid <?php echo $filter_status === 'open' ? '#22c55e' : 'transparent'; ?>; transition: all 0.3s ease;">
                        Open <span style="background: #e9ecef; color: #2c3e50; border-radius: 12px; padding: 2px 8px; margin-left: 6px; font-size: 12px; font-weight: 700;"><?php echo $status_counts['open']; ?></span>
                    </a>
                    <a href="?status=resolved" style="padding: 12px 20px; text-decoration: none; font-size: 14px; font-weight: 600; color: <?php echo $filter_status === 'resolved' ? '#22c55e' : '#7f8c8d'; ?>; border-bottom: 3px solid <?php echo $filter_status === 'resolved' ? '#22c55e' : 'transparent'; ?>; transition: all 0.3s ease;">
                        Resolved <span style="background: #e9ecef; color: #2c3e50; border-radius: 12px; padding: 2px 8px; margin-left: 6px; font-size: 12px; font-weight: 700;"><?php echo $status_counts['resolved']; ?></span>
                    </a>
                </div>


                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

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
                                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                                <button type="button" class="resolve-btn" style="background-color: #3b82f6;" data-dispute-id="<?php echo $dispute['DisputeID']; ?>" data-reason="<?php echo htmlspecialchars($dispute['ReasonText'] ?? '', ENT_QUOTES); ?>" data-evidence="<?php echo htmlspecialchars($dispute['EvidenceFile'] ?? '', ENT_QUOTES); ?>" onclick="openDetailModalFromButton(this)">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                                <?php if (in_array($dispute['Status'], ['open', 'under_review'])): ?>
                                                    <button type="button" class="resolve-btn" data-dispute-id="<?php echo $dispute['DisputeID']; ?>" data-agreement-id="<?php echo $dispute['AgreementID']; ?>" data-project-title="<?php echo htmlspecialchars($dispute['ProjectTitle'], ENT_QUOTES); ?>" data-amount="<?php echo $dispute['PaymentAmount']; ?>" onclick="openResolveModalFromButton(this)">
                                                        Resolve
                                                    </button>
                                                <?php elseif ($dispute['Status'] === 'resolved'): ?>
                                                    <button type="button" class="resolve-btn" style="background-color: #10b981;" data-resolution-action="<?php echo htmlspecialchars($dispute['ResolutionAction'] ?? '', ENT_QUOTES); ?>" data-resolution-notes="<?php echo htmlspecialchars($dispute['AdminNotesText'] ?? '', ENT_QUOTES); ?>" onclick="openResolutionModalFromButton(this)">
                                                        <i class="fas fa-check-circle"></i> View Resolution
                                                    </button>
                                                    <button type="button" class="resolve-btn" style="background-color: #ef4444;" data-dispute-id="<?php echo $dispute['DisputeID']; ?>" data-agreement-id="<?php echo $dispute['AgreementID']; ?>" onclick="openUnresolveConfirmationModal(this)">
                                                        <i class="fas fa-undo"></i> Unresolve
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

    <!-- Resolution Details Modal -->
    <div id="resolutionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Resolution Details</h2>
                <p>View how this dispute was resolved</p>
            </div>

            <div class="form-group">
                <label>Resolution Action</label>
                <div id="resolution_action_display" style="background: #f3f4f6; padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb; min-height: 40px; display: flex; align-items: center; color: #374151; font-weight: 600;"></div>
            </div>

            <div class="form-group">
                <label>Admin Notes</label>
                <div id="resolution_notes_display" style="background: #f3f4f6; padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb; min-height: 80px; white-space: pre-wrap; word-break: break-word;"></div>
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeResolutionModal()">Close</button>
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

            <form method="POST" action="admin_manage_disputes.php" id="resolveForm">
                <input type="hidden" name="action" value="resolve_dispute">
                <input type="hidden" name="dispute_id" id="dispute_id">
                <input type="hidden" name="agreement_id" id="dispute_agreement_id">

                <div class="form-group">
                    <label>Project</label>
                    <div id="dispute_project_title" style="background: #f3f4f6; padding: 10px 14px; border-radius: 8px; border: 1px solid #e5e7eb; min-height: 40px; display: flex; align-items: center; color: #374151;"></div>
                </div>

                <div class="form-group">
                    <label>Amount</label>
                    <div id="dispute_amount" style="background: #f3f4f6; padding: 10px 14px; border-radius: 8px; border: 1px solid #e5e7eb; min-height: 40px; display: flex; align-items: center; color: #374151;"></div>
                </div>

                <div class="form-group">
                    <label for="resolution">Resolution Type *</label>
                    <select name="resolution" id="resolution" required>
                        <option value="">-- Select Resolution --</option>
                        <option value="refund_client">Refund Client (Full Amount)</option>
                        <option value="release_to_freelancer">Release to Freelancer (Full Amount)</option>
                        <option value="resume_ongoing">Resume Agreement (Ongoing) - Minor Dispute</option>
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

    <!-- Unresolve Confirmation Modal -->
    <div id="unresolveConfirmationModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2>Confirm Unresolve</h2>
                <p>Are you sure you want to revert this dispute?</p>
            </div>

            <div style="margin: 20px 0; padding: 16px; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 6px;">
                <p style="margin: 0; color: #7f1d1d; font-size: 14px;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                    This will revert the dispute to open status and deduct funds from wallets.
                </p>
            </div>

            <div style="background: #f3f4f6; padding: 12px; border-radius: 8px; margin: 15px 0; font-size: 14px;">
                <p style="margin: 8px 0;"><strong>Dispute ID:</strong> <span id="unresolve_dispute_id"></span></p>
                <p style="margin: 8px 0; color: #7f1d1d; font-weight: 600;"><i class="fas fa-info-circle" style="margin-right: 6px;"></i>Funds will be deducted back from user wallets</p>
            </div>

            <div class="modal-buttons" style="gap: 10px;">
                <button type="button" class="btn-cancel" onclick="closeUnresolveConfirmationModal()" style="flex: 1;">Cancel</button>
                <button type="button" class="btn-submit" onclick="submitUnresolveForm()" style="flex: 1; background-color: #ef4444;">Yes, Unresolve</button>
            </div>
        </div>
    </div>

    <!-- Hidden unresolve form -->
    <form method="POST" action="admin_manage_disputes.php" id="unresolveForm" style="display: none;">
        <input type="hidden" name="action" value="unresolve_dispute">
        <input type="hidden" name="dispute_id" id="unresolve_dispute_id_input">
        <input type="hidden" name="agreement_id" id="unresolve_agreement_id_input">
    </form>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2>Confirm Resolution</h2>
                <p>Are you sure you want to resolve this dispute?</p>
            </div>

            <div style="margin: 20px 0; padding: 16px; background: #f0f9ff; border-left: 4px solid #3b82f6; border-radius: 6px;">
                <p style="margin: 0; color: #1e40af; font-size: 14px;">
                    <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
                    This action cannot be easily undone. Please review your resolution choice carefully.
                </p>
            </div>

            <div id="confirmation_details" style="background: #f3f4f6; padding: 12px; border-radius: 8px; margin: 15px 0; font-size: 14px;">
                <p style="margin: 8px 0;"><strong>Resolution Type:</strong> <span id="confirm_resolution_type"></span></p>
                <p style="margin: 8px 0;"><strong>Notes:</strong> <span id="confirm_resolution_notes"></span></p>
            </div>

            <div class="modal-buttons" style="gap: 10px;">
                <button type="button" class="btn-cancel" onclick="closeConfirmationModal()" style="flex: 1;">Cancel</button>
                <button type="button" class="btn-submit" onclick="submitResolveForm()" style="flex: 1; background-color: #22c55e;">Confirm & Resolve</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const detailModal = document.getElementById('detailModal');
            const resolveModal = document.getElementById('resolveModal');
            const resolutionModal = document.getElementById('resolutionModal');
            const confirmationModal = document.getElementById('confirmationModal');
            const unresolveConfirmationModal = document.getElementById('unresolveConfirmationModal');

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

            if (resolutionModal) {
                resolutionModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeResolutionModal();
                    }
                });
            }

            if (confirmationModal) {
                confirmationModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeConfirmationModal();
                    }
                });
            }

            if (unresolveConfirmationModal) {
                unresolveConfirmationModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeUnresolveConfirmationModal();
                    }
                });
            }

            // Handle resolve form submission
            const resolveForm = document.getElementById('resolveForm');
            if (resolveForm) {
                resolveForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    showConfirmationModal();
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

        function openResolutionModalFromButton(button) {
            const resolutionAction = button.getAttribute('data-resolution-action');
            const resolutionNotes = button.getAttribute('data-resolution-notes');

            const resolutionModal = document.getElementById('resolutionModal');
            if (!resolutionModal) {
                console.error('Resolution modal not found');
                return;
            }

            // Format resolution action text
            const actionText = resolutionAction ?
                resolutionAction.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) :
                'Unknown';

            document.getElementById('resolution_action_display').textContent = actionText;
            document.getElementById('resolution_notes_display').textContent = resolutionNotes || 'No notes provided';

            resolutionModal.classList.add('show');
        }

        function closeResolutionModal() {
            const resolutionModal = document.getElementById('resolutionModal');
            if (resolutionModal) {
                resolutionModal.classList.remove('show');
            }
        }

        function openResolveModalFromButton(button) {
            const disputeId = button.getAttribute('data-dispute-id');
            const agreementId = button.getAttribute('data-agreement-id');
            const projectTitle = button.getAttribute('data-project-title');
            const amount = button.getAttribute('data-amount');

            if (!disputeId || !agreementId) {
                console.error('Missing required dispute or agreement ID');
                return;
            }

            document.getElementById('dispute_id').value = disputeId;
            document.getElementById('dispute_agreement_id').value = agreementId;
            document.getElementById('dispute_project_title').textContent = projectTitle || 'Unknown Project';
            document.getElementById('dispute_amount').textContent = 'RM ' + parseFloat(amount).toFixed(2);
            document.getElementById('resolveModal').classList.add('show');
        }

        function closeResolveModal() {
            document.getElementById('resolveModal').classList.remove('show');
        }

        function showConfirmationModal() {
            const resolutionSelect = document.getElementById('resolution');
            const notesTextarea = document.getElementById('resolution_notes');
            const confirmationModal = document.getElementById('confirmationModal');

            if (!resolutionSelect.value) {
                alert('Please select a resolution type');
                return;
            }

            // Get the selected option text
            const selectedOption = resolutionSelect.options[resolutionSelect.selectedIndex];
            const resolutionText = selectedOption.text;
            const notesText = notesTextarea.value || 'No notes provided';

            // Update confirmation details
            document.getElementById('confirm_resolution_type').textContent = resolutionText;
            document.getElementById('confirm_resolution_notes').textContent = notesText;

            // Show confirmation modal
            confirmationModal.classList.add('show');
        }

        function closeConfirmationModal() {
            document.getElementById('confirmationModal').classList.remove('show');
        }

        function submitResolveForm() {
            document.getElementById('resolveForm').submit();
        }

        function openUnresolveConfirmationModal(button) {
            const disputeId = button.getAttribute('data-dispute-id');
            const agreementId = button.getAttribute('data-agreement-id');

            if (!disputeId || !agreementId) {
                console.error('Missing required dispute or agreement ID');
                return;
            }

            document.getElementById('unresolve_dispute_id').textContent = disputeId;
            document.getElementById('unresolve_dispute_id_input').value = disputeId;
            document.getElementById('unresolve_agreement_id_input').value = agreementId;

            document.getElementById('unresolveConfirmationModal').classList.add('show');
        }

        function closeUnresolveConfirmationModal() {
            document.getElementById('unresolveConfirmationModal').classList.remove('show');
        }

        function submitUnresolveForm() {
            document.getElementById('unresolveForm').submit();
        }
    </script>
</body>

</html>