<?php
session_start();

// only freelancers can create gigs
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: /index.php');
    exit();
}

$_title = 'Create Gig - Description';
include '../../_head.php';
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
        min-height: 200px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

    /* Character Counter */
    .char-counter-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 8px;
    }

    .char-counter {
        font-size: 0.85rem;
        color: #999;
        font-weight: 500;
    }

    .char-counter.warning {
        color: #ff9800;
    }

    .char-counter.limit-reached {
        color: #f44336;
    }

    /* FAQ Items */
    .faq-items-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .faq-item {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #e9ecef;
    }

    .faq-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        gap: 10px;
    }

    .faq-item-label {
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    .faq-item-count {
        background: #e9ecef;
        color: #666;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .faq-item-remove {
        background: #f44336;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .faq-item-remove:hover {
        background: #d32f2f;
    }

    .faq-item-inputs {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .faq-item-inputs input,
    .faq-item-inputs textarea {
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #ddd;
        font-size: 0.9rem;
        font-family: inherit;
    }

    .faq-item-inputs textarea {
        min-height: 80px;
        resize: vertical;
    }

    .faq-item-inputs input:focus,
    .faq-item-inputs textarea:focus {
        outline: none;
        border-color: rgb(159, 232, 112);
        box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
    }

    .add-faq-btn {
        background: rgb(159, 232, 112);
        color: #333;
        border: none;
        padding: 12px 28px;
        border-radius: 20px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        align-self: flex-start;
    }

    .add-faq-btn:hover {
        background: rgb(140, 210, 90);
        box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
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

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
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

        .faq-item-header {
            flex-wrap: wrap;
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
    <!-- Milestone Stepper -->
    <div class="milestone-container">
        <div class="milestone-stepper">
            <div class="milestone-step completed clickable" data-step="overview">
                <div class="milestone-circle">✓</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Overview</div>
                </div>
            </div>

            <div class="milestone-separator">›</div>

            <div class="milestone-step completed clickable" data-step="pricing">
                <div class="milestone-circle">✓</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Pricing</div>
                </div>
            </div>

            <div class="milestone-separator">›</div>

            <div class="milestone-step active" data-step="description">
                <div class="milestone-circle">3</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Description & FAQ</div>
                </div>
            </div>

            <div class="milestone-separator">›</div>

            <div class="milestone-step" data-step="requirements">
                <div class="milestone-circle">4</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Requirements</div>
                </div>
            </div>

            <div class="milestone-separator">›</div>

            <div class="milestone-step" data-step="gallery">
                <div class="milestone-circle">5</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Gallery</div>
                </div>
            </div>

            <div class="milestone-separator">›</div>

            <div class="milestone-step" data-step="publish">
                <div class="milestone-circle">6</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Publish</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="gig-form-container">
        <form id="descriptionForm" method="POST" action="">
            <!-- Description Section -->
            <div class="gig-form-section">
                <h3>📝 Gig Description</h3>
                <p class="form-description">Describe what you're offering in detail to help buyers understand your service better.</p>
                
                <div class="form-group">
                    <label for="gigDescription">Description *</label>
                    <textarea id="gigDescription" name="gigDescription" placeholder="Describe your gig in detail. Include what you offer, your process, deliverables, and any important information buyers should know..." required maxlength="1200"></textarea>
                    
                    <div class="char-counter-wrapper">
                        <div class="form-description">Maximum 1200 characters</div>
                        <div class="char-counter">
                            <span id="charCount">0</span>/1200
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="gig-form-section">
                <h3>❓ FAQ (Frequently Asked Questions)</h3>
                <p class="form-description">Add FAQ items to help answer common buyer questions about your gig.</p>
                
                <div class="faq-items-container" id="faqContainer">
                    <!-- FAQ items will be added here -->
                </div>

                <button type="button" class="add-faq-btn" onclick="addFAQItem();">+ Add FAQ Item</button>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="goToPreviousStep();">Back to Pricing</button>
                <button type="button" class="btn btn-primary" onclick="validateAndContinue();">Continue to Requirements</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Step pages mapping
    const stepPages = {
        'overview': 'create_gig.php',
        'pricing': 'gig_price.php',
        'description': 'gig_description.php',
        'requirements': 'gig_requirements.php',
        'gallery': 'gig_gallery.php',
        'publish': 'gig_publish.php'
    };

    let faqCount = 0;

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        loadSavedData();
        setupEventListeners();
        addMilestoneClickHandlers();
    });

    function loadSavedData() {
        const savedData = localStorage.getItem('gigDescriptionData');
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                document.getElementById('gigDescription').value = data.description || '';
                updateCharCounter();
                
                // Load FAQ items
                if (data.faqs && Array.isArray(data.faqs)) {
                    data.faqs.forEach(faq => {
                        addFAQItem(faq.question, faq.answer);
                    });
                }
            } catch (e) {
                console.log('No saved description data');
            }
        }
    }

    function setupEventListeners() {
        const descriptionTextarea = document.getElementById('gigDescription');
        descriptionTextarea.addEventListener('input', updateCharCounter);
    }

    function updateCharCounter() {
        const textarea = document.getElementById('gigDescription');
        const charCount = textarea.value.length;
        const counter = document.getElementById('charCount');
        
        counter.textContent = charCount;

        // Change color based on character count
        const counterElement = counter.parentElement;
        if (charCount >= 1100) {
            counterElement.classList.add('limit-reached');
            counterElement.classList.remove('warning');
        } else if (charCount >= 900) {
            counterElement.classList.add('warning');
            counterElement.classList.remove('limit-reached');
        } else {
            counterElement.classList.remove('warning', 'limit-reached');
        }
    }

    function addFAQItem(question = '', answer = '') {
        const container = document.getElementById('faqContainer');
        const itemId = `faq-${faqCount}`;
        faqCount++;

        const faqItem = document.createElement('div');
        faqItem.className = 'faq-item';
        faqItem.id = itemId;
        faqItem.innerHTML = `
            <div class="faq-item-header">
                <div class="faq-item-label">FAQ Item #${faqCount}</div>
                <button type="button" class="faq-item-remove" onclick="removeFAQItem('${itemId}');">Remove</button>
            </div>
            <div class="faq-item-inputs">
                <input type="text" class="faq-question" placeholder="Question" value="${question}" maxlength="200">
                <textarea class="faq-answer" placeholder="Answer" maxlength="500">${answer}</textarea>
            </div>
        `;

        container.appendChild(faqItem);
    }

    function removeFAQItem(itemId) {
        const item = document.getElementById(itemId);
        if (item) {
            item.remove();
        }
    }

    function addMilestoneClickHandlers() {
        const completedSteps = document.querySelectorAll('.milestone-step.completed.clickable');
        completedSteps.forEach(step => {
            step.addEventListener('click', function() {
                const stepKey = this.getAttribute('data-step');
                if (stepPages[stepKey]) {
                    saveDescriptionData();
                    window.location.href = stepPages[stepKey];
                }
            });
        });
    }

    function saveDescriptionData() {
        const faqs = [];
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question').value;
            const answer = item.querySelector('.faq-answer').value;
            
            if (question || answer) {
                faqs.push({ question, answer });
            }
        });

        const descriptionData = {
            description: document.getElementById('gigDescription').value,
            faqs: faqs
        };
        
        localStorage.setItem('gigDescriptionData', JSON.stringify(descriptionData));
    }

    function validateAndContinue() {
        const form = document.getElementById('descriptionForm');
        
        if (!form.checkValidity()) {
            alert('Please fill in the required fields');
            form.reportValidity();
            return;
        }

        const description = document.getElementById('gigDescription').value;
        if (description.length === 0) {
            alert('Please enter a description for your gig');
            return;
        }

        saveDescriptionData();

        // Mark description as completed
        const descriptionStep = document.querySelector('[data-step="description"]');
        descriptionStep.classList.remove('active');
        descriptionStep.classList.add('completed');

        // Mark requirements as active
        const requirementsStep = document.querySelector('[data-step="requirements"]');
        requirementsStep.classList.add('active');

        // Redirect to requirements page
        window.location.href = 'gig_requirements.php';
    }

    function goToPreviousStep() {
        saveDescriptionData();
        window.location.href = 'gig_price.php';
    }
</script>

<?php include '../../_foot.php'; ?>