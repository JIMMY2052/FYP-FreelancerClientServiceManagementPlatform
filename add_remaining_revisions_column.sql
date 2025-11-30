-- Add RemainingRevisions column to agreement table
-- This column tracks how many revisions the client can still request

-- Add the column if it doesn't exist
ALTER TABLE `agreement` 
ADD COLUMN IF NOT EXISTS `RemainingRevisions` INT(11) NOT NULL DEFAULT 3 
AFTER `PaymentAmount`;

-- Update existing agreements to have 3 revisions by default
UPDATE `agreement` 
SET `RemainingRevisions` = 3 
WHERE `RemainingRevisions` IS NULL OR `RemainingRevisions` = 0;

-- Display message
SELECT 'RemainingRevisions column added successfully to agreement table!' as Message;
