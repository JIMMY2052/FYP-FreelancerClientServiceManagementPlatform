-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 12:35 PM
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

-- --------------------------------------------------------
--
-- Table structure for table `agreement`
--
CREATE TABLE
    `agreement` (
        `AgreementID` int (11) NOT NULL,
        `ApplicationID` int (11) DEFAULT NULL,
        `Terms` text DEFAULT NULL,
        `SignedDate` date DEFAULT NULL,
        `Status` varchar(50) DEFAULT NULL,
        `ProjectTitle` varchar(255) DEFAULT NULL,
        `Scope` text DEFAULT NULL,
        `Deliverables` text DEFAULT NULL,
        `PaymentAmount` decimal(10, 2) DEFAULT NULL,
        `ProjectDetail` text DEFAULT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

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
        `Status` varchar(50) DEFAULT NULL,
        `Address` text DEFAULT NULL
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
        `Status`,
        `Address`
    )
VALUES
    (
        1,
        'Sitecore',
        NULL,
        'jimmyckl-wm22@student.tarc.edu.my',
        '$2y$10$BxSAHpJnrutlLgnTkuFehOACPPqYFQ/vXaEgjahq2cei0u4A/irUO',
        NULL,
        'active',
        NULL
    ),
    (
        2,
        'Genting',
        NULL,
        'genting@gmail.com',
        '$2y$10$D1ON60Z0DruTc8tASwybi.VX6wu0nIPxZURmUDSrFEf6ZWb9c7Gv6',
        NULL,
        'active',
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
        2,
        1,
        'freelancer',
        1,
        'client',
        '2025-11-16 12:44:52',
        '2025-11-17 03:31:59',
        'active',
        '{\"user_id\": \"1\", \"user_type\": \"client\", \"deleted_at\": \"2025-11-16 22:22:09\"}'
    ),
    (
        3,
        2,
        'freelancer',
        1,
        'client',
        '2025-11-16 12:48:54',
        '2025-11-16 14:31:33',
        'active',
        '{\"user_id\": \"1\", \"user_type\": \"client\", \"deleted_at\": \"2025-11-16 22:22:03\"}'
    ),
    (
        4,
        3,
        'freelancer',
        1,
        'client',
        '2025-11-16 15:01:15',
        '2025-11-16 15:52:01',
        'active',
        NULL
    ),
    (
        5,
        2,
        'client',
        1,
        'freelancer',
        '2025-11-16 15:51:18',
        '2025-11-16 15:51:18',
        'active',
        NULL
    ),
    (
        6,
        2,
        'client',
        3,
        'freelancer',
        '2025-11-16 15:52:37',
        '2025-11-16 15:52:44',
        'active',
        NULL
    ),
    (
        7,
        4,
        'freelancer',
        1,
        'client',
        '2025-11-17 03:35:39',
        '2025-11-17 08:13:56',
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
        `Status` varchar(50) DEFAULT NULL,
        `Address` text DEFAULT NULL,
        `Experience` text DEFAULT NULL,
        `Education` text DEFAULT NULL,
        `SocialMediaURL` varchar(255) DEFAULT NULL,
        `Bio` text DEFAULT NULL,
        `RatingAverage` decimal(3, 2) DEFAULT NULL,
        `TotalEarned` decimal(10, 2) DEFAULT NULL
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
        `Status`,
        `Address`,
        `Experience`,
        `Education`,
        `SocialMediaURL`,
        `Bio`,
        `RatingAverage`,
        `TotalEarned`
    )
