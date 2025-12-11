<?php
session_start();

// only freelancers can edit gigs
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: /index.php');
    exit();
}

// Check if user is deleted
require_once '../checkUserStatus.php';

require_once '../config.php';

// Get gig ID from URL
$gigID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$freelancerID = $_SESSION['user_id'];

if (!$gigID) {
    $_SESSION['error'] = 'Invalid gig ID.';
    header('Location: my_gig.php');
    exit();
}

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

// Media upload configuration
$MAX_IMAGES = 3;
$MAX_IMAGE_SIZE_BYTES = 10 * 1024 * 1024; // 10MB
$MAX_VIDEO_SIZE_BYTES = 50 * 1024 * 1024; // 50MB
$imageMimeMap = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp'
];
$videoMimeMap = [
    'video/mp4' => 'mp4',
    'video/quicktime' => 'mov',
    'video/x-msvideo' => 'avi',
    'video/webm' => 'webm',
    'video/x-m4v' => 'm4v',
    'video/mpeg' => 'mpeg'
];

// Setup upload directories
$imagesRoot = realpath(__DIR__ . '/../../images');
if ($imagesRoot === false) {
    $imagesRoot = __DIR__ . '/../../images';
    if (!is_dir($imagesRoot)) {
        mkdir($imagesRoot, 0755, true);
    }
}

$galleryUploadDir = rtrim($imagesRoot, '/\\') . DIRECTORY_SEPARATOR . 'gig_media' . DIRECTORY_SEPARATOR;
if (!is_dir($galleryUploadDir)) {
    mkdir($galleryUploadDir, 0755, true);
}

