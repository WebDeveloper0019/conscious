ALTER TABLE `products` ADD COLUMN `published_version` SMALLINT UNSIGNED NULL AFTER `review_version`, ADD COLUMN `published_snapshot_json` JSON NULL AFTER `published_version`;
