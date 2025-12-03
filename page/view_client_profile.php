<?php
session_start();

// Check if user is logged in as freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

// Get client ID from URL
$clientID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$clientID) {
    header('Location: browse_job.php');
    exit();
}

$conn = getDBConnection();

// Fetch client information
$stmt = $conn->prepare("SELECT * FROM client WHERE ClientID = ?");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();
$stmt->close();

if (!$client) {
    header('Location: browse_job.php');
    exit();
}

// Get total jobs posted by this client
$stmt = $conn->prepare("SELECT COUNT(*) as job_count FROM job WHERE ClientID = ? AND Status = 'available'");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$job_result = $stmt->get_result();
$job_count = $job_result->fetch_assoc()['job_count'];
$stmt->close();

// Get completed projects count
$stmt = $conn->prepare("SELECT COUNT(*) as completed_count FROM job WHERE ClientID = ? AND Status = 'complete'");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$completed_result = $stmt->get_result();
$completed_count = $completed_result->fetch_assoc()['completed_count'];
$stmt->close();

// Get client's recent jobs
$stmt = $conn->prepare("SELECT JobID, Title, Budget, Status, PostDate FROM job WHERE ClientID = ? ORDER BY PostDate DESC LIMIT 5");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$jobs_result = $stmt->get_result();
$recent_jobs = [];
while ($row = $jobs_result->fetch_assoc()) {
    $recent_jobs[] = $row;
}
$stmt->close();

// Check if current freelancer has collaborated with this client
$freelancerID = $_SESSION['user_id'];
$has_collaborated = false;
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM agreement a
    WHERE a.FreelancerID = ? AND a.ClientID = ? AND a.Status = 'complete'
");
$stmt->bind_param("ii", $freelancerID, $clientID);
$stmt->execute();
$collab_result = $stmt->get_result();
$has_collaborated = $collab_result->fetch_assoc()['count'] > 0;
$stmt->close();

// Check if freelancer has already reviewed this client
$has_reviewed = false;
$stmt = $conn->prepare("SELECT ReviewID FROM review WHERE FreelancerID = ? AND ClientID = ?");
$stmt->bind_param("ii", $freelancerID, $clientID);
$stmt->execute();
$review_check = $stmt->get_result();
$has_reviewed = $review_check->num_rows > 0;
$stmt->close();

