-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 07, 2025 at 09:10 AM
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
    72,
    1,
    2,
    '2025-12-07 07:49:21',
    NULL,
    '2025-12-12',
    '/uploads/agreements/agreement_72.pdf',
    '2025-12-07 15:49:21',
    '/uploads/agreements/signature_c2_a72_1765093761.png',
    '2025-12-07 15:53:13',
    '2025-12-08 15:49:21',
    '/uploads/agreements/signature_f1_a72_1765093993.png',
    '• The freelancer will deliver the gig-based service as described within 5 day(s).\n• The client will pay RM 50.00 which is held in escrow.\n• Payment will be released upon successful delivery and client approval.\n• The service includes 1 revision(s).\n• Both parties agree to maintain professional conduct throughout the engagement.',
    'ongoing',
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
    `isDelete` tinyint (1) DEFAULT 0,
    `FailedLoginAttempts` int (11) NOT NULL DEFAULT 0,
    `LastFailedLoginAt` datetime DEFAULT NULL
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
    `isDelete`,
    `FailedLoginAttempts`,
    `LastFailedLoginAt`
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
    0,
    0,
    NULL
  ),
  (
    2,
    'Genting',
    '',
    'genting@gmail.com',
    '$2y$10$D1ON60Z0DruTc8tASwybi.VX6wu0nIPxZURmUDSrFEf6ZWb9c7Gv6',
    '0185709586',
    'uploads/profile_pictures/client_2_1764598184.png',
    'active',
    'NO 341, JALAN ZAMRUD 2, BATU LIMA',
    '2025-11-20 15:15:48',
    0,
    0,
    NULL
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
    0,
    0,
    NULL
  ),
  (
    4,
    'Your **257-character** text:  ``` Generating text of a specific character length is a simple task for an AI. I am precisely crafting this response to meet your strict requirement of 257 characters, including all spaces and punctuation. This constraint dem',
    NULL,
    'asdfasdf@gmail.com',
    '$2y$10$WDwb9dBBsmo1nRgvjIR/QO1VtMVBpL4l9D5oLEHZrM.FsuzD31CSC',
    NULL,
    NULL,
    'active',
    NULL,
    '2025-12-07 08:06:13',
    0,
    0,
    NULL
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
    '2025-12-02 06:26:16',
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
  ),
  (
    57,
    48,
    2,
    1,
    6.00,
    'hold',
    '2025-12-02 07:38:59',
    NULL
  ),
  (
    58,
    49,
    2,
    1,
    6.00,
    'hold',
    '2025-12-02 07:42:15',
    NULL
  ),
  (
    59,
    50,
    2,
    1,
    6.00,
    'hold',
    '2025-12-02 07:49:39',
    NULL
  ),
  (
    60,
    51,
    2,
    1,
    6.00,
    'hold',
    '2025-12-02 07:55:27',
    NULL
  ),
  (
    61,
    52,
    2,
    1,
    50.00,
    'hold',
    '2025-12-02 08:00:14',
    NULL
  ),
  (
    62,
    53,
    2,
    1,
    6.00,
    'hold',
    '2025-12-02 08:09:32',
    NULL
  ),
  (
    63,
    54,
    2,
    1,
    50.00,
    'hold',
    '2025-12-02 08:11:22',
    NULL
  ),
  (
    64,
    55,
    2,
    1,
    50.00,
    'hold',
    '2025-12-02 08:17:57',
    NULL
  ),
  (
    65,
    56,
    2,
    1,
    50.00,
    'refunded',
    '2025-12-02 08:22:12',
    NULL
  ),
  (
    66,
    57,
    2,
    1,
    50.00,
    'hold',
    '2025-12-02 08:22:42',
    NULL
  ),
  (
    67,
    58,
    2,
    1,
    6.00,
    'hold',
    '2025-12-02 08:38:21',
    NULL
  ),
  (
    68,
    59,
    2,
    1,
    50.00,
    'hold',
    '2025-12-02 08:39:44',
    NULL
  ),
  (
    69,
    60,
    2,
    1,
    50.00,
    'hold',
    '2025-12-02 08:43:42',
    NULL
  ),
  (
    70,
    61,
    2,
    1,
    50.00,
    'hold',
    '2025-12-02 08:49:44',
    NULL
  ),
  (
    71,
    62,
    2,
    1,
    91.00,
    'hold',
    '2025-12-02 08:56:36',
    NULL
  ),
  (
    72,
    63,
    2,
    1,
    91.00,
    'hold',
    '2025-12-02 09:04:00',
    NULL
  ),
  (
    73,
    64,
    2,
    1,
    132.00,
    'hold',
    '2025-12-02 09:14:08',
    NULL
  ),
  (
    74,
    65,
    2,
    1,
    214.00,
    'hold',
    '2025-12-02 09:17:49',
    NULL
  ),
  (
    75,
    66,
    2,
    1,
    50.00,
    'released',
    '2025-12-02 09:26:30',
    '2025-12-02 13:33:44'
  ),
  (
    76,
    67,
    2,
    1,
    173.00,
    'hold',
    '2025-12-02 09:32:18',
    NULL
  ),
  (
    77,
    68,
    2,
    1,
    1.00,
    'hold',
    '2025-12-02 13:21:19',
    NULL
  ),
  (
    78,
    69,
    2,
    1,
    5.00,
    'hold',
    '2025-12-02 13:24:38',
    NULL
  ),
  (
    79,
    70,
    2,
    1,
    12.00,
    'released',
    '2025-12-02 13:28:17',
    '2025-12-02 13:29:41'
  ),
  (
    80,
    71,
    2,
    1,
    2.00,
    'released',
    '2025-12-02 13:34:23',
    '2025-12-02 13:35:00'
  ),
  (
    81,
    72,
    2,
    1,
    50.00,
    'hold',
    '2025-12-07 07:49:21',
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
    `isDelete` tinyint (1) DEFAULT 0,
    `FailedLoginAttempts` int (11) NOT NULL DEFAULT 0,
    `LastFailedLoginAt` datetime DEFAULT NULL
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
    `isDelete`,
    `FailedLoginAttempts`,
    `LastFailedLoginAt`
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
    0,
    0,
    NULL
  ),
  (
    2,
    'lexas',
    'wer',
    'jc@gmail.com',
    '$2y$10$E/ktmWMUMTAD2uieh9uJ0eib0JfBZpGpzGG83b8/8JbNUH1httt2q',
    NULL,
    NULL,
    'suspended',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0.00,
    '2025-11-20 15:15:48',
    0,
    0,
    NULL
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
    0,
    0,
    NULL
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
  (1, 4, 'Intermediate');

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
  ),
  (
    4,
    1,
    'WEB DESIGN',
    'graphic-design',
    'brand-style-guide',
    'df,a',
    'WEB DESIGN',
    6,
    5,
    3,
    0,
    12,
    3,
    '/images/gig_media/gig-img-692e975a782c62.47083938.png',
    NULL,
    NULL,
    NULL,
    'active',
    '2025-12-02 15:38:04',
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
    87,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 50.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_59_1764664784.pdf',
    'application/pdf',
    '2025-12-02 16:39:44',
    'unread'
  ),
  (
    88,
    39,
    'c2',
    'f1',
    'Agreement signed successfully! The agreement \"asdf\" has been signed and is now active.',
    '/uploads/agreements/agreement_59.pdf',
    'application/pdf',
    '2025-12-02 16:39:55',
    'unread'
  ),
  (
    89,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 50.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_60_1764665022.pdf',
    'application/pdf',
    '2025-12-02 16:43:42',
    'unread'
  ),
  (
    90,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 50.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_61_1764665384.pdf',
    'application/pdf',
    '2025-12-02 16:49:44',
    'unread'
  ),
  (
    91,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 91.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_62_1764665796.pdf',
    'application/pdf',
    '2025-12-02 16:56:36',
    'unread'
  ),
  (
    92,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 91.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_63_1764666240.pdf',
    'application/pdf',
    '2025-12-02 17:04:00',
    'unread'
  ),
  (
    93,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 132.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_64_1764666848.pdf',
    'application/pdf',
    '2025-12-02 17:14:08',
    'unread'
  ),
  (
    94,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 214.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_65_1764667069.pdf',
    'application/pdf',
    '2025-12-02 17:17:49',
    'unread'
  ),
  (
    95,
    39,
    'c2',
    'f1',
    'Agreement signed successfully! The agreement \"asdf\" has been signed and is now active.',
    '/uploads/agreements/agreement_65.pdf',
    'application/pdf',
    '2025-12-02 17:20:27',
    'unread'
  ),
  (
    96,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 50.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_66_1764667590.pdf',
    'application/pdf',
    '2025-12-02 17:26:30',
    'unread'
  ),
  (
    97,
    39,
    'c2',
    'f1',
    'Agreement signed successfully! The agreement \"asdf\" has been signed and is now active.',
    '/uploads/agreements/agreement_66.pdf',
    'application/pdf',
    '2025-12-02 17:26:44',
    'unread'
  ),
  (
    98,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 173.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_67_1764667938.pdf',
    'application/pdf',
    '2025-12-02 17:32:18',
    'unread'
  ),
  (
    99,
    39,
    'f1',
    'c2',
    'I have signed the agreement for the project \"Google\". Please review and sign to proceed. The agreement is attached below.\n\n',
    '/uploads/agreements/agreement_26_1764681679.pdf',
    'application/pdf',
    '2025-12-02 21:21:20',
    'to_accept'
  ),
  (
    100,
    39,
    'f1',
    'c2',
    'I have signed the agreement for the project \"55\". Please review and sign to proceed. The agreement is attached below.\n\n',
    '/uploads/agreements/agreement_27_1764681878.pdf',
    'application/pdf',
    '2025-12-02 21:24:38',
    'to_accept'
  ),
  (
    101,
    39,
    'f1',
    'c2',
    'I have signed the agreement for the project \"jieyang\". Please review and sign to proceed. The agreement is attached below.\n\n',
    '/uploads/agreements/agreement_28_1764682097.pdf',
    'application/pdf',
    '2025-12-02 21:28:17',
    'to_accept'
  ),
  (
    102,
    39,
    'c2',
    'f1',
    'Agreement signed successfully! The agreement \"jieyang\" has been signed and is now active.',
    '/uploads/agreements/agreement_70.pdf',
    'application/pdf',
    '2025-12-02 21:28:44',
    'unread'
  ),
  (
    103,
    39,
    'f1',
    'c2',
    'I have signed the agreement for the project \"tttt\". Please review and sign to proceed. The agreement is attached below.\n\n',
    '/uploads/agreements/agreement_29_1764682463.pdf',
    'application/pdf',
    '2025-12-02 21:34:23',
    'to_accept'
  ),
  (
    104,
    39,
    'c2',
    'f1',
    'Agreement signed successfully! The agreement \"tttt\" has been signed and is now active.',
    '/uploads/agreements/agreement_71.pdf',
    'application/pdf',
    '2025-12-02 21:34:31',
    'unread'
  ),
  (
    105,
    39,
    'f1',
    'c2',
    'New gig order: \"asdf\" for RM 50.00. Please review and sign the agreement to confirm.',
    '/uploads/agreements/gig_agreement_72_1765093761.pdf',
    'application/pdf',
    '2025-12-07 15:49:22',
    'unread'
  ),
  (
    106,
    39,
    'c2',
    'f1',
    'Agreement signed successfully! The agreement \"asdf\" has been signed and is now active.',
    '/uploads/agreements/agreement_72.pdf',
    'application/pdf',
    '2025-12-07 15:53:13',
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
  ),
  (
    38,
    1,
    'freelancer',
    'New gig order from LEXAS for \'WEB DESIGN\'. Payment of RM 6.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    48,
    '2025-12-02 15:39:00',
    0,
    NULL
  ),
  (
    39,
    1,
    'freelancer',
    'New gig order from dfsa for \'WEB DESIGN\'. Payment of RM 6.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    49,
    '2025-12-02 15:42:15',
    0,
    NULL
  ),
  (
    40,
    1,
    'freelancer',
    'New gig order from adf for \'WEB DESIGN\'. Payment of RM 6.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    50,
    '2025-12-02 15:49:39',
    0,
    NULL
  ),
  (
    41,
    1,
    'freelancer',
    'New gig order from ASD for \'WEB DESIGN\'. Payment of RM 6.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    51,
    '2025-12-02 15:55:27',
    0,
    NULL
  ),
  (
    42,
    1,
    'freelancer',
    'New gig order from JIMMY CHAN KAH LOK for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    52,
    '2025-12-02 16:00:14',
    0,
    NULL
  ),
  (
    43,
    1,
    'freelancer',
    'New gig order from asdf for \'WEB DESIGN\'. Payment of RM 6.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    53,
    '2025-12-02 16:09:33',
    0,
    NULL
  ),
  (
    44,
    1,
    'freelancer',
    'New gig order from asdf for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    54,
    '2025-12-02 16:11:22',
    0,
    NULL
  ),
  (
    45,
    1,
    'freelancer',
    'New gig order from sad for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    55,
    '2025-12-02 16:17:57',
    0,
    NULL
  ),
  (
    46,
    1,
    'freelancer',
    'New gig order from adf for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    56,
    '2025-12-02 16:22:12',
    0,
    NULL
  ),
  (
    47,
    1,
    'freelancer',
    'New gig order from asdf for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    57,
    '2025-12-02 16:22:42',
    0,
    NULL
  ),
  (
    48,
    1,
    'freelancer',
    'New gig order from JIMMY CHAN KAH LOK for \'WEB DESIGN\'. Payment of RM 6.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    58,
    '2025-12-02 16:38:21',
    0,
    NULL
  ),
  (
    49,
    1,
    'freelancer',
    'New gig order from adf for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    59,
    '2025-12-02 16:39:44',
    0,
    NULL
  ),
  (
    50,
    1,
    'freelancer',
    'New gig order from sdf for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    60,
    '2025-12-02 16:43:42',
    0,
    NULL
  ),
  (
    51,
    1,
    'freelancer',
    'New gig order from asd for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    61,
    '2025-12-02 16:49:44',
    0,
    NULL
  ),
  (
    52,
    1,
    'freelancer',
    'New gig order from rtr for \'asdf\'. Payment of RM 91.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    62,
    '2025-12-02 16:56:36',
    0,
    NULL
  ),
  (
    53,
    1,
    'freelancer',
    'New gig order from sdfg for \'asdf\'. Payment of RM 91.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    63,
    '2025-12-02 17:04:00',
    0,
    NULL
  ),
  (
    54,
    1,
    'freelancer',
    'New gig order from af for \'asdf\'. Payment of RM 132.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    64,
    '2025-12-02 17:14:08',
    0,
    NULL
  ),
  (
    55,
    1,
    'freelancer',
    'New gig order from fdf for \'asdf\'. Payment of RM 214.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    65,
    '2025-12-02 17:17:49',
    0,
    NULL
  ),
  (
    56,
    1,
    'freelancer',
    'New gig order from asd for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    66,
    '2025-12-02 17:26:30',
    0,
    NULL
  ),
  (
    57,
    1,
    'freelancer',
    'New gig order from asdf for \'asdf\'. Payment of RM 173.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    67,
    '2025-12-02 17:32:18',
    0,
    NULL
  ),
  (
    58,
    2,
    'client',
    'Freelancer has submitted work for \'jieyang\'. Please review the deliverables.',
    'work_submission',
    4,
    '2025-12-02 21:29:17',
    0,
    NULL
  ),
  (
    59,
    1,
    'freelancer',
    'Your work for \'jieyang\' has been approved! Payment of RM 12.00 has been released to your wallet.',
    'work_approval',
    4,
    '2025-12-02 21:29:41',
    0,
    NULL
  ),
  (
    60,
    2,
    'client',
    'Freelancer has submitted work for \'asdf\'. Please review the deliverables.',
    'work_submission',
    5,
    '2025-12-02 21:30:27',
    0,
    NULL
  ),
  (
    61,
    2,
    'client',
    'Freelancer has submitted work for \'asdf\'. Please review the deliverables.',
    'work_submission',
    6,
    '2025-12-02 21:32:52',
    0,
    NULL
  ),
  (
    62,
    1,
    'freelancer',
    'Your work for \'asdf\' has been approved! Payment of RM 50.00 has been released to your wallet.',
    'work_approval',
    5,
    '2025-12-02 21:33:44',
    0,
    NULL
  ),
  (
    63,
    2,
    'client',
    'Freelancer has submitted work for \'tttt\'. Please review the deliverables.',
    'work_submission',
    7,
    '2025-12-02 21:34:46',
    0,
    NULL
  ),
  (
    64,
    1,
    'freelancer',
    'Your work for \'tttt\' has been approved! Payment of RM 2.00 has been released to your wallet.',
    'work_approval',
    7,
    '2025-12-02 21:35:00',
    0,
    NULL
  ),
  (
    65,
    1,
    'freelancer',
    'New gig order from asdf for \'asdf\'. Payment of RM 50.00 is held in escrow. Please review and accept the agreement.',
    'gig_order',
    72,
    '2025-12-07 15:49:22',
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
  (2, '2', 778.00, 2308.00, '2025-12-07 07:49:21'),
  (3, '1', 1499.00, 1127.00, '2025-12-02 13:35:00'),
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
  ),
  (
    115,
    2,
    'payment',
    6.00,
    'completed',
    'Payment for gig \'WEB DESIGN\' - Funds locked in escrow (Agreement #48)',
    'escrow_57',
    '2025-12-02 07:38:59'
  ),
  (
    116,
    2,
    'payment',
    6.00,
    'completed',
    'Payment for gig \'WEB DESIGN\' - Funds locked in escrow (Agreement #49)',
    'escrow_58',
    '2025-12-02 07:42:15'
  ),
  (
    117,
    2,
    'payment',
    6.00,
    'completed',
    'Payment for gig \'WEB DESIGN\' - Funds locked in escrow (Agreement #50)',
    'escrow_59',
    '2025-12-02 07:49:39'
  ),
  (
    118,
    2,
    'payment',
    6.00,
    'completed',
    'Payment for gig \'WEB DESIGN\' - Funds locked in escrow (Agreement #51)',
    'escrow_60',
    '2025-12-02 07:55:27'
  ),
  (
    119,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #52)',
    'escrow_61',
    '2025-12-02 08:00:14'
  ),
  (
    120,
    2,
    'payment',
    6.00,
    'completed',
    'Payment for gig \'WEB DESIGN\' - Funds locked in escrow (Agreement #53)',
    'escrow_62',
    '2025-12-02 08:09:32'
  ),
  (
    121,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #54)',
    'escrow_63',
    '2025-12-02 08:11:22'
  ),
  (
    122,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #55)',
    'escrow_64',
    '2025-12-02 08:17:57'
  ),
  (
    123,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #56)',
    'escrow_65',
    '2025-12-02 08:22:12'
  ),
  (
    124,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #57)',
    'escrow_66',
    '2025-12-02 08:22:42'
  ),
  (
    125,
    2,
    'refund',
    50.00,
    'completed',
    'Refund - Agreement declined: asdf (Agreement #56)',
    'escrow_refund_65',
    '2025-12-02 08:34:50'
  ),
  (
    126,
    2,
    'payment',
    6.00,
    'completed',
    'Payment for gig \'WEB DESIGN\' - Funds locked in escrow (Agreement #58)',
    'escrow_67',
    '2025-12-02 08:38:21'
  ),
  (
    127,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #59)',
    'escrow_68',
    '2025-12-02 08:39:44'
  ),
  (
    128,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #60)',
    'escrow_69',
    '2025-12-02 08:43:42'
  ),
  (
    129,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #61)',
    'escrow_70',
    '2025-12-02 08:49:44'
  ),
  (
    130,
    2,
    'payment',
    91.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #62)',
    'escrow_71',
    '2025-12-02 08:56:36'
  ),
  (
    131,
    2,
    'payment',
    91.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #63)',
    'escrow_72',
    '2025-12-02 09:04:00'
  ),
  (
    132,
    2,
    'payment',
    132.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #64)',
    'escrow_73',
    '2025-12-02 09:14:08'
  ),
  (
    133,
    2,
    'payment',
    214.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #65)',
    'escrow_74',
    '2025-12-02 09:17:49'
  ),
  (
    134,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #66)',
    'escrow_75',
    '2025-12-02 09:26:30'
  ),
  (
    135,
    2,
    'topup',
    1000.00,
    'completed',
    'Wallet Top Up via Stripe',
    NULL,
    '2025-12-02 09:27:41'
  ),
  (
    136,
    2,
    'payment',
    173.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #67)',
    'escrow_76',
    '2025-12-02 09:32:18'
  ),
  (
    137,
    2,
    'payment',
    1.00,
    'completed',
    'Funds locked in escrow for project: Google (Agreement #68)',
    'escrow_77',
    '2025-12-02 13:21:19'
  ),
  (
    138,
    2,
    'payment',
    5.00,
    'completed',
    'Funds locked in escrow for project: 55 (Agreement #69)',
    'escrow_78',
    '2025-12-02 13:24:38'
  ),
  (
    139,
    2,
    'payment',
    12.00,
    'completed',
    'Funds locked in escrow for project: jieyang (Agreement #70)',
    'escrow_79',
    '2025-12-02 13:28:17'
  ),
  (
    140,
    3,
    '',
    12.00,
    'pending',
    'Payment received for \'jieyang\' (Agreement #70)',
    NULL,
    '2025-12-02 13:29:41'
  ),
  (
    141,
    2,
    'payment',
    12.00,
    'pending',
    'Payment released for \'jieyang\' (Agreement #70)',
    NULL,
    '2025-12-02 13:29:41'
  ),
  (
    142,
    3,
    '',
    50.00,
    'pending',
    'Payment received for \'asdf\' (Agreement #66)',
    NULL,
    '2025-12-02 13:33:44'
  ),
  (
    143,
    2,
    'payment',
    50.00,
    'pending',
    'Payment released for \'asdf\' (Agreement #66)',
    NULL,
    '2025-12-02 13:33:44'
  ),
  (
    144,
    2,
    'payment',
    2.00,
    'completed',
    'Funds locked in escrow for project: tttt (Agreement #71)',
    'escrow_80',
    '2025-12-02 13:34:23'
  ),
  (
    145,
    3,
    '',
    2.00,
    'pending',
    'Payment received for \'tttt\' (Agreement #71)',
    NULL,
    '2025-12-02 13:35:00'
  ),
  (
    146,
    2,
    'payment',
    2.00,
    'pending',
    'Payment released for \'tttt\' (Agreement #71)',
    NULL,
    '2025-12-02 13:35:00'
  ),
  (
    147,
    2,
    'payment',
    50.00,
    'completed',
    'Payment for gig \'asdf\' - Funds locked in escrow (Agreement #72)',
    'escrow_81',
    '2025-12-07 07:49:21'
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
AUTO_INCREMENT = 73;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client` MODIFY `ClientID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 5;

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
AUTO_INCREMENT = 82;

--
-- AUTO_INCREMENT for table `freelancer`
--
ALTER TABLE `freelancer` MODIFY `FreelancerID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 6;

--
-- AUTO_INCREMENT for table `gig`
--
ALTER TABLE `gig` MODIFY `GigID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 5;

--
-- AUTO_INCREMENT for table `job`
--
ALTER TABLE `job` MODIFY `JobID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 25;

--
-- AUTO_INCREMENT for table `job_application`
--
ALTER TABLE `job_application` MODIFY `ApplicationID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 30;

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
AUTO_INCREMENT = 107;

--
-- AUTO_INCREMENT for table `message_notification`
--
ALTER TABLE `message_notification` MODIFY `NotificationID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications` MODIFY `NotificationID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 66;

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
AUTO_INCREMENT = 8;

--
-- AUTO_INCREMENT for table `wallet`
--
ALTER TABLE `wallet` MODIFY `WalletID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 6;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions` MODIFY `TransactionID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 148;

--
-- AUTO_INCREMENT for table `work_submissions`
--
ALTER TABLE `work_submissions` MODIFY `SubmissionID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 8;

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