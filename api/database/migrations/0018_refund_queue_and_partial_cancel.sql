-- Migration 0018: Refund queue + partial quantity cancellation support
--
-- Adds:
--   - refund_jobs table: async retry queue for Razorpay refund API calls
--   - cancelled_amount tracking on order_items (already have cancelled_quantity)
--   - refund_details_json on refunds for partial-quantity metadata
--
-- Idempotent — safe to run multiple times.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- Add refund fields required by the asynchronous provider worker. These live
-- here (not in an already-applied migration) so production upgrades are safe.
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='refunds' AND COLUMN_NAME='currency');
SET @sql := IF(@col=0, 'ALTER TABLE `refunds` ADD COLUMN `currency` CHAR(3) NOT NULL DEFAULT ''INR'' AFTER `amount`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='refunds' AND COLUMN_NAME='method');
SET @sql := IF(@col=0, 'ALTER TABLE `refunds` ADD COLUMN `method` VARCHAR(24) NOT NULL DEFAULT ''manual'' AFTER `currency`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='refunds' AND COLUMN_NAME='source');
SET @sql := IF(@col=0, 'ALTER TABLE `refunds` ADD COLUMN `source` VARCHAR(24) NOT NULL DEFAULT ''return'' AFTER `method`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='refunds' AND COLUMN_NAME='provider_payment_id');
SET @sql := IF(@col=0, 'ALTER TABLE `refunds` ADD COLUMN `provider_payment_id` VARCHAR(180) NULL AFTER `provider_reference`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='refunds' AND COLUMN_NAME='failure_message');
SET @sql := IF(@col=0, 'ALTER TABLE `refunds` ADD COLUMN `failure_message` VARCHAR(500) NULL AFTER `provider_payment_id`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

UPDATE `refunds` SET `source`='cancellation' WHERE `return_request_id` IS NULL;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='payment_method');
SET @sql := IF(@col=0, 'ALTER TABLE `orders` ADD COLUMN `payment_method` VARCHAR(24) NULL AFTER `payment_status`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ─────────────────────────────────────────────────────────
-- 1. refund_jobs — async worker queue
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `refund_jobs` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `refund_id` CHAR(36) NOT NULL,
  `order_id` BIGINT(20) UNSIGNED NOT NULL,
  `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `max_attempts` INT UNSIGNED NOT NULL DEFAULT 5,
  `status` VARCHAR(20) NOT NULL DEFAULT 'queued',
      -- queued | processing | succeeded | retry_scheduled | failed_permanent
  `next_attempt_at` DATETIME NOT NULL,
  `last_error` TEXT NULL,
  `locked_by` VARCHAR(64) NULL,
  `locked_until` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,

  UNIQUE KEY `uniq_refund_job` (`refund_id`),
  INDEX `idx_jobs_due` (`status`, `next_attempt_at`),
  CONSTRAINT `fk_refund_jobs_refund` FOREIGN KEY (`refund_id`) REFERENCES `refunds`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_refund_jobs_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────
-- 2. refunds — add details_json for partial-quantity metadata
-- ─────────────────────────────────────────────────────────
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'refunds'
               AND COLUMN_NAME = 'details_json');
SET @sql := IF(@col = 0,
  'ALTER TABLE `refunds` ADD COLUMN `details_json` LONGTEXT NULL AFTER `reason`',  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ─────────────────────────────────────────────────────────
-- 3. order_items — track partial cancel amount separately
--    (cancelled_quantity already exists from earlier migration)
-- ─────────────────────────────────────────────────────────
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_items'
               AND COLUMN_NAME = 'cancelled_amount');
SET @sql := IF(@col = 0,
  'ALTER TABLE `order_items` ADD COLUMN `cancelled_amount` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `cancelled_quantity`',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET SQL_MODE = @OLD_SQL_MODE;

SELECT '0018_refund_queue_and_partial_cancel applied' AS status;
