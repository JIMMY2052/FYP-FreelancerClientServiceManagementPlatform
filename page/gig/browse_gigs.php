<?php
session_start();

// Allow both clients and freelancers to browse gigs
// No login required, but if logged in, check user type
if (isset($_SESSION['user_id']) && !in_array($_SESSION['user_type'], ['client', 'freelancer'])) {
    header('Location: /index.php');
    exit();
}

$_title = 'Browse Gigs - WorkSnyc Freelancer Platform';
include '../../_head.php';
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

// Category and Subcategory mapping (same as in create_gig.php)
$categoryData = [
    'graphic-design' => [
        'name' => 'Graphic & Design',
        'icon' => '🎨',
        'subcategories' => [
            'logo-design' => 'Logo Design',
            'brand-style-guide' => 'Brand Style Guide',
            'game-art' => 'Game Art',
            'graphics-streamers' => 'Graphics for Streamers',
            'business-cards' => 'Business Cards & Stationary',
            'website-design' => 'Website Design',
            'app-design' => 'App Design',
            'ux-design' => 'UX Design',
            'landing-page-design' => 'Landing Page Design'
        ]
    ],
    'programming-tech' => [
        'name' => 'Programming & Tech',
        'icon' => '💻',
        'subcategories' => [
            'website-development' => 'Website Development',
            'website-maintenance' => 'Website Maintenance',
            'software-development' => 'Software Development',
            'game-development' => 'Game Development',
            'ai-development' => 'AI Development',
            'chatbot-development' => 'Chatbot Development',
            'cloud-computing' => 'Cloud Computing',
            'mobile-app-dev' => 'Mobile App Development'
        ]
    ],
    'digital-marketing' => [
        'name' => 'Digital Marketing',
        'icon' => '📱',
        'subcategories' => [
            'seo' => 'SEO',
            'social-media' => 'Social Media Marketing',
            'ppc' => 'PPC Advertising',
            'email-marketing' => 'Email Marketing',
            'content-marketing' => 'Content Marketing'
        ]
    ],
    'writing-translation' => [
        'name' => 'Writing & Translation',
        'icon' => '📝',
        'subcategories' => [
            'article-writing' => 'Article Writing',
            'copywriting' => 'Copywriting',
            'technical-writing' => 'Technical Writing',
            'translation' => 'Translation',
            'proofreading' => 'Proofreading'
        ]
    ],
    'video-animation' => [
        'name' => 'Video & Animation',
        'icon' => '📹',
        'subcategories' => [
            'video-editing' => 'Video Editing',
            'animation' => 'Animation',
            'motion-graphics' => 'Motion Graphics',
            'vfx' => 'VFX',
            'video-production' => 'Video Production'
        ]
    ],
    'music-audio' => [
        'name' => 'Music & Audio',
        'icon' => '🎵',
        'subcategories' => [
            'music-production' => 'Music Production',
            'voice-over' => 'Voice Over',
            'audio-editing' => 'Audio Editing',
            'sound-design' => 'Sound Design'
        ]
    ]
];

// Map category query parameter to database category value
$categoryMap = [
    'design' => 'graphic-design',
    'tech' => 'programming-tech',
    'marketing' => 'digital-marketing',
    'writing' => 'writing-translation',
    'video' => 'video-animation',
    'audio' => 'music-audio'
];

// Get search and filter parameters
$searchQuery = trim($_GET['q'] ?? '');
$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? floatval($_GET['min_price']) : null;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? floatval($_GET['max_price']) : null;
$sortBy = $_GET['sort'] ?? 'newest';

// Get selected category from query parameter
$selectedCategory = $_GET['category'] ?? '';
$categoryFilter = '';

if ($selectedCategory && isset($categoryMap[$selectedCategory])) {
    $categoryFilter = $categoryMap[$selectedCategory];
}

// Get selected subcategory
$selectedSubcategory = $_GET['subcategory'] ?? '';

// Fetch gigs from database
$pdo = getPDOConnection();
$gigs = [];

