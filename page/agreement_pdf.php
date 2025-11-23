<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
include 'config.php';

// Only allow POST requests for security
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method. PDF download requires form submission.");
}

// Verify user is logged in
if (!isset($_SESSION['user_type'])) {
    $_SESSION['error'] = "You must be logged in to create an agreement.";
    header("Location: login.php");
    exit();
}

// Check if agreement ID is provided
if (!isset($_POST['agreement_id'])) {
    die("Agreement ID is required.");
}

$agreement_id = intval($_POST['agreement_id']);

// Get database connection
$conn = getDBConnection();

// Fetch agreement data from database
$sql = "SELECT * FROM agreement WHERE AgreementID = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param('i', $agreement_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Agreement not found.");
}

$agreement = $result->fetch_assoc();
$stmt->close();

// Get current user information (freelancer or client)
$freelancer_name = "Freelancer Name";
$client_name = "Client Name";

if (isset($_SESSION['freelancer_id'])) {
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
}

if (isset($_SESSION['client_id'])) {
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
}

$conn->close();

// Check if TCPDF is installed
$tcpdf_path = __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
if (!file_exists($tcpdf_path)) {
    die("TCPDF library not found. Please run: composer require tecnickcom/tcpdf");
}

// Include TCPDF directly
require_once $tcpdf_path;

// Create PDF object using TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document properties
$pdf->SetCreator('Freelancer Client Service Management Platform');
$pdf->SetAuthor('FYP Platform');
$pdf->SetTitle('Agreement - ' . $agreement['ProjectTitle']);
$pdf->SetSubject('Project Agreement');

// Set margins
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('Helvetica', '', 11);

// ===== HEADER SECTION =====
$pdf->SetFont('Helvetica', 'B', 24);
$pdf->SetTextColor(26, 26, 26);
$pdf->Cell(0, 15, $agreement['ProjectTitle'], 0, 1, 'L');

$pdf->SetFont('Helvetica', '', 11);
$pdf->SetTextColor(123, 143, 163);
$pdf->MultiCell(0, 10, $agreement['ProjectDetail'], 0, 'L');

// Add spacing
$pdf->Ln(5);

// Header info section with right-aligned details
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetTextColor(123, 143, 163);

// Create table for header info
$headerData = array();
$headerData[] = array('Offer from:', $freelancer_name);
$headerData[] = array('To:', $client_name);
$headerData[] = array('Date:', date('F j, Y', strtotime($agreement['SignedDate'])));

// Get starting X position
$startX = $pdf->GetX();
$pageWidth = $pdf->GetPageWidth();
$margin = 15;

// Print right-aligned header info
$lineHeight = 5;
$labelWidth = 60;
$valueWidth = 80;

foreach ($headerData as $item) {
    // Right align - calculate position
    $x = $pageWidth - $margin - $valueWidth;

    $pdf->SetXY($x - $labelWidth, $pdf->GetY());
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($labelWidth, $lineHeight, $item[0], 0, 0, 'R');

    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetTextColor(26, 26, 26);
    $pdf->Cell($valueWidth, $lineHeight, $item[1], 0, 1, 'L');

    $pdf->SetTextColor(123, 143, 163);
}

// Add border line
$pdf->SetDrawColor(229, 231, 235);
$pdf->Line(15, $pdf->GetY() + 3, $pdf->GetPageWidth() - 15, $pdf->GetY() + 3);
$pdf->Ln(8);

// ===== SECTION 1: SCOPE OF WORK =====
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->SetTextColor(26, 26, 26);
$pdf->Cell(10, 10, '1', 0, 0, 'C');
$pdf->Cell(0, 10, 'Scope of Work', 0, 1, 'L');

$pdf->SetFont('Helvetica', '', 11);
$pdf->SetTextColor(90, 107, 125);
$pdf->MultiCell(0, 6, $agreement['Scope'], 0, 'L');
$pdf->Ln(5);

// ===== SECTION 2: DELIVERABLES & TIMELINE =====
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->SetTextColor(26, 26, 26);
$pdf->Cell(10, 10, '2', 0, 0, 'C');
$pdf->Cell(0, 10, 'Deliverables & Timeline', 0, 1, 'L');

$pdf->SetFont('Helvetica', '', 11);
$pdf->SetTextColor(90, 107, 125);
$pdf->MultiCell(0, 6, $agreement['Deliverables'], 0, 'L');
$pdf->Ln(5);

