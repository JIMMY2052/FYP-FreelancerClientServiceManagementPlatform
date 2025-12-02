<?php
session_start();

// Check if user is logged in and is a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if user is deleted
require_once 'checkUserStatus.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$raw_input = file_get_contents('php://input');
error_log("Raw input: " . $raw_input);

$input = json_decode($raw_input, true);
error_log("Decoded input: " . print_r($input, true));

if (!isset($input['agreement_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Agreement ID is required']);
    exit();
}

$agreement_id = intval($input['agreement_id']);
$freelancer_id = $_SESSION['user_id'];

require_once 'config.php';

try {
    $conn = getDBConnection();

    // Start transaction
    $conn->begin_transaction();

    // Verify agreement exists and belongs to the freelancer
    $verify_sql = "SELECT a.AgreementID, a.Status, a.ClientID, a.PaymentAmount, a.ProjectTitle 
                   FROM agreement a 
                   WHERE a.AgreementID = ? AND a.FreelancerID = ?";
    $verify_stmt = $conn->prepare($verify_sql);

    if (!$verify_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $verify_stmt->bind_param('ii', $agreement_id, $freelancer_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        $verify_stmt->close();
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Agreement not found']);
        exit();
    }

    $agreement = $verify_result->fetch_assoc();
    $client_id = $agreement['ClientID'];
    $payment_amount = floatval($agreement['PaymentAmount']);
    $project_title = $agreement['ProjectTitle'];
    $verify_stmt->close();

    // Check if agreement is in 'to_accept' status
    if ($agreement['Status'] !== 'to_accept') {
        $conn->rollback();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Only agreements pending acceptance can be declined']);
        exit();
    }

    // ===== REFUND ESCROW FUNDS TO CLIENT =====
    // Get escrow record
    $escrow_sql = "SELECT EscrowID, Amount, Status FROM escrow WHERE OrderID = ? AND Status = 'hold'";
    $escrow_stmt = $conn->prepare($escrow_sql);
    $escrow_stmt->bind_param('i', $agreement_id);
    $escrow_stmt->execute();
    $escrow_result = $escrow_stmt->get_result();

    if ($escrow_result->num_rows > 0) {
        $escrow = $escrow_result->fetch_assoc();
        $escrow_id = $escrow['EscrowID'];
        $escrow_amount = floatval($escrow['Amount']);
        $escrow_stmt->close();

        // Get client's wallet
        $wallet_sql = "SELECT WalletID, Balance, LockedBalance FROM wallet WHERE UserID = ?";
        $wallet_stmt = $conn->prepare($wallet_sql);
        $wallet_stmt->bind_param('i', $client_id);
        $wallet_stmt->execute();
        $wallet_result = $wallet_stmt->get_result();

        if ($wallet_result->num_rows > 0) {
            $wallet = $wallet_result->fetch_assoc();
            $wallet_id = $wallet['WalletID'];
            $current_balance = floatval($wallet['Balance']);
            $current_locked = floatval($wallet['LockedBalance']);
            $wallet_stmt->close();

            // Release funds: move from LockedBalance back to Balance
            $new_balance = $current_balance + $escrow_amount;
            $new_locked = $current_locked - $escrow_amount;

            // Ensure locked balance doesn't go negative
            if ($new_locked < 0) {
                $new_locked = 0;
            }

            // Update wallet
            $update_wallet_sql = "UPDATE wallet SET Balance = ?, LockedBalance = ? WHERE WalletID = ?";
            $update_wallet_stmt = $conn->prepare($update_wallet_sql);
            $update_wallet_stmt->bind_param('ddi', $new_balance, $new_locked, $wallet_id);

            if (!$update_wallet_stmt->execute()) {
                throw new Exception("Error updating wallet: " . $update_wallet_stmt->error);
            }
            $update_wallet_stmt->close();

            // Record wallet transaction for refund
            $transaction_type = 'refund';
            $transaction_status = 'completed';
            $transaction_desc = "Refund - Agreement declined: " . $project_title . " (Agreement #" . $agreement_id . ")";
            $transaction_ref = "escrow_refund_" . $escrow_id;

            $transaction_sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Status, Description, ReferenceID, CreatedAt) 
                               VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $transaction_stmt = $conn->prepare($transaction_sql);
            $transaction_stmt->bind_param('isdsss', $wallet_id, $transaction_type, $escrow_amount, $transaction_status, $transaction_desc, $transaction_ref);

            if (!$transaction_stmt->execute()) {
                throw new Exception("Error creating transaction: " . $transaction_stmt->error);
            }
            $transaction_stmt->close();

            // Update escrow status to 'refunded'
            $update_escrow_sql = "UPDATE escrow SET Status = 'refunded' WHERE EscrowID = ?";
            $update_escrow_stmt = $conn->prepare($update_escrow_sql);
            $update_escrow_stmt->bind_param('i', $escrow_id);

            if (!$update_escrow_stmt->execute()) {
                throw new Exception("Error updating escrow: " . $update_escrow_stmt->error);
            }
            $update_escrow_stmt->close();

            error_log("Escrow refunded: Agreement #$agreement_id - RM $escrow_amount returned to client #$client_id");
        } else {
            $wallet_stmt->close();
            error_log("Warning: Wallet not found for client #$client_id during escrow refund");
        }
    } else {
        $escrow_stmt->close();
        error_log("Warning: No escrow record found for Agreement #$agreement_id");
    }

    // Update agreement status to 'declined' (do NOT update any gig status)
    $update_sql = "UPDATE agreement SET Status = 'declined', FreelancerSignedDate = NOW() WHERE AgreementID = ?";
    $update_stmt = $conn->prepare($update_sql);

    if (!$update_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $update_stmt->bind_param('i', $agreement_id);

    if (!$update_stmt->execute()) {
        throw new Exception("Execute failed: " . $update_stmt->error);
    }

    $update_stmt->close();

    // Commit transaction
    $conn->commit();
    $conn->close();

    // Send response
    echo json_encode(['success' => true, 'message' => 'Agreement declined successfully and funds refunded to client']);
} catch (Exception $e) {
    // Rollback on error
    if (isset($conn)) {
        $conn->rollback();
        $conn->close();
    }
    error_log("Decline agreement error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit();
}
