-- ===========================
-- ALTER TABLE: Message
-- Add attachment support, ConversationID, and convert SenderID/ReceiverID to VARCHAR for composite IDs
-- ===========================
-- Drop foreign key constraints if they exist (so we can modify the columns)
ALTER TABLE message
DROP FOREIGN KEY IF EXISTS message_ibfk_1;

ALTER TABLE message
DROP FOREIGN KEY IF EXISTS message_ibfk_2;

-- Modify SenderID and ReceiverID to VARCHAR(20) to store composite IDs like f1, c2, etc.
ALTER TABLE message MODIFY COLUMN SenderID VARCHAR(20) NOT NULL;

ALTER TABLE message MODIFY COLUMN ReceiverID VARCHAR(20) NOT NULL;

-- Add AttachmentPath column if it doesn't exist
ALTER TABLE message
ADD COLUMN AttachmentPath VARCHAR(500) NULL AFTER Content;

-- Add AttachmentType column if it doesn't exist
ALTER TABLE message
ADD COLUMN AttachmentType VARCHAR(50) NULL AFTER AttachmentPath;

-- Add ConversationID column if it doesn't exist
ALTER TABLE message
ADD COLUMN ConversationID INT NULL AFTER MessageID;

-- Add indexes for better query performance if they don't exist
ALTER TABLE message ADD INDEX idx_sender (SenderID);

ALTER TABLE message ADD INDEX idx_receiver (ReceiverID);

ALTER TABLE message ADD INDEX idx_timestamp (Timestamp);

ALTER TABLE message ADD INDEX idx_conversation (ConversationID);

-- Ensure Status column has default value
ALTER TABLE message MODIFY COLUMN Status VARCHAR(50) DEFAULT 'unread';

-- Ensure Timestamp has default value
ALTER TABLE message MODIFY COLUMN Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP;

-- ===========================
-- CREATE TABLE: Conversation
-- ===========================
CREATE TABLE
    IF NOT EXISTS Conversation (
        ConversationID INT AUTO_INCREMENT PRIMARY KEY,
        User1ID INT NOT NULL,
        User1Type VARCHAR(50) NOT NULL,
        User2ID INT NOT NULL,
        User2Type VARCHAR(50) NOT NULL,
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        LastMessageAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        Status VARCHAR(50) DEFAULT 'active',
        UNIQUE KEY unique_conversation (User1ID, User1Type, User2ID, User2Type),
        INDEX idx_user1 (User1ID),
        INDEX idx_user2 (User2ID)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===========================
-- Add Foreign Key (optional - uncomment if Conversation table exists)
-- ===========================
-- ALTER TABLE message 
-- ADD CONSTRAINT fk_message_conversation 
-- FOREIGN KEY (ConversationID) REFERENCES Conversation(ConversationID) ON DELETE CASCADE;