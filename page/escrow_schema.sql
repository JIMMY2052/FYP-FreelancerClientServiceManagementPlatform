-- Escrow table for holding project funds safely
CREATE TABLE IF NOT EXISTS `escrow` (
    `EscrowID` int(11) NOT NULL AUTO_INCREMENT,
    `OrderID` int(11) DEFAULT NULL COMMENT 'Order/Project ID if applicable',
    `PayerID` int(11) NOT NULL COMMENT 'Client ID who pays',
    `PayeeID` int(11) NOT NULL COMMENT 'Freelancer ID who receives',
    `Amount` decimal(10,2) NOT NULL,
    `Status` enum('hold','released','refunded') NOT NULL DEFAULT 'hold',
    `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
    `ReleasedAt` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`EscrowID`),
    KEY `PayerID` (`PayerID`),
    KEY `PayeeID` (`PayeeID`),
    KEY `Status` (`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
