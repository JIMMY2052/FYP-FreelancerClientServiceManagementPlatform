# Agreement PDF Generation - Complete Guide

## Overview
Your agreement system now fully supports TCPDF-based PDF generation. The system automatically generates professional PDFs matching the preview design.

## Installation Status ✓

Your TCPDF library is now installed via Composer:
```
vendor/
├── autoload.php
├── composer/
└── tecnickcom/
    └── tcpdf/
```

## Files Created/Updated

### 1. **agreement_pdf.php** (PDF Generator)
- **Location**: `page/agreement_pdf.php`
- **Purpose**: Generates PDF from saved agreement data
- **Usage**: Accessed via `agreement_pdf.php?id=<agreement_id>`
- **Features**:
  - Fetches agreement data from database
  - Retrieves freelancer/client information
  - Creates professional PDF with TCPDF
  - Auto-downloads with sanitized filename

### 2. **agreement_view.php** (Agreement Viewer)
- **Location**: `page/agreement_view.php`
- **Purpose**: Display saved agreement and provide download link
- **Usage**: Redirected after agreement creation
- **Features**:
  - Shows success message on creation
  - Displays agreement details in preview format
  - Provides "Download as PDF" button
  - Shows agreement metadata

### 3. **test_pdf_generation.php** (Testing Tool)
- **Location**: `page/test_pdf_generation.php`
- **Purpose**: Test TCPDF installation and PDF generation
- **Usage**: Visit in browser to verify everything works
- **Features**:
  - Checks TCPDF installation
  - Tests PDF creation
  - Displays sample agreements
  - Quick links to download existing PDFs

### 4. **agreement_process.php** (Updated)
- **Location**: `page/agreement_process.php`
- **Changes**: Now redirects to `agreement_view.php` instead of `agreement.php`

## How It Works

### Complete Workflow:

```
1. User fills agreement form (agreement.php)
   ↓
2. Form submits to agreement_process.php
   ↓
3. Data validated and saved to database
   ↓
4. User redirected to agreement_view.php?id=<ID>&status=created
   ↓
5. Success message displayed
   ↓
6. User clicks "Download as PDF"
   ↓
7. agreement_pdf.php?id=<ID> generates and downloads PDF
```

## Quick Start

### Step 1: Test Installation
Visit in your browser:
```
http://localhost/page/test_pdf_generation.php
```

You should see:
- ✓ TCPDF vendor folder found
- ✓ Autoload successfully included
- ✓ TCPDF class is available
- ✓ Test PDF created successfully
- ✓ Database connection successful

### Step 2: Create an Agreement
1. Go to `page/agreement.php`
2. Fill out the form with:
   - Project Title
   - Project Details
   - Scope of Work
   - Deliverables & Timeline
   - Payment Amount
   - Terms & Conditions
3. Click "✓ Create Agreement"

### Step 3: Download as PDF
1. See success message
2. Click "📥 Download as PDF"
3. PDF will automatically download

## PDF Features

The generated PDF includes:

### Header Section
- Large Project Title
- Project Details subtitle
- Offer from: [Freelancer Name]
- To: [Client Name]
- Date: [Agreement Date]

### 4 Main Sections
1. **Scope of Work** - Detailed description
2. **Deliverables & Timeline** - Milestones and dates
3. **Payment Terms** - Total project price with formatting
4. **Terms & Conditions** - Agreement terms

### Footer
- Agreement ID
- Generation timestamp

## TCPDF Code Explanation

### Key Components:

```php
// 1. Include TCPDF library
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Create PDF object
$pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
// 'P' = Portrait, 'mm' = Millimeters, 'A4' = Page size

// 3. Set document properties
$pdf->SetCreator('FYP Platform');
$pdf->SetAuthor('FYP Platform');
$pdf->SetTitle('Agreement - ' . $title);

// 4. Set margins and auto page break
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);

// 5. Add page
$pdf->AddPage();

// 6. Set font
$pdf->SetFont('Helvetica', 'B', 24); // Family, Style, Size

// 7. Add content
$pdf->SetTextColor(26, 26, 26);
$pdf->Cell(0, 15, 'Project Title', 0, 1, 'L');

// 8. Output PDF
$pdf->Output('filename.pdf', 'D'); // 'D' = Download
```

## TCPDF Methods Used

| Method | Purpose |
|--------|---------|
| `SetCreator()` | Set creator name |
| `SetAuthor()` | Set author name |
| `SetTitle()` | Set document title |
| `SetMargins()` | Set page margins (left, top, right) |
| `SetAutoPageBreak()` | Auto create new page when full |
| `AddPage()` | Add new page |
| `SetFont()` | Set font (family, style, size) |
| `SetTextColor()` | Set text RGB color |
| `SetFillColor()` | Set background RGB color |
| `Cell()` | Add single-line text box |
| `MultiCell()` | Add multi-line text box |
| `Ln()` | Add line break |
| `Line()` | Draw line |
| `Output()` | Output PDF (D=download, I=inline) |

