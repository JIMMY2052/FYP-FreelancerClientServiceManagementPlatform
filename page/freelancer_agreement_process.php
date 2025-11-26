<?php
session_start();

// Check if user is logged in and is a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    http_response_code(403);
    exit('Unauthorized');
}

require_once 'config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

$freelancer_id = $_SESSION['user_id'];
$agreement_id = isset($_POST['agreement_id']) ? intval($_POST['agreement_id']) : 0;
$signature_data = isset($_POST['signature_data']) ? $_POST['signature_data'] : '';

if ($agreement_id === 0 || empty($signature_data)) {
    http_response_code(400);
    exit('Invalid request');
}

// Validate that the freelancer owns this agreement
$conn = getDBConnection();
$sql = "SELECT 
            a.AgreementID,
            a.FreelancerID,
            a.ClientID,
            a.ProjectTitle,
            a.PaymentAmount,
            a.Status,
            a.CreatedDate,
            a.ClientSignedDate,
            a.ExpiredDate,
            a.DeliveryTime,
            a.ClientSignaturePath,
            a.Terms,
            a.Scope,
            a.Deliverables,
            a.ProjectDetail,
            CONCAT(f.FirstName, ' ', f.LastName) as FreelancerName,
            c.CompanyName as ClientName
        FROM agreement a
        JOIN freelancer f ON a.FreelancerID = f.FreelancerID
        JOIN client c ON a.ClientID = c.ClientID
        WHERE a.AgreementID = ? AND a.FreelancerID = ?";

// Also fetch client signature from database if the path exists
$client_signature_path_from_db = null;

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $agreement_id, $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$agreement = $result->fetch_assoc();
$stmt->close();

if (!$agreement) {
    $conn->close();
    http_response_code(404);
    exit('Agreement not found');
}

// Check if agreement status is valid for signing
if ($agreement['Status'] !== 'to_accept') {
    $conn->close();
    http_response_code(400);
    exit('Agreement is not awaiting your signature. Current status: ' . $agreement['Status']);
}

// Check if agreement has expired
$now = new DateTime();
$expiration = new DateTime($agreement['ExpiredDate']);
if ($now > $expiration) {
    $conn->close();
    http_response_code(400);
    exit('Agreement has expired');
}

// Create uploads directory if it doesn't exist
$uploads_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/agreements/';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// Process signature image
$signature_base64 = str_replace('data:image/png;base64,', '', $signature_data);
$signature_binary = base64_decode($signature_base64);

if ($signature_binary === false) {
    $conn->close();
    http_response_code(400);
    exit('Invalid signature data');
}

// Save signature image
$signature_filename = 'signature_f' . $freelancer_id . '_a' . $agreement_id . '_' . time() . '.png';
$signature_path = $uploads_dir . $signature_filename;
file_put_contents($signature_path, $signature_binary);

