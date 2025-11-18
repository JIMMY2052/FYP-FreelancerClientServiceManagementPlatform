<!DOCTYPE html>
<html>

<head>
    <title>Create Agreement</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #ffffff;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .header {
            text-align: center;
            color: #333;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .header p {
            font-size: 1.1rem;
            color: #666;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: start;
        }

        .form-box {
            order: 1;
        }

        .preview-box {
            order: 2;
        }

        .form-box h2,
        .preview-box h2 {
            margin-bottom: 30px;
            color: #1a1a1a;
            font-size: 1.5rem;
            font-weight: 700;
        }

        label {
            font-weight: 600;
            display: block;
            margin-top: 25px;
            margin-bottom: 8px;
            color: #333;
            font-size: 0.95rem;
        }

        input,
        textarea {
            width: 100%;
            padding: 14px;
            margin-bottom: 8px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            resize: vertical;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #1ab394;
            box-shadow: 0 0 0 3px rgba(26, 179, 148, 0.1);
            background: #f9faff;
        }

        .char-count {
            font-size: 12px;
            color: #999;
            text-align: right;
            margin-top: 4px;
            margin-bottom: 0;
        }

        button {
            margin-top: 35px;
            width: 100%;
            padding: 16px;
            background: #1ab394;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(26, 179, 148, 0.2);
        }

        button:hover {
            background: #158a74;
            box-shadow: 0 4px 12px rgba(26, 179, 148, 0.3);
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0);
        }

        /* PREVIEW DESIGN - MODERN PROFESSIONAL */
        .preview-header {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .preview-header-left h3 {
            font-size: 1.8rem;
            color: #1a1a1a;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .preview-header-left p {
            font-size: 0.95rem;
            color: #7b8fa3;
        }

        .preview-header-right {
            text-align: right;
            font-size: 0.9rem;
        }

        .preview-header-right .label {
            color: #7b8fa3;
            display: block;
            margin-bottom: 4px;
            font-size: 0.85rem;
        }

        .preview-header-right .value {
            color: #1a1a1a;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }

        /* SECTION STYLES */
        .preview-section {
            margin-bottom: 32px;
            transition: all 0.3s ease;
        }

        .section-number {
            font-size: 1.3rem;
            color: #1a1a1a;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        .section-number span {
            display: inline-block;
            width: 32px;
            height: 32px;
            line-height: 32px;
            text-align: center;
            background: transparent;
            border-radius: 50%;
            margin-right: 12px;
            font-weight: 700;
            color: #4b5563;
        }

        .section-title {
            font-size: 1.05rem;
            color: #4b5563;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .section-content {
            color: #5a6b7d;
            font-size: 0.95rem;
            line-height: 1.7;
            word-wrap: break-word;
        }

        .section-content ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .section-content li {
            margin-bottom: 12px;
            padding-left: 24px;
            position: relative;
        }

        .section-content li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #3b82f6;
            font-weight: bold;
        }

        .payment-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            margin-top: 12px;
        }

        .payment-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .payment-label {
            color: #5a6b7d;
            font-weight: 500;
        }

        .payment-amount {
            font-size: 1.5rem;
            color: #1ab394;
            font-weight: 700;
        }

        .empty {
            color: #999;
            font-style: italic;
            display: block;
            padding: 12px 0;
        }

        .preview-value {
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
            display: block;
        }

        /* ANIMATION */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .preview-section {
            animation: slideIn 0.5s ease forwards;
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .preview-box {
                position: static;
                max-height: none;
                order: -1;
            }

            .header h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 20px 15px;
            }

            .form-box,
            .preview-box {
                padding: 25px;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .preview-header {
                flex-direction: column;
            }

            .preview-header-right {
                text-align: left;
                margin-top: 16px;
            }
        }
    </style>

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

                <button type="submit">✓ Create Agreement</button>
            </form>
        </div>

    </div>



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

        // FORM VALIDATION
        document.getElementById("agreementForm").addEventListener("submit", function(e) {
            const title = document.getElementById("title").value.trim();
            const projectDetail = document.getElementById("projectDetail").value.trim();
            const scope = document.getElementById("scope").value.trim();
            const deliverables = document.getElementById("deliverables").value.trim();
            const payment = document.getElementById("payment").value.trim();
            const terms = document.getElementById("terms").value.trim();

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
        });
    </script>

</body>

</html>