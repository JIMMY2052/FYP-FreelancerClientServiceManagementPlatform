<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    $application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : null;
    $freelancer_id = isset($_SESSION['agreement_freelancer_id']) ? intval($_SESSION['agreement_freelancer_id']) : null;
    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : null;
    $delivery_time = isset($_POST['delivery_time']) ? intval($_POST['delivery_time']) : 0;
    $client_signature = isset($_POST['signature']) ? $_POST['signature'] : null;
    $client_signed_name = isset($_POST['client_name']) ? trim($_POST['client_name']) : null;

    // Validate required fields
    if (!$application_id || !$freelancer_id || !$job_id) {
        $_SESSION['error'] = "Missing required information.";
        header("Location: my_applications.php");
        exit();
    }

    if (empty($client_signature)) {
        $_SESSION['error'] = "Client signature is required.";
        header("Location: agreementClient.php?application_id=" . $application_id);
        exit();
    }

    // Verify application ownership (client auth)
    $verify_sql = "SELECT ja.ApplicationID, ja.JobID, ja.FreelancerID, j.Title, j.Budget, j.Description, j.DeliveryTime,
                          c.ClientID, c.CompanyName, f.FirstName as FreelancerFirstName, f.LastName as FreelancerLastName
                   FROM job_application ja
                   JOIN job j ON ja.JobID = j.JobID
                   JOIN client c ON j.ClientID = c.ClientID
                   JOIN freelancer f ON ja.FreelancerID = f.FreelancerID
                   WHERE ja.ApplicationID = ? AND j.ClientID = ?";

    $verify_stmt = $conn->prepare($verify_sql);
    $client_id = $_SESSION['user_id'];
    $verify_stmt->bind_param('ii', $application_id, $client_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        $_SESSION['error'] = "Application not found or you don't have permission to sign this agreement.";
        header("Location: my_applications.php");
        exit();
    }

    $app_data = $verify_result->fetch_assoc();
    $client_name = !empty($client_signed_name) ? $client_signed_name : $app_data['CompanyName'];
    $freelancer_name = $app_data['FreelancerFirstName'] . ' ' . $app_data['FreelancerLastName'];
    $job_title = $app_data['Title'];
    $job_budget = $app_data['Budget'];
    $job_desc = $app_data['Description'];
    $delivery_time = intval($app_data['DeliveryTime']);

    // Create agreement record in database
    $status = 'to_accept';
    $client_signed_date = date('Y-m-d H:i:s');
    $expired_date = date('Y-m-d H:i:s', strtotime('+1 days')); // Agreement expires in 1 days from signing
    $terms = "• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.";
    $scope = $job_desc;
    $deliverables = "To be completed upon milestone deliveries as agreed.";

    // Generate PDF filename early for storage in database
    $pdf_filename = 'agreement_' . $application_id . '_' . time() . '.pdf';
    $client_signature_path = '/uploads/agreements/signature_c' . $client_id . '_a' . $application_id . '_' . time() . '.png'; // Store signature file path

    $agreement_sql = "INSERT INTO agreement (FreelancerID, ClientID, ClientName, FreelancerName, ProjectTitle, ProjectDetail, PaymentAmount, RemainingRevisions, Status, ClientSignedDate, ExpiredDate, Terms, Scope, Deliverables, DeliveryTime, ClientSignaturePath, agreeementPath) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $agreement_stmt = $conn->prepare($agreement_sql);

    if (!$agreement_stmt) {
        $_SESSION['error'] = "Database prepare error: " . $conn->error;
        error_log("Agreement prepare error: " . $conn->error);
        header("Location: agreementClient.php?application_id=" . $application_id);
        exit();
    }

    // Prepare PDF path for database storage
    $pdf_path_for_db = '/uploads/agreements/' . $pdf_filename;
    $default_revisions = 3; // Default number of revisions

    $agreement_stmt->bind_param('iissssdissssissss', $freelancer_id, $client_id, $client_name, $freelancer_name, $job_title, $job_desc, $job_budget, $default_revisions, $status, $client_signed_date, $expired_date, $terms, $scope, $deliverables, $delivery_time, $client_signature_path, $pdf_path_for_db);

    if (!$agreement_stmt->execute()) {
        $_SESSION['error'] = "Error creating agreement record: " . $agreement_stmt->error;
        error_log("Agreement execute error: " . $agreement_stmt->error);
        error_log("SQL: " . $agreement_sql);
        error_log("Params: $freelancer_id, $client_id, $client_name, $freelancer_name, $job_title, $job_desc, $job_budget, $status, $client_signed_date");
        header("Location: agreementClient.php?application_id=" . $application_id);
        exit();
    }

    $agreement_id = $conn->insert_id;
    error_log("Agreement created with ID: " . $agreement_id);

    if (!$agreement_id) {
        $_SESSION['error'] = "Failed to get agreement ID from database.";
        error_log("Failed to get agreement ID");
        header("Location: agreementClient.php?application_id=" . $application_id);
        exit();
    }

    error_log("Agreement record successfully created with ID: " . $agreement_id);

    // ===== ESCROW FUNCTIONALITY: Check wallet balance and lock funds =====
    // Get client's wallet
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
    if ($current_balance < $job_budget) {
        // Delete the agreement since we can't proceed without funds
        $delete_agreement_sql = "DELETE FROM agreement WHERE AgreementID = ?";
        $delete_stmt = $conn->prepare($delete_agreement_sql);
        $delete_stmt->bind_param('i', $agreement_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        $_SESSION['error'] = "Insufficient wallet balance. You have RM " . number_format($current_balance, 2) . " but need RM " . number_format($job_budget, 2) . ". Please <a href='payment/wallet.php'>top up your wallet</a> first.";
        error_log("Insufficient balance: Client $client_id has RM $current_balance but needs RM $job_budget");
        $conn->close();
        header("Location: agreementClient.php?application_id=" . $application_id);
        exit();
    }

    // Create escrow record to hold funds
    $escrow_status = 'hold';
    $escrow_created_at = date('Y-m-d H:i:s');
    $escrow_sql = "INSERT INTO escrow (OrderID, PayerID, PayeeID, Amount, Status, CreatedAt) VALUES (?, ?, ?, ?, ?, ?)";
    $escrow_stmt = $conn->prepare($escrow_sql);

    if (!$escrow_stmt) {
        $_SESSION['error'] = "Database error creating escrow: " . $conn->error;
        error_log("Escrow prepare error: " . $conn->error);
        $conn->close();
        header("Location: agreementClient.php?application_id=" . $application_id);
        exit();
    }

    $escrow_stmt->bind_param('iiidss', $agreement_id, $client_id, $freelancer_id, $job_budget, $escrow_status, $escrow_created_at);

    if (!$escrow_stmt->execute()) {
        $_SESSION['error'] = "Error creating escrow record: " . $escrow_stmt->error;
        error_log("Escrow execute error: " . $escrow_stmt->error);
        $escrow_stmt->close();
        $conn->close();
        header("Location: agreementClient.php?application_id=" . $application_id);
        exit();
    }

    $escrow_id = $conn->insert_id;
    $escrow_stmt->close();
    error_log("Escrow record created with ID: $escrow_id - RM $job_budget locked for Agreement #$agreement_id");

    // Update wallet: move funds from Balance to LockedBalance
    $new_balance = $current_balance - $job_budget;
    $new_locked = $current_locked + $job_budget;

    $update_wallet_sql = "UPDATE wallet SET Balance = ?, LockedBalance = ? WHERE WalletID = ?";
    $update_wallet_stmt = $conn->prepare($update_wallet_sql);
    $update_wallet_stmt->bind_param('ddi', $new_balance, $new_locked, $wallet_id);

    if (!$update_wallet_stmt->execute()) {
        $_SESSION['error'] = "Error updating wallet balance: " . $update_wallet_stmt->error;
        error_log("Wallet update error: " . $update_wallet_stmt->error);
        $update_wallet_stmt->close();
        $conn->close();
        header("Location: agreementClient.php?application_id=" . $application_id);
        exit();
    }
    $update_wallet_stmt->close();
    error_log("Wallet updated: Balance RM $current_balance -> RM $new_balance, Locked RM $current_locked -> RM $new_locked");

    // Record wallet transaction
    $transaction_type = 'payment';
    $transaction_status = 'completed';
    $transaction_desc = "Funds locked in escrow for project: " . $job_title . " (Agreement #" . $agreement_id . ")";
    $transaction_ref = "escrow_" . $escrow_id;

    $transaction_sql = "INSERT INTO wallet_transactions (WalletID, Type, Amount, Status, Description, ReferenceID, CreatedAt) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $transaction_stmt = $conn->prepare($transaction_sql);
    $transaction_stmt->bind_param('isdsss', $wallet_id, $transaction_type, $job_budget, $transaction_status, $transaction_desc, $transaction_ref);
    $transaction_stmt->execute();
    $transaction_stmt->close();
    error_log("Wallet transaction recorded for escrow lock");

    // Update job application status from 'pending' to 'accepted'
    $update_app_sql = "UPDATE job_application SET Status = 'accepted', UpdatedAt = NOW() WHERE ApplicationID = ?";
    $update_app_stmt = $conn->prepare($update_app_sql);
    $update_app_stmt->bind_param('i', $application_id);

    if ($update_app_stmt->execute()) {
        error_log("Job application #$application_id status updated to 'accepted'");
    } else {
        error_log("Failed to update job application status: " . $update_app_stmt->error);
    }
    $update_app_stmt->close();

    // Update job status to 'processing' when agreement is created and funds are locked
    $update_job_sql = "UPDATE job SET Status = 'processing' WHERE JobID = ?";
    $update_job_stmt = $conn->prepare($update_job_sql);
    $update_job_stmt->bind_param('i', $job_id);

    if ($update_job_stmt->execute()) {
        error_log("Job #$job_id status updated to 'processing'");
    } else {
        error_log("Failed to update job status to processing: " . $update_job_stmt->error);
    }
    $update_job_stmt->close();
    // ===== END ESCROW FUNCTIONALITY =====

    // Create uploads directory if it doesn't exist
    $base_dir = dirname(dirname(__FILE__));
    $uploads_dir = $base_dir . '/uploads/agreements/';

    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }

    // Save client signature as PNG file
    $signature_base64 = str_replace('data:image/png;base64,', '', $client_signature);
    $signature_binary = base64_decode($signature_base64);

    if ($signature_binary !== false) {
        $signature_filename = 'signature_c' . $client_id . '_a' . $application_id . '_' . time() . '.png';
        $signature_file_path = $uploads_dir . $signature_filename;
        file_put_contents($signature_file_path, $signature_binary);
        $client_signature_path = '/uploads/agreements/' . $signature_filename;
    }

    require_once '../vendor/autoload.php';
    require_once '../vendor/tecnickcom/tcpdf/tcpdf.php';

    // Create PDF object using TCPDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set document properties
    $pdf->SetCreator('Freelancer Client Service Management Platform');
    $pdf->SetAuthor('FYP Platform');
    $pdf->SetTitle('WorkSyng Agreement - ' . $job_title);
    $pdf->SetSubject('Project Agreement');

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
    $pdf->Cell(0, 6, ' : ' . $client_name, 0, 1, 'L', true);

    // Row 2
    $pdf->SetFillColor(255, 255, 255); // White background
    $pdf->SetFont('times', 'B', 9);
    $pdf->SetTextColor(0, 0, 0); // Black
    $pdf->Cell(55, 6, 'DATE SIGNED', 0, 0, 'L', true);
    $pdf->SetFont('times', '', 9);
    $pdf->SetTextColor(0, 0, 0); // Black
    $pdf->Cell(0, 6, ' : ' . date('M d, Y'), 0, 1, 'L', true);

    $pdf->SetFont('times', 'B', 11);
    $pdf->SetTextColor(0, 0, 0); // Black
    $pdf->Cell(55, 6, 'PROJECT VALUE', 0, 0, 'L', true);
    $pdf->SetFont('times', 'B', 9);
    $pdf->SetTextColor(0, 0, 0); // Black
    $pdf->Cell(0, 6, ' : RM ' . number_format($job_budget, 2), 0, 1, 'L', true);

    $pdf->Ln(6);

    // ===== PROJECT TITLE SECTION =====
    $pdf->SetFont('times', 'B', 14);
    $pdf->SetTextColor(0, 0, 0); // Black
    $pdf->SetDrawColor(0, 0, 0); // Black
    $pdf->SetLineWidth(0.3);
    $pdf->Cell(0, 8, 'Gig Title: ' . $job_title, 0, 1, 'L', true);
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
    $pdf->MultiCell(0, 5, $job_desc, 0, 'L', false);
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
    $pdf->MultiCell(0, 5, !empty($deliverables) ? $deliverables : 'As agreed upon during project discussion', 0, 'L', false);
    $pdf->Ln(5);

    // ===== PAYMENT TERMS SECTION =====
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('times', 'B', 12);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);
    $pdf->Cell(0, 8, '3.  PAYMENT TERMS', 'B', 1, 'L', false);

    $paymentText = 'Project Value: RM ' . number_format($job_budget, 2) . "\n" .
        'Delivery Time: ' . $delivery_time . ' days' . "\n" .
        'Payment Schedule: To be completed upon milestone deliveries as agreed.';

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

    $termsText = "• Both parties agree to the terms outlined above.\n";
    $termsText .= "• Payment will be processed upon project completion and mutual agreement.\n";
    $termsText .= "• Either party may terminate this agreement with written notice.\n";
    $termsText .= "• Both parties agree to maintain confidentiality of project details.\n";
    $termsText .= "• Any disputes will be resolved through communication or mediation.\n";

    $pdf->SetFont('times', '', 10);
    $pdf->SetTextColor(0, 0, 0); // Black
    $pdf->SetDrawColor(255, 255, 255);
    $pdf->SetLineWidth(0);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(0, 5, $termsText, 0, 'L', false);
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
    $signatureBoxWidth = 60;
    $leftX = 15;
    $rightX = 15 + $signatureBoxWidth + 15;
    $signatureHeight = 50;
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

    // Client name
    $pdf->SetXY($rightX, $newY);
    $pdf->SetFont('times', 'B', 9);
    $pdf->SetTextColor(0, 0, 0); // Black
    $pdf->Cell($signatureBoxWidth, 5, $client_name, 0, 1, 'C');

    // Client date
    $pdf->SetXY($rightX, $pdf->GetY());
    $pdf->SetFont('times', '', 8);
    $pdf->SetTextColor(0, 0, 0); // Black
    $pdf->Cell($signatureBoxWidth, 4, 'Date: ' . date('M d, Y'), 0, 1, 'C');

    // Save PDF to server
    $pdf_path = $uploads_dir . $pdf_filename;

    try {
        $pdf->Output($pdf_path, 'F');
    } catch (Exception $e) {
        $_SESSION['error'] = "Error creating PDF: " . $e->getMessage();
        error_log("PDF creation error: " . $e->getMessage());
        header("Location: agreementClient.php?application_id=" . $application_id);
        exit();
    }

    // Find or create conversation between client and freelancer
    $conv_sql = "SELECT ConversationID FROM conversation WHERE 
                 (User1ID = ? AND User1Type = 'client' AND User2ID = ? AND User2Type = 'freelancer') OR
                 (User1ID = ? AND User1Type = 'freelancer' AND User2ID = ? AND User2Type = 'client')";
    $conv_stmt = $conn->prepare($conv_sql);
    $conv_stmt->bind_param('iiii', $client_id, $freelancer_id, $freelancer_id, $client_id);
    $conv_stmt->execute();
    $conv_result = $conv_stmt->get_result();

    $conversation_id = null;

    if ($conv_result->num_rows > 0) {
        $conv_data = $conv_result->fetch_assoc();
        $conversation_id = $conv_data['ConversationID'];
    } else {
        // Create new conversation
        $create_conv_sql = "INSERT INTO conversation (User1ID, User1Type, User2ID, User2Type, CreatedAt) VALUES (?, 'client', ?, 'freelancer', NOW())";
        $create_conv_stmt = $conn->prepare($create_conv_sql);

        if (!$create_conv_stmt) {
            $_SESSION['error'] = "Database prepare error for conversation: " . $conn->error;
            error_log("Conversation prepare error: " . $conn->error);
            header("Location: agreementClient.php?application_id=" . $application_id);
            exit();
        }

        $create_conv_stmt->bind_param('ii', $client_id, $freelancer_id);

        if ($create_conv_stmt->execute()) {
            $conversation_id = $conn->insert_id;
            error_log("New conversation created with ID: " . $conversation_id);
        } else {
            $_SESSION['error'] = "Error creating conversation: " . $create_conv_stmt->error;
            error_log("Conversation execute error: " . $create_conv_stmt->error);
            header("Location: agreementClient.php?application_id=" . $application_id);
            exit();
        }
    }

    // Send message with PDF attachment to freelancer
    if ($conversation_id) {
        $message_text = "I have signed the agreement for the project \"" . $job_title . "\". Please review and sign to proceed. The agreement is attached below.\n\n";
        $attachment_path = $pdf_path_for_db;
        $attachment_type = 'application/pdf';

        $msg_sql = "INSERT INTO message (ConversationID, SenderID, ReceiverID, Content, AttachmentPath, AttachmentType, Timestamp, Status) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), 'to_accept')";

        $msg_stmt = $conn->prepare($msg_sql);
        $sender_id = 'c' . $client_id;  // Client sender: c{client_id}
        $receiver_id = 'f' . $freelancer_id;  // Freelancer receiver: f{freelancer_id}
        $msg_stmt->bind_param('isssss', $conversation_id, $sender_id, $receiver_id, $message_text, $attachment_path, $attachment_type);

        if ($msg_stmt->execute()) {
            $conn->close();
            $_SESSION['success'] = "Agreement signed successfully! PDF sent to freelancer for review.";
            header("Location: messages.php?freelancer_id=" . $freelancer_id);
            exit();
        } else {
            $_SESSION['error'] = "Error sending agreement to freelancer: " . $msg_stmt->error;
            error_log("Message execute error: " . $msg_stmt->error);
            $conn->close();
            header("Location: agreementClient.php?application_id=" . $application_id);
            exit();
        }
    } else {
        $_SESSION['error'] = "Error creating conversation. Please try again.";
        error_log("Conversation creation failed or message send failed");
        $conn->close();
        header("Location: agreementClient.php?application_id=" . $application_id);
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: my_applications.php");
    exit();
}
