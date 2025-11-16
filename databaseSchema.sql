SET
    FOREIGN_KEY_CHECKS = 0;

-- ===========================
-- TABLE: Client
-- ===========================
CREATE TABLE
    Client (
        ClientID INT AUTO_INCREMENT PRIMARY KEY,
        CompanyName VARCHAR(255),
        Description TEXT,
        Email VARCHAR(255) UNIQUE,
        Password VARCHAR(255),
        PhoneNo VARCHAR(50),
        Status VARCHAR(50),
        Address TEXT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===========================
-- TABLE: Freelancer
-- ===========================
CREATE TABLE
    Freelancer (
        FreelancerID INT AUTO_INCREMENT PRIMARY KEY,
        FirstName VARCHAR(100),
        LastName VARCHAR(100),
        Email VARCHAR(255) UNIQUE,
        Password VARCHAR(255),
        PhoneNo VARCHAR(50),
        Status VARCHAR(50),
        Address TEXT,
        Experience TEXT,
        Education TEXT,
        SocialMediaURL VARCHAR(255),
        Bio TEXT,
        RatingAverage DECIMAL(3, 2),
        TotalEarned DECIMAL(10, 2) DEFAULT 0.00
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===========================
-- TABLE: Admin
-- ===========================
CREATE TABLE
    Admin (
        AdminID INT AUTO_INCREMENT PRIMARY KEY,
        Email VARCHAR(255) UNIQUE NOT NULL,
        Password VARCHAR(255) NOT NULL,
        Status VARCHAR(50) DEFAULT 'active',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===========================
-- TABLE: Skill
-- ===========================
CREATE TABLE
    Skill (
        SkillID INT AUTO_INCREMENT PRIMARY KEY,
        SkillName VARCHAR(255)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===========================
-- TABLE: FreelancerSkill (Many-to-Many)
-- ===========================
CREATE TABLE
    FreelancerSkill (
        FreelancerID INT,
        SkillID INT,
        ProficiencyLevel VARCHAR(50),
        PRIMARY KEY (FreelancerID, SkillID),
        FOREIGN KEY (FreelancerID) REFERENCES Freelancer (FreelancerID) ON DELETE CASCADE,
        FOREIGN KEY (SkillID) REFERENCES Skill (SkillID) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===========================
-- TABLE: Job
-- ===========================
CREATE TABLE
    Job (
        JobID INT AUTO_INCREMENT PRIMARY KEY,
        ClientID INT,
        Title VARCHAR(255),
        Description TEXT,
        Budget DECIMAL(10, 2),
        Deadline DATE,
        Status VARCHAR(50),
        PostDate DATE,
        FOREIGN KEY (ClientID) REFERENCES Client (ClientID) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===========================
-- TABLE: Application
-- ===========================
CREATE TABLE
    Application (
        ApplicationID INT AUTO_INCREMENT PRIMARY KEY,
        JobID INT,
        FreelancerID INT,
        ProposedBudget DECIMAL(10, 2),
        ProposalText TEXT,
        Attachment VARCHAR(255),
        Status VARCHAR(50),
        ApplicationDate DATE,
        FOREIGN KEY (JobID) REFERENCES Job (JobID) ON DELETE CASCADE,
        FOREIGN KEY (FreelancerID) REFERENCES Freelancer (FreelancerID) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===========================
-- TABLE: Agreement
-- ===========================
CREATE TABLE
    Agreement (
        AgreementID INT AUTO_INCREMENT PRIMARY KEY,
        ApplicationID INT,
        Terms TEXT,
        SignedDate DATE,
        Status VARCHAR(50),
        FOREIGN KEY (ApplicationID) REFERENCES Application (ApplicationID) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===========================
-- TABLE: Payment
-- ===========================
CREATE TABLE
    Payment (
        PaymentID INT AUTO_INCREMENT PRIMARY KEY,
        ApplicationID INT,
        Amount DECIMAL(10, 2),
        PaymentDate DATE,
        Status VARCHAR(50),
        FOREIGN KEY (ApplicationID) REFERENCES Application (ApplicationID) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===========================
-- TABLE: History
-- ===========================
CREATE TABLE
    History (
        HistoryID INT AUTO_INCREMENT PRIMARY KEY,
        ApplicationID INT,
        Action TEXT,
        Timestamp DATETIME,
        FreelancerID INT,
        ClientID INT,
        FOREIGN KEY (ApplicationID) REFERENCES Application (ApplicationID) ON DELETE CASCADE,
        FOREIGN KEY (FreelancerID) REFERENCES Freelancer (FreelancerID),
        FOREIGN KEY (ClientID) REFERENCES Client (ClientID)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===========================
-- TABLE: Message
-- ===========================
CREATE TABLE
    Message (
        MessageID INT AUTO_INCREMENT PRIMARY KEY,
        SenderID INT NOT NULL,
        ReceiverID INT NOT NULL,
        Content TEXT,
        AttachmentPath VARCHAR(500),
        AttachmentType VARCHAR(50),
        Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        Status VARCHAR(50) DEFAULT 'unread',
        INDEX idx_sender (SenderID),
        INDEX idx_receiver (ReceiverID),
        INDEX idx_timestamp (Timestamp)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ===========================
-- TABLE: Review
-- ===========================
CREATE TABLE
    Review (
        ReviewID INT AUTO_INCREMENT PRIMARY KEY,
        FreelancerID INT,
        ClientID INT,
        Rating INT,
        Comment TEXT,
        ReviewDate DATE,
        FOREIGN KEY (FreelancerID) REFERENCES Freelancer (FreelancerID) ON DELETE CASCADE,
        FOREIGN KEY (ClientID) REFERENCES Client (ClientID) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

SET
    FOREIGN_KEY_CHECKS = 1;