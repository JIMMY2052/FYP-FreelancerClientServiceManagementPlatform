<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'client') {
    $_SESSION['error'] = "Only clients can access this page.";
    header("Location: login.php");
    exit();
}

// Include database configuration
include 'config.php';

// Get database connection
$conn = getDBConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $gig_id = isset($_POST['gig_id']) ? intval($_POST['gig_id']) : null;
    $freelancer_id = isset($_POST['freelancer_id']) ? intval($_POST['freelancer_id']) : null;
    $rush_delivery = isset($_POST['rush_delivery']) ? intval($_POST['rush_delivery']) : 0;
    $client_signature = isset($_POST['signature']) ? $_POST['signature'] : null;
    $client_signed_name = isset($_POST['client_name']) ? trim($_POST['client_name']) : null;

    // Get revision info from session (set during payment_details.php)
    $extra_revisions = isset($_SESSION['extra_revisions']) ? intval($_SESSION['extra_revisions']) : 0;
    $additional_revision_price = isset($_SESSION['additional_revision_price']) ? floatval($_SESSION['additional_revision_price']) : 0;

    $client_id = $_SESSION['user_id'];

    // Validate required fields
    if (!$gig_id || !$freelancer_id || !$client_signed_name) {
        $_SESSION['error'] = "Missing required information.";
        header("Location: gig/browse_gigs.php");
        exit();
    }

    if (empty($client_signature)) {
        $_SESSION['error'] = "Client signature is required.";
        header("Location: gigAgreement.php");
        exit();
    }

    // Fetch gig and client data
    $gig_sql = "SELECT g.GigID, g.FreelancerID, g.Title, g.Description, g.Price, g.RushDeliveryPrice, 
                       g.DeliveryTime, g.RushDelivery, g.RevisionCount,
                       f.FirstName as FreelancerFirstName, f.LastName as FreelancerLastName,
                       c.ClientID, c.CompanyName
                FROM gig g
                JOIN freelancer f ON g.FreelancerID = f.FreelancerID
                JOIN client c ON c.ClientID = ?
                WHERE g.GigID = ? AND g.Status = 'active'";

    $gig_stmt = $conn->prepare($gig_sql);
    $gig_stmt->bind_param('ii', $client_id, $gig_id);
    $gig_stmt->execute();
    $gig_result = $gig_stmt->get_result();

    if ($gig_result->num_rows === 0) {
        $_SESSION['error'] = "Gig not found or you don't have permission.";
        header("Location: gig/browse_gigs.php");
        exit();
    }

    $gig = $gig_result->fetch_assoc();
    $gig_stmt->close();

    // Calculate amounts
    $base_price = floatval($gig['Price']);
    $rush_fee = ($rush_delivery && !empty($gig['RushDeliveryPrice'])) ? floatval($gig['RushDeliveryPrice']) : 0;
    $revision_fee = ($extra_revisions > 0 && $additional_revision_price > 0) ? ($extra_revisions * $additional_revision_price) : 0;
    $total_amount = $base_price + $rush_fee + $revision_fee;
    $total_revisions = intval($gig['RevisionCount']) + $extra_revisions;
    $delivery_time = $rush_delivery && !empty($gig['RushDelivery']) ? intval($gig['RushDelivery']) : intval($gig['DeliveryTime']);

    // Prepare agreement data
    $client_name = $client_signed_name; // Use the name client enters
    $freelancer_name = $gig['FreelancerFirstName'] . ' ' . $gig['FreelancerLastName'];
    $project_title = $gig['Title'];
    $project_detail = $gig['Description'];
    $status = 'to_accept'; // Freelancer needs to accept
    $client_signed_date = date('Y-m-d H:i:s');
    $expired_date = date('Y-m-d H:i:s', strtotime('+1 days')); // 7 days for freelancer to accept

    $terms = "• The freelancer will deliver the gig-based service as described within {$delivery_time} day(s).\n";
    $terms .= "• The client will pay RM " . number_format($total_amount, 2) . " which is held in escrow.\n";
    $terms .= "• Payment will be released upon successful delivery and client approval.\n";
    $terms .= "• The service includes {$total_revisions} revision(s).\n";
    $terms .= "• Both parties agree to maintain professional conduct throughout the engagement.";

    $scope = "Gig-based service: " . $project_title;
    $deliverables = substr($project_detail, 0, 500);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // 1. Create agreement record
        $agreement_sql = "INSERT INTO agreement (FreelancerID, ClientID, ClientName, FreelancerName, ProjectTitle, 
                                               ProjectDetail, PaymentAmount, RemainingRevisions, Status, ClientSignedDate, ExpiredDate, 
                                               Terms, Scope, Deliverables, DeliveryTime) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $agreement_stmt = $conn->prepare($agreement_sql);
        // Use the total revision count (including additional revisions)
        $agreement_stmt->bind_param(
            'iissssdissssssi',
            $freelancer_id,
            $client_id,
            $client_name,
            $freelancer_name,
            $project_title,
            $project_detail,
            $total_amount,
            $total_revisions,
            $status,
            $client_signed_date,
            $expired_date,
            $terms,
            $scope,
            $deliverables,
            $delivery_time
        );

        if (!$agreement_stmt->execute()) {
            throw new Exception('Failed to create agreement: ' . $agreement_stmt->error);
        }

        $agreement_id = $conn->insert_id;
        $agreement_stmt->close();

        if (!$agreement_id) {
            throw new Exception('Failed to get agreement ID');
        }

        // 2. Check and update client wallet
        $wallet_check_sql = "SELECT WalletID, Balance, LockedBalance FROM wallet WHERE UserID = ?";
        $wallet_check_stmt = $conn->prepare($wallet_check_sql);
        $wallet_check_stmt->bind_param('i', $client_id);
        $wallet_check_stmt->execute();
        $wallet_result = $wallet_check_stmt->get_result();

        if ($wallet_result->num_rows === 0) {
            // Create wallet if doesn't exist
            $create_wallet_sql = "INSERT INTO wallet (UserID, Balance, LockedBalance) VALUES (?, 0.00, 0.00)";
            $create_wallet_stmt = $conn->prepare($create_wallet_sql);
            $create_wallet_stmt->bind_param('i', $client_id);
            $create_wallet_stmt->execute();
            $wallet_id = $conn->insert_id;
            $current_balance = 0.00;
            $current_locked = 0.00;
            $create_wallet_stmt->close();
        } else {
            $wallet_data = $wallet_result->fetch_assoc();
            $wallet_id = $wallet_data['WalletID'];
            $current_balance = floatval($wallet_data['Balance']);
            $current_locked = floatval($wallet_data['LockedBalance']);
        }
        $wallet_check_stmt->close();

        // Check if client has sufficient balance
        if ($current_balance < $total_amount) {
            throw new Exception('Insufficient wallet balance. You need RM ' . number_format($total_amount - $current_balance, 2) . ' more.');
        }

        // 3. Check freelancer wallet and create if needed
        $freelancer_wallet_sql = "SELECT WalletID FROM wallet WHERE UserID = ?";
        $freelancer_wallet_stmt = $conn->prepare($freelancer_wallet_sql);
        $freelancer_wallet_stmt->bind_param('i', $freelancer_id);
        $freelancer_wallet_stmt->execute();
        $freelancer_wallet_result = $freelancer_wallet_stmt->get_result();

        if ($freelancer_wallet_result->num_rows === 0) {
            $create_freelancer_wallet_sql = "INSERT INTO wallet (UserID, Balance, LockedBalance) VALUES (?, 0.00, 0.00)";
            $create_freelancer_wallet_stmt = $conn->prepare($create_freelancer_wallet_sql);
            $create_freelancer_wallet_stmt->bind_param('i', $freelancer_id);
            $create_freelancer_wallet_stmt->execute();
            $create_freelancer_wallet_stmt->close();
        }
        $freelancer_wallet_stmt->close();

        // 4. Create escrow record
        $escrow_sql = "INSERT INTO escrow (OrderID, PayerID, PayeeID, Amount, Status, CreatedAt) 
                       VALUES (?, ?, ?, ?, 'hold', NOW())";
        $escrow_stmt = $conn->prepare($escrow_sql);
        $escrow_stmt->bind_param('iiid', $agreement_id, $client_id, $freelancer_id, $total_amount);

        if (!$escrow_stmt->execute()) {
            throw new Exception('Failed to create escrow record: ' . $escrow_stmt->error);
        }

        $escrow_id = $conn->insert_id;
        $escrow_stmt->close();

        // 5. Update client wallet - deduct from balance and add to locked balance
        $new_balance = $current_balance - $total_amount;
        $new_locked = $current_locked + $total_amount;

        $update_wallet_sql = "UPDATE wallet SET Balance = ?, LockedBalance = ? WHERE WalletID = ?";
        $update_wallet_stmt = $conn->prepare($update_wallet_sql);
        $update_wallet_stmt->bind_param('ddi', $new_balance, $new_locked, $wallet_id);

        if (!$update_wallet_stmt->execute()) {
            throw new Exception('Failed to update client wallet');
        }
        $update_wallet_stmt->close();

        // 6. Record wallet transaction for client
        $transaction_desc = "Payment for gig '{$project_title}' - Funds locked in escrow (Agreement #{$agreement_id})";
        $transaction_sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Status, Description, ReferenceID, CreatedAt) 
                            VALUES (?, 'payment', ?, 'completed', ?, ?, NOW())";
        $transaction_stmt = $conn->prepare($transaction_sql);
        $ref_id = "escrow_" . $escrow_id;
        $transaction_stmt->bind_param('idss', $wallet_id, $total_amount, $transaction_desc, $ref_id);
        $transaction_stmt->execute();
        $transaction_stmt->close();

        // 7. Save client signature as PNG file
        $signature_base64 = str_replace('data:image/png;base64,', '', $client_signature);
        $signature_binary = base64_decode($signature_base64);

        $base_dir = dirname(dirname(__FILE__));
        $uploads_dir = $base_dir . '/uploads/agreements/';

        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0755, true);
        }

        $signature_filename = 'signature_c' . $client_id . '_a' . $agreement_id . '_' . time() . '.png';
        $signature_path = $uploads_dir . $signature_filename;
        $client_signature_path = '/uploads/agreements/' . $signature_filename;

        if ($signature_binary !== false) {
            file_put_contents($signature_path, $signature_binary);
        }

        // 8. Update agreement with signature path
        $update_agreement_sql = "UPDATE agreement SET ClientSignaturePath = ? WHERE AgreementID = ?";
        $update_agreement_stmt = $conn->prepare($update_agreement_sql);
        $update_agreement_stmt->bind_param('si', $client_signature_path, $agreement_id);
        $update_agreement_stmt->execute();
        $update_agreement_stmt->close();

        // 9. Generate PDF
        require_once '../vendor/autoload.php';
        require_once '../vendor/tecnickcom/tcpdf/tcpdf.php';

        // Define PDF filename and path
        $pdf_filename = 'gig_agreement_' . $agreement_id . '_' . time() . '.pdf';
        $pdf_path_for_db = '/uploads/agreements/' . $pdf_filename;

        // Create PDF object using TCPDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document properties
        $pdf->SetCreator('Freelancer Client Service Management Platform');
        $pdf->SetAuthor('FYP Platform');
        $pdf->SetTitle('WorkSyng Gig Agreement - ' . $project_title);
        $pdf->SetSubject('Gig Service Agreement');

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // Add a page
        $pdf->AddPage();

        // ===== MODERN CLEAN HEADER =====
        $pdf->SetFont('times', 'B', 32);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell(0, 15, 'Service Contract', 0, 1, 'L');

        // Decorative line
        $pdf->SetDrawColor(0, 0, 0); // Black
        $pdf->SetLineWidth(0.3);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(8);

        // ===== INFO GRID SECTION =====
        $pdf->SetFont('times', '', 10);

        // Row 1
        $pdf->SetFillColor(255, 255, 255); // White background
        $pdf->SetDrawColor(0, 0, 0); // Black border
        $pdf->SetLineWidth(0.5);

        // Column 1: Freelancer
        $pdf->SetFont('times', 'B', 9);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell(55, 6, 'FREELANCER', 0, 0, 'L', true);
        $pdf->SetFont('times', '', 9);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell(0, 6, ' : ' . $freelancer_name, 0, 1, 'L', true);

        // Continue with client info
        $pdf->SetFont('times', 'B', 9);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell(55, 6, 'CLIENT', 0, 0, 'L', true);
        $pdf->SetFont('times', '', 9);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell(0, 6, ' : ' . $gig['CompanyName'], 0, 1, 'L', true);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('times', 'B', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(55, 6, 'DATE CREATED', 0, 0, 'L', true);
        $pdf->SetFont('times', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 6, ' : ' . date('M d, Y'), 0, 1, 'L', true);

        $pdf->SetFont('times', 'B', 11);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell(55, 6, 'PROJECT VALUE', 0, 0, 'L', true);
        $pdf->SetFont('times', 'B', 9);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell(0, 6, ' : RM ' . number_format($total_amount, 2), 0, 1, 'L', true);

        $pdf->Ln();

        // ===== GIG TITLE SECTION =====
        $pdf->SetFont('times', 'B', 14);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->SetDrawColor(0, 0, 0); // Black
        $pdf->SetLineWidth(0.3);
        $pdf->Cell(0, 8, 'Gig Title: ' . $project_title, 0, 1, 'L', true);
        $pdf->Ln(4);



        // ===== SCOPE OF WORK SECTION =====
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->SetFont('times', 'B', 12);
        $pdf->SetDrawColor(0, 0, 0); // Black
        $pdf->SetLineWidth(0.3);
        $pdf->Cell(0, 8, '1.  SCOPE OF WORK', 'B', 1, 'L', false);

        $pdf->SetFont('times', '', 10);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->SetDrawColor(255, 255, 255);
        $pdf->SetLineWidth(0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(0, 5, $project_detail, 0, 'L', false);
        $pdf->Ln(5);

        // ===== DELIVERABLES SECTION =====
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('times', 'B', 12);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);
        $pdf->Cell(0, 8, '2.  DELIVERABLES', 'B', 1, 'L', false);

        $pdf->SetFont('times', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(255, 255, 255);
        $pdf->SetLineWidth(0);
        $pdf->SetFillColor(255, 255, 255);
        $deliverableText = 'Delivery Time: ' . $delivery_time . ' day(s)' . "\n" .
            'Revisions Included: ' . $gig['RevisionCount'] . "\n";
        if ($extra_revisions > 0) {
            $deliverableText .= 'Additional Revisions: ' . $extra_revisions . "\n" .
                'Total Revisions: ' . $total_revisions . "\n";
        }
        $deliverableText .= 'The date that need to be complete will be calculated from the date the freelancer accepts this agreement.';
        $pdf->MultiCell(0, 5, $deliverableText, 0, 'L', false);
        $pdf->Ln(5);

        // ===== PAYMENT TERMS SECTION =====
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('times', 'B', 12);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);
        $pdf->Cell(0, 8, '3.  PAYMENT TERMS', 'B', 1, 'L', false);


        if ($rush_delivery) {
            $paymentText .= 'Rush Delivery Fee: RM ' . number_format($rush_fee, 2) . "\n";
        }
        $paymentText .= 'Total Amount: RM ' . number_format($total_amount, 2) . "\n" .
            'Payment Status: Held in escrow until delivery';

        $pdf->SetFont('times', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(255, 255, 255);
        $pdf->SetLineWidth(0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(0, 5, $paymentText, 0, 'L', false);
        $pdf->Ln(5);

        // ===== TERMS & CONDITIONS SECTION =====
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->SetFont('times', 'B', 12);
        $pdf->SetDrawColor(0, 0, 0); // Black
        $pdf->SetLineWidth(0.3);
        $pdf->Cell(0, 8, '4.  TERMS & CONDITIONS', 'B', 1, 'L', false);

        $pdf->SetFont('times', '', 10);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->SetDrawColor(255, 255, 255);
        $pdf->SetLineWidth(0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(0, 5, $terms, 0, 'L', false);
        $pdf->Ln(5);

        // ===== SIGNATURE SECTION =====
        $pdf->Ln(3);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->SetFont('times', 'B', 12);
        $pdf->SetDrawColor(0, 0, 0); // Black
        $pdf->SetLineWidth(0.3);
        $pdf->Cell(0, 8, '5.  SIGNATURES', 'B', 1, 'L', false);

        $pdf->Ln(6);

        // Dual signature layout
        $signatureBoxWidth = 50;
        $leftX = 15;
        $rightX = 15 + $signatureBoxWidth + 20;
        $signatureHeight = 35;
        $currentY = $pdf->GetY();

        // ===== FREELANCER SIGNATURE (LEFT) =====
        $pdf->SetXY($leftX, $currentY);
        $pdf->SetFont('times', 'B', 10);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell($signatureBoxWidth, 5, 'FREELANCER SIGNATURE', 0, 1, 'C');

        // Signature box
        $boxY = $pdf->GetY();
        $pdf->SetDrawColor(0, 0, 0); // Black
        $pdf->SetLineWidth(0.5);
        $pdf->SetXY($leftX, $boxY);
        $pdf->Rect($leftX, $boxY, $signatureBoxWidth, $signatureHeight);

        // ===== CLIENT SIGNATURE (RIGHT) =====
        $pdf->SetXY($rightX, $currentY);
        $pdf->SetFont('times', 'B', 10);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell($signatureBoxWidth, 5, 'CLIENT SIGNATURE', 0, 1, 'C');

        // Signature box
        $clientBoxY = $pdf->GetY();
        $pdf->SetDrawColor(0, 0, 0); // Black
        $pdf->SetLineWidth(0.5);
        $pdf->SetXY($rightX, $clientBoxY);
        $pdf->Rect($rightX, $clientBoxY, $signatureBoxWidth, $signatureHeight);

        // Decode and embed client signature if provided
        if (!empty($client_signature)) {
            $signature_data = str_replace('data:image/png;base64,', '', $client_signature);
            $signature_binary = base64_decode($signature_data);

            // Create temporary file for signature
            $temp_sig_file = tempnam(sys_get_temp_dir(), 'sig_');
            file_put_contents($temp_sig_file, $signature_binary);

            // Embed signature image in PDF (client side, right box)
            $pdf->Image($temp_sig_file, $rightX + 5, $clientBoxY + 5, $signatureBoxWidth - 10, 30, 'PNG');

            // Clean up temp file
            unlink($temp_sig_file);
        } else {
            // Placeholder text in middle if no signature
            $pdf->SetXY($rightX + 5, $clientBoxY + $signatureHeight / 2 - 5);
            $pdf->SetFont('times', '', 9);
            $pdf->SetTextColor(0, 0, 0); // Black
            $pdf->Cell($signatureBoxWidth - 10, 5, '[Client to Sign Here]', 0, 1, 'C');
        }

        // Move to below the signature boxes
        $newY = max($boxY, $clientBoxY) + $signatureHeight + 2;
        $pdf->SetY($newY);

        // Freelancer name (placeholder)
        $pdf->SetXY($leftX, $pdf->GetY());
        $pdf->SetFont('times', 'B', 9);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell($signatureBoxWidth, 5, '_________________', 0, 1, 'C');

        // Freelancer date placeholder
        $pdf->SetXY($leftX, $pdf->GetY());
        $pdf->SetFont('times', '', 8);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell($signatureBoxWidth, 4, 'Date: ___________', 0, 1, 'C');

        // Move down for client section
        $pdf->SetY($newY);

        // Client name
        $pdf->SetXY($rightX, $pdf->GetY());
        $pdf->SetFont('times', 'B', 9);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell($signatureBoxWidth, 5, $client_name, 0, 1, 'C');

        // Client date
        $pdf->SetXY($rightX, $pdf->GetY());
        $pdf->SetFont('times', '', 8);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->Cell($signatureBoxWidth, 4, 'Date: ' . date('M d, Y', strtotime($client_signed_date)), 0, 1, 'C');

        // Save PDF to server
        $pdf_path = $uploads_dir . $pdf_filename;

        try {
            $pdf->Output($pdf_path, 'F');
        } catch (Exception $e) {
            throw new Exception('Failed to generate PDF: ' . $e->getMessage());
        }

        // Update agreement with PDF path
        $update_pdf_sql = "UPDATE agreement SET agreeementPath = ? WHERE AgreementID = ?";
        $update_pdf_stmt = $conn->prepare($update_pdf_sql);
        $update_pdf_stmt->bind_param('si', $pdf_path_for_db, $agreement_id);
        $update_pdf_stmt->execute();
        $update_pdf_stmt->close();

        // 10. Create notification for freelancer
        $notificationMsg = "New gig order from {$client_name} for '{$project_title}'. Payment of RM " . number_format($total_amount, 2) . " is held in escrow. Please review and accept the agreement.";
        $notification_sql = "INSERT INTO notifications (UserID, UserType, Message, RelatedType, RelatedID, CreatedAt, IsRead) 
                             VALUES (?, 'freelancer', ?, 'gig_order', ?, NOW(), 0)";
        $notification_stmt = $conn->prepare($notification_sql);
        $notification_stmt->bind_param('isi', $freelancer_id, $notificationMsg, $agreement_id);
        $notification_stmt->execute();
        $notification_stmt->close();

        // 11. Send agreement to freelancer via message
        $conv_sql = "SELECT ConversationID FROM conversation 
                     WHERE (User1ID = ? AND User1Type = 'client' AND User2ID = ? AND User2Type = 'freelancer') OR
                            (User1ID = ? AND User1Type = 'freelancer' AND User2ID = ? AND User2Type = 'client')
                     LIMIT 1";

        $conv_stmt = $conn->prepare($conv_sql);
        $conv_stmt->bind_param('iiii', $client_id, $freelancer_id, $freelancer_id, $client_id);
        $conv_stmt->execute();
        $conv_result = $conv_stmt->get_result();
        $conversation = $conv_result->fetch_assoc();
        $conv_stmt->close();

        $conversation_id = null;

        if (!$conversation) {
            // Create new conversation
            $insert_conv_sql = "INSERT INTO conversation (User1ID, User1Type, User2ID, User2Type, CreatedAt) VALUES (?, 'client', ?, 'freelancer', NOW())";
            $insert_conv_stmt = $conn->prepare($insert_conv_sql);
            $insert_conv_stmt->bind_param('ii', $client_id, $freelancer_id);
            $insert_conv_stmt->execute();
            $conversation_id = $insert_conv_stmt->insert_id;
            $insert_conv_stmt->close();
        } else {
            $conversation_id = $conversation['ConversationID'];
        }

        // Send message to freelancer with agreement PDF
        $message_sql = "INSERT INTO message (ConversationID, SenderID, ReceiverID, Content, AttachmentPath, AttachmentType, Timestamp, Status) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), 'unread')";

        $sender_id = 'c' . $client_id;
        $receiver_id = 'f' . $freelancer_id;
        $message_text = 'New gig order: "' . $project_title . '" for RM ' . number_format($total_amount, 2) . '. Please review and sign the agreement to confirm.';
        $attachment_path = $pdf_path_for_db;
        $attachment_type = 'application/pdf';

        $msg_stmt = $conn->prepare($message_sql);
        $msg_stmt->bind_param('isssss', $conversation_id, $sender_id, $receiver_id, $message_text, $attachment_path, $attachment_type);
        $msg_stmt->execute();
        $msg_stmt->close();

        // Commit transaction
        $conn->commit();

        // Redirect to success page
        $_SESSION['success'] = 'Gig order confirmed! Funds have been locked in escrow.';
        $_SESSION['agreement_id'] = $agreement_id;
        header('Location: agreementListing.php');
        exit();
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();

        error_log('Gig agreement error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());

        $_SESSION['error'] = $e->getMessage();
        header('Location: gig/browse_gigs.php');
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: gig/browse_gigs.php");
    exit();
}
