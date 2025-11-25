-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2025 at 03:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
SET
  SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET
  time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;

/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;

/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fyp`
--
-- --------------------------------------------------------
--
-- Table structure for table `admin`
--
CREATE TABLE
  `admin` (
    `AdminID` int (11) NOT NULL,
    `Email` varchar(255) NOT NULL,
    `Password` varchar(255) NOT NULL,
    `Status` varchar(50) DEFAULT 'active',
    `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--
INSERT INTO
  `admin` (
    `AdminID`,
    `Email`,
    `Password`,
    `Status`,
    `CreatedAt`
  )
VALUES
  (
    1,
    'jimmychankahlok@gmail.com',
    '$2y$10$BdoF5Lx6GUYgEASR5uJAUuYhOs0gldiguXd6VmG2X3Pv/I.WRZH3e',
    'active',
    '2025-11-19 13:13:06'
  );

-- --------------------------------------------------------
--
-- Table structure for table `agreement`
--
CREATE TABLE
  `agreement` (
    `AgreementID` int (11) NOT NULL,
    `FreelancerID` int (11) DEFAULT NULL,
    `ClientID` int (11) DEFAULT NULL,
    `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp(),
    `ClientSignedDate` datetime DEFAULT NULL,
    `ClientSignaturePath` varchar(255) DEFAULT NULL,
    `FreelancerSignedDate` datetime DEFAULT NULL,
    `ExpiredDate` datetime DEFAULT NULL,
    `FreelancerSignaturePath` varchar(255) DEFAULT NULL,
    `Terms` text DEFAULT NULL,
    `Status` varchar(50) DEFAULT NULL,
    `ProjectTitle` varchar(255) DEFAULT NULL,
    `Scope` text DEFAULT NULL,
    `DeliveryTime` int (11) NOT NULL,
    `Deliverables` text DEFAULT NULL,
    `PaymentAmount` decimal(10, 2) DEFAULT NULL,
    `ProjectDetail` text DEFAULT NULL,
    `FreelancerName` varchar(255) DEFAULT NULL,
    `ClientName` varchar(255) DEFAULT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `agreement`
--
INSERT INTO
  `agreement` (
    `AgreementID`,
    `FreelancerID`,
    `ClientID`,
    `CreatedDate`,
    `ClientSignedDate`,
    `ClientSignaturePath`,
    `FreelancerSignedDate`,
    `ExpiredDate`,
    `FreelancerSignaturePath`,
    `Terms`,
    `Status`,
    `ProjectTitle`,
    `Scope`,
    `DeliveryTime`,
    `Deliverables`,
    `PaymentAmount`,
    `ProjectDetail`,
    `FreelancerName`,
    `ClientName`
  )
VALUES
  (
    2,
    1,
    2,
    '2025-11-25 13:08:55',
    '2025-11-25 21:08:55',
    NULL,
    NULL,
    NULL,
    NULL,
    '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.',
    'signed_by_client',
    'asdf',
    'asdf',
    0,
    'To be completed upon milestone deliveries as agreed.',
    666.00,
    'asdf',
    'JIMMY CHAN LOK',
    'Genting'
  ),
  (
    3,
    1,
    2,
    '2025-11-25 13:13:13',
    '2025-11-25 21:13:13',
    NULL,
    NULL,
    NULL,
    NULL,
    '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.',
    'signed_by_client',
    'asdf',
    'asdf',
    0,
    'To be completed upon milestone deliveries as agreed.',
    666.00,
    'asdf',
    'JIMMY CHAN LOK',
    'Genting'
  ),
  (
    4,
    1,
    2,
    '2025-11-25 13:14:14',
    '2025-11-25 21:14:14',
    NULL,
    NULL,
    NULL,
    NULL,
    '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.',
    'signed_by_client',
    'asdf',
    'asdf',
    0,
    'To be completed upon milestone deliveries as agreed.',
    666.00,
    'asdf',
    'JIMMY CHAN LOK',
    'Genting'
  ),
  (
    5,
    1,
    2,
    '2025-11-25 13:15:12',
    '2025-11-25 21:15:12',
    NULL,
    NULL,
    NULL,
    NULL,
    '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.',
    'signed_by_client',
    'asdf',
    'asdf',
    0,
    'To be completed upon milestone deliveries as agreed.',
    666.00,
    'asdf',
    'JIMMY CHAN LOK',
    'Genting'
  ),
  (
    6,
    1,
    2,
    '2025-11-25 13:19:23',
    '2025-11-25 21:19:23',
    NULL,
    NULL,
    NULL,
    NULL,
    '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.',
    'signed_by_client',
    'asdf',
    'asdf',
    0,
    'To be completed upon milestone deliveries as agreed.',
    666.00,
    'asdf',
    'JIMMY CHAN LOK',
    'Genting'
  ),
  (
    7,
    1,
    2,
    '2025-11-25 13:24:47',
    '2025-11-25 21:24:47',
    NULL,
    NULL,
    NULL,
    NULL,
    '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.',
    'signed_by_client',
    'asdf',
    'asdf',
    0,
    'To be completed upon milestone deliveries as agreed.',
    666.00,
    'asdf',
    'JIMMY CHAN LOK',
    'Genting'
  ),
  (
    8,
    1,
    2,
    '2025-11-25 13:26:52',
    '2025-11-25 21:26:52',
    NULL,
    NULL,
    NULL,
    NULL,
    '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.',
    'signed_by_client',
    'asdf',
    'asdf',
    0,
    'To be completed upon milestone deliveries as agreed.',
    666.00,
    'asdf',
    'JIMMY CHAN LOK',
    'Genting'
  ),
  (
    9,
    1,
    2,
    '2025-11-25 13:52:27',
    '2025-11-25 21:52:27',
    NULL,
    NULL,
    NULL,
    NULL,
    '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.',
    'signed_by_client',
    'asdf',
    'asdf',
    0,
    'To be completed upon milestone deliveries as agreed.',
    666.00,
    'asdf',
    'JIMMY CHAN LOK',
    'Genting'
  ),
  (
    10,
    1,
    2,
    '2025-11-25 14:07:17',
    '2025-11-25 22:07:17',
    '/agreement/',
    NULL,
    '2025-12-25 22:07:17',
    NULL,
    '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.',
    'signed_by_client',
    'asdf',
    'asdf',
    0,
    'To be completed upon milestone deliveries as agreed.',
    666.00,
    'asdf',
    'JIMMY CHAN LOK',
    'Genting'
  ),
  (
    11,
    1,
    2,
    '2025-11-25 14:08:35',
    '2025-11-25 22:08:35',
    '/agreement/',
    NULL,
    '2025-12-25 22:08:35',
    NULL,
    '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.',
    'signed_by_client',
    'asdf',
    'asdf',
    0,
    'To be completed upon milestone deliveries as agreed.',
    666.00,
    'asdf',
    'JIMMY CHAN LOK',
    'Genting'
  ),
  (
    12,
    1,
    2,
    '2025-11-25 14:35:10',
    '2025-11-25 22:35:10',
    '/agreement/',
    NULL,
    '2025-11-28 22:35:10',
    NULL,
    '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.',
    'to_accept',
    'asdf',
    '0',
    0,
    'To be completed upon milestone deliveries as agreed.',
    666.00,
    'asdf',
    'JIMMY CHAN LOK',
    'Genting'
  );

-- --------------------------------------------------------
--
-- Table structure for table `client`
--
CREATE TABLE
  `client` (
    `ClientID` int (11) NOT NULL,
    `CompanyName` varchar(255) DEFAULT NULL,
    `Description` text DEFAULT NULL,
    `Email` varchar(255) DEFAULT NULL,
    `Password` varchar(255) DEFAULT NULL,
    `PhoneNo` varchar(50) DEFAULT NULL,
    `ProfilePicture` varchar(500) DEFAULT NULL,
    `Status` varchar(50) DEFAULT NULL,
    `Address` text DEFAULT NULL,
    `JoinedDate` timestamp NOT NULL DEFAULT current_timestamp(),
    `isDelete` tinyint (1) DEFAULT 0
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `client`
--
INSERT INTO
  `client` (
    `ClientID`,
    `CompanyName`,
    `Description`,
    `Email`,
    `Password`,
    `PhoneNo`,
    `ProfilePicture`,
    `Status`,
    `Address`,
    `JoinedDate`,
    `isDelete`
  )
VALUES
  (
    1,
    'Sitecore',
    NULL,
    'jimmyckl-wm22@student.tarc.edu.my',
    '$2y$10$Hj1o9ccV03Fk5BjT6Db/CeGGIDyartvNIkUw.cqNYnu0t9QN5axge',
    NULL,
    NULL,
    'active',
    NULL,
    '2025-11-20 15:15:48',
    0
  ),
  (
    2,
    'Genting',
    '',
    'genting@gmail.com',
    '$2y$10$D1ON60Z0DruTc8tASwybi.VX6wu0nIPxZURmUDSrFEf6ZWb9c7Gv6',
    '',
    NULL,
    'active',
    '',
    '2025-11-20 15:15:48',
    0
  ),
  (
    3,
    'Google',
    NULL,
    'lucifa@gmail.com',
    '$2y$10$yP7JQYBtyuUYIsBkQI1T.uCp0HLAiTO.TWORqXF1bX5nJLA4deq8C',
    NULL,
    NULL,
    'active',
    NULL,
    '2025-11-20 15:15:48',
    0
  );

-- --------------------------------------------------------
--
-- Table structure for table `conversation`
--
CREATE TABLE
  `conversation` (
    `ConversationID` int (11) NOT NULL,
    `User1ID` int (11) NOT NULL,
    `User1Type` enum ('freelancer', 'client') NOT NULL,
    `User2ID` int (11) NOT NULL,
    `User2Type` enum ('freelancer', 'client') NOT NULL,
    `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
    `LastMessageAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
    `Status` varchar(50) DEFAULT 'active',
    `DeletedBy` longtext CHARACTER
    SET
      utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid (`DeletedBy`))
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `conversation`
--
INSERT INTO
  `conversation` (
    `ConversationID`,
    `User1ID`,
    `User1Type`,
    `User2ID`,
    `User2Type`,
    `CreatedAt`,
    `LastMessageAt`,
    `Status`,
    `DeletedBy`
  )
VALUES
  (
    18,
    1,
    'freelancer',
    2,
    'client',
    '2025-11-25 13:40:22',
    '2025-11-25 13:47:10',
    'active',
    NULL
  );

-- --------------------------------------------------------
--
-- Table structure for table `freelancer`
--
CREATE TABLE
  `freelancer` (
    `FreelancerID` int (11) NOT NULL,
    `FirstName` varchar(100) DEFAULT NULL,
    `LastName` varchar(100) DEFAULT NULL,
    `Email` varchar(255) DEFAULT NULL,
    `Password` varchar(255) DEFAULT NULL,
    `PhoneNo` varchar(50) DEFAULT NULL,
    `ProfilePicture` varchar(500) DEFAULT NULL,
    `Status` varchar(50) DEFAULT NULL,
    `Address` text DEFAULT NULL,
    `Experience` text DEFAULT NULL,
    `Education` text DEFAULT NULL,
    `SocialMediaURL` varchar(255) DEFAULT NULL,
    `Bio` text DEFAULT NULL,
    `Rating` decimal(3, 2) DEFAULT NULL,
    `TotalEarned` decimal(10, 2) DEFAULT NULL,
    `JoinedDate` timestamp NOT NULL DEFAULT current_timestamp(),
    `isDelete` tinyint (1) DEFAULT 0
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `freelancer`
--
INSERT INTO
  `freelancer` (
    `FreelancerID`,
    `FirstName`,
    `LastName`,
    `Email`,
    `Password`,
    `PhoneNo`,
    `ProfilePicture`,
    `Status`,
    `Address`,
    `Experience`,
    `Education`,
    `SocialMediaURL`,
    `Bio`,
    `Rating`,
    `TotalEarned`,
    `JoinedDate`,
    `isDelete`
  )
VALUES
  (
    1,
    'JIMMY CHAN',
    'LOK',
    'jimmychankahlok66@gmail.com',
    '$2y$10$jZYJ20FbflriS3ibKasx7O9faf9bJmmaU6U2tuTi9wyHdVSpyGnCu',
    '0185709586',
    'uploads/profile_pictures/freelancer_1_1763744805.png',
    'active',
    'NO 5, Lorong masria 3, taman bunga raya',
    '3 Years Experience in Web Development',
    'Bachelor Degree in Software Engineering',
    'https://linked.in',
    'asdf',
    NULL,
    0.00,
    '2025-11-20 15:15:48',
    0
  ),
  (
    2,
    'lexas',
    'wer',
    'jc@gmail.com',
    '$2y$10$E/ktmWMUMTAD2uieh9uJ0eib0JfBZpGpzGG83b8/8JbNUH1httt2q',
    NULL,
    NULL,
    'active',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0.00,
    '2025-11-20 15:15:48',
    0
  ),
  (
    3,
    'hc',
    'c',
    'hc@gmail.com',
    '$2y$10$ZOgz2kiWcxShvnSqc2P7dejfEqak/MncYY2EukVaLiuhGggJXaCPG',
    NULL,
    NULL,
    'active',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    '2025-11-20 15:15:48',
    0
  ),
  (
    4,
    'John',
    'Lee',
    'john@gmail.com',
    '$2y$10$9yQ0dOnB/3KlF50FluBsceeKYovo88cNqPR.BHPn8jfMh67Un4UgC',
    '0185709586',
    NULL,
    'active',
    'NO 341, JALAN ZAMRUD 2, BATU LIMA',
    '3 Year experience in web developement',
    '',
    'https://linked.in',
    'Professional Web Developer',
    NULL,
    NULL,
    '2025-11-20 15:15:48',
    0
  );

-- --------------------------------------------------------
--
-- Table structure for table `freelancerskill`
--
CREATE TABLE
  `freelancerskill` (
    `FreelancerID` int (11) NOT NULL,
    `SkillID` int (11) NOT NULL,
    `ProficiencyLevel` varchar(50) DEFAULT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `freelancerskill`
--
INSERT INTO
  `freelancerskill` (`FreelancerID`, `SkillID`, `ProficiencyLevel`)
VALUES
  (1, 1, 'Intermediate'),
  (1, 2, 'Intermediate'),
  (1, 3, 'Intermediate'),
  (1, 4, 'Intermediate'),
  (4, 3, 'Intermediate');

-- --------------------------------------------------------
--
-- Table structure for table `gig`
--
CREATE TABLE
  `gig` (
    `GigID` int (11) NOT NULL,
    `FreelancerID` int (11) NOT NULL,
    `Title` varchar(150) NOT NULL,
    `Category` varchar(100) NOT NULL,
    `Subcategory` varchar(100) NOT NULL,
    `SearchTags` varchar(100) NOT NULL,
    `Description` text NOT NULL,
    `Price` int (11) NOT NULL,
    `DeliveryTime` int (11) NOT NULL,
    `RushDelivery` int (11) DEFAULT NULL,
    `RushDeliveryPrice` int (11) NOT NULL,
    `AdditionalRevision` int (11) DEFAULT NULL,
    `RevisionCount` int (11) NOT NULL,
    `Image1Path` varchar(255) NOT NULL,
    `Image2Path` varchar(255) DEFAULT NULL,
    `Image3Path` varchar(255) DEFAULT NULL,
    `VideoPath` varchar(255) DEFAULT NULL,
    `Status` enum ('active', 'paused', 'deleted') NOT NULL,
    `CreatedAt` datetime NOT NULL,
    `UpdatedAt` datetime DEFAULT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Table structure for table `job`
--
CREATE TABLE
  `job` (
    `JobID` int (11) NOT NULL,
    `ClientID` int (11) DEFAULT NULL,
    `Title` varchar(255) DEFAULT NULL,
    `Description` text DEFAULT NULL,
    `Budget` decimal(10, 2) DEFAULT NULL,
    `DeliveryTime` int (11) NOT NULL,
    `Deadline` date DEFAULT NULL,
    `Status` enum ('available', 'deleted', 'complete', 'processing') NOT NULL DEFAULT 'available',
    `PostDate` date DEFAULT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `job`
--
INSERT INTO
  `job` (
    `JobID`,
    `ClientID`,
    `Title`,
    `Description`,
    `Budget`,
    `DeliveryTime`,
    `Deadline`,
    `Status`,
    `PostDate`
  )
VALUES
  (
    1,
    2,
    'Google',
    'asdf',
    12.00,
    0,
    '2025-11-29',
    'available',
    '2025-11-25'
  ),
  (
    2,
    2,
    'Google',
    'asdf',
    12.00,
    0,
    '2025-11-29',
    'available',
    '2025-11-25'
  ),
  (
    3,
    2,
    'ttt',
    'adf',
    1000.00,
    0,
    '2025-11-29',
    'available',
    '2025-11-25'
  ),
  (
    4,
    2,
    'asdf',
    'asdf',
    12.00,
    0,
    '2025-11-29',
    'available',
    '2025-11-25'
  ),
  (
    5,
    2,
    'asdf',
    'asdf',
    666.00,
    0,
    '2025-11-29',
    'available',
    '2025-11-25'
  );

-- --------------------------------------------------------
--
-- Table structure for table `job_application`
--
CREATE TABLE
  `job_application` (
    `ApplicationID` int (11) NOT NULL,
    `JobID` int (11) NOT NULL,
    `FreelancerID` int (11) NOT NULL,
    `CoverLetter` text DEFAULT NULL,
    `ProposedBudget` decimal(10, 2) DEFAULT NULL,
    `EstimatedDuration` varchar(100) DEFAULT NULL,
    `Status` enum ('pending', 'accepted', 'rejected', 'withdrawn') NOT NULL DEFAULT 'pending',
    `AppliedAt` timestamp NOT NULL DEFAULT current_timestamp(),
    `UpdatedAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `job_application`
--
INSERT INTO
  `job_application` (
    `ApplicationID`,
    `JobID`,
    `FreelancerID`,
    `CoverLetter`,
    `ProposedBudget`,
    `EstimatedDuration`,
    `Status`,
    `AppliedAt`,
    `UpdatedAt`
  )
VALUES
  (
    1,
    1,
    1,
    NULL,
    NULL,
    NULL,
    '',
    '2025-11-25 08:05:23',
    '2025-11-25 11:11:10'
  ),
  (
    2,
    3,
    1,
    NULL,
    NULL,
    NULL,
    '',
    '2025-11-25 11:14:12',
    '2025-11-25 11:21:38'
  ),
  (
    3,
    5,
    1,
    NULL,
    NULL,
    NULL,
    'pending',
    '2025-11-25 11:48:40',
    NULL
  );

-- --------------------------------------------------------
--
-- Table structure for table `job_application_answer`
--
CREATE TABLE
  `job_application_answer` (
    `AnswerID` int (11) NOT NULL,
    `ApplicationID` int (11) DEFAULT NULL,
    `QuestionID` int (11) NOT NULL,
    `FreelancerID` int (11) NOT NULL,
    `JobID` int (11) NOT NULL,
    `SelectedOptionID` int (11) DEFAULT NULL,
    `AnswerText` text DEFAULT NULL,
    `AnsweredAt` timestamp NOT NULL DEFAULT current_timestamp()
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `job_application_answer`
--
INSERT INTO
  `job_application_answer` (
    `AnswerID`,
    `ApplicationID`,
    `QuestionID`,
    `FreelancerID`,
    `JobID`,
    `SelectedOptionID`,
    `AnswerText`,
    `AnsweredAt`
  )
VALUES
  (1, 2, 2, 1, 3, NULL, 'yes', '2025-11-25 11:14:12');

-- --------------------------------------------------------
--
-- Table structure for table `job_question`
--
CREATE TABLE
  `job_question` (
    `QuestionID` int (11) NOT NULL,
    `JobID` int (11) NOT NULL,
    `QuestionText` text NOT NULL,
    `QuestionType` enum ('multiple_choice', 'yes_no') NOT NULL DEFAULT 'multiple_choice',
    `IsRequired` tinyint (1) DEFAULT 1,
    `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `job_question`
--
INSERT INTO
  `job_question` (
    `QuestionID`,
    `JobID`,
    `QuestionText`,
    `QuestionType`,
    `IsRequired`,
    `CreatedAt`
  )
VALUES
  (1, 2, 'asdf', 'yes_no', 1, '2025-11-25 08:04:28'),
  (2, 3, 'asdf', 'yes_no', 1, '2025-11-25 11:14:03');

-- --------------------------------------------------------
--
-- Table structure for table `job_question_option`
--
CREATE TABLE
  `job_question_option` (
    `OptionID` int (11) NOT NULL,
    `QuestionID` int (11) NOT NULL,
    `OptionText` varchar(500) NOT NULL,
    `IsCorrectAnswer` tinyint (1) DEFAULT 0,
    `DisplayOrder` int (11) DEFAULT 0
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Table structure for table `message`
--
CREATE TABLE
  `message` (
    `MessageID` int (11) NOT NULL,
    `ConversationID` int (11) DEFAULT NULL,
    `ReceiverID` varchar(20) NOT NULL,
    `SenderID` varchar(20) NOT NULL,
    `Content` text DEFAULT NULL,
    `AttachmentPath` varchar(500) DEFAULT NULL,
    `AttachmentType` varchar(50) DEFAULT NULL,
    `Timestamp` datetime DEFAULT current_timestamp(),
    `Status` varchar(50) DEFAULT 'unread'
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `message`
--
INSERT INTO
  `message` (
    `MessageID`,
    `ConversationID`,
    `ReceiverID`,
    `SenderID`,
    `Content`,
    `AttachmentPath`,
    `AttachmentType`,
    `Timestamp`,
    `Status`
  )
VALUES
  (
    1,
    18,
    'c2',
    'f1',
    'I am interested in your project: \"Google\". I would like to discuss more about this opportunity.',
    NULL,
    NULL,
    '2025-11-25 21:40:30',
    'unread'
  ),
  (
    2,
    18,
    'c2',
    'f1',
    'hi',
    NULL,
    NULL,
    '2025-11-25 21:40:33',
    'unread'
  ),
  (
    3,
    18,
    'f1',
    'c2',
    'ji',
    NULL,
    NULL,
    '2025-11-25 21:42:26',
    'unread'
  ),
  (
    4,
    18,
    'f1',
    'c2',
    'ji',
    NULL,
    NULL,
    '2025-11-25 21:42:29',
    'unread'
  ),
  (
    5,
    18,
    'f1',
    'c2',
    'asdf',
    NULL,
    NULL,
    '2025-11-25 21:42:36',
    'unread'
  ),
  (
    6,
    18,
    'c2',
    'f1',
    'asdf',
    NULL,
    NULL,
    '2025-11-25 21:42:40',
    'unread'
  ),
  (
    7,
    18,
    'c2',
    'f1',
    'sdfg',
    NULL,
    NULL,
    '2025-11-25 21:43:25',
    'unread'
  ),
  (
    8,
    18,
    'f1',
    'c2',
    'asdf',
    NULL,
    NULL,
    '2025-11-25 21:46:15',
    'unread'
  ),
  (
    9,
    18,
    'c2',
    'f1',
    'asdf',
    NULL,
    NULL,
    '2025-11-25 21:46:35',
    'unread'
  ),
  (
    10,
    18,
    'c2',
    'f1',
    'sdfg',
    NULL,
    NULL,
    '2025-11-25 21:47:07',
    'unread'
  ),
  (
    11,
    18,
    'f1',
    'c2',
    'asdf',
    NULL,
    NULL,
    '2025-11-25 21:47:10',
    'unread'
  ),
  (
    12,
    18,
    'f1',
    'c2',
    'I have signed the agreement for the project \"asdf\". Please review and sign to proceed. The agreement is attached below.\n\nAgreement Link: localhost:8000/page/freelancer_agreement_approval.php?agreement_id=9',
    '/agreement/agreement_3_1764078747.pdf',
    'application/pdf',
    '2025-11-25 21:52:27',
    'unread'
  ),
  (
    13,
    18,
    'f1',
    'c2',
    'I have signed the agreement for the project \"asdf\". Please review and sign to proceed. The agreement is attached below.\n\nAgreement Link: localhost:8000/page/freelancer_agreement_approval.php?agreement_id=10',
    '/agreement/agreement_3_1764079637.pdf',
    'application/pdf',
    '2025-11-25 22:07:17',
    'unread'
  ),
  (
    14,
    18,
    'f1',
    'c2',
    'I have signed the agreement for the project \"asdf\". Please review and sign to proceed. The agreement is attached below.\n\nAgreement Link: localhost:8000/page/freelancer_agreement_approval.php?agreement_id=11',
    '/agreement/agreement_3_1764079715.pdf',
    'application/pdf',
    '2025-11-25 22:08:35',
    'unread'
  ),
  (
    15,
    18,
    'f1',
    'c2',
    'I have signed the agreement for the project \"asdf\". Please review and sign to proceed. The agreement is attached below.\n\nAgreement Link: localhost:8000/page/freelancer_agreement_approval.php?agreement_id=12',
    '/agreement/agreement_3_1764081310.pdf',
    'application/pdf',
    '2025-11-25 22:35:10',
    'unread'
  );

-- --------------------------------------------------------
--
-- Table structure for table `message_notification`
--
CREATE TABLE
  `message_notification` (
    `NotificationID` int (11) NOT NULL,
    `ReceiverID` int (11) NOT NULL,
    `ReceiverType` varchar(20) NOT NULL,
    `SenderID` int (11) NOT NULL,
    `SenderType` varchar(20) NOT NULL,
    `ConversationID` varchar(50) DEFAULT NULL,
    `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
    `IsRead` tinyint (1) DEFAULT 0
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `message_notification`
--
INSERT INTO
  `message_notification` (
    `NotificationID`,
    `ReceiverID`,
    `ReceiverType`,
    `SenderID`,
    `SenderType`,
    `ConversationID`,
    `CreatedAt`,
    `IsRead`
  )
VALUES
  (
    1,
    3,
    'client',
    1,
    'freelancer',
    'c3',
    '2025-11-19 06:55:46',
    1
  );

-- --------------------------------------------------------
--
-- Table structure for table `password_reset`
--
CREATE TABLE
  `password_reset` (
    `ResetID` int (11) NOT NULL,
    `Email` varchar(255) NOT NULL,
    `UserType` enum ('freelancer', 'client') NOT NULL,
    `OTP` varchar(6) NOT NULL,
    `IsUsed` tinyint (1) DEFAULT 0,
    `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
    `ExpiresAt` timestamp NOT NULL DEFAULT current_timestamp()
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Table structure for table `payment`
--
CREATE TABLE
  `payment` (
    `PaymentID` int (11) NOT NULL,
    `ApplicationID` int (11) DEFAULT NULL,
    `Amount` decimal(10, 2) DEFAULT NULL,
    `PaymentDate` date DEFAULT NULL,
    `Status` varchar(50) DEFAULT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Table structure for table `review`
--
CREATE TABLE
  `review` (
    `ReviewID` int (11) NOT NULL,
    `FreelancerID` int (11) DEFAULT NULL,
    `ClientID` int (11) DEFAULT NULL,
    `Rating` int (11) DEFAULT NULL,
    `Comment` text DEFAULT NULL,
    `ReviewDate` date DEFAULT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Table structure for table `skill`
--
CREATE TABLE
  `skill` (
    `SkillID` int (11) NOT NULL,
    `SkillName` varchar(255) DEFAULT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `skill`
--
INSERT INTO
  `skill` (`SkillID`, `SkillName`)
VALUES
  (1, 'web'),
  (2, 'aws'),
  (3, 'networking'),
  (4, 'adfasd');

-- --------------------------------------------------------
--
-- Table structure for table `wallet`
--
CREATE TABLE
  `wallet` (
    `WalletID` int (11) NOT NULL,
    `UserID` varchar(11) NOT NULL,
    `Balance` decimal(10, 2) NOT NULL,
    `LockedBalance` decimal(10, 2) NOT NULL,
    `LastUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `wallet`
--
INSERT INTO
  `wallet` (
    `WalletID`,
    `UserID`,
    `Balance`,
    `LockedBalance`,
    `LastUpdated`
  )
VALUES
  (1, '3', 0.00, 0.00, '2025-11-23 07:49:41'),
  (2, '2', 0.00, 0.00, '2025-11-23 07:49:41'),
  (3, '1', 0.00, 0.00, '2025-11-23 07:49:41'),
  (4, '4', 2000.00, 0.00, '2025-11-23 14:34:27'),
  (5, '5', 1500.00, 0.00, '2025-11-23 14:40:21');

-- --------------------------------------------------------
--
-- Table structure for table `wallet_transactions`
--
CREATE TABLE
  `wallet_transactions` (
    `TransactionID` int (11) NOT NULL,
    `WalletID` int (11) NOT NULL,
    `Type` enum (
      'topup',
      'payment',
      'earning',
      'refund',
      'withdrawal'
    ) NOT NULL,
    `Amount` decimal(10, 2) NOT NULL,
    `Status` enum ('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    `Description` text DEFAULT NULL,
    `ReferenceID` varchar(100) DEFAULT NULL COMMENT 'Order ID, Payment ID, etc.',
    `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `wallet_transactions`
--
INSERT INTO
  `wallet_transactions` (
    `TransactionID`,
    `WalletID`,
    `Type`,
    `Amount`,
    `Status`,
    `Description`,
    `ReferenceID`,
    `CreatedAt`
  )
VALUES
  (
    1,
    4,
    'topup',
    50.00,
    'pending',
    'Wallet top-up via Stripe',
    NULL,
    '2025-11-23 08:00:14'
  ),
  (
    2,
    4,
    'topup',
    50.00,
    'pending',
    'Wallet top-up via Stripe',
    NULL,
    '2025-11-23 08:01:52'
  ),
  (
    3,
    4,
    'topup',
    100.00,
    'pending',
    'Wallet top-up via Stripe',
    NULL,
    '2025-11-23 08:03:00'
  ),
  (
    4,
    4,
    'topup',
    100.00,
    'failed',
    'Payment error: Not a valid URL',
    NULL,
    '2025-11-23 08:06:17'
  ),
  (
    5,
    4,
    'topup',
    100.00,
    'failed',
    'Payment error: Not a valid URL',
    NULL,
    '2025-11-23 08:06:23'
  ),
  (
    6,
    4,
    'topup',
    100.00,
    'failed',
    'Payment error: Not a valid URL',
    NULL,
    '2025-11-23 08:07:10'
  ),
  (
    7,
    4,
    'topup',
    100.00,
    'failed',
    'Payment error: Not a valid URL',
    NULL,
    '2025-11-23 08:10:20'
  ),
  (
    8,
    4,
    'topup',
    200.00,
    'failed',
    'Payment error: Not a valid URL',
    NULL,
    '2025-11-23 08:16:41'
  ),
  (
    9,
    4,
    'topup',
    50.00,
    'failed',
    'Payment error: Not a valid URL',
    NULL,
    '2025-11-23 08:19:46'
  ),
  (
    10,
    4,
    'topup',
    100.00,
    'completed',
    'Wallet Top Up via Stripe',
    NULL,
    '2025-11-23 08:49:49'
  ),
  (
    11,
    4,
    'topup',
    900.00,
    'completed',
    'Wallet Top Up via Stripe',
    NULL,
    '2025-11-23 09:01:25'
  ),
  (
    12,
    4,
    'topup',
    1000.00,
    'completed',
    'Wallet Top Up via Stripe',
    NULL,
    '2025-11-23 14:34:27'
  ),
  (
    13,
    5,
    'topup',
    1000.00,
    'completed',
    'Wallet Top Up via Stripe',
    NULL,
    '2025-11-23 14:39:40'
  ),
  (
    14,
    5,
    'topup',
    500.00,
    'completed',
    'Wallet Top Up via Stripe',
    NULL,
    '2025-11-23 14:40:21'
  );

--
-- Indexes for dumped tables
--
--
-- Indexes for table `admin`
--
ALTER TABLE `admin` ADD PRIMARY KEY (`AdminID`),
ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `agreement`
--
ALTER TABLE `agreement` ADD PRIMARY KEY (`AgreementID`),
ADD KEY `fk_agreement_freelancer` (`FreelancerID`),
ADD KEY `fk_agreement_client` (`ClientID`);

--
-- Indexes for table `client`
--
ALTER TABLE `client` ADD PRIMARY KEY (`ClientID`),
ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `conversation`
--
ALTER TABLE `conversation` ADD PRIMARY KEY (`ConversationID`),
ADD UNIQUE KEY `unique_conversation` (`User1ID`, `User1Type`, `User2ID`, `User2Type`),
ADD KEY `idx_user1` (`User1ID`, `User1Type`),
ADD KEY `idx_user2` (`User2ID`, `User2Type`),
ADD KEY `idx_timestamp` (`LastMessageAt`);

--
-- Indexes for table `freelancer`
--
ALTER TABLE `freelancer` ADD PRIMARY KEY (`FreelancerID`),
ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `freelancerskill`
--
ALTER TABLE `freelancerskill` ADD PRIMARY KEY (`FreelancerID`, `SkillID`),
ADD KEY `SkillID` (`SkillID`);

--
-- Indexes for table `gig`
--
ALTER TABLE `gig` ADD PRIMARY KEY (`GigID`),
ADD KEY `FreelancerID` (`FreelancerID`);

--
-- Indexes for table `job`
--
ALTER TABLE `job` ADD PRIMARY KEY (`JobID`),
ADD KEY `ClientID` (`ClientID`);

--
-- Indexes for table `job_application`
--
ALTER TABLE `job_application` ADD PRIMARY KEY (`ApplicationID`),
ADD KEY `JobID` (`JobID`),
ADD KEY `FreelancerID` (`FreelancerID`),
ADD KEY `Status` (`Status`);

--
-- Indexes for table `job_application_answer`
--
ALTER TABLE `job_application_answer` ADD PRIMARY KEY (`AnswerID`),
ADD KEY `QuestionID` (`QuestionID`),
ADD KEY `FreelancerID` (`FreelancerID`),
ADD KEY `JobID` (`JobID`),
ADD KEY `SelectedOptionID` (`SelectedOptionID`);

--
-- Indexes for table `job_question`
--
ALTER TABLE `job_question` ADD PRIMARY KEY (`QuestionID`),
ADD KEY `JobID` (`JobID`);

--
-- Indexes for table `job_question_option`
--
ALTER TABLE `job_question_option` ADD PRIMARY KEY (`OptionID`),
ADD KEY `QuestionID` (`QuestionID`);

--
-- Indexes for table `message`
--
ALTER TABLE `message` ADD PRIMARY KEY (`MessageID`),
ADD KEY `idx_sender` (`SenderID`),
ADD KEY `idx_receiver` (`ReceiverID`),
ADD KEY `idx_timestamp` (`Timestamp`),
ADD KEY `idx_conversation` (`ConversationID`);

--
-- Indexes for table `message_notification`
--
ALTER TABLE `message_notification` ADD PRIMARY KEY (`NotificationID`),
ADD UNIQUE KEY `unique_notification` (
  `ReceiverID`,
  `ReceiverType`,
  `SenderID`,
  `SenderType`
);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset` ADD PRIMARY KEY (`ResetID`),
ADD KEY `idx_email` (`Email`),
ADD KEY `idx_expires` (`ExpiresAt`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment` ADD PRIMARY KEY (`PaymentID`);

--
-- Indexes for table `review`
--
ALTER TABLE `review` ADD PRIMARY KEY (`ReviewID`),
ADD KEY `FreelancerID` (`FreelancerID`),
ADD KEY `ClientID` (`ClientID`);

--
-- Indexes for table `skill`
--
ALTER TABLE `skill` ADD PRIMARY KEY (`SkillID`);

--
-- Indexes for table `wallet`
--
ALTER TABLE `wallet` ADD PRIMARY KEY (`WalletID`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions` ADD PRIMARY KEY (`TransactionID`),
ADD KEY `WalletID` (`WalletID`),
ADD KEY `idx_wallet_type_status` (`WalletID`, `Type`, `Status`),
ADD KEY `idx_created_at` (`CreatedAt`);

--
-- AUTO_INCREMENT for dumped tables
--
--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin` MODIFY `AdminID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;

--
-- AUTO_INCREMENT for table `agreement`
--
ALTER TABLE `agreement` MODIFY `AgreementID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 13;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client` MODIFY `ClientID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation` MODIFY `ConversationID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 19;

--
-- AUTO_INCREMENT for table `freelancer`
--
ALTER TABLE `freelancer` MODIFY `FreelancerID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 6;

--
-- AUTO_INCREMENT for table `gig`
--
ALTER TABLE `gig` MODIFY `GigID` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job`
--
ALTER TABLE `job` MODIFY `JobID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 6;

--
-- AUTO_INCREMENT for table `job_application`
--
ALTER TABLE `job_application` MODIFY `ApplicationID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `job_application_answer`
--
ALTER TABLE `job_application_answer` MODIFY `AnswerID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;

--
-- AUTO_INCREMENT for table `job_question`
--
ALTER TABLE `job_question` MODIFY `QuestionID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 3;

--
-- AUTO_INCREMENT for table `job_question_option`
--
ALTER TABLE `job_question_option` MODIFY `OptionID` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message` MODIFY `MessageID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 16;

--
-- AUTO_INCREMENT for table `message_notification`
--
ALTER TABLE `message_notification` MODIFY `NotificationID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 8;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset` MODIFY `ResetID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 15;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment` MODIFY `PaymentID` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review` MODIFY `ReviewID` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skill`
--
ALTER TABLE `skill` MODIFY `SkillID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 5;

--
-- AUTO_INCREMENT for table `wallet`
--
ALTER TABLE `wallet` MODIFY `WalletID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 6;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions` MODIFY `TransactionID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 15;

--
-- Constraints for dumped tables
--
--
-- Constraints for table `agreement`
--
ALTER TABLE `agreement` ADD CONSTRAINT `fk_agreement_client` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_agreement_freelancer` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE SET NULL;

--
-- Constraints for table `freelancerskill`
--
ALTER TABLE `freelancerskill` ADD CONSTRAINT `freelancerskill_ibfk_1` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE,
ADD CONSTRAINT `freelancerskill_ibfk_2` FOREIGN KEY (`SkillID`) REFERENCES `skill` (`SkillID`) ON DELETE CASCADE;

--
-- Constraints for table `gig`
--
ALTER TABLE `gig` ADD CONSTRAINT `gig_ibfk_1` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`);

--
-- Constraints for table `job`
--
ALTER TABLE `job` ADD CONSTRAINT `job_ibfk_1` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`) ON DELETE CASCADE;

--
-- Constraints for table `job_application`
--
ALTER TABLE `job_application` ADD CONSTRAINT `job_application_ibfk_1` FOREIGN KEY (`JobID`) REFERENCES `job` (`JobID`) ON DELETE CASCADE,
ADD CONSTRAINT `job_application_ibfk_2` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE;

--
-- Constraints for table `job_application_answer`
--
ALTER TABLE `job_application_answer` ADD CONSTRAINT `job_application_answer_ibfk_1` FOREIGN KEY (`QuestionID`) REFERENCES `job_question` (`QuestionID`) ON DELETE CASCADE,
ADD CONSTRAINT `job_application_answer_ibfk_2` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE,
ADD CONSTRAINT `job_application_answer_ibfk_3` FOREIGN KEY (`JobID`) REFERENCES `job` (`JobID`) ON DELETE CASCADE,
ADD CONSTRAINT `job_application_answer_ibfk_4` FOREIGN KEY (`SelectedOptionID`) REFERENCES `job_question_option` (`OptionID`) ON DELETE SET NULL;

--
-- Constraints for table `job_question`
--
ALTER TABLE `job_question` ADD CONSTRAINT `job_question_ibfk_1` FOREIGN KEY (`JobID`) REFERENCES `job` (`JobID`) ON DELETE CASCADE;

--
-- Constraints for table `job_question_option`
--
ALTER TABLE `job_question_option` ADD CONSTRAINT `job_question_option_ibfk_1` FOREIGN KEY (`QuestionID`) REFERENCES `job_question` (`QuestionID`) ON DELETE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message` ADD CONSTRAINT `message_ibfk_3` FOREIGN KEY (`ConversationID`) REFERENCES `conversation` (`ConversationID`) ON DELETE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review` ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE,
ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions` ADD CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`WalletID`) REFERENCES `wallet` (`WalletID`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;