## Database Schema Requirements

Your `agreement` table must have these columns:
```sql
- AgreementID (INT, Primary Key)
- ProjectTitle (VARCHAR)
- ProjectDetail (TEXT)
- Scope (TEXT)
- Deliverables (TEXT)
- PaymentAmount (DECIMAL)
- Terms (TEXT)
- SignedDate (DATE/DATETIME)
- Status (VARCHAR)
```

## Customization Guide

### Change PDF Filename Format
Edit `agreement_pdf.php`:
```php
$projectTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $agreement['ProjectTitle']);
$filename = 'Agreement_' . $projectTitle . '_' . $agreement_id . '.pdf';
// Change to your preferred format
```

### Change PDF Colors
Edit color RGB values in `agreement_pdf.php`:
```php
$pdf->SetTextColor(26, 26, 26);      // Dark text
$pdf->SetTextColor(123, 143, 163);   // Gray text
$pdf->SetTextColor(90, 107, 125);    // Darker gray
$pdf->SetFillColor(249, 250, 251);   // Light background
```

### Change Font Sizes
Edit font parameters:
```php
$pdf->SetFont('Helvetica', 'B', 24);  // Bold, 24pt
$pdf->SetFont('Helvetica', '', 11);   // Regular, 11pt
// Options: 'B'=Bold, 'I'=Italic, 'U'=Underline
```

### Add Signature Section
Add before `$pdf->Output()`:
```php
$pdf->Ln(15);
$pdf->SetFont('Helvetica', '', 10);
$pdf->Cell(80, 10, 'Freelancer Signature', 'T', 0, 'C');
$pdf->Cell(0, 10, 'Client Signature', 'T', 0, 'C');
```

## Troubleshooting

### Error: "TCPDF library not found"
**Solution**: Ensure vendor folder exists and run:
```bash
composer install
```

### PDF won't download
**Checks**:
1. Agreement ID is valid in URL
2. Agreement exists in database
3. No whitespace before `<?php` tag
4. No echo/print statements before `$pdf->Output()`

### Special characters not showing in PDF
**Solution**: TCPDF uses UTF-8, ensure database has UTF-8 encoding:
```sql
ALTER TABLE agreement CONVERT TO CHARACTER SET utf8mb4;
```

### PDF output is corrupted
**Solution**: Clear any output buffers:
```php
ob_clean();
$pdf->Output($filename, 'D');
```

## Testing Checklist

- [ ] TCPDF vendor folder installed
- [ ] test_pdf_generation.php passes all tests
- [ ] Agreement table exists in database
- [ ] Can create new agreement via form
- [ ] Success message displays after creation
- [ ] Can download PDF from agreement_view.php
- [ ] PDF opens correctly and shows all content
- [ ] Freelancer/Client names appear in PDF
- [ ] Payment amount formatted correctly (RM X,XXX.XX)
- [ ] All 4 sections visible in PDF

## Performance Tips

1. **Cache PDF**: Store generated PDFs instead of generating on each request
2. **Limit text**: Keep agreement fields reasonably sized
3. **Compress images**: If adding images to PDF
4. **Use PDFs sparingly**: Only generate when needed

## Security Considerations

1. **Validate Agreement ID**: Check user has access to agreement
2. **Sanitize filenames**: Special characters removed (already done)
3. **Check authentication**: User must be logged in
4. **Database prepared statements**: Prevents SQL injection (already implemented)

## Next Steps

1. ✓ Test PDF generation: Visit `test_pdf_generation.php`
2. ✓ Create sample agreement
3. ✓ Download and verify PDF
4. ✓ Customize styling if needed
5. Consider adding:
   - Signature fields
   - Milestone milestones
   - Digital signature support
   - Email PDF to parties

## Support Resources

- **TCPDF Documentation**: https://tcpdf.org/
- **TCPDF Examples**: https://tcpdf.org/examples/
- **GitHub Issues**: https://github.com/tecnickcom/TCPDF

## File Locations

```
FYP-FreelancerClientServiceManagementPlatform/
├── page/
│   ├── agreement.php (Form)
│   ├── agreement_process.php (Handler)
│   ├── agreement_view.php (Viewer)
│   ├── agreement_pdf.php (PDF Generator) ✓
│   ├── test_pdf_generation.php (Tester) ✓
│   └── config.php (Database)
├── vendor/
│   ├── autoload.php
│   ├── composer/
│   └── tecnickcom/tcpdf/
└── databaseSchema.sql
```

---

**Setup Date**: November 18, 2025
**TCPDF Version**: Latest from Composer
**Status**: ✓ Ready to Use
