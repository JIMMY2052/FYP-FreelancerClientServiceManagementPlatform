-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2025 at 01:39 PM
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
    `CompleteDate` date DEFAULT NULL,
    `DeliveryDate` date DEFAULT NULL,
    `agreeementPath` varchar(255) DEFAULT NULL,
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
    `RemainingRevisions` int (11) NOT NULL DEFAULT 3,
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
    `CompleteDate`,
    `DeliveryDate`,
    `agreeementPath`,
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
    `RemainingRevisions`,
    `ProjectDetail`,
    `FreelancerName`,
    `ClientName`
  )
VALUES
  (
    47,
    1,
    2,
    '2025-11-30 12:03:34',
    NULL,
    '2025-12-05',
    '/uploads/agreements/agreement_47.pdf',
    '2025-11-30 20:03:34',
    '/uploads/agreements/signature_c2_a47_1764504214.png',
    '2025-11-30 20:03:42',
    '2025-12-01 20:03:34',
    '/uploads/agreements/signature_f1_a47_1764504222.png',
    '• The freelancer will deliver the gig-based service as described within 5 day(s).\n• The client will pay RM 50.00 which is held in escrow.\n• Payment will be released upon successful delivery and client approval.\n• The service includes 1 revision(s).\n• Both parties agree to maintain professional conduct throughout the engagement.',
    'disputed',
    'asdf',
    'Gig-based service: asdf',
    5,
    'sdfgasdfga',
    50.00,
    1,
    'sdfgasdfga',
    'JIMMY CHAN LOK',
    'asdf'
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
    39,
    2,
    'client',
    1,
    'freelancer',
    '2025-11-29 14:36:24',
    NULL,
    'active',
    NULL
  );

