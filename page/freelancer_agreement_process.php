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
            CONCAT(f.FirstName, ' ', f.LastName) as FreelancerName,
            c.CompanyName as ClientName
        FROM agreement a
        JOIN freelancer f ON a.FreelancerID = f.FreelancerID
        JOIN client c ON a.ClientID = c.ClientID
        WHERE a.AgreementID = ? AND a.FreelancerID = ?";

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
    $pdf->SetCreator('WorkSync Platform');
    $pdf->SetAuthor('WorkSync');
    $pdf->SetTitle('Signed Agreement - ' . $agreement['ProjectTitle']);

    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);

    // Add page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 15, 'SERVICE AGREEMENT', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 8, 'WorkSync Freelancer Platform', 0, 1, 'C');
    $pdf->Ln(5);

    // Agreement Details
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Agreement Details', 0, 1);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(50, 7, 'Agreement ID:', 0, 0);
    $pdf->Cell(0, 7, $agreement['AgreementID'], 0, 1);

    $pdf->Cell(50, 7, 'Project Title:', 0, 0);
    $pdf->MultiCell(0, 7, $agreement['ProjectTitle']);

    $pdf->Cell(50, 7, 'Payment Amount:', 0, 0);
    $pdf->Cell(0, 7, 'RM ' . number_format($agreement['PaymentAmount'], 2), 0, 1);

    $pdf->Cell(50, 7, 'Delivery Time:', 0, 0);
    $pdf->Cell(0, 7, $agreement['DeliveryTime'], 0, 1);

    $pdf->Cell(50, 7, 'Created Date:', 0, 0);
    $pdf->Cell(0, 7, date('M d, Y', strtotime($agreement['CreatedDate'])), 0, 1);

    $pdf->Ln(8);

    // Parties
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Parties', 0, 1);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(50, 7, 'Client:', 0, 0);
    $pdf->Cell(0, 7, $agreement['ClientName'], 0, 1);

    $pdf->Cell(50, 7, 'Freelancer:', 0, 0);
    $pdf->Cell(0, 7, $agreement['FreelancerName'], 0, 1);

    $pdf->Ln(10);

    // Signatures Section
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Signatures', 0, 1);

    $pdf->SetFont('helvetica', '', 10);

    // Two column layout for signatures
    $x_col1 = 15;
    $x_col2 = 105;
    $y_sig_start = $pdf->GetY();

    // Client signature column
    $pdf->SetXY($x_col1, $y_sig_start);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(80, 7, 'Client Signature', 0, 1);

    $pdf->SetXY($x_col1, $y_sig_start + 8);
    if ($agreement['ClientSignaturePath'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $agreement['ClientSignaturePath'])) {
        $pdf->Image($_SERVER['DOCUMENT_ROOT'] . $agreement['ClientSignaturePath'], $x_col1 + 5, $pdf->GetY(), 60, 30);
        $pdf->SetY($pdf->GetY() + 35);
    } else {
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(70, 7, '[Client Signature]', 1);
    }

    $pdf->SetXY($x_col1, $pdf->GetY() + 5);
    $pdf->SetFont('helvetica', '', 9);
    if ($agreement['ClientSignedDate']) {
        $pdf->Cell(80, 6, 'Signed: ' . date('M d, Y', strtotime($agreement['ClientSignedDate'])), 0, 1);
    }

    // Freelancer signature column
    $pdf->SetXY($x_col2, $y_sig_start);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(80, 7, 'Freelancer Signature', 0, 1);

    $pdf->SetXY($x_col2, $y_sig_start + 8);
    if (file_exists($signature_path)) {
        $pdf->Image($signature_path, $x_col2 + 5, $pdf->GetY(), 60, 30);
        $pdf->SetY($pdf->GetY() + 35);
    }

    $pdf->SetXY($x_col2, $pdf->GetY() + 5);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(80, 6, 'Signed: ' . date('M d, Y'), 0, 1);

    // Generate filename based on agreement ID (same as client side)
    $pdf_filename = 'agreement_' . $agreement_id . '.pdf';
    $pdf_path = '/uploads/agreements/' . $pdf_filename;
    $full_pdf_path = $_SERVER['DOCUMENT_ROOT'] . $pdf_path;

    // Save PDF (overwrites previous version if it exists)
    $pdf->Output($full_pdf_path, 'F');

    // Update agreement in database
    $signature_db_path = '/uploads/agreements/' . $signature_filename;
    $update_sql = "UPDATE agreement 
                   SET Status = 'ongoing', 
                       FreelancerSignaturePath = ?,
                       FreelancerSignedDate = NOW()
                   WHERE AgreementID = ? AND FreelancerID = ?";

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('sii', $signature_db_path, $agreement_id, $freelancer_id);
    $success = $update_stmt->execute();
    $update_stmt->close();

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
        $attachment_path = $pdf_path;
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
