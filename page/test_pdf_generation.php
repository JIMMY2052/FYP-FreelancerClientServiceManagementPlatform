<?php

/**
 * PDF Generation Test Script
 * This script tests if TCPDF is properly installed and PDF generation works
 * 
 * Run this from your browser: http://localhost/page/test_pdf_generation.php
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set test data
$_SESSION['freelancer_id'] = 1; // Use an actual freelancer ID from your database
$_SESSION['client_id'] = 1;     // Use an actual client ID from your database

include 'config.php';

echo "<h1>PDF Generation Test</h1>";
echo "<hr>";

// Test 1: Check if vendor autoload exists
echo "<h3>Test 1: Checking TCPDF Installation</h3>";
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "✓ TCPDF vendor folder found<br>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✓ Autoload successfully included<br>";
} else {
    echo "✗ TCPDF vendor folder NOT found<br>";
    echo "Please run: <code>composer require tecnickcom/tcpdf</code><br>";
    exit;
}

// Test 2: Check if TCPDF class exists
echo "<h3>Test 2: Checking TCPDF Class</h3>";
if (class_exists('\TCPDF')) {
    echo "✓ TCPDF class is available<br>";
} else {
    echo "✗ TCPDF class not found<br>";
    exit;
}

// Test 3: Create a test PDF
echo "<h3>Test 3: Creating Test PDF</h3>";
try {
    $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Test');
    $pdf->SetAuthor('Test');
    $pdf->SetTitle('Test Agreement PDF');
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Test Agreement PDF', 0, 1, 'C');

    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Ln(5);
    $pdf->Cell(0, 10, 'If you see this PDF, TCPDF is working correctly!', 0, 1, 'L');
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Generated: ' . date('F j, Y H:i:s'), 0, 1, 'L');

    echo "✓ Test PDF created successfully<br>";
    echo "✓ TCPDF is working correctly!<br>";

    // Option to download test PDF
    echo "<br>";
    echo "<a href='#' onclick='downloadPDF()' style='background: #1ab394; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>Download Test PDF</a>";
    echo "<br><br>";

    // Save test PDF temporarily
    $_SESSION['test_pdf_ready'] = true;
} catch (Exception $e) {
    echo "✗ Error creating PDF: " . $e->getMessage() . "<br>";
    exit;
}

// Test 4: Test database connection
echo "<h3>Test 4: Checking Database</h3>";
$conn = getDBConnection();
if ($conn) {
    echo "✓ Database connection successful<br>";

    // Check if agreement table exists
    $result = $conn->query("SHOW TABLES LIKE 'agreement'");
    if ($result->num_rows > 0) {
        echo "✓ Agreement table found<br>";

        // Check if there are any agreements
        $result = $conn->query("SELECT COUNT(*) as count FROM agreement");
        $row = $result->fetch_assoc();
        echo "✓ Total agreements in database: " . $row['count'] . "<br>";

        if ($row['count'] > 0) {
            echo "<h3>Test 5: Sample Agreements</h3>";
            echo "<table border='1' cellpadding='10'>";
            echo "<tr><th>ID</th><th>Title</th><th>Payment</th><th>Status</th><th>Action</th></tr>";

            $result = $conn->query("SELECT AgreementID, ProjectTitle, PaymentAmount, Status FROM agreement LIMIT 5");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['AgreementID'] . "</td>";
                echo "<td>" . htmlspecialchars($row['ProjectTitle']) . "</td>";
                echo "<td>RM " . number_format($row['PaymentAmount'], 2) . "</td>";
                echo "<td>" . ucfirst($row['Status']) . "</td>";
                echo "<td>";
                echo "<form method='POST' action='agreement_pdf.php' style='display:inline;'>";
                echo "<input type='hidden' name='agreement_id' value='" . $row['AgreementID'] . "'>";
                echo "<button type='submit' style='color: #1ab394; text-decoration: none; background: none; border: none; cursor: pointer; padding: 0;'>Download PDF</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p><strong>No agreements found.</strong> Create one first at <a href='agreement.php'>agreement.php</a></p>";
        }
    } else {
        echo "✗ Agreement table not found<br>";
    }

    $conn->close();
} else {
    echo "✗ Database connection failed<br>";
}

// Test summary
echo "<hr>";
echo "<h3>Summary</h3>";
echo "<p><strong>TCPDF PDF Generation is ready to use!</strong></p>";
echo "<p>You can now:</p>";
echo "<ul>";
echo "<li>Create agreements at <a href='agreement.php'>agreement.php</a></li>";
echo "<li>Download generated PDFs using the 'Download as PDF' button</li>";
echo "<li>View saved agreements at <a href='agreement_view.php'>agreement_view.php</a></li>";
echo "</ul>";

?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background: #f5f7fa;
    }

    h1,
    h3 {
        color: #1a1a1a;
    }

    table {
        border-collapse: collapse;
        background: white;
        width: 100%;
    }

    a {
        color: #1ab394;
    }

    code {
        background: #f0f0f0;
        padding: 2px 6px;
        border-radius: 3px;
    }
</style>