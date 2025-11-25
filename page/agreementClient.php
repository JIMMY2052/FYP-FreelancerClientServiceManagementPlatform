<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../index.php');
    exit();
}

$_title = 'Review & Sign Agreement';
$client_id = $_SESSION['user_id'];
$application_id = isset($_GET['application_id']) ? intval($_GET['application_id']) : null;

require_once 'config.php';

// Fetch application with related job and freelancer data
$job_data = array();
$error = null;

if ($application_id) {
    $conn = getDBConnection();

    $sql = "SELECT 
                ja.ApplicationID,
                ja.JobID,
                ja.FreelancerID,
                ja.CoverLetter,
                ja.ProposedBudget,
                ja.EstimatedDuration,
                j.Title as JobTitle,
                j.Description as JobDescription,
                j.Budget as JobBudget,
                j.ClientID,
                j.Deadline,
                CONCAT(f.FirstName, ' ', f.LastName) as FreelancerName,
                c.CompanyName as ClientName
            FROM job_application ja
            JOIN job j ON ja.JobID = j.JobID
            JOIN freelancer f ON ja.FreelancerID = f.FreelancerID
            JOIN client c ON j.ClientID = c.ClientID
            WHERE ja.ApplicationID = ? AND j.ClientID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $application_id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $job_data = $result->fetch_assoc();
        // Store freelancer and client IDs in session for process file
        $_SESSION['agreement_freelancer_id'] = $job_data['FreelancerID'];
        $_SESSION['agreement_client_id'] = $client_id;
    } else {
        $error = "Application not found or you don't have permission to access it.";
    }
    $stmt->close();
    $conn->close();
} else {
    $error = "No application ID provided.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Review & Sign Agreement' ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/assets/js/app.js"></script>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/freelancer.css">
    <link rel="stylesheet" href="/assets/css/client.css">
    <link rel="stylesheet" href="../assets/css/agreement.css">
    <style>
        .form-error {
            border: 2px solid #dc3545 !important;
            background-color: #fff5f5;
        }

        .readonly-field {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <header class="main-header">
        <div class="header-container">
            <div class="header-logo">
                <a href="<?php
                            if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
                                if ($_SESSION['user_type'] === 'freelancer') {
                                    echo '/freelancer_home.php';
                                } else {
                                    echo '/client_home.php';
                                }
                            } else {
                                echo '/index.php';
                            }
                            ?>">
                    <img src="/images/logo.png" alt="Freelancer Platform Logo" class="logo-img">
                </a>
            </div>
            <nav class="header-nav">
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])): ?>
                    <!-- Show profile and notification when logged in -->
                    <span class="notification-icon">🔔</span>
                    <div class="profile-dropdown">
                        <div class="profile-avatar">👤</div>
                        <div class="dropdown-menu">
                            <?php if ($_SESSION['user_type'] === 'freelancer'): ?>
                                <a href="/page/freelancer_profile.php" class="dropdown-item">View Profile</a>
                                <a href="/page/freelancer_dashboard.php" class="dropdown-item">Dashboard</a>
                            <?php else: ?>
                                <a href="/page/client_profile.php" class="dropdown-item">View Profile</a>
                                <a href="/page/client_dashboard.php" class="dropdown-item">Dashboard</a>
                            <?php endif; ?>
                            <a href="/page/payment/wallet.php" class="dropdown-item">Wallet</a>
                            <a href="/page/logout.php" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Show login and signup when not logged in -->
                    <a href="/page/login.php" class="btn btn-login">Login</a>
                    <a href="/page/signup.php" class="btn btn-signup">Sign Up</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="header">
        <div class="header-top">
            <div>
                <h1>📋 Review & Sign Agreement</h1>
                <p>Review the project agreement and sign to accept the freelancer application</p>
            </div>
            <button type="button" onclick="window.history.back()" class="header-back-btn">
                ← Back
            </button>
        </div>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
            <a href="my_applications.php" class="btn-secondary" style="display: inline-block; margin-top: 20px;">
                ← Back to Applications
            </a>
        <?php else: ?>

            <!-- LIVE PREVIEW SECTION (Left) -->
            <div class="preview-box">
                <h2>👁️ Agreement Preview</h2>

                <!-- HEADER -->
                <div class="preview-header">
                    <div class="preview-header-left">
                        <h3 id="pTitle"><?= htmlspecialchars($job_data['JobTitle']) ?></h3>
                        <p id="pProjectLabel"><span class="section-content"><?= htmlspecialchars($job_data['JobDescription']) ?></span></p>
                    </div>
                    <div class="preview-header-right">
                        <span class="label">Freelancer:</span>
                        <span class="value" id="pOfferer"><?= htmlspecialchars($job_data['FreelancerName']) ?></span>
                        <span class="label" style="margin-top: 12px;">Your Company:</span>
                        <span class="value" id="pClient"><?= htmlspecialchars($job_data['ClientName']) ?></span>
                        <span class="label" style="margin-top: 12px;">Date:</span>
                        <span class="value" id="pDate" style="color: #6b7280;">Today</span>
                    </div>
                </div>

                <!-- INTRODUCTORY PARAGRAPH -->
                <div class="preview-section" style="background: #f9f9f9; border-left: 4px solid #1ab394; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <p style="margin: 0; line-height: 1.6; color: #333; font-size: 0.95rem;">
                        This Services Agreement shall become effective on date (the "Execution Date") and is subject to the terms and conditions stated below between <strong><?= htmlspecialchars($job_data['FreelancerName']) ?></strong> (the "Service Provider") and <strong><?= htmlspecialchars($job_data['ClientName']) ?></strong> (the "Client"), collectively referred to as the "Parties".
                    </p>
                </div>

                <!-- SECTION 1: SCOPE OF WORK -->
                <div class="preview-section">
                    <div class="section-number">
                        <span>1</span>
                        <div class="section-title">Scope of Work</div>
                    </div>
                    <div class="section-content">
                        <div id="pScope" class="section-content"><?= nl2br(htmlspecialchars($job_data['JobDescription'])) ?></div>
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
                            <strong>Deadline:</strong> <?= date('M d, Y', strtotime($job_data['Deadline'])) ?><br>
                            <strong>Estimated Duration:</strong> <?= htmlspecialchars($job_data['EstimatedDuration']) ?>
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
                            <div class="payment-total">
                                <span class="payment-label">Total Project Price:</span>
                                <span class="payment-amount" id="pPayment">RM <?= number_format($job_data['JobBudget'], 2) ?></span>
                            </div>
                            <div id="pPaymentInfo" class="section-content">Payment will be held in escrow until project completion, then released to the freelancer upon client approval.</div>
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
                                <li>Payment will be held until project completion</li>
                                <li>Freelancer agrees to meet all deliverables within the agreed timeline</li>
                                <li>Client agrees to provide timely feedback and approval</li>
                                <li>Both parties commit to professional and respectful communication</li>
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
                <h2>✏️ Sign Agreement</h2>

                <form id="agreementForm" action="agreementClient_process.php" method="POST">

                    <label for="title">Project Title</label>
                    <input type="text" name="project_title" id="title" class="readonly-field" value="<?= htmlspecialchars($job_data['JobTitle']) ?>" readonly>

                    <label for="projectDetail">Project Details</label>
                    <textarea name="project_detail" rows="2" id="projectDetail" class="readonly-field" readonly><?= htmlspecialchars($job_data['JobDescription']) ?></textarea>

                    <label for="scope">Scope of Work</label>
                    <textarea name="scope" rows="3" id="scope" class="readonly-field" readonly><?= htmlspecialchars($job_data['JobDescription']) ?></textarea>

                    <label for="deliverables">Deliverables & Timeline</label>
                    <textarea name="deliverables" rows="3" id="deliverables" class="readonly-field" readonly><?= htmlspecialchars($job_data['EstimatedDuration']) ?></textarea>

                    <label for="payment">Payment Amount (RM)</label>
                    <input type="number" name="payment" id="payment" class="readonly-field" value="<?= htmlspecialchars($job_data['JobBudget']) ?>" step="0.01" readonly>

                    <label for="terms">Terms & Conditions</label>
                    <textarea name="terms" rows="4" id="terms" class="readonly-field" readonly>Payment upon project completion. Freelancer agrees to meet all deliverables within the agreed timeline.</textarea>

                    <!-- SIGNATURE SECTION -->
                    <div class="signature-section">
                        <h3>🖊️ Your Digital Signature</h3>
                        <p>Sign below to accept this agreement and the freelancer application</p>

                        <div class="signature-container">
                            <canvas id="signaturePad"></canvas>
                        </div>

                        <div class="signature-buttons">
                            <button type="button" id="clearSignature">Clear</button>
                            <button type="button" class="sign-submit" id="confirmSignature">Confirm Signature</button>
                        </div>

                        <div class="signature-name-field">
                            <label for="clientName">Your Full Name (for signature) *</label>
                            <input type="text" name="client_name" id="clientName" placeholder="Enter your full name" required>
                        </div>

                        <input type="hidden" name="freelancer_name" value="<?= htmlspecialchars($job_data['FreelancerName']) ?>">
                        <input type="hidden" name="application_id" value="<?= $application_id ?>">
                        <input type="hidden" name="job_id" value="<?= htmlspecialchars($job_data['JobID']) ?>">
                        <input type="hidden" name="delivery_time" value="<?= htmlspecialchars($job_data['EstimatedDuration']) ?>">
                        <input type="hidden" name="signature" id="signatureData">

                        <div class="signature-note">
                            ✓ Your signature confirms your acceptance of this agreement and the application
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-green">✓ Sign & Accept Application</button>
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

            // Clear button
            document.getElementById("clearSignature").addEventListener("click", function() {
                signaturePad.clear();
                isSignatureSigned = false;
                document.getElementById("signatureData").value = "";
                document.getElementById("pSignatureImage").style.display = "none";
                document.getElementById("pSignatureName").textContent = "___________________";
            });

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

                // Update preview
                const img = document.getElementById("pSignatureImage");
                img.src = signatureDataURL;
                img.style.display = "block";
                document.getElementById("pSignatureName").textContent = clientName;

                alert("Signature confirmed successfully!");
            });

            // Update client name in preview as typing
            document.getElementById("clientName").addEventListener("input", function() {
                if (isSignatureSigned) {
                    document.getElementById("pSignatureName").textContent = this.value.trim() || "___________________";
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
                alert("Please enter your name and provide your signature to accept this agreement.");
                return;
            }

            // Show confirmation dialog
            showConfirmationDialog(this);
        });

        // Confirmation Dialog
        function showConfirmationDialog(form) {
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
                    <div style="font-size: 3rem; margin-bottom: 16px;">✅</div>
                    <h2 style="font-size: 1.5rem; color: #1a1a1a; margin-bottom: 12px; font-weight: 700;">Sign & Accept Application?</h2>
                    <p style="color: #666; font-size: 1rem; line-height: 1.6; margin-bottom: 30px;">
                        By signing this agreement, you confirm that you accept the freelancer's application and authorize the project funds to be held in escrow until completion.
                    </p>
                    <div style="display: flex; gap: 12px; justify-content: center;">
                        <button id="confirmCancel" type="button" style="
                            padding: 12px 28px;
                            border-radius: 6px;
                            border: 1px solid #ddd;
                            background: #f0f1f3;
                            color: #333;
                            cursor: pointer;
                            font-size: 15px;
                            font-weight: 600;
                            transition: all 0.3s ease;
                        " onmouseover="this.style.background='#e0e2e8'" onmouseout="this.style.background='#f0f1f3'">
                            ✕ Cancel
                        </button>
                        <button id="confirmSubmit" type="button" style="
                            padding: 12px 28px;
                            border-radius: 6px;
                            border: none;
                            background: #1ab394;
                            color: white;
                            cursor: pointer;
                            font-size: 15px;
                            font-weight: 600;
                            transition: all 0.3s ease;
                            box-shadow: 0 2px 8px rgba(26, 179, 148, 0.2);
                        " onmouseover="this.style.background='#158a74'; this.style.boxShadow='0 4px 12px rgba(26, 179, 148, 0.3)'" onmouseout="this.style.background='#1ab394'; this.style.boxShadow='0 2px 8px rgba(26, 179, 148, 0.2)'">
                            ✓ Sign & Accept
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