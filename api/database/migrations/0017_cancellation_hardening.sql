-- Cancellation hardening. No destructive deduplication is performed here.
SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'coupon_redemptions' AND COLUMN_NAME = 'status');
SET @sql := IF(@col = 0,
  'ALTER TABLE `coupon_redemptions` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT ''active'' AFTER `discount_amount`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'coupon_redemptions' AND COLUMN_NAME = 'reversed_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `coupon_redemptions` ADD COLUMN `reversed_at` DATETIME NULL AFTER `status`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'coupon_redemptions' AND COLUMN_NAME = 'reversed_reason');
SET @sql := IF(@col = 0,
  'ALTER TABLE `coupon_redemptions` ADD COLUMN `reversed_reason` VARCHAR(100) NULL AFTER `reversed_at`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET SQL_MODE = @OLD_SQL_MODE;
