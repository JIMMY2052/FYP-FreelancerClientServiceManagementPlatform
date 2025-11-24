-- Job Questions Table
-- This table stores screening questions created by clients for job postings
CREATE TABLE IF NOT EXISTS `job_question` (
    `QuestionID` int(11) NOT NULL AUTO_INCREMENT,
    `JobID` int(11) NOT NULL,
    `QuestionText` text NOT NULL,
    `QuestionType` enum('multiple_choice', 'yes_no') NOT NULL DEFAULT 'multiple_choice',
    `IsRequired` tinyint(1) DEFAULT 1,
    `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`QuestionID`),
    KEY `JobID` (`JobID`),
    CONSTRAINT `job_question_ibfk_1` FOREIGN KEY (`JobID`) REFERENCES `job` (`JobID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Job Question Options Table
-- This table stores predefined answer options for multiple choice questions
CREATE TABLE IF NOT EXISTS `job_question_option` (
    `OptionID` int(11) NOT NULL AUTO_INCREMENT,
    `QuestionID` int(11) NOT NULL,
    `OptionText` varchar(500) NOT NULL,
    `IsCorrectAnswer` tinyint(1) DEFAULT 0,
    `DisplayOrder` int(11) DEFAULT 0,
    PRIMARY KEY (`OptionID`),
    KEY `QuestionID` (`QuestionID`),
    CONSTRAINT `job_question_option_ibfk_1` FOREIGN KEY (`QuestionID`) REFERENCES `job_question` (`QuestionID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Job Application Answers Table
-- This table stores freelancer answers to job questions
CREATE TABLE IF NOT EXISTS `job_application_answer` (
    `AnswerID` int(11) NOT NULL AUTO_INCREMENT,
    `ApplicationID` int(11) DEFAULT NULL,
    `QuestionID` int(11) NOT NULL,
    `FreelancerID` int(11) NOT NULL,
    `JobID` int(11) NOT NULL,
    `SelectedOptionID` int(11) DEFAULT NULL,
    `AnswerText` text DEFAULT NULL,
    `AnsweredAt` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`AnswerID`),
    KEY `QuestionID` (`QuestionID`),
    KEY `FreelancerID` (`FreelancerID`),
    KEY `JobID` (`JobID`),
    KEY `SelectedOptionID` (`SelectedOptionID`),
    CONSTRAINT `job_application_answer_ibfk_1` FOREIGN KEY (`QuestionID`) REFERENCES `job_question` (`QuestionID`) ON DELETE CASCADE,
    CONSTRAINT `job_application_answer_ibfk_2` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE,
    CONSTRAINT `job_application_answer_ibfk_3` FOREIGN KEY (`JobID`) REFERENCES `job` (`JobID`) ON DELETE CASCADE,
    CONSTRAINT `job_application_answer_ibfk_4` FOREIGN KEY (`SelectedOptionID`) REFERENCES `job_question_option` (`OptionID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
