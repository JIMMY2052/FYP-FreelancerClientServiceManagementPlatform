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
    $freelancer_id = isset($_POST['freelancer_id']) ? intval($_POST['freelancer_id']) : null;
    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : null;
    $client_signature = isset($_POST['signature']) ? $_POST['signature'] : null;

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
    $verify_sql = "SELECT ja.ApplicationID, ja.JobID, ja.FreelancerID, j.Title, j.Budget, j.Description, 
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
    $client_name = $app_data['CompanyName'];
    $freelancer_name = $app_data['FreelancerFirstName'] . ' ' . $app_data['FreelancerLastName'];
    $job_title = $app_data['Title'];
    $job_budget = $app_data['Budget'];
    $job_desc = $app_data['Description'];

    // Create agreement record in database
    $status = 'signed_by_client';
    $signed_date = date('Y-m-d');
    $terms = "• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.";
    $scope = $job_desc;
    $deliverables = "To be completed upon milestone deliveries as agreed.";
    $signature_path = 'signature_client_' . $application_id . '_' . time();

    $agreement_sql = "INSERT INTO agreement (ClientName, FreelancerName, ProjectTitle, ProjectDetail, PaymentAmount, Status, SignedDate, Terms, Scope, Deliverables, SignaturePath) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $agreement_stmt = $conn->prepare($agreement_sql);

    if (!$agreement_stmt) {
        $_SESSION['error'] = "Database prepare error: " . $conn->error;
        error_log("Agreement prepare error: " . $conn->error);
        header("Location: agreementClient.php?application_id=" . $application_id);
        exit();
    }

    $agreement_stmt->bind_param('ssssdssssss', $client_name, $freelancer_name, $job_title, $job_desc, $job_budget, $status, $signed_date, $terms, $scope, $deliverables, $signature_path);

    if (!$agreement_stmt->execute()) {
        $_SESSION['error'] = "Error creating agreement record: " . $agreement_stmt->error;
        error_log("Agreement execute error: " . $agreement_stmt->error);
        error_log("SQL: " . $agreement_sql);
        error_log("Params: $client_name, $freelancer_name, $job_title, $job_desc, $job_budget, $status, $signed_date");
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
    require_once '../vendor/autoload.php';
    require_once '../vendor/tecnickcom/tcpdf/tcpdf.php';

    // Create PDF object using TCPDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set document properties
    $pdf->SetCreator('Freelancer Client Service Management Platform');
    $pdf->SetAuthor('FYP Platform');
    $pdf->SetTitle('Agreement - ' . $job_title);
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
    $pdf->Cell(0, 8, 'Gig: ' . $job_title, 0, 1, 'L', true);
    $pdf->Ln(4);

    // ===== PROJECT DETAILS IF PROVIDED =====
    if (!empty($job_desc)) {
        $pdf->SetFont('times', '', 10);
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->MultiCell(0, 5, $job_desc, 0, 'L', false);
        $pdf->Ln(4);
    }

    // ===== INTRODUCTORY PARAGRAPH =====
    $pdf->SetFont('times', '', 10);
    $pdf->SetTextColor(0, 0, 0); // Black
    $pdf->SetFillColor(249, 249, 249);
    $pdf->SetDrawColor(0, 0, 0); // Black
    $pdf->SetLineWidth(0.3);

    $introText = 'This Services Agreement shall become effective on date (the "Execution Date") and is subject to the terms and conditions stated below between ' . $freelancer_name . ' (the "Service Provider") and ' . $client_name . ' (the "Client"), collectively referred to as the "Parties".';

    // Add border box around intro paragraph
    $pdf->SetXY(15, $pdf->GetY());
    $pdf->MultiCell(0, 5, $introText, 'LRB', 'L', true);
    $pdf->Ln(6);

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

    // ===== PAYMENT TERMS SECTION =====
    $pdf->SetTextColor(0, 0, 0); // Black
    $pdf->SetFont('times', 'B', 12);
    $pdf->SetDrawColor(0, 0, 0); // Black
    $pdf->SetLineWidth(0.3);
    $pdf->Cell(0, 8, '2.  PAYMENT TERMS', 'B', 1, 'L', false);

    $paymentText = 'Project Value: RM ' . number_format($job_budget, 2) . "\n\n" .
        'Payment Schedule: To be completed upon milestone deliveries as agreed.';

    $pdf->SetFont('times', '', 10);
    $pdf->SetTextColor(0, 0, 0); // Black
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
    $pdf->Cell(0, 8, '3.  TERMS & CONDITIONS', 'B', 1, 'L', false);

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
    $pdf->Cell(0, 8, '4.  SIGNATURES', 'B', 1, 'L', false);

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
    $uploads_dir = '../uploads/agreements/';
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }

    $pdf_filename = 'agreement_' . $application_id . '_' . time() . '.pdf';
    $pdf_path = $uploads_dir . $pdf_filename;
    $pdf->Output($pdf_path, 'F');

    // Find or create conversation between client and freelancer
    $conv_sql = "SELECT ConversationID FROM conversation WHERE ClientID = ? AND FreelancerID = ?";
    $conv_stmt = $conn->prepare($conv_sql);
    $conv_stmt->bind_param('ii', $client_id, $freelancer_id);
    $conv_stmt->execute();
    $conv_result = $conv_stmt->get_result();

    $conversation_id = null;

    if ($conv_result->num_rows > 0) {
        $conv_data = $conv_result->fetch_assoc();
        $conversation_id = $conv_data['ConversationID'];
    } else {
        // Create new conversation
        $create_conv_sql = "INSERT INTO conversation (ClientID, FreelancerID, CreatedAt) VALUES (?, ?, NOW())";
        $create_conv_stmt = $conn->prepare($create_conv_sql);
        $create_conv_stmt->bind_param('ii', $client_id, $freelancer_id);

        if ($create_conv_stmt->execute()) {
            $conversation_id = $conn->insert_id;
        }
    }

    // Send message with PDF attachment to freelancer
    if ($conversation_id) {
        $message_text = "I have signed the agreement for the project \"" . $job_title . "\". Please review and sign to proceed. The agreement is attached below.\n\n";
        $message_text .= "Agreement Link: " . $_SERVER['HTTP_HOST'] . "/page/freelancer_agreement_approval.php?agreement_id=" . $agreement_id;
        $attachment_path = $pdf_filename;
        $attachment_type = 'agreement';

        $msg_sql = "INSERT INTO message (ConversationID, SenderID, MessageText, AttachmentPath, AttachmentType, Timestamp) 
                    VALUES (?, ?, ?, ?, ?, NOW())";

        $msg_stmt = $conn->prepare($msg_sql);
        $msg_stmt->bind_param('iisss', $conversation_id, $client_id, $message_text, $attachment_path, $attachment_type);

        if ($msg_stmt->execute()) {
            $conn->close();
            $_SESSION['success'] = "Agreement signed successfully! PDF sent to freelancer for review.";
            header("Location: my_applications.php");
            exit();
        } else {
            $_SESSION['error'] = "Error sending agreement to freelancer: " . $msg_stmt->error;
        }
    } else {
        $_SESSION['error'] = "Error creating conversation. Please try again.";
        error_log("Conversation creation failed or message send failed");
    }

    $conn->close();
    $_SESSION['success'] = "Agreement signed successfully! Redirecting...";
    header("Location: my_applications.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: my_applications.php");
    exit();
}
