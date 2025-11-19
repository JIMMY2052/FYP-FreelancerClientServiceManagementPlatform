<?php

/**
 * PDF Helper Class
 * Simplifies common PDF generation tasks
 * 
 * Usage:
 * $pdfHelper = new PDFHelper();
 * $pdf = $pdfHelper->createPDF('Agreement');
 * $pdf->AddContent('Title', 'Content goes here');
 * $pdfHelper->outputPDF($pdf, 'filename.pdf');
 */

class PDFHelper
{

    private $pdf;

    /**
     * Create a new TCPDF instance
     * 
     * @param string $title Document title
     * @param string $author Document author
     * @return \TCPDF PDF object
     */
    public function createPDF($title = 'Document', $author = 'FYP Platform')
    {
        // Check if TCPDF is available
        if (!class_exists('\TCPDF')) {
            throw new Exception('TCPDF library not found. Please install via Composer.');
        }

        $this->pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->pdf->SetCreator('Freelancer Client Service Management Platform');
        $this->pdf->SetAuthor($author);
        $this->pdf->SetTitle($title);
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(true, 15);
        $this->pdf->AddPage();

        return $this->pdf;
    }

    /**
     * Output PDF to user
     * 
     * @param \TCPDF $pdf PDF object
     * @param string $filename Filename for download
     * @param string $mode 'D' for download, 'I' for inline
     */
    public function outputPDF(&$pdf, $filename, $mode = 'D')
    {
        // Sanitize filename
        $filename = $this->sanitizeFilename($filename);
        $pdf->Output($filename, $mode);
    }

    /**
     * Add a section header
     * 
     * @param \TCPDF $pdf PDF object
     * @param int $number Section number (1-4)
     * @param string $title Section title
     */
    public function addSectionHeader(&$pdf, $number, $title)
    {
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetTextColor(26, 26, 26);
        $pdf->Cell(10, 10, $number, 0, 0, 'C');
        $pdf->Cell(0, 10, $title, 0, 1, 'L');
    }

    /**
     * Add section content
     * 
     * @param \TCPDF $pdf PDF object
     * @param string $content Content text
     */
    public function addSectionContent(&$pdf, $content)
    {
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetTextColor(90, 107, 125);
        $pdf->MultiCell(0, 6, $content, 0, 'L');
        $pdf->Ln(5);
    }

    /**
     * Add a payment box
     * 
     * @param \TCPDF $pdf PDF object
     * @param float $amount Payment amount
     * @param string $note Optional note
     */
    public function addPaymentBox(&$pdf, $amount, $note = null)
    {
        $pdf->SetFillColor(249, 250, 251);
        $pdf->SetDrawColor(229, 231, 235);
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(26, 26, 26);
        $pdf->Cell(0, 8, 'Total Project Price: RM ' . number_format($amount, 2), 0, 1, 'L', true);

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(90, 107, 125);

        if ($note === null) {
            $note = 'Payment will be released in milestones upon completion of deliverables.';
        }

        $pdf->MultiCell(0, 6, $note, 0, 'L', true);
        $pdf->Ln(3);
    }

    /**
     * Add header section with title and details
     * 
     * @param \TCPDF $pdf PDF object
     * @param string $title Main title
     * @param string $subtitle Subtitle
     * @param array $details Right-aligned details (label => value)
     */
    public function addHeaderSection(&$pdf, $title, $subtitle, $details = array())
    {
        // Title
        $pdf->SetFont('Helvetica', 'B', 24);
        $pdf->SetTextColor(26, 26, 26);
        $pdf->Cell(0, 15, $title, 0, 1, 'L');

        // Subtitle
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetTextColor(123, 143, 163);
        $pdf->MultiCell(0, 10, $subtitle, 0, 'L');

        $pdf->Ln(5);

        // Details (right-aligned)
        if (!empty($details)) {
            $pageWidth = $pdf->GetPageWidth();
            $margin = 15;
            $lineHeight = 5;
            $labelWidth = 60;
            $valueWidth = 80;

            foreach ($details as $label => $value) {
                $x = $pageWidth - $margin - $valueWidth;

                $pdf->SetXY($x - $labelWidth, $pdf->GetY());
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetTextColor(123, 143, 163);
                $pdf->Cell($labelWidth, $lineHeight, $label, 0, 0, 'R');

                $pdf->SetFont('Helvetica', 'B', 10);
                $pdf->SetTextColor(26, 26, 26);
                $pdf->Cell($valueWidth, $lineHeight, $value, 0, 1, 'L');
            }
        }

        // Border line
        $pdf->SetDrawColor(229, 231, 235);
        $pdf->Line(15, $pdf->GetY() + 3, $pdf->GetPageWidth() - 15, $pdf->GetY() + 3);
        $pdf->Ln(8);
    }

    /**
     * Add footer with metadata
     * 
     * @param \TCPDF $pdf PDF object
     * @param string $agreementID Agreement ID
     * @param string $timestamp Generated timestamp
     */
    public function addFooter(&$pdf, $agreementID, $timestamp = null)
    {
        if ($timestamp === null) {
            $timestamp = date('F j, Y \a\t H:i A');
        }

        $pdf->Ln(10);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(155, 160, 170);
        $pdf->Cell(0, 10, 'Agreement ID: ' . $agreementID . ' | Generated on ' . $timestamp, 0, 0, 'C');
    }

    /**
     * Sanitize filename for safe download
     * 
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    public function sanitizeFilename($filename)
    {
        // Remove extension if present
        $filename = pathinfo($filename, PATHINFO_FILENAME);

        // Replace special characters with underscore
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);

        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);

        // Add .pdf extension
        return $filename . '.pdf';
    }

    /**
     * Get the PDF object
     * 
     * @return \TCPDF
     */
    public function getPDF()
    {
        return $this->pdf;
    }

    /**
     * Set custom font
     * 
     * @param \TCPDF $pdf PDF object
     * @param string $family Font family
     * @param string $style B=Bold, I=Italic, U=Underline, or empty
     * @param int $size Font size
     */
    public function setFont(&$pdf, $family = 'Helvetica', $style = '', $size = 11)
    {
        $pdf->SetFont($family, $style, $size);
    }

    /**
     * Set text color
     * 
     * @param \TCPDF $pdf PDF object
     * @param int $r Red (0-255)
     * @param int $g Green (0-255)
     * @param int $b Blue (0-255)
     */
    public function setTextColor(&$pdf, $r, $g, $b)
    {
        $pdf->SetTextColor($r, $g, $b);
    }
}
