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

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0023 applied' AS status;