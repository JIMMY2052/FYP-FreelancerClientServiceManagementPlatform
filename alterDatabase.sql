-- ===========================
-- ALTER TABLE: Message
-- Add attachment support to existing Message table
-- ===========================
-- Add AttachmentPath column if it doesn't exist
ALTER TABLE message
ADD COLUMN AttachmentPath VARCHAR(500) NULL AFTER Content;

-- Add AttachmentType column if it doesn't exist
ALTER TABLE message
ADD COLUMN AttachmentType VARCHAR(50) NULL AFTER AttachmentPath;

-- Add indexes for better query performance if they don't exist
ALTER TABLE message ADD INDEX idx_sender (SenderID);

ALTER TABLE message ADD INDEX idx_receiver (ReceiverID);

ALTER TABLE message ADD INDEX idx_timestamp (Timestamp);

-- Ensure Status column has default value
ALTER TABLE message MODIFY COLUMN Status VARCHAR(50) DEFAULT 'unread';

-- Ensure Timestamp has default value
ALTER TABLE message MODIFY COLUMN Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP;