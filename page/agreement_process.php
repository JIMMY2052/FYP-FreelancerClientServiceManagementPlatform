<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_type'])) {
    $_SESSION['error'] = "You must be logged in to sign an agreement.";
    header("Location: login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: my_applications.php");
    exit();
}

// Determine workflow: new gig-based or job-application-based
$application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : null;
$project_title = isset($_POST['project_title']) ? trim($_POST['project_title']) : '';
$client_name = isset($_POST['client_name']) ? trim($_POST['client_name']) : '';
$signature_data = isset($_POST['signature_data']) ? $_POST['signature_data'] : '';
$freelancer_name = isset($_POST['freelancer_name']) ? trim($_POST['freelancer_name']) : '';

// Validate required fields based on workflow
$errors = [];

if (empty($client_name)) {
    $errors[] = "Client name is required.";
}

if (empty($signature_data)) {
    $errors[] = "Digital signature is required.";
}

// If there are validation errors, redirect back with error message
if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    if ($application_id) {
        header("Location: agreementClient.php?application_id=" . $application_id);
    } else {
        header("Location: agreement.php");
    }
    exit();
}

// Get database connection
$conn = getDBConnection();

// Save signature image to file
$uploads_dir = '../uploads/signatures/';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// Extract base64 data and save to file
$client_signature_filename = null;
if ($signature_data) {
    // Remove data:image/png;base64, prefix
    $signature_data_clean = str_replace('data:image/png;base64,', '', $signature_data);
    $signature_data_clean = base64_decode($signature_data_clean);

    // Generate unique filename
    $client_signature_filename = 'signature_client_' . time() . '_' . uniqid() . '.png';
    $signature_path = $uploads_dir . $client_signature_filename;

    file_put_contents($signature_path, $signature_data_clean);
}

