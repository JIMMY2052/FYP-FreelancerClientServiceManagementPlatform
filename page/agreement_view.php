<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if agreement data is in session
if (!isset($_SESSION['agreement'])) {
    $_SESSION['error'] = "No agreement data found. Please create a new agreement.";
    header("Location: agreement.php");
    exit();
}

// Get agreement data from session
$agreement = $_SESSION['agreement'];
$agreement_id = isset($agreement['agreement_id']) ? $agreement['agreement_id'] : uniqid('AGR-');

// Get freelancer and client names for display
$freelancer_name = "Freelancer Name";
$client_name = "Client Name";

if (isset($_SESSION['freelancer_id'])) {
    $freelancer_name = $_SESSION['freelancer_name'] ?? "Freelancer Name";
}

if (isset($_SESSION['client_id'])) {
    $client_name = $_SESSION['client_name'] ?? "Client Name";
}

// Get success message from redirect
$showSuccess = isset($_GET['status']) && $_GET['status'] === 'created';

// Get conversation ID from session or URL parameter for back navigation
$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : (isset($_SESSION['last_conversation_id']) ? $_SESSION['last_conversation_id'] : null);
$chat_id = isset($_GET['chat_id']) ? $_GET['chat_id'] : (isset($_SESSION['current_chat_id']) ? $_SESSION['current_chat_id'] : null);
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Agreement</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .header {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            justify-content: center;
        }

        .btn {
            padding: 12px 28px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #1ab394;
            color: white;
            box-shadow: 0 2px 8px rgba(26, 179, 148, 0.2);
        }

        .btn-primary:hover {
            background: #158a74;
            box-shadow: 0 4px 12px rgba(26, 179, 148, 0.3);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f0f1f3;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e0e2e8;
        }

        /* PREVIEW DESIGN - MATCHES agreement.php */
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

        .preview-section {
            margin-bottom: 32px;
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
            white-space: pre-wrap;
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

        @media (max-width: 640px) {
            .container {
                padding: 20px;
            }

            .preview-header {
                flex-direction: column;
            }

            .preview-header-right {
                text-align: left;
                margin-top: 16px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Agreement View</h1>
    </div>

    <?php if ($showSuccess): ?>
        <div class="success-message">
            ✓ Agreement created successfully! You can now download it as PDF or share it with the other party.
        </div>
    <?php endif; ?>

    <div class="container">

        <div class="button-group">
            <form method="POST" action="agreement_pdf.php" style="display: inline;">
                <input type="hidden" name="agreement_id" value="<?php echo $agreement_id; ?>">
                <button type="submit" class="btn btn-primary">📥 Download as PDF</button>
            </form>
            <button onclick="sendAgreementToChat()" class="btn btn-primary" id="sendChatBtn">💬 Send to Chat</button>
            <?php if ($chat_id): ?>
                <a href="messages.php?chatId=<?php echo urlencode($chat_id); ?>" class="btn btn-secondary">
                    ← Back to Conversation
                </a>
            <?php else: ?>
                <a href="agreement.php" class="btn btn-secondary">
                    ← Back to Create Agreement
                </a>
            <?php endif; ?>
        </div>

        <!-- PREVIEW SECTION -->
        <div class="preview-header">
            <div class="preview-header-left">
                <h3><?php echo htmlspecialchars($agreement['project_title']); ?></h3>
                <p><?php echo htmlspecialchars($agreement['project_detail']); ?></p>
            </div>
            <div class="preview-header-right">
                <span class="label">Offer from:</span>
                <span class="value"><?php echo htmlspecialchars($freelancer_name); ?></span>
                <span class="label" style="margin-top: 12px;">To:</span>
                <span class="value"><?php echo htmlspecialchars($client_name); ?></span>
                <span class="label" style="margin-top: 12px;">Date:</span>
                <span class="value"><?php echo date('F j, Y', strtotime($agreement['created_date'])); ?></span>
            </div>
        </div>

        <!-- SECTION 1: SCOPE OF WORK -->
        <div class="preview-section">
            <div class="section-number">
                <span>1</span>
                <div class="section-title">Scope of Work</div>
            </div>
            <div class="section-content">
                <?php echo htmlspecialchars($agreement['scope']); ?>
            </div>
        </div>

        <!-- SECTION 2: DELIVERABLES & TIMELINE -->
        <div class="preview-section">
            <div class="section-number">
                <span>2</span>
                <div class="section-title">Deliverables & Timeline</div>
            </div>
            <div class="section-content">
                <?php echo htmlspecialchars($agreement['deliverables']); ?>
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
                        <span class="payment-amount">RM <?php echo number_format($agreement['payment'], 2); ?></span>
                    </div>
                    <p style="color: #5a6b7d; font-size: 0.95rem;">Payment will be released in milestones upon completion of deliverables.</p>
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
                <?php echo htmlspecialchars($agreement['terms']); ?>
            </div>
        </div>

        <!-- SECTION 5: SIGNATURES -->
        <div class="preview-signature-section" style="margin-top: 40px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
            <h3 style="text-align: center; margin-bottom: 20px; font-size: 1.1rem;">SIGNATURES</h3>
            <div style="display: flex; gap: 30px; justify-content: space-between;">
                <!-- Freelancer/Contractor Signature -->
                <div class="signature-block" style="flex: 1;">
                    <div class="signature-line" style="height: 80px; border: 1px solid #ddd; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #fafafa; margin-bottom: 10px;">
                        <?php if (!empty($agreement['signature_filename'])): ?>
                            <img src="/uploads/signatures/<?php echo htmlspecialchars($agreement['signature_filename']); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;" />
                        <?php else: ?>
                            <span style="color: #999; font-size: 0.9rem;">[Signature]</span>
                        <?php endif; ?>
                    </div>
                    <div class="signature-label" style="text-align: center; font-weight: 600; margin-top: 10px;">Contractor Signature</div>
                    <div style="text-align: center; margin-top: 5px; font-size: 0.95rem; color: #1a1a1a;"><?php echo htmlspecialchars($agreement['freelancer_name'] ?? '___________________'); ?></div>
                    <div style="text-align: center; font-size: 0.9rem; color: #666; margin-top: 8px;">Date: <?php echo date('M d, Y', strtotime($agreement['created_date'])); ?></div>
                </div>

                <!-- Client Signature -->
                <div class="signature-block" style="flex: 1;">
                    <div class="signature-line" style="height: 80px; border: 1px dashed #ccc; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #fafafa; margin-bottom: 10px;">
                        <span style="color: #999; font-size: 0.9rem;">[Client to Sign Here]</span>
                    </div>
                    <div class="signature-label" style="text-align: center; font-weight: 600; margin-top: 10px;">Client Signature</div>
                    <div style="text-align: center; margin-top: 5px; font-size: 0.95rem; color: #1a1a1a;">___________________</div>
                    <div style="text-align: center; font-size: 0.9rem; color: #666; margin-top: 8px;">Date: ___________</div>
                </div>
            </div>
        </div>

        <!-- AGREEMENT INFO -->
        <div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid #e5e7eb; text-align: center;">
            <p style="color: #999; font-size: 0.9rem;">
                Agreement ID: <strong><?php echo $agreement_id; ?></strong> |
                Status: <strong>Created</strong> |
                Created: <strong><?php echo date('F j, Y', strtotime($agreement['created_date'])); ?></strong>
            </p>
        </div>

    </div>

    <script>
        // Send agreement PDF to chat
        function sendAgreementToChat() {
            const agreementId = <?php echo $agreement_id; ?>;
            const sendBtn = document.getElementById('sendChatBtn');

            // Show loading state
            sendBtn.disabled = true;
            const originalText = sendBtn.textContent;
            sendBtn.textContent = '⏳ Sending...';

            // Send request to send agreement to chat
            const chatId = '<?php echo $chat_id; ?>';
            fetch('send_agreement_to_chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'agreement_id=' + agreementId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Agreement sent to chat successfully! 📤');
                        // Redirect back to the conversation if we have a chat ID
                        if (chatId) {
                            window.location.href = 'messages.php?chatId=' + encodeURIComponent(chatId);
                        } else {
                            window.location.href = 'messages.php';
                        }
                    } else {
                        alert('Error: ' + data.message);
                        sendBtn.disabled = false;
                        sendBtn.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error sending agreement to chat');
                    sendBtn.disabled = false;
                    sendBtn.textContent = originalText;
                });
        }
    </script>

</body>

</html>