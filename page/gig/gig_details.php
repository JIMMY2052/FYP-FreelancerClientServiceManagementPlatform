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
                   f.Bio, f.Rating
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

    // Fetch review count for the freelancer
    $reviewCountSql = "SELECT COUNT(*) as review_count FROM review WHERE FreelancerID = :freelancer_id";
    $reviewStmt = $pdo->prepare($reviewCountSql);
    $reviewStmt->execute([':freelancer_id' => $gig['FreelancerID']]);
    $reviewData = $reviewStmt->fetch();
    $reviewCount = $reviewData ? intval($reviewData['review_count']) : 0;
    
    // Get average rating (from database or default to 0)
    $averageRating = !empty($gig['Rating']) ? floatval($gig['Rating']) : 0;

    // Count completed orders for this freelancer
    $orderCountSql = "SELECT COUNT(*) as order_count FROM agreement 
                      WHERE FreelancerID = :freelancer_id AND Status = 'completed'";
    $orderStmt = $pdo->prepare($orderCountSql);
    $orderStmt->execute([':freelancer_id' => $gig['FreelancerID']]);
    $orderData = $orderStmt->fetch();
    $orderCount = $orderData ? intval($orderData['order_count']) : 0;

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
            <div class="scroll-arrow scroll-left" onclick="scrollCategories('left')">
                ◀
            </div>
            <div class="category-list" id="categoryList">
                <?php foreach ($categoryData as $catSlug => $catInfo): ?>
                    <a href="browse_gigs.php?category=<?= urlencode($catSlug) ?>"
                        class="category-item <?= ($gig['Category'] === $catSlug) ? 'active' : '' ?>">
                        <?= htmlspecialchars($catInfo['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="scroll-arrow scroll-right" onclick="scrollCategories('right')">
                ▶
            </div>
        </div>
    </nav>

    <script>
    function scrollCategories(direction) {
        const container = document.getElementById('categoryList');
        const scrollAmount = 300;
        
        if (direction === 'left') {
            container.scrollLeft -= scrollAmount;
        } else {
            container.scrollLeft += scrollAmount;
        }
        
        setTimeout(updateArrowVisibility, 100);
    }

    function updateArrowVisibility() {
        const container = document.getElementById('categoryList');
        const leftArrow = document.querySelector('.scroll-left');
        const rightArrow = document.querySelector('.scroll-right');
        
        if (!container || !leftArrow || !rightArrow) return;
        
        const isAtStart = container.scrollLeft <= 0;
        const isAtEnd = container.scrollLeft + container.clientWidth >= container.scrollWidth - 1;
        
        if (isAtStart) {
            leftArrow.classList.add('hidden');
        } else {
            leftArrow.classList.remove('hidden');
        }
        
        if (isAtEnd) {
            rightArrow.classList.add('hidden');
        } else {
            rightArrow.classList.remove('hidden');
        }
    }

    // Initialize arrow visibility
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            updateArrowVisibility();
            const container = document.getElementById('categoryList');
            if (container) {
                container.addEventListener('scroll', updateArrowVisibility);
            }
            window.addEventListener('resize', updateArrowVisibility);
        });
    } else {
        updateArrowVisibility();
        const container = document.getElementById('categoryList');
        if (container) {
            container.addEventListener('scroll', updateArrowVisibility);
        }
        window.addEventListener('resize', updateArrowVisibility);
    }
    </script>

    <div class="container">
        <div class="gig-layout">
            <!-- Left Column: Gig Details -->
            <div class="gig-main-content">
                <!-- Gig Header -->
                <div class="gig-header">
                    <h1 class="gig-title"><?= htmlspecialchars($gig['Title']) ?></h1>
                    <div class="gig-meta">
                        <div class="gig-meta-item">
                            <i class="fas fa-star" style="color: <?= $averageRating > 0 ? '#ffc107' : '#ddd' ?>;"></i>
                            <span><?= number_format($averageRating, 1) ?> (<?= $reviewCount ?> review<?= $reviewCount !== 1 ? 's' : '' ?>)</span>
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
                    <button class="contact-btn" onclick="contactFreelancer(<?= $gig['FreelancerID'] ?>, <?= $gig['GigID'] ?>)">
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
                                <div class="stat-value"><?= $orderCount ?></div>
                                <div class="stat-label">Order<?= $orderCount !== 1 ? 's' : '' ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value" style="color: <?= $averageRating > 0 ? '#ffc107' : '#999' ?>;">
                                    <i class="fas fa-star" style="font-size: 0.8em; margin-right: 2px;"></i>
                                    <?= number_format($averageRating, 1) ?>
                                </div>
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
                    <div id="additionalRevisionSummary" style="display: none;" class="order-summary-item">
                        <span class="order-summary-label">Additional Revisions</span>
                        <span class="order-summary-value" id="additionalRevisionFeeDisplay">+RM0.00</span>
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

                <?php if (!empty($gig['AdditionalRevision']) && $gig['AdditionalRevision'] > 0): ?>
                    <div class="additional-revision-option" id="additionalRevisionOption">
                        <div class="additional-revision-header">
                            <div class="additional-revision-title">
                                <i class="fas fa-redo"></i>
                                Additional Revisions
                            </div>
                            <div class="additional-revision-desc">
                                <?= htmlspecialchars($gig['RevisionCount']) ?> revision(s) included. Add more for RM<?= number_format($gig['AdditionalRevision'], 2) ?> each.
                            </div>
                        </div>
                        <div class="revision-selector">
                            <button type="button" class="revision-btn" onclick="decreaseRevisions()">-</button>
                            <input type="number" id="additionalRevisionCount" value="0" min="0" max="10" readonly class="revision-count">
                            <button type="button" class="revision-btn" onclick="increaseRevisions()">+</button>
                        </div>
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
        const additionalRevisionPrice = <?= !empty($gig['AdditionalRevision']) ? $gig['AdditionalRevision'] : 0 ?>;
        const includedRevisions = <?= !empty($gig['RevisionCount']) ? $gig['RevisionCount'] : 0 ?>;

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
            const rushFeeDisplay = document.getElementById('rushDeliveryFeeDisplay');

            if (checkbox.checked) {
                option.classList.add('selected');
                rushSummary.style.display = 'flex';
                rushFeeDisplay.textContent = '+RM' + rushDeliveryPrice.toFixed(2);
            } else {
                option.classList.remove('selected');
                rushSummary.style.display = 'none';
            }
            updateTotalAmount();
        }

        // Revision selector functions
        function increaseRevisions() {
            const input = document.getElementById('additionalRevisionCount');
            const currentValue = parseInt(input.value) || 0;
            if (currentValue < 10) {
                input.value = currentValue + 1;
                updateTotalAmount();
            }
        }

        function decreaseRevisions() {
            const input = document.getElementById('additionalRevisionCount');
            const currentValue = parseInt(input.value) || 0;
            if (currentValue > 0) {
                input.value = currentValue - 1;
                updateTotalAmount();
            }
        }

        // Update total amount calculation
        function updateTotalAmount() {
            const totalAmountEl = document.getElementById('totalAmount');
            const additionalRevisionSummary = document.getElementById('additionalRevisionSummary');
            const additionalRevisionFeeDisplay = document.getElementById('additionalRevisionFeeDisplay');
            
            let total = gigPrice;
            
            // Add rush delivery if selected
            const rushCheckbox = document.getElementById('rushDeliveryCheckbox');
            if (rushCheckbox && rushCheckbox.checked) {
                total += rushDeliveryPrice;
            }
            
            // Add additional revisions
            const revisionInput = document.getElementById('additionalRevisionCount');
            if (revisionInput) {
                const revisionCount = parseInt(revisionInput.value) || 0;
                const revisionFee = revisionCount * additionalRevisionPrice;
                
                if (revisionCount > 0) {
                    additionalRevisionSummary.style.display = 'flex';
                    additionalRevisionFeeDisplay.textContent = '+RM' + revisionFee.toFixed(2) + ' (' + revisionCount + ' × RM' + additionalRevisionPrice.toFixed(2) + ')';
                    total += revisionFee;
                } else {
                    additionalRevisionSummary.style.display = 'none';
                }
            }
            
            totalAmountEl.textContent = 'RM' + total.toFixed(2);
        }

        // Confirm order
        function confirmOrder() {
            const rushDeliveryEnabled = document.getElementById('rushDeliveryCheckbox')?.checked || false;
            const additionalRevisions = parseInt(document.getElementById('additionalRevisionCount')?.value || 0);

            // Redirect to payment details page
            let url = '../payment/payment_details.php?gig_id=<?= $gig['GigID'] ?>&rush=' + (rushDeliveryEnabled ? '1' : '0');
            if (additionalRevisions > 0) {
                url += '&extra_revisions=' + additionalRevisions;
            }
            window.location.href = url;
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

        // Contact freelancer (from gig) and open chat with quote card
        function contactFreelancer(freelancerId, gigId) {
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'client'): ?>
                // Submit POST form to messages_entry.php so IDs are stored in session
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../messages_entry.php';

                const freelancerInput = document.createElement('input');
                freelancerInput.type = 'hidden';
                freelancerInput.name = 'freelancer_id';
                freelancerInput.value = freelancerId;
                form.appendChild(freelancerInput);

                if (gigId) {
                    const gigInput = document.createElement('input');
                    gigInput.type = 'hidden';
                    gigInput.name = 'gig_id';
                    gigInput.value = gigId;
                    form.appendChild(gigInput);
                }

                document.body.appendChild(form);
                form.submit();
            <?php else: ?>
                // Redirect to login
                alert('Please log in as a client to contact this freelancer.');
                window.location.href = '../login.php?redirect=' + encodeURIComponent(window.location.href);
            <?php endif; ?>
        }
    </script>
</body>

</html>