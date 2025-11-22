# Digital Signature Architecture & Flow

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    DIGITAL SIGNATURE SYSTEM                     │
└─────────────────────────────────────────────────────────────────┘

                          ┌──────────────┐
                          │  Freelancer  │
                          │   (Browser)  │
                          └──────┬───────┘
                                 │
                    ┌────────────┴────────────┐
                    │                         │
        ┌───────────▼─────────────┐  ┌──────▼──────────────┐
        │   agreement.php         │  │  Signature Pad      │
        │   (Form & Canvas)       │  │  JavaScript Library │
        │                         │  │                     │
        │ - Text Fields           │  │ - Draw Canvas       │
        │ - Live Preview          │  │ - Clear Button      │
        │ - Signature Canvas      │  │ - Confirm Button    │
        │ - Name Field            │  │ - Convert to Base64 │
        └───────────┬─────────────┘  └──────┬──────────────┘
                    │                       │
        ┌───────────▼───────────────────────┘
        │
    ┌───▼──────────────────────────────────────────┐
    │  Form Submission (POST)                      │
    │  ├─ project_title                            │
    │  ├─ project_detail                           │
    │  ├─ scope                                    │
    │  ├─ deliverables                            │
    │  ├─ payment                                 │
    │  ├─ terms                                   │
    │  ├─ freelancer_name ← NEW                   │
    │  └─ signature_data (base64 PNG) ← NEW       │
    └───┬──────────────────────────────────────────┘
        │
        ▼
    ┌──────────────────────────────────────────────┐
    │  agreement_process.php                       │
    │  (Backend Processing)                        │
    │                                              │
    │  1. Validate all inputs                      │
    │  2. Decode base64 signature                  │
    │  3. Create PNG file                          │
    │  4. Save to /uploads/signatures/             │
    │  5. Insert to database                       │
    │  6. Store file reference in DB               │
    └───┬──────────────────────────────────────────┘
        │
    ┌───┼────────────────────────────────────────┐
    │   │                                         │
    ▼   ▼                                         ▼
 ┌──────────────┐        ┌──────────────────────────────────┐
 │  Database    │        │  File System                     │
 │              │        │                                  │
 │ agreement    │        │  /uploads/signatures/            │
 │ ├─ columns   │        │  ├─ signature_1234567_abc.png   │
 │ │            │        │  ├─ signature_1234568_def.png   │
 │ ├─ ID        │        │  ├─ signature_1234569_ghi.png   │
 │ ├─ Project   │        │  └─ ...                         │
 │ ├─ Terms     │        │                                  │
 │ ├─ Freelancer│        │                                  │
 │ │ Name ←NEW  │        │                                  │
 │ └─ Signature │◄──────►│                                  │
 │   Path ←NEW  │        │                                  │
 └──────────────┘        └──────────────────────────────────┘
        ▲
        │
        └─────────┐
                  │
        ┌─────────▼──────────────────────────────┐
        │  agreement_pdf.php                     │
        │  (PDF Generation)                      │
        │                                        │
        │  1. Query agreement from DB            │
        │  2. Load signature image from storage  │
        │  3. Create PDF sections:               │
        │     - Section 1: Scope                 │
        │     - Section 2: Deliverables          │
        │     - Section 3: Payment               │
        │     - Section 4: Terms                 │
        │     - Section 5: Signature ← NEW       │
        │  4. Embed signature image              │
        │  5. Add signature line & name          │
        │  6. Output PDF to download             │
        └─────────┬──────────────────────────────┘
                  │
                  ▼
        ┌──────────────────────────┐
        │  PDF File (Downloaded)   │
        │                          │
        │  ✓ All agreement details │
        │  ✓ Signature image       │
        │  ✓ Signature line        │
        │  ✓ Freelancer name       │
        │  ✓ Signed date           │
        └──────────────────────────┘
