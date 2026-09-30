CREATE TABLE `carts` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `user_id` CHAR(36) NOT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'active', `currency` CHAR(3) NOT NULL DEFAULT 'INR',
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_active_user_cart` (`user_id`,`status`),
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cart_items` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `cart_id` CHAR(36) NOT NULL,
  `variant_id` CHAR(36) NOT NULL, `quantity` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_cart_variant` (`cart_id`,`variant_id`),
  CONSTRAINT `fk_cart_item_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_item_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `saved_vendors` (
  `user_id` CHAR(36) NOT NULL, `vendor_id` CHAR(36) NOT NULL, `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`user_id`,`vendor_id`),
  CONSTRAINT `fk_saved_vendor_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_saved_vendor_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shipping_quotes` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `user_id` CHAR(36) NOT NULL, `vendor_id` CHAR(36) NOT NULL,
  `address_id` CHAR(36) NOT NULL, `amount` DECIMAL(12,2) NOT NULL, `rule_snapshot_json` JSON NOT NULL,
  `serviceable` TINYINT(1) NOT NULL, `reason` VARCHAR(300) NULL, `expires_at` DATETIME NOT NULL, `created_at` DATETIME NOT NULL,
  INDEX `idx_shipping_quote_user` (`user_id`,`expires_at`),
  CONSTRAINT `fk_shipping_quote_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shipping_quote_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_shipping_quote_address` FOREIGN KEY (`address_id`) REFERENCES `user_addresses`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `coupons` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `vendor_id` CHAR(36) NULL, `code` VARCHAR(40) NOT NULL,
  `discount_type` VARCHAR(20) NOT NULL, `discount_value` DECIMAL(12,2) NOT NULL,
  `min_order_amount` DECIMAL(12,2) NOT NULL DEFAULT 0, `max_discount_amount` DECIMAL(12,2) NULL,
  `usage_limit` INT UNSIGNED NULL, `per_customer_limit` INT UNSIGNED NOT NULL DEFAULT 1,
  `starts_at` DATETIME NOT NULL, `ends_at` DATETIME NOT NULL, `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_coupon_code` (`code`), INDEX `idx_coupon_active` (`status`,`starts_at`,`ends_at`),
  CONSTRAINT `fk_coupon_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `checkout_quotes` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `user_id` CHAR(36) NOT NULL, `cart_id` CHAR(36) NOT NULL,
  `address_id` CHAR(36) NOT NULL, `coupon_id` CHAR(36) NULL, `pricing_json` JSON NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL, `shipping_amount` DECIMAL(12,2) NOT NULL,
  `discount_amount` DECIMAL(12,2) NOT NULL, `tax_amount` DECIMAL(12,2) NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL, `currency` CHAR(3) NOT NULL DEFAULT 'INR',
  `status` VARCHAR(20) NOT NULL DEFAULT 'open', `expires_at` DATETIME NOT NULL, `created_at` DATETIME NOT NULL,
  INDEX `idx_checkout_quote_user` (`user_id`,`status`,`expires_at`),
  CONSTRAINT `fk_checkout_quote_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_checkout_quote_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_checkout_quote_address` FOREIGN KEY (`address_id`) REFERENCES `user_addresses`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_checkout_quote_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `orders`
  ADD COLUMN `checkout_quote_id` CHAR(36) NULL AFTER `user_id`,
  ADD COLUMN `payment_status` VARCHAR(24) NOT NULL DEFAULT 'pending' AFTER `status`,
  ADD COLUMN `currency` CHAR(3) NOT NULL DEFAULT 'INR' AFTER `payment_status`,
  ADD COLUMN `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `discount_amount`,
  ADD COLUMN `address_snapshot_json` JSON NULL AFTER `total_amount`,
  ADD COLUMN `placed_at` DATETIME NULL AFTER `address_snapshot_json`,
  ADD COLUMN `cancelled_at` DATETIME NULL AFTER `placed_at`,
  ADD CONSTRAINT `fk_order_quote` FOREIGN KEY (`checkout_quote_id`) REFERENCES `checkout_quotes`(`id`) ON DELETE SET NULL;

CREATE TABLE `vendor_order_groups` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `order_id` BIGINT UNSIGNED NOT NULL, `vendor_id` CHAR(36) NOT NULL,
  `group_number` VARCHAR(48) NOT NULL, `status` VARCHAR(32) NOT NULL DEFAULT 'awaiting_acceptance',
  `subtotal` DECIMAL(12,2) NOT NULL, `shipping_amount` DECIMAL(12,2) NOT NULL,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0, `tax_amount` DECIMAL(12,2) NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL, `policy_snapshot_json` JSON NOT NULL,
  `accepted_at` DATETIME NULL, `dispatch_due_at` DATETIME NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_vendor_group_number` (`group_number`), UNIQUE KEY `uniq_order_vendor` (`order_id`,`vendor_id`),
  INDEX `idx_vendor_group_queue` (`vendor_id`,`status`,`created_at`),
  CONSTRAINT `fk_vendor_group_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vendor_group_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `order_items`
  ADD COLUMN `vendor_group_id` CHAR(36) NULL AFTER `order_id`,
  ADD COLUMN `vendor_id` CHAR(36) NULL AFTER `vendor_group_id`,
  ADD COLUMN `variant_id` CHAR(36) NULL AFTER `product_id`,
  ADD COLUMN `sku` VARCHAR(100) NULL AFTER `product_name`,
  ADD COLUMN `status` VARCHAR(32) NOT NULL DEFAULT 'awaiting_acceptance' AFTER `quantity`,
  ADD COLUMN `mrp` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `unit_price`,
  ADD COLUMN `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `mrp`,
  ADD COLUMN `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `discount_amount`,
  ADD COLUMN `line_total` DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER `tax_amount`,
  ADD COLUMN `product_snapshot_json` JSON NULL AFTER `image`,
  ADD COLUMN `policy_snapshot_json` JSON NULL AFTER `product_snapshot_json`,
  ADD COLUMN `cancelled_quantity` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `policy_snapshot_json`,
  ADD COLUMN `returned_quantity` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `cancelled_quantity`,
  ADD CONSTRAINT `fk_order_item_group` FOREIGN KEY (`vendor_group_id`) REFERENCES `vendor_order_groups`(`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_item_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_order_item_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE RESTRICT;

CREATE TABLE `inventory_reservations` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `quote_id` CHAR(36) NOT NULL, `variant_id` CHAR(36) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL, `status` VARCHAR(20) NOT NULL DEFAULT 'reserved',
  `expires_at` DATETIME NOT NULL, `created_at` DATETIME NOT NULL, `released_at` DATETIME NULL,
  UNIQUE KEY `uniq_quote_variant_reservation` (`quote_id`,`variant_id`),
  CONSTRAINT `fk_reservation_quote` FOREIGN KEY (`quote_id`) REFERENCES `checkout_quotes`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reservation_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payment_attempts` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `order_id` BIGINT UNSIGNED NOT NULL, `provider` VARCHAR(40) NOT NULL,
  `provider_order_id` VARCHAR(150) NULL, `provider_payment_id` VARCHAR(150) NULL,
  `amount` DECIMAL(12,2) NOT NULL, `currency` CHAR(3) NOT NULL DEFAULT 'INR', `status` VARCHAR(24) NOT NULL,
  `failure_code` VARCHAR(100) NULL, `failure_message` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_provider_payment` (`provider`,`provider_payment_id`), INDEX `idx_payment_order` (`order_id`,`created_at`),
  CONSTRAINT `fk_payment_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shipment_packages` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `vendor_group_id` CHAR(36) NOT NULL,
  `package_number` VARCHAR(52) NOT NULL, `status` VARCHAR(32) NOT NULL DEFAULT 'packing',
  `courier_name` VARCHAR(120) NULL, `tracking_reference` VARCHAR(180) NULL, `tracking_url` VARCHAR(500) NULL,
  `vendor_shipping_cost` DECIMAL(12,2) NULL, `weight_grams` INT UNSIGNED NULL,
  `length_cm` DECIMAL(8,2) NULL, `width_cm` DECIMAL(8,2) NULL, `height_cm` DECIMAL(8,2) NULL,
  `expected_delivery_date` DATE NULL, `dispatched_at` DATETIME NULL, `delivered_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_package_number` (`package_number`), INDEX `idx_package_group` (`vendor_group_id`,`status`),
  CONSTRAINT `fk_package_group` FOREIGN KEY (`vendor_group_id`) REFERENCES `vendor_order_groups`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shipment_package_items` (
  `package_id` CHAR(36) NOT NULL, `order_item_id` BIGINT UNSIGNED NOT NULL, `quantity` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`package_id`,`order_item_id`),
  CONSTRAINT `fk_package_item_package` FOREIGN KEY (`package_id`) REFERENCES `shipment_packages`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_package_item_order_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shipment_events` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `package_id` CHAR(36) NOT NULL,
  `status` VARCHAR(32) NOT NULL, `event_at` DATETIME NOT NULL, `location` VARCHAR(180) NULL,
  `note` VARCHAR(600) NULL, `evidence_file_id` CHAR(36) NULL,
  `actor_type` VARCHAR(24) NOT NULL, `actor_id` CHAR(36) NOT NULL, `created_at` DATETIME NOT NULL,
  INDEX `idx_shipment_timeline` (`package_id`,`event_at`),
  CONSTRAINT `fk_shipment_event_package` FOREIGN KEY (`package_id`) REFERENCES `shipment_packages`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shipment_event_file` FOREIGN KEY (`evidence_file_id`) REFERENCES `file_assets`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `delivery_instructions` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `order_id` BIGINT UNSIGNED NOT NULL, `user_id` CHAR(36) NOT NULL,
  `instruction` VARCHAR(500) NOT NULL, `vendor_confirmation_requested` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  CONSTRAINT `fk_delivery_instruction_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_delivery_instruction_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
