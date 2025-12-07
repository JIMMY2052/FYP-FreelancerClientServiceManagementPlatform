# E-Invoice System Implementation Guide

## Overview
A comprehensive e-invoice generation system has been implemented that automatically creates official invoices when clients accept completed work from freelancers.

## Files Created/Modified

### 1. New Files Created

#### a) `InvoiceGenerator.php`
- Location: `/page/InvoiceGenerator.php`
- Purpose: Core class for generating PDF e-invoices
- Features:
  - Generates unique invoice numbers (format: INV-YYYYMMDD-XXXXXX)
  - Creates professional PDF invoices using TCPDF library
  - Includes platform branding, billing details, payment info
  - Stores invoices in `/uploads/invoices/` directory

#### b) `my_invoices.php`
- Location: `/page/my_invoices.php`
- Purpose: Invoice viewing page for both clients and freelancers
- Features:
  - Lists all invoices for the logged-in user
  - Download functionality
  - Responsive grid layout
  - Shows invoice number, project title, amount, date, and status

#### c) `create_invoices_table.sql`
- Location: `/create_invoices_table.sql`
- Purpose: Database schema for storing invoice records
- Fields:
  - InvoiceID (Primary Key)
  - InvoiceNumber (Unique)
  - AgreementID (Foreign Key)
  - ClientID, FreelancerID
  - Amount, InvoiceDate
  - InvoiceFilePath
  - Status (generated/sent/paid)

### 2. Modified Files

#### a) `review_work_process.php`
- Added: `require_once 'InvoiceGenerator.php';`
- Added invoice generation logic after payment release (Step 5)
- Process flow when work is approved:
  1. Update submission status to approved
  2. Update agreement status to complete
  3. Release escrow funds
  4. Create notifications
  5. **Generate e-invoice (NEW)**
  6. Save invoice to database
  7. Notify both users about invoice generation

## Database Setup

Run the SQL script to create the invoices table:

```sql
-- Execute this in your MySQL database
SOURCE create_invoices_table.sql;
```

Or manually run:
```sql
CREATE TABLE IF NOT EXISTS `invoices` (
  `InvoiceID` int(11) NOT NULL AUTO_INCREMENT,
  `InvoiceNumber` varchar(50) NOT NULL,
  `AgreementID` int(11) NOT NULL,
  `ClientID` int(11) NOT NULL,
  `FreelancerID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `InvoiceDate` datetime NOT NULL,
  `InvoiceFilePath` varchar(500) DEFAULT NULL,
  `Status` enum('generated','sent','paid') NOT NULL DEFAULT 'paid',
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`InvoiceID`),
  UNIQUE KEY `unique_agreement` (`AgreementID`),
  KEY `idx_client` (`ClientID`),
  KEY `idx_freelancer` (`FreelancerID`),
  KEY `idx_invoice_number` (`InvoiceNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Directory Setup

Ensure the invoices directory exists and has proper permissions:

```bash
mkdir -p uploads/invoices
chmod 755 uploads/invoices
```

## E-Invoice Features

### Invoice Content
1. **Header Section**
   - Platform branding (WorkSnyc)
   - Contact information
   - "E-INVOICE" title

2. **Invoice Information**
   - Unique invoice number
   - Invoice date
   - Payment status (PAID)

3. **Billing Details**
   - Service Provider (Freelancer): Name and email
   - Bill To (Client): Company name and email

4. **Items Table**
   - Project description
   - Quantity (1)
   - Amount in RM
   - Subtotal, Tax (if applicable), Total

5. **Payment Information Box**
   - Payment method (Escrow - Platform Wallet)
   - Transaction date and time
   - Agreement ID reference

6. **Footer**
   - Terms and conditions
   - Computer-generated notice
   - Platform copyright

### Invoice Number Format
- Pattern: `INV-YYYYMMDD-AAAAAA`
- Example: `INV-20251207-000023`
- Where:
  - YYYYMMDD = Current date
  - AAAAAA = Agreement ID (padded to 6 digits)

## User Flow

### For Clients:
1. Review submitted work at `/page/review_work.php`
2. Click "Accept Work" button
3. System automatically:
   - Releases payment from escrow
   - Generates e-invoice
   - Sends notification with invoice number
4. View all invoices at `/page/my_invoices.php`
5. Download PDF invoice

### For Freelancers:
1. Submit work and wait for client review
2. When work is approved:
   - Receive payment in wallet
   - Get notification about invoice generation
3. View all invoices at `/page/my_invoices.php`
4. Download PDF invoice for records

## Notifications

Both users receive notifications when invoice is generated:
- **Type**: 'invoice'
- **Client Message**: "E-Invoice INV-XXXXXXX has been generated for 'Project Title'. View your invoice in the transaction history."
- **Freelancer Message**: Same as client

## Error Handling

- Invoice generation is wrapped in try-catch
- If invoice generation fails, it logs the error but doesn't stop the payment process
- This ensures payments always complete even if PDF generation has issues

## Testing Checklist

1. ✅ Create invoices directory with proper permissions
2. ✅ Run SQL script to create invoices table
3. ✅ Complete a work submission
4. ✅ Accept the work as a client
5. ✅ Verify invoice is generated in `/uploads/invoices/`
6. ✅ Check invoice record in database
7. ✅ Verify both users receive notifications
8. ✅ Access `/page/my_invoices.php` as client
9. ✅ Access `/page/my_invoices.php` as freelancer
10. ✅ Download and verify PDF invoice content

## Navigation Integration

Add link to sidebar/menu for both user types:
```html
<a href="/page/my_invoices.php">
    <i class="fas fa-file-invoice"></i>
    My Invoices
</a>
```

## Security Considerations

- Invoices are stored with timestamp to prevent overwrites
- Only authenticated users can access my_invoices.php
- Download links use relative paths
- Invoice numbers are unique per agreement
- Database constraints prevent duplicate invoices for same agreement

## API Reference

### InvoiceGenerator::generateInvoice($data)
**Parameters:**
```php
$data = [
    'agreement_id' => int,
    'client_name' => string,
    'client_email' => string,
    'freelancer_name' => string,
    'freelancer_email' => string,
    'project_title' => string,
    'project_detail' => string,
    'amount' => float
];
```

**Returns:** String (file path to generated invoice)

### InvoiceGenerator::getInvoiceNumber()
**Returns:** String (generated invoice number)

## Maintenance

- Invoices are stored permanently in `/uploads/invoices/`
- Consider implementing:
  - Archive old invoices (> 1 year)
  - Backup invoice files regularly
  - Add invoice search/filter functionality
  - Email invoices to both parties
  - Tax calculation support

## Future Enhancements

1. Email invoice as attachment
2. Invoice history with filtering
3. Tax calculation integration
4. Multi-currency support
5. Invoice templates customization
6. Bulk invoice download
7. Annual invoice summaries
8. Invoice dispute mechanism