```

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    USER INTERACTION FLOW                    │
└─────────────────────────────────────────────────────────────┘

        User Opens agreement.php
                  │
                  ▼
        ┌──────────────────────┐
        │  Form Display        │
        │  - All fields        │
        │  - Signature Canvas  │
        │  - Name Field        │
        └──────────┬───────────┘
                   │
                   ▼
        ┌──────────────────────┐      ┌──────────────────┐
        │  User Input          │      │  Signature Pad   │
        │  - Fill Agreement    │      │  (Interactive)   │
        │  - Draw Signature    │      │                  │
        │  - Enter Name        │      │  Canvas Tracking │
        │                      │◄─────│  - Mouse/Touch   │
        │                      │      │  - Line Drawing  │
        └──────────┬───────────┘      └──────────────────┘
                   │
                   ▼
        ┌──────────────────────────────────────┐
        │  Live Preview Updates (Real-time)    │
        │  ├─ Agreement text preview           │
        │  ├─ Signature canvas to preview      │
        │  └─ Name field to preview            │
        └──────────┬──────────────────────────┘
                   │
        ┌──────────┴──────────┐
        │                     │
        ▼                     ▼
    Click "Confirm"      ┌──────────────┐
    Signature Button     │  Validation  │
        │                │              │
        │◄───────────────│  Confirm all │
        │                │  fields OK?  │
        │                └──────────────┘
        ▼
    ┌──────────────────────────────┐
    │  Signature Confirmed         │
    │  ├─ Base64 saved to hidden   │
    │  │   input field             │
    │  └─ Preview updated with     │
    │      signature image         │
    └──────────┬───────────────────┘
               │
        ┌──────┴───────┐
        │              │
        ▼              ▼
    User Enters    Canvas Ready
    Full Name      for PDF
        │              │
        └──────┬───────┘
               │
               ▼
    ┌──────────────────────────┐
    │  Form Validation        │
    │  ├─ All required fields  │
    │  ├─ Signature confirmed  │
    │  └─ Name not empty       │
    └──────────┬───────────────┘
               │
        ┌──────▼──────┐
        │   Valid?    │
        └──────┬──────┘
        ┌──────┴──────┐
        │             │
        NO           YES
        │             │
        ▼             ▼
    Alert       ┌────────────────┐
    Error       │  Submit Form   │
                │  (POST)        │
                └────────┬───────┘
                         │
                         ▼
            agreement_process.php
                         │
         ┌───────────────┴────────────────┐
         │                                │
         ▼                                ▼
    Backend            Signature
    Processing         Processing
    ├─ Validate        ├─ Decode base64
    ├─ Sanitize        ├─ Verify PNG
    └─ DB Insert       ├─ Generate filename
                       ├─ Save to disk
                       └─ Reference in DB
         │                                │
         └───────────────┬────────────────┘
                         │
                         ▼
            ┌──────────────────────────┐
            │  Database Updated        │
            │  ├─ agreement record     │
            │  ├─ FreelancerName field │
            │  └─ SignaturePath field  │
            └──────────┬───────────────┘
                       │
                       ▼
            ┌──────────────────────────┐
            │  Signature File Saved    │
            │  ├─ Location:            │
            │  │  /uploads/signatures/ │
            │  └─ Filename:            │
            │     signature_*.png      │
            └──────────┬───────────────┘
                       │
                       ▼
            ┌──────────────────────────┐
            │  Redirect to PDF View    │
            │  agreement_view.php      │
            └──────────┬───────────────┘
                       │
                       ▼
            ┌──────────────────────────┐
            │  User Downloads PDF      │
            │  ├─ agreement_pdf.php    │
            │  ├─ Reads agreement DB   │
            │  ├─ Loads signature img  │
            │  ├─ Embeds in PDF        │
            │  └─ Streams to browser   │
            └──────────┬───────────────┘
                       │
                       ▼
            ┌──────────────────────────┐
            │  PDF File Created        │
            │  ✓ Signature embedded    │
            │  ✓ Name displayed        │
            │  ✓ Professional format   │
            │  ✓ Signed date included  │
            └──────────────────────────┘
```

## File Processing Flow

