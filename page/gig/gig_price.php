<?php
session_start();

// only freelancers can create gigs
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: /index.php');
    exit();
}

$_title = 'Create Gig - Pricing';
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
        min-height: 80px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-row-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
    }

    .form-description {
        font-size: 0.85rem;
        color: #999;
        margin-top: 6px;
    }

    /* Price Range Display */
    .price-range-display {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        margin-top: 15px;
    }

    .price-range-text {
        font-weight: 600;
        color: rgb(159, 232, 112);
        font-size: 1.1rem;
    }

    .price-range-text span {
        font-weight: 700;
    }

    /* Input Number Spinner */
    input[type="number"] {
        font-size: 0.95rem;
    }

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: inner-spin-button !important;
        opacity: 1;
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

        .form-row,
        .form-row-3 {
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

            <div class="milestone-step active" data-step="pricing">
                <div class="milestone-circle">2</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Pricing</div>
                </div>
            </div>

            <div class="milestone-separator">›</div>

            <div class="milestone-step" data-step="description">
                <div class="milestone-circle">3</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Description</div>
                </div>
            </div>

            <div class="milestone-separator">›</div>

            <div class="milestone-step" data-step="gallery">
                <div class="milestone-circle">4</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Gallery</div>
                </div>
            </div>

            <div class="milestone-separator">›</div>

            <div class="milestone-step" data-step="publish">
                <div class="milestone-circle">5</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Publish</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="gig-form-container">
        <form id="pricingForm" method="POST" action="">
            <!-- Price Range Section -->
            <div class="gig-form-section">
                <h3>💰 Price Range</h3>
                <p class="form-description">Set the minimum and maximum price for your gig. Buyers can choose any price within this range.</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="minPrice">Minimum Price (MYR) *</label>
                        <input type="number" id="minPrice" name="minPrice" placeholder="e.g., 50" min="5" step="1" required>
                        <div class="form-description">Enter whole numbers only (minimum MYR 5)</div>
                    </div>

                    <div class="form-group">
                        <label for="maxPrice">Maximum Price (MYR) *</label>
                        <input type="number" id="maxPrice" name="maxPrice" placeholder="e.g., 500" min="5" step="1" required>
                        <div class="form-description">Whole numbers only</div>
                    </div>
                </div>

                <div class="price-range-display">
                    <div class="price-range-text">
                        Price Range: <span id="displayMinPrice">MYR 0</span> - <span id="displayMaxPrice">MYR 0</span>
                    </div>
                </div>
            </div>

            <!-- Delivery Time Section -->
            <div class="gig-form-section">
                <h3>📅 Delivery Time</h3>
                <p class="form-description">How many days will it take you to complete the gig?</p>
                
                <div class="form-row-3">
                    <div class="form-group">
                        <label for="deliveryDays">Days to Deliver *</label>
                        <input type="number" id="deliveryDays" name="deliveryDays" placeholder="e.g., 3" min="1" max="90" required>
                        <div class="form-description">Between 1-90 days</div>
                    </div>

                    <div class="form-group">
                        <label for="standardDays">Standard Delivery (Days) *</label>
                        <select id="standardDays" name="standardDays" required>
                            <option value="" disabled selected hidden>Select</option>
                            <option value="1">1 Day</option>
                            <option value="3">3 Days</option>
                            <option value="7">7 Days</option>
                            <option value="14">14 Days</option>
                            <option value="30">30 Days</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="rushDeliveryDays">Rush Delivery (Days)</label>
                        <select id="rushDeliveryDays" name="rushDeliveryDays">
                            <option value="" selected hidden>Optional</option>
                            <option value="1">1 Day</option>
                            <option value="2">2 Days</option>
                            <option value="3">3 Days</option>
                            <option value="5">5 Days</option>
                            <option value="7">7 Days</option>
                        </select>
                        <div class="form-description">Faster delivery with additional cost</div>
                    </div>
                </div>
            </div>

            <!-- Revisions Section -->
            <div class="gig-form-section">
                <h3>🔄 Revisions</h3>
                <p class="form-description">How many revisions will you include with your gig?</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="revisions">Number of Revisions *</label>
                        <select id="revisions" name="revisions" required>
                            <option value="" disabled selected hidden>Select</option>
                            <option value="1">1 Revision</option>
                            <option value="2">2 Revisions</option>
                            <option value="3">3 Revisions</option>
                            <option value="5">5 Revisions</option>
                            <option value="10">10 Revisions</option>
                            <option value="unlimited">Unlimited Revisions</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="additionalRevisionPrice">Price per Additional Revision (MYR)</label>
                        <input type="number" id="additionalRevisionPrice" name="additionalRevisionPrice" placeholder="e.g., 10" min="0" step="1">
                        <div class="form-description">Optional, whole numbers only</div>
                    </div>
                </div>
            </div>

            <!-- Summary Section -->
            <div class="gig-form-section">
                <h3>📋 Summary</h3>
                <div class="price-range-display">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 0.95rem;">
                        <div>
                            <strong>Base Price Range:</strong><br>
                            <span id="summaryPrice">MYR 0 - MYR 0</span>
                        </div>
                        <div>
                            <strong>Delivery Time:</strong><br>
                            <span id="summaryDelivery">-</span>
                        </div>
                        <div>
                            <strong>Revisions Included:</strong><br>
                            <span id="summaryRevisions">-</span>
                        </div>
                        <div>
                            <strong>Rush Delivery Option:</strong><br>
                            <span id="summaryRush">Not available</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="goToPreviousStep();">Back to Overview</button>
                <button type="button" class="btn btn-primary" onclick="validateAndContinue();">Continue to Description</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Step pages mapping (requirements removed)
    const stepPages = {
        'overview': 'create_gig.php',
        'pricing': 'gig_price.php',
        'description': 'gig_description.php',
        'gallery': 'gig_gallery.php',
        'publish': 'gig_publish.php'
    };

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        loadSavedData();
        setupEventListeners();
        addMilestoneClickHandlers();
    });

    function loadSavedData() {
        const savedData = localStorage.getItem('gigPricingData');
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                document.getElementById('minPrice').value = data.minPrice || '';
                document.getElementById('maxPrice').value = data.maxPrice || '';
                document.getElementById('deliveryDays').value = data.deliveryDays || '';
                document.getElementById('standardDays').value = data.standardDays || '';
                document.getElementById('rushDeliveryDays').value = data.rushDeliveryDays || '';
                document.getElementById('revisions').value = data.revisions || '';
                document.getElementById('additionalRevisionPrice').value = data.additionalRevisionPrice || '';
                updateSummary();
            } catch (e) {
                console.log('No saved pricing data');
            }
        }
    }

    function setupEventListeners() {
        const minPriceInput = document.getElementById('minPrice');
        const maxPriceInput = document.getElementById('maxPrice');
        const deliveryDaysInput = document.getElementById('deliveryDays');
        const standardDaysSelect = document.getElementById('standardDays');
        const rushDeliverySelect = document.getElementById('rushDeliveryDays');
        const revisionsSelect = document.getElementById('revisions');

        minPriceInput.addEventListener('input', updateSummary);
        maxPriceInput.addEventListener('input', updateSummary);
        deliveryDaysInput.addEventListener('input', updateSummary);
        standardDaysSelect.addEventListener('change', updateSummary);
        rushDeliverySelect.addEventListener('change', updateSummary);
        revisionsSelect.addEventListener('change', updateSummary);
    }

    function updateSummary() {
        const minPrice = parseInt(document.getElementById('minPrice').value, 10) || 0;
        const maxPrice = parseInt(document.getElementById('maxPrice').value, 10) || 0;
        const deliveryDays = document.getElementById('deliveryDays').value || '-';
        const standardDays = document.getElementById('standardDays').value || '-';
        const rushDeliveryDays = document.getElementById('rushDeliveryDays').value || '-';
        const revisions = document.getElementById('revisions').value || '-';
        const additionalRevision = parseInt(document.getElementById('additionalRevisionPrice').value, 10) || 0;

        // Update price range display
        document.getElementById('displayMinPrice').textContent = 'MYR ' + minPrice;
        document.getElementById('displayMaxPrice').textContent = 'MYR ' + maxPrice;

        // Update summary
        document.getElementById('summaryPrice').textContent = `MYR ${minPrice} - MYR ${maxPrice}`;
        document.getElementById('summaryDelivery').textContent = standardDays === '-' ? '-' : standardDays;
        document.getElementById('summaryRevisions').textContent = revisions === '-' ? '-' : (revisions === 'unlimited' ? 'Unlimited' : revisions);
        document.getElementById('summaryRush').textContent = rushDeliveryDays === '-' ? 'Not available' : rushDeliveryDays;
        document.getElementById('summaryAdditionalRevision').textContent = additionalRevision ? `MYR ${additionalRevision}` : 'Not set';
    }

    function addMilestoneClickHandlers() {
        const completedSteps = document.querySelectorAll('.milestone-step.completed.clickable');
        completedSteps.forEach(step => {
            step.addEventListener('click', function() {
                const stepKey = this.getAttribute('data-step');
                if (stepPages[stepKey]) {
                    savePricingData();
                    window.location.href = stepPages[stepKey];
                }
            });
        });
    }

    function savePricingData() {
        const pricingData = {
            minPrice: document.getElementById('minPrice').value,
            maxPrice: document.getElementById('maxPrice').value,
            deliveryDays: document.getElementById('deliveryDays').value,
            standardDays: document.getElementById('standardDays').value,
            rushDeliveryDays: document.getElementById('rushDeliveryDays').value,
            revisions: document.getElementById('revisions').value,
            additionalRevisionPrice: document.getElementById('additionalRevisionPrice').value
        };
        localStorage.setItem('gigPricingData', JSON.stringify(pricingData));
    }

    function goToPreviousStep() {
        savePricingData();
        window.location.href = 'create_gig.php';
    }

    function validateAndContinue() {
        const form = document.getElementById('pricingForm');
        if (!form.checkValidity()) {
            alert('Please fill in all required fields');
            form.reportValidity();
            return;
        }

        const minPrice = parseInt(document.getElementById('minPrice').value, 10);
        const maxPrice = parseInt(document.getElementById('maxPrice').value, 10);
        if (minPrice >= maxPrice) {
            alert('Maximum price must be greater than minimum price');
            return;
        }

        savePricingData();

        // Mark pricing as completed, move to description
        const pricingStep = document.querySelector('[data-step="pricing"]');
        pricingStep.classList.remove('active');
        pricingStep.classList.add('completed');

        const descriptionStep = document.querySelector('[data-step="description"]');
        descriptionStep.classList.add('active');

        window.location.href = 'gig_description.php';
    }
</script>
<?php include '../../_foot.php'; ?>