CREATE DATABASE IF NOT EXISTS `conscious_indi` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `conscious_indi`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `first_name` VARCHAR(60) NOT NULL,
  `last_name` VARCHAR(60) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `mobile` VARCHAR(10) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `marketing_opt_in` TINYINT(1) NOT NULL DEFAULT 0,
  `otp_hash` VARCHAR(255) NULL,
  `otp_expires_at` DATETIME NULL,
  `otp_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `otp_last_sent_at` DATETIME NULL,
  `date_of_birth` DATE NULL,
  `gender` VARCHAR(30) NULL,
  `anniversary_date` DATE NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_users_email` (`email`),
  UNIQUE KEY `uniq_users_mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `user_id` CHAR(36) NOT NULL,
  `label` VARCHAR(30) NOT NULL DEFAULT 'Home', `full_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(10) NOT NULL, `pincode` CHAR(6) NOT NULL,
  `address_line1` VARCHAR(255) NOT NULL, `address_line2` VARCHAR(255) NULL,
  `city` VARCHAR(100) NOT NULL, `state` VARCHAR(100) NOT NULL, `landmark` VARCHAR(150) NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  CONSTRAINT `fk_address_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_address_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_preferences` (
  `user_id` CHAR(36) NOT NULL PRIMARY KEY, `order_updates` TINYINT(1) NOT NULL DEFAULT 1,
  `offers_promotions` TINYINT(1) NOT NULL DEFAULT 1, `wishlist_alerts` TINYINT(1) NOT NULL DEFAULT 1,
  `newsletter` TINYINT(1) NOT NULL DEFAULT 0, `updated_at` DATETIME NULL,
  CONSTRAINT `fk_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wishlist_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` CHAR(36) NOT NULL,
  `product_id` VARCHAR(100) NOT NULL, `product_name` VARCHAR(255) NULL, `product_price` DECIMAL(12,2) NULL,
  `product_image` VARCHAR(500) NULL, `vendor_name` VARCHAR(255) NULL, `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_user_product` (`user_id`,`product_id`), INDEX `idx_wishlist_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` CHAR(36) NOT NULL,
  `order_number` VARCHAR(40) NOT NULL, `status` VARCHAR(30) NOT NULL DEFAULT 'processing',
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0, `shipping_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0, `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_order_number` (`order_number`), INDEX `idx_orders_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `order_id` BIGINT UNSIGNED NOT NULL,
  `product_id` VARCHAR(100) NOT NULL, `product_name` VARCHAR(255) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1, `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0, `image` VARCHAR(500) NULL,
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  INDEX `idx_order_items_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
