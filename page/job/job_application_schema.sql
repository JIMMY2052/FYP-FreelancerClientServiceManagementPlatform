-- Job Applications Table
-- This table stores freelancer applications to job postings
CREATE TABLE
    IF NOT EXISTS `job_application` (
        `ApplicationID` int (11) NOT NULL AUTO_INCREMENT,
        `JobID` int (11) NOT NULL,
        `FreelancerID` int (11) NOT NULL,
        `CoverLetter` text DEFAULT NULL,
        `ProposedBudget` decimal(10, 2) DEFAULT NULL,
        `EstimatedDuration` varchar(100) DEFAULT NULL,
        `Status` enum ('pending', 'accepted', 'rejected', 'withdrawn') NOT NULL DEFAULT 'pending',
        `AppliedAt` timestamp NOT NULL DEFAULT current_timestamp(),
        `UpdatedAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`ApplicationID`),
        KEY `JobID` (`JobID`),
        KEY `FreelancerID` (`FreelancerID`),
        KEY `Status` (`Status`),
        CONSTRAINT `job_application_ibfk_1` FOREIGN KEY (`JobID`) REFERENCES `job` (`JobID`) ON DELETE CASCADE,
        CONSTRAINT `job_application_ibfk_2` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;