```
┌──────────────────────────────────────────────────────────┐
│            SIGNATURE FILE PROCESSING                     │
└──────────────────────────────────────────────────────────┘

Frontend (Browser)
├─ Canvas Element
│  ├─ User draws signature with mouse/touch
│  └─ Signature Pad.js tracks coordinates
│
└─ Button Actions
   ├─ Clear: signaturePad.clear()
   └─ Confirm: signaturePad.toDataURL("image/png")
                ↓
                Base64 String
                "data:image/png;base64,iVBORw0KGgoAAAANS..."
                ↓
                Stored in hidden input
                <input type="hidden" name="signature_data">

Backend Processing (agreement_process.php)
├─ Receive POST data
│  └─ $_POST['signature_data'] = base64 string
│
├─ Validate and Clean
│  ├─ Remove "data:image/png;base64," prefix
│  ├─ base64_decode() to binary PNG
│  └─ Validate PNG format
│
├─ Generate Unique Filename
│  ├─ 'signature_' . time() . '_' . uniqid() . '.png'
│  └─ Example: signature_1234567890_abc123def.png
│
├─ Create Directory (if needed)
│  └─ mkdir('/uploads/signatures/', 0755, true)
│
├─ Save Binary Data to File
│  ├─ file_put_contents($path, $binary_data)
│  └─ File saved to /uploads/signatures/signature_*.png
│
└─ Store Reference in Database
   ├─ agreement.FreelancerName = "John Doe"
   └─ agreement.SignaturePath = "signature_*.png"

Database Storage
├─ Table: agreement
│  └─ Columns:
│     ├─ AgreementID (int)
│     ├─ FreelancerName (varchar) ← NEW
│     └─ SignaturePath (varchar) ← NEW
│
└─ Example Row:
   ├─ AgreementID: 42
   ├─ ProjectTitle: "E-commerce Platform"
   ├─ FreelancerName: "John Doe"
   └─ SignaturePath: "signature_1234567890_abc123def.png"

File System Storage
├─ Directory: /uploads/signatures/
│  └─ Permissions: 755 (rwxr-xr-x)
│
└─ Files:
   ├─ signature_1234567890_abc123def.png (150KB PNG)
   ├─ signature_1234567891_ghi456jkl.png (145KB PNG)
   └─ signature_1234567892_mno789pqr.png (155KB PNG)

PDF Generation (agreement_pdf.php)
├─ Query Database
│  ├─ SELECT * FROM agreement WHERE AgreementID = 42
│  └─ Result includes SignaturePath: "signature_1234567890_abc123def.png"
│
├─ Load Signature Image
│  ├─ Path: /uploads/signatures/signature_1234567890_abc123def.png
│  └─ Verify file exists
│
├─ Create PDF
│  ├─ Section 1-4: Agreement text
│  ├─ Section 5: Signature
│  │  ├─ $pdf->Image(path, x, y, width, height)
│  │  ├─ Embed signature.png (80mm x 50mm)
│  │  ├─ Draw signature line
│  │  └─ Print "Freelancer Signature: John Doe"
│  └─ Footer: Agreement ID and date
│
└─ Output PDF
   ├─ Generate filename: Agreement_E-commerce_Platform_42.pdf
   └─ Stream to browser for download
      └─ User downloads PDF with embedded signature

PDF File Delivered
├─ Agreement_E-commerce_Platform_42.pdf
└─ Contents:
   ├─ ✓ Project title and details
   ├─ ✓ Scope of work
   ├─ ✓ Deliverables & timeline
   ├─ ✓ Payment terms
   ├─ ✓ Terms & conditions
   ├─ ✓ Signature image (embedded PNG)
   ├─ ✓ Signature line
   ├─ ✓ Freelancer name: John Doe
   └─ ✓ Signed date
```

## Database Schema Update

```sql
BEFORE:
┌─────────────────────────────┐
│  agreement                  │
├─────────────────────────────┤
│ AgreementID (PK)            │
│ ProjectTitle                │
│ ProjectDetail               │
│ Scope                       │
│ Deliverables                │
│ PaymentAmount               │
│ Terms                       │
│ Status                      │
│ SignedDate                  │
└─────────────────────────────┘

AFTER (with new columns):
┌─────────────────────────────┐
│  agreement                  │
├─────────────────────────────┤
│ AgreementID (PK)            │
│ ProjectTitle                │
│ ProjectDetail               │
│ FreelancerName (NEW)        │
│ SignaturePath (NEW)         │
│ Scope                       │
│ Deliverables                │
│ PaymentAmount               │
│ Terms                       │
│ Status                      │
│ SignedDate                  │
└─────────────────────────────┘

SQL Command:
ALTER TABLE `agreement` 
ADD COLUMN `FreelancerName` varchar(255) NULL AFTER `ProjectDetail`;

ALTER TABLE `agreement` 
ADD COLUMN `SignaturePath` varchar(255) NULL AFTER `FreelancerName`;
```

## Security Flow

