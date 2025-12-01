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
    <link rel="stylesheet" href="../../assets/css/gig-details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        RM<?= number_format($gig['Price'], 2) ?>
                    </div>
                    <button class="order-btn" onclick="openOrderModal()">
                        <i class="fas fa-shopping-cart"></i>
                        Order Now
                    </button>
                    <button class="contact-btn" onclick="contactFreelancer(<?= $gig['FreelancerID'] ?>)">
                        <i class="fas fa-comment-dots"></i>
                        Contact Me
                    </button>
                </div>

                <!-- Freelancer Card -->
                <div class="freelancer-card">
                    <a href="../view_freelancer_profile.php?id=<?= $gig['FreelancerID'] ?>" class="freelancer-link">
                        <div class="freelancer-header">
                            <?php
                            $profilePicSrc = '';
                            if (!empty($gig['ProfilePicture'])) {
                                // Handle different path formats
                                $picPath = $gig['ProfilePicture'];
                                // If path doesn't start with /, add it
                                if (strpos($picPath, '/') !== 0 && strpos($picPath, 'http') !== 0) {
                                    $picPath = '/' . $picPath;
                                }
                                $profilePicSrc = $picPath;
                            }
                            ?>
                            <?php if (!empty($profilePicSrc)): ?>
                                <img src="<?= htmlspecialchars($profilePicSrc) ?>"
                                    alt="<?= htmlspecialchars($gig['FirstName']) ?>"
                                    class="freelancer-avatar"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="freelancer-avatar-initials" style="display:none;">
                                    <?= strtoupper(substr($gig['FirstName'], 0, 1)) ?>
                                </div>
                            <?php else: ?>
                                <div class="freelancer-avatar-initials">
                                    <?= strtoupper(substr($gig['FirstName'], 0, 1)) ?>
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
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Gig</h2>
                <button class="close-modal" onclick="closeOrderModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="order-summary">
                    <div class="order-summary-item">
                        <span class="order-summary-label">Gig Price</span>
                        <span class="order-summary-value">RM<?= number_format($gig['Price'], 2) ?></span>
                    </div>
                    <div class="order-summary-item">
                        <span class="order-summary-label">Delivery Time</span>
                        <span class="order-summary-value"><?= htmlspecialchars($gig['DeliveryTime']) ?> days</span>
                    </div>
                    <div id="rushDeliverySummary" style="display: none;" class="order-summary-item">
                        <span class="order-summary-label">Rush Delivery Fee</span>
                        <span class="order-summary-value" id="rushDeliveryFeeDisplay">+RM0.00</span>
                    </div>
                    <div class="order-summary-item order-total">
                        <span class="order-summary-label">Total Amount</span>
                        <span class="order-summary-value" id="totalAmount">RM<?= number_format($gig['Price'], 2) ?></span>
                    </div>
                </div>

                <?php if (!empty($gig['RushDelivery']) && $gig['RushDelivery'] > 0): ?>
                    <div class="rush-delivery-option" id="rushDeliveryOption">
                        <label class="rush-delivery-checkbox" for="rushDeliveryCheckbox">
                            <input type="checkbox" id="rushDeliveryCheckbox" onchange="toggleRushDelivery()">
                            <div class="rush-delivery-label">
                                <div class="rush-delivery-title">
                                    <i class="fas fa-bolt"></i>
                                    Rush Delivery
                                </div>
                                <div class="rush-delivery-desc">
                                    Get your order in <?= htmlspecialchars($gig['RushDelivery']) ?> days instead of <?= htmlspecialchars($gig['DeliveryTime']) ?> days
                                </div>
                            </div>
                            <div class="rush-delivery-price">
                                +RM<?= number_format($gig['RushDeliveryPrice'] ?? 0, 2) ?>
                            </div>
                        </label>
                    </div>
                <?php endif; ?>

                <div class="modal-actions">
                    <button class="modal-btn modal-btn-secondary" onclick="closeOrderModal()">Cancel</button>
                    <button class="modal-btn modal-btn-primary" onclick="confirmOrder()">Confirm Order</button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../_foot.php'; ?>

    <script>
        const gigPrice = <?= $gig['Price'] ?>;
        const rushDeliveryPrice = <?= !empty($gig['RushDeliveryPrice']) ? $gig['RushDeliveryPrice'] : 0 ?>;
        const rushDeliveryDays = <?= !empty($gig['RushDelivery']) ? $gig['RushDelivery'] : 0 ?>;

        // Modal functions
        function openOrderModal() {
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'client'): ?>
                document.getElementById('orderModal').style.display = 'block';
                document.body.style.overflow = 'hidden';
            <?php else: ?>
                alert('Please log in as a client to order this gig.');
                window.location.href = '../login.php?redirect=' + encodeURIComponent(window.location.href);
            <?php endif; ?>
        }

        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                closeOrderModal();
            }
        }

        // Toggle rush delivery
        function toggleRushDelivery() {
            const checkbox = document.getElementById('rushDeliveryCheckbox');
            const option = document.getElementById('rushDeliveryOption');
            const rushSummary = document.getElementById('rushDeliverySummary');
            const totalAmountEl = document.getElementById('totalAmount');
            const rushFeeDisplay = document.getElementById('rushDeliveryFeeDisplay');

            if (checkbox.checked) {
                option.classList.add('selected');
                rushSummary.style.display = 'flex';
                rushFeeDisplay.textContent = '+RM' + rushDeliveryPrice.toFixed(2);
                const total = gigPrice + rushDeliveryPrice;
                totalAmountEl.textContent = 'RM' + total.toFixed(2);
            } else {
                option.classList.remove('selected');
                rushSummary.style.display = 'none';
                totalAmountEl.textContent = 'RM' + gigPrice.toFixed(2);
            }
        }

        // Confirm order
        function confirmOrder() {
            const rushDeliveryEnabled = document.getElementById('rushDeliveryCheckbox')?.checked || false;

            // Redirect to payment details page
            window.location.href = '../payment/payment_details.php?gig_id=<?= $gig['GigID'] ?>&rush=' + (rushDeliveryEnabled ? '1' : '0');
        }

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