// ===== SECTION 3: PAYMENT TERMS =====
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->SetTextColor(26, 26, 26);
$pdf->Cell(10, 10, '3', 0, 0, 'C');
$pdf->Cell(0, 10, 'Payment Terms', 0, 1, 'L');

// Payment box background
$pdf->SetFillColor(249, 250, 251);
$pdf->SetDrawColor(229, 231, 235);
$boxStartY = $pdf->GetY();

$pdf->Cell(0, 8, 'Total Project Price: RM ' . number_format($agreement['PaymentAmount'], 2), 0, 1, 'L', true);

$pdf->SetFont('Helvetica', '', 10);
$pdf->SetTextColor(90, 107, 125);
$pdf->MultiCell(0, 6, 'Payment will be released in milestones upon completion of deliverables.', 0, 'L', true);

$pdf->Ln(3);

// ===== SECTION 4: TERMS & CONDITIONS =====
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->SetTextColor(26, 26, 26);
$pdf->Cell(10, 10, '4', 0, 0, 'C');
$pdf->Cell(0, 10, 'Terms & Conditions', 0, 1, 'L');

$pdf->SetFont('Helvetica', '', 11);
$pdf->SetTextColor(90, 107, 125);
$pdf->MultiCell(0, 6, $agreement['Terms'], 0, 'L');

// ===== SECTION 5: DIGITAL SIGNATURE =====
$pdf->Ln(10);
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->SetTextColor(26, 26, 26);
$pdf->Cell(10, 10, '5', 0, 0, 'C');
$pdf->Cell(0, 10, 'Freelancer Signature', 0, 1, 'L');

$pdf->Ln(5);

// Add signature image if it exists
$signaturePath = null;
if (!empty($agreement['SignaturePath'])) {
    $signaturePath = __DIR__ . '/../uploads/signatures/' . $agreement['SignaturePath'];
}

if ($signaturePath && file_exists($signaturePath)) {
    // Center the signature
    $pdf->SetY($pdf->GetY());

    // Signature box
    $pdf->SetDrawColor(26, 26, 26);
    $pdf->SetFillColor(255, 255, 255);
    $boxWidth = 80;
    $boxHeight = 50;

    // Calculate center position
    $pageWidth = $pdf->GetPageWidth();
    $centerX = ($pageWidth - $boxWidth) / 2;

    // Draw signature image
    $pdf->Image($signaturePath, $centerX, $pdf->GetY(), $boxWidth, $boxHeight);
    $pdf->SetY($pdf->GetY() + $boxHeight);

    $pdf->Ln(3);
}

// Signature line and name
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetTextColor(90, 107, 125);

// Draw signature line
$lineY = $pdf->GetY();
$pageWidth = $pdf->GetPageWidth();
$lineStartX = ($pageWidth - 100) / 2;
$lineEndX = $lineStartX + 100;

// Set position and draw line
$pdf->SetXY($lineStartX, $lineY);
$pdf->SetDrawColor(26, 26, 26);
$pdf->Line($lineStartX, $lineY, $lineEndX, $lineY);

$pdf->SetY($lineY + 3);

// Freelancer name
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetTextColor(26, 26, 26);
$freelancerSignatureName = !empty($agreement['FreelancerName']) ? $agreement['FreelancerName'] : 'Freelancer';
$pdf->SetX($lineStartX);
$pdf->Cell(100, 8, 'Freelancer Signature: ' . $freelancerSignatureName, 0, 1, 'C');

// Signature date
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetTextColor(123, 143, 163);
$pdf->SetX($lineStartX);
$pdf->Cell(100, 6, 'Signed on: ' . date('F j, Y', strtotime($agreement['SignedDate'])), 0, 1, 'C');

// Add footer
$pdf->Ln(10);
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetTextColor(155, 160, 170);
$pdf->Cell(0, 10, 'Agreement ID: ' . $agreement_id . ' | Generated on ' . date('F j, Y \a\t H:i A'), 0, 0, 'C');

// Output PDF with clean filename
$projectTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $agreement['ProjectTitle']);
$filename = 'Agreement_' . $projectTitle . '_' . $agreement_id . '.pdf';
$pdf->Output($filename, 'D'); // 'D' = download to user's device
