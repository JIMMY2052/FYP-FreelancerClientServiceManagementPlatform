<?php

$_title = 'Home - Freelancer Platform';
include '_head.php';

?>

<!-- Hero Section (Keeping existing design) -->
<div class="post-job-container hero-section">
    <div class="hero-content">
        <p class="post-job-tagline">Post. Match. Complete.</p>
        <p class="hero-subtitle">Connect with talented freelancers and find your next project</p>
        <a href="/job/create/createJob.php" class="btn-post-job">Post job</a>
    </div>
    <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&auto=format&fit=crop&q=80" alt="Team collaboration" class="hero-img">
    </div>
</div>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <h2 class="section-title">Why Choose Our Platform?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?w=400&auto=format&fit=crop&q=80" alt="Quick Matching" class="feature-image">
                </div>
                <h3 class="feature-title">Quick Matching</h3>
                <p class="feature-description">Find the perfect freelancer or client in minutes with our intelligent matching system.</p>
            </div>
            <div class="feature-card">
                <div class="feature-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?w=400&auto=format&fit=crop&q=80" alt="Professional Services" class="feature-image">
                </div>
                <h3 class="feature-title">Professional Services</h3>
                <p class="feature-description">Connect with verified professionals across various industries and skill sets.</p>
            </div>
            <div class="feature-card">
                <div class="feature-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=400&auto=format&fit=crop&q=80" alt="Secure Platform" class="feature-image">
                </div>
                <h3 class="feature-title">Secure Platform</h3>
                <p class="feature-description">Your projects and payments are protected with our secure platform infrastructure.</p>
            </div>
            <div class="feature-card">
                <div class="feature-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=400&auto=format&fit=crop&q=80" alt="Quality Guaranteed" class="feature-image">
                </div>
                <h3 class="feature-title">Quality Guaranteed</h3>
                <p class="feature-description">Work with top-rated freelancers and clients who deliver exceptional results.</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works-section">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <div class="steps-container">
            <div class="step-item">
                <div class="step-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=300&auto=format&fit=crop&q=80" alt="Post Your Project" class="step-image">
                    <div class="step-number">1</div>
                </div>
                <h3 class="step-title">Post Your Project</h3>
                <p class="step-description">Create a detailed job posting or service gig with your requirements and budget.</p>
            </div>
            <div class="step-item">
                <div class="step-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=300&auto=format&fit=crop&q=80" alt="Get Matched" class="step-image">
                    <div class="step-number">2</div>
                </div>
                <h3 class="step-title">Get Matched</h3>
                <p class="step-description">Our platform connects you with the most suitable freelancers or clients automatically.</p>
            </div>
            <div class="step-item">
                <div class="step-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1556761175-b413da4baf72?w=300&auto=format&fit=crop&q=80" alt="Complete & Review" class="step-image">
                    <div class="step-number">3</div>
                </div>
                <h3 class="step-title">Complete & Review</h3>
                <p class="step-description">Finish your project, make secure payments, and leave reviews to help others.</p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">10K+</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">5K+</div>
                <div class="stat-label">Completed Projects</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Expert Freelancers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">98%</div>
                <div class="stat-label">Satisfaction Rate</div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-title">Ready to Get Started?</h2>
        <p class="cta-description">Join thousands of clients and freelancers who are already using our platform.</p>
        <div class="cta-buttons">
            <a href="/job/create/createJob.php" class="btn-cta btn-cta-primary">Post a Job</a>
            <a href="/signup.php" class="btn-cta btn-cta-secondary">Sign Up as Freelancer</a>
        </div>
    </div>
</section>

<?php

include '_foot.php';

?>