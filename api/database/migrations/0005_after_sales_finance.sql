CREATE TABLE `return_requests` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `order_id` BIGINT UNSIGNED NOT NULL, `order_item_id` BIGINT UNSIGNED NOT NULL,
  `user_id` CHAR(36) NOT NULL, `vendor_id` CHAR(36) NOT NULL, `request_type` VARCHAR(24) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL, `reason_code` VARCHAR(60) NOT NULL, `description` VARCHAR(1200) NOT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'requested', `resolution` VARCHAR(32) NULL,
  `policy_snapshot_json` JSON NOT NULL, `vendor_note` VARCHAR(1000) NULL, `admin_note` VARCHAR(1000) NULL,
  `requested_at` DATETIME NOT NULL, `reviewed_at` DATETIME NULL, `completed_at` DATETIME NULL, `updated_at` DATETIME NULL,
  INDEX `idx_return_user` (`user_id`,`status`,`requested_at`), INDEX `idx_return_vendor` (`vendor_id`,`status`,`requested_at`),
  CONSTRAINT `fk_return_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_return_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_return_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_return_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `return_evidence` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `return_request_id` CHAR(36) NOT NULL, `file_asset_id` CHAR(36) NOT NULL,
  `uploaded_by_type` VARCHAR(24) NOT NULL, `uploaded_by_id` CHAR(36) NOT NULL, `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_return_evidence_request` FOREIGN KEY (`return_request_id`) REFERENCES `return_requests`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_return_evidence_file` FOREIGN KEY (`file_asset_id`) REFERENCES `file_assets`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `refunds` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `return_request_id` CHAR(36) NULL, `order_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL, `status` VARCHAR(24) NOT NULL DEFAULT 'recommended',
  `provider_reference` VARCHAR(180) NULL, `reason` VARCHAR(500) NOT NULL,
  `recommended_by_type` VARCHAR(24) NOT NULL, `recommended_by_id` CHAR(36) NOT NULL,
  `approved_by` CHAR(36) NULL, `approved_at` DATETIME NULL, `processed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  INDEX `idx_refund_order` (`order_id`,`status`),
  CONSTRAINT `fk_refund_return` FOREIGN KEY (`return_request_id`) REFERENCES `return_requests`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_refund_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `commission_rules` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `vendor_id` CHAR(36) NULL, `category_id` BIGINT UNSIGNED NULL,
  `commission_percent` DECIMAL(5,2) NOT NULL, `fixed_fee` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `effective_from` DATETIME NOT NULL, `effective_until` DATETIME NULL, `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `created_by` CHAR(36) NOT NULL, `created_at` DATETIME NOT NULL,
  INDEX `idx_commission_effective` (`vendor_id`,`category_id`,`status`,`effective_from`),
  CONSTRAINT `fk_commission_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_commission_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_item_financials` (
  `order_item_id` BIGINT UNSIGNED NOT NULL PRIMARY KEY, `gross_amount` DECIMAL(12,2) NOT NULL,
  `discount_amount` DECIMAL(12,2) NOT NULL, `tax_amount` DECIMAL(12,2) NOT NULL,
  `commission_percent` DECIMAL(5,2) NOT NULL, `commission_amount` DECIMAL(12,2) NOT NULL,
  `fixed_fee` DECIMAL(12,2) NOT NULL, `vendor_net_amount` DECIMAL(12,2) NOT NULL,
  `snapshot_json` JSON NOT NULL, `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_item_financial_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vendor_ledger_entries` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `vendor_id` CHAR(36) NOT NULL, `entry_type` VARCHAR(32) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL, `currency` CHAR(3) NOT NULL DEFAULT 'INR', `balance_bucket` VARCHAR(20) NOT NULL,
  `reference_type` VARCHAR(40) NOT NULL, `reference_id` VARCHAR(80) NOT NULL,
  `description` VARCHAR(500) NOT NULL, `available_at` DATETIME NULL, `created_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_vendor_ledger_reference` (`vendor_id`,`entry_type`,`reference_type`,`reference_id`),
  INDEX `idx_vendor_ledger_balance` (`vendor_id`,`balance_bucket`,`created_at`),
  CONSTRAINT `fk_ledger_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payout_requests` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `vendor_id` CHAR(36) NOT NULL, `bank_account_id` CHAR(36) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL, `status` VARCHAR(32) NOT NULL DEFAULT 'requested',
  `requested_at` DATETIME NOT NULL, `maker_admin_id` CHAR(36) NULL, `maker_at` DATETIME NULL,
  `checker_admin_id` CHAR(36) NULL, `checked_at` DATETIME NULL, `payment_reference` VARCHAR(180) NULL,
  `rejection_reason` VARCHAR(500) NULL, `paid_at` DATETIME NULL, `updated_at` DATETIME NULL,
  INDEX `idx_payout_queue` (`status`,`requested_at`), INDEX `idx_payout_vendor` (`vendor_id`,`status`,`requested_at`),
  CONSTRAINT `fk_payout_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_payout_bank` FOREIGN KEY (`bank_account_id`) REFERENCES `vendor_bank_accounts`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `coupon_redemptions` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `coupon_id` CHAR(36) NOT NULL, `user_id` CHAR(36) NOT NULL,
  `order_id` BIGINT UNSIGNED NOT NULL, `discount_amount` DECIMAL(12,2) NOT NULL, `created_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_coupon_order` (`coupon_id`,`order_id`), INDEX `idx_coupon_user` (`coupon_id`,`user_id`),
  CONSTRAINT `fk_redemption_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_redemption_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_redemption_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_reviews` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `user_id` CHAR(36) NOT NULL, `order_item_id` BIGINT UNSIGNED NOT NULL,
  `product_id` CHAR(36) NOT NULL, `vendor_id` CHAR(36) NOT NULL, `rating` TINYINT UNSIGNED NOT NULL,
  `title` VARCHAR(180) NULL, `review_text` VARCHAR(3000) NOT NULL, `status` VARCHAR(24) NOT NULL DEFAULT 'published',
  `vendor_reply` VARCHAR(2000) NULL, `replied_at` DATETIME NULL, `moderation_note` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_review_order_item` (`user_id`,`order_item_id`), INDEX `idx_review_product` (`product_id`,`status`,`created_at`),
  CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_review_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_review_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
