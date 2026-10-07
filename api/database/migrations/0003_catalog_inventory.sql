CREATE TABLE IF NOT EXISTS `categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `parent_id` BIGINT UNSIGNED NULL,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(140) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_category_slug` (`slug`),
  CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `vendor_id` CHAR(36) NOT NULL,
  `category_id` BIGINT UNSIGNED NULL,
  `subcategory_id` BIGINT UNSIGNED NULL,
  `name` VARCHAR(220) NOT NULL,
  `slug` VARCHAR(240) NOT NULL,
  `sku` VARCHAR(100) NOT NULL,
  `short_description` VARCHAR(500) NULL,
  `description` TEXT NOT NULL,
  `mrp` DECIMAL(12,2) NOT NULL,
  `selling_price` DECIMAL(12,2) NOT NULL,
  `gst_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `material` VARCHAR(500) NULL,
  `craft_technique` VARCHAR(300) NULL,
  `dimensions` VARCHAR(255) NULL,
  `origin_state` VARCHAR(80) NULL,
  `gi_tag` VARCHAR(180) NULL,
  `care_instructions` TEXT NULL,
  `tags_json` JSON NULL,
  `package_weight_grams` INT UNSIGNED NOT NULL,
  `package_length_cm` DECIMAL(8,2) NULL,
  `package_width_cm` DECIMAL(8,2) NULL,
  `package_height_cm` DECIMAL(8,2) NULL,
  `processing_min_days` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `processing_max_days` SMALLINT UNSIGNED NOT NULL DEFAULT 5,
  `status` VARCHAR(32) NOT NULL DEFAULT 'draft',
  `review_version` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `rejection_reason` VARCHAR(1000) NULL,
  `published_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_vendor_sku` (`vendor_id`,`sku`),
  UNIQUE KEY `uniq_product_slug` (`slug`),
  CONSTRAINT `fk_product_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_product_subcategory` FOREIGN KEY (`subcategory_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_product_marketplace` (`status`,`category_id`,`published_at`),
  INDEX `idx_product_vendor` (`vendor_id`,`status`,`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_versions` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `product_id` CHAR(36) NOT NULL,
  `version_number` SMALLINT UNSIGNED NOT NULL,
  `snapshot_json` JSON NOT NULL,
  `review_status` VARCHAR(32) NOT NULL DEFAULT 'draft',
  `submitted_at` DATETIME NULL,
  `reviewed_at` DATETIME NULL,
  `reviewed_by` CHAR(36) NULL,
  `review_notes` VARCHAR(1000) NULL,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_product_version` (`product_id`,`version_number`),
  CONSTRAINT `fk_product_version_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_images` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `product_id` CHAR(36) NOT NULL,
  `file_asset_id` CHAR(36) NOT NULL,
  `alt_text` VARCHAR(220) NOT NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_product_image_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_image_file` FOREIGN KEY (`file_asset_id`) REFERENCES `file_assets`(`id`),
  INDEX `idx_product_image_order` (`product_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `product_id` CHAR(36) NOT NULL,
  `sku` VARCHAR(100) NOT NULL,
  `name` VARCHAR(180) NOT NULL,
  `attributes_json` JSON NULL,
  `price_adjustment` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `weight_grams` INT UNSIGNED NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_variant_sku` (`product_id`,`sku`),
  CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_levels` (
  `variant_id` CHAR(36) NOT NULL PRIMARY KEY,
  `stock_quantity` INT UNSIGNED NOT NULL DEFAULT 0,
  `reserved_quantity` INT UNSIGNED NOT NULL DEFAULT 0,
  `low_stock_threshold` INT UNSIGNED NOT NULL DEFAULT 5,
  `updated_at` DATETIME NOT NULL,
  CONSTRAINT `fk_inventory_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_movements` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `variant_id` CHAR(36) NOT NULL,
  `vendor_id` CHAR(36) NOT NULL,
  `movement_type` VARCHAR(32) NOT NULL,
  `quantity_delta` INT NOT NULL,
  `reserved_delta` INT NOT NULL DEFAULT 0,
  `stock_after` INT UNSIGNED NOT NULL,
  `reserved_after` INT UNSIGNED NOT NULL,
  `reference_type` VARCHAR(40) NULL,
  `reference_id` VARCHAR(80) NULL,
  `note` VARCHAR(500) NULL,
  `actor_type` VARCHAR(24) NOT NULL,
  `actor_id` CHAR(36) NOT NULL,
  `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_inventory_movement_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_inventory_movement_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE RESTRICT,
  INDEX `idx_inventory_history` (`variant_id`,`created_at`),
  INDEX `idx_inventory_reference` (`reference_type`,`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sustainability_claims` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `vendor_id` CHAR(36) NOT NULL,
  `product_id` CHAR(36) NULL,
  `claim_type` VARCHAR(80) NOT NULL,
  `scope` VARCHAR(180) NOT NULL,
  `claim_statement` VARCHAR(600) NOT NULL,
  `evidence_type` VARCHAR(120) NOT NULL,
  `issuer` VARCHAR(180) NOT NULL,
  `reference_number` VARCHAR(120) NULL,
  `valid_until` DATE NOT NULL,
  `limitations` VARCHAR(600) NOT NULL,
  `evidence_file_id` CHAR(36) NOT NULL,
  `review_status` VARCHAR(32) NOT NULL DEFAULT 'draft',
  `public_status` VARCHAR(24) NOT NULL DEFAULT 'hidden',
  `reviewed_by` CHAR(36) NULL,
  `reviewed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_claim_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_claim_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_claim_file` FOREIGN KEY (`evidence_file_id`) REFERENCES `file_assets`(`id`),
  INDEX `idx_claim_review` (`review_status`,`valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_certificates` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `vendor_id` CHAR(36) NOT NULL,
  `certificate_type` VARCHAR(100) NOT NULL,
  `issuer` VARCHAR(180) NOT NULL,
  `reference_number` VARCHAR(120) NULL,
  `valid_from` DATE NULL,
  `valid_until` DATE NULL,
  `file_asset_id` CHAR(36) NOT NULL,
  `verification_status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_certificate_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_certificate_file` FOREIGN KEY (`file_asset_id`) REFERENCES `file_assets`(`id`),
  INDEX `idx_certificate_vendor` (`vendor_id`,`verification_status`,`valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
