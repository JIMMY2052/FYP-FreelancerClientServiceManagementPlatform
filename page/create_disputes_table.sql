-- Create Disputes Table
-- This table tracks agreement disputes filed by clients or freelancers
CREATE TABLE
    IF NOT EXISTS `dispute` (
        `DisputeID` int (11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `AgreementID` int (11) NOT NULL,
        `InitiatorID` int (11) NOT NULL COMMENT 'FreelancerID or ClientID who filed dispute',
        `InitiatorType` enum ('freelancer', 'client') NOT NULL COMMENT 'Type of user who filed dispute',
        `ReasonText` text NOT NULL COMMENT 'Reason for dispute',
        `EvidenceFile` varchar(255) DEFAULT NULL COMMENT 'Path to evidence file/document',
        `Status` enum ('open', 'under_review', 'resolved', 'rejected') NOT NULL DEFAULT 'open' COMMENT 'Current dispute status',
        `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When dispute was filed',
        `AdminNotesText` text DEFAULT NULL COMMENT 'Admin review notes',
        `ResolutionAction` enum (
            'refund_client',
            'release_to_freelancer',
            'split_payment',
            'rejected'
        ) DEFAULT NULL COMMENT 'How dispute was resolved',
        `ResolvedAt` timestamp NULL DEFAULT NULL COMMENT 'When dispute was resolved',
        `ResolvedByAdminID` int (11) DEFAULT NULL COMMENT 'Admin who resolved dispute',
        -- Foreign Keys
        CONSTRAINT fk_dispute_agreement FOREIGN KEY (`AgreementID`) REFERENCES `agreement` (`AgreementID`) ON DELETE CASCADE,
        CONSTRAINT fk_dispute_admin FOREIGN KEY (`ResolvedByAdminID`) REFERENCES `admin` (`AdminID`) ON DELETE SET NULL,
        -- Indexes for faster queries
        KEY `idx_agreement` (`AgreementID`),
        KEY `idx_initiator` (`InitiatorID`),
        KEY `idx_status` (`Status`),
        KEY `idx_created` (`CreatedAt`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'Tracks disputes filed on agreements';