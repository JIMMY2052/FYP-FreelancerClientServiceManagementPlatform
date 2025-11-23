-- Add FreelancerName and SignaturePath columns to agreement table if they don't exist
ALTER TABLE `agreement`
ADD COLUMN `FreelancerName` varchar(255) NULL AFTER `ProjectDetail`;

ALTER TABLE `agreement`
ADD COLUMN `SignaturePath` varchar(255) NULL AFTER `FreelancerName`;