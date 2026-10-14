<<<<<<< HEAD
-- Migration 0013: Cancellation + Refunds
-- Adds: refunds table, cancelled_reason on orders, cancellation audit trail

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- 1. Refunds table
CREATE TABLE IF NOT EXISTS `refunds` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `order_id` BIGINT(20) UNSIGNED NOT NULL,
  `order_item_id` BIGINT(20) UNSIGNED NULL,        -- NULL for full-order refund
  `user_id` CHAR(36) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `currency` CHAR(3) NOT NULL DEFAULT 'INR',
  `method` VARCHAR(24) NOT NULL,                   -- razorpay | manual | bank_transfer | upi | wallet | cod
  `status` VARCHAR(24) NOT NULL DEFAULT 'pending', -- pending | processing | processed | failed | cancelled
  `provider_payment_id` VARCHAR(150) NULL,
  `provider_refund_id` VARCHAR(150) NULL,
  `reason` VARCHAR(500) NULL,
  `failure_message` VARCHAR(500) NULL,
  `notes_json` LONGTEXT NULL,
  `processed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  INDEX `idx_refunds_order` (`order_id`),
  INDEX `idx_refunds_user` (`user_id`),
  INDEX `idx_refunds_status` (`status`),
  CONSTRAINT `fk_refunds_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_refunds_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add cancelled_reason to orders (idempotent)
=======
-- Cancellation metadata. The refunds table already exists from migration 0005.
SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

>>>>>>> origin/main
SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'cancelled_reason'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `orders` ADD COLUMN `cancelled_reason` VARCHAR(500) NULL AFTER `cancelled_at`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

<<<<<<< HEAD
-- 3. Add payment_method to orders (idempotent) — so refund branch doesn't guess
SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'payment_method'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `orders` ADD COLUMN `payment_method` VARCHAR(24) NULL AFTER `payment_status`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET SQL_MODE = @OLD_SQL_MODE;
=======
SET SQL_MODE = @OLD_SQL_MODE;
>>>>>>> origin/main
