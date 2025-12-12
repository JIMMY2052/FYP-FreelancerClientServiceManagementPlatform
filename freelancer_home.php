<?php

session_start();

// Check if user is logged in and is a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: /index.php');
    exit();
}

// Check if user is deleted
require_once './page/checkUserStatus.php';

$_title = 'Dashboard - WorkSnyc Freelancer Platform';

// Fetch latest available jobs
require_once './page/config.php';

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

$pdo = getPDOConnection();

// Fetch latest 3 available jobs
try {
    $stmt = $pdo->prepare("
        SELECT j.JobID, j.Title, j.Description, j.Budget, j.PostDate, c.CompanyName 
        FROM job j
        INNER JOIN client c ON j.ClientID = c.ClientID
        WHERE j.Status = 'available'
        ORDER BY j.PostDate DESC
        LIMIT 3
    ");
    $stmt->execute();
    $latestJobs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('[freelancer_home] Failed to fetch jobs: ' . $e->getMessage());
    $latestJobs = [];
}

include '_head.php';

?>

<link rel="stylesheet" href="/css/freelancer_home.css">

<!-- Freelancer Dashboard Hero Section -->
<section class="freelancer-hero-section">
    <div class="container">
        <div class="hero-content-wrapper">
            <div class="hero-text">
                <h1 class="hero-title">Work Your Way</h1>
                <p class="hero-subtitle">Find your next opportunity and grow your freelance career</p>
                <div class="hero-buttons">
                    <a href="/page/job/browse_job.php" class="btn-hero btn-hero-primary">Browse Projects</a>
                    <a href="/page/gig/my_gig.php" class="btn-hero btn-hero-secondary">My Gigs</a>
                </div>
            </div>
            <div class="hero-image-wrapper">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&auto=format&fit=crop&q=80" alt="Freelancer working" class="hero-main-image">
            </div>
        </div>
    </div>
</section>

<!-- Freelancer Specializations Section -->
<section class="specializations-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Find Clients In Your Specialty</h2>
            <p class="section-subtitle">Browse projects from various industries</p>
        </div>
        <div class="categories-grid">
            <div class="category-card">
                <div class="category-icon">🎨</div>
                <h3 class="category-title">Graphic & Design</h3>
                <p class="category-description">Logo design, branding, UI/UX projects</p>
                <a href="/page/job/browse_job.php" class="category-link">Browse Projects →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">💻</div>
                <h3 class="category-title">Programming & Tech</h3>
                <p class="category-description">Web development, mobile apps, software</p>
                <a href="/page/job/browse_job.php" class="category-link">Browse Projects →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📝</div>
                <h3 class="category-title">Writing & Translation</h3>
                <p class="category-description">Content writing, copywriting, translation</p>
                <a href="/page/job/browse_job.php" class="category-link">Browse Projects →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📱</div>
                <h3 class="category-title">Digital Marketing</h3>
                <p class="category-description">SEO, social media, email marketing</p>
                <a href="/page/job/browse_job.php" class="category-link">Browse Projects →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📹</div>
                <h3 class="category-title">Video & Animation</h3>
                <p class="category-description">Video editing, animation, VFX projects</p>
                <a href="/page/job/browse_job.php" class="category-link">Browse Projects →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">🎵</div>
                <h3 class="category-title">Music & Audio</h3>
                <p class="category-description">Music production, voice-over, audio editing</p>
                <a href="/page/job/browse_job.php" class="category-link">Browse Projects →</a>
            </div>
        </div>
    </div>
</section>

<!-- Available Opportunities Section -->
<section class="opportunities-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Available Opportunities</h2>
            <p class="section-subtitle">New projects matching your skills</p>
        </div>
        <?php if (!empty($latestJobs)): ?>
            <div class="opportunities-grid">
                <?php foreach ($latestJobs as $job): ?>
                    <div class="opportunity-card">
                        <div class="opportunity-header">
                            <h3 class="opportunity-title"><?= htmlspecialchars($job['Title']) ?></h3>
                            <span class="opportunity-budget">RM <?= number_format($job['Budget'], 0) ?></span>
                        </div>
                        <p class="opportunity-description"><?= htmlspecialchars(mb_strimwidth($job['Description'], 0, 150, '...')) ?></p>
                        <div class="opportunity-skills">
                            <span class="skill-tag"><?= htmlspecialchars($job['CompanyName']) ?></span>
                        </div>
                        <div class="opportunity-footer">
                            <span class="opportunity-time">
                                <?php
                                $postDate = new DateTime($job['PostDate']);
                                $now = new DateTime();
                                $interval = $now->diff($postDate);
                                
                                if ($interval->days == 0) {
                                    if ($interval->h == 0) {
                                        echo $interval->i . ' minute(s) ago';
                                    } else {
                                        echo $interval->h . ' hour(s) ago';
                                    }
                                } elseif ($interval->days == 1) {
                                    echo '1 day ago';
                                } else {
                                    echo $interval->days . ' days ago';
                                }
                                ?>
                            </span>
                            <a href="/page/job/job_details.php?id=<?= $job['JobID'] ?>" class="btn-small">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-opportunities">
                <p>No available opportunities at the moment. Check back later!</p>
            </div>
        <?php endif; ?>
        <div class="view-all-section">
            <a href="/page/job/browse_job.php" class="btn-primary">View All Opportunities</a>
        </div>
    </div>
</section>

<!-- Freelancer Tools Section -->
<section class="freelancer-tools-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Freelancer Tools</h2>
            <p class="section-subtitle">Manage your career effectively</p>
        </div>
        <div class="tools-grid">
            <div class="tool-item">
                <div class="tool-icon">📋</div>
                <h3 class="tool-title">Portfolio</h3>
                <p class="tool-description">Showcase your best work and attract more clients with a professional portfolio.</p>
            </div>
            <div class="tool-item">
                <div class="tool-icon">📄</div>
                <h3 class="tool-title">Proposals</h3>
                <p class="tool-description">Create and track all your project proposals in one place.</p>
            </div>
            <div class="tool-item">
                <div class="tool-icon">🏆</div>
                <h3 class="tool-title">Certifications</h3>
                <p class="tool-description">Add certifications and badges to build trust with clients.</p>
            </div>
            <div class="tool-item">
                <div class="tool-icon">📈</div>
                <h3 class="tool-title">Performance Analytics</h3>
                <p class="tool-description">Track your profile views, project success rate, and earnings.</p>
            </div>
        </div>
    </div>
</section>

<!-- Recent Activity Section -->
<section class="recent-activity-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Recent Activity</h2>
        </div>
        <div class="activity-list">
            <div class="activity-item">
                <div class="activity-icon">💬</div>
                <div class="activity-content">
                    <p class="activity-text"><strong>John Smith</strong> sent you a message about "Website Redesign Project"</p>
                    <span class="activity-time">2 hours ago</span>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon">⭐</div>
                <div class="activity-content">
                    <p class="activity-text"><strong>Sarah Johnson</strong> left you a 5-star review</p>
                    <span class="activity-time">1 day ago</span>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon">✅</div>
                <div class="activity-content">
                    <p class="activity-text">Your project "Logo Design" has been marked as complete</p>
                    <span class="activity-time">3 days ago</span>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon">💳</div>
                <div class="activity-content">
                    <p class="activity-text">Payment of $450 received for "Social Media Graphics"</p>
                    <span class="activity-time">5 days ago</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php

include '_foot.php';

?>