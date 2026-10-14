-- Cancellation metadata. The refunds table already exists from migration 0005.
SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'cancelled_reason'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `orders` ADD COLUMN `cancelled_reason` VARCHAR(500) NULL AFTER `cancelled_at`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET SQL_MODE = @OLD_SQL_MODE;
