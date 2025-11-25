-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2025 at 06:29 AM
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
    `Terms` text DEFAULT NULL,
    `SignedDate` date DEFAULT NULL,
    `Status` varchar(50) DEFAULT NULL,
    `ProjectTitle` varchar(255) DEFAULT NULL,
    `Scope` text DEFAULT NULL,
    `Deliverables` text DEFAULT NULL,
    `PaymentAmount` decimal(10, 2) DEFAULT NULL,
    `ProjectDetail` text DEFAULT NULL,
    `FreelancerName` varchar(255) DEFAULT NULL,
    `SignaturePath` varchar(255) DEFAULT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `agreement`
--
INSERT INTO
  `agreement` (
    `AgreementID`,
    `Terms`,
    `SignedDate`,
    `Status`,
    `ProjectTitle`,
    `Scope`,
    `Deliverables`,
    `PaymentAmount`,
    `ProjectDetail`,
    `FreelancerName`,
    `SignaturePath`
  )
VALUES
  (
    1,
    'sdfasdf',
    '2025-11-18',
    'pending',
    'asdf',
    'asdf',
    'asdf',
    1.00,
    'asdf',
    NULL,
    NULL
  ),
  (
    2,
    'sdf',
    '2025-11-18',
    'pending',
    'asdf',
    'asdf',
    'asdf',
    12.00,
    'asdf',
    NULL,
    NULL
  ),
  (
    3,
    'If cannot finish at 31/11/2025. The service will be FREE',
    '2025-11-18',
    'pending',
    'Web Design',
    'One page of landing Page with good design',
    '31/11/2025 should finish it',
    1000.00,
    'The landing Page',
    NULL,
    NULL
  ),
  (
    4,
    'asdf',
    '2025-11-18',
    'pending',
    'asdf',
    'asdf',
    'asdfasdf',
    11111.00,
    'asdf',
    NULL,
    NULL
  ),
  (
    5,
    'adf',
    '2025-11-18',
    'pending',
    'asfdf',
    'sdfasdf',
    'dafasd',
    12.00,
    'asdfa',
    NULL,
    NULL
  ),
  (
    6,
    'asdf',
    '2025-11-18',
    'pending',
    'asdfasdf',
    'asdfasdf',
    'asdf',
    1.00,
    'asdfasdf',
    NULL,
    NULL
  ),
  (
    7,
    'asdfasdf',
    '2025-11-18',
    'pending',
    'asdfas',
    'as',
    'asf',
    222.00,
    'dfasdf',
    NULL,
    NULL
  ),
  (
    8,
    'sdfasdf',
    '2025-11-18',
    'pending',
    'asdfasdf',
    'asdf',
    'asdf',
    111.00,
    'af',
    NULL,
    NULL
  ),
  (
    9,
    '阿斯蒂芬',
    '2025-11-18',
    'pending',
    'asdf',
    '阿斯顿法国红酒看来',
    '阿斯蒂芬',
    555.00,
    '阿斯顿法国红酒看来',
    NULL,
    NULL
  ),
  (
    10,
    'ASDF',
    '2025-11-21',
    'pending',
    'SDF',
    'ASDF',
    'ASDF',
    12.00,
    'ASDF',
    NULL,
    NULL
  ),
  (
    11,
    'asdf',
    '2025-11-23',
    'pending',
    'asdf',
    'asdf',
    'asdf',
    111.00,
    'asdf',
    'JIMMY CHAN KAH LOK',
    'signature_1763868278_69227e766c312.png'
  ),
  (
    12,
    'asdf',
    '2025-11-23',
    'pending',
    'asdf',
    'asdfa',
    'asdf',
    12.00,
    'asdf',
    'asdf',
    'signature_1763881238_6922b116571c4.png'
  ),
  (
    13,
    'asdf',
    '2025-11-24',
    'pending',
    'asdf',
    'asdf',
    'asdf',
    12.35,
    'asdf',
    'JIMMY CHAN LOK',
    'signature_1763988455_692453e7f3eab.png'
  ),
  (
    14,
    'dsf',
    '2025-11-24',
    'pending',
    'asdf',
    'asdf',
    'asdf',
    12.00,
    'asdf',
    'JIMMY CHAN LOK',
    'signature_1763989102_6924566e4fc47.png'
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
    11,
    1,
    'freelancer',
    3,
    'client',
    '2025-11-19 07:29:22',
    '2025-11-21 17:06:04',
    'active',
    NULL
  ),
  (
    12,
    1,
    'freelancer',
    2,
    'client',
    '2025-11-23 06:10:41',
    '2025-11-23 06:34:33',
    'active',
    NULL
  ),
  (
    13,
    1,
    'freelancer',
    1,
    'client',
    '2025-11-23 06:21:39',
    '2025-11-23 06:25:09',
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
    `Deadline`,
    `Status`,
    `PostDate`
  )
VALUES
  (
    1,
    1,
    'asdf',
    'asdf',
    12.00,
    '2025-11-14',
    'available',
    '2025-11-19'
  ),
  (
    2,
    3,
    'Google',
    'asdf',
    888.00,
    '2025-11-15',
    'available',
    '2025-11-19'
  ),
  (
    3,
    2,
    'asdf',
    'asdf',
    123.00,
    '2025-11-24',
    'available',
    '2025-11-23'
  );

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
    97,
    11,
    'c3',
    'f1',
    'hi',
    NULL,
    NULL,
    '2025-11-19 15:29:31',
    'unread'
  ),
  (
    98,
    11,
    'f1',
    'c3',
    'hi JIMMY',
    NULL,
    NULL,
    '2025-11-22 01:05:59',
    'unread'
  ),
  (
    99,
    11,
    'c3',
    'f1',
    'Ya Hi Google',
    NULL,
    NULL,
    '2025-11-22 01:06:04',
    'unread'
  ),
  (
    100,
    13,
    'c1',
    'f1',
    'hi',
    NULL,
    NULL,
    '2025-11-23 14:25:09',
    'unread'
  ),
  (
    101,
    12,
    'c2',
    'f1',
    'I am interested in your project: \"asdf\". I would like to discuss more about this opportunity.',
    NULL,
    NULL,
    '2025-11-23 14:34:33',
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
ALTER TABLE `agreement` ADD PRIMARY KEY (`AgreementID`);

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
AUTO_INCREMENT = 15;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client` MODIFY `ClientID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation` MODIFY `ConversationID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 14;

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
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message` MODIFY `MessageID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 102;

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
-- Constraints for dumped tables
--
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
-- Constraints for table `message`
--
ALTER TABLE `message` ADD CONSTRAINT `message_ibfk_3` FOREIGN KEY (`ConversationID`) REFERENCES `conversation` (`ConversationID`) ON DELETE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review` ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE,
ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;