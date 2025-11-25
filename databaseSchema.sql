-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2025 at 01:56 PM
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
-- Table structure for table `agreement`
--
CREATE TABLE
  `agreement` (
    `AgreementID` int (11) NOT NULL,
    `FreelancerID` int (11) DEFAULT NULL,
    `ClientID` int (11) DEFAULT NULL,
    `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp(),
    `ClientSignedDate` datetime DEFAULT NULL,
    `FreelancerSignedDate` datetime DEFAULT NULL,
    `Terms` text DEFAULT NULL,
    `Status` varchar(50) DEFAULT NULL,
    `ProjectTitle` varchar(255) DEFAULT NULL,
    `Scope` text DEFAULT NULL,
    `Deliverables` text DEFAULT NULL,
    `PaymentAmount` decimal(10, 2) DEFAULT NULL,
    `ProjectDetail` text DEFAULT NULL,
    `FreelancerName` varchar(255) DEFAULT NULL,
    `ClientName` varchar(255) DEFAULT NULL,
    `SignaturePath` varchar(255) DEFAULT NULL
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
    `FreelancerSignedDate`,
    `Terms`,
    `Status`,
    `ProjectTitle`,
    `Scope`,
    `Deliverables`,
    `PaymentAmount`,
    `ProjectDetail`,
    `FreelancerName`,
    `ClientName`,
    `SignaturePath`
  )
VALUES
  (
    1,
    NULL,
    NULL,
    '2025-11-25 12:45:50',
    NULL,
    NULL,
    '• Both parties agree to the terms outlined above.\n• Payment will be processed upon project completion and mutual agreement.\n• Either party may terminate this agreement with written notice.\n• Both parties agree to maintain confidentiality of project details.\n• Any disputes will be resolved through communication or mediation.',
    'signed_by_client',
    'asdf',
    'asdf',
    'To be completed upon milestone deliveries as agreed.',
    666.00,
    'asdf',
    'JIMMY CHAN LOK',
    'Genting',
    'signature_client_3_1764074098'
  );

--
-- Indexes for dumped tables
--
--
-- Indexes for table `agreement`
--
ALTER TABLE `agreement` ADD PRIMARY KEY (`AgreementID`),
ADD KEY `fk_agreement_freelancer` (`FreelancerID`),
ADD KEY `fk_agreement_client` (`ClientID`);

--
-- AUTO_INCREMENT for dumped tables
--
--
-- AUTO_INCREMENT for table `agreement`
--
ALTER TABLE `agreement` MODIFY `AgreementID` int (11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;

--
-- Constraints for dumped tables
--
--
-- Constraints for table `agreement`
--
ALTER TABLE `agreement` ADD CONSTRAINT `fk_agreement_client` FOREIGN KEY (`ClientID`) REFERENCES `client` (`ClientID`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_agreement_freelancer` FOREIGN KEY (`FreelancerID`) REFERENCES `freelancer` (`FreelancerID`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;