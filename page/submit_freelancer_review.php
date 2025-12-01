<?php
session_start();
require_once 'config.php';

// Check if user is logged in as a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    $_SESSION['error'] = 'You must be logged in as a client to submit a review.';
    header('Location: login.php');
    exit();
}

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gig/browse_gigs.php');
    exit();
}

$client_id = $_SESSION['user_id'];
$freelancer_id = isset($_POST['freelancer_id']) ? intval($_POST['freelancer_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

// Validate inputs
if (!$freelancer_id || !$rating || empty($comment)) {
    $_SESSION['error'] = 'Please fill in all required fields.';
    header('Location: view_freelancer_profile.php?id=' . $freelancer_id);
    exit();
}

if ($rating < 1 || $rating > 5) {
    $_SESSION['error'] = 'Invalid rating value.';
    header('Location: view_freelancer_profile.php?id=' . $freelancer_id);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Check if client has collaborated with this freelancer
    $stmt = $conn->prepare("
        SELECT COUNT(*) as collab_count 
        FROM agreement 
        WHERE ClientID = ? AND FreelancerID = ? AND Status = 'completed'
    ");
    $stmt->bind_param("ii", $client_id, $freelancer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $collab_count = $result->fetch_assoc()['collab_count'] ?? 0;
    $stmt->close();
    
    if ($collab_count === 0) {
        $_SESSION['error'] = 'You must complete a project with this freelancer before leaving a review.';
        header('Location: view_freelancer_profile.php?id=' . $freelancer_id);
        exit();
    }
    
    // Check if client has already reviewed this freelancer
    $stmt = $conn->prepare("
        SELECT ReviewID 
        FROM review 
        WHERE ClientID = ? AND FreelancerID = ?
    ");
    $stmt->bind_param("ii", $client_id, $freelancer_id);
    $stmt->execute();
    $review_check = $stmt->get_result();
    
    if ($review_check->num_rows > 0) {
        $_SESSION['error'] = 'You have already submitted a review for this freelancer.';
        $stmt->close();
        $conn->close();
        header('Location: view_freelancer_profile.php?id=' . $freelancer_id);
        exit();
    }
    $stmt->close();
    
    // Insert the review
    $stmt = $conn->prepare("
        INSERT INTO review (ClientID, FreelancerID, Rating, Comment, ReviewDate) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iiis", $client_id, $freelancer_id, $rating, $comment);
    
    if ($stmt->execute()) {
        $review_id = $stmt->insert_id;
        $stmt->close();
        
        // Update freelancer's average rating
        $stmt = $conn->prepare("
            UPDATE freelancer 
            SET Rating = (
                SELECT AVG(Rating) 
                FROM review 
                WHERE FreelancerID = ?
            )
            WHERE FreelancerID = ?
        ");
        $stmt->bind_param("ii", $freelancer_id, $freelancer_id);
        $stmt->execute();
        $stmt->close();
        
        // Create notification for freelancer
        $stmt = $conn->prepare("
            INSERT INTO notifications (UserID, UserType, Message, IsRead, CreatedAt) 
            VALUES (?, 'freelancer', ?, 0, NOW())
        ");
        $notification_message = "You have received a new " . $rating . "-star review from a client!";
        $stmt->bind_param("is", $freelancer_id, $notification_message);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['success'] = 'Thank you! Your review has been submitted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to submit review. Please try again.';
    }
    
    $conn->close();
    
} catch (Exception $e) {
    error_log('Review submission error: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while submitting your review.';
}

header('Location: view_freelancer_profile.php?id=' . $freelancer_id);
exit();
?>