// NEW WORKFLOW: Job Application Agreement Signing (from agreementClient.php)
if ($application_id) {
    // Verify application exists and user is the client
    $sql_verify = "SELECT ja.*, j.JobID, j.Budget, j.Title, j.Description, j.Deadline, j.ClientID, 
                   f.FreelancerID, f.FirstName as FFreelancerName, f.LastName as FFreelancerLast,
                   c.ClientID, c.CompanyName
                   FROM job_application ja
                   JOIN job j ON ja.JobID = j.JobID
                   JOIN freelancer f ON ja.FreelancerID = f.FreelancerID
                   JOIN client c ON j.ClientID = c.ClientID
                   WHERE ja.ApplicationID = ? AND j.ClientID = ?";

    $stmt_verify = $conn->prepare($sql_verify);
    if (!$stmt_verify) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: my_applications.php");
        exit();
    }

    $client_id = $_SESSION['user_id'];
    $stmt_verify->bind_param('ii', $application_id, $client_id);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();

    if ($result_verify->num_rows === 0) {
        $_SESSION['error'] = "Application not found or you don't have permission to sign this agreement.";
        header("Location: my_applications.php");
        exit();
    }

    $app_data = $result_verify->fetch_assoc();
    $freelancer_id = $app_data['FreelancerID'];
    $job_id = $app_data['JobID'];
    $job_budget = $app_data['Budget'];
    $job_title = $app_data['Title'];
    $job_desc = $app_data['Description'];
    $estimated_duration = $app_data['EstimatedDuration'] ?? '';

    // Get full freelancer name
    $full_freelancer_name = $app_data['FFreelancerName'] . ' ' . $app_data['FFreelancerLast'];

    // Generate PDF with client signature using TCPDF
    require_once '../vendor/autoload.php';
    $pdf = new \TCPDF();
    $pdf->SetDefaultMonospacedFont(\PDF_FONT_MONOSPACED);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(159, 232, 112); // Primary green
    $pdf->Cell(0, 10, 'SERVICE AGREEMENT', 0, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);

    // Agreement details
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(50, 6, 'Project Title:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, $job_title, 0, 1);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(50, 6, 'Freelancer:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, $full_freelancer_name, 0, 1);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(50, 6, 'Client:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, $client_name, 0, 1);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(50, 6, 'Budget:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, 'RM ' . number_format($job_budget, 2), 0, 1);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(50, 6, 'Project Description:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 6, $job_desc, 0, 'L');

    // Client Signature Section
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'Client Signature:', 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 4, 'Name: ' . $client_name, 0, 1);
    $pdf->Cell(0, 4, 'Date: ' . date('Y-m-d'), 0, 1);

    // Add signature image if available
    if ($client_signature_filename && file_exists($uploads_dir . $client_signature_filename)) {
        $pdf->Cell(50, 3, 'Signature:', 0, 0);
        $pdf->Image($uploads_dir . $client_signature_filename, $pdf->GetX() + 2, $pdf->GetY(), 40, 20);
        $pdf->Ln(22);
    }

    // Freelancer Signature Section (placeholder)
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'Freelancer Signature:', 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 4, 'Name: ' . $full_freelancer_name . ' (Pending)', 0, 1);
    $pdf->Cell(0, 4, 'Date: (Pending)', 0, 1);
    $pdf->Cell(50, 3, 'Signature:', 0, 0);
    $pdf->SetDrawColor(100, 100, 100);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect($pdf->GetX() + 2, $pdf->GetY(), 40, 20);

    // Save PDF
    $pdf_filename = 'agreement_job_' . $application_id . '_' . time() . '.pdf';
    $pdf_dir = '../uploads/agreements/';
    if (!is_dir($pdf_dir)) {
        mkdir($pdf_dir, 0755, true);
    }
    $pdf_path = $pdf_dir . $pdf_filename;
    $pdf->Output($pdf_path, 'F');

    // Send message to freelancer with PDF attachment
    $sender_id_composite = $_SESSION['user_id'] . '_client';
    $receiver_id_composite = $freelancer_id . '_freelancer';

    $message_content = "Your client has signed the agreement for the project \"" . $job_title . "\". Please review and sign to accept the project.";
    $attachment_path = '/uploads/agreements/' . $pdf_filename;
    $attachment_type = 'application/pdf';

    $sql_conv = "SELECT ConversationID FROM conversation 
                 WHERE (User1ID = CONCAT(?, '_client') AND User2ID = CONCAT(?, '_freelancer'))
                 OR (User1ID = CONCAT(?, '_freelancer') AND User2ID = CONCAT(?, '_client'))";

    $stmt_conv = $conn->prepare($sql_conv);
    $user1_id = $_SESSION['user_id'];
    $user2_id = $freelancer_id;
    $stmt_conv->bind_param('iiii', $user1_id, $user2_id, $user1_id, $user2_id);
    $stmt_conv->execute();
    $result_conv = $stmt_conv->get_result();

    $conversation_id = null;
    if ($result_conv->num_rows > 0) {
        $conv_data = $result_conv->fetch_assoc();
        $conversation_id = $conv_data['ConversationID'];
    } else {
        // Create new conversation
        $sql_new_conv = "INSERT INTO conversation (User1ID, User1Type, User2ID, User2Type, CreatedAt) 
                        VALUES (?, 'client', ?, 'freelancer', NOW())";
        $stmt_new_conv = $conn->prepare($sql_new_conv);
        $stmt_new_conv->bind_param('ii', $_SESSION['user_id'], $freelancer_id);
        $stmt_new_conv->execute();
        $conversation_id = $stmt_new_conv->insert_id;
    }

    $sql_msg = "INSERT INTO message (ConversationID, SenderID, ReceiverID, Content, AttachmentPath, AttachmentType, Timestamp, Status)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 'unread')";

    $stmt_msg = $conn->prepare($sql_msg);
    if (!$stmt_msg) {
        $_SESSION['error'] = "Error sending message: " . $conn->error;
        header("Location: my_applications.php");
        exit();
    }

    $stmt_msg->bind_param('isssss', $conversation_id, $sender_id_composite, $receiver_id_composite, $message_content, $attachment_path, $attachment_type);

    if ($stmt_msg->execute()) {
        $conn->close();
        $_SESSION['success'] = "Agreement signed successfully! A copy has been sent to the freelancer for their approval.";
        header("Location: my_applications.php");
        exit();
    } else {
        $_SESSION['error'] = "Error sending agreement to freelancer: " . $stmt_msg->error;
        header("Location: my_applications.php");
        exit();
    }
} else {
    // OLD WORKFLOW: Gig-based agreement (legacy code)
    $project_detail = isset($_POST['project_detail']) ? trim($_POST['project_detail']) : '';
    $scope = isset($_POST['scope']) ? trim($_POST['scope']) : '';
    $deliverables = isset($_POST['deliverables']) ? trim($_POST['deliverables']) : '';
    $payment = isset($_POST['payment']) ? floatval($_POST['payment']) : 0;
    $terms = isset($_POST['terms']) ? trim($_POST['terms']) : '';
    $gig_id = isset($_POST['gig_id']) ? intval($_POST['gig_id']) : null;

    if (empty($project_title)) {
        $errors[] = "Project title is required.";
    }
    if (empty($project_detail)) {
        $errors[] = "Project details are required.";
    }
    if (empty($scope)) {
        $errors[] = "Scope of work is required.";
    }
    if (empty($deliverables)) {
        $errors[] = "Deliverables & timeline is required.";
    }
    if ($payment <= 0) {
        $errors[] = "Payment amount must be greater than 0.";
    }
    if (empty($terms)) {
        $errors[] = "Terms & conditions are required.";
    }
    if (empty($freelancer_name)) {
        $errors[] = "Freelancer name is required.";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode(' ', $errors);
        header("Location: agreement.php");
        exit();
    }

    // Insert agreement into database (legacy gig workflow)
    $sql = "INSERT INTO agreement (ProjectTitle, ProjectDetail, Scope, Deliverables, PaymentAmount, Terms, FreelancerName, ClientName, GigID, SignaturePath, Status, SignedDate) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: agreement.php");
        exit();
    }

    $status = 'pending';
    $stmt->bind_param(
        'ssssdsssiss',
        $project_title,
        $project_detail,
        $scope,
        $deliverables,
        $payment,
        $terms,
        $freelancer_name,
        $client_name,
        $gig_id,
        $client_signature_filename,
        $status
    );

    if ($stmt->execute()) {
        $agreement_id = $stmt->insert_id;
        $conn->close();

        $_SESSION['agreement'] = array(
            'agreement_id' => $agreement_id,
            'project_title' => $project_title,
            'project_detail' => $project_detail,
            'scope' => $scope,
            'deliverables' => $deliverables,
            'payment' => $payment,
            'terms' => $terms,
            'freelancer_name' => $freelancer_name,
            'client_name' => $client_name,
            'signature_filename' => $client_signature_filename,
            'created_date' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'gig_id' => $gig_id
        );

        $_SESSION['agreement_freelancer_name'] = $freelancer_name;
        $_SESSION['agreement_client_name'] = $client_name;
        $_SESSION['success'] = "Agreement created successfully with ID: " . $agreement_id;

        header("Location: agreement_view.php?status=created&agreement_id=" . $agreement_id . "&send_to_chat=1");
        exit();
    } else {
        $_SESSION['error'] = "Error creating agreement: " . $stmt->error;
        header("Location: agreement.php");
        exit();
    }
}

$stmt->close();
$conn->close();
