<?php
session_start();
require_once 'config.php';

$conn = getDBConnection();
$freelancer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$freelancer_id) {
    header('Location: browse_gigs.php');
    exit();
}

// Get freelancer information
$stmt = $conn->prepare("
    SELECT 
        FreelancerID, FirstName, LastName, Email, PhoneNo, Address, 
        Experience, Education, Bio, Rating, ProfilePicture,
        TotalEarned, SocialMediaURL, JoinedDate
    FROM freelancer 
    WHERE FreelancerID = ?
");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$freelancer = $result->fetch_assoc();
$stmt->close();

if (!$freelancer) {
    header('Location: browse_gigs.php');
    exit();
}

// Get freelancer skills
$stmt = $conn->prepare("
    SELECT s.SkillName, fs.ProficiencyLevel 
    FROM freelancerskill fs
    INNER JOIN skill s ON fs.SkillID = s.SkillID
    WHERE fs.FreelancerID = ?
    ORDER BY fs.ProficiencyLevel DESC
");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$skills_result = $stmt->get_result();
$skills = [];
while ($row = $skills_result->fetch_assoc()) {
    $skills[] = $row;
}
$stmt->close();

// Get total completed projects (from agreements)
$stmt = $conn->prepare("
    SELECT COUNT(*) as completed_count 
    FROM agreement 
    WHERE FreelancerID = ? AND Status = 'completed'
");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$completed_result = $stmt->get_result();
$completed_count = $completed_result->fetch_assoc()['completed_count'] ?? 0;
$stmt->close();

// Check if current user (client) has collaborated with this freelancer
$can_review = false;
$has_reviewed = false;
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'client') {
    $client_id = $_SESSION['user_id'];

    // Check if client has any completed agreements with this freelancer
    // This includes: gigs purchased or jobs where freelancer was hired
    $stmt = $conn->prepare("
        SELECT COUNT(*) as collab_count 
        FROM agreement 
        WHERE ClientID = ? AND FreelancerID = ? AND Status = 'completed'
    ");
    $stmt->bind_param("ii", $client_id, $freelancer_id);
    $stmt->execute();
    $collab_result = $stmt->get_result();
    $collab_count = $collab_result->fetch_assoc()['collab_count'] ?? 0;
    $stmt->close();

    if ($collab_count > 0) {
        $can_review = true;

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
            $has_reviewed = true;
        }
        $stmt->close();
    }
}

// Get reviews and ratings
$stmt = $conn->prepare("
    SELECT r.ReviewID, r.Rating, r.Comment, r.ReviewDate, c.CompanyName, c.ClientID
    FROM review r
    LEFT JOIN client c ON r.ClientID = c.ClientID
    WHERE r.FreelancerID = ?
    ORDER BY r.ReviewDate DESC
");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

// Calculate average rating
$average_rating = 0;
if (!empty($reviews)) {
    $total_rating = 0;
    foreach ($reviews as $review) {
        $total_rating += $review['Rating'];
    }
    $average_rating = $total_rating / count($reviews);
}

// Get active gigs
$stmt = $conn->prepare("
    SELECT GigID, Title, Category, Price, Status
    FROM gig
    WHERE FreelancerID = ? AND Status = 'active'
    ORDER BY CreatedAt DESC
    LIMIT 6
");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$gigs_result = $stmt->get_result();
$gigs = [];
while ($row = $gigs_result->fetch_assoc()) {
    $gigs[] = $row;
}
$stmt->close();

// Get years of experience
$member_since = new DateTime($freelancer['JoinedDate']);
$now = new DateTime();
$years_experience = $now->diff($member_since)->y + 1;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($freelancer['FirstName'] . ' ' . $freelancer['LastName']) ?> - Freelancer Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/client.css">
    <link rel="stylesheet" href="../assets/css/freelancer-profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php require_once '../_head.php'; ?>

    <div class="profile-breadcrumb">
        <div class="breadcrumb-container">
            <a href="gig/browse_gigs.php"><i class="fas fa-arrow-left"></i> Back to Browse</a>
        </div>
    </div>

    <div class="profile-container">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Hero Section -->
        <div class="profile-hero">
            <div class="profile-avatar-large">
                <?php
                $profilePicSrc = '';
                if (!empty($freelancer['ProfilePicture'])) {
                    $picPath = $freelancer['ProfilePicture'];
                    if (strpos($picPath, '/') !== 0 && strpos($picPath, 'http') !== 0) {
                        $picPath = '/' . $picPath;
                    }
                    $profilePicSrc = $picPath;
                }
                ?>
                <?php if (!empty($profilePicSrc)): ?>
                    <img src="<?= htmlspecialchars($profilePicSrc) ?>"
                        alt="<?= htmlspecialchars($freelancer['FirstName']) ?>"
                        onerror="this.style.display='none'; this.parentElement.style.display='flex'; this.parentElement.style.alignItems='center'; this.parentElement.style.justifyContent='center';">
                <?php else: ?>
                    <?= strtoupper(substr($freelancer['FirstName'], 0, 1)) ?>
                <?php endif; ?>
            </div>

            <div class="profile-info-header">
                <div class="profile-name-title">
                    <h1 class="profile-name"><?= htmlspecialchars($freelancer['FirstName'] . ' ' . $freelancer['LastName']) ?></h1>
                </div>

                <?php if (!empty($freelancer['Bio'])): ?>
                    <p class="profile-bio"><?= htmlspecialchars($freelancer['Bio']) ?></p>
                <?php endif; ?>

                <div class="profile-stats-row">
                    <div class="stat-box">
                        <div class="stat-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?= $completed_count ?></div>
                            <div class="stat-label">Completed Projects</div>
                        </div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?= $years_experience ?></div>
                            <div class="stat-label">Years Member</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Form Section (Only for clients who have collaborated) -->
        <?php if ($can_review && !$has_reviewed): ?>
            <div class="review-form-section">
                <h3 class="review-form-title">Leave a Review</h3>
                <div class="review-note">
                    <i class="fas fa-check-circle"></i>
                    <span>You have worked with this freelancer and can leave a review.</span>
                </div>
                <form class="review-form" method="POST" action="submit_freelancer_review.php" id="reviewForm">
                    <input type="hidden" name="freelancer_id" value="<?= $freelancer_id ?>">

                    <div class="form-group">
                        <label class="form-label">Rating *</label>
                        <div class="star-rating" id="starRating">
                            <i class="far fa-star" data-rating="1"></i>
                            <i class="far fa-star" data-rating="2"></i>
                            <i class="far fa-star" data-rating="3"></i>
                            <i class="far fa-star" data-rating="4"></i>
                            <i class="far fa-star" data-rating="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Your Review *</label>
                        <textarea name="comment" class="review-textarea" placeholder="Share your experience working with this freelancer..." required></textarea>
                    </div>

                    <button type="submit" class="btn-submit-review">Submit Review</button>
                </form>
            </div>
        <?php elseif ($has_reviewed): ?>
            <div class="review-form-section">
                <div class="review-note info">
                    <i class="fas fa-info-circle"></i>
                    <span>You have already submitted a review for this freelancer.</span>
                </div>
            </div>
        <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'client'): ?>
            <div class="review-form-section">
                <div class="review-note warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>You need to complete at least one project with this freelancer before you can leave a review.</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Rating Section -->
        <div class="rating-section">
            <div class="rating-header">
                <div class="rating-display">
                    <div class="rating-number"><?= number_format($average_rating, 1) ?></div>
                    <div class="rating-stars">
                        <?php
                        $full_stars = floor($average_rating);
                        $has_half = ($average_rating - $full_stars) >= 0.5;

                        for ($i = 0; $i < 5; $i++) {
                            if ($i < $full_stars) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif ($i == $full_stars && $has_half) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <div class="rating-count"><?= count($reviews) ?> review<?= count($reviews) !== 1 ? 's' : '' ?></div>
                </div>
                <div class="rating-info">
                    <p><strong>Rating Summary</strong></p>
                    <p>Based on <?= count($reviews) ?> client review<?= count($reviews) !== 1 ? 's' : '' ?>, this freelancer has maintained an excellent track record of delivering quality work on time and exceeding client expectations.</p>
                </div>
            </div>

            <!-- Reviews -->
            <div class="reviews-section">
                <div class="reviews-title">Client Reviews</div>
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div>
                                    <div class="review-author"><?= htmlspecialchars($review['CompanyName'] ?? 'Anonymous Client') ?></div>
                                    <div class="review-date"><?= date('M d, Y', strtotime($review['ReviewDate'])) ?></div>
                                </div>
                                <div class="review-rating">
                                    <?php
                                    for ($i = 0; $i < $review['Rating']; $i++) {
                                        echo '<i class="fas fa-star"></i>';
                                    }
                                    for ($i = $review['Rating']; $i < 5; $i++) {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php if (!empty($review['Comment'])): ?>
                                <div class="review-comment">
                                    <?= nl2br(htmlspecialchars($review['Comment'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reviews">
                        <p>No reviews yet. Hire this freelancer to leave the first review!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Content -->
        <div class="profile-main">
            <!-- Left Column -->
            <div>
                <!-- About Section -->
                <?php if (!empty($freelancer['Experience']) || !empty($freelancer['Education'])): ?>
                    <div class="about-section">
                        <h2 class="section-title">About</h2>

                        <?php if (!empty($freelancer['Experience'])): ?>
                            <div class="about-item">
                                <div class="about-label">
                                    <i class="fas fa-briefcase"></i> Experience
                                </div>
                                <div class="about-text"><?= nl2br(htmlspecialchars($freelancer['Experience'])) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($freelancer['Education'])): ?>
                            <div class="about-item">
                                <div class="about-label">
                                    <i class="fas fa-graduation-cap"></i> Education
                                </div>
                                <div class="about-text"><?= nl2br(htmlspecialchars($freelancer['Education'])) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Skills Section -->
                <?php if (!empty($skills)): ?>
                    <div class="about-section">
                        <h2 class="section-title">Skills</h2>
                        <div class="skills-container">
                            <?php foreach ($skills as $skill): ?>
                                <div class="skill-tag">
                                    <?= htmlspecialchars($skill['SkillName']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Gigs Section -->
                <?php if (!empty($gigs)): ?>
                    <div class="gigs-section">
                        <div class="section-header">
                            <h2 class="section-title">Active Gigs</h2>
                            <a href="gig/browse_gigs.php?freelancer=<?= $freelancer_id ?>" class="view-all-link">View All</a>
                        </div>
                        <div class="gigs-grid">
                            <?php foreach ($gigs as $gig): ?>
                                <a href="gig/gig_details.php?id=<?= $gig['GigID'] ?>" style="text-decoration: none; color: inherit;">
                                    <div class="gig-card">
                                        <div class="gig-card-title"><?= htmlspecialchars($gig['Title']) ?></div>
                                        <div class="gig-card-category"><?= htmlspecialchars($gig['Category']) ?></div>
                                        <div class="gig-card-footer">
                                            <span>RM<?= number_format($gig['Price'], 0) ?></span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column - Sidebar -->
            <div class="sidebar">
                <!-- Contact Card -->
                <div class="contact-card">
                    <h2 class="section-title">Contact Information</h2>

                    <?php if (!empty($freelancer['Email'])): ?>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Email</div>
                                <div class="contact-value">
                                    <a href="mailto:<?= htmlspecialchars($freelancer['Email']) ?>">
                                        <?= htmlspecialchars($freelancer['Email']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($freelancer['PhoneNo'])): ?>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-phone"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Phone</div>
                                <div class="contact-value"><?= htmlspecialchars($freelancer['PhoneNo']) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($freelancer['Address'])): ?>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Location</div>
                                <div class="contact-value"><?= htmlspecialchars($freelancer['Address']) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($freelancer['SocialMediaURL'])): ?>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-link"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Social Media</div>
                                <div class="contact-value">
                                    <a href="<?= htmlspecialchars($freelancer['SocialMediaURL']) ?>" target="_blank">
                                        View Profile <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <button class="btn-message" onclick="contactFreelancer(<?= $freelancer_id ?>)">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </div>

                <!-- Member Badge -->
                <div class="member-badge">
                    <i class="fas fa-check-circle"></i>
                    <div class="member-title">Verified Member</div>
                    <div class="member-date">Since <?= date('M Y', strtotime($freelancer['JoinedDate'])) ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../_foot.php'; ?>

    <script>
        function contactFreelancer(freelancerId) {
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'client'): ?>
                window.location.href = 'messages.php?freelancer=' + freelancerId;
            <?php else: ?>
                alert('Please log in as a client to contact this freelancer.');
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
            <?php endif; ?>
        }

        // Star rating functionality
        document.addEventListener('DOMContentLoaded', function() {
            const starRating = document.getElementById('starRating');
            if (starRating) {
                const stars = starRating.querySelectorAll('i');
                const ratingValue = document.getElementById('ratingValue');

                stars.forEach(star => {
                    star.addEventListener('click', function() {
                        const rating = this.getAttribute('data-rating');
                        ratingValue.value = rating;

                        // Update star display
                        stars.forEach((s, index) => {
                            if (index < rating) {
                                s.classList.remove('far');
                                s.classList.add('fas', 'active');
                            } else {
                                s.classList.remove('fas', 'active');
                                s.classList.add('far');
                            }
                        });
                    });

                    star.addEventListener('mouseenter', function() {
                        const rating = this.getAttribute('data-rating');
                        stars.forEach((s, index) => {
                            if (index < rating) {
                                s.classList.add('active');
                            } else {
                                s.classList.remove('active');
                            }
                        });
                    });
                });

                starRating.addEventListener('mouseleave', function() {
                    const currentRating = ratingValue.value;
                    stars.forEach((s, index) => {
                        if (currentRating && index < currentRating) {
                            s.classList.add('active');
                        } else if (!s.classList.contains('fas')) {
                            s.classList.remove('active');
                        }
                    });
                });
            }
        });
    </script>
</body>

</html>