// Generate new PDF with both signatures
try {
    // Create new PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Freelancer Client Service Management Platform');
    $pdf->SetAuthor('FYP Platform');
    $pdf->SetTitle('Agreement - ' . $agreement['ProjectTitle']);
    $pdf->SetSubject('Project Agreement');

    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);

    // Add page
    $pdf->AddPage();

    // ===== MODERN CLEAN HEADER =====
    $pdf->SetFont('times', 'B', 32);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 15, 'Service Contract', 0, 1, 'L');

    // Decorative line
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(8);

    // ===== INFO GRID SECTION =====
    $pdf->SetFont('times', '', 10);

    // Row 1 - Freelancer and Client
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.5);

    $pdf->SetFont('times', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(55, 6, 'FREELANCER', 0, 0, 'L', true);
    $pdf->SetFont('times', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 6, ' : ' . $agreement['FreelancerName'], 0, 1, 'L', true);

    $pdf->SetFont('times', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(55, 6, 'CLIENT', 0, 0, 'L', true);
    $pdf->SetFont('times', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 6, ' : ' . $agreement['ClientName'], 0, 1, 'L', true);

    // Row 2 - Date and Amount
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetFont('times', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(55, 6, 'DATE SIGNED', 0, 0, 'L', true);
    $pdf->SetFont('times', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 6, ' : ' . date('M d, Y'), 0, 1, 'L', true);

    $pdf->SetFont('times', 'B', 11);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(55, 6, 'PROJECT VALUE', 0, 0, 'L', true);
    $pdf->SetFont('times', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 6, ' : RM ' . number_format($agreement['PaymentAmount'], 2), 0, 1, 'L', true);

    $pdf->Ln(6);

    // ===== PROJECT TITLE SECTION =====
    $pdf->SetFont('times', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);
    $pdf->Cell(0, 8, 'Project: ' . $agreement['ProjectTitle'], 0, 1, 'L', true);
    $pdf->Ln(4);

    // ===== PROJECT DETAILS IF PROVIDED =====
    if (!empty($agreement['ProjectDetail'])) {
        $pdf->SetFont('times', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, $agreement['ProjectDetail'], 0, 'L', false);
        $pdf->Ln(4);
    }

    // ===== INTRODUCTORY PARAGRAPH =====
    $pdf->SetFont('times', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(249, 249, 249);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);

    $introText = 'This Services Agreement shall become effective on date (the "Execution Date") and is subject to the terms and conditions stated below between ' . $agreement['FreelancerName'] . ' (the "Service Provider") and ' . $agreement['ClientName'] . ' (the "Client"), collectively referred to as the "Parties".';

    // Add border box around intro paragraph
    $pdf->SetXY(15, $pdf->GetY());
    $pdf->MultiCell(0, 5, $introText, 'LRB', 'L', true);
    $pdf->Ln(6);

    // ===== SCOPE OF WORK SECTION =====
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('times', 'B', 12);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);
    $pdf->Cell(0, 8, '1.  SCOPE OF WORK', 'B', 1, 'L', false);

    $pdf->SetFont('times', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(255, 255, 255);
    $pdf->SetLineWidth(0);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(0, 5, !empty($agreement['Scope']) ? $agreement['Scope'] : $agreement['ProjectDetail'], 0, 'L', false);
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
    $pdf->MultiCell(0, 5, !empty($agreement['Deliverables']) ? $agreement['Deliverables'] : 'As agreed upon during project discussion', 0, 'L', false);
    $pdf->Ln(5);

    // ===== PAYMENT TERMS SECTION =====
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('times', 'B', 12);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);
    $pdf->Cell(0, 8, '3.  PAYMENT TERMS', 'B', 1, 'L', false);

    $paymentText = 'Project Value: RM ' . number_format($agreement['PaymentAmount'], 2) . "\n\n" .
        'Delivery Time: ' . $agreement['DeliveryTime'] . ' days' . "\n\n" .
        'Payment Schedule: To be completed upon milestone deliveries as agreed.';

    $pdf->SetFont('times', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(255, 255, 255);
    $pdf->SetLineWidth(0);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(0, 5, $paymentText, 0, 'L', false);
    $pdf->Ln(5);

    // ===== TERMS & CONDITIONS SECTION =====
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('times', 'B', 12);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);
    $pdf->Cell(0, 8, '4.  TERMS & CONDITIONS', 'B', 1, 'L', false);

    $termsText = !empty($agreement['Terms']) ? $agreement['Terms'] :
        "• Both parties agree to the terms outlined above.\n" .
        "• Payment will be processed upon project completion and mutual agreement.\n" .
        "• Either party may terminate this agreement with written notice.\n" .
        "• Both parties agree to maintain confidentiality of project details.\n" .
        "• Any disputes will be resolved through communication or mediation.\n";

    $pdf->SetFont('times', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(255, 255, 255);
    $pdf->SetLineWidth(0);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(0, 5, $termsText, 0, 'L', false);
    $pdf->Ln(5);

    // ===== SIGNATURE SECTION =====
    $pdf->Ln(3);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('times', 'B', 12);
    $pdf->SetDrawColor(0, 0, 0);
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
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($signatureBoxWidth, 5, 'FREELANCER SIGNATURE', 0, 1, 'C');

    // Signature box
    $boxY = $pdf->GetY();
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.5);
    $pdf->SetXY($leftX, $boxY);
    $pdf->Rect($leftX, $boxY, $signatureBoxWidth, $signatureHeight);

    // Embed freelancer signature
    $pdf->SetXY($leftX, $boxY);
    if (file_exists($signature_path)) {
        $pdf->Image($signature_path, $leftX + 5, $boxY + 5, $signatureBoxWidth - 10, 30, 'PNG');
    }

    // ===== CLIENT SIGNATURE (RIGHT) =====
    $pdf->SetXY($rightX, $currentY);
    $pdf->SetFont('times', 'B', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($signatureBoxWidth, 5, 'CLIENT SIGNATURE', 0, 1, 'C');

    // Signature box
    $clientBoxY = $pdf->GetY();
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.5);
    $pdf->SetXY($rightX, $clientBoxY);
    $pdf->Rect($rightX, $clientBoxY, $signatureBoxWidth, $signatureHeight);

    // Embed client signature if provided
    $pdf->SetXY($rightX, $clientBoxY);
    if ($agreement['ClientSignaturePath'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $agreement['ClientSignaturePath'])) {
        $pdf->Image($_SERVER['DOCUMENT_ROOT'] . $agreement['ClientSignaturePath'], $rightX + 5, $clientBoxY + 5, $signatureBoxWidth - 10, 30, 'PNG');
    } else {
        // Placeholder if no signature found
        $pdf->SetXY($rightX + 5, $clientBoxY + $signatureHeight / 2 - 5);
        $pdf->SetFont('times', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($signatureBoxWidth - 10, 5, '[Pending Signature]', 0, 1, 'C');
    }

    // Move to below the signature boxes
    $newY = max($boxY, $clientBoxY) + $signatureHeight + 2;
    $pdf->SetY($newY);

    // Freelancer name
    $pdf->SetXY($leftX, $pdf->GetY());
    $pdf->SetFont('times', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($signatureBoxWidth, 5, $agreement['FreelancerName'], 0, 1, 'C');

    // Freelancer date
    $pdf->SetXY($leftX, $pdf->GetY());
    $pdf->SetFont('times', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($signatureBoxWidth, 4, 'Date: ' . date('M d, Y'), 0, 1, 'C');

    // Client name
    $pdf->SetXY($rightX, $newY);
    $pdf->SetFont('times', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($signatureBoxWidth, 5, $agreement['ClientName'], 0, 1, 'C');

    // Client date
    $pdf->SetXY($rightX, $pdf->GetY());
    $pdf->SetFont('times', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    if ($agreement['ClientSignedDate']) {
        $pdf->Cell($signatureBoxWidth, 4, 'Date: ' . date('M d, Y', strtotime($agreement['ClientSignedDate'])), 0, 1, 'C');
    } else {
        $pdf->Cell($signatureBoxWidth, 4, 'Date: ___________', 0, 1, 'C');
    }

    // Generate filename based on agreement ID
    $pdf_filename = 'agreement_' . $agreement_id . '.pdf';
    $pdf_path = '/uploads/agreements/' . $pdf_filename;
    $full_pdf_path = $_SERVER['DOCUMENT_ROOT'] . $pdf_path;

    // Save PDF (overwrites previous version if it exists)
    $pdf->Output($full_pdf_path, 'F');

    error_log("PDF generated for agreement #$agreement_id at $full_pdf_path");

    // Update agreement in database
    $signature_db_path = '/uploads/agreements/' . $signature_filename;
    $pdf_path_for_db = '/uploads/agreements/' . $pdf_filename;

    $update_sql = "UPDATE agreement 
                   SET Status = 'ongoing', 
                       FreelancerSignaturePath = ?,
                       FreelancerSignedDate = NOW(),
                       agreeementPath = ?
                   WHERE AgreementID = ? AND FreelancerID = ?";

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ssii', $signature_db_path, $pdf_path_for_db, $agreement_id, $freelancer_id);
    $success = $update_stmt->execute();
    $update_stmt->close();

    error_log("Agreement #$agreement_id updated: Status=ongoing, FreelancerSignaturePath=$signature_db_path, agreeementPath=$pdf_path_for_db");

    if ($success) {
        // Get conversation with client
        $client_id = $agreement['ClientID'];
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

        // Send message to client
        $message_sql = "INSERT INTO message (ConversationID, SenderID, ReceiverID, Content, AttachmentPath, AttachmentType, Timestamp, Status) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), 'unread')";

        $sender_id = 'f' . $freelancer_id;
        $receiver_id = 'c' . $client_id;
        $message_text = 'Agreement signed successfully! The agreement "' . $agreement['ProjectTitle'] . '" has been signed and is now active.';
        $attachment_path = $pdf_path_for_db;
        $attachment_type = 'application/pdf';

        $msg_stmt = $conn->prepare($message_sql);
        $msg_stmt->bind_param('isssss', $conversation_id, $sender_id, $receiver_id, $message_text, $attachment_path, $attachment_type);
        $msg_stmt->execute();
        $msg_stmt->close();

        $conn->close();

        // Redirect to agreement listing with success message
        header('Location: agreementListing.php?status=ongoing&signed=success');
        exit();
    } else {
        $conn->close();
        http_response_code(500);
        exit('Failed to update agreement');
    }
} catch (Exception $e) {
    $conn->close();
    http_response_code(500);
    exit('Error processing agreement: ' . $e->getMessage());
}
