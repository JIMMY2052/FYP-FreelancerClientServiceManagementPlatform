-- Migration: Update gig table to support multi-image gallery storage
-- Created: November 23, 2025
-- Purpose: Add columns for storing individual image paths and rating information

-- Add columns for storing up to 3 images and 1 video
ALTER TABLE `gig` 
ADD COLUMN `Image1Path` VARCHAR(255) DEFAULT NULL AFTER `GalleryUrl`,
ADD COLUMN `Image2Path` VARCHAR(255) DEFAULT NULL AFTER `Image1Path`,
ADD COLUMN `Image3Path` VARCHAR(255) DEFAULT NULL AFTER `Image2Path`,
ADD COLUMN `VideoPath` VARCHAR(255) DEFAULT NULL AFTER `Image3Path`;

-- Add rating columns
ALTER TABLE `gig`
ADD COLUMN `Rating` DECIMAL(3, 2) DEFAULT 0.00 AFTER `VideoPath`,
ADD COLUMN `RatingCount` INT DEFAULT 0 AFTER `Rating`;

-- Update existing records to use GalleryUrl as Image1Path if needed
-- This will migrate any existing data from GalleryUrl to Image1Path
UPDATE `gig` 
SET `Image1Path` = `GalleryUrl` 
WHERE `GalleryUrl` IS NOT NULL AND `GalleryUrl` != '' AND `Image1Path` IS NULL;

-- Optional: Add comment to GalleryUrl for backward compatibility
ALTER TABLE `gig` 
MODIFY COLUMN `GalleryUrl` TEXT COMMENT 'Deprecated: Use Image1Path, Image2Path, Image3Path instead';

-- Add index for better performance on rating queries
ALTER TABLE `gig`
ADD INDEX `idx_rating` (`Rating`),
ADD INDEX `idx_category_subcategory` (`Category`, `Subcategory`);