try {
    $sql = "SELECT g.GigID, g.Title, g.Description, g.Category, g.Subcategory, 
                   g.Price, g.DeliveryTime, g.RushDelivery, g.RushDeliveryPrice, g.Image1Path, g.Status, g.CreatedAt,
                   f.FirstName, f.LastName, f.ProfilePicture
            FROM gig g
            INNER JOIN freelancer f ON g.FreelancerID = f.FreelancerID
            WHERE g.Status = 'active'";
    
    $params = [];
    
    // Search by title
    if ($searchQuery !== '') {
        $sql .= " AND g.Title LIKE :search";
        $params[':search'] = '%' . $searchQuery . '%';
    }
    
    // Filter by category
    if ($categoryFilter) {
        $sql .= " AND g.Category = :category";
        $params[':category'] = $categoryFilter;
    }
    
    // Filter by subcategory
    if ($selectedSubcategory) {
        $sql .= " AND g.Subcategory = :subcategory";
        $params[':subcategory'] = $selectedSubcategory;
    }
    
    // Filter by price range
    if ($minPrice !== null) {
        $sql .= " AND g.Price >= :minPrice";
        $params[':minPrice'] = $minPrice;
    }
    if ($maxPrice !== null) {
        $sql .= " AND g.Price <= :maxPrice";
        $params[':maxPrice'] = $maxPrice;
    }
    
    // Sort
    if ($sortBy === 'price_low') {
        $sql .= " ORDER BY g.Price ASC, g.CreatedAt DESC";
    } elseif ($sortBy === 'price_high') {
        $sql .= " ORDER BY g.Price DESC, g.CreatedAt DESC";
    } elseif ($sortBy === 'delivery') {
        $sql .= " ORDER BY g.DeliveryTime ASC, g.CreatedAt DESC";
    } else { // newest (default)
        $sql .= " ORDER BY g.CreatedAt DESC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $gigs = $stmt->fetchAll();
    
    // Process each gig to set default values
    foreach ($gigs as &$gig) {
        // Set default rating values
        $gig['Rating'] = 0;
        $gig['RatingCount'] = 0;
        
        // Use Image1Path directly as thumbnail
        $gig['ThumbnailUrl'] = $gig['Image1Path'] ?? null;
    }
    unset($gig);
} catch (PDOException $e) {
    error_log('[browse_gigs] Fetch failed: ' . $e->getMessage());
    $gigs = [];
}

?>

<style>
    /* Category Tab Styles */
    .category-tab-container {
        background: #fff;
        border-bottom: 2px solid #e0e0e0;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .category-tab-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .category-tabs {
        display: flex;
        gap: 0;
        overflow-x: auto;
        scrollbar-width: thin;
        -webkit-overflow-scrolling: touch;
    }

    .category-tabs::-webkit-scrollbar {
        height: 4px;
    }

    .category-tabs::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 2px;
    }

    .category-tab-item {
        position: relative;
        padding: 16px 24px;
        cursor: pointer;
        white-space: nowrap;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
        font-weight: 500;
        color: #555;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .category-tab-item:hover {
        background: #f8f8f8;
        color: #333;
    }

    .category-tab-item.active {
        color: rgb(159, 232, 112);
        border-bottom-color: rgb(159, 232, 112);
    }

    .category-tab-item .category-icon {
        font-size: 1.2em;
    }

    /* Subcategory Dropdown */
    .subcategory-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 250px;
        padding: 8px 0;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
        margin-top: 3px;
    }

    .category-tab-item:hover .subcategory-dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .subcategory-item {
        padding: 12px 20px;
        color: #555;
        text-decoration: none;
        display: block;
        transition: background 0.2s ease;
    }

    .subcategory-item:hover {
        background: #f0f7ff;
        color: rgb(159, 232, 112);
    }

    .subcategory-item.active {
        background: #e3f2fd;
        color: rgb(159, 232, 112);
        font-weight: 600;
    }

    /* Browse Gigs Page Styles */
    .browse-gigs-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .page-header {
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 10px;
    }

    .page-header p {
        color: #666;
        font-size: 1.1rem;
    }

    .gigs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
        margin-top: 30px;
    }

    .gig-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        text-decoration: none;
    }

    .gig-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        transform: translateY(-4px);
    }

    .gig-thumbnail {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: #f5f5f5;
    }

    .gig-card-body {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .gig-freelancer-info {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .gig-freelancer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        background: #e0e0e0;
    }

    .gig-freelancer-name {
        font-weight: 600;
        color: #333;
        font-size: 0.95rem;
    }

    .gig-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .gig-description {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 15px;
        flex-grow: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .gig-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
    }

    .gig-rating {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #666;
        font-size: 0.9rem;
    }

    .gig-rating .stars {
        color: #ffc107;
    }

    .gig-delivery {
        color: #666;
        font-size: 0.9rem;
    }

    .gig-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
    }

    .gig-price {
        font-size: 1.2rem;
        font-weight: 700;
        color: rgb(159, 232, 112);
    }

    .gig-view-btn {
        padding: 8px 16px;
        background: rgb(159, 232, 112);
        color: #fff;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: background 0.2s ease;
    }

    .gig-view-btn:hover {
        background: rgb(159, 232, 112);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 20px;
    }

    .empty-state h2 {
        font-size: 1.5rem;
        margin-bottom: 10px;
        color: #333;
    }

    .empty-state p {
        font-size: 1rem;
        margin-bottom: 20px;
    }

    .filter-badge {
        display: inline-block;
        padding: 6px 12px;
        background: #e3f2fd;
        color: rgb(159, 232, 112);
        border-radius: 20px;
        font-size: 0.85rem;
        margin-right: 8px;
        margin-bottom: 8px;
    }

    .filter-badge .remove {
        margin-left: 8px;
        cursor: pointer;
        font-weight: bold;
    }

    .filter-badge .remove:hover {
        color: rgb(159, 232, 112);
    }

    /* Search Filters (similar to browse_job.php) */
    .search-filters {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 25px;
        padding: 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        box-sizing: border-box;
    }

    .filter-input {
        flex: 1;
        min-width: 200px;
        padding: 11px 16px;
        border-radius: 12px;
        border: 1px solid #ddd;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .filter-input:focus {
        outline: none;
        border-color: rgb(159, 232, 112);
        box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
    }

    .filter-select {
        padding: 11px 16px;
        border-radius: 12px;
        border: 1px solid #ddd;
        font-size: 0.9rem;
        background: white;
        transition: all 0.3s ease;
        box-sizing: border-box;
        min-width: 180px;
    }

    .filter-select:focus {
        outline: none;
        border-color: rgb(159, 232, 112);
        box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
    }

    .filter-search {
        padding: 11px 24px;
        border-radius: 16px;
        background: rgb(159, 232, 112);
        color: #333;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        box-sizing: border-box;
    }

    .filter-search:hover {
        background: rgb(140, 210, 90);
        box-shadow: 0 4px 8px rgba(159, 232, 112, 0.3);
    }

    .filter-reset {
        padding: 11px 24px;
        border-radius: 16px;
        background: #eee;
        color: #333;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
        font-size: 0.9rem;
        box-sizing: border-box;
    }

    .filter-reset:hover {
        background: #ddd;
    }

    .results-count {
        color: #666;
        margin-bottom: 20px;
        font-weight: 500;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .category-tabs {
            padding: 0 10px;
        }

        .category-tab-item {
            padding: 12px 16px;
            font-size: 0.9rem;
        }

        .search-filters {
            flex-direction: column;
            padding: 15px;
        }

        .filter-input {
            min-width: 100%;
        }

        .filter-select {
            width: 100%;
        }

        .filter-search,
        .filter-reset {
            width: 100%;
            text-align: center;
        }

        .gigs-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .browse-gigs-container {
            padding: 20px 15px;
        }

        .page-header h1 {
            font-size: 2rem;
        }
    }

    @media (max-width: 480px) {
        .browse-gigs-container {
            padding: 15px 10px;
        }

        .search-filters {
            padding: 12px;
        }

        .filter-input,
        .filter-select {
            padding: 10px 12px;
            font-size: 0.85rem;
        }
    }
