<?php
// Handle parameters from messaging page
$_title = 'Create Agreement';
$freelancer_name = '';
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;
$freelancer_id = isset($_GET['freelancer_id']) ? intval($_GET['freelancer_id']) : null;

require_once 'config.php';

// Fetch freelancer name if freelancer_id is provided
if ($freelancer_id) {
    $conn = getDBConnection();
    $sql = "SELECT CONCAT(FirstName, ' ', LastName) as FullName FROM freelancer WHERE FreelancerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $freelancer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $freelancer_name = $row['FullName'];
    }
    $stmt->close();
    $conn->close();
}

// Fetch client name if client_id is provided
$client_name = '';
if ($client_id) {
    $conn = getDBConnection();
    $sql = "SELECT CompanyName FROM client WHERE ClientID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $client_name = $row['CompanyName'];
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Create Agreement' ?></title>
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
                <h1>📋 Project Agreement</h1>
                <p>Create a professional agreement with real-time preview and digital signature</p>
            </div>
            <button type="button" onclick="window.history.back()" class="header-back-btn">
                ← Back to Messages
            </button>
        </div>
    </div>

    <div class="container">

        <!-- LIVE PREVIEW SECTION (Left) -->
        <div class="preview-box">
            <h2>👁️ Live Preview</h2>

            <!-- HEADER -->
            <div class="preview-header">
                <div class="preview-header-left">
                    <h3 id="pTitle">Project Agreement</h3>
                    <p id="pProjectLabel"><span class="empty">Project details...</span></p>
                </div>
                <div class="preview-header-right">
                    <span class="label">Offer from:</span>
                    <span class="value" id="pOfferer"><?php echo $freelancer_name ? htmlspecialchars($freelancer_name) : 'Freelancer Name'; ?></span>
                    <span class="label" style="margin-top: 12px;">To:</span>
                    <span class="value" id="pClient"><?php echo $client_name ? htmlspecialchars($client_name) : 'Client Name'; ?></span>
                    <span class="label" style="margin-top: 12px;">Date:</span>
                    <span class="value" id="pDate" style="color: #6b7280;">Today</span>
                </div>
            </div>

            <!-- INTRODUCTORY PARAGRAPH -->
            <div class="preview-section" style="background: #f9f9f9; border-left: 4px solid #1ab394; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <p style="margin: 0; line-height: 1.6; color: #333; font-size: 0.95rem;">
                    This Services Agreement shall become effective on date (the "Execution Date") and is subject to the terms and conditions stated below between <strong id="pIntroFreelancer">Freelancer Name</strong> (the "Service Provider") and <strong id="pIntroClient">Client Name</strong> (the "Client"), collectively referred to as the "Parties".
                </p>
            </div>

            <!-- SECTION 1: SCOPE OF WORK -->
            <div class="preview-section">
                <div class="section-number">
                    <span>1</span>
                    <div class="section-title">Scope of Work</div>
                </div>
                <div class="section-content">
                    <div id="pScope" class="empty">Describe the work scope...</div>
                </div>
            </div>

            <!-- SECTION 2: DELIVERABLES & TIMELINE -->
            <div class="preview-section">
                <div class="section-number">
                    <span>2</span>
                    <div class="section-title">Deliverables & Timeline</div>
                </div>
                <div class="section-content">
                    <div id="pDeliver" class="empty">Add deliverables and timeline...</div>
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
                            <span class="payment-amount" id="pPayment">RM 0.00</span>
                        </div>
                        <div id="pPaymentInfo" class="empty">Enter the payment amount...</div>
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
                    <div id="pTerms" class="empty">Define the terms...</div>
                </div>
            </div>

            <!-- SECTION 5: SIGNATURES -->
            <div class="preview-signature-section">
                <h3 style="text-align: center; margin-bottom: 20px; font-size: 1.1rem;">SIGNATURES</h3>
                <div style="display: flex; gap: 30px; justify-content: space-between;">
                    <!-- Freelancer/Contractor Signature -->
                    <div class="signature-block" style="flex: 1;">
                        <div class="signature-line">
                            <img id="pSignatureImage" style="display: none; max-width: 100%; height: auto;" />
                        </div>
                        <div class="signature-label" style="text-align: center; font-weight: 600; margin-top: 10px;">Contractor Signature</div>
                        <div class="signature-name" id="pSignatureName" style="text-align: center; margin-top: 5px;">___________________</div>
                        <div style="text-align: center; font-size: 0.9rem; color: #666; margin-top: 8px;">Date: ___________</div>
                    </div>

                    <!-- Client Signature -->
                    <div class="signature-block" style="flex: 1;">
                        <div class="signature-line" style="height: 80px; border: 1px dashed #ccc; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #fafafa;">
                            <span style="color: #999; font-size: 0.9rem;">[Client to Sign Here]</span>
                        </div>
                        <div class="signature-label" style="text-align: center; font-weight: 600; margin-top: 10px;">Client Signature</div>
                        <div style="text-align: center; margin-top: 5px;">___________________</div>
                        <div style="text-align: center; font-size: 0.9rem; color: #666; margin-top: 8px;">Date: ___________</div>
                    </div>
                </div>
            </div>
        </div>


        <!-- FORM SECTION (Right) -->
        <div class="form-box">
            <h2>✏️ Fill Agreement Details</h2>

            <form id="agreementForm" action="agreement_process.php" method="POST">

                <label for="title">Project Title *</label>
                <input type="text" name="project_title" id="title" placeholder="e.g., E-commerce Platform Development" required>

                <label for="projectDetail">Project Details *</label>
                <textarea name="project_detail" rows="2" id="projectDetail" placeholder="Brief description of the project..." maxlength="300" required></textarea>
                <div class="char-count"><span id="detailCount">0</span> / 300</div>

                <label for="scope">Scope of Work *</label>
                <textarea name="scope" rows="3" id="scope" placeholder="Describe what the freelancer will do..." maxlength="500" required></textarea>
                <div class="char-count"><span id="scopeCount">0</span> / 500</div>

                <label for="deliverables">Deliverables & Timeline *</label>
                <textarea name="deliverables" rows="3" id="deliverables" placeholder="List phases and deadlines..." maxlength="500" required></textarea>
                <div class="char-count"><span id="deliverCount">0</span> / 500</div>

                <label for="payment">Payment Amount (RM) *</label>
                <input type="number" name="payment" id="payment" placeholder="e.g., 5000" step="0.01" required>

                <label for="terms">Terms & Conditions *</label>
                <textarea name="terms" rows="4" id="terms" placeholder="Define the agreement terms..." required></textarea>

                <!-- SIGNATURE SECTION -->
                <div class="signature-section">
                    <h3>🖊️ Digital Signature</h3>
                    <p>Draw your signature below to electronically sign this agreement</p>

                    <div class="signature-container">
                        <canvas id="signaturePad"></canvas>
                    </div>

                    <div class="signature-buttons">
                        <button type="button" id="clearSignature">Clear</button>
                        <button type="button" class="sign-submit" id="confirmSignature">Confirm Signature</button>
                    </div>

                    <div class="signature-name-field">
                        <label for="freelancerName">Your Full Name (for signature) *</label>
                        <input type="text" name="freelancer_name" id="freelancerName" placeholder="Enter your full name" value="<?php echo $freelancer_name; ?>" required>
                    </div>

                    <?php if ($client_name): ?>
                        <div class="signature-name-field">
                            <label for="clientName">Client Name</label>
                            <input type="text" name="client_name" id="clientName" placeholder="Client name" value="<?php echo $client_name; ?>" readonly>
                        </div>
                        <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                    <?php endif; ?>

                    <div class="signature-note">
                        ✓ Your signature will be included in the final PDF agreement
                    </div>

                    <input type="hidden" name="signature_data" id="signatureData">
                </div>

                <button type="submit">✓ Create Agreement</button>
            </form>
        </div>

    </div>

    <!-- Signature Pad Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>

    <script>
        // SMOOTH LIVE PREVIEW UPDATES
        function updatePreview(id, previewID) {
            const value = document.getElementById(id).value.trim();
            const previewEl = document.getElementById(previewID);

            if (value === "") {
                previewEl.innerHTML = "<span class='empty'>Start typing to preview...</span>";
            } else {
                previewEl.innerHTML = `<span class="section-content">${value.replace(/\n/g, '<br>')}</span>`;
            }
        }

        // UPDATE PAYMENT PREVIEW WITH FORMATTING
        function updatePaymentPreview() {
            const payment = document.getElementById("payment").value;
            const previewEl = document.getElementById("pPayment");
            const infoEl = document.getElementById("pPaymentInfo");

            if (payment) {
                const formatted = parseFloat(payment).toFixed(2);
                previewEl.textContent = `RM ${formatted}`;
                infoEl.innerHTML = "<span class='section-content'>Payment will be released in milestones upon completion of deliverables.</span>";
            } else {
                previewEl.textContent = "RM 0.00";
                infoEl.innerHTML = "<span class='empty'>Enter the payment amount...</span>";
            }
        }

        // UPDATE PROJECT TITLE HEADER
        function updateTitleHeader() {
            const title = document.getElementById("title").value.trim();
            const titleEl = document.getElementById("pTitle");

            if (title) {
                titleEl.textContent = title;
            } else {
                titleEl.textContent = "Project Agreement";
            }
        }

        // UPDATE PROJECT DETAIL
        function updateProjectDetail() {
            const detail = document.getElementById("projectDetail").value.trim();
            const projectLabel = document.getElementById("pProjectLabel");

            if (detail) {
                projectLabel.innerHTML = `<span class="section-content">${detail}</span>`;
            } else {
                projectLabel.innerHTML = "<span class='empty'>Project details...</span>";
            }
        } // CHARACTER COUNTERS
        function updateCharCount(inputId, countId) {
            const input = document.getElementById(inputId);
            const count = document.getElementById(countId);
            count.textContent = input.value.length;
        }

        // INPUT EVENT LISTENERS
        document.getElementById("title").addEventListener("input", () => {
            updatePreview("title", "pTitle");
            updateTitleHeader();
        });

        document.getElementById("projectDetail").addEventListener("input", () => {
            updateProjectDetail();
            updateCharCount("projectDetail", "detailCount");
        });

        document.getElementById("scope").addEventListener("input", () => {
            updatePreview("scope", "pScope");
            updateCharCount("scope", "scopeCount");
        });

        document.getElementById("deliverables").addEventListener("input", () => {
            updatePreview("deliverables", "pDeliver");
            updateCharCount("deliverables", "deliverCount");
        });

        document.getElementById("payment").addEventListener("input", updatePaymentPreview);

        document.getElementById("terms").addEventListener("input", () => {
            updatePreview("terms", "pTerms");
        });

        // GET CURRENT DATE
        function getCurrentDate() {
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            return new Date().toLocaleDateString('en-US', options);
        }

        // INITIALIZE DATE AND NAMES
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById("pDate").textContent = getCurrentDate();

            // Update freelancer name in preview
            const freelancerInput = document.getElementById("freelancerName");
            if (freelancerInput) {
                const freelancerName = freelancerInput.value.trim();
                if (freelancerName) {
                    document.getElementById("pOfferer").textContent = freelancerName;
                    document.getElementById("pIntroFreelancer").textContent = freelancerName;
                }
                // Update preview when freelancer name changes
                freelancerInput.addEventListener("input", function() {
                    const name = this.value.trim();
                    if (name) {
                        document.getElementById("pOfferer").textContent = name;
                        document.getElementById("pIntroFreelancer").textContent = name;
                    } else {
                        document.getElementById("pOfferer").textContent = "Freelancer Name";
                        document.getElementById("pIntroFreelancer").textContent = "Freelancer Name";
                    }
                });
            }

            // Update client name in preview
            const clientInput = document.getElementById("clientName");
            if (clientInput) {
                const clientName = clientInput.value.trim();
                if (clientName) {
                    document.getElementById("pClient").textContent = clientName;
                    document.getElementById("pIntroClient").textContent = clientName;
                }
            }
        });

        // ===== SIGNATURE PAD INITIALIZATION =====
        let signaturePad;
        let isSignatureSigned = false;

        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById("signaturePad");

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

                const fullName = document.getElementById("freelancerName").value.trim();
                if (!fullName) {
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
                document.getElementById("pSignatureName").textContent = fullName;
            });

            // Update freelancer name in preview as typing
            document.getElementById("freelancerName").addEventListener("input", function() {
                if (isSignatureSigned) {
                    document.getElementById("pSignatureName").textContent = this.value.trim() || "___________________";
                }
            });
        });

        // FORM VALIDATION
        document.getElementById("agreementForm").addEventListener("submit", function(e) {
            e.preventDefault();

            // Clear previous error highlighting
            document.querySelectorAll(".form-error").forEach(el => {
                el.classList.remove("form-error");
            });

            const title = document.getElementById("title").value.trim();
            const projectDetail = document.getElementById("projectDetail").value.trim();
            const scope = document.getElementById("scope").value.trim();
            const deliverables = document.getElementById("deliverables").value.trim();
            const payment = document.getElementById("payment").value.trim();
            const terms = document.getElementById("terms").value.trim();
            const freelancerName = document.getElementById("freelancerName").value.trim();
            const signatureData = document.getElementById("signatureData").value;

            let hasErrors = false;

            if (!title) {
                document.getElementById("title").classList.add("form-error");
                hasErrors = true;
            }

            if (!projectDetail) {
                document.getElementById("projectDetail").classList.add("form-error");
                hasErrors = true;
            }

            if (!scope) {
                document.getElementById("scope").classList.add("form-error");
                hasErrors = true;
            }

            if (!deliverables) {
                document.getElementById("deliverables").classList.add("form-error");
                hasErrors = true;
            }

            if (!payment) {
                document.getElementById("payment").classList.add("form-error");
                hasErrors = true;
            }

            if (!terms) {
                document.getElementById("terms").classList.add("form-error");
                hasErrors = true;
            }

            if (!freelancerName) {
                document.getElementById("freelancerName").classList.add("form-error");
                hasErrors = true;
            }

            if (!signatureData) {
                document.getElementById("signaturePadContainer").classList.add("form-error");
                hasErrors = true;
            }

            if (hasErrors) {
                alert("Please fill in all required fields (highlighted in red) and provide a signature.");
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
                    <div style="font-size: 3rem; margin-bottom: 16px;">📋</div>
                    <h2 style="font-size: 1.5rem; color: #1a1a1a; margin-bottom: 12px; font-weight: 700;">Create Agreement?</h2>
                    <p style="color: #666; font-size: 1rem; line-height: 1.6; margin-bottom: 30px;">
                        Are you sure you want to create this agreement? This action will save the agreement and you can review, download, or send it to the other party.
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
                            ✓ Create Agreement
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