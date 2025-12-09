// Chat Application JavaScript with File Upload Support

class ChatApp {
    constructor() {
        this.currentUser = null;
        this.currentChat = null;
        this.selectedFiles = [];
        this.messageRefreshInterval = null;
        this.lastMessageSentTime = 0; // Track last message send time
        this.messageDelay = 2000; // 2 second delay between messages
        this.maxFileSize = 10 * 1024 * 1024; // 10MB
        this.allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        this.lastLoadedChat = null; // Track which chat was last loaded to clear messages on switch
        this.init();
    }

    async init() {
        // Get current user info
        this.currentUser = window.currentUserData;

        // Initialize event listeners
        this.setupEventListeners();

        // Load chat list
        await this.loadChatList();

        // Start auto-refresh
        this.startMessageRefresh();
    }

    setupEventListeners() {
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const chatSidebar = document.querySelector('.chat-sidebar');
        
        if (sidebarToggle && chatSidebar) {
            sidebarToggle.addEventListener('click', () => {
                chatSidebar.classList.toggle('mobile-hidden');
                sidebarToggle.classList.toggle('active');
            });
        }

        // Message input
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const attachMenuBtn = document.getElementById('attachMenuBtn');
        const attachmentMenu = document.getElementById('attachmentMenu');
        const uploadPhotoBtn = document.getElementById('uploadPhotoBtn');
        const uploadFileBtn = document.getElementById('uploadFileBtn');
        const fileInput = document.getElementById('fileInput');
        const photoInput = document.getElementById('photoInput');

        if (sendBtn) {
            sendBtn.addEventListener('click', () => this.sendMessage());
        }

        if (messageInput) {
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            // Auto-resize textarea
            messageInput.addEventListener('input', () => {
                messageInput.style.height = 'auto';
                messageInput.style.height = Math.min(messageInput.scrollHeight, 100) + 'px';
            });
        }

        // Attachment menu toggle
        if (attachMenuBtn) {
            attachMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (attachmentMenu) {
                    attachmentMenu.classList.toggle('show');
                }
            });
        }

        // Upload photo option
        if (uploadPhotoBtn) {
            uploadPhotoBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (photoInput) {
                    photoInput.click();
                    attachmentMenu?.classList.remove('show');
                }
            });
        }

        // Upload file option
        if (uploadFileBtn) {
            uploadFileBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (fileInput) {
                    fileInput.click();
                    attachmentMenu?.classList.remove('show');
                }
            });
        }

        // Agreement option
        const agreementBtn = document.getElementById('agreementBtn');
        if (agreementBtn) {
            agreementBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (this.currentChat) {
                    // Get the other user's name from the header
                    const otherUserName = document.getElementById('headerName')?.textContent || '';
                    const otherUserId = this.currentOtherId;

                    // Build agreement URL with freelancer name and client ID
                    const currentUserType = window.currentUserData.type;
                    let agreementUrl = 'agreement.php?';

                    if (currentUserType === 'freelancer') {
                        // Freelancer creating agreement - pass freelancer ID and client ID
                        agreementUrl += `freelancer_id=${window.currentUserData.id}&client_id=${otherUserId}`;
                    } else {
                        // Client creating agreement - pass client ID and freelancer ID
                        agreementUrl += `client_id=${window.currentUserData.id}&freelancer_id=${otherUserId}`;
                    }

                    window.location.href = agreementUrl;
                    attachmentMenu?.classList.remove('show');
                } else {
                    alert('Please select a conversation first');
                }
            });
        }

        // Handle photo input
        if (photoInput) {
            photoInput.addEventListener('change', (e) => this.handleFileSelect(e));
        }

        // Handle file input
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        }

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (attachmentMenu && !attachmentMenu.contains(e.target) && !attachMenuBtn?.contains(e.target)) {
                attachmentMenu.classList.remove('show');
            }
        });

        // Search
        const searchInput = document.getElementById('chatSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.filterChats(e.target.value));
        }

        // Modal close
        const modal = document.getElementById('imageModal');
        const modalClose = document.getElementById('modalClose');
        if (modalClose) {
            modalClose.addEventListener('click', () => {
                modal.classList.remove('show');
            });
        }
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
        }

        // View Profile
        const viewProfileBtn = document.getElementById('viewProfileBtn');
        if (viewProfileBtn) {
            viewProfileBtn.addEventListener('click', () => this.viewProfile());
        }
    }

    handleFileSelect(event) {
        const inputFiles = Array.from(event.target.files || []);
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];

        if (inputFiles.length === 0) {
            return;
        }

        // Only allow one file in total for a message
        if (this.selectedFiles.length > 0) {
            // Use centered modal if available, else alert
            const fileWarningModal = document.getElementById('fileWarningModal');
            const fileWarningText = document.getElementById('fileWarningText');
            if (fileWarningModal && fileWarningText) {
                fileWarningText.textContent = 'You have already attached a file. Please send this message before attaching another file.';
                fileWarningModal.style.display = 'block';
            } else {
                alert('You have already attached a file. Please send this message before attaching another file.');
            }
            event.target.value = '';
            return;
        }

        // Take only the first selected file
        const file = inputFiles[0];

        // Validate file size
        if (file.size > this.maxFileSize) {
            const fileWarningModal = document.getElementById('fileWarningModal');
            const fileWarningText = document.getElementById('fileWarningText');
            if (fileWarningModal && fileWarningText) {
                fileWarningText.textContent = 'The selected file exceeds the 10MB size limit.';
                fileWarningModal.style.display = 'block';
            } else {
                alert(`File ${file.name} is too large. Maximum size is 10MB.`);
            }
            event.target.value = '';
            return;
        }

        // Get file extension
        const fileExt = file.name.split('.').pop().toLowerCase();

        // Validate file type - check both MIME type and extension
        const mimeValid = this.allowedFileTypes.includes(file.type);
        const extValid = allowedExtensions.includes(fileExt);

        if (!(mimeValid || extValid)) {
            const fileWarningModal = document.getElementById('fileWarningModal');
            const fileWarningText = document.getElementById('fileWarningText');
            if (fileWarningModal && fileWarningText) {
                fileWarningText.textContent = 'File type not allowed. Allowed: images (jpg, png, gif), PDF, Word documents.';
                fileWarningModal.style.display = 'block';
            } else {
                alert(`File type not allowed: ${file.type}. Allowed: images (jpg, png, gif), PDF, Word documents.`);
            }
            event.target.value = '';
            return;
        }

        // Store exactly one file
        this.selectedFiles = [file];

        this.displayFilePreview();
        event.target.value = ''; // Reset input
    }

    displayFilePreview() {
        const previewContainer = document.getElementById('filePreview');
        previewContainer.innerHTML = '';

        this.selectedFiles.forEach((file, index) => {
            const preview = document.createElement('div');
            preview.className = 'file-preview';

            let previewHTML = '';
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'file-preview-img';
                    preview.insertBefore(img, preview.firstChild);
                };
                reader.readAsDataURL(file);
            } else {
                const icon = this.getFileIcon(file.type);
                preview.innerHTML = `<div class="file-icon">${icon}</div>`;
            }

            const filename = document.createElement('span');
            filename.className = 'file-preview-name';
            filename.textContent = file.name;
            preview.appendChild(filename);

            const removeBtn = document.createElement('button');
            removeBtn.className = 'file-preview-remove';
            removeBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
            removeBtn.type = 'button';
            removeBtn.addEventListener('click', () => this.removeFile(index));
            preview.appendChild(removeBtn);

            previewContainer.appendChild(preview);
        });
    }

    removeFile(index) {
        this.selectedFiles.splice(index, 1);
        this.displayFilePreview();
    }

    getFileIcon(mimeType) {
        if (mimeType.startsWith('image/')) {
            return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>';
        }
        if (mimeType.includes('pdf')) {
            return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><text x="9" y="17" font-size="8" fill="currentColor">PDF</text></svg>';
        }
        if (mimeType.includes('word') || mimeType.includes('document')) {
            return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>';
        }
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>';
    }

    async loadChatList() {
        try {
            const response = await fetch('../page/get_chat_list.php');
            const chats = await response.json();

            const chatList = document.getElementById('chatList');
            chatList.innerHTML = '';

            if (chats.length === 0) {
                chatList.innerHTML = '<li class="loading-state"><p>No conversations yet</p></li>';
                return;
            }

            chats.forEach((chat, index) => {
                const chatItem = document.createElement('li');
                chatItem.className = 'chat-item';
                chatItem.setAttribute('data-chat-id', chat.id);
                chatItem.setAttribute('data-conversation-id', chat.conversationId);
                chatItem.setAttribute('data-user-id', chat.userId);
                if (this.currentChat === chat.id) {
                    chatItem.classList.add('active');
                }

                const initials = this.getInitials(chat.name);
                const time = this.formatTime(chat.lastMessageTime);

                // Process last message preview - show only quote type if it's JSON
                let messagePreview = chat.lastMessage;
                try {
                    const parsedMsg = JSON.parse(chat.lastMessage);
                    if (parsedMsg && typeof parsedMsg === 'object' && parsedMsg.type) {
                        if (parsedMsg.type === 'job_quote') {
                            messagePreview = '💼 Project Quote';
                        } else if (parsedMsg.type === 'gig_quote') {
                            messagePreview = '✨ Gig Quote';
                        } else if (parsedMsg.type === 'agreement') {
                            messagePreview = '📋 Agreement';
                        }
                    }
                } catch (e) {
                    // Not JSON, use as-is
                }

                // Create avatar HTML - with profile picture if available
                let avatarHTML = `<div class="chat-item-avatar">${initials}</div>`;
                if (chat.profilePicture) {
                    avatarHTML = `
                        <div class="chat-item-avatar" style="background-color: transparent; overflow: hidden;">
                            <img src="/${this.escapeHtml(chat.profilePicture)}" 
                                 alt="${this.escapeHtml(chat.name)}" 
                                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;"
                                 onerror="this.style.display='none'; this.parentElement.textContent='${initials}';">
                        </div>
                    `;
                }

                chatItem.innerHTML = `
                    ${avatarHTML}
                    <div class="chat-item-content">
                        <h3 class="chat-item-name">${this.escapeHtml(chat.name)}</h3>
                        <p class="chat-item-preview">${messagePreview}</p>
                    </div>
                    <div class="chat-item-time">${time}</div>
                `;

                chatItem.addEventListener('click', (e) => this.selectChat(chat.id, chat.name, chat.userId, chat.userType, e));
                chatList.appendChild(chatItem);

                // Auto-select the first conversation if none is selected
                if (index === 0 && !this.currentChat) {
                    setTimeout(() => {
                        this.selectChat(chat.id, chat.name, chat.userId, chat.userType, null);
                    }, 100);
                }
            });
        } catch (error) {
            console.error('Error loading chat list:', error);
        }
    }

    async selectChat(chatId, chatName, userId, userType, event) {
        this.currentChat = chatId;

        // Store receiver info directly from chat list (now provided as userId and userType)
        this.currentOtherId = userId || null;
        this.currentOtherType = userType || null;

        // Fallback: if not provided, parse from new compact format (c1, f2, etc.) or legacy format
        if (!this.currentOtherId || !this.currentOtherType) {
            if (typeof chatId === 'string') {
                // Try new compact format: c1, f2, etc.
                if (chatId.length > 1 && (chatId[0] === 'c' || chatId[0] === 'f')) {
                    this.currentOtherType = chatId[0] === 'c' ? 'client' : 'freelancer';
                    this.currentOtherId = parseInt(chatId.substring(1), 10) || null;
                }
                // Try legacy format: client_5, freelancer_3
                else if (chatId.indexOf('_') !== -1) {
                    const parts = chatId.split('_', 2);
                    this.currentOtherType = parts[0];
                    this.currentOtherId = parseInt(parts[1], 10) || null;
                }
            }
        }

        // Update header
        const headerName = document.getElementById('headerName');
        const headerStatus = document.getElementById('headerStatus');
        const headerAvatar = document.getElementById('headerAvatar');

        if (headerName) {
            headerName.textContent = chatName;
        }
        if (headerStatus) {
            headerStatus.textContent = '';
        }
        if (headerAvatar) {
            // Fetch and display profile picture
            this.loadHeaderProfilePicture(headerAvatar, this.currentOtherId, this.currentOtherType, chatName);
        }

        // Update active state
        document.querySelectorAll('.chat-item').forEach(item => {
            item.classList.remove('active');
        });

        // Set active based on chatId if event is null, or use event.currentTarget
        if (event && event.currentTarget) {
            event.currentTarget.classList.add('active');
        } else {
            // Find and activate the chat item by chat ID
            const chatItems = document.querySelectorAll('.chat-item');
            chatItems.forEach(item => {
                const itemChatId = item.getAttribute('data-chat-id') || item.textContent;
                if (itemChatId === chatId) {
                    item.classList.add('active');
                }
            });
        }

        // Load messages
        await this.loadMessages();
    }

    async loadMessages() {
        if (!this.currentChat) return;

        try {
            const response = await fetch(`../page/get_messages.php?chatId=${this.currentChat}`);
            const messages = await response.json();

            const container = document.getElementById('messagesContainer');

            // If we switched to a different chat, clear the container
            if (this.lastLoadedChat !== this.currentChat) {
                container.innerHTML = '';
                this.lastLoadedChat = this.currentChat;
            }

            // Store current message count to detect new messages
            const currentMessageCount = container.querySelectorAll('.message-group').length;

            // If no messages, show empty state
            if (messages.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">💬</div>
                        <h3 class="empty-state-title">Start the conversation</h3>
                        <p class="empty-state-text">Send your first message to begin chatting</p>
                    </div>
                `;
                return;
            }

            // If this is the first load, render all messages
            if (currentMessageCount === 0) {
                container.innerHTML = '';
                messages.forEach(msg => {
                    // Build composite ID for current user to compare with senderId
                    const userPrefix = this.currentUser.type === 'freelancer' ? 'f' : 'c';
                    const compositeUserId = userPrefix + this.currentUser.id;
                    const isSent = msg.senderId === compositeUserId;
                    const messageGroup = document.createElement('div');
                    messageGroup.className = `message-group ${isSent ? 'sent' : 'received'}`;

                    const time = this.formatMessageTime(msg.timestamp);

                    let contentHTML = '';
                    if (msg.content || msg.attachmentPath) {
                        contentHTML = '<div class="message-bubble">';
                        if (msg.content) {
                            // Check if content is JSON (agreement message or job quote)
                            let parsedContent = null;
                            try {
                                parsedContent = JSON.parse(msg.content);
                            } catch (e) {
                                // Not JSON, treat as regular message
                            }

                            if (parsedContent && parsedContent.type === 'agreement') {
                                // Render agreement message
                                contentHTML += `
                                    <div class="message-content agreement-message">
                                        <div class="agreement-header">📋 Project Agreement</div>
                                        <div class="agreement-details">
                                            <p><strong>Project:</strong> ${this.escapeHtml(parsedContent.project_title)}</p>
                                            <p><strong>Amount:</strong> RM ${parseFloat(parsedContent.payment_amount).toFixed(2)}</p>
                                            <p><strong>Status:</strong> ${this.escapeHtml(parsedContent.status)}</p>
                                        </div>
                                        <form method="POST" action="/page/agreement_pdf.php" style="display: inline;">
                                            <input type="hidden" name="agreement_id" value="${parsedContent.agreement_id}">
                                            <button type="submit" class="btn-agreement-download">📥 Download PDF</button>
                                        </form>
                                        <a href="/page/agreement_view.php?agreement_id=${parsedContent.agreement_id}" class="btn-agreement-view">👁️ View Details</a>
                                    </div>
                                `;
                            } else if (parsedContent && parsedContent.type === 'job_quote') {
                                // Render job quote card
                                contentHTML += `
                                    <div class="message-content job-quote-card">
                                        <div class="job-quote-card-header">💼 Project Quote</div>
                                        <div class="job-quote-card-body">
                                            <p><strong>Project:</strong> ${this.escapeHtml(parsedContent.project_title || '')}</p>
                                            <p><strong>Budget:</strong> ${this.escapeHtml(parsedContent.budget || '')}</p>
                                            <p><strong>Deadline:</strong> ${this.escapeHtml(parsedContent.deadline || '')}</p>
                                            <p><strong>Description:</strong><br>${this.escapeHtml(parsedContent.description || '')}</p>
                                        </div>
                                    </div>
                                `;
                            } else if (parsedContent && parsedContent.type === 'gig_quote') {
                                // Render gig quote card
                                contentHTML += `
                                    <div class="message-content gig-quote-card">
                                        <div class="gig-quote-card-header">✨ Gig Quote</div>
                                        <div class="gig-quote-card-body">
                                            <p><strong>Gig:</strong> ${this.escapeHtml(parsedContent.gig_title || '')}</p>
                                            <p><strong>Price:</strong> ${this.escapeHtml(parsedContent.gig_price || '')}</p>
                                            <p><strong>Delivery Time:</strong> ${this.escapeHtml(parsedContent.delivery_time || '')}</p>
                                            <p><strong>Description:</strong><br>${this.escapeHtml(parsedContent.description || '')}</p>
                                        </div>
                                    </div>
                                `;
                            } else {
                                // Regular message
                                contentHTML += `<div class="message-content">${this.escapeHtml(msg.content)}</div>`;
                            }
                        }
                        if (msg.attachmentPath) {
                            contentHTML += this.renderAttachment(msg.attachmentPath, msg.attachmentType, isSent, msg.status);
                        }
                        contentHTML += `<div class="message-time">${time}</div></div>`;
                    }

                    messageGroup.innerHTML = contentHTML;
                    container.appendChild(messageGroup);
                });
            } else if (messages.length > currentMessageCount) {
                // Only add new messages (avoid re-rendering existing ones)
                const newMessages = messages.slice(currentMessageCount);
                newMessages.forEach(msg => {
                    // Build composite ID for current user to compare with senderId
                    const userPrefix = this.currentUser.type === 'freelancer' ? 'f' : 'c';
                    const compositeUserId = userPrefix + this.currentUser.id;
                    const isSent = msg.senderId === compositeUserId;
                    const messageGroup = document.createElement('div');
                    messageGroup.className = `message-group ${isSent ? 'sent' : 'received'}`;

                    const time = this.formatMessageTime(msg.timestamp);

                    let contentHTML = '';
                    if (msg.content || msg.attachmentPath) {
                        contentHTML = '<div class="message-bubble">';
                        if (msg.content) {
                            // Check if content is JSON (agreement message or job quote)
                            let parsedContent = null;
                            try {
                                parsedContent = JSON.parse(msg.content);
                            } catch (e) {
                                // Not JSON, treat as regular message
                            }

                            if (parsedContent && parsedContent.type === 'agreement') {
                                // Render agreement message
                                contentHTML += `
                                    <div class="message-content agreement-message">
                                        <div class="agreement-header">📋 Project Agreement</div>
                                        <div class="agreement-details">
                                            <p><strong>Project:</strong> ${this.escapeHtml(parsedContent.project_title)}</p>
                                            <p><strong>Amount:</strong> RM ${parseFloat(parsedContent.payment_amount).toFixed(2)}</p>
                                            <p><strong>Status:</strong> ${this.escapeHtml(parsedContent.status)}</p>
                                        </div>
                                        <form method="POST" action="/page/agreement_pdf.php" style="display: inline;">
                                            <input type="hidden" name="agreement_id" value="${parsedContent.agreement_id}">
                                            <button type="submit" class="btn-agreement-download">📥 Download PDF</button>
                                        </form>
                                        <a href="/page/agreement_view.php?agreement_id=${parsedContent.agreement_id}" class="btn-agreement-view">👁️ View Details</a>
                                    </div>
                                `;
                            } else if (parsedContent && parsedContent.type === 'job_quote') {
                                // Render job quote card
                                contentHTML += `
                                    <div class="message-content job-quote-card">
                                        <div class="job-quote-card-header">💼 Project Quote</div>
                                        <div class="job-quote-card-body">
                                            <p><strong>Project:</strong> ${this.escapeHtml(parsedContent.project_title || '')}</p>
                                            <p><strong>Budget:</strong> ${this.escapeHtml(parsedContent.budget || '')}</p>
                                            <p><strong>Deadline:</strong> ${this.escapeHtml(parsedContent.deadline || '')}</p>
                                            <p><strong>Description:</strong><br>${this.escapeHtml(parsedContent.description || '')}</p>
                                        </div>
                                    </div>
                                `;
                            } else if (parsedContent && parsedContent.type === 'gig_quote') {
                                // Render gig quote card
                                contentHTML += `
                                    <div class="message-content gig-quote-card">
                                        <div class="gig-quote-card-header">✨ Gig Quote</div>
                                        <div class="gig-quote-card-body">
                                            <p><strong>Gig:</strong> ${this.escapeHtml(parsedContent.gig_title || '')}</p>
                                            <p><strong>Price:</strong> ${this.escapeHtml(parsedContent.gig_price || '')}</p>
                                            <p><strong>Delivery:</strong> ${this.escapeHtml(parsedContent.delivery_time || '')}</p>
                                            <p><strong>Description:</strong><br>${this.escapeHtml(parsedContent.description || '')}</p>
                                        </div>
                                    </div>
                                `;
                            } else {
                                // Regular message
                                contentHTML += `<div class="message-content">${this.escapeHtml(msg.content)}</div>`;
                            }
                        }
                        if (msg.attachmentPath) {
                            contentHTML += this.renderAttachment(msg.attachmentPath, msg.attachmentType, isSent, msg.status);
                        }
                        contentHTML += `<div class="message-time">${time}</div></div>`;
                    }

                    messageGroup.innerHTML = contentHTML;
                    container.appendChild(messageGroup);
                });
            }

            // Scroll to bottom only if user is already near bottom
            if (container) {
                const distanceFromBottom = container.scrollHeight - container.scrollTop - container.clientHeight;
                // If user is within 100px of bottom, keep auto-scrolling
                if (distanceFromBottom < 100) {
                    container.scrollTop = container.scrollHeight;
                }
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    renderAttachment(filePath, fileType, isSent, status = null) {
        if (fileType && fileType.startsWith('image/')) {
            return `
                <div class="attachment-container">
                    <img src="${filePath}" class="attachment-image" onclick="window.chatApp.openImageModal('${filePath}')">
                </div>
            `;
        } else {
            const icon = this.getFileIcon(fileType);
            const fileName = filePath.split('/').pop();

            // If status is 'to_accept' and it's a PDF (agreement), show with action buttons
            if (status === 'to_accept' && fileType === 'application/pdf' && !isSent) {
                return `
                    <div class="attachment-container">
                        <div class="agreement-actions">
                            <a href="${filePath}" download class="attachment-file">
                                <span class="attachment-icon">${icon}</span>
                                <span>${this.escapeHtml(fileName)}</span>
                            </a>
                            <div class="agreement-buttons">
                                <a href="../page/freelancer_agreement_approval.php?agreement_id=" class="btn-accept-sign">✓ Accept & Sign</a>
                                <button type="button" class="btn-decline" onclick="alert('Agreement declined')">✕ Decline</button>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                return `
                    <div class="attachment-container">
                        <a href="${filePath}" download class="attachment-file">
                            <span class="attachment-icon">${icon}</span>
                            <span>${this.escapeHtml(fileName)}</span>
                        </a>
                    </div>
                `;
            }
        }
    }

    openImageModal(imagePath) {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        modalImage.src = imagePath;
        modal.classList.add('active');
    }

    async sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const content = messageInput.value.trim();

        if (!content && this.selectedFiles.length === 0) {
            alert('Please enter a message or select a file');
            return;
        }

        if (!this.currentChat) {
            alert('Please select a conversation');
            return;
        }

        // Check if enough time has passed since last message
        const now = Date.now();
        const timeSinceLastMessage = now - this.lastMessageSentTime;

        if (timeSinceLastMessage < this.messageDelay) {
            const waitTime = Math.ceil((this.messageDelay - timeSinceLastMessage) / 1000);
            alert(`Please wait ${waitTime} second(s) before sending another message`);
            return;
        }

        try {
            const formData = new FormData();
            formData.append('chatId', this.currentChat);
            // Send explicit receiver fields to avoid server-side parsing errors
            if (this.currentOtherId) {
                formData.append('receiverId', this.currentOtherId);
            }
            if (this.currentOtherType) {
                formData.append('receiverType', this.currentOtherType);
            }
            // Debug: log what we're sending
            console.log('[sendMessage] Sending to chatId=', this.currentChat, 'receiverId=', this.currentOtherId, 'receiverType=', this.currentOtherType);
            formData.append('content', content);

            // Add files
            this.selectedFiles.forEach((file, index) => {
                console.log(`[sendMessage] Adding file ${index}: name=${file.name}, type=${file.type}, size=${file.size}`);
                formData.append('files[]', file);
            });

            console.log('[sendMessage] Total files:', this.selectedFiles.length);
            console.log('[sendMessage] Sending to:', '../page/send_message.php');

            const response = await fetch('../page/send_message.php', {
                method: 'POST',
                body: formData
            });

            console.log('[sendMessage] Response status:', response.status);
            let result;
            try {
                result = await response.json();
            } catch (jsonError) {
                console.error('[sendMessage] Failed to parse JSON response:', jsonError);
                const text = await response.text();
                console.error('[sendMessage] Raw response:', text);
                alert('Server error: Invalid response format');
                return;
            }

            console.log('[sendMessage] Response result:', result);

            if (result.success) {
                // Record when message was sent
                this.lastMessageSentTime = Date.now();

                messageInput.value = '';
                messageInput.style.height = 'auto';
                this.selectedFiles = [];
                this.displayFilePreview();

                // Reload messages instead of full page reload to preserve scroll position
                await this.loadMessages();
            } else {
                const errorMsg = result.error || 'Unknown error';
                console.error('[sendMessage] Server error:', errorMsg);
                alert('Error: ' + errorMsg);
            }
        } catch (error) {
            console.error('Error sending message:', error);
            console.error('Error details:', error.message);
            alert('Error sending message: ' + (error.message || 'Unknown error'));
        }
    }

    filterChats(query) {
        const chatItems = document.querySelectorAll('.chat-item');
        const lowerQuery = query.toLowerCase();

        chatItems.forEach(item => {
            const name = item.querySelector('.chat-item-name').textContent.toLowerCase();
            const preview = item.querySelector('.chat-item-preview').textContent.toLowerCase();

            if (name.includes(lowerQuery) || preview.includes(lowerQuery)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    startMessageRefresh() {
        // Auto-refresh messages every 2 seconds
        this.messageRefreshInterval = setInterval(() => {
            if (this.currentChat) {
                this.loadMessages();
            }
        }, 2000);
    }

    getInitials(name) {
        if (!name) return '?';
        return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    }

    async loadHeaderProfilePicture(headerAvatarElement, userId, userType, userName) {
        if (!userId || !userType || !headerAvatarElement) return;

        try {
            const response = await fetch(`../page/get_user_profile.php?user_id=${userId}&user_type=${userType}`);
            const data = await response.json();

            if (data.success && data.profilePicture) {
                // Display profile picture
                const profilePic = data.profilePicture;
                const imgPath = profilePic.startsWith('/') ? profilePic : '/' + profilePic;

                headerAvatarElement.innerHTML = `
                    <img src="${this.escapeHtml(imgPath)}" 
                         alt="${this.escapeHtml(userName)}" 
                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;"
                         onerror="this.style.display='none'; this.parentElement.textContent='${this.getInitials(userName)}';">
                `;
            } else {
                // Fallback to initials
                headerAvatarElement.textContent = this.getInitials(userName);
            }
        } catch (error) {
            console.error('Error loading profile picture:', error);
            // Fallback to initials
            headerAvatarElement.textContent = this.getInitials(userName);
        }
    }

    formatTime(dateString) {
        if (!dateString) return '';

        const date = new Date(dateString);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        if (date.toDateString() === today.toDateString()) {
            return date.toLocaleTimeString('en-MY', { hour: '2-digit', minute: '2-digit', hour12: true, timeZone: 'Asia/Kuala_Lumpur' });
        } else if (date.toDateString() === yesterday.toDateString()) {
            return 'Yesterday';
        } else {
            return date.toLocaleDateString('en-MY', { month: 'short', day: 'numeric', timeZone: 'Asia/Kuala_Lumpur' });
        }
    }

    formatMessageTime(dateString) {
        if (!dateString) return '';

        const date = new Date(dateString);
        return date.toLocaleTimeString('en-MY', { hour: '2-digit', minute: '2-digit', hour12: true, timeZone: 'Asia/Kuala_Lumpur' });
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    viewProfile() {
        if (!this.currentOtherId || !this.currentOtherType) {
            alert('Please select a conversation first');
            return;
        }

        // Navigate to profile page
        const profileUrl = `view_profile.php?type=${this.currentOtherType}&id=${this.currentOtherId}`;
        window.location.href = profileUrl;
    }

    destroy() {
        if (this.messageRefreshInterval) {
            clearInterval(this.messageRefreshInterval);
        }
    }
}

// Initialize chat app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.chatApp = new ChatApp();
});