// Fetch gig details - verify it belongs to this freelancer
try {
    $stmt = $pdo->prepare("SELECT * FROM gig WHERE GigID = :gig_id AND FreelancerID = :freelancer_id AND Status = 'active'");
    $stmt->execute([
        ':gig_id' => $gigID,
        ':freelancer_id' => $freelancerID
    ]);
    $gig = $stmt->fetch();

    if (!$gig) {
        $_SESSION['error'] = 'Gig not found or you do not have permission to edit it.';
        header('Location: my_gig.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error.';
    error_log('[edit_gig] Fetch failed: ' . $e->getMessage());
    header('Location: my_gig.php');
    exit();
}

// Handle form submission - BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $deliveryTime = intval($_POST['delivery_time'] ?? 0);
    $rushDelivery = intval($_POST['rush_delivery'] ?? 0);
    $rushDeliveryPrice = floatval($_POST['rush_delivery_price'] ?? 0);
    $revisionCount = intval($_POST['revision_count'] ?? 0);
    $additionalRevisionPrice = floatval($_POST['additional_revision_price'] ?? 0);

    // Validation
    if (empty($title) || empty($description) || $price <= 0 || $deliveryTime <= 0) {
        $_SESSION['error'] = 'Please fill in all required fields.';
    } else {
        // Handle media updates
        $image1Path = $gig['Image1Path'];
        $image2Path = $gig['Image2Path'];
        $image3Path = $gig['Image3Path'];
        $videoPath = $gig['VideoPath'];
        
        // Process image deletions
        for ($i = 1; $i <= 3; $i++) {
            if (isset($_POST["delete_image{$i}"]) && $_POST["delete_image{$i}"] === '1') {
                $oldPath = $gig["Image{$i}Path"];
                if ($oldPath) {
                    $oldFile = $galleryUploadDir . basename($oldPath);
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }
                ${"image{$i}Path"} = null;
            }
        }
        
        // Process video deletion
        if (isset($_POST['delete_video']) && $_POST['delete_video'] === '1') {
            $oldVideoPath = $gig['VideoPath'];
            if ($oldVideoPath) {
                $oldVideoFile = $galleryUploadDir . basename($oldVideoPath);
                if (is_file($oldVideoFile)) {
                    @unlink($oldVideoFile);
                }
            }
            $videoPath = null;
        }
        
        // Process new image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $imageSlots = [];
            if (!$image1Path) $imageSlots[] = 1;
            if (!$image2Path) $imageSlots[] = 2;
            if (!$image3Path) $imageSlots[] = 3;
            
            foreach ($_FILES['images']['tmp_name'] as $idx => $tmpName) {
                if (empty($tmpName) || !is_uploaded_file($tmpName)) continue;
                if (empty($imageSlots)) break;
                
                $slot = array_shift($imageSlots);
                $fileSize = $_FILES['images']['size'][$idx] ?? 0;
                $fileMime = mime_content_type($tmpName);
                
                if ($fileSize > $MAX_IMAGE_SIZE_BYTES) {
                    $_SESSION['error'] = 'Image file too large (max 10MB).';
                    continue;
                }
                
                if (!isset($imageMimeMap[$fileMime])) {
                    $_SESSION['error'] = 'Invalid image format.';
                    continue;
                }
                
                $ext = $imageMimeMap[$fileMime];
                $uniqueName = 'gig_' . $gigID . '_img' . $slot . '_' . time() . '.' . $ext;
                $destination = $galleryUploadDir . $uniqueName;
                
                if (move_uploaded_file($tmpName, $destination)) {
                    ${"image{$slot}Path"} = '/images/gig_media/' . $uniqueName;
                }
            }
        }
        
        // Process new video upload
        if (!empty($_FILES['video']['tmp_name']) && is_uploaded_file($_FILES['video']['tmp_name'])) {
            $tmpName = $_FILES['video']['tmp_name'];
            $fileSize = $_FILES['video']['size'];
            $fileMime = mime_content_type($tmpName);
            
            if ($fileSize > $MAX_VIDEO_SIZE_BYTES) {
                $_SESSION['error'] = 'Video file too large (max 50MB).';
            } elseif (!isset($videoMimeMap[$fileMime])) {
                $_SESSION['error'] = 'Invalid video format.';
            } else {
                // Delete old video if exists
                if ($videoPath) {
                    $oldVideoFile = $galleryUploadDir . basename($videoPath);
                    if (is_file($oldVideoFile)) {
                        @unlink($oldVideoFile);
                    }
                }
                
                $ext = $videoMimeMap[$fileMime];
                $uniqueName = 'gig_' . $gigID . '_video_' . time() . '.' . $ext;
                $destination = $galleryUploadDir . $uniqueName;
                
                if (move_uploaded_file($tmpName, $destination)) {
                    $videoPath = '/images/gig_media/' . $uniqueName;
                }
            }
        }

        try {
            $stmt = $pdo->prepare("UPDATE gig SET 
                Title = :title,
                Description = :description,
                Price = :price,
                DeliveryTime = :delivery_time,
                RushDelivery = :rush_delivery,
                RushDeliveryPrice = :rush_delivery_price,
                RevisionCount = :revision_count,
                AdditionalRevision = :additional_revision_price,
                Image1Path = :image1_path,
                Image2Path = :image2_path,
                Image3Path = :image3_path,
                VideoPath = :video_path,
                UpdatedAt = NOW()
                WHERE GigID = :gig_id AND FreelancerID = :freelancer_id");

            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':price' => $price,
                ':delivery_time' => $deliveryTime,
                ':rush_delivery' => $rushDelivery,
                ':rush_delivery_price' => $rushDeliveryPrice,
                ':revision_count' => $revisionCount,
                ':additional_revision_price' => $additionalRevisionPrice,
                ':image1_path' => $image1Path,
                ':image2_path' => $image2Path,
                ':image3_path' => $image3Path,
                ':video_path' => $videoPath,
                ':gig_id' => $gigID,
                ':freelancer_id' => $freelancerID
            ]);

            $_SESSION['success'] = 'Gig updated successfully!';
            header('Location: my_gig.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to update gig. Please try again.';
            error_log('[edit_gig] Update failed: ' . $e->getMessage());
        }
    }
}

// NOW output HTML
$_title = 'Edit Gig';
include '../../_head.php';

?>

