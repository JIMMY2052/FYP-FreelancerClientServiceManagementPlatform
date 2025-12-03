<?php
session_start();

// Check if user is logged in as freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: job/browse_job.php');
    exit();
}

$freelancerID = $_SESSION['user_id'];
$clientID = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

// Validate inputs
if (!$clientID || $rating < 1 || $rating > 5 || empty($comment)) {
    $_SESSION['error'] = 'Please provide a valid rating and comment.';
    header('Location: view_client_profile.php?id=' . $clientID);
    exit();
}

$conn = getDBConnection();

try {
    // Verify that freelancer has collaborated with this client
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM agreement a
        WHERE a.FreelancerID = ? AND a.ClientID = ? AND a.Status = 'complete'
    ");
    $stmt->bind_param("ii", $freelancerID, $clientID);
    $stmt->execute();
    $result = $stmt->get_result();
    $has_collaborated = $result->fetch_assoc()['count'] > 0;
    $stmt->close();

    if (!$has_collaborated) {
        $_SESSION['error'] = 'You can only review clients you have worked with.';
        header('Location: view_client_profile.php?id=' . $clientID);
        exit();
    }

    // Check if already reviewed
    $stmt = $conn->prepare("SELECT ReviewID FROM review WHERE FreelancerID = ? AND ClientID = ?");
    $stmt->bind_param("ii", $freelancerID, $clientID);
    $stmt->execute();
    $existing_review = $stmt->get_result();
    
    if ($existing_review->num_rows > 0) {
        $_SESSION['error'] = 'You have already reviewed this client.';
        $stmt->close();
        header('Location: view_client_profile.php?id=' . $clientID);
        exit();
    }
    $stmt->close();

    // Insert the review
    $stmt = $conn->prepare("
        INSERT INTO review (FreelancerID, ClientID, Rating, Comment, ReviewDate)
        VALUES (?, ?, ?, ?, CURDATE())
    ");
    $stmt->bind_param("iiis", $freelancerID, $clientID, $rating, $comment);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Thank you! Your review has been submitted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to submit review. Please try again.';
    }
    
    $stmt->close();
    $conn->close();
    
    header('Location: view_client_profile.php?id=' . $clientID);
    exit();

} catch (Exception $e) {
    error_log('Review submission error: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while submitting your review.';
    $conn->close();
    header('Location: view_client_profile.php?id=' . $clientID);
    exit();
}
?>
