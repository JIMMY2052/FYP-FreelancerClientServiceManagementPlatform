<?php

require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

/**
 * E-Invoice Generator Class
 * Generates official standard e-invoices for work completion
 */
class InvoiceGenerator
{
    private $pdf;
    private $invoiceNumber;
    private $invoiceDate;

    /**
     * Generate e-invoice for completed work
     * 
     * @param array $data Invoice data
     * @return string Path to generated invoice file
     */
    public function generateInvoice($data)
    {
        // Generate unique invoice number
        $this->invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($data['agreement_id'], 6, '0', STR_PAD_LEFT);
        $this->invoiceDate = date('Y-m-d H:i:s');

        // Create PDF
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->pdf->SetCreator('WorkSnyc Platform');
        $this->pdf->SetAuthor('WorkSnyc');
        $this->pdf->SetTitle('E-Invoice ' . $this->invoiceNumber);
        $this->pdf->SetSubject('Payment Invoice');

        // Remove default header/footer
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // Set margins
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(true, 20);

        // Add page
        $this->pdf->AddPage();

        // Build invoice content
        $this->addHeader();
        $this->addInvoiceInfo();
        $this->addBillingInfo($data);
        $this->addItemsTable($data);
        $this->addPaymentInfo($data);
        $this->addFooter();

        // Save to file
        $invoiceDir = __DIR__ . '/../uploads/invoices/';
        if (!is_dir($invoiceDir)) {
            mkdir($invoiceDir, 0755, true);
        }

        $filename = 'invoice_' . $data['agreement_id'] . '_' . time() . '.pdf';
        $filepath = $invoiceDir . $filename;
        $this->pdf->Output($filepath, 'F');

        return 'uploads/invoices/' . $filename;
    }

    /**
     * Add invoice header
     */
    private function addHeader()
    {
        // Platform name and logo area
        $this->pdf->SetFont('helvetica', 'B', 24);
        $this->pdf->SetTextColor(22, 163, 74); // Green color
        $this->pdf->Cell(0, 15, 'WorkSnyc', 0, 1, 'L');

        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->Cell(0, 5, 'Freelancer Client Service Management Platform', 0, 1, 'L');
        $this->pdf->Cell(0, 5, 'Email: support@worksnyc.com | Phone: +60 12-345 6789', 0, 1, 'L');
        
        $this->pdf->Ln(5);

        // E-INVOICE header
        $this->pdf->SetFont('helvetica', 'B', 20);
        $this->pdf->SetTextColor(44, 62, 80);
        $this->pdf->Cell(0, 10, 'E-INVOICE', 0, 1, 'R');

        // Draw line
        $this->pdf->SetDrawColor(159, 232, 112);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
        $this->pdf->Ln(5);
    }

