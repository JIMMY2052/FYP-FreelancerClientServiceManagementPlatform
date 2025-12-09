// Additional behavior for messages.php page

// Job Quote functionality - only show for specific client
document.addEventListener('DOMContentLoaded', function () {
    const jobQuoteClose = document.getElementById('jobQuoteClose');
    const sendJobQuoteBtn = document.getElementById('sendJobQuoteBtn');
    const sendGigQuoteBtn = document.getElementById('sendGigQuoteBtn');
    const jobQuoteContainer = document.querySelector('.job-quote-container');

    if (jobQuoteClose) {
        jobQuoteClose.addEventListener('click', function () {
            if (jobQuoteContainer) {
                jobQuoteContainer.style.display = 'none';
            }

            // Inform server this quote has been dismissed for this session
            fetch('../page/messages_quote_dismiss.php', { method: 'POST', credentials: 'same-origin' })
                .catch(() => { });
        });
    }

    // Handle Job Quote Send
    if (sendJobQuoteBtn) {
        sendJobQuoteBtn.addEventListener('click', function () {
            if (!window.chatApp || !window.chatApp.currentChat) {
                alert('Please select a conversation first');
                return;
            }

            const titleEl = document.querySelector('.job-quote-item .job-quote-value');
            const budgetEl = document.querySelector('.job-quote-budget');
            const deadlineEl = document.querySelector('.job-quote-item:nth-child(3) .job-quote-value');
            const descEl = document.querySelector('.job-quote-text');

            const quotePayload = {
                type: 'job_quote',
                project_title: titleEl ? titleEl.textContent.trim() : '',
                budget: budgetEl ? budgetEl.textContent.trim() : '',
                deadline: deadlineEl ? deadlineEl.textContent.trim() : '',
                description: descEl ? descEl.textContent.trim() : ''
            };

            const messageInput = document.getElementById('messageInput');
            if (messageInput) {
                messageInput.value = JSON.stringify(quotePayload);
            }

            // Trigger normal send flow
            if (window.chatApp && typeof window.chatApp.sendMessage === 'function') {
                window.chatApp.sendMessage();
            }

            // After sending, hide the quote panel so it doesn't appear again
            if (jobQuoteContainer) {
                jobQuoteContainer.style.display = 'none';
            }

            // Inform server this quote has been used/dismissed for this session
            fetch('../page/messages_quote_dismiss.php', { method: 'POST', credentials: 'same-origin' })
                .catch(() => { });
        });
    }

    // Handle Gig Quote Send (similar to job but with different structure)
    if (sendGigQuoteBtn) {
        sendGigQuoteBtn.addEventListener('click', function () {
            if (!window.chatApp || !window.chatApp.currentChat) {
                alert('Please select a conversation first');
                return;
            }

            // Get gig quote details from the gig quote container
            const gigQuoteContainer = document.querySelectorAll('.job-quote-container')[document.querySelectorAll('.job-quote-container').length - 1]; // Get last container (gig quote)
            const titleEl = gigQuoteContainer.querySelector('.job-quote-item .job-quote-value');
            const priceEl = gigQuoteContainer.querySelector('.job-quote-budget');
            const deliveryEl = gigQuoteContainer.querySelector('.job-quote-item:nth-child(3) .job-quote-value');
            const descEl = gigQuoteContainer.querySelector('.job-quote-text');

            const quotePayload = {
                type: 'gig_quote',
                gig_title: titleEl ? titleEl.textContent.trim() : '',
                gig_price: priceEl ? priceEl.textContent.trim() : '',
                delivery_time: deliveryEl ? deliveryEl.textContent.trim() : '',
                description: descEl ? descEl.textContent.trim() : ''
            };

            const messageInput = document.getElementById('messageInput');
            if (messageInput) {
                messageInput.value = JSON.stringify(quotePayload);
            }

            // Trigger normal send flow
            if (window.chatApp && typeof window.chatApp.sendMessage === 'function') {
                window.chatApp.sendMessage();
            }

            // After sending, hide the gig quote panel
            if (gigQuoteContainer) {
                gigQuoteContainer.style.display = 'none';
            }

            // Inform server this quote has been used/dismissed for this session
            fetch('../page/messages_quote_dismiss.php', { method: 'POST', credentials: 'same-origin' })
                .catch(() => { });
        });
    }

    // Monitor conversation changes and hide quote if switching to different client
    if (window.showJobQuote && jobQuoteContainer) {
        const originalShowQuote = function () {
            if (window.chatApp && window.chatApp.currentOtherId === window.quoteClientId) {
                jobQuoteContainer.style.display = 'block';
            } else {
                jobQuoteContainer.style.display = 'none';
            }
        };

        const observer = new MutationObserver(function () {
            if (window.chatApp && window.chatApp.currentOtherId !== undefined) {
                originalShowQuote();
            }
        });

        const headerName = document.getElementById('headerName');
        if (headerName) {
            observer.observe(headerName, {
                childList: true,
                subtree: true,
                characterData: true
            });
        }

        originalShowQuote();
    }
});

