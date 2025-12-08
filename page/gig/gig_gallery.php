<?php
session_start();

// only freelancers can create gigs
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: /index.php');
    exit();
}

$gallerySessionKey = 'gig_gallery';
$galleryState = $_SESSION[$gallerySessionKey] ?? ['images' => [], 'video' => null];
$storedImages = $galleryState['images'] ?? [];
$storedVideo = $galleryState['video'] ?? null;

$MAX_IMAGES = 3;
$MIN_IMAGES = 1;
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

$imagesErrorMessage = '';
$videoErrorMessage = '';

// Use /images folder instead of /uploads
$imagesRoot = realpath(__DIR__ . '/../../images');
if ($imagesRoot === false) {
    $imagesRoot = __DIR__ . '/../../images';
    if (!is_dir($imagesRoot)) {
        mkdir($imagesRoot, 0755, true);
    }
}

// Create gig_media subdirectory inside images
$galleryUploadDir = rtrim($imagesRoot, '/\\') . DIRECTORY_SEPARATOR . 'gig_media' . DIRECTORY_SEPARATOR;
if (!is_dir($galleryUploadDir)) {
    mkdir($galleryUploadDir, 0755, true);
}
$publicGalleryPath = '/images/gig_media/';

if (!function_exists('gigGalleryNormalizeUploads')) {
    function gigGalleryNormalizeUploads(?array $field): array
    {
        if (!$field || !isset($field['name']) || !is_array($field['name'])) {
            return [];
        }

        $files = [];
        $count = count($field['name']);
        for ($i = 0; $i < $count; $i++) {
            if (empty($field['name'][$i])) {
                continue;
            }
            $files[] = [
                'name' => $field['name'][$i],
                'type' => $field['type'][$i] ?? '',
                'tmp_name' => $field['tmp_name'][$i] ?? '',
                'error' => $field['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $field['size'][$i] ?? 0,
            ];
        }
        return $files;
    }
}

if (!function_exists('gigGalleryDeleteMediaFile')) {
    function gigGalleryDeleteMediaFile(?array $media, string $dir): void
    {
        if (empty($media['path'])) {
            return;
        }
        $target = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . basename($media['path']);
        if (is_file($target)) {
            @unlink($target);
        }
    }
}

if (!function_exists('gigGalleryCleanupUploadedFiles')) {
    function gigGalleryCleanupUploadedFiles(array $mediaList, string $dir): void
    {
        foreach ($mediaList as $media) {
            gigGalleryDeleteMediaFile($media, $dir);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $incomingImages = gigGalleryNormalizeUploads($_FILES['images'] ?? null);

    if (!empty($incomingImages) && count($incomingImages) > $MAX_IMAGES) {
        $imagesErrorMessage = "You can upload up to {$MAX_IMAGES} images only.";
    }

    if (!$imagesErrorMessage && !empty($incomingImages)) {
        $savedImages = [];
        foreach ($incomingImages as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $imagesErrorMessage = 'Failed to upload one of the images. Please try again.';
                break;
            }

            if ($file['size'] > $MAX_IMAGE_SIZE_BYTES) {
                $imagesErrorMessage = 'Each image must be smaller than 10MB.';
                break;
            }

            $mime = $finfo->file($file['tmp_name']);
            if (!$mime || !isset($imageMimeMap[$mime])) {
                $imagesErrorMessage = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
                break;
            }

            $extension = $imageMimeMap[$mime];
            $newFilename = 'gig-img-' . uniqid('', true) . '.' . $extension;
            $targetPath = $galleryUploadDir . $newFilename;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                $imagesErrorMessage = 'Unable to save one of the images. Please try again.';
                break;
            }

            $savedImages[] = [
                'path' => $publicGalleryPath . $newFilename,
                'name' => $file['name'],
                'size' => $file['size'],
                'type' => $mime
            ];
        }

        if ($imagesErrorMessage) {
            gigGalleryCleanupUploadedFiles($savedImages, $galleryUploadDir);
        } else {
            gigGalleryCleanupUploadedFiles($storedImages, $galleryUploadDir);
            $storedImages = $savedImages;
        }
    }

    if (!$imagesErrorMessage && empty($storedImages)) {
        $imagesErrorMessage = 'Please upload at least one image.';
    }

    if (isset($_FILES['video']) && !empty($_FILES['video']['name'])) {
        $videoFile = $_FILES['video'];
        if ($videoFile['error'] !== UPLOAD_ERR_OK) {
            $videoErrorMessage = 'Failed to upload the video. Please try again.';
        } elseif ($videoFile['size'] > $MAX_VIDEO_SIZE_BYTES) {
            $videoErrorMessage = 'Video must be smaller than 50MB.';
        } else {
            $videoMime = $finfo->file($videoFile['tmp_name']);
            if (!$videoMime || !isset($videoMimeMap[$videoMime])) {
                $videoErrorMessage = 'Unsupported video format. Please upload MP4, MOV, WEBM, AVI, or M4V.';
            } else {
                $extension = $videoMimeMap[$videoMime];
                $videoFilename = 'gig-vid-' . uniqid('', true) . '.' . $extension;
                $videoTarget = $galleryUploadDir . $videoFilename;

                if (!move_uploaded_file($videoFile['tmp_name'], $videoTarget)) {
                    $videoErrorMessage = 'Unable to save the video. Please try again.';
                } else {
                    gigGalleryDeleteMediaFile($storedVideo, $galleryUploadDir);
                    $storedVideo = [
                        'path' => $publicGalleryPath . $videoFilename,
                        'name' => $videoFile['name'],
                        'size' => $videoFile['size'],
                        'type' => $videoMime
                    ];
                }
            }
        }
    }

    if (!$imagesErrorMessage && !$videoErrorMessage) {
        $_SESSION[$gallerySessionKey] = [
            'images' => $storedImages,
            'video' => $storedVideo
        ];

        header('Location: gig_summary.php');
        exit();
    }
}

$_title = 'Create Gig - Gallery';
include '../../_head.php';
?>

<style>
    /* Description page style applied to Gallery page */

    .create-gig-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 40px 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #2c3e50;
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
        font-weight: 700;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    .milestone-step.active .milestone-label {
        color: rgb(159, 232, 112);
        font-weight: 700;
    }

    .milestone-step.completed .milestone-label {
        color: rgb(140, 210, 90);
        font-weight: 700;
    }

    /* Separator arrow */
    .milestone-separator {
        flex-shrink: 0;
        color: #ddd;
        font-size: 1.5rem;
        margin: 0 10px;
        font-weight: 300;
    }

    .milestone-step.completed~.milestone-separator {
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
        margin: 0 0 8px 0;
    }

    .gig-form-section p {
        font-size: 0.85rem;
        color: #999;
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

    /* Images container - dynamic layout */
    .images-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        align-items: start;
    }

    .images-container.has-images {
        grid-template-columns: auto 1fr;
    }

    .images-preview-wrapper {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        min-width: 200px;
    }

    .preview-item {
        width: 160px;
        height: 120px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #333;
        background: #fafbfc;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        flex-shrink: 0;
    }

    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .preview-label {
        position: absolute;
        bottom: 8px;
        left: 8px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Drag and drop upload area */
    .upload-area {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .images-container.has-images .upload-area {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }

    .upload-box {
        width: 100%;
        min-height: 140px;
        border: 2px dashed #5d7a9f;
        border-radius: 8px;
        background: #f5f8fb;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .upload-box:hover {
        border-color: rgb(159, 232, 112);
        background: rgba(159, 232, 112, 0.05);
    }

    .upload-box.dragover {
        border-color: rgb(159, 232, 112);
        background: rgba(159, 232, 112, 0.1);
    }

    .upload-icon {
        font-size: 2.5rem;
        margin-bottom: 8px;
        opacity: 0.6;
    }

    .upload-text {
        font-size: 0.85rem;
        color: #666;
        text-align: center;
        line-height: 1.4;
    }

    .upload-text a {
        color: rgb(159, 232, 112);
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
    }

    .upload-text a:hover {
        text-decoration: underline;
    }

    .video-preview-wrapper {
        display: flex;
        gap: 12px;
        margin-top: 12px;
    }

    .preview-video {
        width: 160px;
        height: 120px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e6e9ec;
        background: #fafbfc;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preview-video video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .error {
        color: #f44336;
        font-weight: 600;
        margin-top: 6px;
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

    /* Responsive */
    @media (max-width: 768px) {
        .create-gig-container {
            padding: 20px;
        }

        .gig-form-container {
            padding: 20px;
        }

        .images-container {
            grid-template-columns: 1fr;
        }

        .images-container.has-images {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }

        .upload-area {
            grid-template-columns: 1fr;
        }

        .images-container.has-images .upload-area {
            grid-template-columns: 1fr;
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

            <div class="milestone-step active" data-step="gallery">
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

    <div class="gig-form-container">
        <form id="galleryForm" method="POST" action="gig_gallery.php" enctype="multipart/form-data" novalidate>
            <div class="gig-form-section">
                <h3>🖼️ Images (up to 3)</h3>
                <p>Get noticed by the right buyers with visual examples of your services.</p>
                <p class="form-description">You must keep at least one image. Uploading a new set will replace the currently saved gallery.</p>

                <div class="input-col">
                    <div class="images-container <?php echo !empty($storedImages) ? 'has-images' : ''; ?>" id="imagesContainer">
                        <div class="images-preview-wrapper" id="imagesPreviewWrapper" <?php echo empty($storedImages) ? 'style="display:none;"' : ''; ?>>
                            <?php foreach ($storedImages as $index => $image): ?>
                                <div class="preview-item saved-media">
                                    <img src="<?php echo htmlspecialchars($image['path']); ?>" alt="<?php echo htmlspecialchars($image['name']); ?>">
                                    <?php if ($index === 0): ?>
                                        <div class="preview-label">⭐ Primary</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="upload-area" id="imagesUploadArea">
                            <div class="upload-box" onclick="document.getElementById('imagesInput').click();">
                                <div class="upload-icon">🖼️</div>
                                <div class="upload-text">
                                    Drag & drop images (max 3)<br>
                                    <a onclick="event.stopPropagation(); document.getElementById('imagesInput').click();">Browse</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="imagesInput" name="images[]" accept="image/*" multiple style="display:none;">
                    <div id="imagesError" class="error" <?php echo empty($imagesErrorMessage) ? 'style="display:none;"' : ''; ?>>
                        <?php echo htmlspecialchars($imagesErrorMessage); ?>
                    </div>
                </div>

                <h3 style="margin-top: 30px;">🎥 Video (one only)</h3>
                <p>Capture buyers' attention with a video that showcases your service.<br>Please choose a video shorter than 75 seconds and smaller than 50MB</p>

                <div class="input-col">
                    <div class="upload-area" style="grid-template-columns: 1fr;">
                        <div class="upload-box" id="videoBox" onclick="document.getElementById('videoInput').click();">
                            <div class="upload-icon">🎬</div>
                            <div class="upload-text">
                                Drag & drop a Video or<br>
                                <a onclick="event.stopPropagation(); document.getElementById('videoInput').click();">Browse</a>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="videoInput" name="video" accept="video/*" style="display:none;">
                    <div id="videoError" class="error" <?php echo empty($videoErrorMessage) ? 'style="display:none;"' : ''; ?>>
                        <?php echo htmlspecialchars($videoErrorMessage); ?>
                    </div>
                    <div class="video-preview-wrapper" id="videoPreviewWrapper">
                        <?php if (!empty($storedVideo)): ?>
                            <div class="preview-video saved-media">
                                <video src="<?php echo htmlspecialchars($storedVideo['path']); ?>" controls></video>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="goToPreviousStep();">Back to Description</button>
                <button type="submit" id="continueBtn" class="btn btn-primary" disabled>Continue to Publish</button>
            </div>
        </form>
    </div>
</div>

<script>
    const MAX_IMAGES = <?php echo (int) $MAX_IMAGES; ?>;
    const MIN_IMAGES = <?php echo (int) $MIN_IMAGES; ?>;
    const MAX_IMAGE_SIZE = <?php echo (int) $MAX_IMAGE_SIZE_BYTES; ?>;
    const MAX_VIDEO_SIZE = <?php echo (int) $MAX_VIDEO_SIZE_BYTES; ?>;
    const serverImages = <?php echo json_encode(array_values($storedImages), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES); ?>;
    const serverVideo = <?php echo json_encode($storedVideo, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES); ?>;

    const imagesInput = document.getElementById('imagesInput');
    const videoInput = document.getElementById('videoInput');
    const imagesPreviewWrapper = document.getElementById('imagesPreviewWrapper');
    const videoPreviewWrapper = document.getElementById('videoPreviewWrapper');
    const imagesError = document.getElementById('imagesError');
    const videoError = document.getElementById('videoError');
    const continueBtn = document.getElementById('continueBtn');
    const imagesUploadArea = document.getElementById('imagesUploadArea');
    const imagesContainer = document.getElementById('imagesContainer');
    const videoBox = document.getElementById('videoBox');
    const galleryForm = document.getElementById('galleryForm');

    let selectedImages = [];
    let selectedVideo = null;

    document.addEventListener('DOMContentLoaded', function() {
        imagesInput.addEventListener('change', handleImagesChange);
        videoInput.addEventListener('change', handleVideoChange);
        galleryForm.addEventListener('submit', function(event) {
            if (!validateAndContinue(true)) {
                event.preventDefault();
            }
        });
        setupDragDrop();
        addMilestoneClickHandlers();
        renderImagePreviews();
        renderVideoPreviews();
        updateContinueState();
    });

    function setupDragDrop() {
        imagesUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            imagesUploadArea.classList.add('dragover');
        });
        imagesUploadArea.addEventListener('dragleave', () => {
            imagesUploadArea.classList.remove('dragover');
        });
        imagesUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            imagesUploadArea.classList.remove('dragover');
            handleImagesChange({ target: { files: e.dataTransfer.files } });
        });

        videoBox.addEventListener('dragover', (e) => {
            e.preventDefault();
            videoBox.classList.add('dragover');
        });
        videoBox.addEventListener('dragleave', () => {
            videoBox.classList.remove('dragover');
        });
        videoBox.addEventListener('drop', (e) => {
            e.preventDefault();
            videoBox.classList.remove('dragover');
            handleVideoChange({ target: { files: e.dataTransfer.files } });
        });
    }

    function handleImagesChange(e) {
        imagesError.style.display = 'none';
        imagesError.textContent = '';

        const files = Array.from(e.target.files || []);
        const imageFiles = files.filter(file => file.type && file.type.startsWith('image/'));

        if (imageFiles.length === 0) {
            selectedImages = [];
            renderImagePreviews();
            updateContinueState();
            return;
        }

        if (imageFiles.length > MAX_IMAGES) {
            imagesError.textContent = `You can upload up to ${MAX_IMAGES} images only.`;
            imagesError.style.display = 'block';
            selectedImages = imageFiles.slice(0, MAX_IMAGES);
        } else {
            selectedImages = imageFiles;
        }

        const invalidImage = selectedImages.find(file => file.size > MAX_IMAGE_SIZE);
        if (invalidImage) {
            const limitInMb = (MAX_IMAGE_SIZE / (1024 * 1024)).toFixed(0);
            imagesError.textContent = `Image "${invalidImage.name}" exceeds the ${limitInMb}MB limit.`;
            imagesError.style.display = 'block';
        }

        renderImagePreviews();
        updateContinueState();
    }

    function handleVideoChange(e) {
        videoError.style.display = 'none';
        videoError.textContent = '';

        const file = e.target.files && e.target.files[0];
        if (!file) {
            selectedVideo = null;
            renderVideoPreviews();
            updateContinueState();
            return;
        }

        if (!file.type.startsWith('video/')) {
            videoError.textContent = 'Selected file is not a valid video.';
            videoError.style.display = 'block';
            videoInput.value = '';
            selectedVideo = null;
            renderVideoPreviews();
            updateContinueState();
            return;
        }

        if (file.size > MAX_VIDEO_SIZE) {
            const limitInMb = (MAX_VIDEO_SIZE / (1024 * 1024)).toFixed(0);
            videoError.textContent = `Video exceeds the ${limitInMb}MB limit.`;
            videoError.style.display = 'block';
            videoInput.value = '';
            selectedVideo = null;
            renderVideoPreviews();
            updateContinueState();
            return;
        }

        selectedVideo = file;
        renderVideoPreviews();
        updateContinueState();
    }

    function renderImagePreviews() {
        imagesPreviewWrapper.innerHTML = '';
        const showingUploaded = selectedImages.length > 0;
        const sourceImages = showingUploaded ? selectedImages : serverImages;

        if (sourceImages.length > 0) {
            imagesPreviewWrapper.style.display = 'flex';
            imagesContainer.classList.add('has-images');
        } else {
            imagesPreviewWrapper.style.display = 'none';
            imagesContainer.classList.remove('has-images');
        }

        sourceImages.forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'preview-item';

            if (showingUploaded) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    const img = document.createElement('img');
                    img.src = ev.target.result;
                    img.alt = file.name;
                    wrapper.appendChild(img);
                    if (index === 0) {
                        const label = document.createElement('div');
                        label.className = 'preview-label';
                        label.innerHTML = '⭐ Primary';
                        wrapper.appendChild(label);
                    }
                };
                reader.readAsDataURL(file);
            } else {
                const img = document.createElement('img');
                img.src = file.path;
                img.alt = file.name || 'Saved image';
                wrapper.appendChild(img);
                if (index === 0) {
                    const label = document.createElement('div');
                    label.className = 'preview-label';
                    label.innerHTML = '⭐ Primary';
                    wrapper.appendChild(label);
                }
            }

            imagesPreviewWrapper.appendChild(wrapper);
        });
    }

    function renderVideoPreviews() {
        videoPreviewWrapper.innerHTML = '';
        if (selectedVideo) {
            const reader = new FileReader();
            const wrapper = document.createElement('div');
            wrapper.className = 'preview-video';
            reader.onload = function(ev) {
                const vid = document.createElement('video');
                vid.src = ev.target.result;
                vid.controls = true;
                wrapper.appendChild(vid);
            };
            reader.readAsDataURL(selectedVideo);
            videoPreviewWrapper.appendChild(wrapper);
        } else if (serverVideo) {
            const wrapper = document.createElement('div');
            wrapper.className = 'preview-video';
            const vid = document.createElement('video');
            vid.src = serverVideo.path;
            vid.controls = true;
            wrapper.appendChild(vid);
            videoPreviewWrapper.appendChild(wrapper);
        }
    }

    function updateContinueState() {
        const hasServerImages = serverImages.length > 0 && selectedImages.length === 0;
        const hasMinImage = hasServerImages || selectedImages.length >= MIN_IMAGES;
        const imageSizeOk = selectedImages.every(file => file.size <= MAX_IMAGE_SIZE);
        const imagesCountOk = selectedImages.length === 0 ? true : selectedImages.length <= MAX_IMAGES;
        const videoOk = !selectedVideo || selectedVideo.size <= MAX_VIDEO_SIZE;
        const hasImageError = imagesError.textContent.trim().length > 0 && imagesError.style.display !== 'none';
        const hasVideoError = videoError.textContent.trim().length > 0 && videoError.style.display !== 'none';

        continueBtn.disabled = !(hasMinImage && imageSizeOk && imagesCountOk && videoOk && !hasImageError && !hasVideoError);
    }

    function validateAndContinue(isSubmitEvent = false) {
        if (selectedImages.length === 0 && serverImages.length === 0) {
            imagesError.textContent = 'Please upload at least one image.';
            imagesError.style.display = 'block';
            updateContinueState();
            return false;
        }

        if (selectedImages.length > MAX_IMAGES) {
            imagesError.textContent = `You can upload up to ${MAX_IMAGES} images only.`;
            imagesError.style.display = 'block';
            updateContinueState();
            return false;
        }

        if (selectedImages.length > 0) {
            for (const img of selectedImages) {
                if (!img.type.startsWith('image/')) {
                    imagesError.textContent = `File "${img.name}" is not an image.`;
                    imagesError.style.display = 'block';
                    updateContinueState();
                    return false;
                }
                if (img.size > MAX_IMAGE_SIZE) {
                    imagesError.textContent = `Image "${img.name}" exceeds the size limit.`;
                    imagesError.style.display = 'block';
                    updateContinueState();
                    return false;
                }
            }
        }

        if (selectedVideo) {
            if (!selectedVideo.type.startsWith('video/')) {
                videoError.textContent = 'Selected video is not valid.';
                videoError.style.display = 'block';
                updateContinueState();
                return false;
            }
            if (selectedVideo.size > MAX_VIDEO_SIZE) {
                videoError.textContent = 'Selected video is too large.';
                videoError.style.display = 'block';
                updateContinueState();
                return false;
            }
        }

        if (!isSubmitEvent) {
            galleryForm.submit();
        }
        return true;
    }

    function goToPreviousStep() {
        window.location.href = 'gig_description.php';
    }

    function addMilestoneClickHandlers() {
        const stepPages = {
            'overview': 'create_gig.php',
            'pricing': 'gig_price.php',
            'description': 'gig_description.php',
            'gallery': 'gig_gallery.php',
            'publish': 'gig_summary.php'
        };
        const clickableSteps = document.querySelectorAll('.milestone-step.completed.clickable');
        clickableSteps.forEach(step => {
            step.addEventListener('click', function() {
                const stepKey = this.getAttribute('data-step');
                if (stepPages[stepKey]) {
                    window.location.href = stepPages[stepKey];
                }
            });
        });
    }
</script>

<?php include '../../_foot.php'; ?>