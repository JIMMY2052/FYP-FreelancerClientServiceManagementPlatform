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

// Use freelancer and client names from the agreement record
$freelancer_name = !empty($agreement['FreelancerName']) ? $agreement['FreelancerName'] : 'Freelancer Name';
$client_name = !empty($agreement['ClientName']) ? $agreement['ClientName'] : 'Client Name';

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

// Create 2x2 info grid
$colWidth = 85;
$rowHeight = 18;

// Row 1
$pdf->SetFillColor(255, 255, 255); // White background
$pdf->SetDrawColor(0, 0, 0); // Black border
$pdf->SetLineWidth(0.5);

// Column 1: Freelancer
$pdf->SetFont('times', 'B', 9);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell(55, 6, 'FREELANCER', 0, 0, 'R', true);
$pdf->SetFont('times', '', 9);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell(0, 6, ' : ' . $freelancer_name, 0, 1, 'L', true);

// Continue with client info
$pdf->SetFont('times', 'B', 9);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell(55, 6, 'CLIENT', 0, 0, 'R', true);
$pdf->SetFont('times', '', 9);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell(0, 6, ' : ' . $client_name, 0, 1, 'L', true);

// Row 2
$pdf->SetFillColor(255, 255, 255); // White background
$pdf->SetFont('times', 'B', 9);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell(55, 6, 'DATE SIGNED', 0, 0, 'R', true);
$pdf->SetFont('times', '', 9);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell(0, 6, ' : ' . date('M d, Y', strtotime($agreement['SignedDate'] ?? 'now')), 0, 1, 'L', true);

$pdf->SetFont('times', 'B', 11);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell(55, 6, 'PROJECT VALUE', 0, 0, 'R', true);
$pdf->SetFont('times', 'B', 9);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell(0, 6, ' : RM ' . number_format($agreement['PaymentAmount'], 2), 0, 1, 'L', true);

$pdf->Ln(6);

// ===== PROJECT TITLE SECTION =====
$pdf->SetFont('times', 'B', 14);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->SetFillColor(240, 242, 247);
$pdf->SetDrawColor(0, 0, 0); // Black
$pdf->SetLineWidth(0.3);
$pdf->Cell(0, 8, 'Project: ' . $agreement['ProjectTitle'], 0, 1, 'L', true);
$pdf->Ln(4);