<div class="container">
    <div class="breadcrumb">
        <a href="my_gig.php">← Back to My Gigs</a>
    </div>

    <h1 class="page-title">Edit Gig</h1>

    <div class="form-container">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="edit-gig-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Gig Title *</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($gig['Title']) ?>" required maxlength="200">
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="8" required><?= htmlspecialchars($gig['Description']) ?></textarea>
            </div>

            <!-- Images Section -->
            <div class="form-section">
                <h3>Images</h3>
                <p class="section-description">Upload up to 3 images for your gig (max 10MB each)</p>
                
                <div class="media-grid">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <?php $imagePath = $gig["Image{$i}Path"]; ?>
                        <div class="media-item">
                            <div class="media-preview">
                                <?php if ($imagePath): ?>
                                    <img src="<?= htmlspecialchars($imagePath) ?>" alt="Gig Image <?= $i ?>">
                                    <div class="media-overlay">
                                        <label class="delete-checkbox">
                                            <input type="checkbox" name="delete_image<?= $i ?>" value="1" onchange="toggleImageDelete(<?= $i ?>, this.checked)">
                                            <span>Delete</span>
                                        </label>
                                    </div>
                                <?php else: ?>
                                    <div class="media-placeholder">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                            <circle cx="8.5" cy="8.5" r="1.5"/>
                                            <polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                        <p>No image</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <label class="media-label">Image <?= $i ?></label>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="form-group">
                    <label>Add New Images</label>
                    <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple style="display: none;">
                    <div class="upload-box" id="imageUploadBox" onclick="handleImageUploadClick()">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <p class="upload-text">Click to upload images</p>
                        <p class="upload-hint">JPEG, PNG, GIF, WEBP (max 10MB each)</p>
                    </div>
                    <div id="imageFileNames" class="file-names"></div>
                </div>
            </div>

            <!-- Video Section -->
            <div class="form-section">
                <h3>Video</h3>
                <p class="section-description">Optional: Add a video to showcase your gig (max 50MB)</p>
                
                <?php if ($gig['VideoPath']): ?>
                    <div class="video-preview-container">
                        <video controls class="video-preview">
                            <source src="<?= htmlspecialchars($gig['VideoPath']) ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div class="video-delete">
                            <label class="delete-checkbox">
                                <input type="checkbox" name="delete_video" value="1" onchange="toggleVideoDelete(this.checked)">
                                <span>Delete Video</span>
                            </label>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-video-message">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="23 7 16 12 23 17 23 7"/>
                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                        </svg>
                        <p>No video uploaded</p>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Upload New Video</label>
                    <input type="file" id="video" name="video" accept="video/mp4,video/quicktime,video/webm" style="display: none;">
                    <div class="upload-box" onclick="document.getElementById('video').click()">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <p class="upload-text">Click to upload video</p>
                        <p class="upload-hint">MP4, MOV, WEBM, AVI (max 50MB)</p>
                    </div>
                    <div id="videoFileName" class="file-names"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price (RM) *</label>
                    <input type="number" id="price" name="price" value="<?= htmlspecialchars($gig['Price']) ?>" min="1" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="delivery_time">Delivery Time (Days) *</label>
                    <input type="number" id="delivery_time" name="delivery_time" value="<?= htmlspecialchars($gig['DeliveryTime']) ?>" min="1" step="1" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="rush_delivery">Rush Delivery (Days)</label>
                    <input type="number" id="rush_delivery" name="rush_delivery" value="<?= htmlspecialchars($gig['RushDelivery'] ?? '') ?>" min="1" step="1">
                    <small class="form-hint">Optional: Faster delivery option</small>
                </div>

                <div class="form-group">
                    <label for="rush_delivery_price">Rush Delivery Price (RM)</label>
                    <input type="number" id="rush_delivery_price" name="rush_delivery_price" value="<?= htmlspecialchars($gig['RushDeliveryPrice'] ?? '') ?>" min="0" step="0.01">
                    <small class="form-hint">Additional charge for rush delivery</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="revision_count">Number of Revisions</label>
                    <input type="number" id="revision_count" name="revision_count" value="<?= htmlspecialchars($gig['RevisionCount'] ?? 0) ?>" min="0" step="1">
                    <small class="form-hint">How many revisions are included</small>
                </div>

                <div class="form-group">
                    <label for="additional_revision_price">Additional Revision Price (RM)</label>
                    <input type="number" id="additional_revision_price" name="additional_revision_price" value="<?= htmlspecialchars($gig['AdditionalRevision'] ?? 0) ?>" min="0" step="0.01">
                    <small class="form-hint">Price per extra revision beyond included count</small>
                </div>
            </div>

            <div class="form-actions">
                <a href="my_gig.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Update Gig</button>
            </div>
        </form>
    </div>
</div>

