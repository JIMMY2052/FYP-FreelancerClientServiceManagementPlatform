<?php

session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: /index.php');
    exit();
}

$_title = 'Dashboard - WorkSnyc Freelancer Platform';
include '_head.php';

?>

<!-- Client Dashboard Hero Section -->
<section class="client-hero-section">
    <div class="container">
        <div class="hero-content-wrapper">
            <div class="hero-text">
                <h1 class="hero-title">Post. Match. Complete.</h1>
                <p class="hero-subtitle">Find talented freelancers and manage your projects efficiently</p>
                <div class="hero-buttons">
                    <a href="/page/job/createJob.php" class="btn-hero btn-hero-primary">Post a Project</a>
                    <a href="/page/browse_freelancers.php" class="btn-hero btn-hero-secondary">Browse Freelancers</a>
                </div>
            </div>
            <div class="hero-image-wrapper">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&auto=format&fit=crop&q=80" alt="Client managing projects" class="hero-main-image">
            </div>
        </div>
    </div>
</section>

<!-- Client Quick Stats Section -->
<section class="quick-stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <div class="stat-number">8</div>
                    <div class="stat-label">Active Projects</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <div class="stat-number">12</div>
                    <div class="stat-label">Hired Freelancers</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <div class="stat-number">$15,200</div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <div class="stat-number">28</div>
                    <div class="stat-label">Completed Projects</div>
                </div>
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
                <a href="/page/browse_freelancers.php?category=design" class="category-link">Browse Freelancers →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">💻</div>
                <h3 class="category-title">Programming & Tech</h3>
                <p class="category-description">Web development, mobile apps, software engineering</p>
                <a href="/page/browse_freelancers.php?category=tech" class="category-link">Browse Freelancers →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📝</div>
                <h3 class="category-title">Writing & Translation</h3>
                <p class="category-description">Content writing, copywriting, translation services</p>
                <a href="/page/browse_freelancers.php?category=writing" class="category-link">Browse Freelancers →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📱</div>
                <h3 class="category-title">Digital Marketing</h3>
                <p class="category-description">SEO, social media, email marketing, PPC campaigns</p>
                <a href="/page/browse_freelancers.php?category=marketing" class="category-link">Browse Freelancers →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">📹</div>
                <h3 class="category-title">Video & Animation</h3>
                <p class="category-description">Video editing, animation, motion graphics, VFX</p>
                <a href="/page/browse_freelancers.php?category=video" class="category-link">Browse Freelancers →</a>
            </div>
            <div class="category-card">
                <div class="category-icon">🎵</div>
                <h3 class="category-title">Music & Audio</h3>
                <p class="category-description">Music production, voice-over, audio editing</p>
                <a href="/page/browse_freelancers.php?category=audio" class="category-link">Browse Freelancers →</a>
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
        <div class="projects-grid">
            <div class="project-card">
                <div class="project-header">
                    <h3 class="project-title">E-commerce Website Development</h3>
                    <span class="project-status status-in-progress">In Progress</span>
                </div>
                <p class="project-description">Full-stack e-commerce platform development</p>
                <div class="project-info">
                    <span class="project-budget">Budget: $3,000</span>
                    <span class="project-progress">60% Complete</span>
                </div>
                <div class="project-footer">
                    <span class="project-time">Due: Dec 15, 2025</span>
                    <a href="/page/project_details.php" class="btn-small">View Project</a>
                </div>
            </div>

            <div class="project-card">
                <div class="project-header">
                    <h3 class="project-title">Brand Identity Design</h3>
                    <span class="project-status status-in-progress">In Progress</span>
                </div>
                <p class="project-description">Complete brand identity including logo and guidelines</p>
                <div class="project-info">
                    <span class="project-budget">Budget: $800</span>
                    <span class="project-progress">40% Complete</span>
                </div>
                <div class="project-footer">
                    <span class="project-time">Due: Dec 20, 2025</span>
                    <a href="/page/project_details.php" class="btn-small">View Project</a>
                </div>
            </div>

            <div class="project-card">
                <div class="project-header">
                    <h3 class="project-title">Social Media Content</h3>
                    <span class="project-status status-pending">Pending</span>
                </div>
                <p class="project-description">Monthly social media content creation and management</p>
                <div class="project-info">
                    <span class="project-budget">Budget: $500/month</span>
                    <span class="project-progress">Awaiting Start</span>
                </div>
                <div class="project-footer">
                    <span class="project-time">Starts: Dec 1, 2025</span>
                    <a href="/page/project_details.php" class="btn-small">View Project</a>
                </div>
            </div>
        </div>
        <div class="view-all-section">
            <a href="/page/my_projects.php" class="btn-primary">View All Projects</a>
        </div>
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

<!-- Recent Freelancer Recommendations -->
<section class="recommendations-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Recommended Freelancers</h2>
            <p class="section-subtitle">Top-rated professionals matching your needs</p>
        </div>
        <div class="freelancer-cards-grid">
            <div class="freelancer-card">
                <div class="freelancer-header">
                    <img src="https://via.placeholder.com/100" alt="Freelancer" class="freelancer-avatar">
                    <h3 class="freelancer-name">Alex Johnson</h3>
                    <p class="freelancer-title">Full Stack Developer</p>
                </div>
                <div class="freelancer-rating">
                    <span class="stars">⭐⭐⭐⭐⭐</span>
                    <span class="rating-count">4.9 (128 reviews)</span>
                </div>
                <p class="freelancer-description">Expert in React, Node.js, and MongoDB with 8+ years experience</p>
                <div class="freelancer-footer">
                    <a href="/page/freelancer_profile.php" class="btn-small">View Profile</a>
                </div>
            </div>

            <div class="freelancer-card">
                <div class="freelancer-header">
                    <img src="https://via.placeholder.com/100" alt="Freelancer" class="freelancer-avatar">
                    <h3 class="freelancer-name">Maria Garcia</h3>
                    <p class="freelancer-title">UI/UX Designer</p>
                </div>
                <div class="freelancer_rating">
                    <span class="stars">⭐⭐⭐⭐⭐</span>
                    <span class="rating-count">4.8 (95 reviews)</span>
                </div>
                <p class="freelancer-description">Specializes in web and mobile app design with modern aesthetics</p>
                <div class="freelancer-footer">
                    <a href="/page/freelancer_profile.php" class="btn-small">View Profile</a>
                </div>
            </div>

            <div class="freelancer-card">
                <div class="freelancer-header">
                    <img src="https://via.placeholder.com/100" alt="Freelancer" class="freelancer-avatar">
                    <h3 class="freelancer-name">David Chen</h3>
                    <p class="freelancer-title">Digital Marketing Specialist</p>
                </div>
                <div class="freelancer_rating">
                    <span class="stars">⭐⭐⭐⭐⭐</span>
                    <span class="rating-count">4.7 (112 reviews)</span>
                </div>
                <p class="freelancer-description">Expert in SEO, SEM, and social media marketing strategy</p>
                <div class="freelancer-footer">
                    <a href="/page/freelancer_profile.php" class="btn-small">View Profile</a>
                </div>
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