// ===== PROJECT DETAILS IF PROVIDED =====
if (!empty($agreement['ProjectDetail'])) {
    $pdf->SetFont('times', '', 10);
    $pdf->SetTextColor(0, 0, 0); // Black
    $pdf->MultiCell(0, 5, $agreement['ProjectDetail'], 0, 'L', false);
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

// ===== CONTENT SECTIONS WITH MODERN STYLING =====
$sectionColor = array(0, 0, 0); // Black
$textColor = array(0, 0, 0); // Black

// Enhanced function to create modern sections
function createModernSection($pdf, $number, $title, $content, $bgColor)
{
    // Section header without background
    $pdf->SetTextColor($bgColor[0], $bgColor[1], $bgColor[2]); // Black
    $pdf->SetFont('times', 'B', 12);
    $pdf->SetDrawColor($bgColor[0], $bgColor[1], $bgColor[2]); // Black
    $pdf->SetLineWidth(0.3);

    // Header cell - no fill
    $pdf->Cell(0, 8, $number . '.  ' . strtoupper($title), 'B', 1, 'L', false);

    // Content area
    $pdf->SetFont('times', '', 10);
    $pdf->SetTextColor(0, 0, 0); // Black
    $pdf->SetDrawColor(255, 255, 255);
    $pdf->SetLineWidth(0);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(0, 5, $content, 0, 'L', false);
    $pdf->Ln(5);
}

// SECTION 1: SCOPE OF WORK
createModernSection($pdf, '1', 'Scope of Work', $agreement['Scope'], $sectionColor);

// SECTION 2: DELIVERABLES
createModernSection($pdf, '2', 'Deliverables & Timeline', $agreement['Deliverables'], $sectionColor);

// SECTION 3: PAYMENT TERMS
$paymentText = 'Project Value: RM ' . number_format($agreement['PaymentAmount'], 2) . "\n\n" .
    'Payment Schedule: To be completed upon milestone deliveries as agreed.';
createModernSection($pdf, '3', 'Payment Terms', $paymentText, $sectionColor);

// SECTION 4: TERMS & CONDITIONS
createModernSection($pdf, '4', 'Terms & Conditions', $agreement['Terms'], $sectionColor);

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
$pageWidth = $pdf->GetPageWidth();
$leftX = 15;
$rightX = 15 + $signatureBoxWidth + 15;
$signatureHeight = 50;
$currentY = $pdf->GetY();

// ===== CONTRACTOR SIGNATURE (LEFT) =====
$pdf->SetXY($leftX, $currentY);
$pdf->SetFont('times', 'B', 10);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell($signatureBoxWidth, 5, 'CONTRACTOR SIGNATURE', 0, 1, 'C');

// Signature box
$boxY = $pdf->GetY();
$pdf->SetDrawColor(0, 0, 0); // Black
$pdf->SetLineWidth(0.5);
$pdf->SetXY($leftX, $boxY);
$pdf->Rect($leftX, $boxY, $signatureBoxWidth, $signatureHeight);

// Add signature image if it exists
$signaturePath = null;
if (!empty($agreement['SignaturePath'])) {
    $signaturePath = __DIR__ . '/../uploads/signatures/' . $agreement['SignaturePath'];
}

if ($signaturePath && file_exists($signaturePath)) {
    // Draw signature image centered in box
    $pdf->Image($signaturePath, $leftX + 5, $boxY + 5, $signatureBoxWidth - 10, 30);
}

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

// Placeholder text in middle
$pdf->SetXY($rightX + 5, $clientBoxY + $signatureHeight / 2 - 5);
$pdf->SetFont('times', '', 9);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell($signatureBoxWidth - 10, 5, '[Client to Sign Here]', 0, 1, 'C');

// Move to below the signature boxes
$newY = max($boxY, $clientBoxY) + $signatureHeight + 2;
$pdf->SetY($newY);

// Contractor name
$pdf->SetXY($leftX, $pdf->GetY());
$pdf->SetFont('times', 'B', 9);
$pdf->SetTextColor(0, 0, 0); // Black
$freelancerSignatureName = !empty($agreement['FreelancerName']) ? $agreement['FreelancerName'] : 'Contractor';
$pdf->Cell($signatureBoxWidth, 5, $freelancerSignatureName, 0, 1, 'C');

// Contractor date
$pdf->SetXY($leftX, $pdf->GetY());
$pdf->SetFont('times', '', 8);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell($signatureBoxWidth, 4, 'Date: ' . date('M d, Y', strtotime($agreement['SignedDate'] ?? 'now')), 0, 1, 'C');

// Client name placeholder
$pdf->SetXY($rightX, $newY);
$pdf->SetFont('times', 'B', 9);
$pdf->SetTextColor(0, 0, 0); // Black
$clientSignatureName = !empty($agreement['ClientName']) ? $agreement['ClientName'] : 'Client';
$pdf->Cell($signatureBoxWidth, 5, '_________________', 0, 1, 'C');

// Client date placeholder
$pdf->SetXY($rightX, $pdf->GetY());
$pdf->SetFont('times', '', 8);
$pdf->SetTextColor(0, 0, 0); // Black
$pdf->Cell($signatureBoxWidth, 4, 'Date: ___________', 0, 1, 'C');

// Output PDF with clean filename
$projectTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $agreement['ProjectTitle']);
$filename = 'Agreement_' . $projectTitle . '_' . $agreement_id . '.pdf';
$pdf->Output($filename, 'D'); // 'D' = download to user's device
