-- Alter gig table to match the new structure
ALTER TABLE `gig` 
ADD COLUMN `SearchTags` varchar(100) NOT NULL AFTER `Subcategory`,
ADD COLUMN `RushDelivery` int(11) DEFAULT NULL AFTER `DeliveryTime`,
ADD COLUMN `AdditionalRevision` int(11) DEFAULT NULL AFTER `RushDelivery`,
MODIFY COLUMN `ThumnailUrl` varchar(255) DEFAULT NULL,
DROP COLUMN `Visibility`,
DROP COLUMN `Rating`,
DROP COLUMN `RatingCount`,
MODIFY COLUMN `UpdatedAt` datetime DEFAULT NULL;
