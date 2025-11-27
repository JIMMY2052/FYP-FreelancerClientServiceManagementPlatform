<?php
session_start();

// only freelancers can create gigs
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: /index.php');
    exit();
}

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

$_title = 'Create Gig - Publish';
include '../../_head.php';

$errors = [];
$successMessage = '';
$gallerySessionKey = 'gig_gallery';
$galleryState = $_SESSION[$gallerySessionKey] ?? ['images' => [], 'video' => null];
$galleryImages = $galleryState['images'] ?? [];
$galleryVideo = $galleryState['video'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gigTitle = trim($_POST['gigTitle'] ?? '');
    $gigCategory = trim($_POST['gigCategory'] ?? '');
    $gigSubcategory = trim($_POST['gigSubcategory'] ?? '');
    $searchTags = trim($_POST['searchTags'] ?? '');
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $deliveryDays = intval($_POST['deliveryDays'] ?? 0);
    $standardDays = intval($_POST['standardDays'] ?? 0);
    $rushDeliveryDays = trim($_POST['rushDeliveryDays'] ?? '');
    $revisions = trim($_POST['revisions'] ?? '');
    $additionalRevisionPrice = isset($_POST['additionalRevisionPrice']) ? floatval($_POST['additionalRevisionPrice']) : 0;
    $gigDescription = trim($_POST['gigDescription'] ?? '');

    if ($gigTitle === '') {
        $errors[] = 'Gig title is required.';
    }
    if ($gigCategory === '' || $gigSubcategory === '') {
        $errors[] = 'Category and subcategory are required.';
    }
    if ($gigDescription === '') {
        $errors[] = 'Gig description is required.';
    }
    if ($price <= 0) {
        $errors[] = 'Please provide a valid price (must be greater than 0).';
    }
    if ($deliveryDays <= 0 && $standardDays <= 0) {
        $errors[] = 'Please provide a delivery time.';
    }
    if ($revisions === '') {
        $errors[] = 'Please select the number of revisions.';
    }
    if (empty($galleryImages)) {
        $errors[] = 'Please upload at least one gallery image before publishing.';
    }

    if (empty($errors)) {
        $pdo = getPDOConnection();
        $freelancerID = intval($_SESSION['user_id']);
        $deliveryTime = $deliveryDays > 0 ? intval($deliveryDays) : intval($standardDays);
        $revisionCount = ($revisions === 'unlimited') ? null : intval($revisions);
        
        // Extract individual image paths (up to 3)
        $image1Path = isset($galleryImages[0]['path']) ? $galleryImages[0]['path'] : null;
        $image2Path = isset($galleryImages[1]['path']) ? $galleryImages[1]['path'] : null;
        $image3Path = isset($galleryImages[2]['path']) ? $galleryImages[2]['path'] : null;
        $videoPath = isset($galleryVideo['path']) ? $galleryVideo['path'] : null;
        
        $status = 'active';
        $createdAt = date('Y-m-d H:i:s');
        $rushDeliveryValue = ($rushDeliveryDays !== '') ? intval($rushDeliveryDays) : null;
        $additionalRevisionValue = ($additionalRevisionPrice !== '' && $additionalRevisionPrice !== null) ? intval($additionalRevisionPrice) : 0;
        $priceValue = intval($price);

        try {
            $stmt = $pdo->prepare("INSERT INTO gig (FreelancerID, Title, Category, Subcategory, SearchTags, Description, Price, DeliveryTime, RushDelivery, AdditionalRevision, RevisionCount, Image1Path, Image2Path, Image3Path, VideoPath, Status, CreatedAt, UpdatedAt) VALUES (:freelancer_id, :title, :category, :subcategory, :search_tags, :description, :price, :delivery_time, :rush_delivery, :additional_revision, :revision_count, :image1_path, :image2_path, :image3_path, :video_path, :status, :created_at, :updated_at)");
            $stmt->execute([
                ':freelancer_id' => $freelancerID,
                ':title' => $gigTitle,
                ':category' => $gigCategory,
                ':subcategory' => $gigSubcategory,
                ':search_tags' => $searchTags,
                ':description' => $gigDescription,
                ':price' => $priceValue,
                ':delivery_time' => $deliveryTime,
                ':rush_delivery' => $rushDeliveryValue,
                ':additional_revision' => $additionalRevisionValue,
                ':revision_count' => $revisionCount,
                ':image1_path' => $image1Path,
                ':image2_path' => $image2Path,
                ':image3_path' => $image3Path,
                ':video_path' => $videoPath,
                ':status' => $status,
                ':created_at' => $createdAt,
                ':updated_at' => null
            ]);

            unset($_SESSION[$gallerySessionKey]);
            $_SESSION['success'] = 'Your gig has been published successfully.';
            header('Location: /page/gig/my_gig.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Failed to save gig. Database error: ' . $e->getMessage();
            error_log('[gig_summary] PDO insert failed: ' . $e->getMessage());
        }
    }
}

?>

<style>
    .create-gig-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 40px 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #2c3e50;
    }

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

    .milestone-label-wrapper {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .milestone-label {
        font-weight: 700;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    .milestone-step.active .milestone-label {
        color: rgb(159, 232, 112);
    }

    .milestone-step.completed .milestone-label {
        color: rgb(140, 210, 90);
    }

    .milestone-separator {
        flex-shrink: 0;
        color: #ddd;
        font-size: 1.5rem;
        margin: 0 10px;
        font-weight: 300;
    }

    .gig-form-container {
        background: white;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .gig-summary-section {
        margin-bottom: 30px;
    }

    .gig-summary-section h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 16px 0;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
    }

    .summary-card {
        border: 1px solid #e5e9ef;
        border-radius: 12px;
        padding: 16px;
        background: #f8fafc;
    }

    .summary-card strong {
        display: block;
        font-size: 0.9rem;
        color: #5d6d7e;
        margin-bottom: 6px;
    }

    .summary-value {
        font-size: 1rem;
        font-weight: 600;
        color: #2c3e50;
    }

    .description-box {
        border: 1px solid #e5e9ef;
        border-radius: 12px;
        padding: 16px;
        background: #fff;
        min-height: 150px;
        white-space: pre-wrap;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .gallery-preview {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .gallery-preview .preview-item,
    .gallery-preview .preview-video {
        width: 160px;
        height: 120px;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid #e5e9ef;
        background: #fafbfc;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .gallery-preview img,
    .gallery-preview video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

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

    .btn-secondary {
        background: #eee;
        color: #333;
    }

    .btn-primary:hover {
        background: rgb(140, 210, 90);
        box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
    }

    .btn-secondary:hover {
        background: #ddd;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .alert {
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .alert-error {
        background: #fdecea;
        color: #b23c17;
        border: 1px solid #f8c7c1;
    }

    .alert-warning {
        background: #fff4e5;
        color: #8c5b15;
        border: 1px solid #f6d8b5;
    }

    @media (max-width: 768px) {
        .create-gig-container {
            padding: 20px;
        }

        .gig-form-container {
            padding: 20px;
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
            <div class="milestone-step completed clickable" data-step="description">
                <div class="milestone-circle">✓</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Description</div>
                </div>
            </div>
            <div class="milestone-separator">›</div>
            <div class="milestone-step completed clickable" data-step="gallery">
                <div class="milestone-circle">✓</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Gallery</div>
                </div>
            </div>
            <div class="milestone-separator">›</div>
            <div class="milestone-step active" data-step="publish">
                <div class="milestone-circle">5</div>
                <div class="milestone-label-wrapper">
                    <div class="milestone-label">Publish</div>
                </div>
            </div>
        </div>
    </div>

    <div class="gig-form-container">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div id="missingDataNotice" class="alert alert-warning" style="display:none;"></div>

        <form id="publishForm" method="POST" action="gig_summary.php">
            <div class="gig-summary-section">
                <h3>🧾 Overview</h3>
                <div class="summary-grid">
                    <div class="summary-card">
                        <strong>Gig Title</strong>
                        <div class="summary-value" id="summaryTitle">-</div>
                    </div>
                    <div class="summary-card">
                        <strong>Category</strong>
                        <div class="summary-value" id="summaryCategory">-</div>
                    </div>
                    <div class="summary-card">
                        <strong>Subcategory</strong>
                        <div class="summary-value" id="summarySubcategory">-</div>
                    </div>
                    <div class="summary-card">
                        <strong>Search Tags</strong>
                        <div class="summary-value" id="summaryTags">-</div>
                    </div>
                </div>
            </div>

            <div class="gig-summary-section">
                <h3>💰 Pricing & Delivery</h3>
                <div class="summary-grid">
                    <div class="summary-card">
                        <strong>Price</strong>
                        <div class="summary-value" id="summaryPrice">MYR 0.00</div>
                    </div>
                    <div class="summary-card">
                        <strong>Delivery Time</strong>
                        <div class="summary-value" id="summaryDeliveryTime">-</div>
                    </div>
                    <div class="summary-card">
                        <strong>Revisions Included</strong>
                        <div class="summary-value" id="summaryRevisionsIncluded">-</div>
                    </div>
                    <div class="summary-card">
                        <strong>Rush Delivery</strong>
                        <div class="summary-value" id="summaryRushDelivery">Not available</div>
                    </div>
                    <div class="summary-card">
                        <strong>Additional Revision Price</strong>
                        <div class="summary-value" id="summaryAdditionalRevision">MYR 0.00</div>
                    </div>
                </div>
            </div>

            <div class="gig-summary-section">
                <h3>📝 Description</h3>
                <div class="description-box" id="summaryDescription">No description provided yet.</div>
            </div>

            <div class="gig-summary-section">
                <h3>🖼️ Gallery</h3>
                <p class="form-description">Primary image will be used as your gig thumbnail. Make sure everything looks perfect.</p>
                <div class="gallery-preview" id="galleryPreview">
                    <?php if (!empty($galleryImages)): ?>
                        <?php foreach ($galleryImages as $index => $image): ?>
                            <div class="preview-item">
                                <img src="<?php echo htmlspecialchars($image['path']); ?>" alt="<?php echo htmlspecialchars($image['name']); ?>">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No gallery images found. Please go back and upload at least one image.</p>
                    <?php endif; ?>
                </div>
                <?php if (!empty($galleryVideo)): ?>
                    <div style="margin-top: 20px;">
                        <strong>Video Preview</strong>
                        <div class="gallery-preview">
                            <div class="preview-video">
                                <video src="<?php echo htmlspecialchars($galleryVideo['path']); ?>" controls></video>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <input type="hidden" name="gigTitle" id="inputGigTitle">
            <input type="hidden" name="gigCategory" id="inputGigCategory">
            <input type="hidden" name="gigSubcategory" id="inputGigSubcategory">
            <input type="hidden" name="searchTags" id="inputSearchTags">
            <input type="hidden" name="price" id="inputPrice">
            <input type="hidden" name="deliveryDays" id="inputDeliveryDays">
            <input type="hidden" name="standardDays" id="inputStandardDays">
            <input type="hidden" name="rushDeliveryDays" id="inputRushDeliveryDays">
            <input type="hidden" name="revisions" id="inputRevisions">
            <input type="hidden" name="additionalRevisionPrice" id="inputAdditionalRevisionPrice">
            <input type="hidden" name="gigDescription" id="inputGigDescription">

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="goToPreviousStep();">Back to Gallery</button>
                <button type="submit" id="publishBtn" class="btn btn-primary" disabled>Publish Gig</button>
            </div>
        </form>
    </div>
</div>

<script>
    const serverImages = <?php echo json_encode(array_values($galleryImages), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES); ?>;
    const serverVideo = <?php echo json_encode($galleryVideo, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES); ?>;

    const milestonePages = {
        'overview': 'create_gig.php',
        'pricing': 'gig_price.php',
        'description': 'gig_description.php',
        'gallery': 'gig_gallery.php',
        'publish': 'gig_summary.php'
    };

    const publishBtn = document.getElementById('publishBtn');
    const missingNotice = document.getElementById('missingDataNotice');

    document.addEventListener('DOMContentLoaded', function() {
        populateSummary();
        addMilestoneClickHandlers();
    });

    function addMilestoneClickHandlers() {
        const clickableSteps = document.querySelectorAll('.milestone-step.completed.clickable');
        clickableSteps.forEach(step => {
            step.addEventListener('click', function() {
                const stepKey = this.getAttribute('data-step');
                if (milestonePages[stepKey]) {
                    window.location.href = milestonePages[stepKey];
                }
            });
        });
    }

    function goToPreviousStep() {
        window.location.href = 'gig_gallery.php';
    }

    function parseData(key) {
        try {
            const raw = localStorage.getItem(key);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            console.warn('Failed to parse data for key', key);
            return null;
        }
    }

    function populateSummary() {
        const overview = parseData('gigFormData') || {};
        const pricing = parseData('gigPricingData') || {};
        const description = parseData('gigDescriptionData') || {};

        const title = overview.gigTitle || '';
        const category = overview.gigCategory || '';
        const subcategory = overview.gigSubcategory || '';
        const tags = overview.searchTags || '';

        const price = parseInt(pricing.price || 0, 10);
        const deliveryDays = pricing.deliveryDays || '';
        const standardDays = pricing.standardDays || '';
        const rushDays = pricing.rushDeliveryDays || '';
        const revisions = pricing.revisions || '';
        const additionalRevisionPrice = parseInt(pricing.additionalRevisionPrice || 0, 10);
        const descriptionText = description.description || '';

        document.getElementById('summaryTitle').textContent = title || 'No title yet';
        document.getElementById('summaryCategory').textContent = category || '-';
        document.getElementById('summarySubcategory').textContent = subcategory || '-';
        document.getElementById('summaryTags').textContent = tags || 'No tags added';

        document.getElementById('summaryPrice').textContent = `MYR ${price}`;
        document.getElementById('summaryDeliveryTime').textContent = deliveryDays || standardDays || '-';
        document.getElementById('summaryRevisionsIncluded').textContent = revisions ? (revisions === 'unlimited' ? 'Unlimited' : revisions) : '-';
        document.getElementById('summaryRushDelivery').textContent = rushDays ? `${rushDays} day(s)` : 'Not available';
        document.getElementById('summaryAdditionalRevision').textContent = additionalRevisionPrice ? `MYR ${additionalRevisionPrice}` : 'Not set';
        document.getElementById('summaryDescription').textContent = descriptionText || 'No description provided yet.';

        document.getElementById('inputGigTitle').value = title;
        document.getElementById('inputGigCategory').value = category;
        document.getElementById('inputGigSubcategory').value = subcategory;
        document.getElementById('inputSearchTags').value = tags;
        document.getElementById('inputPrice').value = price || '';
        document.getElementById('inputDeliveryDays').value = deliveryDays || '';
        document.getElementById('inputStandardDays').value = standardDays || '';
        document.getElementById('inputRushDeliveryDays').value = rushDays || '';
        document.getElementById('inputRevisions').value = revisions || '';
        document.getElementById('inputAdditionalRevisionPrice').value = additionalRevisionPrice || '';
        document.getElementById('inputGigDescription').value = descriptionText || '';

        const missingFields = [];
        if (!title) missingFields.push('Gig Title');
        if (!category) missingFields.push('Category');
        if (!subcategory) missingFields.push('Subcategory');
        if (!price || price <= 0) missingFields.push('Valid price');
        if (!deliveryDays && !standardDays) missingFields.push('Delivery time');
        if (!revisions) missingFields.push('Revisions');
        if (!descriptionText) missingFields.push('Description');
        if (!serverImages || serverImages.length === 0) missingFields.push('Gallery images');

        if (missingFields.length > 0) {
            missingNotice.style.display = 'block';
            missingNotice.textContent = 'Missing information: ' + missingFields.join(', ') + '. Please review previous steps.';
            publishBtn.disabled = true;
        } else {
            missingNotice.style.display = 'none';
            publishBtn.disabled = false;
        }
    }
</script>

<?php include '../../_foot.php'; ?>