// Get all reviews for this client
$stmt = $conn->prepare("
    SELECT r.ReviewID, r.Rating, r.Comment, r.ReviewDate, 
           f.FirstName, f.LastName, f.ProfilePicture
    FROM review r
    INNER JOIN freelancer f ON r.FreelancerID = f.FreelancerID
    WHERE r.ClientID = ?
    ORDER BY r.ReviewDate DESC
");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$reviews_result = $stmt->get_result();
$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

// Calculate average rating
$avg_rating = 0;
if (!empty($reviews)) {
    $total_rating = array_sum(array_column($reviews, 'Rating'));
    $avg_rating = $total_rating / count($reviews);
}

$conn->close();

// Process profile picture path
$profilePic = $client['ProfilePicture'];
if ($profilePic && !empty($profilePic) && strpos($profilePic, 'http') !== 0) {
    if (strpos($profilePic, '/') !== 0) {
        $profilePic = '/' . $profilePic;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($client['CompanyName']) ?> - WorkSnyc</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/profile.css">
    <link rel="stylesheet" href="/assets/css/client-profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .reviews-section {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-top: 2rem;
        }
        
        .review-form-container {
            background: #f9fafb;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #e9ecef;
        }
        
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 0.25rem;
        }
        
        .star-rating input[type="radio"] {
            display: none;
        }
        
        .star-rating label {
            cursor: pointer;
            font-size: 2rem;
            color: #d1d5db;
            transition: color 0.2s;
        }
        
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #fbbf24;
        }
        
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .review-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .review-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .reviewer-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            position: relative;
        }
        
        .reviewer-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .avatar-initial {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            position: absolute;
            top: 0;
            left: 0;
        }
        
        .reviewer-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
        }
        
        .review-date {
            color: #6b7280;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        
        .review-rating {
            display: flex;
            gap: 0.25rem;
        }
        
        .review-rating .star {
            color: #d1d5db;
            font-size: 1.25rem;
        }
        
        .review-rating .star.filled {
            color: #fbbf24;
        }
        
        .review-comment {
            color: #4b5563;
            line-height: 1.6;
            font-size: 0.95rem;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .reviews-section {
                padding: 1.5rem;
            }
            
            .review-form-container {
                padding: 1.5rem;
            }
            
            .review-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .star-rating label {
                font-size: 1.75rem;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/freelancer_sidebar.php'; ?>

    <div class="container">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #c3e6cb; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #f5c6cb; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Back Button -->
        <div style="margin-bottom: 2rem;">
            <a href="<?= isset($_GET['source']) && $_GET['source'] === 'messages' ? 'messages.php' : (isset($_SESSION['job_id']) ? 'job/job_details.php?id=' . intval($_SESSION['job_id']) : 'job/browse_job.php') ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #16a34a; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>

        <!-- Hero Section -->
        <div class="profile-hero">
            <div class="hero-avatar-section">
                <?php if ($profilePic && !empty($profilePic)): ?>
                    <img src="<?= htmlspecialchars($profilePic) ?>" alt="<?= htmlspecialchars($client['CompanyName']) ?>" class="hero-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <?php endif; ?>
                <div class="hero-avatar-initial" style="<?= ($profilePic && !empty($profilePic)) ? 'display:none;' : 'display:flex;' ?>">
                    <?= strtoupper(substr($client['CompanyName'], 0, 1)) ?>
                </div>
            </div>

            <div class="hero-info">
                <h1 class="hero-name"><?= htmlspecialchars($client['CompanyName']) ?></h1>
                <?php if (!empty($reviews)): ?>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                        <span style="color: #fbbf24; font-size: 1.2rem;">⭐</span>
                        <span style="font-weight: 700; font-size: 1.1rem; color: #2c3e50;"><?= number_format($avg_rating, 1) ?></span>
                        <span style="color: #6b7280; font-size: 0.9rem;">(<?= count($reviews) ?> <?= count($reviews) == 1 ? 'review' : 'reviews' ?>)</span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($client['Description'])): ?>
                    <p class="hero-description"><?= htmlspecialchars($client['Description']) ?></p>
                <?php endif; ?>

                <!-- Stats Section -->
                <div class="stats-container" style="display: flex; gap: 2rem; margin-top: 2rem; flex-wrap: wrap;">
                    <!-- Active Projects Stat -->
                    <div class="stat-card" style="flex: 1; min-width: 180px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); padding: 2rem; border-radius: 16px; box-shadow: 0 4px 15px rgba(22, 163, 74, 0.2); color: white; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                            <div>
                                <div style="font-size: 2.5rem; font-weight: 900; line-height: 1; margin-bottom: 0.5rem;"><?= $job_count ?></div>
                                <div style="font-size: 0.95rem; opacity: 0.9; font-weight: 500;">Active Projects</div>
                            </div>
                            <div style="font-size: 2.5rem; opacity: 0.3;">
                                <i class="fas fa-briefcase"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Projects Stat -->
                    <div class="stat-card" style="flex: 1; min-width: 180px; background: linear-gradient(135deg, #0f766e 0%, #0d5f59 100%); padding: 2rem; border-radius: 16px; box-shadow: 0 4px 15px rgba(15, 118, 110, 0.2); color: white; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                            <div>
                                <div style="font-size: 2.5rem; font-weight: 900; line-height: 1; margin-bottom: 0.5rem;"><?= $completed_count ?></div>
                                <div style="font-size: 0.95rem; opacity: 0.9; font-weight: 500;">Completed Projects</div>
                            </div>
                            <div style="font-size: 2.5rem; opacity: 0.3;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="messages.php?client_id=<?= $clientID ?>" class="action-btn btn-contact">
                        <i class="fas fa-comment-dots"></i> Send Message
                    </a>
                    <a href="job/browse_job.php?q=<?= urlencode($client['CompanyName']) ?>" class="action-btn btn-browse">
                        <i class="fas fa-briefcase"></i> Browse Their Projects
                    </a>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="contact-section">
            <h2 class="section-title">Contact Information</h2>
            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-label">Email</div>
                    <div class="contact-value">
                        <a href="mailto:<?= htmlspecialchars($client['Email']) ?>" class="contact-link">
                            <?= htmlspecialchars($client['Email']) ?>
                        </a>
                    </div>
                </div>
                <?php if (!empty($client['PhoneNo'])): ?>
                    <div class="contact-item">
                        <div class="contact-label">Phone</div>
                        <div class="contact-value">
                            <a href="tel:<?= htmlspecialchars($client['PhoneNo']) ?>" class="contact-link">
                                <?= htmlspecialchars($client['PhoneNo']) ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Jobs Section -->
        <?php if (!empty($recent_jobs)): ?>
            <div class="recent-jobs-section">
                <h2 class="section-title">Recent Projects</h2>
                <div class="jobs-list">
                    <?php foreach ($recent_jobs as $job): ?>
                        <div class="job-item">
                            <div class="job-title"><?= htmlspecialchars($job['Title']) ?></div>
                            <div class="job-meta">
                                <span>Posted: <?= date('M d, Y', strtotime($job['PostDate'])) ?></span>
                                <span class="job-budget">RM <?= number_format($job['Budget'], 0) ?></span>
                                <span class="job-status <?= $job['Status'] ?>"><?= ucfirst($job['Status']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <h2 class="section-title">Reviews & Ratings</h2>
            
            <!-- Add Review Form (only if collaborated and not reviewed yet) -->
            <?php if ($has_collaborated && !$has_reviewed): ?>
                <div class="review-form-container">
                    <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: #2c3e50;">Share Your Experience</h3>
                    <form method="POST" action="submit_client_review.php" class="review-form">
                        <input type="hidden" name="client_id" value="<?= $clientID ?>">
                        
                        <div class="form-group">
                            <label for="rating" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Rating</label>
                            <div class="star-rating">
                                <input type="radio" id="star5" name="rating" value="5" required>
                                <label for="star5" title="5 stars">★</label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4" title="4 stars">★</label>
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3" title="3 stars">★</label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2" title="2 stars">★</label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1" title="1 star">★</label>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <label for="comment" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Your Review</label>
                            <textarea id="comment" name="comment" rows="4" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem; font-family: inherit; resize: vertical;" placeholder="Share your experience working with this client..."></textarea>
                        </div>
                        
                        <button type="submit" style="margin-top: 1rem; padding: 0.75rem 2rem; background: rgb(159, 232, 112); color: #333; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">Submit Review</button>
                    </form>
                </div>
            <?php elseif (!$has_collaborated): ?>
                <div style="background: #f3f4f6; padding: 1.5rem; border-radius: 12px; text-align: center; color: #6b7280; margin-bottom: 2rem;">
                    <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block;"></i>
                    <p style="margin: 0;">You need to complete a project with this client before you can leave a review.</p>
                </div>
            <?php elseif ($has_reviewed): ?>
                <div style="background: #e8f5e9; padding: 1.5rem; border-radius: 12px; text-align: center; color: #2e7d32; margin-bottom: 2rem;">
                    <i class="fas fa-check-circle" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block;"></i>
                    <p style="margin: 0;">You have already reviewed this client. Thank you for your feedback!</p>
                </div>
            <?php endif; ?>
            
            <!-- Display Reviews -->
            <?php if (!empty($reviews)): ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <?php 
                            $reviewerPic = $review['ProfilePicture'];
                            if ($reviewerPic && !empty($reviewerPic) && strpos($reviewerPic, 'http') !== 0) {
                                if (strpos($reviewerPic, '/') !== 0) {
                                    $reviewerPic = '/' . $reviewerPic;
                                }
                            }
                        ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <?php if ($reviewerPic && !empty($reviewerPic)): ?>
                                            <img src="<?= htmlspecialchars($reviewerPic) ?>" alt="<?= htmlspecialchars($review['FirstName']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <?php endif; ?>
                                        <div class="avatar-initial" style="<?= ($reviewerPic && !empty($reviewerPic)) ? 'display:none;' : 'display:flex;' ?>">
                                            <?= strtoupper(substr($review['FirstName'], 0, 1)) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="reviewer-name"><?= htmlspecialchars($review['FirstName'] . ' ' . $review['LastName']) ?></div>
                                        <div class="review-date"><?= date('F j, Y', strtotime($review['ReviewDate'])) ?></div>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <span class="star <?= $i < $review['Rating'] ? 'filled' : '' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="review-comment"><?= nl2br(htmlspecialchars($review['Comment'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; background: #f9fafb; border-radius: 12px; color: #6b7280;">
                    <i class="fas fa-star" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem; display: block;"></i>
                    <p style="font-size: 1.1rem; margin: 0;">No reviews yet. Be the first to review this client!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../_foot.php'; ?>
</body>

</html>