<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow all users to view gig details
$_title = 'Gig Details - WorkSnyc Freelancer Platform';
require_once '../config.php';

if (!function_exists('getPDOConnection')) {
    function getPDOConnection(): PDO
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            return new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Database connection error: ' . $e->getMessage());
        }
    }
}

// Get gig ID from URL
$gigID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$gigID) {
    header('Location: browse_gigs.php');
    exit();
}

$pdo = getPDOConnection();

// Fetch gig details with freelancer information
try {
    $sql = "SELECT g.*, 
                   f.FreelancerID, f.FirstName, f.LastName, f.ProfilePicture, 
                   f.Bio
            FROM gig g
            INNER JOIN freelancer f ON g.FreelancerID = f.FreelancerID
            WHERE g.GigID = :gigID";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':gigID' => $gigID]);
    $gig = $stmt->fetch();
    
    if (!$gig) {
        header('Location: browse_gigs.php');
        exit();
    }
    
    // Parse gallery images and video from new schema columns
    $galleryImages = [];
    $galleryVideo = null;
    
    // Add images if they exist
    if (!empty($gig['Image1Path'])) {
        $galleryImages[] = $gig['Image1Path'];
    }
    if (!empty($gig['Image2Path'])) {
        $galleryImages[] = $gig['Image2Path'];
    }
    if (!empty($gig['Image3Path'])) {
        $galleryImages[] = $gig['Image3Path'];
    }
    
    // Add video if it exists
    if (!empty($gig['VideoPath'])) {
        $galleryVideo = $gig['VideoPath'];
    }
    
} catch (PDOException $e) {
    error_log('[gig_details] Fetch failed: ' . $e->getMessage());
    die('Database error: ' . $e->getMessage());
}

// Category data for the tab navigation
$categoryData = [
    'graphic-design' => ['name' => 'Graphic & Design', 'icon' => '🎨'],
    'programming-tech' => ['name' => 'Programming & Tech', 'icon' => '💻'],
    'digital-marketing' => ['name' => 'Digital Marketing', 'icon' => '📱'],
    'writing-translation' => ['name' => 'Writing & Translation', 'icon' => '📝'],
    'video-animation' => ['name' => 'Video & Animation', 'icon' => '📹'],
    'music-audio' => ['name' => 'Music & Audio', 'icon' => '🎵'],
    'business' => ['name' => 'Business', 'icon' => '💼']
];