-- --------------------------------------------------------
--
-- Table structure for table `dispute`
--
CREATE TABLE
  `dispute` (
    `DisputeID` int (11) NOT NULL,
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
    `ResolvedByAdminID` int (11) DEFAULT NULL COMMENT 'Admin who resolved dispute'
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'Tracks disputes filed on agreements';

--
-- Dumping data for table `dispute`
--
INSERT INTO
  `dispute` (
    `DisputeID`,
    `AgreementID`,
    `InitiatorID`,
    `InitiatorType`,
    `ReasonText`,
    `EvidenceFile`,
    `Status`,
    `CreatedAt`,
    `AdminNotesText`,
    `ResolutionAction`,
    `ResolvedAt`,
    `ResolvedByAdminID`
  )
VALUES
  (
    9,
    47,
    1,
    'freelancer',
    'Non-delivery of work\n\n77888',
    NULL,
    'resolved',
    '2025-11-30 12:36:01',
    'asdf',
    'refund_client',
    '2025-11-30 13:48:31',
    1
  );

-- --------------------------------------------------------
--
-- Table structure for table `escrow`
--
CREATE TABLE
  `escrow` (
    `EscrowID` int (11) NOT NULL,
    `OrderID` int (11) DEFAULT NULL COMMENT 'Order/Project ID if applicable',
    `PayerID` int (11) NOT NULL COMMENT 'Client ID who pays',
    `PayeeID` int (11) NOT NULL COMMENT 'Freelancer ID who receives',
    `Amount` decimal(10, 2) NOT NULL,
    `Status` enum ('hold', 'released', 'refunded') NOT NULL DEFAULT 'hold',
    `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
    `ReleasedAt` timestamp NULL DEFAULT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `escrow`
--
INSERT INTO
  `escrow` (
    `EscrowID`,
    `OrderID`,
    `PayerID`,
    `PayeeID`,
    `Amount`,
    `Status`,
    `CreatedAt`,
    `ReleasedAt`
  )
VALUES
  (
    1,
    2,
    2,
    1,
    12.00,
    'hold',
    '2025-11-26 04:56:03',
    NULL
  ),
  (
    2,
    7,
    2,
    3,
    12.00,
    'hold',
    '2025-11-26 04:59:56',
    NULL
  ),
  (
    3,
    8,
    2,
    1,
    666.00,
    'hold',
    '2025-11-26 05:22:15',
    NULL
  ),
  (
    4,
    9,
    2,
    3,
    666.00,
    'hold',
    '2025-11-26 05:44:58',
    NULL
  ),
  (
    5,
    10,
    2,
    3,
    12.00,
    'hold',
    '2025-11-26 05:47:52',
    NULL
  ),
  (
    6,
    11,
    2,
    3,
    12.00,
    'hold',
    '2025-11-26 05:55:46',
    NULL
  ),
  (
    7,
    12,
    2,
    3,
    1.00,
    'hold',
    '2025-11-26 06:44:19',
    NULL
  ),
  (
    8,
    13,
    2,
    3,
    12.00,
    'hold',
    '2025-11-26 07:32:20',
    NULL
  ),
  (
    9,
    14,
    2,
    3,
    1.00,
    'hold',
    '2025-11-26 07:41:01',
    NULL
  ),
  (
    10,
    1,
    2,
    3,
    1.00,
    'hold',
    '2025-11-26 08:36:30',
    NULL
  ),
  (
    11,
    2,
    2,
    3,
    1.00,
    'hold',
    '2025-11-26 11:46:13',
    NULL
  ),
  (
    12,
    3,
    2,
    3,
    1.00,
    'hold',
    '2025-11-26 12:45:18',
    NULL
  ),
  (
    13,
    4,
    2,
    3,
    12.00,
    'hold',
    '2025-11-26 12:57:41',
    NULL
  ),
  (
    14,
    5,
    2,
    3,
    1.00,
    'hold',
    '2025-11-26 12:59:56',
    NULL
  ),
  (
    15,
    6,
    2,
    3,
    1.00,
    'hold',
    '2025-11-26 13:05:23',
    NULL
  ),
  (
    16,
    7,
    2,
    3,
    1.00,
    'hold',
    '2025-11-26 13:11:17',
    NULL
  ),
  (
    17,
    8,
    2,
    3,
    1.00,
    'hold',
    '2025-11-26 13:18:03',
    NULL
  ),
  (
    18,
    9,
    2,
    3,
    1.00,
    'hold',
    '2025-11-26 13:26:12',
    NULL
  ),
  (
    19,
    10,
    2,
    3,
    1.00,
    'hold',
    '2025-11-26 13:39:26',
    NULL
  ),
  (
    20,
    11,
    1,
    1,
    12.00,
    'hold',
    '2025-11-27 03:58:55',
    NULL
  ),
  (
    21,
    12,
    1,
    1,
    1.00,
    'hold',
    '2025-11-27 10:48:50',
    NULL
  ),
  (
    22,
    13,
    1,
    1,
    12.00,
    'hold',
    '2025-11-27 10:55:01',
    NULL
  ),
  (
    23,
    14,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 10:56:02',
    NULL
  ),
  (
    24,
    15,
    1,
    1,
    1.00,
    'hold',
    '2025-11-27 11:40:58',
    NULL
  ),
  (
    25,
    16,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 11:52:00',
    NULL
  ),
  (
    26,
    17,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 12:11:17',
    NULL
  ),
  (
    28,
    19,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 12:27:54',
    NULL
  ),
  (
    29,
    20,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 12:56:07',
    NULL
  ),
  (
    30,
    21,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 13:03:10',
    NULL
  ),
  (
    31,
    22,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 13:08:42',
    NULL
  ),
  (
    32,
    23,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 13:12:16',
    NULL
  ),
  (
    33,
    24,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 13:13:39',
    NULL
  ),
  (
    34,
    25,
    1,
    1,
    50.00,
    'refunded',
    '2025-11-27 13:41:35',
    NULL
  ),
  (
    35,
    26,
    1,
    1,
    50.00,
    'released',
    '2025-11-27 13:46:24',
    '2025-11-27 14:45:39'
  ),
  (
    36,
    27,
    1,
    1,
    50.00,
    'released',
    '2025-11-27 14:02:56',
    '2025-11-27 15:02:16'
  ),
  (
    37,
    28,
    1,
    1,
    1.00,
    'hold',
    '2025-11-27 14:12:54',
    NULL
  ),
  (
    38,
    29,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 15:23:41',
    NULL
  ),
  (
    39,
    30,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 15:25:50',
    NULL
  ),
  (
    40,
    31,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 15:34:53',
    NULL
  ),
  (
    41,
    32,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 15:42:52',
    NULL
  ),
  (
    42,
    33,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 15:46:31',
    NULL
  ),
  (
    43,
    34,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 15:50:14',
    NULL
  ),
  (
    44,
    35,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 15:51:48',
    NULL
  ),
  (
    45,
    36,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 15:54:34',
    NULL
  ),
  (
    46,
    37,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 15:55:19',
    NULL
  ),
  (
    47,
    38,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 15:57:32',
    NULL
  ),
  (
    48,
    39,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 16:08:00',
    NULL
  ),
  (
    49,
    40,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 16:09:50',
    NULL
  ),
  (
    50,
    41,
    1,
    1,
    50.00,
    'hold',
    '2025-11-27 16:18:55',
    NULL
  ),
  (
    51,
    42,
    1,
    1,
    50.00,
    'released',
    '2025-11-27 16:27:08',
    '2025-11-27 16:32:07'
  ),
  (
    52,
    43,
    2,
    1,
    50.00,
    'hold',
    '2025-11-29 13:12:05',
    NULL
  ),
  (
    53,
    44,
    2,
    1,
    50.00,
    'hold',
    '2025-11-29 13:59:03',
    NULL
  ),
  (
    54,
    45,
    2,
    1,
    50.00,
    'hold',
    '2025-11-29 14:36:23',
    NULL
  ),
  (
    55,
    46,
    2,
    1,
    50.00,
    'hold',
    '2025-11-30 08:57:36',
    NULL
  ),
  (
    56,
    47,
    2,
    1,
    50.00,
    'refunded',
    '2025-11-30 12:03:34',
    '2025-11-30 13:48:31'
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
    'uploads/profile_pictures/freelancer_1_1764238297.png',
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

--
-- Dumping data for table `gig`
--
INSERT INTO
  `gig` (
    `GigID`,
    `FreelancerID`,
    `Title`,
    `Category`,
    `Subcategory`,
    `SearchTags`,
    `Description`,
    `Price`,
    `DeliveryTime`,
    `RushDelivery`,
    `RushDeliveryPrice`,
    `AdditionalRevision`,
    `RevisionCount`,
    `Image1Path`,
    `Image2Path`,
    `Image3Path`,
    `VideoPath`,
    `Status`,
    `CreatedAt`,
    `UpdatedAt`
  )
VALUES
  (
    1,
    1,
    'asdf',
    'graphic-design',
    'brand-style-guide',
    'asdf',
    'sdfgasdfga',
    50,
    5,
    NULL,
    0,
    41,
    1,
    '/images/gig_media/gig-img-69258fe1668d22.24165885.png',
    '/images/gig_media/gig-img-69258fe166b923.17453196.png',
    '/images/gig_media/gig-img-69258fe166ef69.11102469.png',
    NULL,
    '',
    '2025-11-26 12:44:04',
    NULL
  ),
  (
    2,
    1,
    'asdf',
    'graphic-design',
    'game-art',
    'asdf',
    'sdfgasdfga',
    50,
    5,
    NULL,
    0,
    41,
    1,
    '/images/gig_media/gig-img-69282e95e87051.18313842.png',
    '/images/gig_media/gig-img-69282e95e8a631.02946817.png',
    '/images/gig_media/gig-img-69282e95e8cea0.25785486.png',
    NULL,
    '',
    '2025-11-27 18:57:27',
    NULL
  ),
  (
    3,
    1,
    'asdf',
    'graphic-design',
    'brand-style-guide',
    'asdf',
    'sdfgasdfga',
    50,
    5,
    NULL,
    0,
    41,
    1,
    '/images/gig_media/gig-img-69283fbf5a78b4.19865488.png',
    NULL,
    NULL,
    NULL,
    'active',
    '2025-11-27 20:10:40',
    NULL
  );

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
    13,
    2,
    'Hello',
    'asdf',
    1.00,
    3,
    '2025-11-29',
    'processing',
    '2025-11-26'
  ),
  (
    14,
    1,
    'dsfasdf',
    'asdf',
    12.00,
    5,
    '2025-11-29',
    'processing',
    '2025-11-27'
  ),
  (
    15,
    1,
    'hhh',
    'hhh',
    1.00,
    6,
    '2025-11-29',
    'processing',
    '2025-11-27'
  ),
  (
    16,
    1,
    'dfg',
    'asdf',
    12.00,
    32,
    '2025-11-28',
    'processing',
    '2025-11-27'
  ),
  (
    17,
    1,
    'asdf',
    'asdf',
    1.00,
    21,
    '2025-11-29',
    'processing',
    '2025-11-27'
  ),
  (
    18,
    1,
    'asdf',
    'asdf',
    1.00,
    3,
    '2025-11-29',
    '',
    '2025-11-27'
  ),
  (
    19,
    2,
    'Google',
    'gdhf',
    1.00,
    3,
    '2025-12-20',
    'available',
    '2025-11-30'
  ),
  (
    20,
    2,
    '12',
    'asdf',
    1.00,
    1,
    '2025-12-06',
    'available',
    '2025-11-30'
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
    18,
    13,
    3,
    NULL,
    NULL,
    NULL,
    'accepted',
    '2025-11-26 13:17:36',
    '2025-11-26 13:39:26'
  ),
  (
    19,
    14,
    1,
    NULL,
    NULL,
    NULL,
    'accepted',
    '2025-11-27 03:56:26',
    '2025-11-27 03:58:55'
  ),
  (
    20,
    15,
    1,
    NULL,
    NULL,
    NULL,
    'accepted',
    '2025-11-27 10:47:36',
    '2025-11-27 10:48:50'
  ),
  (
    21,
    16,
    1,
    NULL,
    NULL,
    NULL,
    'accepted',
    '2025-11-27 10:54:48',
    '2025-11-27 10:55:01'
  ),
  (
    22,
    17,
    1,
    NULL,
    NULL,
    NULL,
    'accepted',
    '2025-11-27 11:40:41',
    '2025-11-27 11:40:58'
  ),
  (
    23,
    18,
    1,
    NULL,
    NULL,
    NULL,
    'accepted',
    '2025-11-27 14:12:39',
    '2025-11-27 14:12:54'
  ),
  (
    24,
    19,
    1,
    NULL,
    NULL,
    NULL,
    'pending',
    '2025-11-30 07:47:42',
    NULL
  ),
  (
    25,
    20,
    1,
    NULL,
    NULL,
    NULL,
    'pending',
    '2025-11-30 07:50:45',
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
    62,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 50.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_45_1764426984.pdf',
    'application/pdf',
    '2025-11-29 22:36:24',
    'unread'
  ),
  (
    63,
    39,
    'c2',
    'f1',
    'Agreement signed successfully! The agreement \"asdf\" has been signed and is now active.',
    '/uploads/agreements/agreement_45.pdf',
    'application/pdf',
    '2025-11-29 22:36:38',
    'unread'
  ),
  (
    64,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 50.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_46_1764493057.pdf',
    'application/pdf',
    '2025-11-30 16:57:37',
    'unread'
  ),
  (
    65,
    39,
    'c2',
    'f1',
    'Agreement signed successfully! The agreement \"asdf\" has been signed and is now active.',
    '/uploads/agreements/agreement_46.pdf',
    'application/pdf',
    '2025-11-30 16:57:44',
    'unread'
  ),
  (
    66,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 50.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_47_1764504214.pdf',
    'application/pdf',
    '2025-11-30 20:03:34',
    'unread'
  ),
  (
    67,
    39,
    'c2',
    'f1',
    'Agreement signed successfully! The agreement \"asdf\" has been signed and is now active.',
    '/uploads/agreements/agreement_47.pdf',
    'application/pdf',
    '2025-11-30 20:03:42',
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
-- Table structure for table `notifications`
--
CREATE TABLE
  `notifications` (
    `NotificationID` int (11) NOT NULL,
    `UserID` int (11) NOT NULL,
    `UserType` enum ('client', 'freelancer', 'admin') NOT NULL,
    `Message` text NOT NULL,
    `RelatedType` varchar(50) DEFAULT NULL,
    `RelatedID` int (11) DEFAULT NULL,
    `CreatedAt` datetime NOT NULL,
    `IsRead` tinyint (1) DEFAULT 0,
    `ReadAt` datetime DEFAULT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--
INSERT INTO
  `notifications` (
    `NotificationID`,
    `UserID`,
    `UserType`,
    `Message`,
    `RelatedType`,
    `RelatedID`,
    `CreatedAt`,
    `IsRead`,
    `ReadAt`
  )
VALUES
  (
    26,
    1,
    'freelancer',
    'New gig order from ii for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    38,
    '2025-11-27 23:57:32',
    0,
    NULL
  ),
  (
    27,
    1,
    'freelancer',
    'New gig order from 111 for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    39,
    '2025-11-28 00:08:00',
    0,
    NULL
  ),
  (
    28,
    1,
    'freelancer',
    'New gig order from yhj for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    40,
    '2025-11-28 00:09:50',
    0,
    NULL
  ),
  (
    29,
    1,
    'freelancer',
    'New gig order from JIE YANG for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    41,
    '2025-11-28 00:18:55',
    0,
    NULL
  ),
  (
    30,
    1,
    'freelancer',
    'New gig order from pop for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    42,
    '2025-11-28 00:27:08',
    0,
    NULL
  ),
  (
    31,
    1,
    'client',
    'Freelancer has submitted work for \'asdf\'. Please review the deliverables.',
    'work_submission',
    3,
    '2025-11-28 00:32:02',
    0,
    NULL
  ),
  (
    32,
    1,
    'freelancer',
    'Your work for \'asdf\' has been approved! Payment of RM 50.00 has been released to your wallet.',
    'work_approval',
    3,
    '2025-11-28 00:32:07',
    0,
    NULL
  ),
  (
    33,
    1,
    'freelancer',
    'New gig order from JIMMY CHAN KAH LOK for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    43,
    '2025-11-29 21:12:05',
    0,
    NULL
  ),
  (
    34,
    1,
    'freelancer',
    'New gig order from asdf for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    44,
    '2025-11-29 21:59:04',
    0,
    NULL
  ),
  (
    35,
    1,
    'freelancer',
    'New gig order from okk for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    45,
    '2025-11-29 22:36:24',
    0,
    NULL
  ),
  (
    36,
    1,
    'freelancer',
    'New gig order from asd for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    46,
    '2025-11-30 16:57:37',
    0,
    NULL
  ),
  (
    37,
    1,
    'freelancer',
    'New gig order from asdf for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    47,
    '2025-11-30 20:03:34',
    0,
    NULL
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
-- Table structure for table `submission_files`
--
CREATE TABLE
  `submission_files` (
    `FileID` int (11) NOT NULL,
    `SubmissionID` int (11) NOT NULL,
    `OriginalFileName` varchar(255) NOT NULL,
    `StoredFileName` varchar(255) NOT NULL,
    `FilePath` varchar(500) NOT NULL,
    `FileSize` bigint (20) NOT NULL,
    `FileType` varchar(50) DEFAULT NULL,
    `UploadedAt` datetime NOT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

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
  (2, '2', 985.00, 1165.00, '2025-11-30 13:48:31'),
  (3, '1', 1435.00, 1127.00, '2025-11-30 13:41:28'),
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
    82,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #45)',
    'escrow_54',
    '2025-11-29 14:36:23'
  ),
  (
    83,
    3,
    '',
    50.00,
    'completed',
    'Dispute reversal: Payment deducted for agreement #45',
    'dispute_reverse_release_45',
    '2025-11-30 07:29:08'
  ),
  (
    84,
    2,
    'refund',
    50.00,
    'completed',
    'Dispute refund for agreement #45: ',
    'dispute_refund_45',
    '2025-11-30 07:40:44'
  ),
  (
    85,
    2,
    '',
    50.00,
    'completed',
    'Dispute reversal: Refund deducted for agreement #45',
    'dispute_reverse_refund_45',
    '2025-11-30 07:52:34'
  ),
  (
    86,
    3,
    'earning',
    50.00,
    'completed',
    'Dispute resolution: Payment released for agreement #45: ',
    'dispute_release_45',
    '2025-11-30 07:53:18'
  ),
  (
    87,
    3,
    '',
    50.00,
    'completed',
    'Dispute reversal: Payment deducted for agreement #45',
    'dispute_reverse_release_45',
    '2025-11-30 07:55:00'
  ),
  (
    88,
    2,
    'refund',
    50.00,
    'completed',
    'Dispute refund for agreement #45: HI',
    'dispute_refund_45',
    '2025-11-30 08:21:18'
  ),
  (
    89,
    2,
    '',
    50.00,
    'completed',
    'Dispute reversal: Refund deducted for agreement #45',
    'dispute_reverse_refund_45',
    '2025-11-30 08:22:14'
  ),
  (
    90,
    2,
    'refund',
    50.00,
    'completed',
    'Dispute refund for agreement #45: ',
    'dispute_refund_45',
    '2025-11-30 08:23:41'
  ),
  (
    91,
    2,
    '',
    50.00,
    'completed',
    'Dispute reversal: Refund deducted for agreement #45',
    'dispute_reverse_refund_45',
    '2025-11-30 08:24:35'
  ),
  (
    92,
    2,
    'refund',
    50.00,
    'completed',
    'Dispute refund for agreement #45: ',
    'dispute_refund_45',
    '2025-11-30 08:38:08'
  ),
  (
    93,
    2,
    '',
    50.00,
    'completed',
    'Dispute reversal: Refund deducted for agreement #45',
    'dispute_reverse_refund_45',
    '2025-11-30 08:38:15'
  ),
  (
    94,
    3,
    'topup',
    1000.00,
    'completed',
    'Wallet Top Up via Stripe',
    NULL,
    '2025-11-30 08:54:28'
  ),
  (
    95,
    2,
    'topup',
    1000.00,
    'completed',
    'Wallet Top Up via Stripe',
    NULL,
    '2025-11-30 08:54:33'
  ),
  (
    96,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #46)',
    'escrow_55',
    '2025-11-30 08:57:36'
  ),
  (
    97,
    2,
    'refund',
    50.00,
    'completed',
    'Dispute refund for agreement #46: ',
    'dispute_refund_46',
    '2025-11-30 09:03:21'
  ),
  (
    98,
    2,
    '',
    50.00,
    'completed',
    'Dispute reversal: Refund deducted for agreement #46',
    'dispute_reverse_refund_46',
    '2025-11-30 09:03:37'
  ),
  (
    99,
    3,
    'earning',
    50.00,
    'completed',
    'Dispute resolution: Payment released for agreement #46: ',
    'dispute_release_46',
    '2025-11-30 09:04:43'
  ),
  (
    100,
    3,
    '',
    50.00,
    'completed',
    'Dispute reversal: Payment deducted for agreement #46',
    'dispute_reverse_release_46',
    '2025-11-30 09:05:41'
  ),
  (
    101,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #47)',
    'escrow_56',
    '2025-11-30 12:03:34'
  ),
  (
    102,
    2,
    'refund',
    50.00,
    'completed',
    'Dispute refund for agreement #47: ',
    'dispute_refund_47',
    '2025-11-30 12:35:28'
  ),
  (
    103,
    2,
    '',
    50.00,
    'completed',
    'Dispute reversal: Refund deducted for agreement #47',
    'dispute_reverse_refund_47',
    '2025-11-30 12:35:37'
  ),
  (
    104,
    3,
    'earning',
    50.00,
    'completed',
    'Dispute resolution: Payment released for agreement #47: ',
    'dispute_release_47',
    '2025-11-30 12:36:11'
  ),
  (
    105,
    3,
    '',
    50.00,
    'completed',
    'Dispute reversal: Payment deducted for agreement #47',
    'dispute_reverse_release_47',
    '2025-11-30 12:53:16'
  ),
  (
    106,
    2,
    'refund',
    50.00,
    'completed',
    'Dispute refund for agreement #47: ',
    'dispute_refund_47',
    '2025-11-30 12:53:27'
  ),
  (
    107,
    2,
    '',
    50.00,
    'completed',
    'Dispute reversal: Refund deducted for agreement #47',
    'dispute_reverse_refund_47',
    '2025-11-30 12:56:57'
  ),
  (
    108,
    2,
    'refund',
    50.00,
    'completed',
    'Dispute refund for agreement #47: asdf',
    'dispute_refund_47',
    '2025-11-30 12:59:22'
  ),
  (
    109,
    2,
    '',
    50.00,
    'completed',
    'Dispute reversal: Refund deducted for agreement #47',
    'dispute_reverse_refund_47',
    '2025-11-30 12:59:27'
  ),
  (
    110,
    2,
    'refund',
    50.00,
    'completed',
    'Dispute refund for agreement #47: uuyy',
    'dispute_refund_47',
    '2025-11-30 13:03:09'
  ),
  (
    111,
    2,
    '',
    50.00,
    'completed',
    'Dispute reversal: Refund deducted for agreement #47',
    'dispute_reverse_refund_47',
    '2025-11-30 13:03:12'
  ),
  (
    112,
    3,
    'earning',
    50.00,
    'completed',
    'Dispute resolution: Payment released for agreement #47: ',
    'dispute_release_47',
    '2025-11-30 13:36:46'
  ),
  (
    113,
    3,
    '',
    50.00,
    'completed',
    'Dispute reversal: Payment deducted for agreement #47',
    'dispute_reverse_release_47',
    '2025-11-30 13:41:28'
  ),
  (
    114,
    2,
    'refund',
    50.00,
    'completed',
    'Dispute refund for agreement #47: asdf',
    'dispute_refund_47',
    '2025-11-30 13:48:31'
  );

-- --------------------------------------------------------
--
-- Table structure for table `work_submissions`
--
CREATE TABLE
  `work_submissions` (
    `SubmissionID` int (11) NOT NULL,
    `AgreementID` int (11) NOT NULL,
    `FreelancerID` int (11) NOT NULL,
    `ClientID` int (11) NOT NULL,
    `SubmissionTitle` varchar(255) NOT NULL,
    `SubmissionNotes` text DEFAULT NULL,
    `Status` enum (
      'pending_review',
      'approved',
      'rejected',
      'revision_requested'
    ) DEFAULT 'pending_review',
    `ReviewNotes` text DEFAULT NULL,
    `ReviewedAt` datetime DEFAULT NULL,
    `SubmittedAt` datetime NOT NULL,
    `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

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
-- Indexes for table `dispute`
--
ALTER TABLE `dispute` ADD PRIMARY KEY (`DisputeID`),
ADD KEY `fk_dispute_admin` (`ResolvedByAdminID`),
ADD KEY `idx_agreement` (`AgreementID`),
ADD KEY `idx_initiator` (`InitiatorID`),
ADD KEY `idx_status` (`Status`),
ADD KEY `idx_created` (`CreatedAt`);

--
-- Indexes for table `escrow`
--
ALTER TABLE `escrow` ADD PRIMARY KEY (`EscrowID`),
ADD KEY `PayerID` (`PayerID`),
ADD KEY `PayeeID` (`PayeeID`),
ADD KEY `Status` (`Status`);

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
-- Indexes for table `notifications`
--
ALTER TABLE `notifications` ADD PRIMARY KEY (`NotificationID`),
ADD KEY `idx_user` (`UserID`, `UserType`),
ADD KEY `idx_read` (`IsRead`),
ADD KEY `idx_created` (`CreatedAt`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset` ADD PRIMARY KEY (`ResetID`),
ADD KEY `idx_email` (`Email`),
ADD KEY `idx_expires` (`ExpiresAt`);

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
-- Indexes for table `submission_files`
--
ALTER TABLE `submission_files` ADD PRIMARY KEY (`FileID`),
ADD KEY `idx_submission` (`SubmissionID`);

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
-- Indexes for table `work_submissions`
--
ALTER TABLE `work_submissions` ADD PRIMARY KEY (`SubmissionID`),
ADD KEY `idx_agreement` (`AgreementID`),
ADD KEY `idx_freelancer` (`FreelancerID`),
ADD KEY `idx_client` (`ClientID`),
ADD KEY `idx_status` (`Status`);

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
AUTO_INCREMENT = 48;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client` MODIFY `ClientID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation` MODIFY `ConversationID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 40;

--
-- AUTO_INCREMENT for table `dispute`
--
ALTER TABLE `dispute` MODIFY `DisputeID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 10;

--
-- AUTO_INCREMENT for table `escrow`
--
ALTER TABLE `escrow` MODIFY `EscrowID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 57;

--
-- AUTO_INCREMENT for table `freelancer`
--
ALTER TABLE `freelancer` MODIFY `FreelancerID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 6;

--
-- AUTO_INCREMENT for table `gig`
--
ALTER TABLE `gig` MODIFY `GigID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `job`
--
ALTER TABLE `job` MODIFY `JobID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 21;

--
-- AUTO_INCREMENT for table `job_application`
--
ALTER TABLE `job_application` MODIFY `ApplicationID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 26;

--
-- AUTO_INCREMENT for table `job_application_answer`
--
ALTER TABLE `job_application_answer` MODIFY `AnswerID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

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
AUTO_INCREMENT = 68;

--
-- AUTO_INCREMENT for table `message_notification`
--
ALTER TABLE `message_notification` MODIFY `NotificationID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications` MODIFY `NotificationID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 38;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset` MODIFY `ResetID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 16;

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
-- AUTO_INCREMENT for table `submission_files`
--
ALTER TABLE `submission_files` MODIFY `FileID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `wallet`
--
ALTER TABLE `wallet` MODIFY `WalletID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 6;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions` MODIFY `TransactionID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 115;

--
-- AUTO_INCREMENT for table `work_submissions`
--
ALTER TABLE `work_submissions` MODIFY `SubmissionID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- Constraints for dumped tables
--
--
-- Constraints for table `agreement`
--
ALTER TABLE `agreement` ADD CONSTRAINT `fk_agreement_client` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_agreement_freelancer` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE SET NULL;

--
-- Constraints for table `dispute`
--
ALTER TABLE `dispute` ADD CONSTRAINT `fk_dispute_admin` FOREIGN KEY (`ResolvedByAdminID`) REFERENCES `admin` (`AdminID`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_dispute_agreement` FOREIGN KEY (`AgreementID`) REFERENCES `agreement` (`AgreementID`) ON DELETE CASCADE;

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
-- Constraints for table `submission_files`
--
ALTER TABLE `submission_files` ADD CONSTRAINT `submission_files_ibfk_1` FOREIGN KEY (`SubmissionID`) REFERENCES `work_submissions` (`SubmissionID`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions` ADD CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`WalletID`) REFERENCES `wallet` (`WalletID`) ON DELETE CASCADE;

--
-- Constraints for table `work_submissions`
--
ALTER TABLE `work_submissions` ADD CONSTRAINT `work_submissions_ibfk_1` FOREIGN KEY (`AgreementID`) REFERENCES `agreement` (`AgreementID`) ON DELETE CASCADE,
ADD CONSTRAINT `work_submissions_ibfk_2` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE,
ADD CONSTRAINT `work_submissions_ibfk_3` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;