<style>
    .container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }

    .breadcrumb {
        margin-bottom: 20px;
    }

    .breadcrumb a {
        color: #666;
        text-decoration: none;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: color 0.3s;
    }

    .breadcrumb a:hover {
        color: rgb(159, 232, 112);
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 20px 0 30px 0;
    }

    .form-container {
        background: white;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-success::before {
        content: "✓";
        font-weight: 700;
        font-size: 1.2rem;
    }

    .alert-error {
        background: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }

    .alert-error::before {
        content: "✕";
        font-weight: 700;
        font-size: 1.2rem;
    }

    .edit-gig-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-group input,
    .form-group textarea {
        padding: 12px 16px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: rgb(159, 232, 112);
        box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-hint {
        font-size: 0.85rem;
        color: #666;
        margin-top: 5px;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        justify-content: flex-end;
    }

    .btn-cancel,
    .btn-submit {
        padding: 14px 32px;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        text-decoration: none;
        display: inline-block;
    }

    .btn-cancel {
        background: #f8f9fa;
        color: #2c3e50;
        border: 2px solid #e9ecef;
    }

    .btn-cancel:hover {
        background: #fff;
        border-color: #ddd;
    }

    .btn-submit {
        background: rgb(159, 232, 112);
        color: #2c3e50;
    }

    .btn-submit:hover {
        background: rgb(140, 210, 90);
        box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
        transform: translateY(-2px);
    }

    .form-section {
        margin: 30px 0;
        padding: 25px;
        background: #f8f9fa;
        border-radius: 12px;
    }

    .form-section h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 8px 0;
    }

    .section-description {
        color: #666;
        font-size: 0.9rem;
        margin: 0 0 20px 0;
    }

    .media-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .media-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .media-preview {
        position: relative;
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
        background: white;
        border: 2px solid #e9ecef;
    }

    .media-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .media-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #999;
    }

    .media-placeholder svg {
        margin-bottom: 8px;
    }

    .media-placeholder p {
        margin: 0;
        font-size: 0.85rem;
    }

    .media-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .media-preview:hover .media-overlay {
        opacity: 1;
    }

    .delete-checkbox {
        display: flex;
        align-items: center;
        gap: 8px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        padding: 8px 16px;
        background: rgba(220, 53, 69, 0.9);
        border-radius: 6px;
        transition: background 0.3s;
    }

    .delete-checkbox:hover {
        background: rgba(220, 53, 69, 1);
    }

    .delete-checkbox input[type="checkbox"] {
        width: auto;
        margin: 0;
        cursor: pointer;
    }

    .media-label {
        text-align: center;
        font-size: 0.85rem;
        color: #666;
        font-weight: 500;
    }

    .video-preview-container {
        margin-bottom: 20px;
    }

    .video-preview {
        width: 100%;
        max-width: 600px;
        border-radius: 8px;
        border: 2px solid #e9ecef;
        margin-bottom: 15px;
    }

    .video-delete {
        display: flex;
        justify-content: flex-start;
    }

    .video-delete .delete-checkbox {
        color: #2c3e50;
        background: #fff;
        border: 2px solid #dc3545;
    }

    .video-delete .delete-checkbox:hover {
        background: #dc3545;
        color: white;
    }

    .no-video-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px;
        background: white;
        border-radius: 8px;
        border: 2px dashed #e9ecef;
        color: #999;
        margin-bottom: 20px;
    }

    .no-video-message svg {
        margin-bottom: 12px;
    }

    .no-video-message p {
        margin: 0;
        font-size: 0.95rem;
    }

    .upload-box {
        padding: 40px 20px;
        border: 2px dashed #ddd;
        border-radius: 12px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #999;
    }

    .upload-box:hover {
        border-color: rgb(159, 232, 112);
        background: rgba(159, 232, 112, 0.05);
        color: rgb(159, 232, 112);
    }

    .upload-box.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
        background: #f5f5f5;
    }

    .upload-box.disabled .upload-text {
        color: #999;
    }

    .upload-box svg {
        margin-bottom: 12px;
        transition: transform 0.3s ease;
    }

    .upload-box:hover svg {
        transform: translateY(-5px);
    }

    .upload-text {
        margin: 0 0 8px 0;
        font-size: 1rem;
        font-weight: 600;
        color: #2c3e50;
    }

    .upload-hint {
        margin: 0;
        font-size: 0.85rem;
        color: #999;
    }

    .file-names {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        font-size: 0.9rem;
        color: #2c3e50;
        display: none;
    }

    .file-names.show {
        display: block;
    }

    .file-names p {
        margin: 5px 0;
        padding: 5px 10px;
        background: white;
        border-radius: 6px;
        border-left: 3px solid rgb(159, 232, 112);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .file-names p .file-name-text {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-names p .remove-file-btn {
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
        line-height: 1;
        flex-shrink: 0;
        transition: background 0.2s;
    }

    .file-names p .remove-file-btn:hover {
        background: #bb2d3b;
    }

    @media (max-width: 768px) {
        .form-container {
            padding: 25px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-cancel,
        .btn-submit {
            width: 100%;
        }

        .media-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    // Track active images
    let activeImageCount = 0;
    let selectedFiles = []; // Store selected files as DataTransfer

    function countActiveImages() {
        activeImageCount = 0;
        const deleteCheckboxes = document.querySelectorAll('input[name^="delete_image"]');
        deleteCheckboxes.forEach((checkbox, index) => {
            const imageNum = index + 1;
            const hasImage = <?php echo json_encode([
                1 => !empty($gig['Image1Path']),
                2 => !empty($gig['Image2Path']),
                3 => !empty($gig['Image3Path'])
            ]); ?>[imageNum];
            
            if (hasImage && !checkbox.checked) {
                activeImageCount++;
            }
        });
        
        updateUploadBoxState();
    }

    function updateUploadBoxState() {
        const uploadBox = document.getElementById('imageUploadBox');
        const uploadText = uploadBox.querySelector('.upload-text');
        
        if (activeImageCount >= 3) {
            uploadBox.classList.add('disabled');
            uploadText.textContent = 'Maximum 3 images reached';
            uploadBox.querySelector('.upload-hint').textContent = 'Delete an image to upload new ones';
        } else {
            uploadBox.classList.remove('disabled');
            uploadText.textContent = 'Click to upload images';
            uploadBox.querySelector('.upload-hint').textContent = 'JPEG, PNG, GIF, WEBP (max 10MB each)';
        }
    }

    function handleImageUploadClick() {
        if (activeImageCount < 3) {
            document.getElementById('images').click();
        }
    }

    function removeSelectedFile(index) {
        // Check if this would leave us with no images at all
        if (selectedFiles.length === 1 && activeImageCount === 0) {
            alert('At least one image is required for your gig.');
            return;
        }
        
        // Remove file from selectedFiles array
        selectedFiles.splice(index, 1);
        
        // Update the file input with remaining files
        const dt = new DataTransfer();
        selectedFiles.forEach(file => {
            dt.items.add(file);
        });
        document.getElementById('images').files = dt.files;
        
        // Re-render the file list
        renderSelectedFiles();
    }

    function renderSelectedFiles() {
        const fileNamesDiv = document.getElementById('imageFileNames');
        
        if (selectedFiles.length > 0) {
            fileNamesDiv.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const p = document.createElement('p');
                p.innerHTML = `
                    <span class="file-name-text">${file.name}</span>
                    <button type="button" class="remove-file-btn" onclick="removeSelectedFile(${index})" title="Remove file">×</button>
                `;
                fileNamesDiv.appendChild(p);
            });
            fileNamesDiv.classList.add('show');
        } else {
            fileNamesDiv.classList.remove('show');
        }
    }

    function toggleImageDelete(imageNum, isChecked) {
        // Count how many images would remain after this change
        const deleteCheckboxes = document.querySelectorAll('input[name^="delete_image"]');
        let remainingCount = 0;
        
        deleteCheckboxes.forEach((checkbox, index) => {
            const num = index + 1;
            const hasImage = <?php echo json_encode([
                1 => !empty($gig['Image1Path']),
                2 => !empty($gig['Image2Path']),
                3 => !empty($gig['Image3Path'])
            ]); ?>[num];
            
            // For this checkbox, use the new state; for others, use current state
            const willBeDeleted = (num === imageNum) ? isChecked : checkbox.checked;
            
            if (hasImage && !willBeDeleted) {
                remainingCount++;
            }
        });
        
        // If this would delete the last image and no new images are selected, prevent it
        if (remainingCount === 0 && selectedFiles.length === 0) {
            alert('At least one image is required for your gig.');
            event.target.checked = false;
            return;
        }
        
        const mediaItem = event.target.closest('.media-item');
        if (isChecked) {
            mediaItem.style.opacity = '0.5';
        } else {
            mediaItem.style.opacity = '1';
        }
        
        // Recount active images
        countActiveImages();
    }

    function toggleVideoDelete(isChecked) {
        const videoContainer = document.querySelector('.video-preview-container');
        if (videoContainer) {
            if (isChecked) {
                videoContainer.style.opacity = '0.5';
            } else {
                videoContainer.style.opacity = '1';
            }
        }
    }

    // Handle image file selection display
    document.getElementById('images').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        if (files.length > 0) {
            // Check available slots
            const availableSlots = 3 - activeImageCount;
            
            if (files.length > availableSlots) {
                alert(`You can only upload ${availableSlots} more image${availableSlots > 1 ? 's' : ''}. Please select fewer files.`);
                this.value = ''; // Clear the file input
                selectedFiles = [];
                renderSelectedFiles();
                return;
            }
            
            // Store selected files
            selectedFiles = files;
            renderSelectedFiles();
        } else {
            selectedFiles = [];
            renderSelectedFiles();
        }
    });

    // Handle video file selection display
    document.getElementById('video').addEventListener('change', function(e) {
        const fileNameDiv = document.getElementById('videoFileName');
        const file = e.target.files[0];
        
        if (file) {
            fileNameDiv.innerHTML = '<p>' + file.name + '</p>';
            fileNameDiv.classList.add('show');
        } else {
            fileNameDiv.classList.remove('show');
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        countActiveImages();
    });
</script>

<?php include '../../_foot.php'; ?>