</style>

<!-- Category Tab Section -->
<div class="category-tab-container">
    <div class="category-tab-wrapper">
        <div class="category-tabs">
            <a href="/page/gig/browse_gigs.php" class="category-tab-item <?php echo empty($selectedCategory) ? 'active' : ''; ?>">
                <span>All Categories</span>
            </a>
            <?php foreach ($categoryData as $key => $category): ?>
                <?php 
                $categoryParam = array_search($key, $categoryMap);
                if (!$categoryParam) {
                    // Handle direct category keys
                    if ($key === 'graphic-design') $categoryParam = 'design';
                    elseif ($key === 'programming-tech') $categoryParam = 'tech';
                    elseif ($key === 'digital-marketing') $categoryParam = 'marketing';
                    elseif ($key === 'writing-translation') $categoryParam = 'writing';
                    elseif ($key === 'video-animation') $categoryParam = 'video';
                    elseif ($key === 'music-audio') $categoryParam = 'audio';
                }
                $isActive = ($selectedCategory === $categoryParam);
                ?>
                <a href="/page/gig/browse_gigs.php?category=<?php echo urlencode($categoryParam); ?>" class="category-tab-item <?php echo $isActive ? 'active' : ''; ?>">
                    <!--<span class="category-icon"><?php echo htmlspecialchars($category['icon']); ?></span>-->
                    <span><?php echo htmlspecialchars($category['name']); ?></span>
                    <div class="subcategory-dropdown">
                        <?php foreach ($category['subcategories'] as $subKey => $subName): ?>
                            <a href="/page/gig/browse_gigs.php?category=<?php echo urlencode($categoryParam); ?>&subcategory=<?php echo urlencode($subKey); ?>" 
                               class="subcategory-item <?php echo ($selectedSubcategory === $subKey && $isActive) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($subName); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Browse Gigs Content -->
