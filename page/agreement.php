<!DOCTYPE html>
<html>

<head>
    <title>Create Agreement</title>
    <link rel="stylesheet" href="../assets/css/agreement.css">
</head>

<body>

    <div class="header">
        <h1>Create Project Agreement</h1>
        <p>Fill in the details and preview your agreement in real-time</p>
    </div>

    <div class="container">

        <!-- LIVE PREVIEW SECTION (Left) -->
        <div class="preview-box">
            <h2>📋 Live Preview</h2>

            <!-- HEADER -->
            <div class="preview-header">
                <div class="preview-header-left">
                    <h3 id="pTitleHeader">Project Agreement</h3>
                    <p id="pProjectLabel"><span class="empty">Project details...</span></p>
                </div>
                <div class="preview-header-right">
                    <span class="label">Offer from:</span>
                    <span class="value" id="pOfferer">Freelancer Name</span>
                    <span class="label" style="margin-top: 12px;">To:</span>
                    <span class="value" id="pClient">Client Name</span>
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
                <div class="signature-block">
                    <div class="signature-line">
                        <img id="pSignatureImage" style="display: none;" />
                    </div>
                    <div class="signature-label">Freelancer Signature</div>
                    <div class="signature-name" id="pSignatureName">___________________</div>
                </div>
            </div>
        </div>


        <!-- FORM SECTION (Right) -->
        <div class="form-box">
            <h2>✏️ Agreement Details</h2>

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
                    <h3>Digital Signature</h3>
                    <p style="color: #666; font-size: 0.95rem; margin-bottom: 16px;">Sign below to electronically sign this agreement</p>

                    <div class="signature-container">
                        <canvas id="signaturePad"></canvas>
                    </div>

                    <div class="signature-buttons">
                        <button type="button" id="clearSignature">Clear</button>
                        <button type="button" class="sign-submit" id="confirmSignature">Confirm Signature</button>
                    </div>

                    <div class="signature-name-field">
                        <label for="freelancerName">Your Full Name (for signature) *</label>
                        <input type="text" name="freelancer_name" id="freelancerName" placeholder="Enter your full name" required>
                    </div>

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
            const titleEl = document.getElementById("pTitleHeader");
            const projectLabel = document.getElementById("pProjectLabel");

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

        // INITIALIZE DATE
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById("pDate").textContent = getCurrentDate();
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

                alert("Signature confirmed! Your signature will be included in the agreement.");
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
            const title = document.getElementById("title").value.trim();
            const projectDetail = document.getElementById("projectDetail").value.trim();
            const scope = document.getElementById("scope").value.trim();
            const deliverables = document.getElementById("deliverables").value.trim();
            const payment = document.getElementById("payment").value.trim();
            const terms = document.getElementById("terms").value.trim();
            const freelancerName = document.getElementById("freelancerName").value.trim();
            const signatureData = document.getElementById("signatureData").value;

            if (!title) {
                alert("Project title cannot be empty.");
                e.preventDefault();
                return;
            }

            if (!projectDetail) {
                alert("Project details cannot be empty.");
                e.preventDefault();
                return;
            }

            if (!scope) {
                alert("Scope of work cannot be empty.");
                e.preventDefault();
                return;
            }

            if (!deliverables) {
                alert("Deliverables & timeline cannot be empty.");
                e.preventDefault();
                return;
            }

            if (!payment) {
                alert("Payment amount cannot be empty.");
                e.preventDefault();
                return;
            }

            if (!terms) {
                alert("Terms & conditions cannot be empty.");
                e.preventDefault();
                return;
            }

            if (!freelancerName) {
                alert("Please enter your full name for signature.");
                e.preventDefault();
                return;
            }

            if (!signatureData) {
                alert("Please sign the agreement and confirm your signature.");
                e.preventDefault();
                return;
            }
        });
    </script>

</body>

</html>