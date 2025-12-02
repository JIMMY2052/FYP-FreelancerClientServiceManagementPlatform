<?php

session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: /index.php');
    exit();
}

$_title = 'Dashboard - WorkSnyc Freelancer Platform';
include '_head.php';
require_once 'page/config.php';

$clientID = $_SESSION['user_id'];
$conn = getDBConnection();

// Fetch latest 3 projects for this client
$sql = "SELECT JobID, Title, Description, Budget, Deadline, Status, PostDate 
        FROM job 
        WHERE ClientID = ? AND Status != 'deleted'
        ORDER BY PostDate DESC 
        LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $clientID);
$stmt->execute();
$result = $stmt->get_result();
$projects = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

?>

<!-- Client Dashboard Hero Section -->
<section class="client-hero-section">
    <div class="container">
        <div class="hero-content-wrapper">
            <div class="hero-text">
                <h1 class="hero-title">Post. Match. Complete.</h1>
                <p class="hero-subtitle">Find talented freelancers and manage your projects efficiently</p>
                <div class="hero-buttons">
                    <a href="/page/gig/browse_gigs.php" class="btn-hero btn-hero-primary">Browse Gigs</a>
                    <a href="/page/job/my_jobs.php" class="btn-hero btn-hero-secondary">My Projects</a>
                </div>
            </div>
            <div class="hero-image-wrapper">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&auto=format&fit=crop&q=80" alt="Client managing projects" class="hero-main-image">
            </div>
        </div>
    </div>
</section>

<!-- Browse Freelancer Categories Section -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Find Talent by Expertise</h2>
            <p class="section-subtitle">Browse skilled freelancers across all industries</p>
        </div>
        <div class="categories-grid">
            <div class="category-card">
                <div class="category-icon">🎨</div>
                <h3 class="category-title">Graphic & Design</h3>
                <p class="category-description">Logo design, branding, UI/UX, illustrations</p>
                <a href="/page/gig/browse_gigs.php?category=design" class="category-link">Browse Freelancers →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">💻</div>
                <h3 class="category-title">Programming & Tech</h3>
                <p class="category-description">Web development, mobile apps, software engineering</p>
                <a href="/page/gig/browse_gigs.php?category=tech" class="category-link">Browse Gigs →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📝</div>
                <h3 class="category-title">Writing & Translation</h3>
                <p class="category-description">Content writing, copywriting, translation services</p>
                <a href="/page/gig/browse_gigs.php?category=writing" class="category-link">Browse Gigs →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📱</div>
                <h3 class="category-title">Digital Marketing</h3>
                <p class="category-description">SEO, social media, email marketing, PPC campaigns</p>
                <a href="/page/gig/browse_gigs.php?category=marketing" class="category-link">Browse Gigs →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📹</div>
                <h3 class="category-title">Video & Animation</h3>
                <p class="category-description">Video editing, animation, motion graphics, VFX</p>
                <a href="/page/gig/browse_gigs.php?category=video" class="category-link">Browse Gigs →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">🎵</div>
                <h3 class="category-title">Music & Audio</h3>
                <p class="category-description">Music production, voice-over, audio editing</p>
                <a href="/page/gig/browse_gigs.php?category=audio" class="category-link">Browse Gigs →</a>
            </div>
        </div>
    </div>
</section>

<!-- Active Projects Section -->
<section class="active-projects-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Your Active Projects</h2>
            <p class="section-subtitle">Manage and track your projects</p>
        </div>
        <?php if (empty($projects)): ?>
            <div class="empty-projects-state">
                <div class="empty-icon">📋</div>
                <h3>No Projects Yet</h3>
                <p>Start by posting your first project to find talented freelancers</p>
                <a href="/page/job/createJob.php" class="btn-create-project">Post Your First Project</a>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <?php
                    // Calculate days until deadline
                    $deadline = new DateTime($project['Deadline']);
                    $today = new DateTime();
                    $daysLeft = $today->diff($deadline)->days;
                    $isPast = $deadline < $today;
                    
                    // Determine status display
                    $statusClass = 'status-' . strtolower($project['Status']);
                    $statusText = ucfirst($project['Status']);
                    
                    // Mock progress (in real app, calculate from milestones/tasks)
                    $progress = ($project['Status'] === 'available') ? 0 : (($project['Status'] === 'processing') ? 50 : 100);
                    ?>
                    <div class="project-card">
                        <div class="project-status-badge <?= $statusClass ?>">
                            <?= $statusText ?>
                        </div>
                        <div class="project-header">
                            <h3 class="project-title"><?= htmlspecialchars($project['Title']) ?></h3>
                        </div>
                        <p class="project-description"><?= htmlspecialchars(mb_strimwidth($project['Description'], 0, 100, '...')) ?></p>
                        <div class="project-progress-bar">
                            <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                        </div>
                        <div class="project-stats">
                            <div class="stat-item">
                                <span class="stat-label">Budget</span>
                                <span class="stat-value">RM <?= number_format($project['Budget'], 0) ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Progress</span>
                                <span class="stat-value"><?= $progress ?>%</span>
                            </div>
                        </div>
                        <div class="project-footer">
                            <div class="project-due">
                                <i class="fas fa-calendar"></i>
                                <span><?= $isPast ? 'Past due' : 'Due: ' . date('M d, Y', strtotime($project['Deadline'])) ?></span>
                            </div>
                            <a href="/page/job/client_job_details.php?id=<?= $project['JobID'] ?>" class="btn-view-project">View Project →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="view-all-section">
                <a href="/page/job/my_jobs.php" class="btn-view-all">View All Projects</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Client Tools Section -->
<section class="client-tools-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Client Tools</h2>
            <p class="section-subtitle">Simplify project management</p>
        </div>
        <div class="tools-grid">
            <div class="tool-item">
                <div class="tool-icon">📝</div>
                <h3 class="tool-title">Post a Project</h3>
                <p class="tool-description">Create a new project and connect with talented freelancers.</p>
                <a href="/page/post_project.php" class="tool-link">Post Project →</a>
            </div>
            <div class="tool-item">
                <div class="tool-icon">👥</div>
                <h3 class="tool-title">Manage Freelancers</h3>
                <p class="tool-description">Keep track of all your hired freelancers and contracts.</p>
                <a href="/page/manage_freelancers.php" class="tool-link">View Freelancers →</a>
            </div>
            <div class="tool-item">
                <div class="tool-icon">💳</div>
                <h3 class="tool-title">Payment Management</h3>
                <p class="tool-description">Manage invoices, payments, and billing history.</p>
                <a href="/page/payments.php" class="tool-link">View Payments →</a>
            </div>
            <div class="tool-item">
                <div class="tool-icon">📊</div>
                <h3 class="tool-title">Reports & Analytics</h3>
                <p class="tool-description">Get insights on project performance and spending.</p>
                <a href="/page/reports.php" class="tool-link">View Reports →</a>
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
                    <p class="activity-text"><strong>Alex Johnson</strong> submitted deliverables for "E-commerce Website"</p>
                    <span class="activity-time">2 hours ago</span>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon">✅</div>
                <div class="activity-content">
                    <p class="activity-text">Project "Brand Identity Design" completed and marked as done</p>
                    <span class="activity-time">1 day ago</span>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon">💬</div>
                <div class="activity-content">
                    <p class="activity-text"><strong>Maria Garcia</strong> sent you a message about design revisions</p>
                    <span class="activity-time">3 days ago</span>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon">💳</div>
                <div class="activity-content">
                    <p class="activity-text">Payment of $1,500 released to freelancer for completed work</p>
                    <span class="activity-time">5 days ago</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php

include '_foot.php';

?>


