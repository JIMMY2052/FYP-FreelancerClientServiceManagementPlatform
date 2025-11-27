 <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    session_start();
    require_once '../config.php';

    // Check if user is logged in as client
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }

    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit();
    }

    // Get POST data
    $gigId = isset($_POST['gig_id']) ? intval($_POST['gig_id']) : 0;
    $rushDelivery = isset($_POST['rush_delivery']) ? intval($_POST['rush_delivery']) : 0;
    $agreedToTerms = isset($_POST['agreed_terms']) ? intval($_POST['agreed_terms']) : 0;

    if (!$gigId || !$agreedToTerms) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }

    $clientId = $_SESSION['user_id'];

    // Return redirect response to client agreement page
    echo json_encode([
        'success' => true,
        'message' => 'Proceeding to agreement...',
        'redirect' => 'gigAgreement.php',
        'method' => 'POST',
        'gig_id' => $gigId,
        'rush_delivery' => $rushDelivery
    ]);
    exit();

    // The following code is kept as reference but no longer executes
    // All gig payment processing now goes through gigAgreement.php and gigAgreement_process.php

    $conn = getDBConnection();

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Fetch gig and freelancer details
        $sql = "SELECT g.GigID, g.FreelancerID, g.Title, g.Description, g.Price, g.RushDeliveryPrice, 
                   g.DeliveryTime, g.RushDelivery, g.RevisionCount, g.Status,
                   f.FirstName, f.LastName, f.Email as FreelancerEmail,
                   c.CompanyName, c.Email as ClientEmail
            FROM gig g
            JOIN freelancer f ON g.FreelancerID = f.FreelancerID
            JOIN client c ON c.ClientID = ?
            WHERE g.GigID = ? AND g.Status = 'active'";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $clientId, $gigId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Gig not found or unavailable');
        }

        $gig = $result->fetch_assoc();
        $freelancerId = $gig['FreelancerID'];
        $stmt->close();

        // Calculate total amount
        $basePrice = floatval($gig['Price']);
        $rushFee = ($rushDelivery && !empty($gig['RushDeliveryPrice'])) ? floatval($gig['RushDeliveryPrice']) : 0;
        $totalAmount = $basePrice + $rushFee;
        $deliveryTime = $rushDelivery && !empty($gig['RushDelivery']) ? intval($gig['RushDelivery']) : intval($gig['DeliveryTime']);

        // 2. Check client wallet balance
        $sql = "SELECT WalletID, Balance, LockedBalance FROM wallet WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $clientId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Create wallet if doesn't exist
            $stmt->close();
            $sql = "INSERT INTO wallet (UserID, Balance, LockedBalance, LastUpdated) VALUES (?, 0, 0, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $clientId);
            $stmt->execute();
            $stmt->close();

            throw new Exception('Insufficient wallet balance. Please top up your wallet.');
        }

        $wallet = $result->fetch_assoc();
        $walletId = $wallet['WalletID'];
        $currentBalance = floatval($wallet['Balance']);
        $stmt->close();

        if ($currentBalance < $totalAmount) {
            throw new Exception('Insufficient wallet balance. You need RM ' . number_format($totalAmount - $currentBalance, 2) . ' more.');
        }

        // 3. Check if freelancer has a wallet, create if not
        $sql = "SELECT WalletID FROM wallet WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $freelancerId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $sql = "INSERT INTO wallet (UserID, Balance, LockedBalance, LastUpdated) VALUES (?, 0, 0, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $freelancerId);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt->close();
        }

        // 4. Create agreement record
        $clientName = $gig['CompanyName'];
        $freelancerName = $gig['FirstName'] . ' ' . $gig['LastName'];
        $projectTitle = $gig['Title'];
        $projectDetail = $gig['Description'];
        $status = 'to_accept'; // Freelancer needs to accept
        $clientSignedDate = date('Y-m-d H:i:s');
        $expiredDate = date('Y-m-d H:i:s', strtotime('+7 days')); // 7 days for freelancer to accept

        $terms = "1. The freelancer will deliver the service as described within {$deliveryTime} day(s).\n";
        $terms .= "2. The client will pay RM " . number_format($totalAmount, 2) . " which is held in escrow.\n";
        $terms .= "3. Payment will be released upon successful delivery and client approval.\n";
        $terms .= "4. The service includes {$gig['RevisionCount']} revision(s).";

        $scope = "Gig-based service: " . $projectTitle;
        $deliverables = "As described in the gig: " . substr($projectDetail, 0, 200);

        $sql = "INSERT INTO agreement (FreelancerID, ClientID, ClientName, FreelancerName, ProjectTitle, 
                                   ProjectDetail, PaymentAmount, Status, ClientSignedDate, ExpiredDate, 
                                   Terms, Scope, Deliverables, DeliveryTime) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'iissssdssssssi',
            $freelancerId,
            $clientId,
            $clientName,
            $freelancerName,
            $projectTitle,
            $projectDetail,
            $totalAmount,
            $status,
            $clientSignedDate,
            $expiredDate,
            $terms,
            $scope,
            $deliverables,
            $deliveryTime
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to create agreement: ' . $stmt->error);
        }

        $agreementId = $stmt->insert_id;
        $stmt->close();

        if (!$agreementId) {
            throw new Exception('Failed to get agreement ID');
        }

        // 5. Create escrow record
        $sql = "INSERT INTO escrow (OrderID, PayerID, PayeeID, Amount, Status, CreatedAt) 
            VALUES (?, ?, ?, ?, 'hold', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiid', $agreementId, $clientId, $freelancerId, $totalAmount);

        if (!$stmt->execute()) {
            throw new Exception('Failed to create escrow record: ' . $stmt->error);
        }

        $escrowId = $stmt->insert_id;
        $stmt->close();

        // 6. Update client wallet - deduct from balance and add to locked balance
        $sql = "UPDATE wallet SET Balance = Balance - ?, LockedBalance = LockedBalance + ?, LastUpdated = NOW() 
            WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ddi', $totalAmount, $totalAmount, $clientId);

        if (!$stmt->execute() || $stmt->affected_rows === 0) {
            throw new Exception('Failed to update client wallet');
        }
        $stmt->close();

        // 7. Record wallet transaction for client
        $transactionDesc = "Payment for '{$projectTitle}' - Funds locked in escrow (Agreement #{$agreementId})";
        $sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Description, CreatedAt) 
            VALUES (?, 'payment', ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ids', $walletId, $totalAmount, $transactionDesc);
        $stmt->execute();
        $stmt->close();

        // 8. Create notification for freelancer
        $notificationMsg = "New gig order from {$clientName} for '{$projectTitle}'. Payment of RM " . number_format($totalAmount, 2) . " is held in escrow. Please review and accept the agreement.";
        $sql = "INSERT INTO notifications (UserID, UserType, Message, RelatedType, RelatedID, CreatedAt, IsRead) 
            VALUES (?, 'freelancer', ?, 'gig_order', ?, NOW(), 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isi', $freelancerId, $notificationMsg, $agreementId);
        $stmt->execute();
        $stmt->close();

        // 9. Update gig status to 'processing' if needed
        $sql = "UPDATE gig SET Status = 'processing' WHERE GigID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $gigId);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment successful! Funds locked in escrow.',
            'agreement_id' => $agreementId,
            'escrow_id' => $escrowId,
            'amount' => $totalAmount
        ]);
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();

        error_log('Gig payment error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } finally {
        $conn->close();
    }
    ?>
