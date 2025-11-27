<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../index.php');
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit();
}

$_title = 'Review & Sign Gig Agreement';
$client_id = $_SESSION['user_id'];
$gig_id = isset($_POST['gig_id']) ? intval($_POST['gig_id']) : null;
$rush_delivery = isset($_POST['rush_delivery']) ? intval($_POST['rush_delivery']) : 0;

require_once 'config.php';

// Fetch gig and freelancer details
$gig_data = array();
$error = null;

if ($gig_id) {
    $conn = getDBConnection();

    $sql = "SELECT 
                g.GigID,
                g.FreelancerID,
                g.Title,
                g.Description,
                g.Price,
                g.RushDeliveryPrice,
                g.DeliveryTime,
                g.RushDelivery,
                g.RevisionCount,
                g.Status,
                CONCAT(f.FirstName, ' ', f.LastName) as FreelancerName,
                f.ProfilePicture as FreelancerProfilePic,
                c.CompanyName as ClientName
            FROM gig g
            JOIN freelancer f ON g.FreelancerID = f.FreelancerID
            JOIN client c ON c.ClientID = ?
            WHERE g.GigID = ? AND g.Status = 'active'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $client_id, $gig_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $gig_data = $result->fetch_assoc();

        // Calculate totals
        $base_price = floatval($gig_data['Price']);
        $rush_fee = ($rush_delivery && !empty($gig_data['RushDeliveryPrice'])) ? floatval($gig_data['RushDeliveryPrice']) : 0;
        $gig_data['TotalAmount'] = $base_price + $rush_fee;
        $gig_data['RushDeliverySelected'] = $rush_delivery;
        $gig_data['FinalDeliveryTime'] = $rush_delivery && !empty($gig_data['RushDelivery']) ? intval($gig_data['RushDelivery']) : intval($gig_data['DeliveryTime']);

        // Store freelancer ID in session for process file
        $_SESSION['agreement_freelancer_id'] = $gig_data['FreelancerID'];
        $_SESSION['agreement_client_id'] = $client_id;
    } else {
        $error = "Gig not found or unavailable.";
    }
    $stmt->close();
    $conn->close();
} else {
    $error = "No gig ID provided.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Review & Sign Gig Agreement' ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/assets/js/app.js"></script>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/freelancer.css">
    <link rel="stylesheet" href="/assets/css/client.css">
    <link rel="stylesheet" href="../assets/css/agreement.css">
    <link rel="stylesheet" href="../assets/css/agreementClient.css">
</head>

<body>
    <button type="button" onclick="window.history.back()" class="header-back-btn">
        <span>←</span> Back
    </button>
    <div class="header">
        <div class="header-top">
            <div>
                <h1>Review & Sign Gig Agreement</h1>
                <p>Review the gig agreement and sign to confirm your order</p>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
            <a href="gig/browse_gigs.php" class="btn-secondary" style="display: inline-block; margin-top: 20px;">
                ← Back to Gigs
            </a>
        <?php else: ?>

            <!-- LIVE PREVIEW SECTION (Left) -->
            <div class="preview-box">
                <h2>Gig Agreement Preview</h2>

                <!-- HEADER -->
                <div class="preview-header">
                    <div class="preview-header-left">
                        <h3 id="pTitle"><?= htmlspecialchars($gig_data['Title']) ?></h3>
                        <p id="pProjectLabel"><span class="section-content"><?= htmlspecialchars($gig_data['Description']) ?></span></p>
                    </div>
                    <div class="preview-header-right">
                        <span class="label">Freelancer:</span>
                        <span class="value" id="pOfferer"><?= htmlspecialchars($gig_data['FreelancerName']) ?></span>
                        <span class="label" style="margin-top: 12px;">Your Company:</span>
                        <span class="value" id="pClient"><?= htmlspecialchars($gig_data['ClientName']) ?></span>
                        <span class="label" style="margin-top: 12px;">Date:</span>
                        <span class="value" id="pDate" style="color: #6b7280;">Today</span>
                    </div>
                </div>



                <!-- SECTION 1: SCOPE OF WORK -->
                <div class="preview-section">
                    <div class="section-number">
                        <span>1</span>
                        <div class="section-title">Scope of Work</div>
                    </div>
                    <div class="section-content">
                        <div id="pScope" class="section-content"><?= nl2br(htmlspecialchars($gig_data['Description'])) ?></div>
                    </div>
                </div>

                <!-- SECTION 2: DELIVERABLES & TIMELINE -->
                <div class="preview-section">
                    <div class="section-number">
                        <span>2</span>
                        <div class="section-title">Deliverables & Timeline</div>
                    </div>
                    <div class="section-content">
                        <div id="pDeliver" class="section-content">
                            <strong>Delivery Time:</strong> <?= $gig_data['FinalDeliveryTime'] ?> day(s)<br>
                            <strong>Revisions Included:</strong> <?= $gig_data['RevisionCount'] ?> revision(s)<br>
                            <strong>Delivery Method:</strong> As specified in the gig description
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: PAYMENT TERMS -->
                <div class="preview-section">
                    <div class="section-number">
                        <span>3</span>
                        <div class="section-title">Payment Terms</div>
                    </div>
                    <div class="section-content">
                        <div class="payment-box">

                            <?php if ($gig_data['RushDeliverySelected']): ?>
                                <div class="payment-item"><strong>Rush Delivery Fee:</strong> RM <?= number_format($gig_data['RushDeliveryPrice'], 2) ?></div>
                            <?php endif; ?>
                            <div class="payment-item"><strong>Total Amount:</strong> RM <?= number_format($gig_data['TotalAmount'], 2) ?></div>
                            <div class="payment-item"><strong>Payment Status:</strong> Held in escrow until delivery</div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: TERMS & CONDITIONS -->
                <div class="preview-section">
                    <div class="section-number">
                        <span>4</span>
                        <div class="section-title">Terms & Conditions</div>
                    </div>
                    <div class="section-content">
                        <div id="pTerms" class="section-content">
                            <ul style="margin: 0; padding-left: 20px;">
                                <li>The freelancer will deliver the service as described within <?= $gig_data['FinalDeliveryTime'] ?> day(s)</li>
                                <li>The client will pay RM <?= number_format($gig_data['TotalAmount'], 2) ?> which is held in escrow</li>
                                <li>Payment will be released upon successful delivery and client approval</li>
                                <li>The service includes <?= $gig_data['RevisionCount'] ?> revision(s)</li>
                                <li>Both parties agree to maintain professional conduct throughout the engagement</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: SIGNATURES -->
                <div class="preview-signature-section">
                    <h3 style="text-align: center; margin-bottom: 20px; font-size: 1.1rem;">SIGNATURES</h3>
                    <div style="display: flex; gap: 30px; justify-content: space-between;">
                        <!-- Freelancer Signature -->
                        <div class="signature-block" style="flex: 1;">
                            <div class="signature-line" style="height: 80px; border: 1px dashed #ccc; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #fafafa;">
                                <span style="color: #999; font-size: 0.9rem;">[Freelancer Signature]</span>
                            </div>
                            <div class="signature-label" style="text-align: center; font-weight: 600; margin-top: 10px;">Freelancer Signature</div>
                            <div style="text-align: center; margin-top: 5px;">___________________</div>
                            <div style="text-align: center; font-size: 0.9rem; color: #666; margin-top: 8px;">Date: ___________</div>
                        </div>

                        <!-- Client Signature -->
                        <div class="signature-block" style="flex: 1;">
                            <div class="signature-line">
                                <img id="pSignatureImage" style="display: none; max-width: 100%; height: auto;" />
                            </div>
                            <div class="signature-label" style="text-align: center; font-weight: 600; margin-top: 10px;">Client Signature</div>
                            <div class="signature-name" id="pSignatureName" style="text-align: center; margin-top: 5px;">___________________</div>
                            <div style="text-align: center; font-size: 0.9rem; color: #666; margin-top: 8px;">Date: ___________</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM SECTION (Right) -->
            <div class="form-box">
                <h2>Sign Agreement</h2>

                <form id="agreementForm" action="gigAgreement_process.php" method="POST">

                    <label for="title">Gig Title</label>
                    <input type="text" name="gig_title" id="title" class="readonly-field" value="<?= htmlspecialchars($gig_data['Title']) ?>" readonly>

                    <label for="freelancerName">Freelancer</label>
                    <input type="text" name="freelancer_name" id="freelancerName" class="readonly-field" value="<?= htmlspecialchars($gig_data['FreelancerName']) ?>" readonly>

                    <label for="description">Description</label>
                    <textarea name="description" rows="2" id="description" class="readonly-field" readonly><?= htmlspecialchars($gig_data['Description']) ?></textarea>

                    <label for="deliveryTime">Delivery Time (Days)</label>
                    <input type="number" name="delivery_time" id="deliveryTime" class="readonly-field" value="<?= $gig_data['FinalDeliveryTime'] ?>" readonly>



                    <?php if ($gig_data['RushDeliverySelected']): ?>
                        <label for="rushFee">Rush Delivery Fee (RM)</label>
                        <input type="number" name="rush_fee" id="rushFee" class="readonly-field" value="<?= $gig_data['RushDeliveryPrice'] ?>" step="0.01" readonly>
                    <?php endif; ?>

                    <label for="totalPrice">Total Amount (RM)</label>
                    <input type="number" name="total_amount" id="totalPrice" class="readonly-field" value="<?= $gig_data['TotalAmount'] ?>" step="0.01" readonly style="font-weight: bold; color: #1ab394; font-size: 16px;">

                    <label for="revisions">Revisions Included</label>
                    <input type="number" name="revisions" id="revisions" class="readonly-field" value="<?= $gig_data['RevisionCount'] ?>" readonly>

                    <!-- SIGNATURE SECTION -->
                    <div class="signature-section">
                        <h3>🖊️ Your Digital Signature</h3>
                        <p>Sign below to confirm your order and agree to the terms.</p>
                        <div class="signature-name-field">
                            <label for="clientName">Your Full Name (for signature) *</label>
                            <input type="text" name="client_name" id="clientName" placeholder="Enter your full name" required>
                        </div>
                        <div class="signature-container">
                            <canvas id="signaturePad"></canvas>
                        </div>

                        <div class="signature-buttons">
                            <button type="button" id="clearSignature">Clear</button>
                            <button type="button" class="sign-submit" id="confirmSignature">Confirm Signature</button>
                        </div>

                        <input type="hidden" name="gig_id" value="<?= $gig_id ?>">
                        <input type="hidden" name="freelancer_id" value="<?= $gig_data['FreelancerID'] ?>">
                        <input type="hidden" name="rush_delivery" value="<?= $rush_delivery ?>">
                        <input type="hidden" name="signature" id="signatureData">

                        <div class="signature-note" id="signatureNote" style="display: none;">
                            ✓ Your signature confirms your acceptance of this gig order
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-green">✓ Confirm & Pay</button>
                </form>
            </div>

        <?php endif; ?>
    </div>

    <!-- Signature Pad Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>

    <script>
        // GET CURRENT DATE
        function getCurrentDate() {
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            return new Date().toLocaleDateString('en-US', options);
        }

        // INITIALIZE DATE
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById("pDate")) {
                document.getElementById("pDate").textContent = getCurrentDate();
            }
        });

        // ===== SIGNATURE PAD INITIALIZATION =====
        let signaturePad;
        let isSignatureSigned = false;
        let isSignatureConfirmed = false;

        function updateSubmitButtonState() {
            const submitBtn = document.querySelector('.btn-submit-green');
            submitBtn.disabled = !isSignatureConfirmed;
        }

        function updateConfirmButtonState() {
            const confirmBtn = document.getElementById("confirmSignature");
            const clientName = document.getElementById("clientName").value.trim();
            const hasSignature = signaturePad && !signaturePad.isEmpty();

            // If signature is confirmed, button stays disabled until cleared
            if (isSignatureConfirmed) {
                confirmBtn.style.backgroundColor = '#ccc';
                confirmBtn.style.color = '#666';
                confirmBtn.style.cursor = 'not-allowed';
                confirmBtn.disabled = true;
                return;
            }

            if (hasSignature && clientName) {
                confirmBtn.style.backgroundColor = '#1ab394';
                confirmBtn.style.color = 'white';
                confirmBtn.style.cursor = 'pointer';
                confirmBtn.disabled = false;
            } else {
                confirmBtn.style.backgroundColor = '#ccc';
                confirmBtn.style.color = '#666';
                confirmBtn.style.cursor = 'not-allowed';
                confirmBtn.disabled = true;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById("signaturePad");
            if (!canvas) return;

            // Set canvas size
            const container = canvas.parentElement;
            canvas.width = container.offsetWidth - 32;
            canvas.height = 200;

            // Initialize Signature Pad
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)'
            });

            // Set initial button states
            updateConfirmButtonState();
            updateSubmitButtonState();

            // Clear button
            document.getElementById("clearSignature").addEventListener("click", function() {
                signaturePad.clear();
                isSignatureSigned = false;
                isSignatureConfirmed = false;
                document.getElementById("signatureData").value = "";
                document.getElementById("pSignatureImage").style.display = "none";
                document.getElementById("pSignatureName").textContent = "___________________";
                document.getElementById("signatureNote").style.display = "none";
                updateConfirmButtonState();
                updateSubmitButtonState();
            });

            // Track signature changes
            signaturePad.onEnd = function() {
                updateConfirmButtonState();
            };

            // Confirm signature button
            document.getElementById("confirmSignature").addEventListener("click", function() {
                if (signaturePad.isEmpty()) {
                    alert("Please sign before confirming.");
                    return;
                }

                const clientName = document.getElementById("clientName").value.trim();
                if (!clientName) {
                    alert("Please enter your full name.");
                    return;
                }

                // Get signature as data URL
                const signatureDataURL = signaturePad.toDataURL("image/png");
                document.getElementById("signatureData").value = signatureDataURL;
                isSignatureSigned = true;
                isSignatureConfirmed = true;

                // Update preview
                const img = document.getElementById("pSignatureImage");
                img.src = signatureDataURL;
                img.style.display = "block";
                document.getElementById("pSignatureName").textContent = clientName;
                document.getElementById("signatureNote").style.display = "block";

                // Disable confirm button and enable submit button after confirmation
                updateConfirmButtonState();
                updateSubmitButtonState();

                alert("Signature confirmed successfully!");
            });

            // Update client name in preview as typing
            document.getElementById("clientName").addEventListener("input", function() {
                if (isSignatureConfirmed) {
                    document.getElementById("pSignatureName").textContent = this.value.trim() || "___________________";
                } else {
                    updateConfirmButtonState();
                }
            });
        });

        // FORM VALIDATION
        document.getElementById("agreementForm").addEventListener("submit", function(e) {
            e.preventDefault();

            const clientName = document.getElementById("clientName").value.trim();
            const signatureData = document.getElementById("signatureData").value;

            let hasErrors = false;

            if (!clientName) {
                document.getElementById("clientName").classList.add("form-error");
                hasErrors = true;
            }

            if (!signatureData) {
                document.querySelector(".signature-container").classList.add("form-error");
                hasErrors = true;
            }

            if (hasErrors) {
                alert("Please enter your name and provide your signature to confirm this gig order.");
                return;
            }

            // Show confirmation dialog
            showConfirmationDialog(this);
        });

        // Confirmation Dialog
        function showConfirmationDialog(form) {
            const totalAmount = document.getElementById("totalPrice").value;
            const gigTitle = document.getElementById("title").value;

            const confirmOverlay = document.createElement('div');
            confirmOverlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            `;

            const confirmBox = document.createElement('div');
            confirmBox.style.cssText = `
                background: white;
                border-radius: 12px;
                padding: 40px;
                max-width: 450px;
                width: 90%;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                animation: slideUp 0.3s ease-out;
            `;

            confirmBox.innerHTML = `
                <style>
                    @keyframes slideUp {
                        from {
                            opacity: 0;
                            transform: translateY(20px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                </style>
                <div style="text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 16px;">💳</div>
                    <h2 style="font-size: 1.5rem; color: #1a1a1a; margin-bottom: 12px; font-weight: 700;">Confirm Gig Order?</h2>
                    <p style="color: #666; font-size: 1rem; line-height: 1.6; margin-bottom: 20px;">
                        You are about to order <strong>${gigTitle}</strong> for <strong>RM ${totalAmount}</strong>. 
                        Funds will be held in escrow until delivery.
                    </p>
                    <div style="display: flex; gap: 12px; justify-content: center;">
                        <button id="confirmCancel" style="flex: 1; padding: 12px 20px; border: none; background: #e9ecef; color: #2c3e50; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                            Cancel
                        </button>
                        <button id="confirmSubmit" style="flex: 1; padding: 12px 20px; border: none; background: #1ab394; color: white; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                            Yes, Confirm Order
                        </button>
                    </div>
                </div>
            `;

            confirmOverlay.appendChild(confirmBox);
            document.body.appendChild(confirmOverlay);

            // Cancel button
            document.getElementById("confirmCancel").addEventListener("click", function() {
                confirmOverlay.remove();
            });

            // Confirm button
            document.getElementById("confirmSubmit").addEventListener("click", function() {
                confirmOverlay.remove();
                form.submit();
            });

            // Close on overlay click
            confirmOverlay.addEventListener("click", function(e) {
                if (e.target === confirmOverlay) {
                    confirmOverlay.remove();
                }
            });
        }
    </script>

</body>

</html>