```
┌─────────────────────────────────────────────────┐
│        SECURITY & VALIDATION LAYERS             │
└─────────────────────────────────────────────────┘

CLIENT-SIDE (Browser - agreement.php)
├─ JavaScript Validation
│  ├─ Check signature drawn: signaturePad.isEmpty()
│  ├─ Check name not empty: freelancerName.value.trim()
│  ├─ Form submit validation
│  └─ Alert user of errors
│
└─ Data Format
   ├─ Signature: Base64 PNG string
   └─ Name: Plain text (trimmed)

NETWORK (POST Request)
├─ Standard HTTPS (in production)
├─ POST data encrypted
└─ Form fields:
   ├─ signature_data (base64)
   └─ freelancer_name (text)

SERVER-SIDE (PHP - agreement_process.php)
├─ Input Validation
│  ├─ Verify freelancer_name not empty
│  ├─ Verify signature_data exists
│  ├─ Validate base64 format
│  └─ Decode and verify PNG format
│
├─ File Security
│  ├─ Generate unique filename (prevent overwrite)
│  ├─ Validate PNG MIME type
│  ├─ Store outside publicly accessible path (optional)
│  └─ Set proper file permissions (644)
│
├─ Database Security
│  ├─ Use prepared statements
│  ├─ Parameterized queries
│  ├─ Input sanitization
│  └─ Escape special characters
│
└─ Error Handling
   ├─ Try-catch blocks
   ├─ Graceful failure
   └─ Log errors to file (not user-facing)

DATABASE (MySQL/MariaDB)
├─ Access Control
│  ├─ Prepared statements prevent SQL injection
│  └─ User permissions (web server user)
│
├─ Data Storage
│  ├─ FreelancerName: Text (255 chars max)
│  ├─ SignaturePath: Filename reference only
│  └─ No sensitive data in columns
│
└─ Integrity
   ├─ Primary key enforcement
   └─ Data type validation

FILE SYSTEM
├─ Directory Permissions
│  ├─ /uploads/signatures/: 755 (rwxr-xr-x)
│  └─ Readable by web server, not world-writable
│
├─ File Naming
│  ├─ Unique timestamps prevent collisions
│  ├─ Random uniqid() adds randomness
│  └─ Example: signature_1234567890_abc123.png
│
└─ File Content
   ├─ PNG format only (enforced by Signature Pad)
   ├─ Binary data, cannot execute as code
   └─ Image embedded in PDF, not served directly

PDF GENERATION (agreement_pdf.php)
├─ Data Retrieval
│  ├─ Query signed agreements from database
│  ├─ Verify file path exists before embedding
│  └─ Graceful degradation if file missing
│
├─ Image Embedding
│  ├─ TCPDF->Image() with file validation
│  ├─ Size constraints (80mm x 50mm)
│  └─ Proper image boundaries
│
└─ PDF Output
   ├─ Stream to browser with proper headers
   ├─ Filename: Agreement_[ProjectName]_[ID].pdf
   └─ User downloads PDF locally

FULL CHAIN SECURITY:
Frontend Validation
    ↓
Network Encryption (HTTPS)
    ↓
Backend Validation & Sanitization
    ↓
Unique Filenames & Permissions
    ↓
Prepared SQL Statements
    ↓
Secure PDF Generation
    ↓
Downloaded PDF File
```

## Signature Lifecycle

```
┌──────────────────────────────────────────────────────┐
│         SIGNATURE LIFECYCLE TIMELINE                 │
└──────────────────────────────────────────────────────┘

T=0 User Visits agreement.php
    ├─ Form loads with signature canvas
    └─ Signature Pad.js initialized

T=1-5 min User Draws Signature
      ├─ Multiple strokes on canvas
      ├─ Can clear and redraw multiple times
      └─ No data stored yet (client-side only)

T=5-10 min User Confirms Signature
       ├─ Clicks "Confirm Signature" button
       ├─ signaturePad.toDataURL() converts to base64
       ├─ Data stored in hidden form field
       ├─ Preview updates with signature image
       └─ Ready to submit form

T=10-11 min User Enters Full Name & Submits
         ├─ Types name in text field
         ├─ Clicks "Create Agreement" button
         ├─ Client-side validation passes
         └─ Form POST sent to server

T=11-12 sec Backend Processing
         ├─ agreement_process.php receives POST
         ├─ Validates all inputs
         ├─ Decodes base64 to PNG binary
         ├─ Generates unique filename
         ├─ Saves PNG to /uploads/signatures/
         ├─ Inserts agreement record in database
         │  ├─ FreelancerName = "John Doe"
         │  ├─ SignaturePath = "signature_*.png"
         │  └─ All other fields
         ├─ Stores data in $_SESSION
         └─ Redirects to success page

T=12-13 sec User Sees Success Message
         ├─ Confirmation that agreement created
         ├─ Agreement ID displayed
         └─ Option to download PDF

T=13-30 sec User Downloads PDF
         ├─ Clicks "Download PDF" button
         ├─ POST request to agreement_pdf.php
         ├─ Queries database for agreement
         ├─ Loads signature image from disk
         ├─ Generates PDF with embedded signature
         ├─ Sets download headers
         └─ Browser downloads PDF file

T=30 sec+ PDF File Available
        ├─ File: Agreement_ProjectName_ID.pdf
        ├─ Contains:
        │  ├─ All agreement details
        │  ├─ Signature image (embedded PNG)
        │  ├─ Freelancer name: John Doe
        │  ├─ Signature date
        │  └─ Professional formatting
        ├─ Stored locally on user's computer
        └─ Can be shared, printed, archived

ARCHIVE INDEFINITELY:
├─ Database Record (permanent)
│  ├─ Queryable by Agreement ID
│  ├─ Searchable by freelancer name
│  └─ Audit trail preserved
│
├─ Signature Image File (permanent)
│  ├─ Stored in /uploads/signatures/
│  ├─ Backup-protected
│  └─ Available for PDF regeneration
│
└─ PDF File (user's copy)
   ├─ Stored locally
   ├─ Immutable (already generated)
   └─ Physical record of signed agreement
```

---

This architecture ensures:
✓ Secure signature capture and storage
✓ Professional PDF generation with embedded signatures
✓ Database integrity and auditability
✓ File system security
✓ User experience throughout the signing process
