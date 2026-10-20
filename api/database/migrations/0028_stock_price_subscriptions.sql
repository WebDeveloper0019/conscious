-- Migration 0028: Back-in-stock + price-drop subscriptions
-- Idempotent.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

CREATE TABLE IF NOT EXISTS `stock_price_subscriptions` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `user_id` CHAR(36) NOT NULL,
  `product_id` VARCHAR(100) NOT NULL,
  `variant_id` CHAR(36) NULL,
  `subscription_type` VARCHAR(20) NOT NULL,
      -- back_in_stock | price_drop
  `target_price` DECIMAL(12,2) NULL,
      -- for price_drop: notify when price goes at/below this
  `original_price` DECIMAL(12,2) NULL,
      -- snapshot at subscription time
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
      -- active | notified | cancelled | expired
  `last_notified_at` DATETIME NULL,
  `notify_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_sub` (`user_id`, `product_id`, `variant_id`, `subscription_type`),
  INDEX `idx_sub_active` (`status`, `subscription_type`),
  INDEX `idx_sub_product` (`product_id`, `status`),
  INDEX `idx_sub_user` (`user_id`, `status`),
  CONSTRAINT `fk_sub_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0028 applied' AS status;