<div class="browse-gigs-container">
    <div class="page-header">
        <h1>Browse Gigs</h1>
        <p>Discover talented freelancers and their gigs</p>
    </div>

    <!-- Search and Filter Form -->
    <form method="get" class="search-filters">
        <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search gigs by title..." class="filter-input">
        <input type="number" name="min_price" value="<?php echo ($minPrice !== null) ? htmlspecialchars($minPrice) : ''; ?>" placeholder="Min price (MYR)" class="filter-input" step="1">
        <input type="number" name="max_price" value="<?php echo ($maxPrice !== null) ? htmlspecialchars($maxPrice) : ''; ?>" placeholder="Max price (MYR)" class="filter-input" step="1">
        <select name="sort" class="filter-select">
            <option value="newest" <?php if ($sortBy === 'newest') echo 'selected'; ?>>Newest</option>
            <option value="price_low" <?php if ($sortBy === 'price_low') echo 'selected'; ?>>Price: Low to High</option>
            <option value="price_high" <?php if ($sortBy === 'price_high') echo 'selected'; ?>>Price: High to Low</option>
            <option value="delivery" <?php if ($sortBy === 'delivery') echo 'selected'; ?>>Fastest Delivery</option>
        </select>
        <?php if ($categoryFilter): ?>
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
        <?php endif; ?>
        <?php if ($selectedSubcategory): ?>
            <input type="hidden" name="subcategory" value="<?php echo htmlspecialchars($selectedSubcategory); ?>">
        <?php endif; ?>
        <button type="submit" class="filter-search">Search</button>
        <a href="/page/gig/browse_gigs.php" class="filter-reset">Reset</a>
    </form>

    <p class="results-count"><?php echo count($gigs); ?> gig(s) found</p>

    <div style="margin-bottom: 20px;">
        <?php if ($categoryFilter || $selectedSubcategory): ?>
            <strong>Active Filters:</strong>
            <?php if ($categoryFilter): ?>
                <span class="filter-badge">
                    Category: <?php echo htmlspecialchars($categoryData[$categoryFilter]['name'] ?? $categoryFilter); ?>
                    <a href="/page/gig/browse_gigs.php<?php echo $selectedSubcategory ? '?subcategory=' . urlencode($selectedSubcategory) : ''; ?>" class="remove">×</a>
                </span>
            <?php endif; ?>
            <?php if ($selectedSubcategory): ?>
                <span class="filter-badge">
                    Subcategory: <?php 
                        $subName = '';
                        foreach ($categoryData as $cat) {
                            if (isset($cat['subcategories'][$selectedSubcategory])) {
                                $subName = $cat['subcategories'][$selectedSubcategory];
                                break;
                            }
                        }
                        echo htmlspecialchars($subName ?: $selectedSubcategory);
                    ?>
                    <a href="/page/gig/browse_gigs.php<?php echo $categoryFilter ? '?category=' . urlencode($selectedCategory) : ''; ?>" class="remove">×</a>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    </div>

    <?php if (empty($gigs)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <h2>No gigs found</h2>
            <p><?php echo ($categoryFilter || $selectedSubcategory) ? 'Try adjusting your filters or browse all categories.' : 'There are no active gigs available at the moment.'; ?></p>
            <?php if ($categoryFilter || $selectedSubcategory): ?>
                <a href="/page/gig/browse_gigs.php" class="gig-view-btn">View All Gigs</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="gigs-grid">
            <?php foreach ($gigs as $gig): ?>
                <div class="gig-card" onclick="window.location.href='/page/gig/gig_details.php?id=<?php echo intval($gig['GigID']); ?>';" style="cursor: pointer;">
                    <?php if (!empty($gig['ThumbnailUrl'])): ?>
                        <img src="<?php echo htmlspecialchars($gig['ThumbnailUrl']); ?>" alt="<?php echo htmlspecialchars($gig['Title']); ?>" class="gig-thumbnail" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="gig-thumbnail" style="display: none; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 3rem;">
                            <?php 
                            $categoryIcon = '💼';
                            if (isset($categoryData[$gig['Category']])) {
                                $categoryIcon = $categoryData[$gig['Category']]['icon'];
                            }
                            echo $categoryIcon;
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="gig-thumbnail" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 3rem;">
                            <?php 
                            $categoryIcon = '💼';
                            // Get icon from categoryData using the gig's category key
                            if (isset($categoryData[$gig['Category']])) {
                                $categoryIcon = $categoryData[$gig['Category']]['icon'];
                            }
                            echo $categoryIcon;
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="gig-card-body">
                        <div class="gig-freelancer-info">
                            <?php if ($gig['ProfilePicture']): ?>
                                <img src="<?php echo htmlspecialchars($gig['ProfilePicture']); ?>" alt="Freelancer" class="gig-freelancer-avatar">
                            <?php else: ?>
                                <div class="gig-freelancer-avatar" style="display: flex; align-items: center; justify-content: center; background: rgb(159, 232, 112); color: white; font-weight: bold;">
                                    <?php echo strtoupper(substr($gig['FirstName'], 0, 1) . substr($gig['LastName'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <span class="gig-freelancer-name"><?php echo htmlspecialchars($gig['FirstName'] . ' ' . $gig['LastName']); ?></span>
                        </div>
                        
                        <h3 class="gig-title"><?php echo htmlspecialchars($gig['Title']); ?></h3>
                        <p class="gig-description"><?php echo htmlspecialchars(mb_strimwidth($gig['Description'], 0, 150, '...')); ?></p>
                        
                        <div class="gig-meta">
                            <?php if ($gig['Rating'] > 0): ?>
                                <div class="gig-rating">
                                    <span class="stars"><?php echo str_repeat('⭐', min(5, round($gig['Rating']))); ?></span>
                                    <span><?php echo number_format($gig['Rating'], 1); ?> (<?php echo $gig['RatingCount']; ?>)</span>
                                </div>
                            <?php else: ?>
                                <div class="gig-rating">
                                    <span>New</span>
                                </div>
                            <?php endif; ?>
                            <div class="gig-delivery">
                                ⏱️ <?php echo intval($gig['DeliveryTime']); ?> day(s)
                            </div>
                        </div>
                        
                        <div class="gig-footer">
                            <div class="gig-price">
                                MYR <?php echo number_format($gig['Price'], 0); ?>
                            </div>
                            <a href="/page/gig/gig_details.php?id=<?php echo intval($gig['GigID']); ?>" class="gig-view-btn">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../../_foot.php'; ?>

