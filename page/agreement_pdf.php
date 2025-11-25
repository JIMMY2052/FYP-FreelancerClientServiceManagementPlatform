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

// ===== MODERN CLEAN HEADER =====
$pdf->SetFont('times', 'B', 32);
$pdf->SetTextColor(45, 85, 255); // Modern blue
$pdf->Cell(0, 15, 'PROJECT AGREEMENT', 0, 1, 'L');

$pdf->SetFont('times', '', 10);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(0, 6, 'Professional Service Contract', 0, 1, 'L');

// Decorative line
$pdf->SetDrawColor(45, 85, 255);
$pdf->SetLineWidth(1);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(8);

// ===== INFO GRID SECTION =====
$pdf->SetFont('times', '', 10);

// Create 2x2 info grid
$colWidth = 85;
$rowHeight = 18;

// Row 1
$pdf->SetFillColor(245, 248, 255); // Light blue background
$pdf->SetDrawColor(200, 215, 255);
$pdf->SetLineWidth(0.5);

// Column 1: Freelancer
$pdf->SetFont('times', 'B', 9);
$pdf->SetTextColor(45, 85, 255);
$pdf->Cell($colWidth, 6, 'FREELANCER', 0, 0, 'L', true);
$pdf->SetFont('times', '', 10);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(0, 6, $freelancer_name, 0, 1, 'L', true);

// Continue with client info
$pdf->SetFont('times', 'B', 9);
$pdf->SetTextColor(45, 85, 255);
$pdf->Cell($colWidth, 6, 'CLIENT', 0, 0, 'L', true);
$pdf->SetFont('times', '', 10);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(0, 6, $client_name, 0, 1, 'L', true);

$pdf->Ln(2);

// Row 2
$pdf->SetFillColor(248, 250, 255);
$pdf->SetFont('times', 'B', 9);
$pdf->SetTextColor(45, 85, 255);
$pdf->Cell($colWidth, 6, 'DATE SIGNED', 0, 0, 'L', true);
$pdf->SetFont('times', '', 10);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell(0, 6, date('M d, Y', strtotime($agreement['SignedDate'] ?? 'now')), 0, 1, 'L', true);

$pdf->SetFont('times', 'B', 9);
$pdf->SetTextColor(45, 85, 255);
$pdf->Cell($colWidth, 6, 'PROJECT VALUE', 0, 0, 'L', true);
$pdf->SetFont('times', 'B', 11);
$pdf->SetTextColor(45, 85, 255);
$pdf->Cell(0, 6, 'RM ' . number_format($agreement['PaymentAmount'], 2), 0, 1, 'L', true);

$pdf->Ln(6);

// ===== PROJECT TITLE SECTION =====
$pdf->SetFont('times', 'B', 14);
$pdf->SetTextColor(30, 30, 30);
$pdf->SetFillColor(240, 242, 247);
$pdf->SetDrawColor(45, 85, 255);
$pdf->SetLineWidth(0.5);
$pdf->Cell(0, 8, 'Project: ' . $agreement['ProjectTitle'], 0, 1, 'L', true);
$pdf->Ln(4);

// ===== PROJECT DETAILS IF PROVIDED =====
if (!empty($agreement['ProjectDetail'])) {
    $pdf->SetFont('times', '', 10);
    $pdf->SetTextColor(50, 50, 50);
    $pdf->MultiCell(0, 5, $agreement['ProjectDetail'], 0, 'L', false);
    $pdf->Ln(4);
}

// ===== CONTENT SECTIONS WITH MODERN STYLING =====
$sectionColor = array(45, 85, 255); // Modern blue
$textColor = array(30, 30, 30);

// Enhanced function to create modern sections
function createModernSection($pdf, $number, $title, $content, $bgColor)
{
    // Section header without background
    $pdf->SetTextColor($bgColor[0], $bgColor[1], $bgColor[2]);
    $pdf->SetFont('times', 'B', 12);
    $pdf->SetDrawColor($bgColor[0], $bgColor[1], $bgColor[2]);
    $pdf->SetLineWidth(0.8);

    // Header cell - no fill
    $pdf->Cell(0, 8, $number . '.  ' . strtoupper($title), 'B', 1, 'L', false);

    // Content area
    $pdf->SetFont('times', '', 10);
    $pdf->SetTextColor(30, 30, 30);
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
$pdf->SetTextColor(45, 85, 255);
$pdf->SetFont('times', 'B', 12);
$pdf->SetDrawColor(45, 85, 255);
$pdf->SetLineWidth(0.8);
$pdf->Cell(0, 8, '5.  DIGITAL SIGNATURE', 'B', 1, 'L', false);

$pdf->Ln(6);

// Add signature image if it exists
$signaturePath = null;
if (!empty($agreement['SignaturePath'])) {
    $signaturePath = __DIR__ . '/../uploads/signatures/' . $agreement['SignaturePath'];
}

// Signature block layout
$blockWidth = 60;
$pageWidth = $pdf->GetPageWidth();
$centerX = ($pageWidth - $blockWidth) / 2;

if ($signaturePath && file_exists($signaturePath)) {
    // Draw signature image
    $pdf->Image($signaturePath, $centerX + 5, $pdf->GetY(), $blockWidth - 10, 28);
    $pdf->SetY($pdf->GetY() + 30);
}

// Signature line
$pdf->SetDrawColor(45, 85, 255);
$pdf->SetLineWidth(0.8);
$lineY = $pdf->GetY();
$pdf->Line($centerX, $lineY, $centerX + $blockWidth, $lineY);

$pdf->Ln(3);

// Freelancer name
$pdf->SetFont('times', 'B', 11);
$pdf->SetTextColor(30, 30, 30);
$freelancerSignatureName = !empty($agreement['FreelancerName']) ? $agreement['FreelancerName'] : 'Freelancer';
$pdf->SetX($centerX);
$pdf->Cell($blockWidth, 6, $freelancerSignatureName, 0, 1, 'C');

// Date
$pdf->SetFont('times', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetX($centerX);
$pdf->Cell($blockWidth, 5, 'Date: ' . date('M d, Y', strtotime($agreement['SignedDate'] ?? 'now')), 0, 1, 'C');

// ===== PROFESSIONAL FOOTER =====
$pdf->Ln(8);
$pdf->SetDrawColor(45, 85, 255);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(4);

$pdf->SetFont('times', '', 9);
$pdf->SetTextColor(120, 120, 120);
$footerText = 'Agreement ID: ' . $agreement_id . ' | Generated: ' . date('M d, Y \a\t H:i A');
$pdf->Cell(0, 4, $footerText, 0, 1, 'C');

$pdf->SetFont('times', '', 8);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 4, 'This agreement is a legally binding contract between the parties mentioned above.', 0, 1, 'C');

// Output PDF with clean filename
$projectTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $agreement['ProjectTitle']);
$filename = 'Agreement_' . $projectTitle . '_' . $agreement_id . '.pdf';
$pdf->Output($filename, 'D'); // 'D' = download to user's device