?>
<?php require_once '../../_head.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($gig['Title']) ?> - WorkSnyc</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/client.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fa;
        }

        /* Category Navigation */
        .category-nav {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .category-nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .category-nav-container::-webkit-scrollbar {
            height: 4px;
        }

        .category-list {
            display: flex;
            gap: 0;
        }

        .category-item {
            padding: 16px 24px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            white-space: nowrap;
            text-decoration: none;
            color: #555;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .category-item:hover {
            background: #f8f8f8;
            color: #333;
        }

        .category-item.active {
            color: rgb(159, 232, 112);
            border-bottom-color: rgb(159, 232, 112);
        }

        .category-icon {
            margin-right: 8px;
            font-size: 1.1em;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }

        .gig-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
            align-items: start;
        }

        /* Left Column - Gig Details */
        .gig-main-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .gig-header {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .gig-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 15px 0;
            line-height: 1.3;
        }

        .gig-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            color: #666;
            font-size: 0.9rem;
            flex-wrap: wrap;
        }

        .gig-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .gig-meta-item i {
            color: rgb(159, 232, 112);
        }

        /* Gallery Section */
        .gig-gallery {
            margin-bottom: 30px;
        }

        .gallery-main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .gallery-main-video {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .gallery-thumbnails {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }

        .gallery-thumbnail {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .gallery-thumbnail:hover,
        .gallery-thumbnail.active {
            border-color: rgb(159, 232, 112);
            box-shadow: 0 2px 8px rgba(159, 232, 112, 0.3);
        }

        .gallery-thumbnail-video {
            position: relative;
            width: 100%;
            height: 80px;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            overflow: hidden;
        }

        .gallery-thumbnail-video:hover,
        .gallery-thumbnail-video.active {
            border-color: rgb(159, 232, 112);
            box-shadow: 0 2px 8px rgba(159, 232, 112, 0.3);
        }

        .gallery-thumbnail-video::after {
            content: '▶';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 20px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        /* Section Styling */
        .gig-section {
            margin-bottom: 30px;
        }

        .gig-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }

        .gig-description {
            color: #555;
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .gig-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .gig-info-item {
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .gig-info-label {
            font-size: 0.75rem;
            color: #999;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .gig-info-value {
            font-size: 1rem;
            color: #2c3e50;
            font-weight: 600;
        }

        /* Right Column - Sidebar */
        .gig-sidebar {
            position: sticky;
            top: 100px;
        }

        .pricing-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }

        .price-range {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
            line-height: 1.2;
        }

        .contact-btn {
            width: 100%;
            padding: 14px 20px;
            background: rgb(159, 232, 112);
            color: #2c3e50;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .contact-btn:hover {
            background: rgb(140, 210, 90);
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
            transform: translateY(-2px);
        }

        /* Freelancer Card */
        .freelancer-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }

        .freelancer-header {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .freelancer-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .freelancer-info h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .freelancer-stats {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .stat-item {
            flex: 1;
            text-align: center;
            padding: 12px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .stat-label {
            font-size: 0.7rem;
            color: #999;
            text-transform: uppercase;
            margin-top: 3px;
            font-weight: 600;
        }

        .freelancer-bio {
            color: #555;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        @media (max-width: 1024px) {
            .gig-layout {
                grid-template-columns: 1fr;
            }

            .gig-sidebar {
                position: static;
            }

            .gallery-main-image,
            .gallery-main-video {
                height: 350px;
            }
        }

        @media (max-width: 768px) {
            .gig-main-content {
                padding: 20px;
            }

            .gig-title {
                font-size: 1.4rem;
            }

            .gallery-main-image,
            .gallery-main-video {
                height: 280px;
            }

            .gig-info-grid {
                grid-template-columns: 1fr;
            }

            .price-range {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Category Navigation -->
    <nav class="category-nav">
        <div class="category-nav-container">
            <div class="category-list">
                <?php foreach ($categoryData as $catSlug => $catInfo): ?>
                    <a href="browse_gigs.php?category=<?= urlencode($catSlug) ?>" 
                       class="category-item <?= ($gig['Category'] === $catSlug) ? 'active' : '' ?>">
                        <?= htmlspecialchars($catInfo['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="gig-layout">
            <!-- Left Column: Gig Details -->
            <div class="gig-main-content">
                <!-- Gig Header -->
                <div class="gig-header">
                    <h1 class="gig-title"><?= htmlspecialchars($gig['Title']) ?></h1>
                    <div class="gig-meta">
                        <div class="gig-meta-item">
                            <i class="fas fa-star"></i>
                            <span>0.0 (0 reviews)</span>
                        </div>
                        <div class="gig-meta-item">
                            <i class="fas fa-folder"></i>
                            <span><?= htmlspecialchars($gig['Category']) ?></span>
                        </div>
                        <div class="gig-meta-item">
                            <i class="fas fa-clock"></i>
                            <span><?= htmlspecialchars($gig['DeliveryTime']) ?> days delivery</span>
                        </div>
                    </div>
                </div>

                <!-- Gallery -->
                <?php if (!empty($galleryImages) || !empty($galleryVideo)): ?>
                <div class="gig-gallery">
                    <!-- Main Display -->
                    <div id="mainGalleryDisplay">
                        <?php if (!empty($galleryImages)): ?>
                            <img src="<?= htmlspecialchars($galleryImages[0]) ?>" 
                                 alt="Gig Image" 
                                 class="gallery-main-image"
                                 id="mainImage"
                                 onerror="this.src='/images/placeholder.jpg'">
                        <?php elseif (!empty($galleryVideo)): ?>
                            <video class="gallery-main-video" controls id="mainVideo">
                                <source src="<?= htmlspecialchars($galleryVideo) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php endif; ?>
                    </div>

                    <!-- Thumbnails -->
                    <?php if (count($galleryImages) > 1 || !empty($galleryVideo)): ?>
                    <div class="gallery-thumbnails">
                        <?php foreach ($galleryImages as $index => $image): ?>
                            <img src="<?= htmlspecialchars($image) ?>" 
                                 alt="Thumbnail <?= $index + 1 ?>" 
                                 class="gallery-thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                 onclick="showImage('<?= htmlspecialchars($image, ENT_QUOTES) ?>', this)"
                                 onerror="this.style.display='none'">
                        <?php endforeach; ?>
                        
                        <?php if (!empty($galleryVideo)): ?>
                            <div class="gallery-thumbnail-video <?= empty($galleryImages) ? 'active' : '' ?>"
                                 onclick="showVideo('<?= htmlspecialchars($galleryVideo, ENT_QUOTES) ?>', this)">
                                <video style="width:100%; height:100%; object-fit:cover;">
                                    <source src="<?= htmlspecialchars($galleryVideo) ?>" type="video/mp4">
                                </video>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <div class="gig-section">
                    <h2 class="gig-section-title">About This Gig</h2>
                    <p class="gig-description"><?= nl2br(htmlspecialchars($gig['Description'])) ?></p>
                </div>

                <!-- Gig Information -->
                <div class="gig-section">
                    <h2 class="gig-section-title">Gig Details</h2>
                    <div class="gig-info-grid">
                        <div class="gig-info-item">
                            <div class="gig-info-label">Category</div>
                            <div class="gig-info-value"><?= htmlspecialchars($gig['Category']) ?></div>
                        </div>
                        <div class="gig-info-item">
                            <div class="gig-info-label">Subcategory</div>
                            <div class="gig-info-value"><?= htmlspecialchars($gig['Subcategory']) ?></div>
                        </div>
                        <div class="gig-info-item">
                            <div class="gig-info-label">Delivery Time</div>
                            <div class="gig-info-value"><?= htmlspecialchars($gig['DeliveryTime']) ?> days</div>
                        </div>
                        <div class="gig-info-item">
                            <div class="gig-info-label">Revisions</div>
                            <div class="gig-info-value"><?= htmlspecialchars($gig['RevisionCount']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Pricing & Freelancer -->
            <div class="gig-sidebar">
                <!-- Pricing Card -->
                <div class="pricing-card">
                    <div class="price-range">
                        RM<?= number_format($gig['MinPrice'], 2) ?> - RM<?= number_format($gig['MaxPrice'], 2) ?>
                    </div>
                    <button class="contact-btn" onclick="contactFreelancer(<?= $gig['FreelancerID'] ?>)">
                        <i class="fas fa-comment-dots"></i>
                        Contact Me
                    </button>
                </div>

                <!-- Freelancer Card -->
                <div class="freelancer-card">
                    <div class="freelancer-header">
                        <?php if (!empty($gig['ProfilePicture'])): ?>
                            <img src="<?= htmlspecialchars($gig['ProfilePicture']) ?>" 
                                 alt="<?= htmlspecialchars($gig['FirstName']) ?>" 
                                 class="freelancer-avatar">
                        <?php else: ?>
                            <div class="freelancer-avatar" style="display: flex; align-items: center; justify-content: center; background: rgb(159, 232, 112); color: white; font-weight: bold; font-size: 24px;">
                                <?= strtoupper(substr($gig['FirstName'], 0, 1) . substr($gig['LastName'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="freelancer-info">
                            <h3><?= htmlspecialchars($gig['FirstName'] . ' ' . $gig['LastName']) ?></h3>
                        </div>
                    </div>

                    <div class="freelancer-stats">
                        <div class="stat-item">
                            <div class="stat-value">0</div>
                            <div class="stat-label">Orders</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">0.0</div>
                            <div class="stat-label">Rating</div>
                        </div>
                    </div>

                    <?php if (!empty($gig['Bio'])): ?>
                    <div class="freelancer-bio">
                        <?= htmlspecialchars(substr($gig['Bio'], 0, 150)) ?><?= strlen($gig['Bio']) > 150 ? '...' : '' ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../_foot.php'; ?>

    <script>
        // Gallery navigation
        function showImage(imageSrc, thumbnail) {
            const mainDisplay = document.getElementById('mainGalleryDisplay');
            mainDisplay.innerHTML = `<img src="${imageSrc}" alt="Gig Image" class="gallery-main-image" id="mainImage" onerror="this.src='/images/placeholder.jpg'">`;
            
            // Update active thumbnail
            document.querySelectorAll('.gallery-thumbnail, .gallery-thumbnail-video').forEach(t => t.classList.remove('active'));
            thumbnail.classList.add('active');
        }

        function showVideo(videoSrc, thumbnail) {
            const mainDisplay = document.getElementById('mainGalleryDisplay');
            mainDisplay.innerHTML = `
                <video class="gallery-main-video" controls id="mainVideo" autoplay>
                    <source src="${videoSrc}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            `;
            
            // Update active thumbnail
            document.querySelectorAll('.gallery-thumbnail, .gallery-thumbnail-video').forEach(t => t.classList.remove('active'));
            thumbnail.classList.add('active');
        }

        // Contact freelancer
        function contactFreelancer(freelancerId) {
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'client'): ?>
                // Redirect to messages with freelancer
                window.location.href = '../messages.php?freelancer=' + freelancerId;
            <?php else: ?>
                // Redirect to login
                alert('Please log in as a client to contact this freelancer.');
                window.location.href = '../login.php?redirect=' + encodeURIComponent(window.location.href);
            <?php endif; ?>
        }
    </script>
</body>
</html>
