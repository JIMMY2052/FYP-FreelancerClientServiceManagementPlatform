<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_type'])) {
    $_SESSION['error'] = "You must be logged in to create an agreement.";
    header("Location: login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: agreement.php");
    exit();
}

// Get POST data
$project_title = isset($_POST['project_title']) ? trim($_POST['project_title']) : '';
$project_detail = isset($_POST['project_detail']) ? trim($_POST['project_detail']) : '';
$scope = isset($_POST['scope']) ? trim($_POST['scope']) : '';
$deliverables = isset($_POST['deliverables']) ? trim($_POST['deliverables']) : '';
$payment = isset($_POST['payment']) ? floatval($_POST['payment']) : 0;
$terms = isset($_POST['terms']) ? trim($_POST['terms']) : '';
$freelancer_name = isset($_POST['freelancer_name']) ? trim($_POST['freelancer_name']) : '';
$signature_data = isset($_POST['signature_data']) ? $_POST['signature_data'] : '';

// Validate required fields
$errors = [];

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

if (empty($signature_data)) {
    $errors[] = "Digital signature is required.";
}

// If there are validation errors, redirect back with error message
if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    header("Location: agreement.php");
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
$signature_filename = null;
if ($signature_data) {
    // Remove data:image/png;base64, prefix
    $signature_data_clean = str_replace('data:image/png;base64,', '', $signature_data);
    $signature_data_clean = base64_decode($signature_data_clean);

    // Generate unique filename
    $signature_filename = 'signature_' . time() . '_' . uniqid() . '.png';
    $signature_path = $uploads_dir . $signature_filename;

    file_put_contents($signature_path, $signature_data_clean);
}

// Insert agreement into database
$sql = "INSERT INTO agreement (ProjectTitle, ProjectDetail, Scope, Deliverables, PaymentAmount, Terms, FreelancerName, SignaturePath, Status, SignedDate) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: agreement.php");
    exit();
}

// Bind parameters
$status = 'pending';
$stmt->bind_param(
    'ssssdsss',
    $project_title,
    $project_detail,
    $scope,
    $deliverables,
    $payment,
    $terms,
    $freelancer_name,
    $signature_filename,
    $status
);

// Execute statement
if ($stmt->execute()) {
    // Get the ID of the newly inserted agreement
    $agreement_id = $stmt->insert_id;

    // Store agreement data in session
    $_SESSION['agreement'] = array(
        'agreement_id' => $agreement_id,
        'project_title' => $project_title,
        'project_detail' => $project_detail,
        'scope' => $scope,
        'deliverables' => $deliverables,
        'payment' => $payment,
        'terms' => $terms,
        'freelancer_name' => $freelancer_name,
        'signature_filename' => $signature_filename,
        'created_date' => date('Y-m-d H:i:s'),
        'status' => 'pending'
    );

    $_SESSION['success'] = "Agreement created successfully with ID: " . $agreement_id;

    // Redirect to view the created agreement
    header("Location: agreement_view.php?status=created");
    exit();
} else {
    $_SESSION['error'] = "Error creating agreement: " . $stmt->error;
    header("Location: agreement.php");
    exit();
}

// Close statement and connection
$stmt->close();
$conn->close();
