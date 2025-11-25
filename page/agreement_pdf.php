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

// Set margins - wider margins for professional look
$pdf->SetMargins(20, 20, 20);
$pdf->SetAutoPageBreak(true, 20);

// Add a page
$pdf->AddPage();

// Set default font - using helvetica which is built-in to TCPDF
$pdf->SetFont('helvetica', '', 11);

// ===== PROFESSIONAL HEADER SECTION =====
// Main title bar with gradient-like effect using darker green
$pdf->SetFillColor(22, 163, 74); // Darker green
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 26);
$pdf->Cell(0, 18, 'PROJECT AGREEMENT', 0, 1, 'C', true);

// Subtitle bar
$pdf->SetFillColor(34, 197, 94); // Primary green
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', '', 12);
$projectTitleDisplay = substr(strtoupper($agreement['ProjectTitle']), 0, 50);
$pdf->Cell(0, 10, $projectTitleDisplay, 0, 1, 'C', true);

$pdf->Ln(8);

// ===== PROFESSIONAL HEADER INFO SECTION =====
// Info box with professional styling
$pdf->SetFillColor(245, 250, 247);
$pdf->SetDrawColor(34, 197, 94);
$pdf->SetLineWidth(0.8);
$pdf->SetFont('helvetica', '', 10);

// Header info in 3-column layout
$infoX = $pdf->GetX();
$infoY = $pdf->GetY();
$pdf->SetTextColor(22, 163, 74);

// Freelancer info
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(50, 6, 'FREELANCER', 0, 0, 'L', true);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(70, 6, $freelancer_name, 0, 0, 'L', true);

// Client info
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(22, 163, 74);
$pdf->Cell(35, 6, 'CLIENT', 0, 0, 'L', true);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 6, $client_name, 0, 1, 'L', true);

// Date row
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(22, 163, 74);
$pdf->Cell(50, 6, 'DATE', 0, 0, 'L', true);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(70, 6, date('F j, Y', strtotime($agreement['SignedDate'] ?? 'now')), 0, 0, 'L', true);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(22, 163, 74);
$pdf->Cell(35, 6, 'AMOUNT', 0, 0, 'L', true);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(22, 163, 74);
$pdf->Cell(0, 6, 'RM ' . number_format($agreement['PaymentAmount'], 2), 0, 1, 'L', true);

// Bottom border for info box
$pdf->SetDrawColor(34, 197, 94);
$pdf->SetLineWidth(1.5);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(8);

// ===== PROJECT DETAIL SECTION =====
if (!empty($agreement['ProjectDetail'])) {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(50, 50, 50);
    $pdf->SetFillColor(250, 250, 250);
    $pdf->MultiCell(0, 5, $agreement['ProjectDetail'], 0, 'L', false);
    $pdf->Ln(5);
}

// ===== CONTENT SECTIONS WITH BETTER STYLING =====
$sectionColor = array(34, 197, 94); // Green for section headers
$textColor = array(0, 0, 0);

// Enhanced function to create professional sections
function createSection($pdf, $number, $title, $content, $bgColor, $textColor)
{
    // Section header with background
    $pdf->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetDrawColor(22, 163, 74);
    $pdf->SetLineWidth(0.8);

    // Header cell
    $pdf->Cell(12, 9, $number, 1, 0, 'C', true);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 9, '  ' . strtoupper($title), 1, 1, 'L', true);

    // Content area with border
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
    $pdf->SetDrawColor(34, 197, 94);
    $pdf->SetLineWidth(0.5);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(0, 5, $content, 1, 'L', false);
    $pdf->Ln(6);
}

// SECTION 1: SCOPE OF WORK
createSection($pdf, '1', 'Scope of Work', $agreement['Scope'], $sectionColor, $textColor);

// SECTION 2: DELIVERABLES & TIMELINE
createSection($pdf, '2', 'Deliverables & Timeline', $agreement['Deliverables'], $sectionColor, $textColor);

// SECTION 3: PAYMENT TERMS
$paymentText = 'Total Project Price: RM ' . number_format($agreement['PaymentAmount'], 2) . "\n\n" .
    'Payment will be released in milestones upon completion of deliverables as per the agreed schedule.';
createSection($pdf, '3', 'Payment Terms', $paymentText, $sectionColor, $textColor);

// SECTION 4: TERMS & CONDITIONS
createSection($pdf, '4', 'Terms & Conditions', $agreement['Terms'], $sectionColor, $textColor);

// ===== SECTION 5: DIGITAL SIGNATURE =====
$pdf->SetFillColor(22, 163, 74);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetDrawColor(22, 163, 74);
$pdf->SetLineWidth(0.8);
$pdf->Cell(12, 9, '5', 1, 0, 'C', true);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 9, '  DIGITAL SIGNATURE', 1, 1, 'L', true);

$pdf->Ln(6);

// Add signature image if it exists
$signaturePath = null;
if (!empty($agreement['SignaturePath'])) {
    $signaturePath = __DIR__ . '/../uploads/signatures/' . $agreement['SignaturePath'];
}

if ($signaturePath && file_exists($signaturePath)) {
    // Draw signature image centered
    $boxWidth = 75;
    $boxHeight = 35;
    $pageWidth = $pdf->GetPageWidth();
    $centerX = ($pageWidth - $boxWidth) / 2;

    // Signature box with border
    $pdf->SetDrawColor(34, 197, 94);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect($centerX - 2, $pdf->GetY() - 2, $boxWidth + 4, $boxHeight + 4);

    $pdf->Image($signaturePath, $centerX, $pdf->GetY(), $boxWidth, $boxHeight);
    $pdf->SetY($pdf->GetY() + $boxHeight + 6);
}

// Professional signature block
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(22, 163, 74);
$pdf->SetLineWidth(1.2);

// Draw signature line
$lineY = $pdf->GetY();
$pageWidth = $pdf->GetPageWidth();
$lineStartX = ($pageWidth - 85) / 2;
$pdf->Line($lineStartX, $lineY, $lineStartX + 85, $lineY);

$pdf->Ln(4);

// Freelancer name
$pdf->SetFont('helvetica', 'B', 11);
$freelancerSignatureName = !empty($agreement['FreelancerName']) ? $agreement['FreelancerName'] : 'Freelancer';
$pdf->SetX($lineStartX);
$pdf->Cell(85, 7, $freelancerSignatureName, 0, 1, 'C');

// Signature label
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetX($lineStartX);
$pdf->Cell(85, 5, 'Freelancer Signature', 0, 1, 'C');

$pdf->Ln(4);

// Date signed with professional formatting
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 5, 'Date: ' . date('F j, Y', strtotime($agreement['SignedDate'] ?? 'now')), 0, 1, 'C');

// Add professional footer
$pdf->Ln(10);
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(120, 120, 120);
$pdf->SetDrawColor(34, 197, 94);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(3);

// Professional footer text
$footerText1 = 'Agreement ID: ' . $agreement_id . ' | Generated on ' . date('F j, Y \a\t H:i A');
$pdf->Cell(0, 4, $footerText1, 0, 1, 'C');

$footerText2 = 'This is a digitally signed agreement and is legally binding under the jurisdiction of Malaysia.';
$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 4, $footerText2, 0, 1, 'C');

// Output PDF with clean filename
$projectTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $agreement['ProjectTitle']);
$filename = 'Agreement_' . $projectTitle . '_' . $agreement_id . '.pdf';
$pdf->Output($filename, 'D'); // 'D' = download to user's device
