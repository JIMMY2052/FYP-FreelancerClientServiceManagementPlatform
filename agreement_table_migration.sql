-- Migration: Update agreement table structure
-- Remove SignaturePath and add ClientSignaturePath, FreelancerSignaturePath, ExpiredDate
-- Add new columns
ALTER TABLE `agreement`
ADD COLUMN `ClientSignaturePath` varchar(255) DEFAULT NULL AFTER `ClientSignedDate`,
ADD COLUMN `FreelancerSignaturePath` varchar(255) DEFAULT NULL AFTER `FreelancerSignedDate`,
ADD COLUMN `ExpiredDate` datetime DEFAULT NULL AFTER `FreelancerSignedDate`;

-- Drop old column
ALTER TABLE `agreement`
DROP COLUMN `SignaturePath`;

-- Verify the new structure
SHOW COLUMNS
FROM
    `agreement`;