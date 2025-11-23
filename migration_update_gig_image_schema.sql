-- Migration: Update gig table to use separate image/video path columns
-- Date: 2025-11-23
-- Description: Replace GalleryUrl and ThumnailUrl with Image1Path, Image2Path, Image3Path, and VideoPath

-- Add new columns for individual image and video paths
ALTER TABLE `gig`
ADD COLUMN `Image1Path` VARCHAR(255) NULL AFTER `RevisionCount`,
ADD COLUMN `Image2Path` VARCHAR(255) NULL AFTER `Image1Path`,
ADD COLUMN `Image3Path` VARCHAR(255) NULL AFTER `Image2Path`,
ADD COLUMN `VideoPath` VARCHAR(255) NULL AFTER `Image3Path`;

-- Migrate existing GalleryUrl data to Image1Path (if data exists)
-- This assumes GalleryUrl contains JSON array of image paths
UPDATE `gig`
SET `Image1Path` = JSON_UNQUOTE(JSON_EXTRACT(`GalleryUrl`, '$[0]'))
WHERE `GalleryUrl` IS NOT NULL 
  AND `GalleryUrl` != ''
  AND JSON_VALID(`GalleryUrl`)
  AND JSON_LENGTH(`GalleryUrl`) > 0;

UPDATE `gig`
SET `Image2Path` = JSON_UNQUOTE(JSON_EXTRACT(`GalleryUrl`, '$[1]'))
WHERE `GalleryUrl` IS NOT NULL 
  AND `GalleryUrl` != ''
  AND JSON_VALID(`GalleryUrl`)
  AND JSON_LENGTH(`GalleryUrl`) > 1;

UPDATE `gig`
SET `Image3Path` = JSON_UNQUOTE(JSON_EXTRACT(`GalleryUrl`, '$[2]'))
WHERE `GalleryUrl` IS NOT NULL 
  AND `GalleryUrl` != ''
  AND JSON_VALID(`GalleryUrl`)
  AND JSON_LENGTH(`GalleryUrl`) > 2;

-- Optional: Drop old columns after confirming migration success
-- ALTER TABLE `gig` DROP COLUMN `GalleryUrl`;
-- ALTER TABLE `gig` DROP COLUMN `ThumnailUrl`;

-- Add indexes for better query performance
CREATE INDEX idx_gig_image1 ON `gig`(`Image1Path`);
CREATE INDEX idx_gig_status ON `gig`(`Status`);

-- Note: Make sure to backup your database before running this migration!
