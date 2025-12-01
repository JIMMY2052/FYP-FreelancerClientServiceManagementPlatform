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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: #1e293b;
            line-height: 1.6;
        }

        .profile-breadcrumb {
            background: white;
            padding: 1.2rem 0;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 70px;
            z-index: 50;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .breadcrumb-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .breadcrumb-container a {
            color: #16a34a;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.2s ease;
        }

        .breadcrumb-container a:hover {
            color: #15803d;
        }

        .profile-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        /* Hero Section */
        .profile-hero {
            background: white;
            border-radius: 16px;
            padding: 3.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 3rem;
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 3rem;
            align-items: start;
            border: 1px solid #e2e8f0;
        }

        .profile-avatar-large {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: linear-gradient(135deg, #16a34a, #15803d);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 4.5rem;
            flex-shrink: 0;
            box-shadow: 0 8px 24px rgba(22, 163, 74, 0.25);
            overflow: hidden;
            border: 4px solid white;
        }

        .profile-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info-header {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .profile-name-title {
            display: flex;
            align-items: baseline;
            gap: 1rem;
        }

        .profile-name {
            font-size: 2.75rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .profile-bio {
            color: #64748b;
            font-size: 1.1rem;
            line-height: 1.7;
            max-width: 700px;
        }

        .profile-stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
            margin-top: 1.5rem;
            padding-top: 2rem;
            border-top: 2px solid #f1f5f9;
        }

        .stat-box {
            display: flex;
            gap: 1.2rem;
            align-items: center;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #16a34a;
            font-size: 1.8rem;
            flex-shrink: 0;
            border: 1px solid #bbf7d0;
        }

        .stat-content {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .stat-value {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0f172a;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #78909c;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Rating Section */
        .rating-section {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 3rem;
            border: 1px solid #e2e8f0;
        }

        .rating-header {
            display: flex;
            align-items: center;
            gap: 2.5rem;
            margin-bottom: 3rem;
            padding-bottom: 2.5rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .rating-display {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            min-width: 140px;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f0fdf4, #f5fff5);
            border-radius: 12px;
            border: 1px solid #bbf7d0;
        }

        .rating-number {
            font-size: 3.5rem;
            font-weight: 900;
            color: #16a34a;
            line-height: 1;
        }

        .rating-stars {
            display: flex;
            gap: 0.35rem;
            color: #facc15;
            font-size: 1.4rem;
        }

        .rating-count {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
        }

        .rating-info {
            flex: 1;
        }

        .rating-info p {
            color: #64748b;
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .rating-info p:first-child {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.05rem;
        }

        /* Reviews */
        .reviews-section {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 3rem;
            border: 1px solid #e2e8f0;
        }

        .reviews-title {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 2.5rem;
            color: #0f172a;
        }

        .review-item {
            padding: 2rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            transition: all 0.3s ease;
        }

        .review-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1.2rem;
        }

        .review-author {
            font-weight: 700;
            color: #0f172a;
            font-size: 1rem;
        }

        .review-date {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .review-rating {
            display: flex;
            gap: 0.2rem;
            color: #facc15;
            font-size: 1.1rem;
        }

        .review-comment {
            color: #475569;
            line-height: 1.8;
            margin-top: 1rem;
            font-size: 0.95rem;
        }

        .no-reviews {
            text-align: center;
            padding: 3rem 2rem;
            color: #94a3b8;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 12px;
            border: 1px dashed #cbd5e1;
        }

        /* Main Content Grid */
        .profile-main {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2.5rem;
        }

        /* About Section */
        .about-section {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: #16a34a;
            font-size: 1.2rem;
        }

        .about-item {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .about-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .about-label {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.05rem;
        }

        .about-label i {
            color: #16a34a;
            font-size: 1.1rem;
        }

        .about-text {
            color: #64748b;
            line-height: 1.8;
            font-size: 0.95rem;
        }

        /* Skills */
        .skills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .skill-tag {
            background: linear-gradient(135deg, #f0fdf4, #f5fff5);
            color: #16a34a;
            padding: 0.65rem 1.3rem;
            border-radius: 24px;
            font-size: 0.9rem;
            border: 1.5px solid #bbf7d0;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: default;
        }

        .skill-tag:hover {
            background: linear-gradient(135deg, #dcfce7, #f5fff5);
            border-color: #86efac;
            transform: translateY(-2px);
        }

        /* Sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .contact-card {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        .contact-item {
            display: flex;
            gap: 1.2rem;
            align-items: flex-start;
            margin-bottom: 1.8rem;
            padding-bottom: 1.8rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .contact-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .contact-icon {
            color: #16a34a;
            font-size: 1.4rem;
            width: 32px;
            text-align: center;
            flex-shrink: 0;
            margin-top: 0.3rem;
        }

        .contact-content {
            flex: 1;
        }

        .contact-label {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.8rem;
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .contact-value {
            color: #64748b;
            font-size: 0.95rem;
            word-break: break-word;
        }

        .contact-value a {
            color: #16a34a;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .contact-value a:hover {
            color: #15803d;
        }

        .btn-message {
            width: 100%;
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: white;
            border: none;
            padding: 1.2rem;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
            font-size: 1rem;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15);
        }

        .btn-message:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(22, 163, 74, 0.3);
        }

        .btn-message:active {
            transform: translateY(-1px);
        }

        /* Member Badge */
        .member-badge {
            background: linear-gradient(135deg, #f0fdf4, #f5fff5);
            border: 2px solid #bbf7d0;
            border-radius: 12px;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .member-badge i {
            color: #16a34a;
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            display: block;
        }

        .member-title {
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.5rem;
            font-size: 1.05rem;
        }

        .member-date {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 600;
        }

        /* Review Form */
        .review-form-section {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 3rem;
            border: 1px solid #e2e8f0;
        }

        .review-form-title {
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: #0f172a;
        }

        .review-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.95rem;
        }

        .star-rating {
            display: flex;
            gap: 0.5rem;
            font-size: 2rem;
        }

        .star-rating i {
            color: #cbd5e1;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .star-rating i:hover,
        .star-rating i.active {
            color: #facc15;
            transform: scale(1.1);
        }

        .review-textarea {
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.95rem;
            resize: vertical;
            min-height: 120px;
            transition: border-color 0.2s ease;
        }

        .review-textarea:focus {
            outline: none;
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
        }

        .btn-submit-review {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            align-self: flex-start;
        }

        .btn-submit-review:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(22, 163, 74, 0.3);
        }

        .btn-submit-review:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            transform: none;
        }

        .review-note {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 1rem;
            color: #15803d;
            font-size: 0.9rem;
            display: flex;
            align-items: start;
            gap: 0.75rem;
        }

        .review-note i {
            margin-top: 0.2rem;
        }

        .review-note.warning {
            background: #fef3c7;
            border-color: #fde047;
            color: #854d0e;
        }

        .review-note.info {
            background: #dbeafe;
            border-color: #93c5fd;
            color: #1e40af;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        /* Gigs */
        .gigs-section {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            grid-column: 1 / -1;
            border: 1px solid #e2e8f0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .section-header .section-title {
            margin-bottom: 0;
        }

        .view-all-link {
            color: #16a34a;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: color 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .view-all-link:hover {
            color: #15803d;
        }

        .gigs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.8rem;
        }

        .gig-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.8rem;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .gig-card:hover {
            border-color: #16a34a;
            box-shadow: 0 8px 24px rgba(22, 163, 74, 0.12);
            transform: translateY(-4px);
            background: white;
        }

        .gig-card-title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.75rem;
            line-height: 1.5;
            font-size: 1.05rem;
            flex-grow: 1;
        }

        .gig-card-category {
            display: inline-block;
            background: #dbeafe;
            color: #0369a1;
            font-size: 0.75rem;
            padding: 0.4rem 0.85rem;
            border-radius: 8px;
            font-weight: 700;
            margin-bottom: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .gig-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.2rem;
            border-top: 1px solid #e2e8f0;
            font-weight: 700;
            color: #16a34a;
            font-size: 1.1rem;
        }

        @media (max-width: 1024px) {
            .profile-hero {
                grid-template-columns: 1fr;
                padding: 2.5rem;
                gap: 2rem;
            }

            .profile-main {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .profile-avatar-large {
                width: 140px;
                height: 140px;
                font-size: 3.5rem;
                margin: 0 auto;
            }

            .profile-info-header {
                text-align: center;
            }

            .profile-name {
                font-size: 2.2rem;
            }

            .gigs-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 1.5rem;
            }

            .profile-hero {
                padding: 1.5rem;
                gap: 1.5rem;
            }

            .profile-name {
                font-size: 1.8rem;
            }

            .profile-stats-row {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }

            .stat-value {
                font-size: 1.4rem;
            }

            .gigs-grid {
                grid-template-columns: 1fr;
            }

            .rating-header {
                flex-direction: column;
                text-align: center;
            }

            .rating-section,
            .reviews-section,
            .about-section,
            .contact-card,
            .gigs-section {
                padding: 1.5rem;
            }
        }
    </style>
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