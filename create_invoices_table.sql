-- Create invoices table
CREATE TABLE IF NOT EXISTS `invoices` (
  `InvoiceID` int(11) NOT NULL AUTO_INCREMENT,
  `InvoiceNumber` varchar(50) NOT NULL,
  `AgreementID` int(11) NOT NULL,
  `ClientID` int(11) NOT NULL,
  `FreelancerID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `InvoiceDate` datetime NOT NULL,
  `InvoiceFilePath` varchar(500) DEFAULT NULL,
  `Status` enum('generated','sent','paid') NOT NULL DEFAULT 'paid',
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`InvoiceID`),
  UNIQUE KEY `unique_agreement` (`AgreementID`),
  KEY `idx_client` (`ClientID`),
  KEY `idx_freelancer` (`FreelancerID`),
  KEY `idx_invoice_number` (`InvoiceNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
