-- Work Submissions Table
CREATE TABLE IF NOT EXISTS work_submissions (
    SubmissionID INT PRIMARY KEY AUTO_INCREMENT,
    AgreementID INT NOT NULL,
    FreelancerID INT NOT NULL,
    ClientID INT NOT NULL,
    SubmissionTitle VARCHAR(255) NOT NULL,
    SubmissionNotes TEXT,
    Status ENUM('pending_review', 'approved', 'rejected', 'revision_requested') DEFAULT 'pending_review',
    ReviewNotes TEXT,
    ReviewedAt DATETIME,
    SubmittedAt DATETIME NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (AgreementID) REFERENCES agreement(AgreementID) ON DELETE CASCADE,
    FOREIGN KEY (FreelancerID) REFERENCES freelancer(FreelancerID) ON DELETE CASCADE,
    FOREIGN KEY (ClientID) REFERENCES client(ClientID) ON DELETE CASCADE,
    INDEX idx_agreement (AgreementID),
    INDEX idx_freelancer (FreelancerID),
    INDEX idx_client (ClientID),
    INDEX idx_status (Status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Submission Files Table
CREATE TABLE IF NOT EXISTS submission_files (
    FileID INT PRIMARY KEY AUTO_INCREMENT,
    SubmissionID INT NOT NULL,
    OriginalFileName VARCHAR(255) NOT NULL,
    StoredFileName VARCHAR(255) NOT NULL,
    FilePath VARCHAR(500) NOT NULL,
    FileSize BIGINT NOT NULL,
    FileType VARCHAR(50),
    UploadedAt DATETIME NOT NULL,
    FOREIGN KEY (SubmissionID) REFERENCES work_submissions(SubmissionID) ON DELETE CASCADE,
    INDEX idx_submission (SubmissionID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications Table (if not exists)
CREATE TABLE IF NOT EXISTS notifications (
    NotificationID INT PRIMARY KEY AUTO_INCREMENT,
    UserID INT NOT NULL,
    UserType ENUM('client', 'freelancer', 'admin') NOT NULL,
    Message TEXT NOT NULL,
    RelatedType VARCHAR(50),
    RelatedID INT,
    CreatedAt DATETIME NOT NULL,
    IsRead TINYINT(1) DEFAULT 0,
    ReadAt DATETIME,
    INDEX idx_user (UserID, UserType),
    INDEX idx_read (IsRead),
    INDEX idx_created (CreatedAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update agreement table to include pending_review status (if column exists, this will add the new value)
-- Note: This is for documentation. Manually update if needed.
-- ALTER TABLE agreement MODIFY Status ENUM('to_accept', 'ongoing', 'pending_review', 'completed', 'cancelled') DEFAULT 'to_accept';
