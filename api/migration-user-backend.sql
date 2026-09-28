-- Run this once if the existing conscious_indi database was created
-- with the original 6-column users table.
USE `conscious_indi`;

ALTER TABLE `users`
  ADD COLUMN `date_of_birth` DATE NULL,
  ADD COLUMN `gender` VARCHAR(30) NULL,
  ADD COLUMN `anniversary_date` DATE NULL,
  ADD COLUMN `updated_at` DATETIME NULL;

-- If any ADD COLUMN above reports that the column already exists, skip that
-- particular line and continue with the CREATE TABLE statements below.

CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `user_id` CHAR(36) NOT NULL,
  `label` VARCHAR(30) NOT NULL DEFAULT 'Home',
  `full_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `pincode` VARCHAR(10) NOT NULL,
  `address_line1` VARCHAR(255) NOT NULL,
  `address_line2` VARCHAR(255) NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `landmark` VARCHAR(150) NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_address_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_address_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_preferences` (
  `user_id` CHAR(36) NOT NULL PRIMARY KEY,
  `order_updates` TINYINT(1) NOT NULL DEFAULT 1,
  `offers_promotions` TINYINT(1) NOT NULL DEFAULT 1,
  `wishlist_alerts` TINYINT(1) NOT NULL DEFAULT 1,
  `newsletter` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wishlist_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` CHAR(36) NOT NULL,
  `product_id` VARCHAR(100) NOT NULL,
  `product_name` VARCHAR(255) NULL,
  `product_price` DECIMAL(12,2) NULL,
  `product_image` VARCHAR(500) NULL,
  `vendor_name` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_user_product` (`user_id`,`product_id`),
  INDEX `idx_wishlist_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` CHAR(36) NOT NULL,
  `order_number` VARCHAR(40) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'processing',
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `shipping_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_order_number` (`order_number`),
  INDEX `idx_orders_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `product_id` VARCHAR(100) NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `image` VARCHAR(500) NULL,
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  INDEX `idx_order_items_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
