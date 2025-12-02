<?php
session_start();

// only freelancers can create gigs
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: /index.php');
    exit();
}

$_title = 'Create Gig - Overview';
include '../../_head.php';

// Category and Subcategory mapping with metadata
$categoryData = [
    'graphic-design' => [
        'name' => 'Graphic & Design',
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
        ],
        'metadata' => [
            'logo-design' => [
                'design_style' => ['Modern', 'Minimal', 'Vintage', 'Abstract', 'Geometric'],
                'file_format' => ['PNG', 'SVG', 'AI', 'PSD', 'PDF'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'brand-style-guide' => [
                'pages_included' => ['5-10', '10-20', '20-30', '30+'],
                'elements_included' => ['Logo', 'Color Palette', 'Typography', 'Icons', 'Imagery Style'],
                'file_format' => ['PDF', 'Figma', 'Adobe XD', 'Sketch'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'game-art' => [
                'art_type' => ['Character Design', '2D Art', '3D Art', 'Animation', 'Concept Art'],
                'style' => ['Realistic', 'Cartoon', 'Pixel Art', 'Anime', 'Low Poly'],
                'deliverables' => ['Artwork Only', 'With PSD/Source Files', 'Unlimited Revisions'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'graphics-streamers' => [
                'graphics_type' => ['Stream Overlay', 'Emotes', 'Stream Alerts', 'Panels', 'Banners'],
                'resolution' => ['720p', '1080p', '2K', '4K'],
                'animated' => ['No', 'Yes - Simple', 'Yes - Complex'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'business-cards' => [
                'design_type' => ['Business Cards', 'Stationery (Letterhead, Envelope, etc.)'],
                'quantity' => ['Digital Only', '50 Cards', '100 Cards', '250 Cards', '500 Cards'],
                'sided' => ['Single-sided', 'Double-sided'],
                'file_format' => ['PNG', 'PDF', 'AI', 'PSD'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'website-design' => [
                'pages' => ['1-3', '3-5', '5-10', '10+'],
                'features' => ['Responsive', 'E-commerce', 'Blog', 'Contact Form', 'SEO Optimized'],
                'design_tool' => ['Figma', 'Adobe XD', 'Sketch', 'Photoshop', 'Webflow'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'app-design' => [
                'platform' => ['iOS', 'Android', 'Cross-platform'],
                'screens' => ['5-10', '10-20', '20-30', '30+'],
                'features' => ['UI Kit', 'Prototype', 'Animation', 'Icon Design'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'ux-design' => [
                'deliverables' => ['Wireframes', 'Prototypes', 'User Research', 'Usability Testing'],
                'scope' => ['Single Flow', 'Multiple Flows', 'Full App', 'Full Website'],
                'tools' => ['Figma', 'Adobe XD', 'Sketch', 'Framer'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'landing-page-design' => [
                'sections' => ['3-5', '5-8', '8-12', '12+'],
                'features' => ['Responsive', 'CTA Buttons', 'Forms', 'Animation', 'SEO Ready'],
                'include_copywriting' => ['Design Only', 'With Copy', 'With Copy & Images'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ]
        ]
    ],
    'programming-tech' => [
        'name' => 'Programming & Tech',
        'subcategories' => [
            'website-development' => 'Website Development',
            'website-maintenance' => 'Website Maintenance',
            'software-development' => 'Software Development',
            'game-development' => 'Game Development',
            'ai-development' => 'AI Development',
            'chatbot-development' => 'Chatbot Development',
            'cloud-computing' => 'Cloud Computing',
            'mobile-app-dev' => 'Mobile App Development'
        ],
        'metadata' => [
            'website-development' => [
                'type' => ['Static Website', 'Dynamic Website', 'E-commerce', 'CMS Website'],
                'pages' => ['1-5', '5-10', '10-20', '20+'],
                'technologies' => ['HTML/CSS', 'WordPress', 'React', 'Vue', 'Next.js', 'Custom'],
                'hosting' => ['Not Included', 'Included', 'Help with Setup'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'website-maintenance' => [
                'services' => ['Bug Fixes', 'Updates', 'Backups', 'Security', 'Performance'],
                'response_time' => ['24 hours', '12 hours', '4 hours', 'Real-time'],
                'hours_per_month' => ['5', '10', '20', '40+'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'software-development' => [
                'project_type' => ['Desktop App', 'Web App', 'Backend API', 'Full Stack'],
                'tech_stack' => ['Python', 'JavaScript', 'Java', 'C#', '.NET', 'Go', 'Rust'],
                'scope' => ['Small Project', 'Medium Project', 'Large Project', 'Enterprise'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'game-development' => [
                'game_type' => ['2D Game', '3D Game', 'Mobile Game', 'VR Game'],
                'engine' => ['Unity', 'Unreal Engine', 'Godot', 'Custom'],
                'features' => ['Mechanics Only', 'With Graphics', 'Multiplayer', 'Full Game'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'ai-development' => [
                'ai_type' => ['Machine Learning', 'Deep Learning', 'NLP', 'Computer Vision', 'Chatbot AI'],
                'framework' => ['TensorFlow', 'PyTorch', 'Scikit-learn', 'OpenAI API'],
                'deliverables' => ['Model Only', 'With Integration', 'With Documentation'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'chatbot-development' => [
                'platform' => ['Discord', 'Telegram', 'Slack', 'Custom Platform', 'Multi-platform'],
                'ai_level' => ['Rule-based', 'Basic AI', 'Advanced AI', 'GPT Integration'],
                'features' => ['Text Only', 'With Voice', 'With Actions', 'With Database'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'cloud-computing' => [
                'service' => ['Infrastructure Setup', 'Migration', 'Optimization', 'Security'],
                'provider' => ['AWS', 'Google Cloud', 'Azure', 'DigitalOcean', 'Heroku'],
                'scope' => ['Single Service', 'Full Stack', 'Multi-region', 'Enterprise Setup'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'mobile-app-dev' => [
                'platform' => ['iOS', 'Android', 'Cross-platform (React Native)', 'Cross-platform (Flutter)'],
                'features' => ['UI/UX', 'Backend Integration', 'Database', 'Authentication'],
                'complexity' => ['Simple', 'Medium', 'Complex', 'Enterprise'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ]
        ]
    ],
    'digital-marketing' => [
        'name' => 'Digital Marketing',
        'subcategories' => [
            'seo' => 'SEO',
            'social-media' => 'Social Media Marketing',
            'ppc' => 'PPC Advertising',
            'email-marketing' => 'Email Marketing',
            'content-marketing' => 'Content Marketing'
        ],
        'metadata' => [
            'seo' => [
                'scope' => ['On-page SEO', 'Technical SEO', 'Link Building', 'Full SEO Audit'],
                'keywords' => ['1-5', '5-10', '10-20', '20+'],
                'reporting' => ['Monthly', 'Bi-weekly', 'Weekly'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'social-media' => [
                'platforms' => ['1 Platform', '2-3 Platforms', '4-5 Platforms', 'All Platforms'],
                'content_type' => ['Posts Only', 'Posts & Stories', 'Posts & Videos', 'Full Content'],
                'frequency' => ['2 per week', '4 per week', 'Daily', 'Custom'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'ppc' => [
                'platform' => ['Google Ads', 'Facebook Ads', 'Instagram Ads', 'Multiple Platforms'],
                'budget' => ['$100-500', '$500-1000', '$1000-5000', '$5000+'],
                'management' => ['Setup Only', 'Management & Optimization'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'email-marketing' => [
                'type' => ['Campaign Creation', 'List Building', 'Automation', 'Full Service'],
                'emails' => ['5-10', '10-20', '20-50', '50+'],
                'personalization' => ['Basic', 'Intermediate', 'Advanced'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'content-marketing' => [
                'content_type' => ['Blog Posts', 'Videos', 'Infographics', 'Whitepapers', 'Case Studies'],
                'quantity' => ['1', '3', '5', '10+'],
                'seo_optimized' => ['No', 'Yes'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ]
        ]
    ],
    'writing-translation' => [
        'name' => 'Writing & Translation',
        'subcategories' => [
            'article-writing' => 'Article Writing',
            'copywriting' => 'Copywriting',
            'technical-writing' => 'Technical Writing',
            'translation' => 'Translation',
            'proofreading' => 'Proofreading'
        ],
        'metadata' => [
            'article-writing' => [
                'length' => ['Under 500 words', '500-1000 words', '1000-2000 words', '2000+ words'],
                'topics' => ['Technology', 'Lifestyle', 'Business', 'Health', 'Entertainment'],
                'seo_optimized' => ['No', 'Yes'],
                'research_included' => ['No', 'Yes'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'copywriting' => [
                'type' => ['Product Description', 'Ad Copy', 'Landing Page', 'Email Copy', 'Social Media'],
                'length' => ['Short', 'Medium', 'Long'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'technical-writing' => [
                'document_type' => ['User Manual', 'API Documentation', 'Guide', 'Help Articles'],
                'length' => ['Short', 'Medium', 'Long', 'Extensive'],
                'diagrams_included' => ['No', 'Yes'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'translation' => [
                'source_language' => ['English', 'Spanish', 'French', 'German', 'Chinese', 'Japanese'],
                'target_language' => ['English', 'Spanish', 'French', 'German', 'Chinese', 'Japanese'],
                'word_count' => ['Up to 500', '500-1000', '1000-5000', '5000+'],
                'type' => ['Document', 'Website', 'Software', 'Marketing'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ],
            'proofreading' => [
                'document_type' => ['Academic', 'Business', 'Creative', 'Technical'],
                'word_count' => ['Up to 500', '500-1000', '1000-5000', '5000+'],
                'level' => ['Basic', 'Standard', 'Professional'],
                'revisions' => ['0', '1', '2', '3', '5', 'Unlimited']
            ]
        ]
    ]
];
?>

<style>
    /* Create Gig Page Styles */
    .create-gig-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    /* Milestone Stepper */
    .milestone-container {
        margin-bottom: 50px;
    }

    .milestone-stepper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 30px 0;
        border-bottom: 2px solid #e9ecef;
    }

    .milestone-step {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        position: relative;
        text-decoration: none;
        color: inherit;
        cursor: default;
    }

    .milestone-step.clickable {
        cursor: pointer;
    }

    .milestone-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #e9ecef;
        border: 2px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #999;
        transition: all 0.3s ease;
        flex-shrink: 0;
        font-size: 1rem;
    }

    .milestone-step.active .milestone-circle {
        background: rgb(159, 232, 112);
        color: #333;
        border-color: rgb(159, 232, 112);
        box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
    }

    .milestone-step.completed .milestone-circle {
        background: rgb(140, 210, 90);
        color: white;
        border-color: rgb(140, 210, 90);
    }

    .milestone-step.clickable.completed:hover .milestone-circle {
        background: rgb(159, 232, 112);
        box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
    }

    .milestone-label-wrapper {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .milestone-label {
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    .milestone-step.active .milestone-label {
        color: rgb(159, 232, 112);
    }

    .milestone-step.completed .milestone-label {
        color: rgb(140, 210, 90);
    }

    .milestone-sublabel {
        font-size: 0.8rem;
        color: #999;
    }

    .milestone-step.completed .milestone-sublabel {
        color: rgb(140, 210, 90);
    }

    /* Separator arrow */
    .milestone-separator {
        flex-shrink: 0;
        color: #ddd;
        font-size: 1.5rem;
        margin: 0 10px;
        font-weight: 300;
    }

    .milestone-step.completed ~ .milestone-separator {
        color: rgb(140, 210, 90);
    }

    @media (max-width: 1024px) {
        .milestone-stepper {
            padding: 20px 0;
        }

        .milestone-circle {
            width: 40px;
            height: 40px;
            font-size: 0.9rem;
        }

        .milestone-label {
            font-size: 0.85rem;
        }

        .milestone-sublabel {
            font-size: 0.75rem;
        }

        .milestone-separator {
            margin: 0 5px;
        }
    }

    @media (max-width: 768px) {
        .milestone-stepper {
            padding: 15px 0;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .milestone-step {
            min-width: max-content;
            flex: 0 0 auto;
        }

        .milestone-circle {
            width: 35px;
            height: 35px;
            font-size: 0.8rem;
        }

        .milestone-label {
            font-size: 0.8rem;
        }

        .milestone-sublabel {
            display: none;
        }

        .milestone-separator {
            margin: 0 3px;
            font-size: 1.2rem;
        }
    }

    @media (max-width: 480px) {
        .milestone-circle {
            width: 30px;
            height: 30px;
            font-size: 0.7rem;
        }

        .milestone-label {
            font-size: 0.75rem;
        }
    }

    /* Form Styles */
    .gig-form-container {
        background: white;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .gig-form-section {
        margin-bottom: 30px;
    }

    .gig-form-section h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 16px 0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid #ddd;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: rgb(159, 232, 112);
        box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-description {
        font-size: 0.85rem;
        color: #999;
        margin-top: 6px;
    }

    /* Metadata Section */
    .metadata-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        display: none;
    }

    .metadata-container.show {
        display: block;
    }

    .metadata-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .metadata-item {
        display: flex;
        flex-direction: column;
    }

    .metadata-item label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .metadata-item label .required {
        color: #e74c3c;
        margin-left: 2px;
    }

    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        padding: 12px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .checkbox-group.error {
        border: 2px solid #e74c3c;
        background: #ffebee;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .checkbox-item input[type="checkbox"],
    .checkbox-item input[type="radio"] {
        width: auto;
        margin: 0;
        cursor: pointer;
    }

    .checkbox-item label {
        margin: 0;
        font-weight: 400;
        font-size: 0.9rem;
        cursor: pointer;
    }

    /* Tags Input Styles */
    .tags-input-container {
        min-height: 120px;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 12px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: flex-start;
        cursor: text;
        transition: all 0.3s ease;
    }

    .tags-input-container:focus-within {
        border-color: rgb(159, 232, 112);
        box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
    }

    .tag-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: #e9ecef;
        border-radius: 20px;
        font-size: 0.9rem;
        color: #2c3e50;
        font-weight: 500;
    }

    .tag-remove {
        cursor: pointer;
        background: none;
        border: none;
        color: #999;
        font-size: 1.1rem;
        line-height: 1;
        padding: 0;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .tag-remove:hover {
        background: #dc3545;
        color: white;
    }

    .tags-input-field {
        border: none;
        outline: none;
        padding: 8px 4px;
        font-size: 0.95rem;
        flex: 1;
        min-width: 200px;
        font-family: inherit;
    }

    .tags-input-field::placeholder {
        color: #999;
    }

    .tags-counter {
        font-size: 0.85rem;
        color: #666;
        margin-top: 6px;
    }

    .tags-counter.limit-reached {
        color: #dc3545;
        font-weight: 600;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 30px;
        justify-content: flex-end;
    }

    .btn {
        padding: 12px 28px;
        border-radius: 20px;
        border: none;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        font-size: 0.95rem;
    }

    .btn-primary {
        background: rgb(159, 232, 112);
        color: #333;
    }

    .btn-primary:hover {
        background: rgb(140, 210, 90);
        box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
    }

    .btn-secondary {
        background: #eee;
        color: #333;
    }

    .btn-secondary:hover {
        background: #ddd;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .create-gig-container {
            padding: 20px;
        }

        .gig-form-container {
            padding: 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }
</style>

<div class="create-gig-container">
    <!-- Milestone Stepper (requirements removed) -->
    <div class="milestone-container">
        <div class="milestone-stepper">
            <div class="milestone-step active" data-step="overview">
                <div class="milestone-circle">1</div>
                <div class="milestone-label-wrapper"><div class="milestone-label">Overview</div></div>
            </div>

            <div class="milestone-separator">›</div>

            <div class="milestone-step" data-step="pricing">
                <div class="milestone-circle">2</div>
                <div class="milestone-label-wrapper"><div class="milestone-label">Pricing</div></div>
            </div>

            <div class="milestone-separator">›</div>

            <div class="milestone-step" data-step="description">
                <div class="milestone-circle">3</div>
                <div class="milestone-label-wrapper"><div class="milestone-label">Description</div></div>
            </div>

            <div class="milestone-separator">›</div>

            <div class="milestone-step" data-step="gallery">
                <div class="milestone-circle">4</div>
                <div class="milestone-label-wrapper"><div class="milestone-label">Gallery</div></div>
            </div>

            <div class="milestone-separator">›</div>

            <div class="milestone-step" data-step="publish">
                <div class="milestone-circle">5</div>
                <div class="milestone-label-wrapper"><div class="milestone-label">Publish</div></div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="gig-form-container">
        <form id="overviewForm" method="POST" action="">
            <!-- Gig Title Section -->
            <div class="gig-form-section">
                <h3>Gig Title</h3>
                <div class="form-group">
                    <label for="gigTitle">What's the title of your gig? *</label>
                    <input type="text" id="gigTitle" name="gigTitle" placeholder="e.g., I will design a professional logo for your brand" required maxlength="120">
                    <div class="form-description">Be specific and clear about what you're offering</div>
                </div>
            </div>

            <!-- Category Section -->
            <div class="gig-form-section">
                <h3>Category & Subcategory</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="gigCategory">Category *</label>
                        <select id="gigCategory" name="gigCategory" required>
                            <option value="" disabled selected hidden>Select Category</option>
                            <?php foreach ($categoryData as $key => $category): ?>
                                <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="gigSubcategory">Subcategory *</label>
                        <select id="gigSubcategory" name="gigSubcategory" required>
                            <option value="" disabled selected hidden>Select Subcategory</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Gig Metadata Section -->
            <div class="gig-form-section">
                <h3>Gig Details</h3>
                <div class="metadata-container" id="metadataContainer">
                    <div class="metadata-grid" id="metadataGrid">
                        <!-- Metadata will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Search Tags Section -->
            <div class="gig-form-section">
                <h3>Search Tags</h3>
                <div class="form-group">
                    <label for="searchTags">Add up to 5 tags to help buyers find your gig *</label>
                    <div class="tags-input-container" id="tagsContainer">
                        <input type="text" class="tags-input-field" id="tagsInput" placeholder="Type a tag and press Enter" autocomplete="off">
                    </div>
                    <input type="hidden" id="searchTags" name="searchTags" required>
                    <div class="tags-counter" id="tagsCounter">0 / 5 tags</div>
                    <div class="form-description">Press Enter or comma to add a tag. Use letters and numbers only.</div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="window.history.back();">Back</button>
                <button type="button" class="btn btn-primary" onclick="validateAndContinue();">Continue to Pricing</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Category Data
    const categoryData = <?php echo json_encode($categoryData); ?>;

    // Tags management
    let tags = [];
    const MAX_TAGS = 5;

    // Milestone step pages
    const stepPages = {
        'overview': 'create_gig.php',
        'pricing': 'gig_price.php',
        'description': 'gig_description.php',
        'gallery': 'gig_gallery.php',
        'publish': 'gig_summary.php'
    };

    // Initialize milestone
    document.addEventListener('DOMContentLoaded', function() {
        const milestoneSteps = document.querySelectorAll('.milestone-step');
        
        // Get stored form data to mark completed steps
        const storedData = localStorage.getItem('gigFormData');
        if (storedData) {
            try {
                const formData = JSON.parse(storedData);
                markCompletedSteps(formData);
            } catch (e) {
                console.log('No stored form data');
            }
        }

        // Add click handlers to completed steps only
        milestoneSteps.forEach(step => {
            if (step.classList.contains('completed')) {
                step.classList.add('clickable');
                step.addEventListener('click', function(e) {
                    e.preventDefault();
                    const stepKey = this.getAttribute('data-step');
                    if (stepPages[stepKey]) {
                        window.location.href = stepPages[stepKey];
                    }
                });
            }
        });
    });

    // Tags Input Functionality
    const tagsInput = document.getElementById('tagsInput');
    const tagsContainer = document.getElementById('tagsContainer');
    const tagsCounter = document.getElementById('tagsCounter');
    const searchTagsHidden = document.getElementById('searchTags');

    // Focus input when clicking on container
    tagsContainer.addEventListener('click', function() {
        tagsInput.focus();
    });

    // Handle tag input
    tagsInput.addEventListener('keydown', function(e) {
        if ((e.key === 'Enter' || e.key === ',') && this.value.trim()) {
            e.preventDefault();
            addTag(this.value.trim());
            this.value = '';
        } else if (e.key === 'Backspace' && !this.value && tags.length > 0) {
            removeTag(tags.length - 1);
        }
    });

    // Handle blur to add tag
    tagsInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            addTag(this.value.trim());
            this.value = '';
        }
    });

    function addTag(tagText) {
        // Clean the tag (remove special characters, keep only letters, numbers and spaces)
        tagText = tagText.toLowerCase().replace(/[^a-z0-9\s]/g, '').trim();
        
        if (!tagText) {
            return;
        }

        // Check if already exists
        if (tags.includes(tagText)) {
            alert('This tag already exists!');
            return;
        }

        // Check limit
        if (tags.length >= MAX_TAGS) {
            alert(`Maximum ${MAX_TAGS} tags allowed!`);
            return;
        }

        // Add tag
        tags.push(tagText);
        renderTags();
        updateTagsInput();
    }

    function removeTag(index) {
        tags.splice(index, 1);
        renderTags();
        updateTagsInput();
    }

    function renderTags() {
        // Remove existing tag items
        const existingTags = tagsContainer.querySelectorAll('.tag-item');
        existingTags.forEach(tag => tag.remove());

        // Add tags before input field
        tags.forEach((tag, index) => {
            const tagElement = document.createElement('div');
            tagElement.className = 'tag-item';
            tagElement.innerHTML = `
                ${tag}
                <button type="button" class="tag-remove" onclick="removeTagByIndex(${index})" title="Remove tag">×</button>
            `;
            tagsContainer.insertBefore(tagElement, tagsInput);
        });

        // Update counter
        tagsCounter.textContent = `${tags.length} / ${MAX_TAGS} tags`;
        if (tags.length >= MAX_TAGS) {
            tagsCounter.classList.add('limit-reached');
            tagsInput.disabled = true;
            tagsInput.placeholder = 'Maximum tags reached';
        } else {
            tagsCounter.classList.remove('limit-reached');
            tagsInput.disabled = false;
            tagsInput.placeholder = 'Type a tag and press Enter';
        }
    }

    function updateTagsInput() {
        searchTagsHidden.value = tags.join(',');
    }

    // Make removeTag accessible globally for onclick
    window.removeTagByIndex = function(index) {
        removeTag(index);
    };

    function markCompletedSteps(formData) {
        // Mark overview as completed since we're on it
        const overviewStep = document.querySelector('[data-step="overview"]');
        if (overviewStep && !overviewStep.classList.contains('active')) {
            overviewStep.classList.add('completed');
        }
    }

    // Handle category change
    document.getElementById('gigCategory').addEventListener('change', function() {
        const selectedCategory = this.value;
        const subcategorySelect = document.getElementById('gigSubcategory');
        const metadataContainer = document.getElementById('metadataContainer');

        // Reset subcategory
        subcategorySelect.innerHTML = '<option value="" disabled selected hidden>Select Subcategory</option>';
        metadataContainer.classList.remove('show');

        if (selectedCategory && categoryData[selectedCategory]) {
            const subcategories = categoryData[selectedCategory].subcategories;
            for (const [key, name] of Object.entries(subcategories)) {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = name;
                subcategorySelect.appendChild(option);
            }
        }
    });

    // Handle subcategory change
    document.getElementById('gigSubcategory').addEventListener('change', function() {
        const categoryKey = document.getElementById('gigCategory').value;
        const subcategoryKey = this.value;
        const metadataGrid = document.getElementById('metadataGrid');
        const metadataContainer = document.getElementById('metadataContainer');

        metadataGrid.innerHTML = '';

        if (categoryKey && subcategoryKey && categoryData[categoryKey].metadata[subcategoryKey]) {
            const metadata = categoryData[categoryKey].metadata[subcategoryKey];
            
            for (const [key, options] of Object.entries(metadata)) {
                const metadataItem = document.createElement('div');
                metadataItem.className = 'metadata-item';

                // Format label (convert kebab-case to Title Case)
                const label = key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

                // Determine if field should be radio (single choice) or checkbox (multiple choice)
                // RADIO FIELDS (single choice only):
                const singleChoiceFields = [
                    'revisions', 'length', 'quantity', 'word_count', 'pages', 'screens', 
                    'emails', 'sections', 'hours_per_month', 'keywords', 'sided', 'animated',
                    'seo_optimized', 'research_included', 'diagrams_included', 'design_type',
                    'resolution', 'scope', 'type', 'project_type', 'game_type', 'ai_type',
                    'ai_level', 'platform', 'complexity', 'reporting', 'frequency', 
                    'personalization', 'budget', 'management', 'response_time', 'hosting',
                    'include_copywriting', 'source_language', 'target_language', 'level',
                    'pages_included', 'design_tool', 'engine'
                ];
                
                // CHECKBOX FIELDS (multiple choice allowed):
                // features, file_format, elements_included, deliverables, graphics_type,
                // art_type, style, technologies, services, tech_stack, framework, 
                // service, provider, content_type, topics, document_type
                
                const isRadioField = singleChoiceFields.includes(key);
                const inputType = isRadioField ? 'radio' : 'checkbox';
                
                metadataItem.innerHTML = `
                    <label>${label} <span class="required">*</span></label>
                    <div class="checkbox-group" data-group="${key}">
                        ${Array.isArray(options) ? options.map(option => `
                            <div class="checkbox-item">
                                <input type="${inputType}" name="${key}" value="${option}" id="${key}_${option}">
                                <label for="${key}_${option}">${option}</label>
                            </div>
                        `).join('') : ''}
                    </div>
                `;

                metadataGrid.appendChild(metadataItem);
            }

            metadataContainer.classList.add('show');
        }
    });

    function validateAndContinue() {
        const form = document.getElementById('overviewForm');
        
        if (!form.checkValidity()) {
            alert('Please fill in all required fields');
            form.reportValidity();
            return;
        }

        // Validate metadata: ensure at least one option is selected for each element
        const metadataGroups = document.querySelectorAll('.checkbox-group[data-group]');
        let hasError = false;
        let errorMessage = '';

        metadataGroups.forEach(group => {
            const groupName = group.getAttribute('data-group');
            const inputs = group.querySelectorAll('input[type="checkbox"], input[type="radio"]');
            const checked = Array.from(inputs).some(input => input.checked);
            
            if (!checked) {
                hasError = true;
                const label = groupName.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                errorMessage += `• Please select at least one option for "${label}"\n`;
                group.classList.add('error');
            } else {
                group.classList.remove('error');
            }
        });

        if (hasError) {
            alert('Please complete all gig details:\n\n' + errorMessage);
            return;
        }

        // Store form data in session/localStorage
        const formData = {
            gigTitle: document.getElementById('gigTitle').value,
            gigCategory: document.getElementById('gigCategory').value,
            gigSubcategory: document.getElementById('gigSubcategory').value,
            searchTags: tags.join(','),
            metadata: getMetadataValues()
        };

        // Store in localStorage
        localStorage.setItem('gigFormData', JSON.stringify(formData));

        // Mark step as completed
        const overviewStep = document.querySelector('[data-step="overview"]');
        overviewStep.classList.remove('active');
        overviewStep.classList.add('completed');
        overviewStep.classList.add('clickable');

        // Mark pricing as active
        const pricingStep = document.querySelector('[data-step="pricing"]');
        pricingStep.classList.add('active');

        // Redirect to pricing page
        window.location.href = 'gig_price.php';
    }

    function getMetadataValues() {
        const metadata = {};
        const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
        const radios = document.querySelectorAll('input[type="radio"]:checked');
        
        checkboxes.forEach(checkbox => {
            const key = checkbox.name;
            if (!metadata[key]) {
                metadata[key] = [];
            }
            metadata[key].push(checkbox.value);
        });

        radios.forEach(radio => {
            const key = radio.name;
            metadata[key] = [radio.value]; // Radio buttons only have one value
        });

        return metadata;
    }
</script>

<?php include '../../_foot.php'; ?>