// Auto-load conversation if coming from "Contact Me" button or "Message" button
document.addEventListener('DOMContentLoaded', function () {
    // File upload validation: only one file total per message
    let hasAttachedFile = false;
    const fileInput = document.getElementById('fileInput');
    const photoInput = document.getElementById('photoInput');
    const filePreview = document.getElementById('filePreview');
    const sendBtn = document.getElementById('sendBtn');
    const fileWarningModal = document.getElementById('fileWarningModal');
    const fileWarningText = document.getElementById('fileWarningText');
    const fileWarningClose = document.getElementById('fileWarningClose');

    function showFileWarning(message) {
        if (!fileWarningModal || !fileWarningText) return;
        fileWarningText.textContent = message;
        fileWarningModal.style.display = 'block';
    }

    if (fileWarningClose && fileWarningModal) {
        fileWarningClose.addEventListener('click', function () {
            fileWarningModal.style.display = 'none';
        });
        const overlay = fileWarningModal.querySelector('.modal-overlay');
        if (overlay) {
            overlay.addEventListener('click', function () {
                fileWarningModal.style.display = 'none';
            });
        }
    }

    function clearAttachmentState() {
        hasAttachedFile = false;
        if (fileInput) fileInput.value = '';
        if (photoInput) photoInput.value = '';
        if (filePreview) filePreview.innerHTML = '';
    }

    if (sendBtn) {
        sendBtn.addEventListener('click', function () {
            // After sending, allow next file
            setTimeout(clearAttachmentState, 100);
        });
    }

    function handleFileSelection(input) {
        if (!input || !input.files) return;

        const file = input.files[0];
        if (!file) return;

        // If a file is already attached, block second attachment
        if (hasAttachedFile) {
            input.value = '';
            showFileWarning('Only one attachment is allowed per message. Please send this message before adding another file.');
            return;
        }

        // Simple client-side size validation (match 10MB server rule)
        const maxSizeBytes = 10 * 1024 * 1024;
        if (file.size > maxSizeBytes) {
            input.value = '';
            showFileWarning('The selected file exceeds the 10MB size limit.');
            return;
        }

        if (filePreview) {
            // Always allow only one file preview at a time
            filePreview.innerHTML = '';
            const item = document.createElement('div');
            item.className = 'file-preview-item';
            const nameSpan = document.createElement('span');
            nameSpan.textContent = file.name;
            item.appendChild(nameSpan);
            filePreview.appendChild(item);
        }

        // Mark that this message already has an attachment
        hasAttachedFile = true;
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            handleFileSelection(fileInput);
        });
    }

    if (photoInput) {
        photoInput.addEventListener('change', function () {
            handleFileSelection(photoInput);
        });
    }

    if (window.autoLoadConversation) {
        const targetUserId = window.targetClientId || window.targetFreelancerId;

        if (targetUserId) {
            const maxAttempts = 20; // Try for up to 10 seconds (20 * 500ms)
            let attempts = 0;

            const autoLoadInterval = setInterval(function () {
                attempts++;

                const conversationItem = document.querySelector(`[data-user-id="${targetUserId}"]`);

                if (conversationItem) {
                    clearInterval(autoLoadInterval);
                    conversationItem.click();
                    console.log('Auto-loaded conversation with user ID:', targetUserId);
                } else if (attempts >= maxAttempts) {
                    clearInterval(autoLoadInterval);
                    console.log('Could not find conversation with user ID:', targetUserId);
                }
            }, 500);
        }
    }
});

// View Profile functionality
document.addEventListener('DOMContentLoaded', function () {
    const viewProfileBtn = document.getElementById('viewProfileBtn');

    if (viewProfileBtn) {
        viewProfileBtn.addEventListener('click', function () {
            if (window.chatApp && window.chatApp.currentChat) {
                const currentUserType = window.currentUserData.type;
                const chatOtherUserId = window.chatApp.currentOtherId;
                const chatOtherUserType = window.chatApp.currentOtherType;

                if (chatOtherUserId && chatOtherUserType) {
                    if (currentUserType === 'freelancer' && chatOtherUserType === 'client') {
                        window.location.href = 'view_client_profile.php?id=' + chatOtherUserId + '&source=messages';
                    } else if (currentUserType === 'client' && chatOtherUserType === 'freelancer') {
                        window.location.href = 'view_freelancer_profile.php?id=' + chatOtherUserId + '&source=messages';
                    }
                } else {
                    alert('Please select a conversation first');
                }
            } else {
                alert('Please select a conversation first');
            }
        });
    }
});

// Search conversations input validation
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('chatSearch');

    if (!searchInput) return;

    // Clean as user types
    searchInput.addEventListener('input', function () {
        if (this.value.startsWith(' ')) {
            this.value = this.value.trimStart();
        }

        const maxLen = 50;
        if (this.value.length > maxLen) {
            this.value = this.value.slice(0, maxLen);
        }
    });

    searchInput.addEventListener('blur', function () {
        this.value = this.value.trim();
    });

    searchInput.addEventListener('change', function () {
        const alnum = this.value.replace(/[^a-zA-Z0-9\s]/g, '');
        if (!alnum.trim()) {
            this.value = '';
        }
    });
});