    /**
     * Add invoice information
     */
    private function addInvoiceInfo()
    {
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetTextColor(44, 62, 80);
        
        $y = $this->pdf->GetY();
        
        // Left column - Invoice details
        $this->pdf->SetXY(15, $y);
        $this->pdf->Cell(60, 6, 'Invoice Number:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->Cell(60, 6, $this->invoiceNumber, 0, 1, 'L');
        
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetX(15);
        $this->pdf->Cell(60, 6, 'Invoice Date:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->Cell(60, 6, date('d F Y', strtotime($this->invoiceDate)), 0, 1, 'L');
        
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetX(15);
        $this->pdf->Cell(60, 6, 'Payment Status:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->SetTextColor(22, 163, 74);
        $this->pdf->Cell(60, 6, 'PAID', 0, 1, 'L');
        
        $this->pdf->Ln(5);
    }

    /**
     * Add billing information
     */
    private function addBillingInfo($data)
    {
        $y = $this->pdf->GetY();
        
        // Bill From (Freelancer)
        $this->pdf->SetFillColor(248, 250, 252);
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->SetTextColor(44, 62, 80);
        $this->pdf->SetXY(15, $y);
        $this->pdf->Cell(85, 8, 'Service Provider (Freelancer)', 0, 0, 'L', true);
        
        // Bill To (Client)
        $this->pdf->SetXY(110, $y);
        $this->pdf->Cell(85, 8, 'Bill To (Client)', 0, 1, 'L', true);
        
        $this->pdf->Ln(2);
        
        // Freelancer details
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetTextColor(44, 62, 80);
        $this->pdf->SetX(15);
        $this->pdf->Cell(85, 6, $data['freelancer_name'], 0, 0, 'L');
        
        // Client details
        $this->pdf->SetX(110);
        $this->pdf->Cell(85, 6, $data['client_name'], 0, 1, 'L');
        
        // Freelancer email
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->SetX(15);
        $this->pdf->Cell(85, 5, 'Email: ' . $data['freelancer_email'], 0, 0, 'L');
        
        // Client email
        $this->pdf->SetX(110);
        $this->pdf->Cell(85, 5, 'Email: ' . $data['client_email'], 0, 1, 'L');
        
        $this->pdf->Ln(8);
    }

    /**
     * Add items table
     */
    private function addItemsTable($data)
    {
        // Table header
        $this->pdf->SetFillColor(22, 163, 74);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('helvetica', 'B', 11);
        
        $this->pdf->Cell(110, 8, 'Description', 1, 0, 'L', true);
        $this->pdf->Cell(25, 8, 'Qty', 1, 0, 'C', true);
        $this->pdf->Cell(45, 8, 'Amount (RM)', 1, 1, 'R', true);
        
        // Table content
        $this->pdf->SetFillColor(255, 255, 255);
        $this->pdf->SetTextColor(44, 62, 80);
        $this->pdf->SetFont('helvetica', '', 10);
        
        // Get current Y position
        $startY = $this->pdf->GetY();
        
        // Project description - calculate height needed
        $descriptionText = $data['project_title'];
        $cellHeight = 8;
        
        // Draw description cell
        $this->pdf->MultiCell(110, $cellHeight, $descriptionText, 1, 'L', true, 0, '', '', true, 0, false, true, $cellHeight, 'M');
        
        // Draw quantity cell at same height
        $this->pdf->Cell(25, $cellHeight, '1', 1, 0, 'C', true);
        
        // Draw amount cell at same height - right aligned
        $this->pdf->Cell(45, $cellHeight, number_format($data['amount'], 2), 1, 1, 'R', true);
        
        // Additional description if available
        if (!empty($data['project_detail']) && strlen($data['project_detail']) > 0) {
            $this->pdf->SetFont('helvetica', 'I', 8);
            $this->pdf->SetTextColor(100, 100, 100);
            $detailText = 'Details: ' . substr($data['project_detail'], 0, 150);
            if (strlen($data['project_detail']) > 150) {
                $detailText .= '...';
            }
            $this->pdf->MultiCell(180, 4, $detailText, 0, 'L');
        }
        
        // Subtotal, Tax, Total section
        $this->pdf->Ln(8);
        
        $subtotal = $data['amount'];
        $tax = 0; // No tax for now
        $total = $subtotal + $tax;
        
        // Subtotal row
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->SetTextColor(44, 62, 80);
        $this->pdf->Cell(135, 6, '', 0, 0, 'L');
        $this->pdf->Cell(0, 6, 'Subtotal: RM ' . number_format($subtotal, 2), 0, 1, 'R');
        
        // Tax row (if applicable)
        if ($tax > 0) {
            $this->pdf->Cell(135, 6, '', 0, 0, 'L');
            $this->pdf->Cell(0, 6, 'Tax (0%): RM ' . number_format($tax, 2), 0, 1, 'R');
        }
        
        // Draw separator line
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->Line(135, $this->pdf->GetY() + 2, 195, $this->pdf->GetY() + 2);
        $this->pdf->Ln(4);
        
        // Total row with background
        $this->pdf->SetFont('helvetica', 'B', 13);
        $this->pdf->SetFillColor(248, 250, 252);
        $this->pdf->SetTextColor(22, 163, 74);
        
        $totalY = $this->pdf->GetY();
        $this->pdf->Rect(135, $totalY, 60, 10, 'F');
        $this->pdf->SetXY(135, $totalY);
        $this->pdf->Cell(60, 10, 'Total: RM ' . number_format($total, 2), 0, 1, 'R', false);
        
        $this->pdf->Ln(5);
    }

    /**
     * Add payment information
     */
    private function addPaymentInfo($data)
    {
        // Payment information box
        $this->pdf->SetFillColor(232, 245, 233);
        $this->pdf->SetDrawColor(22, 163, 74);
        $this->pdf->SetLineWidth(0.3);
        
        $boxY = $this->pdf->GetY();
        $this->pdf->Rect(15, $boxY, 180, 25, 'FD');
        
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetTextColor(22, 163, 74);
        $this->pdf->SetXY(20, $boxY + 3);
        $this->pdf->Cell(0, 6, 'PAYMENT INFORMATION', 0, 1, 'L');
        
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetTextColor(44, 62, 80);
        $this->pdf->SetX(20);
        $this->pdf->Cell(0, 5, 'Payment Method: Escrow (Platform Wallet)', 0, 1, 'L');
        $this->pdf->SetX(20);
        $this->pdf->Cell(0, 5, 'Transaction Date: ' . date('d F Y H:i:s', strtotime($this->invoiceDate)), 0, 1, 'L');
        $this->pdf->SetX(20);
        $this->pdf->Cell(0, 5, 'Agreement ID: #' . $data['agreement_id'], 0, 1, 'L');
        
        $this->pdf->Ln(8);
    }

    /**
     * Add footer
     */
    private function addFooter()
    {
        $this->pdf->SetY(-30);
        
        // Terms and conditions
        $this->pdf->SetFont('helvetica', 'I', 8);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->MultiCell(0, 4, 'This is a computer-generated e-invoice and does not require a physical signature. Payment has been processed through the platform\'s secure escrow system. For any queries, please contact support@worksnyc.com.', 0, 'C');
        
        $this->pdf->Ln(2);
        
        // Draw line
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
        
        $this->pdf->Ln(2);
        
        // Footer text
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->Cell(0, 4, 'Generated on ' . date('d F Y H:i:s') . ' | WorkSnyc Platform © ' . date('Y'), 0, 1, 'C');
    }

    /**
     * Get invoice number
     */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }
}