VALUES
    (
        1,
        'JIMMY CHAN',
        'LOK',
        'jimmychankahlok66@gmail.com',
        '$2y$10$jZYJ20FbflriS3ibKasx7O9faf9bJmmaU6U2tuTi9wyHdVSpyGnCu',
        '0185709586',
        'active',
        'NO 5, Lorong masria 3, taman bunga raya',
        '3 Years Experience in Web Development',
        'Bachelor Degree in Software Engineering',
        'https://linked.in',
        'asdf',
        NULL,
        0.00
    ),
    (
        2,
        'lexas',
        'wer',
        'jc@gmail.com',
        '$2y$10$E/ktmWMUMTAD2uieh9uJ0eib0JfBZpGpzGG83b8/8JbNUH1httt2q',
        NULL,
        'active',
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        0.00
    ),
    (
        3,
        'hc',
        'c',
        'hc@gmail.com',
        '$2y$10$ZOgz2kiWcxShvnSqc2P7dejfEqak/MncYY2EukVaLiuhGggJXaCPG',
        NULL,
        'active',
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL
    ),
    (
        4,
        'John',
        'Lee',
        'john@gmail.com',
        '$2y$10$9yQ0dOnB/3KlF50FluBsceeKYovo88cNqPR.BHPn8jfMh67Un4UgC',
        '0185709586',
        'active',
        'NO 341, JALAN ZAMRUD 2, BATU LIMA',
        '3 Year experience in web developement',
        '',
        'https://linked.in',
        'Professional Web Developer',
        NULL,
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
    (4, 3, 'Intermediate');

-- --------------------------------------------------------
--
-- Table structure for table `history`
--
CREATE TABLE
    `history` (
        `HistoryID` int (11) NOT NULL,
        `ApplicationID` int (11) DEFAULT NULL,
        `Action` text DEFAULT NULL,
        `Timestamp` datetime DEFAULT NULL,
        `FreelancerID` int (11) DEFAULT NULL,
        `ClientID` int (11) DEFAULT NULL
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
        `Status` varchar(50) DEFAULT NULL,
        `PostDate` date DEFAULT NULL
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
        26,
        NULL,
        '1',
        '1',
        'asdfasd',
        NULL,
        NULL,
        '2025-11-16 20:35:32',
        'unread'
    ),
    (
        27,
        2,
        '1',
        '1',
        'asdfsadf',
        NULL,
        NULL,
        '2025-11-16 20:44:52',
        'unread'
    ),
    (
        28,
        2,
        '1',
        '1',
        'asdf',
        NULL,
        NULL,
        '2025-11-16 20:46:31',
        'unread'
    ),
    (
        29,
        3,
        '1',
        '2',
        'hi',
        NULL,
        NULL,
        '2025-11-16 20:48:54',
        'unread'
    ),
    (
        33,
        2,
        '1',
        '1',
        'asdf',
        NULL,
        NULL,
        '2025-11-16 20:49:18',
        'unread'
    ),
    (
        48,
        2,
        'f1',
        'c1',
        'hi again',
        NULL,
        NULL,
        '2025-11-16 22:08:23',
        'unread'
    ),
    (
        49,
        2,
        'c1',
        'f1',
        'yo',
        NULL,
        NULL,
        '2025-11-16 22:29:25',
        'unread'
    ),
    (
        50,
        2,
        'c1',
        'f1',
        NULL,
        '/uploads/messages/1763303379_f2fb852a.pdf',
        'application/pdf',
        '2025-11-16 22:29:39',
        'unread'
    ),
    (
        51,
        2,
        'f1',
        'c1',
        'he',
        NULL,
        NULL,
        '2025-11-16 22:29:45',
        'unread'
    ),
    (
        52,
        3,
        'c1',
        'f2',
        'asdf',
        NULL,
        NULL,
        '2025-11-16 22:31:27',
        'unread'
    ),
    (
        53,
        3,
        'f2',
        'c1',
        'asdf',
        NULL,
        NULL,
        '2025-11-16 22:31:33',
        'unread'
    ),
    (
        54,
        4,
        'c1',
        'f3',
        'df',
        NULL,
        NULL,
        '2025-11-16 23:01:15',
        'unread'
    ),
    (
        55,
        5,
        'f1',
        'c2',
        'hi',
        NULL,
        NULL,
        '2025-11-16 23:51:18',
        'unread'
    ),
    (
        56,
        4,
        'c1',
        'f3',
        'das',
        NULL,
        NULL,
        '2025-11-16 23:51:42',
        'unread'
    ),
    (
        57,
        4,
        'c1',
        'f3',
        'asdf',
        NULL,
        NULL,
        '2025-11-16 23:52:01',
        'unread'
    ),
    (
        58,
        6,
        'f3',
        'c2',
        'asdf',
        NULL,
        NULL,
        '2025-11-16 23:52:37',
        'unread'
    ),
    (
        59,
        6,
        'c2',
        'f3',
        'dfg',
        NULL,
        NULL,
        '2025-11-16 23:52:44',
        'unread'
    ),
    (
        60,
        2,
        'c1',
        'f1',
        'testing',
        NULL,
        NULL,
        '2025-11-17 11:31:50',
        'unread'
    ),
    (
        61,
        2,
        'f1',
        'c1',
        'testing',
        NULL,
        NULL,
        '2025-11-17 11:31:59',
        'unread'
    ),
    (
        62,
        7,
        'c1',
        'f4',
        'hello',
        NULL,
        NULL,
        '2025-11-17 11:35:39',
        'unread'
    ),
    (
        63,
        7,
        'f4',
        'c1',
        'I am SiteCore',
        NULL,
        NULL,
        '2025-11-17 11:35:50',
        'unread'
    ),
    (
        64,
        7,
        'c1',
        'f4',
        'Hi I am John Lee',
        NULL,
        NULL,
        '2025-11-17 16:01:58',
        'unread'
    ),
    (
        65,
        7,
        'f4',
        'c1',
        'Hi',
        NULL,
        NULL,
        '2025-11-17 16:02:10',
        'unread'
    ),
    (
        66,
        7,
        'c1',
        'f4',
        'zxcv',
        NULL,
        NULL,
        '2025-11-17 16:04:00',
        'unread'
    ),
    (
        67,
        7,
        'c1',
        'f4',
        'hi',
        NULL,
        NULL,
        '2025-11-17 16:12:59',
        'unread'
    ),
    (
        68,
        7,
        'c1',
        'f4',
        'jj',
        NULL,
        NULL,
        '2025-11-17 16:13:56',
        'unread'
    );

-- --------------------------------------------------------
--
-- Table structure for table `milestone`
--
CREATE TABLE
    `milestone` (
        `MilestoneID` int (11) NOT NULL,
        `AgreementID` int (11) NOT NULL,
        `MilestoneNumber` int (11) NOT NULL,
        `MilestoneName` varchar(255) NOT NULL,
        `Description` longtext DEFAULT NULL,
        `DueDate` date NOT NULL,
        `Amount` decimal(12, 2) NOT NULL,
        `Status` enum ('pending', 'in_progress', 'completed', 'delayed') DEFAULT 'pending',
        `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
        `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
-- Table structure for table `service`
--
CREATE TABLE
    `service` (
        `ServiceID` int (11) NOT NULL,
        `FreelancerID` int (11) NOT NULL,
        `Title` varchar(150) NOT NULL,
        `Category` varchar(100) NOT NULL,
        `Subcategory` varchar(100) NOT NULL,
        `Description` text NOT NULL,
        `Price` decimal(10, 2) NOT NULL,
        `DeliveryTime` int (11) NOT NULL,
        `RevisionCount` int (11) NOT NULL,
        `ThumnailUrl` varchar(255) NOT NULL,
        `GalleryUrl` text NOT NULL,
        `Status` enum ('active', 'paused', 'deleted') NOT NULL,
        `Visibility` enum ('public', 'private') NOT NULL,
        `Rating` decimal(3, 2) NOT NULL,
        `RatingCount` int (11) NOT NULL,
        `CreatedAt` datetime NOT NULL,
        `UpdatedAt` datetime NOT NULL
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
    (3, 'networking');

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
-- Indexes for table `history`
--
ALTER TABLE `history` ADD PRIMARY KEY (`HistoryID`),
ADD KEY `FreelancerID` (`FreelancerID`),
ADD KEY `ClientID` (`ClientID`);

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
-- Indexes for table `milestone`
--
ALTER TABLE `milestone` ADD PRIMARY KEY (`MilestoneID`),
ADD UNIQUE KEY `unique_milestone_number` (`AgreementID`, `MilestoneNumber`),
ADD KEY `idx_agreement` (`AgreementID`);

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
-- Indexes for table `service`
--
ALTER TABLE `service` ADD PRIMARY KEY (`ServiceID`),
ADD KEY `FreelancerID` (`FreelancerID`);

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
ALTER TABLE `admin` MODIFY `AdminID` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agreement`
--
ALTER TABLE `agreement` MODIFY `AgreementID` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client` MODIFY `ClientID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 3;

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation` MODIFY `ConversationID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 8;

--
-- AUTO_INCREMENT for table `freelancer`
--
ALTER TABLE `freelancer` MODIFY `FreelancerID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 5;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history` MODIFY `HistoryID` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job`
--
ALTER TABLE `job` MODIFY `JobID` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message` MODIFY `MessageID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 69;

--
-- AUTO_INCREMENT for table `milestone`
--
ALTER TABLE `milestone` MODIFY `MilestoneID` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment` MODIFY `PaymentID` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review` MODIFY `ReviewID` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service` MODIFY `ServiceID` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skill`
--
ALTER TABLE `skill` MODIFY `SkillID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- Constraints for dumped tables
--
--
-- Constraints for table `freelancerskill`
--
ALTER TABLE `freelancerskill` ADD CONSTRAINT `freelancerskill_ibfk_1` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE,
ADD CONSTRAINT `freelancerskill_ibfk_2` FOREIGN KEY (`SkillID`) REFERENCES `skill` (`SkillID`) ON DELETE CASCADE;

--
-- Constraints for table `history`
--
ALTER TABLE `history` ADD CONSTRAINT `history_ibfk_2` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`),
ADD CONSTRAINT `history_ibfk_3` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`);

--
-- Constraints for table `job`
--
ALTER TABLE `job` ADD CONSTRAINT `job_ibfk_1` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`) ON DELETE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message` ADD CONSTRAINT `message_ibfk_3` FOREIGN KEY (`ConversationID`) REFERENCES `conversation` (`ConversationID`) ON DELETE CASCADE;

--
-- Constraints for table `milestone`
--
ALTER TABLE `milestone` ADD CONSTRAINT `milestone_ibfk_1` FOREIGN KEY (`AgreementID`) REFERENCES `agreement` (`AgreementID`) ON DELETE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review` ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE,
ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`) ON DELETE CASCADE;

--
-- Constraints for table `service`
--
ALTER TABLE `service` ADD CONSTRAINT `service_ibfk_1` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;