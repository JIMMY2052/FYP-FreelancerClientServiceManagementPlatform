<?php

session_start();

// Check if user is logged in and is a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: /index.php');
    exit();
}

$_title = 'Dashboard - WorkSnyc Freelancer Platform';
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
                <a href="/page/browse_projects.php?category=design" class="category-link">Browse Projects →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">💻</div>
                <h3 class="category-title">Programming & Tech</h3>
                <p class="category-description">Web development, mobile apps, software</p>
                <a href="/page/browse_projects.php?category=tech" class="category-link">Browse Projects →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📝</div>
                <h3 class="category-title">Writing & Translation</h3>
                <p class="category-description">Content writing, copywriting, translation</p>
                <a href="/page/browse_projects.php?category=writing" class="category-link">Browse Projects →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📱</div>
                <h3 class="category-title">Digital Marketing</h3>
                <p class="category-description">SEO, social media, email marketing</p>
                <a href="/page/browse_projects.php?category=marketing" class="category-link">Browse Projects →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📹</div>
                <h3 class="category-title">Video & Animation</h3>
                <p class="category-description">Video editing, animation, VFX projects</p>
                <a href="/page/browse_projects.php?category=video" class="category-link">Browse Projects →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">🎵</div>
                <h3 class="category-title">Music & Audio</h3>
                <p class="category-description">Music production, voice-over, audio editing</p>
                <a href="/page/browse_projects.php?category=audio" class="category-link">Browse Projects →</a>
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
        <div class="opportunities-grid">
            <div class="opportunity-card">
                <div class="opportunity-header">
                    <h3 class="opportunity-title">Website Redesign Project</h3>
                    <span class="opportunity-budget">$500 - $1000</span>
                </div>
                <p class="opportunity-description">We need a modern website redesign for our e-commerce platform. Must have experience with React and responsive design.</p>
                <div class="opportunity-skills">
                    <span class="skill-tag">React</span>
                    <span class="skill-tag">UI/UX</span>
                    <span class="skill-tag">JavaScript</span>
                </div>
                <div class="opportunity-footer">
                    <span class="opportunity-time">Posted 2 hours ago</span>
                    <a href="/page/project_details.php" class="btn-small">View Details</a>
                </div>
            </div>

            <div class="opportunity-card">
                <div class="opportunity-header">
                    <h3 class="opportunity-title">Logo Design</h3>
                    <span class="opportunity-budget">$200 - $500</span>
                </div>
                <p class="opportunity-description">Looking for a creative logo designer for our new tech startup. Modern, minimalist design preferred.</p>
                <div class="opportunity-skills">
                    <span class="skill-tag">Graphic Design</span>
                    <span class="skill-tag">Adobe XD</span>
                    <span class="skill-tag">Branding</span>
                </div>
                <div class="opportunity-footer">
                    <span class="opportunity-time">Posted 4 hours ago</span>
                    <a href="/page/project_details.php" class="btn-small">View Details</a>
                </div>
            </div>

            <div class="opportunity-card">
                <div class="opportunity-header">
                    <h3 class="opportunity-title">Mobile App Development</h3>
                    <span class="opportunity-budget">$2000 - $5000</span>
                </div>
                <p class="opportunity-description">Develop a cross-platform mobile app for iOS and Android. Flutter or React Native experience required.</p>
                <div class="opportunity-skills">
                    <span class="skill-tag">Flutter</span>
                    <span class="skill-tag">Mobile Dev</span>
                    <span class="skill-tag">API Integration</span>
                </div>
                <div class="opportunity-footer">
                    <span class="opportunity-time">Posted 6 hours ago</span>
                    <a href="/page/project_details.php" class="btn-small">View Details</a>
                </div>
            </div>
        </div>
        <div class="view-all-section">
            <a href="/page/browse_projects.php" class="btn-primary">View All Opportunities</a>
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
                <a href="/page/portfolio.php" class="tool-link">Manage Portfolio →</a>
            </div>
            <div class="tool-item">
                <div class="tool-icon">📄</div>
                <h3 class="tool-title">Proposals</h3>
                <p class="tool-description">Create and track all your project proposals in one place.</p>
                <a href="/page/proposals.php" class="tool-link">View Proposals →</a>
            </div>
            <div class="tool-item">
                <div class="tool-icon">🏆</div>
                <h3 class="tool-title">Certifications</h3>
                <p class="tool-description">Add certifications and badges to build trust with clients.</p>
                <a href="/page/certifications.php" class="tool-link">Add Certifications →</a>
            </div>
            <div class="tool-item">
                <div class="tool-icon">📈</div>
                <h3 class="tool-title">Performance Analytics</h3>
                <p class="tool-description">Track your profile views, project success rate, and earnings.</p>
                <a href="/page/analytics.php" class="tool-link">View Analytics →</a>
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