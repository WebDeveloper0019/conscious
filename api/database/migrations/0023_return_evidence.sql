-- Migration 0023: Return evidence images
--
-- Links uploaded images to return requests.
-- Uses the existing file_assets table for actual storage metadata.
--
-- Idempotent.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

CREATE TABLE IF NOT EXISTS `return_evidence` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `return_request_id` CHAR(36) NOT NULL,
  `file_asset_id` CHAR(36) NOT NULL,
  `uploaded_by_type` VARCHAR(20) NOT NULL DEFAULT 'customer',
  `uploaded_by_id` CHAR(36) NOT NULL,
  `caption` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,

  UNIQUE KEY `uniq_return_file` (`return_request_id`, `file_asset_id`),
  INDEX `idx_re_return` (`return_request_id`, `sort_order`),
  CONSTRAINT `fk_re_return` FOREIGN KEY (`return_request_id`) REFERENCES `return_requests`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_re_file`   FOREIGN KEY (`file_asset_id`)     REFERENCES `file_assets`(`id`)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration 0005 already created this table on existing installations. Add
-- the newer metadata columns explicitly instead of relying on CREATE IF NOT EXISTS.
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='return_evidence' AND COLUMN_NAME='caption');
SET @sql := IF(@col=0, 'ALTER TABLE `return_evidence` ADD COLUMN `caption` VARCHAR(255) NULL AFTER `uploaded_by_id`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='return_evidence' AND COLUMN_NAME='sort_order');
SET @sql := IF(@col=0, 'ALTER TABLE `return_evidence` ADD COLUMN `sort_order` INT NOT NULL DEFAULT 0 AFTER `caption`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='return_evidence' AND INDEX_NAME='uniq_return_file');
SET @sql := IF(@idx=0, 'ALTER TABLE `return_evidence` ADD UNIQUE KEY `uniq_return_file` (`return_request_id`,`file_asset_id`)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0023 applied' AS status;
