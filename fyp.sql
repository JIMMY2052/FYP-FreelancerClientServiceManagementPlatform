-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 12, 2025 at 08:12 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


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

CREATE TABLE `admin` (
  `AdminID` int(11) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Status` varchar(50) DEFAULT 'active',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`AdminID`, `Email`, `Password`, `Status`, `CreatedAt`) VALUES
(1, 'jimmychankahlok@gmail.com', '$2y$10$BdoF5Lx6GUYgEASR5uJAUuYhOs0gldiguXd6VmG2X3Pv/I.WRZH3e', 'active', '2025-11-19 13:13:06');

-- --------------------------------------------------------

--
-- Table structure for table `agreement`
--

CREATE TABLE `agreement` (
  `AgreementID` int(11) NOT NULL,
  `FreelancerID` int(11) DEFAULT NULL,
  `ClientID` int(11) DEFAULT NULL,
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
  `DeliveryTime` int(11) NOT NULL,
  `Deliverables` text DEFAULT NULL,
  `PaymentAmount` decimal(10,2) DEFAULT NULL,
  `RemainingRevisions` int(11) NOT NULL DEFAULT 3,
  `ProjectDetail` text DEFAULT NULL,
  `FreelancerName` varchar(255) DEFAULT NULL,
  `ClientName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agreement`
--

INSERT INTO `agreement` (`AgreementID`, `FreelancerID`, `ClientID`, `CreatedDate`, `CompleteDate`, `DeliveryDate`, `agreeementPath`, `ClientSignedDate`, `ClientSignaturePath`, `FreelancerSignedDate`, `ExpiredDate`, `FreelancerSignaturePath`, `Terms`, `Status`, `ProjectTitle`, `Scope`, `DeliveryTime`, `Deliverables`, `PaymentAmount`, `RemainingRevisions`, `ProjectDetail`, `FreelancerName`, `ClientName`) VALUES
(27, 8, 5, '2025-11-30 07:54:45', NULL, '2025-12-07', '/uploads/agreements/agreement_27.pdf', '2025-11-30 15:54:45', '/uploads/agreements/signature_c5_a15_1764489285.png', '2025-11-30 15:55:30', '2025-12-01 15:54:45', '/uploads/agreements/signature_f8_a27_1764489330.png', '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.', 'completed', 'Mobile Application Project', '0', 7, 'To be completed upon milestone deliveries as agreed.', 1500.00, 3, 'Mobile Application Project', 'Jie Yang Loo', 'Loo Jie Yang'),
(28, 8, 5, '2025-11-30 12:41:10', NULL, '2025-12-05', '/uploads/agreements/agreement_28.pdf', '2025-11-30 20:41:10', '/uploads/agreements/signature_c5_a28_1764506470.png', '2025-11-30 20:49:11', '2025-12-01 20:41:10', '/uploads/agreements/signature_f8_a28_1764506951.png', '• The freelancer will deliver the gig-based service as described within 5 day(s).\n• The client will pay RM 1,700.00 which is held in escrow.\n• Payment will be released upon successful delivery and client approval.\n• The service includes 2 revision(s).\n• Both parties agree to maintain professional conduct throughout the engagement.', 'completed', 'I will do wix website design, redesign business wix website, website development', 'Gig-based service: I will do wix website design, redesign business wix website, website development', 5, '7 figure Dropship Expert at Your Service:\r\n\r\n\r\n\r\nI\'m not only here to launch your store effectively and teach you how to grow a brand!\r\n\r\n\r\n\r\nDid you know 95% of Shopify Dropshipping stores FAIL due to easily avoidable mistakes?\r\n\r\n\r\n\r\nWhy Choose Me?\r\n\r\nDropshipping has allowed me to work for myself for over 5 years\r\n\r\nYou probably bought something from my stores\r\n\r\nOver 5 years experience \r\n\r\nBeginner-friendly model\r\n\r\nEasy to understand - work at a pace that suits you\r\n\r\nA genuine person who w', 1700.00, 0, '7 figure Dropship Expert at Your Service:\r\n\r\n\r\n\r\nI\'m not only here to launch your store effectively and teach you how to grow a brand!\r\n\r\n\r\n\r\nDid you know 95% of Shopify Dropshipping stores FAIL due to easily avoidable mistakes?\r\n\r\n\r\n\r\nWhy Choose Me?\r\n\r\nDropshipping has allowed me to work for myself for over 5 years\r\n\r\nYou probably bought something from my stores\r\n\r\nOver 5 years experience \r\n\r\nBeginner-friendly model\r\n\r\nEasy to understand - work at a pace that suits you\r\n\r\nA genuine person who wants to help you succeed!\r\n\r\n\r\n\r\nWhat You Will Receive:\r\n\r\nA fully operational automated dropshipping website. \r\n\r\nProfessionally designed store made to convert with a premium theme\r\n\r\nHelp You select a profitable trendy product with title optimization\r\n\r\n Installing apps that increase conversions\r\n\r\nMy Personal Support before and after the project. Answer all your queries \r\n\r\nA store built to CONVERT!\r\n\r\n Custom Coded Website (Stand out from the crowd!)\r\n\r\n Compelling product descriptions with targeted keywords\r\n\r\n  Share my private agent with you', 'Jie Yang Loo', 'Loo Jie Yang'),
(29, 8, 6, '2025-12-07 07:43:25', NULL, '2025-12-14', '/uploads/agreements/agreement_29.pdf', '2025-12-07 15:43:25', '/uploads/agreements/signature_c6_a16_1765093405.png', '2025-12-07 15:44:42', '2025-12-08 15:43:25', '/uploads/agreements/signature_f8_a29_1765093482.png', '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.', '', 'We are looking for graphic designer', '0', 7, 'To be completed upon milestone deliveries as agreed.', 800.00, 3, 'we are looking for graphic designer', 'Jie Yang Loo', 'Lee Chun Yin'),
(30, 8, 5, '2025-12-07 08:06:32', NULL, '2025-12-14', '/uploads/agreements/agreement_30.pdf', '2025-12-07 16:06:32', '/uploads/agreements/signature_c5_a17_1765094792.png', '2025-12-07 16:06:54', '2025-12-08 16:06:32', '/uploads/agreements/signature_f8_a30_1765094814.png', '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.', '', 'We are looking for Java developer to develop a website using Java.', '0', 7, 'To be completed upon milestone deliveries as agreed.', 1000.00, 3, 'Java Developer', 'Jie Yang Loo', 'Lee Chun Yin'),
(31, 8, 5, '2025-12-08 09:29:23', NULL, '2025-12-12', '/uploads/agreements/agreement_31.pdf', '2025-12-08 17:29:23', '/uploads/agreements/signature_c5_a18_1765186163.png', '2025-12-08 17:30:32', '2025-12-09 17:29:23', '/uploads/agreements/signature_f8_a31_1765186231.png', '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.', '', 'Design an E-commerce Website', '0', 4, 'To be completed upon milestone deliveries as agreed.', 1200.00, 3, 'design an e-commerce website', 'Jie Yang Loo', 'Loo Jie Yang'),
(32, 9, 5, '2025-12-11 12:14:49', NULL, '2025-12-16', '/uploads/agreements/agreement_32.pdf', '2025-12-11 20:14:49', '/uploads/agreements/signature_c5_a20_1765455289.png', '2025-12-12 00:25:00', '2025-12-12 20:14:49', '/uploads/agreements/signature_f9_a32_1765470299.png', '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.', 'ongoing', 'We are looking for desinger to design a poster for us', '0', 5, 'To be completed upon milestone deliveries as agreed.', 500.00, 3, 'we are looking for desinger to design a poster for us.', 'Jonathan Loo', 'Google'),
(33, 8, 5, '2025-12-11 16:46:48', NULL, NULL, '/uploads/agreements/agreement_22_1765471608.pdf', '2025-12-12 00:46:48', '/uploads/agreements/signature_c5_a22_1765471608.png', '2025-12-12 14:47:18', '2025-12-13 00:46:48', NULL, '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.', 'declined', 'web design project', '0', 7, 'To be completed upon milestone deliveries as agreed.', 800.00, 3, 'we are looking an UI/UX designer to design our website.', 'Jie Yang Loo', 'Google'),
(34, 9, 5, '2025-12-11 23:28:57', NULL, '2025-12-14', '/uploads/agreements/agreement_34.pdf', '2025-12-12 07:28:57', '/uploads/agreements/signature_c5_a34_1765495737.png', '2025-12-12 07:29:49', '2025-12-13 07:28:57', '/uploads/agreements/signature_f9_a34_1765495789.png', '• The freelancer will deliver the gig-based service as described within 2 day(s).\n• The client will pay RM 230.00 which is held in escrow.\n• Payment will be released upon successful delivery and client approval.\n• The service includes 3 revision(s).\n• Both parties agree to maintain professional conduct throughout the engagement.', '', 'I will do modern minimalist business logo design', 'Gig-based service: I will do modern minimalist business logo design', 2, 'At WePerfectionist, our award-winning team crafts modern minimalist logos and brand identity that help businesses stand out, build trust, and scale faster. Every logo design is tailored to your vision from scratch to reflect your brand values and audience.\r\n\r\n\r\n\r\nWhat you\'ll receive:\r\n\r\nOriginal, strategic logo design (no templates, no AI)\r\nDeep brand analysis before starting your project\r\nModern & minimalist logo crafted for startups, entrepreneurs, and global brands\r\nComplete brand style guide', 230.00, 0, 'At WePerfectionist, our award-winning team crafts modern minimalist logos and brand identity that help businesses stand out, build trust, and scale faster. Every logo design is tailored to your vision from scratch to reflect your brand values and audience.\r\n\r\n\r\n\r\nWhat you\'ll receive:\r\n\r\nOriginal, strategic logo design (no templates, no AI)\r\nDeep brand analysis before starting your project\r\nModern & minimalist logo crafted for startups, entrepreneurs, and global brands\r\nComplete brand style guide (premium package) with vector + high-resolution files\r\nUnlimited revisions until youre 100% satisfied\r\nQuick delivery, premium support & dedicated creative team', 'Jonathan Loo', 'Google'),
(35, 12, 8, '2025-12-12 02:41:34', NULL, NULL, '/uploads/agreements/agreement_25_1765507294.pdf', '2025-12-12 10:41:34', '/uploads/agreements/signature_c8_a25_1765507294.png', '2025-12-12 10:43:18', '2025-12-13 10:41:34', NULL, '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.', '', 'Web Design for our company official website', '0', 7, 'To be completed upon milestone deliveries as agreed.', 850.00, 3, 'Web Design for our company official website', 'John Wick', 'Lee Chun YIn'),
(36, 12, 8, '2025-12-12 02:45:14', NULL, NULL, '/uploads/agreements/agreement_26_1765507514.pdf', '2025-12-12 10:45:14', '/uploads/agreements/signature_c8_a26_1765507514.png', '2025-12-12 10:47:20', '2025-12-13 10:45:14', NULL, '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.', '', 'Graphic Designer', '0', 4, 'To be completed upon milestone deliveries as agreed.', 100.00, 3, 'Graphic Design', 'John Wick', 'Lee Chun Yin'),
(37, 12, 8, '2025-12-12 02:48:38', NULL, '2025-12-16', '/uploads/agreements/agreement_37.pdf', '2025-12-12 10:48:38', '/uploads/agreements/signature_c8_a27_1765507718.png', '2025-12-12 10:48:56', '2025-12-13 10:48:38', '/uploads/agreements/signature_f12_a37_1765507736.png', '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.', '', 'Interior Designer', '0', 4, 'To be completed upon milestone deliveries as agreed.', 200.00, 3, 'Interior design', 'John Wick', 'Lee Chun Yin'),
(38, 12, 8, '2025-12-12 02:57:01', NULL, '2025-12-20', '/uploads/agreements/agreement_38.pdf', '2025-12-12 10:57:01', '/uploads/agreements/signature_c8_a28_1765508221.png', '2025-12-12 10:57:26', '2025-12-13 10:57:01', '/uploads/agreements/signature_f12_a38_1765508246.png', '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.', '', 'internship', '0', 8, 'To be completed upon milestone deliveries as agreed.', 77.00, 3, 'fasdfa', 'John Wick', 'Lee Chun Yin'),
(39, 12, 8, '2025-12-12 03:08:27', NULL, '2025-12-17', '/uploads/agreements/agreement_39.pdf', '2025-12-12 11:08:27', '/uploads/agreements/signature_c8_a39_1765508907.png', '2025-12-12 11:08:48', '2025-12-13 11:08:27', '/uploads/agreements/signature_f12_a39_1765508928.png', '• The freelancer will deliver the gig-based service as described within 5 day(s).\n• The client will pay RM 7.00 which is held in escrow.\n• Payment will be released upon successful delivery and client approval.\n• The service includes 4 revision(s).\n• Both parties agree to maintain professional conduct throughout the engagement.', '', 'i will develop a mobile app', 'Gig-based service: i will develop a mobile app', 5, '123', 7.00, 0, '123', 'John Wick', 'Lee Chun Yin');

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `ClientID` int(11) NOT NULL,
  `CompanyName` varchar(255) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `PhoneNo` varchar(50) DEFAULT NULL,
  `ProfilePicture` varchar(500) DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `JoinedDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `isDelete` tinyint(1) DEFAULT 0,
  `FailedLoginAttempts` int(11) NOT NULL DEFAULT 0,
  `LastFailedLoginAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`ClientID`, `CompanyName`, `Description`, `Email`, `Password`, `PhoneNo`, `ProfilePicture`, `Status`, `Address`, `JoinedDate`, `isDelete`, `FailedLoginAttempts`, `LastFailedLoginAt`) VALUES
(5, 'Google', NULL, 'loojy-wm22@student.tarc.edu.my', '$2y$10$o.15RDpumUcNGoRXQjqWmOCoSVkKxGptf5VIXwW2/x1N.4YRS45j2', NULL, NULL, 'active', NULL, '2025-11-30 06:53:20', 0, 0, NULL),
(6, 'Allianz', NULL, 'leechunyin-wm22@student.tarc.edu.my', '$2y$10$XhMG4RRUJa.ucoxaaA8xz.Jx8z6mS567esPYUfLZ90fDb1g1Ih5kS', NULL, NULL, 'active', NULL, '2025-12-03 06:57:39', 0, 0, NULL),
(7, 'asdf', NULL, 'asdfasdf@gmail.com', '$2y$10$vLRfuIdFLxHySTLl6rn3Z.8ebSf4rbHMogEeT96pkeGoN9btbXRU6', NULL, NULL, 'active', NULL, '2025-12-12 01:26:46', 0, 3, NULL),
(8, 'Tarumt', NULL, 'tarumt@gmail.com', '$2y$10$ZxcSfQesJyV.fQaCm1oam.AY0R6ThB6L43i.mhP0VZh78400RxBWy', NULL, NULL, 'active', NULL, '2025-12-12 02:16:15', 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `conversation`
--

CREATE TABLE `conversation` (
  `ConversationID` int(11) NOT NULL,
  `User1ID` int(11) NOT NULL,
  `User1Type` enum('freelancer','client') NOT NULL,
  `User2ID` int(11) NOT NULL,
  `User2Type` enum('freelancer','client') NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastMessageAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `Status` varchar(50) DEFAULT 'active',
  `DeletedBy` enum('user1','user2') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversation`
--

INSERT INTO `conversation` (`ConversationID`, `User1ID`, `User1Type`, `User2ID`, `User2Type`, `CreatedAt`, `LastMessageAt`, `Status`, `DeletedBy`) VALUES
(17, 5, 'client', 8, 'freelancer', '2025-11-30 07:54:46', '2025-12-12 01:25:21', 'active', NULL),
(18, 6, 'client', 8, 'freelancer', '2025-12-07 07:43:26', NULL, 'active', NULL),
(19, 5, 'client', 9, 'freelancer', '2025-12-11 12:14:50', NULL, 'active', NULL),
(20, 8, 'client', 12, 'freelancer', '2025-12-12 02:41:34', '2025-12-12 03:04:29', 'active', NULL),
(21, 8, 'client', 8, 'freelancer', '2025-12-12 03:01:37', '2025-12-12 03:01:49', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dispute`
--

CREATE TABLE `dispute` (
  `DisputeID` int(11) NOT NULL,
  `AgreementID` int(11) NOT NULL,
  `InitiatorID` int(11) NOT NULL COMMENT 'FreelancerID or ClientID who filed dispute',
  `InitiatorType` enum('freelancer','client') NOT NULL COMMENT 'Type of user who filed dispute',
  `ReasonText` text NOT NULL COMMENT 'Reason for dispute',
  `EvidenceFile` varchar(255) DEFAULT NULL COMMENT 'Path to evidence file/document',
  `Status` enum('open','under_review','resolved','rejected') NOT NULL DEFAULT 'open' COMMENT 'Current dispute status',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When dispute was filed',
  `AdminNotesText` text DEFAULT NULL COMMENT 'Admin review notes',
  `ResolutionAction` enum('refund_client','release_to_freelancer','split_payment','rejected') DEFAULT NULL COMMENT 'How dispute was resolved',
  `ResolvedAt` timestamp NULL DEFAULT NULL COMMENT 'When dispute was resolved',
  `ResolvedByAdminID` int(11) DEFAULT NULL COMMENT 'Admin who resolved dispute'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks disputes filed on agreements';

--
-- Dumping data for table `dispute`
--

INSERT INTO `dispute` (`DisputeID`, `AgreementID`, `InitiatorID`, `InitiatorType`, `ReasonText`, `EvidenceFile`, `Status`, `CreatedAt`, `AdminNotesText`, `ResolutionAction`, `ResolvedAt`, `ResolvedByAdminID`) VALUES
(1, 37, 8, 'client', 'Non-delivery of work\n\nsdfasfd', '/uploads/disputes/dispute_37_1765507913.pdf', 'resolved', '2025-12-12 02:51:53', 'I think the client side is correct', 'refund_client', '2025-12-12 02:54:36', 1),
(2, 38, 12, 'freelancer', 'Project scope change\n\nRequirement Change', '/uploads/disputes/dispute_38_1765508267.pdf', 'open', '2025-12-12 02:57:47', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `escrow`
--

CREATE TABLE `escrow` (
  `EscrowID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `PayerID` int(11) NOT NULL,
  `PayeeID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `Status` enum('hold','released','refunded') NOT NULL DEFAULT 'hold',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ReleasedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `escrow`
--

INSERT INTO `escrow` (`EscrowID`, `OrderID`, `PayerID`, `PayeeID`, `Amount`, `Status`, `CreatedAt`, `ReleasedAt`) VALUES
(12, 27, 5, 8, 1500.00, 'released', '2025-11-30 08:59:06', '2025-11-30 08:59:06'),
(13, 28, 5, 8, 1700.00, 'released', '2025-11-30 15:02:26', '2025-11-30 15:02:26'),
(14, 29, 6, 8, 800.00, 'released', '2025-12-07 07:53:27', '2025-12-07 07:53:27'),
(15, 30, 5, 8, 1000.00, 'released', '2025-12-07 08:07:26', '2025-12-07 08:07:26'),
(16, 31, 5, 8, 1200.00, 'released', '2025-12-08 13:43:46', '2025-12-08 13:43:46'),
(17, 32, 5, 9, 500.00, 'hold', '2025-12-11 12:14:49', NULL),
(18, 33, 5, 8, 800.00, 'refunded', '2025-12-12 06:47:18', NULL),
(19, 34, 5, 9, 230.00, 'released', '2025-12-11 23:44:51', '2025-12-11 23:44:51'),
(20, 35, 8, 12, 850.00, 'refunded', '2025-12-12 02:43:18', NULL),
(21, 36, 8, 12, 100.00, 'refunded', '2025-12-12 02:47:20', NULL),
(22, 37, 8, 12, 200.00, 'refunded', '2025-12-12 02:54:36', '2025-12-12 02:54:36'),
(23, 38, 8, 12, 77.00, 'hold', '2025-12-12 03:00:28', NULL),
(24, 39, 8, 12, 7.00, 'released', '2025-12-12 03:13:17', '2025-12-12 03:13:17');

-- --------------------------------------------------------

--
-- Table structure for table `freelancer`
--

CREATE TABLE `freelancer` (
  `FreelancerID` int(11) NOT NULL,
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
  `Rating` decimal(3,2) DEFAULT NULL,
  `TotalEarned` decimal(10,2) DEFAULT NULL,
  `JoinedDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `isDelete` tinyint(1) DEFAULT 0,
  `FailedLoginAttempts` int(11) NOT NULL DEFAULT 0,
  `LastFailedLoginAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `freelancer`
--

INSERT INTO `freelancer` (`FreelancerID`, `FirstName`, `LastName`, `Email`, `Password`, `PhoneNo`, `ProfilePicture`, `Status`, `Address`, `Experience`, `Education`, `SocialMediaURL`, `Bio`, `Rating`, `TotalEarned`, `JoinedDate`, `isDelete`, `FailedLoginAttempts`, `LastFailedLoginAt`) VALUES
(8, 'Jie Yang', 'Loo', 'loojieyang030310@gmail.com', '$2y$10$AgkwdAIpM.QSksisA4dVr.6ozculFKnSLdPC7iOHBVVTHjsVqiUBW', '01158618591', 'uploads/profile_pictures/freelancer_8_1764485536.JPG', 'active', 'B-20-9, PV13, Jalan Danau Saujana 1, Taman Danau Kota, Kuala Lumpur', 'iMocha Sdn Bhd', 'Bachelor Degree', 'https://linkedin/in/jieyangloo', 'i am a TARUMT student majoring in bachelor of information technology.', 5.00, NULL, '2025-11-30 06:52:02', 0, 0, NULL),
(9, 'Jonathan', 'Loo', 'leechunyin234563@gmail.com', '$2y$10$/RRZTLh82WXlDLlj6QLFdeVHt1YV3ojrhqdWSgnVx7fcXtAZ7pkPq', NULL, NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-01 13:24:02', 0, 0, NULL),
(10, 'Lee', 'Chun Yin', 'leechunyin-wm22@student.tarc.edu.my', '$2y$10$mIMO0aHKpBxyz3HT75HB7OVrTP2FraUxigJZ1R4Rescfuh1f30aTm', NULL, NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-03 06:56:31', 0, 1, NULL),
(11, 'Jimmy Kah Lok', 'Chan', 'jimmychankahlok66@gmail.com', '$2y$10$A.7EEZxobBhH/4tAUHFGxO5IA7g9JhieRBLbKSB5/YC5HnPsqjO2a', NULL, NULL, 'blocked', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 02:17:37', 0, 3, NULL),
(12, 'John', 'Wick', 'Jimmy@gmail.com', '$2y$10$8W6BnqIH2K6ubqgcbrhrXeT1EJvb/FweyCjndSkf1daNb2ZFd2thG', NULL, NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 02:35:40', 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `freelancerskill`
--

CREATE TABLE `freelancerskill` (
  `FreelancerID` int(11) NOT NULL,
  `SkillID` int(11) NOT NULL,
  `ProficiencyLevel` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gig`
--

CREATE TABLE `gig` (
  `GigID` int(11) NOT NULL,
  `FreelancerID` int(11) NOT NULL,
  `Title` varchar(150) NOT NULL,
  `Category` varchar(100) NOT NULL,
  `Subcategory` varchar(100) NOT NULL,
  `SearchTags` varchar(100) NOT NULL,
  `Description` text NOT NULL,
  `Price` int(11) NOT NULL,
  `DeliveryTime` int(11) NOT NULL,
  `RushDelivery` int(11) DEFAULT NULL,
  `RushDeliveryPrice` int(11) NOT NULL,
  `AdditionalRevision` int(11) DEFAULT NULL,
  `RevisionCount` int(11) NOT NULL,
  `Image1Path` varchar(255) NOT NULL,
  `Image2Path` varchar(255) DEFAULT NULL,
  `Image3Path` varchar(255) DEFAULT NULL,
  `VideoPath` varchar(255) DEFAULT NULL,
  `Status` enum('active','paused','deleted') NOT NULL,
  `CreatedAt` datetime NOT NULL,
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gig`
--

INSERT INTO `gig` (`GigID`, `FreelancerID`, `Title`, `Category`, `Subcategory`, `SearchTags`, `Description`, `Price`, `DeliveryTime`, `RushDelivery`, `RushDeliveryPrice`, `AdditionalRevision`, `RevisionCount`, `Image1Path`, `Image2Path`, `Image3Path`, `VideoPath`, `Status`, `CreatedAt`, `UpdatedAt`) VALUES
(9, 8, 'I will do wix website design, redesign business wix website, website development', 'programming-tech', 'website-development', 'website,business website', '7 figure Dropship Expert at Your Service:\r\n\r\n\r\n\r\nI\'m not only here to launch your store effectively and teach you how to grow a brand!\r\n\r\n\r\n\r\nDid you know 95% of Shopify Dropshipping stores FAIL due to easily avoidable mistakes?\r\n\r\n\r\n\r\nWhy Choose Me?\r\n\r\nDropshipping has allowed me to work for myself for over 5 years\r\n\r\nYou probably bought something from my stores\r\n\r\nOver 5 years experience \r\n\r\nBeginner-friendly model\r\n\r\nEasy to understand - work at a pace that suits you\r\n\r\nA genuine person who wants to help you succeed!\r\n\r\n\r\n\r\nWhat You Will Receive:\r\n\r\nA fully operational automated dropshipping website. \r\n\r\nProfessionally designed store made to convert with a premium theme\r\n\r\nHelp You select a profitable trendy product with title optimization\r\n\r\n Installing apps that increase conversions\r\n\r\nMy Personal Support before and after the project. Answer all your queries \r\n\r\nA store built to CONVERT!\r\n\r\n Custom Coded Website (Stand out from the crowd!)\r\n\r\n Compelling product descriptions with targeted keywords\r\n\r\n  Share my private agent with you', 1000, 7, 5, 700, 300, 2, '/images/gig_media/gig-img-692c3823ba8a75.28278106.png', '/images/gig_media/gig-img-692c3823bd9f65.05064055.jpg', '/images/gig_media/gig-img-692c3823bde792.26643164.png', NULL, 'active', '2025-11-30 20:27:19', '2025-11-30 20:35:57'),
(10, 9, 'I will do modern minimalist business logo design', 'graphic-design', 'logo-design', 'logo design,design', 'At WePerfectionist, our award-winning team crafts modern minimalist logos and brand identity that help businesses stand out, build trust, and scale faster. Every logo design is tailored to your vision from scratch to reflect your brand values and audience.\r\n\r\n\r\n\r\nWhat you\'ll receive:\r\n\r\nOriginal, strategic logo design (no templates, no AI)\r\nDeep brand analysis before starting your project\r\nModern & minimalist logo crafted for startups, entrepreneurs, and global brands\r\nComplete brand style guide (premium package) with vector + high-resolution files\r\nUnlimited revisions until youre 100% satisfied\r\nQuick delivery, premium support & dedicated creative team', 140, 3, 2, 50, 40, 2, '/images/gig_media/gig_10_img1_1765470949.jpg', NULL, NULL, NULL, 'active', '2025-12-01 21:31:17', '2025-12-12 07:28:17'),
(11, 8, 'I will create 3 modern minimalist business logo design', 'graphic-design', 'logo-design', 'logo', 'A warm welcome to my gig. This gig assures you of the logo design with minimalism and creativity. \r\n\r\n\r\n\r\nFlat and minimal logo design concepts are one of our strengths. To be a timeless logo, it doesn\'t need to have complex structures or patterns. It just needs to be simple, memorable, and easy to understand, which actually gives a distinctive essence to your business. By walking on this path, we have completed more than 100,000 projects successfully, with more than 45K feedbacks and counting. \r\n\r\n\r\n\r\nPortfolio: https://www.fiverr.com/users/logoflow/portfolio\r\n\r\n\r\n\r\nWhy me?\r\n\r\nUnderstand the client\'s vision properly\r\n\r\nHighly satisfied client database\r\n\r\nTotally custom-made design\r\n\r\nSimple but impactful communication\r\n\r\nFresh ideas\r\n\r\n\r\n\r\nFile Formats: \r\n\r\nAI | EPS | JPG | PNG | PDF (PSD & SVG on request)\r\n\r\n\r\n\r\nWorkflow:\r\n\r\nUnderstanding requirements > Research > Brainstorming ideas > Execution > Quality Check > Delivery\r\n\r\n\r\n\r\nOur expertise:\r\n\r\nMinimalist | Flat | Luxury | Typography | Text-based | | Trendy | Feminine | Professional | Modern | Minimal | Business logo | Unique | Monogram | Badge\r\n\r\n\r\n\r\nNote: For complex and mascot designs, please message me first in the Fiverr inbox!\r\n\r\n\r\n\r\nEvery Sunday is a weekend!', 80, 4, 2, 60, 60, 3, '/images/gig_media/gig-img-69366da6b3c272.11003200.png', NULL, NULL, NULL, 'deleted', '2025-12-08 14:21:52', NULL),
(12, 8, 'I will create 3 modern minimalist business logo design', 'graphic-design', 'logo-design', 'logo', 'A warm welcome to my gig. This gig assures you of the logo design with minimalism and creativity. \r\n\r\n\r\n\r\nFlat and minimal logo design concepts are one of our strengths. To be a timeless logo, it doesn\'t need to have complex structures or patterns. It just needs to be simple, memorable, and easy to understand, which actually gives a distinctive essence to your business. By walking on this path, we have completed more than 100,000 projects successfully, with more than 45K feedbacks and counting. \r\n\r\n\r\n\r\nPortfolio: https://www.fiverr.com/users/logoflow/portfolio\r\n\r\n\r\n\r\nWhy me?\r\n\r\nUnderstand the client\'s vision properly\r\n\r\nHighly satisfied client database\r\n\r\nTotally custom-made design\r\n\r\nSimple but impactful communication\r\n\r\nFresh ideas\r\n\r\n\r\n\r\nFile Formats: \r\n\r\nAI | EPS | JPG | PNG | PDF (PSD & SVG on request)\r\n\r\n\r\n\r\nWorkflow:\r\n\r\nUnderstanding requirements > Research > Brainstorming ideas > Execution > Quality Check > Delivery\r\n\r\n\r\n\r\nOur expertise:\r\n\r\nMinimalist | Flat | Luxury | Typography | Text-based | | Trendy | Feminine | Professional | Modern | Minimal | Business logo | Unique | Monogram | Badge\r\n\r\n\r\n\r\nNote: For complex and mascot designs, please message me first in the Fiverr inbox!\r\n\r\n\r\n\r\nEvery Sunday is a weekend!', 80, 4, 2, 60, 60, 3, '/images/gig_media/gig_12_img1_1765468924.png', NULL, NULL, NULL, 'active', '2025-12-08 14:24:33', '2025-12-12 00:22:52'),
(13, 12, 'i will develop a mobile app', 'graphic-design', 'ux-design', '1', '123', 5, 1, 5, 1, 1, 3, '/images/gig_media/gig-img-693b85ebde81d4.20790688.png', NULL, NULL, NULL, 'active', '2025-12-12 11:03:19', '2025-12-12 11:05:37');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `InvoiceID` int(11) NOT NULL,
  `InvoiceNumber` varchar(50) NOT NULL,
  `AgreementID` int(11) NOT NULL,
  `ClientID` int(11) NOT NULL,
  `FreelancerID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `InvoiceDate` datetime NOT NULL,
  `InvoiceFilePath` varchar(500) DEFAULT NULL,
  `Status` enum('generated','sent','paid') NOT NULL DEFAULT 'paid',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`InvoiceID`, `InvoiceNumber`, `AgreementID`, `ClientID`, `FreelancerID`, `Amount`, `InvoiceDate`, `InvoiceFilePath`, `Status`, `CreatedAt`) VALUES
(1, 'INV-20251207-000030', 30, 5, 8, 1000.00, '2025-12-07 16:07:26', 'uploads/invoices/invoice_30_1765094846.pdf', 'paid', '2025-12-07 08:07:26'),
(2, 'INV-20251208-000031', 31, 5, 8, 1200.00, '2025-12-08 21:43:46', 'uploads/invoices/invoice_31_1765201426.pdf', 'paid', '2025-12-08 13:43:46'),
(3, 'INV-20251212-000034', 34, 5, 9, 230.00, '2025-12-12 07:44:51', 'uploads/invoices/invoice_34_1765496691.pdf', 'paid', '2025-12-11 23:44:51'),
(4, 'INV-20251212-000039', 39, 8, 12, 7.00, '2025-12-12 11:13:17', 'uploads/invoices/invoice_39_1765509197.pdf', 'paid', '2025-12-12 03:13:17');

-- --------------------------------------------------------

--
-- Table structure for table `job`
--

CREATE TABLE `job` (
  `JobID` int(11) NOT NULL,
  `ClientID` int(11) DEFAULT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Budget` decimal(10,2) DEFAULT NULL,
  `DeliveryTime` int(11) NOT NULL,
  `Deadline` date DEFAULT NULL,
  `Status` enum('available','deleted','complete','processing') NOT NULL DEFAULT 'available',
  `PostDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job`
--

INSERT INTO `job` (`JobID`, `ClientID`, `Title`, `Description`, `Budget`, `DeliveryTime`, `Deadline`, `Status`, `PostDate`) VALUES
(13, 5, 'Mobile Application Project', 'Mobile Application Project', 1500.00, 7, '2025-12-07', 'complete', '2025-11-30'),
(14, 5, 'We are looking for Java developer to develop a website using Java.', 'Java Developer', 1000.00, 7, '2025-12-08', 'complete', '2025-12-01'),
(15, 6, 'We are looking for graphic designer', 'we are looking for graphic designer', 800.00, 7, '2025-12-09', 'complete', '2025-12-03'),
(16, 5, 'Design an E-commerce Website', 'design an e-commerce website', 1200.00, 4, '2025-12-10', 'complete', '2025-12-08'),
(17, 5, 'We are looking for desinger to design a poster for us', 'we are looking for desinger to design a poster for us.', 500.00, 5, '2025-12-09', 'complete', '2025-12-08'),
(18, 5, 'web design project', 'we are looking an UI/UX designer to design our website.', 800.00, 7, '2025-12-14', 'processing', '2025-12-11'),
(19, 8, 'Web Design for our company official website', 'Web Design for our company official website', 850.00, 7, '2025-12-14', 'processing', '2025-12-12'),
(20, 8, 'Graphic Designer', 'Graphic Design', 100.00, 4, '2025-12-24', 'processing', '2025-12-12'),
(21, 8, 'Interior Designer', 'Interior design', 200.00, 4, '2025-12-19', 'processing', '2025-12-12'),
(22, 8, 'internship', 'fasdfa', 77.00, 8, '2025-12-18', 'complete', '2025-12-12'),
(23, 5, 'business websites', 'business website', 300.00, 5, '2025-12-14', 'available', '2025-12-12'),
(24, 5, 'ddd', 'ddd', 300.00, 3, '2025-12-14', 'available', '2025-12-12');

-- --------------------------------------------------------

--
-- Table structure for table `job_application`
--

CREATE TABLE `job_application` (
  `ApplicationID` int(11) NOT NULL,
  `JobID` int(11) NOT NULL,
  `FreelancerID` int(11) NOT NULL,
  `CoverLetter` text DEFAULT NULL,
  `ProposedBudget` decimal(10,2) DEFAULT NULL,
  `EstimatedDuration` varchar(100) DEFAULT NULL,
  `Status` enum('pending','accepted','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  `AppliedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_application`
--

INSERT INTO `job_application` (`ApplicationID`, `JobID`, `FreelancerID`, `CoverLetter`, `ProposedBudget`, `EstimatedDuration`, `Status`, `AppliedAt`, `UpdatedAt`) VALUES
(15, 13, 8, NULL, NULL, NULL, 'accepted', '2025-11-30 07:38:36', '2025-11-30 07:54:45'),
(16, 15, 8, NULL, NULL, NULL, 'accepted', '2025-12-07 07:38:22', '2025-12-07 07:43:26'),
(17, 14, 8, NULL, NULL, NULL, 'accepted', '2025-12-07 08:05:49', '2025-12-07 08:06:32'),
(18, 16, 8, NULL, NULL, NULL, 'accepted', '2025-12-08 07:49:10', '2025-12-08 09:29:23'),
(19, 17, 8, NULL, NULL, NULL, 'rejected', '2025-12-11 12:06:00', '2025-12-11 14:21:52'),
(20, 17, 9, NULL, NULL, NULL, 'accepted', '2025-12-11 12:14:23', '2025-12-11 12:14:49'),
(21, 18, 9, NULL, NULL, NULL, 'rejected', '2025-12-11 16:41:49', '2025-12-11 23:23:35'),
(22, 18, 8, NULL, NULL, NULL, 'accepted', '2025-12-11 16:44:46', '2025-12-11 16:46:48'),
(23, 19, 8, NULL, NULL, NULL, 'rejected', '2025-12-12 02:28:13', '2025-12-12 02:41:34'),
(24, 19, 9, NULL, NULL, NULL, 'rejected', '2025-12-12 02:31:30', '2025-12-12 02:33:42'),
(25, 19, 12, NULL, NULL, NULL, 'accepted', '2025-12-12 02:37:23', '2025-12-12 02:41:34'),
(26, 20, 12, NULL, NULL, NULL, 'accepted', '2025-12-12 02:44:45', '2025-12-12 02:45:14'),
(27, 21, 12, NULL, NULL, NULL, 'accepted', '2025-12-12 02:48:11', '2025-12-12 02:48:38'),
(28, 22, 12, NULL, NULL, NULL, 'accepted', '2025-12-12 02:56:10', '2025-12-12 02:57:01'),
(29, 23, 8, NULL, NULL, NULL, 'rejected', '2025-12-12 06:52:56', '2025-12-12 07:02:44'),
(30, 23, 9, NULL, NULL, NULL, 'rejected', '2025-12-12 06:53:17', '2025-12-12 06:59:11'),
(31, 24, 8, NULL, NULL, NULL, 'rejected', '2025-12-12 07:11:31', '2025-12-12 07:11:41');

-- --------------------------------------------------------

--
-- Table structure for table `job_application_answer`
--

CREATE TABLE `job_application_answer` (
  `AnswerID` int(11) NOT NULL,
  `ApplicationID` int(11) DEFAULT NULL,
  `QuestionID` int(11) NOT NULL,
  `FreelancerID` int(11) NOT NULL,
  `JobID` int(11) NOT NULL,
  `SelectedOptionID` int(11) DEFAULT NULL,
  `AnswerText` text DEFAULT NULL,
  `AnsweredAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_application_answer`
--

INSERT INTO `job_application_answer` (`AnswerID`, `ApplicationID`, `QuestionID`, `FreelancerID`, `JobID`, `SelectedOptionID`, `AnswerText`, `AnsweredAt`) VALUES
(7, 15, 6, 8, 13, 27, 'two year', '2025-11-30 07:38:36'),
(8, 17, 7, 8, 14, 32, 'one year', '2025-12-07 08:05:49'),
(9, 18, 8, 8, 16, 38, '1 year', '2025-12-08 07:49:10'),
(10, 19, 9, 8, 17, 44, '1 year', '2025-12-11 12:06:00'),
(11, 20, 9, 9, 17, 45, '2 year', '2025-12-11 12:14:23'),
(12, 21, 10, 9, 18, 52, 'three year', '2025-12-11 16:41:49'),
(13, 22, 10, 8, 18, 50, 'one year', '2025-12-11 16:44:46'),
(14, 23, 12, 8, 19, 60, '1 year', '2025-12-12 02:28:13'),
(15, 24, 12, 9, 19, 61, '2 yera', '2025-12-12 02:31:30'),
(16, 25, 12, 12, 19, 62, '3 year', '2025-12-12 02:37:23');

-- --------------------------------------------------------

--
-- Table structure for table `job_question`
--

CREATE TABLE `job_question` (
  `QuestionID` int(11) NOT NULL,
  `JobID` int(11) NOT NULL,
  `QuestionText` text NOT NULL,
  `QuestionType` enum('multiple_choice','yes_no') NOT NULL DEFAULT 'multiple_choice',
  `IsRequired` tinyint(1) DEFAULT 1,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_question`
--

INSERT INTO `job_question` (`QuestionID`, `JobID`, `QuestionText`, `QuestionType`, `IsRequired`, `CreatedAt`) VALUES
(6, 13, 'how many years of experience in mobile application development?', 'multiple_choice', 1, '2025-11-30 06:59:44'),
(7, 14, 'How many years of experience as Java developer?', 'multiple_choice', 1, '2025-12-01 14:48:13'),
(8, 16, 'how many years of experience in designning e-commerce webstie?', 'multiple_choice', 0, '2025-12-08 06:43:29'),
(9, 17, 'how many years of experience in designning poster.', 'multiple_choice', 1, '2025-12-08 14:49:37'),
(10, 18, 'how many years of experience to be an UI/UX designer?', 'multiple_choice', 1, '2025-12-11 16:41:31'),
(12, 19, 'How many years of experience in UI/UX design?', 'multiple_choice', 1, '2025-12-12 02:26:40');

-- --------------------------------------------------------

--
-- Table structure for table `job_question_option`
--

CREATE TABLE `job_question_option` (
  `OptionID` int(11) NOT NULL,
  `QuestionID` int(11) NOT NULL,
  `OptionText` varchar(500) NOT NULL,
  `IsCorrectAnswer` tinyint(1) DEFAULT 0,
  `DisplayOrder` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_question_option`
--

INSERT INTO `job_question_option` (`OptionID`, `QuestionID`, `OptionText`, `IsCorrectAnswer`, `DisplayOrder`) VALUES
(25, 6, 'less than one year', 0, 0),
(26, 6, 'one year', 0, 1),
(27, 6, 'two year', 0, 2),
(28, 6, 'three year', 0, 3),
(29, 6, 'four year', 0, 4),
(30, 6, 'four year and above', 0, 5),
(31, 7, 'less than one year', 0, 0),
(32, 7, 'one year', 0, 1),
(33, 7, 'two year', 0, 2),
(34, 7, 'three year', 0, 3),
(35, 7, 'four year', 0, 4),
(36, 7, 'four year and above', 0, 5),
(37, 8, 'less than 1 year', 0, 0),
(38, 8, '1 year', 0, 1),
(39, 8, '2 year', 0, 2),
(40, 8, '3 year', 0, 3),
(41, 8, '4 year', 0, 4),
(42, 8, '4 year and above', 0, 5),
(43, 9, 'less than 1 year', 0, 0),
(44, 9, '1 year', 0, 1),
(45, 9, '2 year', 0, 2),
(46, 9, '3 year', 0, 3),
(47, 9, '4 year', 0, 4),
(48, 9, 'more than 4 year', 0, 5),
(49, 10, 'less than one year', 0, 0),
(50, 10, 'one year', 0, 1),
(51, 10, 'two year', 0, 2),
(52, 10, 'three year', 0, 3),
(53, 10, 'four year', 0, 4),
(54, 10, 'four year and above', 0, 5),
(59, 12, 'less than 1 year', 0, 1),
(60, 12, '1 year', 0, 2),
(61, 12, '2 yera', 0, 3),
(62, 12, '3 year', 0, 4);

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `MessageID` int(11) NOT NULL,
  `ConversationID` int(11) DEFAULT NULL,
  `ReceiverID` varchar(20) NOT NULL,
  `SenderID` varchar(20) NOT NULL,
  `Content` text DEFAULT NULL,
  `AttachmentPath` varchar(500) DEFAULT NULL,
  `AttachmentType` varchar(50) DEFAULT NULL,
  `Timestamp` datetime DEFAULT current_timestamp(),
  `Status` varchar(50) DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`MessageID`, `ConversationID`, `ReceiverID`, `SenderID`, `Content`, `AttachmentPath`, `AttachmentType`, `Timestamp`, `Status`) VALUES
(115, 17, 'f8', 'c5', 'I have signed the agreement for the project \"Mobile Application Project\". Please review and sign to proceed. The agreement is attached below.\n\n', '/uploads/agreements/agreement_15_1764489285.pdf', 'application/pdf', '2025-11-30 15:54:46', 'to_accept'),
(116, 17, 'c5', 'f8', 'Agreement signed successfully! The agreement \"Mobile Application Project\" has been signed and is now active.', '/uploads/agreements/agreement_27.pdf', 'application/pdf', '2025-11-30 15:55:30', 'unread'),
(117, 17, 'f8', 'c5', 'New gig order: \"I will do wix website design, redesign business wix website, website development\" for RM 1,700.00. Please review and sign the agreement to confirm.', '/uploads/agreements/gig_agreement_28_1764506470.pdf', 'application/pdf', '2025-11-30 20:41:10', 'unread'),
(118, 17, 'c5', 'f8', 'Agreement signed successfully! The agreement \"I will do wix website design, redesign business wix website, website development\" has been signed and is now active.', '/uploads/agreements/agreement_28.pdf', 'application/pdf', '2025-11-30 20:49:11', 'unread'),
(119, 18, 'f8', 'c6', 'I have signed the agreement for the project \"We are looking for graphic designer\". Please review and sign to proceed. The agreement is attached below.\n\n', '/uploads/agreements/agreement_16_1765093405.pdf', 'application/pdf', '2025-12-07 15:43:26', 'to_accept'),
(120, 18, 'c6', 'f8', 'Agreement signed successfully! The agreement \"We are looking for graphic designer\" has been signed and is now active.', '/uploads/agreements/agreement_29.pdf', 'application/pdf', '2025-12-07 15:44:42', 'unread'),
(121, 17, 'f8', 'c5', 'I have signed the agreement for the project \"We are looking for Java developer to develop a website using Java.\". Please review and sign to proceed. The agreement is attached below.\n\n', '/uploads/agreements/agreement_17_1765094792.pdf', 'application/pdf', '2025-12-07 16:06:32', 'to_accept'),
(122, 17, 'c5', 'f8', 'Agreement signed successfully! The agreement \"We are looking for Java developer to develop a website using Java.\" has been signed and is now active.', '/uploads/agreements/agreement_30.pdf', 'application/pdf', '2025-12-07 16:06:54', 'unread'),
(123, 17, 'f8', 'c5', 'I have signed the agreement for the project \"Design an E-commerce Website\". Please review and sign to proceed. The agreement is attached below.\n\n', '/uploads/agreements/agreement_18_1765186163.pdf', 'application/pdf', '2025-12-08 17:29:24', 'to_accept'),
(124, 17, 'c5', 'f8', 'Agreement signed successfully! The agreement \"Design an E-commerce Website\" has been signed and is now active.', '/uploads/agreements/agreement_31.pdf', 'application/pdf', '2025-12-08 17:30:32', 'unread'),
(125, 19, 'f9', 'c5', 'I have signed the agreement for the project \"We are looking for desinger to design a poster for us\". Please review and sign to proceed. The agreement is attached below.\n\n', '/uploads/agreements/agreement_20_1765455289.pdf', 'application/pdf', '2025-12-11 20:14:50', 'to_accept'),
(126, 19, 'c5', 'f9', 'Agreement signed successfully! The agreement \"We are looking for desinger to design a poster for us\" has been signed and is now active.', '/uploads/agreements/agreement_32.pdf', 'application/pdf', '2025-12-12 00:25:00', 'unread'),
(127, 17, 'f8', 'c5', 'I have signed the agreement for the project \"web design project\". Please review and sign to proceed. The agreement is attached below.\n\n', '/uploads/agreements/agreement_22_1765471608.pdf', 'application/pdf', '2025-12-12 00:46:48', 'to_accept'),
(128, 19, 'f9', 'c5', 'New gig order: \"I will do modern minimalist business logo design\" for RM 230.00. Please review and sign the agreement to confirm.', '/uploads/agreements/gig_agreement_34_1765495737.pdf', 'application/pdf', '2025-12-12 07:28:57', 'unread'),
(129, 19, 'c5', 'f9', 'Agreement signed successfully! The agreement \"I will do modern minimalist business logo design\" has been signed and is now active.', '/uploads/agreements/agreement_34.pdf', 'application/pdf', '2025-12-12 07:29:49', 'unread'),
(130, 17, 'c5', 'f8', NULL, '/uploads/messages/1765502721_f07301dd.pdf', 'application/pdf', '2025-12-12 09:25:21', 'unread'),
(131, 20, 'f12', 'c8', 'I have signed the agreement for the project \"Web Design for our company official website\". Please review and sign to proceed. The agreement is attached below.\n\n', '/uploads/agreements/agreement_25_1765507294.pdf', 'application/pdf', '2025-12-12 10:41:34', 'to_accept'),
(132, 20, 'f12', 'c8', 'I have signed the agreement for the project \"Graphic Designer\". Please review and sign to proceed. The agreement is attached below.\n\n', '/uploads/agreements/agreement_26_1765507514.pdf', 'application/pdf', '2025-12-12 10:45:14', 'to_accept'),
(133, 20, 'f12', 'c8', 'I have signed the agreement for the project \"Interior Designer\". Please review and sign to proceed. The agreement is attached below.\n\n', '/uploads/agreements/agreement_27_1765507718.pdf', 'application/pdf', '2025-12-12 10:48:38', 'to_accept'),
(134, 20, 'c8', 'f12', 'Agreement signed successfully! The agreement \"Interior Designer\" has been signed and is now active.', '/uploads/agreements/agreement_37.pdf', 'application/pdf', '2025-12-12 10:48:56', 'unread'),
(135, 20, 'f12', 'c8', 'I have signed the agreement for the project \"internship\". Please review and sign to proceed. The agreement is attached below.\n\n', '/uploads/agreements/agreement_28_1765508221.pdf', 'application/pdf', '2025-12-12 10:57:01', 'to_accept'),
(136, 20, 'c8', 'f12', 'Agreement signed successfully! The agreement \"internship\" has been signed and is now active.', '/uploads/agreements/agreement_38.pdf', 'application/pdf', '2025-12-12 10:57:26', 'unread'),
(137, 21, 'f8', 'c8', '{\"type\":\"gig_quote\",\"gig_title\":\"I will create 3 modern minimalist business logo design\",\"gig_price\":\"RM80.00\",\"delivery_time\":\"4 days\",\"description\":\"A warm welcome to my gig. This gig assures you of the logo design with minimalism and creativity. \\n\\n\\n\\nFlat and minimal logo design concepts are...\"}', NULL, NULL, '2025-12-12 11:01:49', 'unread'),
(138, 20, 'f12', 'c8', '{\"type\":\"gig_quote\",\"gig_title\":\"i will develop a mobile app\",\"gig_price\":\"RM5.00\",\"delivery_time\":\"1 days\",\"description\":\"123\"}', NULL, NULL, '2025-12-12 11:03:29', 'unread'),
(139, 20, 'c8', 'f12', NULL, '/uploads/messages/1765508644_058c6e35.png', 'image/png', '2025-12-12 11:04:04', 'unread'),
(140, 20, 'f12', 'c8', NULL, '/uploads/messages/1765508669_96b5b92a.pdf', 'application/pdf', '2025-12-12 11:04:29', 'unread'),
(141, 20, 'f12', 'c8', 'New gig order: \"i will develop a mobile app\" for RM 7.00. Please review and sign the agreement to confirm.', '/uploads/agreements/gig_agreement_39_1765508907.pdf', 'application/pdf', '2025-12-12 11:08:27', 'unread'),
(142, 20, 'c8', 'f12', 'Agreement signed successfully! The agreement \"i will develop a mobile app\" has been signed and is now active.', '/uploads/agreements/agreement_39.pdf', 'application/pdf', '2025-12-12 11:08:48', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `message_notification`
--

CREATE TABLE `message_notification` (
  `NotificationID` int(11) NOT NULL,
  `ReceiverID` int(11) NOT NULL,
  `ReceiverType` varchar(20) NOT NULL,
  `SenderID` int(11) NOT NULL,
  `SenderType` varchar(20) NOT NULL,
  `ConversationID` varchar(50) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `IsRead` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `NotificationID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `UserType` enum('client','freelancer','admin') NOT NULL,
  `Message` text NOT NULL,
  `RelatedType` varchar(50) DEFAULT NULL,
  `RelatedID` int(11) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL,
  `IsRead` tinyint(1) DEFAULT 0,
  `ReadAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`NotificationID`, `UserID`, `UserType`, `Message`, `RelatedType`, `RelatedID`, `CreatedAt`, `IsRead`, `ReadAt`) VALUES
(10, 5, 'client', 'Freelancer has submitted work for \'Mobile Application Project\'. Please review the deliverables.', 'work_submission', 10, '2025-11-30 16:45:58', 0, NULL),
(11, 8, 'freelancer', 'Your work submission for \'Mobile Application Project\' needs revisions. Please check the client\'s feedback and resubmit.', 'work_revision', 10, '2025-11-30 16:50:16', 0, NULL),
(12, 5, 'client', 'Freelancer has submitted work for \'Mobile Application Project\'. Please review the deliverables.', 'work_submission', 11, '2025-11-30 16:53:32', 0, NULL),
(13, 8, 'freelancer', 'Your work for \'Mobile Application Project\' has been approved! Payment of RM 1,500.00 has been released to your wallet.', 'work_approval', 11, '2025-11-30 16:59:06', 0, NULL),
(14, 8, 'freelancer', 'New gig order from Loo Jie Yang for \'I will do wix website design, redesign business wix website, website development\'. Payment of RM 1,700.00 is held in escrow. Please review and accept the agreement.', 'gig_order', 28, '2025-11-30 20:41:10', 0, NULL),
(15, 5, 'client', 'Freelancer has submitted work for \'I will do wix website design, redesign business wix website, website development\'. Please review the deliverables.', 'work_submission', 12, '2025-11-30 20:52:06', 0, NULL),
(16, 8, 'freelancer', 'Your work submission for \'I will do wix website design, redesign business wix website, website development\' needs revisions. Please check the client\'s feedback and resubmit. You have 1 revision(s) remaining.', 'work_revision', 12, '2025-11-30 22:57:04', 0, NULL),
(17, 5, 'client', 'Freelancer has submitted work for \'I will do wix website design, redesign business wix website, website development\'. Please review the deliverables.', 'work_submission', 13, '2025-11-30 22:58:01', 0, NULL),
(18, 8, 'freelancer', 'Your work submission for \'I will do wix website design, redesign business wix website, website development\' needs revisions. Please check the client\'s feedback and resubmit. This is your final revision.', 'work_revision', 13, '2025-11-30 22:58:24', 0, NULL),
(19, 5, 'client', 'Freelancer has submitted work for \'I will do wix website design, redesign business wix website, website development\'. Please review the deliverables.', 'work_submission', 14, '2025-11-30 22:58:57', 0, NULL),
(20, 8, 'freelancer', 'Your work for \'I will do wix website design, redesign business wix website, website development\' has been approved! Payment of RM 1,700.00 has been released to your wallet.', 'work_approval', 14, '2025-11-30 23:02:26', 0, NULL),
(21, 8, 'freelancer', 'You have received a new 5-star review from a client!', NULL, NULL, '2025-12-02 14:26:44', 0, NULL),
(22, 6, 'client', 'Freelancer has submitted work for \'We are looking for graphic designer\'. Please review the deliverables.', 'work_submission', 15, '2025-12-07 15:51:33', 0, NULL),
(23, 8, 'freelancer', 'Your work for \'We are looking for graphic designer\' has been approved! Payment of RM 800.00 has been released to your wallet.', 'work_approval', 15, '2025-12-07 15:53:27', 0, NULL),
(24, 5, 'client', 'Freelancer has submitted work for \'We are looking for Java developer to develop a website using Java.\'. Please review the deliverables.', 'work_submission', 16, '2025-12-07 16:07:13', 0, NULL),
(25, 8, 'freelancer', 'Your work for \'We are looking for Java developer to develop a website using Java.\' has been approved! Payment of RM 1,000.00 has been released to your wallet.', 'work_approval', 16, '2025-12-07 16:07:26', 0, NULL),
(26, 5, 'client', 'E-Invoice INV-20251207-000030 has been generated for \'We are looking for Java developer to develop a website using Java.\'. View your invoice in the transaction history.', 'invoice', 30, '2025-12-07 16:07:26', 0, NULL),
(27, 8, 'freelancer', 'E-Invoice INV-20251207-000030 has been generated for \'We are looking for Java developer to develop a website using Java.\'. View your invoice in the transaction history.', 'invoice', 30, '2025-12-07 16:07:26', 0, NULL),
(28, 5, 'client', 'Freelancer has submitted work for \'Design an E-commerce Website\'. Please review the deliverables.', 'work_submission', 17, '2025-12-08 17:31:49', 0, NULL),
(29, 8, 'freelancer', 'Your work for \'Design an E-commerce Website\' has been approved! Payment of RM 1,200.00 has been released to your wallet.', 'work_approval', 17, '2025-12-08 21:43:46', 0, NULL),
(30, 5, 'client', 'E-Invoice INV-20251208-000031 has been generated for \'Design an E-commerce Website\'. View your invoice in the transaction history.', 'invoice', 31, '2025-12-08 21:43:46', 0, NULL),
(31, 8, 'freelancer', 'E-Invoice INV-20251208-000031 has been generated for \'Design an E-commerce Website\'. View your invoice in the transaction history.', 'invoice', 31, '2025-12-08 21:43:46', 0, NULL),
(32, 9, 'freelancer', 'New gig order from Google for \'I will do modern minimalist business logo design\'. Payment of RM 230.00 is held in escrow. Please review and accept the agreement.', 'gig_order', 34, '2025-12-12 07:28:57', 0, NULL),
(33, 5, 'client', 'Freelancer has submitted work for \'I will do modern minimalist business logo design\'. Please review the deliverables.', 'work_submission', 18, '2025-12-12 07:30:54', 0, NULL),
(34, 9, 'freelancer', 'Your work submission for \'I will do modern minimalist business logo design\' needs revisions. Please check the client\'s feedback and resubmit. You have 2 revision(s) remaining.', 'work_revision', 18, '2025-12-12 07:43:37', 0, NULL),
(35, 5, 'client', 'Freelancer has submitted work for \'I will do modern minimalist business logo design\'. Please review the deliverables.', 'work_submission', 19, '2025-12-12 07:44:05', 0, NULL),
(36, 9, 'freelancer', 'Your work submission for \'I will do modern minimalist business logo design\' needs revisions. Please check the client\'s feedback and resubmit. You have 1 revision(s) remaining.', 'work_revision', 19, '2025-12-12 07:44:15', 0, NULL),
(37, 5, 'client', 'Freelancer has submitted work for \'I will do modern minimalist business logo design\'. Please review the deliverables.', 'work_submission', 20, '2025-12-12 07:44:24', 0, NULL),
(38, 9, 'freelancer', 'Your work submission for \'I will do modern minimalist business logo design\' needs revisions. Please check the client\'s feedback and resubmit. This is your final revision.', 'work_revision', 20, '2025-12-12 07:44:30', 0, NULL),
(39, 5, 'client', 'Freelancer has submitted work for \'I will do modern minimalist business logo design\'. Please review the deliverables.', 'work_submission', 21, '2025-12-12 07:44:42', 0, NULL),
(40, 9, 'freelancer', 'Your work for \'I will do modern minimalist business logo design\' has been approved! Payment of RM 230.00 has been released to your wallet.', 'work_approval', 21, '2025-12-12 07:44:51', 0, NULL),
(41, 5, 'client', 'E-Invoice INV-20251212-000034 has been generated for \'I will do modern minimalist business logo design\'. View your invoice in the transaction history.', 'invoice', 34, '2025-12-12 07:44:51', 0, NULL),
(42, 9, 'freelancer', 'E-Invoice INV-20251212-000034 has been generated for \'I will do modern minimalist business logo design\'. View your invoice in the transaction history.', 'invoice', 34, '2025-12-12 07:44:51', 0, NULL),
(43, 12, 'freelancer', 'New gig order from Lee Chun Yin for \'i will develop a mobile app\'. Payment of RM 7.00 is held in escrow. Please review and accept the agreement.', 'gig_order', 39, '2025-12-12 11:08:27', 0, NULL),
(44, 8, 'client', 'Freelancer has submitted work for \'i will develop a mobile app\'. Please review the deliverables.', 'work_submission', 22, '2025-12-12 11:10:06', 0, NULL),
(45, 12, 'freelancer', 'Your work submission for \'i will develop a mobile app\' needs revisions. Please check the client\'s feedback and resubmit. You have 3 revision(s) remaining.', 'work_revision', 22, '2025-12-12 11:11:18', 0, NULL),
(46, 8, 'client', 'Freelancer has submitted work for \'i will develop a mobile app\'. Please review the deliverables.', 'work_submission', 23, '2025-12-12 11:11:43', 0, NULL),
(47, 12, 'freelancer', 'Your work submission for \'i will develop a mobile app\' needs revisions. Please check the client\'s feedback and resubmit. You have 2 revision(s) remaining.', 'work_revision', 23, '2025-12-12 11:11:59', 0, NULL),
(48, 8, 'client', 'Freelancer has submitted work for \'i will develop a mobile app\'. Please review the deliverables.', 'work_submission', 24, '2025-12-12 11:12:19', 0, NULL),
(49, 12, 'freelancer', 'Your work submission for \'i will develop a mobile app\' needs revisions. Please check the client\'s feedback and resubmit. You have 1 revision(s) remaining.', 'work_revision', 24, '2025-12-12 11:12:30', 0, NULL),
(50, 8, 'client', 'Freelancer has submitted work for \'i will develop a mobile app\'. Please review the deliverables.', 'work_submission', 25, '2025-12-12 11:12:45', 0, NULL),
(51, 12, 'freelancer', 'Your work submission for \'i will develop a mobile app\' needs revisions. Please check the client\'s feedback and resubmit. This is your final revision.', 'work_revision', 25, '2025-12-12 11:12:53', 0, NULL),
(52, 8, 'client', 'Freelancer has submitted work for \'i will develop a mobile app\'. Please review the deliverables.', 'work_submission', 26, '2025-12-12 11:13:06', 0, NULL),
(53, 12, 'freelancer', 'Your work for \'i will develop a mobile app\' has been approved! Payment of RM 7.00 has been released to your wallet.', 'work_approval', 26, '2025-12-12 11:13:17', 0, NULL),
(54, 8, 'client', 'E-Invoice INV-20251212-000039 has been generated for \'i will develop a mobile app\'. View your invoice in the transaction history.', 'invoice', 39, '2025-12-12 11:13:17', 0, NULL),
(55, 12, 'freelancer', 'E-Invoice INV-20251212-000039 has been generated for \'i will develop a mobile app\'. View your invoice in the transaction history.', 'invoice', 39, '2025-12-12 11:13:17', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `ResetID` int(11) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `UserType` enum('freelancer','client') NOT NULL,
  `OTP` varchar(6) NOT NULL,
  `IsUsed` tinyint(1) DEFAULT 0,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `ExpiresAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `ReviewID` int(11) NOT NULL,
  `FreelancerID` int(11) DEFAULT NULL,
  `ClientID` int(11) DEFAULT NULL,
  `Rating` int(11) DEFAULT NULL,
  `Comment` text DEFAULT NULL,
  `ReviewDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`ReviewID`, `FreelancerID`, `ClientID`, `Rating`, `Comment`, `ReviewDate`) VALUES
(1, 8, 5, 5, 'good work', '2025-12-02');

-- --------------------------------------------------------

--
-- Table structure for table `skill`
--

CREATE TABLE `skill` (
  `SkillID` int(11) NOT NULL,
  `SkillName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submission_files`
--

CREATE TABLE `submission_files` (
  `FileID` int(11) NOT NULL,
  `SubmissionID` int(11) NOT NULL,
  `OriginalFileName` varchar(255) NOT NULL,
  `StoredFileName` varchar(255) NOT NULL,
  `FilePath` varchar(500) NOT NULL,
  `FileSize` bigint(20) NOT NULL,
  `FileType` varchar(50) DEFAULT NULL,
  `UploadedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submission_files`
--

INSERT INTO `submission_files` (`FileID`, `SubmissionID`, `OriginalFileName`, `StoredFileName`, `FilePath`, `FileSize`, `FileType`, `UploadedAt`) VALUES
(11, 10, 'LOO JIE YANG.zip', '1764492358_692c04465cb65_LOOJIEYANG.zip', 'uploads/work_submissions/agreement_27/1764492358_692c04465cb65_LOOJIEYANG.zip', 909658, 'zip', '2025-11-30 16:45:58'),
(12, 11, 'LOO JIE YANG.zip', '1764492812_692c060c23629_LOOJIEYANG.zip', 'uploads/work_submissions/agreement_27/1764492812_692c060c23629_LOOJIEYANG.zip', 909658, 'zip', '2025-11-30 16:53:32'),
(13, 12, 'LOO JIE YANG.zip', '1764507126_692c3df6e7323_LOOJIEYANG.zip', 'uploads/work_submissions/agreement_28/1764507126_692c3df6e7323_LOOJIEYANG.zip', 909658, 'zip', '2025-11-30 20:52:06'),
(14, 13, 'LOO JIE YANG.zip', '1764514681_692c5b799a485_LOOJIEYANG.zip', 'uploads/work_submissions/agreement_28/1764514681_692c5b799a485_LOOJIEYANG.zip', 909658, 'zip', '2025-11-30 22:58:01'),
(15, 14, 'LOO JIE YANG.zip', '1764514737_692c5bb1a78d3_LOOJIEYANG.zip', 'uploads/work_submissions/agreement_28/1764514737_692c5bb1a78d3_LOOJIEYANG.zip', 909658, 'zip', '2025-11-30 22:58:57'),
(16, 15, 'Loo Jie Yang\'s Resume.pdf', '1765093893_693532052c23c_LooJieYangsResume.pdf', 'uploads/work_submissions/agreement_29/1765093893_693532052c23c_LooJieYangsResume.pdf', 132462, 'pdf', '2025-12-07 15:51:33'),
(17, 16, 'Loo Jie Yang\'s Resume.pdf', '1765094833_693535b17d18c_LooJieYangsResume.pdf', 'uploads/work_submissions/agreement_30/1765094833_693535b17d18c_LooJieYangsResume.pdf', 132462, 'pdf', '2025-12-07 16:07:13'),
(18, 17, 'Loo Jie Yang\'s Resume.pdf', '1765186309_69369b0538f5f_LooJieYangsResume.pdf', 'uploads/work_submissions/agreement_31/1765186309_69369b0538f5f_LooJieYangsResume.pdf', 132462, 'pdf', '2025-12-08 17:31:49'),
(19, 18, '4B-20251211T014504Z-1-001.zip', '1765495854_693b542e7f055_4B-20251211T014504Z-1-001.zip', 'uploads/work_submissions/agreement_34/1765495854_693b542e7f055_4B-20251211T014504Z-1-001.zip', 933, 'zip', '2025-12-12 07:30:54'),
(20, 19, '4B-20251211T014504Z-1-001.zip', '1765496645_693b574522b3a_4B-20251211T014504Z-1-001.zip', 'uploads/work_submissions/agreement_34/1765496645_693b574522b3a_4B-20251211T014504Z-1-001.zip', 933, 'zip', '2025-12-12 07:44:05'),
(21, 20, '4B-20251211T014504Z-1-001.zip', '1765496664_693b5758d1e19_4B-20251211T014504Z-1-001.zip', 'uploads/work_submissions/agreement_34/1765496664_693b5758d1e19_4B-20251211T014504Z-1-001.zip', 933, 'zip', '2025-12-12 07:44:24'),
(22, 21, '4B-20251211T014504Z-1-001.zip', '1765496682_693b576a0ea76_4B-20251211T014504Z-1-001.zip', 'uploads/work_submissions/agreement_34/1765496682_693b576a0ea76_4B-20251211T014504Z-1-001.zip', 933, 'zip', '2025-12-12 07:44:42'),
(23, 22, '4B-20251211T014504Z-1-001.zip', '1765509006_693b878e9e74d_4B-20251211T014504Z-1-001.zip', 'uploads/work_submissions/agreement_39/1765509006_693b878e9e74d_4B-20251211T014504Z-1-001.zip', 933, 'zip', '2025-12-12 11:10:06'),
(24, 23, '4B-20251211T014504Z-1-001.zip', '1765509103_693b87efc0214_4B-20251211T014504Z-1-001.zip', 'uploads/work_submissions/agreement_39/1765509103_693b87efc0214_4B-20251211T014504Z-1-001.zip', 933, 'zip', '2025-12-12 11:11:43'),
(25, 24, '4B-20251211T014504Z-1-001.zip', '1765509139_693b8813065fd_4B-20251211T014504Z-1-001.zip', 'uploads/work_submissions/agreement_39/1765509139_693b8813065fd_4B-20251211T014504Z-1-001.zip', 933, 'zip', '2025-12-12 11:12:19'),
(26, 25, '4B-20251211T014504Z-1-001.zip', '1765509165_693b882d4b5e5_4B-20251211T014504Z-1-001.zip', 'uploads/work_submissions/agreement_39/1765509165_693b882d4b5e5_4B-20251211T014504Z-1-001.zip', 933, 'zip', '2025-12-12 11:12:45'),
(27, 26, '4B-20251211T014504Z-1-001.zip', '1765509186_693b88426a640_4B-20251211T014504Z-1-001.zip', 'uploads/work_submissions/agreement_39/1765509186_693b88426a640_4B-20251211T014504Z-1-001.zip', 933, 'zip', '2025-12-12 11:13:06');

-- --------------------------------------------------------

--
-- Table structure for table `wallet`
--

CREATE TABLE `wallet` (
  `WalletID` int(11) NOT NULL,
  `UserID` varchar(11) NOT NULL,
  `Balance` decimal(10,2) NOT NULL,
  `LockedBalance` decimal(10,2) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallet`
--

INSERT INTO `wallet` (`WalletID`, `UserID`, `Balance`, `LockedBalance`, `LastUpdated`) VALUES
(8, '8', 2516.00, 77.00, '2025-12-12 03:13:17'),
(9, '5', 870.00, 500.00, '2025-12-12 06:47:18'),
(10, '6', 200.00, 0.00, '2025-12-07 07:53:27'),
(11, '9', 1230.00, 0.00, '2025-12-11 23:44:51'),
(12, '12', 500.00, 0.00, '2025-12-12 03:14:01');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `TransactionID` int(11) NOT NULL,
  `WalletID` int(11) NOT NULL,
  `Type` enum('topup','payment','earning','refund','withdrawal') NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `Status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `Description` text DEFAULT NULL,
  `ReferenceID` varchar(100) DEFAULT NULL COMMENT 'Order ID, Payment ID, etc.',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`TransactionID`, `WalletID`, `Type`, `Amount`, `Status`, `Description`, `ReferenceID`, `CreatedAt`) VALUES
(51, 8, 'topup', 2000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-11-30 07:33:38'),
(52, 9, 'topup', 3000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-11-30 07:54:18'),
(53, 9, 'payment', 1500.00, 'completed', 'Funds locked in escrow for project: Mobile Application Project (Agreement #27)', 'escrow_12', '2025-11-30 07:54:45'),
(54, 8, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-11-30 08:52:08'),
(55, 8, '', 1500.00, 'pending', 'Payment received for \'Mobile Application Project\' (Agreement #27)', NULL, '2025-11-30 08:59:06'),
(56, 9, 'payment', 1500.00, 'pending', 'Payment released for \'Mobile Application Project\' (Agreement #27)', NULL, '2025-11-30 08:59:06'),
(57, 8, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-11-30 09:03:00'),
(58, 8, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-11-30 09:03:02'),
(59, 9, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-11-30 12:38:45'),
(60, 9, 'payment', 1700.00, 'completed', 'Payment for gig \'I will do wix website design, redesign business wix website, website development\' - Funds locked in escrow (Agreement #28)', 'escrow_13', '2025-11-30 12:41:10'),
(61, 8, '', 1700.00, 'pending', 'Payment received for \'I will do wix website design, redesign business wix website, website development\' (Agreement #28)', NULL, '2025-11-30 15:02:26'),
(62, 9, 'payment', 1700.00, 'pending', 'Payment released for \'I will do wix website design, redesign business wix website, website development\' (Agreement #28)', NULL, '2025-11-30 15:02:26'),
(63, 8, 'withdrawal', 8200.00, '', 'Withdrawal to Maybank - ddd (****4242)', NULL, '2025-11-30 15:16:42'),
(64, 9, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-12-02 08:04:08'),
(65, 9, 'withdrawal', 1000.00, '', 'Withdrawal to Maybank - 12 (****4242)', NULL, '2025-12-02 08:13:38'),
(66, 9, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-12-02 08:14:18'),
(67, 8, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-12-02 12:04:44'),
(68, 8, 'withdrawal', 400.00, '', 'Withdrawal to Maybank - loo jie yang (****4242)', NULL, '2025-12-02 12:15:23'),
(69, 8, 'withdrawal', 100.00, 'completed', 'Withdrawal to Maybank - loo jie yang (****4242)', NULL, '2025-12-02 12:21:10'),
(70, 8, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-12-07 07:38:10'),
(71, 10, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-12-07 07:43:03'),
(72, 10, 'payment', 800.00, 'completed', 'Funds locked in escrow for project: We are looking for graphic designer (Agreement #29)', 'escrow_14', '2025-12-07 07:43:26'),
(73, 8, '', 800.00, 'pending', 'Payment received for \'We are looking for graphic designer\' (Agreement #29)', NULL, '2025-12-07 07:53:27'),
(74, 10, 'payment', 800.00, 'pending', 'Payment released for \'We are looking for graphic designer\' (Agreement #29)', NULL, '2025-12-07 07:53:27'),
(75, 9, 'payment', 1000.00, 'completed', 'Funds locked in escrow for project: We are looking for Java developer to develop a website using Java. (Agreement #30)', 'escrow_15', '2025-12-07 08:06:32'),
(76, 8, '', 1000.00, 'pending', 'Payment received for \'We are looking for Java developer to develop a website using Java.\' (Agreement #30)', NULL, '2025-12-07 08:07:26'),
(77, 9, 'payment', 1000.00, 'pending', 'Payment released for \'We are looking for Java developer to develop a website using Java.\' (Agreement #30)', NULL, '2025-12-07 08:07:26'),
(78, 8, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-12-08 08:44:43'),
(79, 8, 'withdrawal', 2000.00, 'completed', 'Withdrawal to Public Bank - loo jie yang (****4242)', NULL, '2025-12-08 08:45:37'),
(80, 8, 'withdrawal', 1000.00, 'completed', 'Withdrawal to CIMB Bank - loo jie yang (****4242)', NULL, '2025-12-08 08:46:54'),
(81, 9, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-12-08 09:27:56'),
(82, 9, 'payment', 1200.00, 'completed', 'Funds locked in escrow for project: Design an E-commerce Website (Agreement #31)', 'escrow_16', '2025-12-08 09:29:23'),
(83, 8, '', 1200.00, 'pending', 'Payment received for \'Design an E-commerce Website\' (Agreement #31)', NULL, '2025-12-08 13:43:46'),
(84, 9, 'payment', 1200.00, 'pending', 'Payment released for \'Design an E-commerce Website\' (Agreement #31)', NULL, '2025-12-08 13:43:46'),
(85, 11, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-12-11 12:12:41'),
(86, 9, 'payment', 500.00, 'completed', 'Funds locked in escrow for project: We are looking for desinger to design a poster for us (Agreement #32)', 'escrow_17', '2025-12-11 12:14:49'),
(87, 9, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-12-11 16:46:21'),
(88, 9, 'payment', 800.00, 'completed', 'Funds locked in escrow for project: web design project (Agreement #33)', 'escrow_18', '2025-12-11 16:46:48'),
(89, 8, 'topup', 100.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-12-11 16:58:03'),
(90, 9, 'payment', 230.00, 'completed', 'Payment for gig \'I will do modern minimalist business logo design\' - Funds locked in escrow (Agreement #34)', 'escrow_19', '2025-12-11 23:28:57'),
(91, 11, '', 230.00, 'pending', 'Payment received for \'I will do modern minimalist business logo design\' (Agreement #34)', NULL, '2025-12-11 23:44:51'),
(92, 9, 'payment', 230.00, 'pending', 'Payment released for \'I will do modern minimalist business logo design\' (Agreement #34)', NULL, '2025-12-11 23:44:51'),
(93, 12, 'topup', 1000.00, 'completed', 'Wallet Top Up via Stripe', NULL, '2025-12-12 02:37:09'),
(94, 8, 'payment', 850.00, 'completed', 'Funds locked in escrow for project: Web Design for our company official website (Agreement #35)', 'escrow_20', '2025-12-12 02:41:34'),
(95, 8, 'refund', 850.00, 'completed', 'Refund - Agreement declined: Web Design for our company official website (Agreement #35)', 'escrow_refund_20', '2025-12-12 02:43:18'),
(96, 8, 'payment', 100.00, 'completed', 'Funds locked in escrow for project: Graphic Designer (Agreement #36)', 'escrow_21', '2025-12-12 02:45:14'),
(97, 8, 'refund', 100.00, 'completed', 'Refund - Agreement declined: Graphic Designer (Agreement #36)', 'escrow_refund_21', '2025-12-12 02:47:20'),
(98, 8, 'payment', 200.00, 'completed', 'Funds locked in escrow for project: Interior Designer (Agreement #37)', 'escrow_22', '2025-12-12 02:48:38'),
(99, 8, 'refund', 200.00, 'completed', 'Dispute refund for agreement #37: I think the client side is correct', 'dispute_refund_37', '2025-12-12 02:54:36'),
(100, 8, 'payment', 77.00, 'completed', 'Funds locked in escrow for project: internship (Agreement #38)', 'escrow_23', '2025-12-12 02:57:01'),
(101, 12, 'earning', 77.00, 'completed', 'Dispute resolution: Payment released for agreement #38: I think the freelancer side is correct', 'dispute_release_38', '2025-12-12 02:58:42'),
(102, 12, '', 77.00, 'completed', 'Dispute reversal: Payment deducted for agreement #38', 'dispute_reverse_release_38', '2025-12-12 03:00:28'),
(103, 8, 'payment', 7.00, 'completed', 'Payment for gig \'i will develop a mobile app\' - Funds locked in escrow (Agreement #39)', 'escrow_24', '2025-12-12 03:08:27'),
(104, 12, '', 7.00, 'pending', 'Payment received for \'i will develop a mobile app\' (Agreement #39)', NULL, '2025-12-12 03:13:17'),
(105, 8, 'payment', 7.00, 'pending', 'Payment released for \'i will develop a mobile app\' (Agreement #39)', NULL, '2025-12-12 03:13:17'),
(106, 12, 'withdrawal', 507.00, 'completed', 'Withdrawal to CIMB Bank - Loo Jie Yang (****4242)', NULL, '2025-12-12 03:14:01'),
(107, 9, 'refund', 800.00, 'completed', 'Refund - Agreement declined: web design project (Agreement #33)', 'escrow_refund_18', '2025-12-12 06:47:18');

-- --------------------------------------------------------

--
-- Table structure for table `work_submissions`
--

CREATE TABLE `work_submissions` (
  `SubmissionID` int(11) NOT NULL,
  `AgreementID` int(11) NOT NULL,
  `FreelancerID` int(11) NOT NULL,
  `ClientID` int(11) NOT NULL,
  `SubmissionTitle` varchar(255) NOT NULL,
  `SubmissionNotes` text DEFAULT NULL,
  `Status` enum('pending_review','approved','rejected','revision_requested') DEFAULT 'pending_review',
  `ReviewNotes` text DEFAULT NULL,
  `ReviewedAt` datetime DEFAULT NULL,
  `SubmittedAt` datetime NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_submissions`
--

INSERT INTO `work_submissions` (`SubmissionID`, `AgreementID`, `FreelancerID`, `ClientID`, `SubmissionTitle`, `SubmissionNotes`, `Status`, `ReviewNotes`, `ReviewedAt`, `SubmittedAt`, `CreatedAt`) VALUES
(10, 27, 8, 5, 'Mobile Development Submission', 'mobile development completed', 'rejected', 'change design', '2025-11-30 16:50:16', '2025-11-30 16:45:58', '2025-11-30 08:45:58'),
(11, 27, 8, 5, 'Mobile Development Submission', 'mobile development second version', 'approved', '', '2025-11-30 16:59:06', '2025-11-30 16:53:32', '2025-11-30 08:53:32'),
(12, 28, 8, 5, 'Final website development submission', 'final website development submission', 'rejected', 'redesign the UI', '2025-11-30 22:57:04', '2025-11-30 20:52:06', '2025-11-30 12:52:06'),
(13, 28, 8, 5, 'Final website development submission 2', 'UI redesign completed', 'rejected', 'add some additional features', '2025-11-30 22:58:24', '2025-11-30 22:58:01', '2025-11-30 14:58:01'),
(14, 28, 8, 5, 'Final website development submission 3', 'added features', 'approved', '', '2025-11-30 23:02:26', '2025-11-30 22:58:57', '2025-11-30 14:58:57'),
(15, 29, 8, 6, 'completed', 'completed', 'approved', '', '2025-12-07 15:53:27', '2025-12-07 15:51:33', '2025-12-07 07:51:33'),
(16, 30, 8, 5, 'completed', 'asdf', 'approved', '', '2025-12-07 16:07:26', '2025-12-07 16:07:13', '2025-12-07 08:07:13'),
(17, 31, 8, 5, 'Final Design Submission', 'I have completed the e-commerce webstite design.', 'approved', '', '2025-12-08 21:43:46', '2025-12-08 17:31:49', '2025-12-08 09:31:49'),
(18, 34, 9, 5, 'Final Submission', 'i have completed the project', 'rejected', 'change the design a little bit', '2025-12-12 07:43:37', '2025-12-12 07:30:54', '2025-12-11 23:30:54'),
(19, 34, 9, 5, 'final project submission', 'i have redesign a little bit', 'rejected', 'ddd', '2025-12-12 07:44:15', '2025-12-12 07:44:05', '2025-12-11 23:44:05'),
(20, 34, 9, 5, 'ddd', 'ddd', 'rejected', 'fff', '2025-12-12 07:44:30', '2025-12-12 07:44:24', '2025-12-11 23:44:24'),
(21, 34, 9, 5, 'fff', 'fff', 'approved', '', '2025-12-12 07:44:51', '2025-12-12 07:44:42', '2025-12-11 23:44:42'),
(22, 39, 12, 8, 'final project submission', 'final project submission', 'rejected', 'ddd', '2025-12-12 11:11:18', '2025-12-12 11:10:06', '2025-12-12 03:10:06'),
(23, 39, 12, 8, 'resubmit project', 'resubmit project', 'rejected', 'dd', '2025-12-12 11:11:59', '2025-12-12 11:11:43', '2025-12-12 03:11:43'),
(24, 39, 12, 8, 'dddddd', 'dd', 'rejected', 'ddd', '2025-12-12 11:12:30', '2025-12-12 11:12:19', '2025-12-12 03:12:19'),
(25, 39, 12, 8, 'ddd', 'ddd', 'rejected', 'ddd', '2025-12-12 11:12:53', '2025-12-12 11:12:45', '2025-12-12 03:12:45'),
(26, 39, 12, 8, 'sss', 'sss', 'approved', '', '2025-12-12 11:13:17', '2025-12-12 11:13:06', '2025-12-12 03:13:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `agreement`
--
ALTER TABLE `agreement`
  ADD PRIMARY KEY (`AgreementID`),
  ADD KEY `FreelancerID` (`FreelancerID`),
  ADD KEY `ClientID` (`ClientID`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`ClientID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`ConversationID`),
  ADD UNIQUE KEY `unique_conversation` (`User1ID`,`User1Type`,`User2ID`,`User2Type`),
  ADD KEY `idx_user1` (`User1ID`,`User1Type`),
  ADD KEY `idx_user2` (`User2ID`,`User2Type`),
  ADD KEY `idx_timestamp` (`LastMessageAt`);

--
-- Indexes for table `dispute`
--
ALTER TABLE `dispute`
  ADD PRIMARY KEY (`DisputeID`),
  ADD KEY `fk_dispute_admin` (`ResolvedByAdminID`),
  ADD KEY `idx_agreement` (`AgreementID`),
  ADD KEY `idx_initiator` (`InitiatorID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_created` (`CreatedAt`);

--
-- Indexes for table `escrow`
--
ALTER TABLE `escrow`
  ADD PRIMARY KEY (`EscrowID`);

--
-- Indexes for table `freelancer`
--
ALTER TABLE `freelancer`
  ADD PRIMARY KEY (`FreelancerID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `freelancerskill`
--
ALTER TABLE `freelancerskill`
  ADD PRIMARY KEY (`FreelancerID`,`SkillID`),
  ADD KEY `SkillID` (`SkillID`);

--
-- Indexes for table `gig`
--
ALTER TABLE `gig`
  ADD PRIMARY KEY (`GigID`),
  ADD KEY `FreelancerID` (`FreelancerID`),
  ADD KEY `idx_category_subcategory` (`Category`,`Subcategory`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`InvoiceID`),
  ADD UNIQUE KEY `unique_agreement` (`AgreementID`),
  ADD KEY `idx_client` (`ClientID`),
  ADD KEY `idx_freelancer` (`FreelancerID`),
  ADD KEY `idx_invoice_number` (`InvoiceNumber`);

--
-- Indexes for table `job`
--
ALTER TABLE `job`
  ADD PRIMARY KEY (`JobID`),
  ADD KEY `ClientID` (`ClientID`);

--
-- Indexes for table `job_application`
--
ALTER TABLE `job_application`
  ADD PRIMARY KEY (`ApplicationID`),
  ADD KEY `JobID` (`JobID`),
  ADD KEY `FreelancerID` (`FreelancerID`),
  ADD KEY `Status` (`Status`);

--
-- Indexes for table `job_application_answer`
--
ALTER TABLE `job_application_answer`
  ADD PRIMARY KEY (`AnswerID`),
  ADD KEY `QuestionID` (`QuestionID`),
  ADD KEY `FreelancerID` (`FreelancerID`),
  ADD KEY `JobID` (`JobID`),
  ADD KEY `SelectedOptionID` (`SelectedOptionID`);

--
-- Indexes for table `job_question`
--
ALTER TABLE `job_question`
  ADD PRIMARY KEY (`QuestionID`),
  ADD KEY `JobID` (`JobID`);

--
-- Indexes for table `job_question_option`
--
ALTER TABLE `job_question_option`
  ADD PRIMARY KEY (`OptionID`),
  ADD KEY `QuestionID` (`QuestionID`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`MessageID`),
  ADD KEY `idx_sender` (`SenderID`),
  ADD KEY `idx_receiver` (`ReceiverID`),
  ADD KEY `idx_timestamp` (`Timestamp`),
  ADD KEY `idx_conversation` (`ConversationID`);

--
-- Indexes for table `message_notification`
--
ALTER TABLE `message_notification`
  ADD PRIMARY KEY (`NotificationID`),
  ADD UNIQUE KEY `unique_notification` (`ReceiverID`,`ReceiverType`,`SenderID`,`SenderType`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `idx_user` (`UserID`,`UserType`),
  ADD KEY `idx_read` (`IsRead`),
  ADD KEY `idx_created` (`CreatedAt`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`ResetID`),
  ADD KEY `idx_email` (`Email`),
  ADD KEY `idx_expires` (`ExpiresAt`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`ReviewID`),
  ADD KEY `FreelancerID` (`FreelancerID`),
  ADD KEY `ClientID` (`ClientID`);

--
-- Indexes for table `skill`
--
ALTER TABLE `skill`
  ADD PRIMARY KEY (`SkillID`);

--
-- Indexes for table `submission_files`
--
ALTER TABLE `submission_files`
  ADD PRIMARY KEY (`FileID`),
  ADD KEY `idx_submission` (`SubmissionID`);

--
-- Indexes for table `wallet`
--
ALTER TABLE `wallet`
  ADD PRIMARY KEY (`WalletID`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`TransactionID`),
  ADD KEY `WalletID` (`WalletID`),
  ADD KEY `idx_wallet_type_status` (`WalletID`,`Type`,`Status`),
  ADD KEY `idx_created_at` (`CreatedAt`);

--
-- Indexes for table `work_submissions`
--
ALTER TABLE `work_submissions`
  ADD PRIMARY KEY (`SubmissionID`),
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
ALTER TABLE `admin`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agreement`
--
ALTER TABLE `agreement`
  MODIFY `AgreementID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `ClientID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation`
  MODIFY `ConversationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `dispute`
--
ALTER TABLE `dispute`
  MODIFY `DisputeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `escrow`
--
ALTER TABLE `escrow`
  MODIFY `EscrowID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `freelancer`
--
ALTER TABLE `freelancer`
  MODIFY `FreelancerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `gig`
--
ALTER TABLE `gig`
  MODIFY `GigID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `InvoiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `job`
--
ALTER TABLE `job`
  MODIFY `JobID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `job_application`
--
ALTER TABLE `job_application`
  MODIFY `ApplicationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `job_application_answer`
--
ALTER TABLE `job_application_answer`
  MODIFY `AnswerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `job_question`
--
ALTER TABLE `job_question`
  MODIFY `QuestionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `job_question_option`
--
ALTER TABLE `job_question_option`
  MODIFY `OptionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `MessageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `message_notification`
--
ALTER TABLE `message_notification`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `ResetID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `ReviewID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `skill`
--
ALTER TABLE `skill`
  MODIFY `SkillID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `submission_files`
--
ALTER TABLE `submission_files`
  MODIFY `FileID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `wallet`
--
ALTER TABLE `wallet`
  MODIFY `WalletID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `TransactionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `work_submissions`
--
ALTER TABLE `work_submissions`
  MODIFY `SubmissionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agreement`
--
ALTER TABLE `agreement`
  ADD CONSTRAINT `agreement_ibfk_1` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`),
  ADD CONSTRAINT `agreement_ibfk_2` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`);

--
-- Constraints for table `dispute`
--
ALTER TABLE `dispute`
  ADD CONSTRAINT `fk_dispute_admin` FOREIGN KEY (`ResolvedByAdminID`) REFERENCES `admin` (`AdminID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_dispute_agreement` FOREIGN KEY (`AgreementID`) REFERENCES `agreement` (`AgreementID`) ON DELETE CASCADE;

--
-- Constraints for table `freelancerskill`
--
ALTER TABLE `freelancerskill`
  ADD CONSTRAINT `freelancerskill_ibfk_1` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `freelancerskill_ibfk_2` FOREIGN KEY (`SkillID`) REFERENCES `skill` (`SkillID`) ON DELETE CASCADE;

--
-- Constraints for table `gig`
--
ALTER TABLE `gig`
  ADD CONSTRAINT `gig_ibfk_1` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`);

--
-- Constraints for table `job`
--
ALTER TABLE `job`
  ADD CONSTRAINT `job_ibfk_1` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`) ON DELETE CASCADE;

--
-- Constraints for table `job_application`
--
ALTER TABLE `job_application`
  ADD CONSTRAINT `job_application_ibfk_1` FOREIGN KEY (`JobID`) REFERENCES `job` (`JobID`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_application_ibfk_2` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE;

--
-- Constraints for table `job_application_answer`
--
ALTER TABLE `job_application_answer`
  ADD CONSTRAINT `job_application_answer_ibfk_1` FOREIGN KEY (`QuestionID`) REFERENCES `job_question` (`QuestionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_application_answer_ibfk_2` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_application_answer_ibfk_3` FOREIGN KEY (`JobID`) REFERENCES `job` (`JobID`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_application_answer_ibfk_4` FOREIGN KEY (`SelectedOptionID`) REFERENCES `job_question_option` (`OptionID`) ON DELETE SET NULL;

--
-- Constraints for table `job_question`
--
ALTER TABLE `job_question`
  ADD CONSTRAINT `job_question_ibfk_1` FOREIGN KEY (`JobID`) REFERENCES `job` (`JobID`) ON DELETE CASCADE;

--
-- Constraints for table `job_question_option`
--
ALTER TABLE `job_question_option`
  ADD CONSTRAINT `job_question_option_ibfk_1` FOREIGN KEY (`QuestionID`) REFERENCES `job_question` (`QuestionID`) ON DELETE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_3` FOREIGN KEY (`ConversationID`) REFERENCES `conversation` (`ConversationID`) ON DELETE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`) ON DELETE CASCADE;

--
-- Constraints for table `submission_files`
--
ALTER TABLE `submission_files`
  ADD CONSTRAINT `submission_files_ibfk_1` FOREIGN KEY (`SubmissionID`) REFERENCES `work_submissions` (`SubmissionID`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`WalletID`) REFERENCES `wallet` (`WalletID`) ON DELETE CASCADE;

--
-- Constraints for table `work_submissions`
--
ALTER TABLE `work_submissions`
  ADD CONSTRAINT `work_submissions_ibfk_1` FOREIGN KEY (`AgreementID`) REFERENCES `agreement` (`AgreementID`) ON DELETE CASCADE,
  ADD CONSTRAINT `work_submissions_ibfk_2` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `work_submissions_ibfk_3` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
