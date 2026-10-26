CREATE TABLE IF NOT EXISTS `influencer_service_orders` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `order_number` VARCHAR(44) NOT NULL,
  `vendor_id` CHAR(36) NOT NULL,
  `influencer_id` CHAR(36) NOT NULL,
  `package_id` CHAR(36) NOT NULL,
  `brief` VARCHAR(5000) NOT NULL,
  `contact_name` VARCHAR(140) NOT NULL,
  `contact_email` VARCHAR(190) NOT NULL,
  `contact_mobile` VARCHAR(20) NULL,
  `target_products_json` JSON NULL,
  `requested_dates_json` JSON NULL,
  `currency` CHAR(3) NOT NULL DEFAULT 'INR',
  `base_amount` DECIMAL(12,2) NOT NULL,
  `gst_rate` DECIMAL(5,2) NOT NULL,
  `gst_amount` DECIMAL(12,2) NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `influencer_earning_amount` DECIMAL(12,2) NOT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'pending_payment',
  `payment_status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `reservation_expires_at` DATETIME NOT NULL,
  `cancellation_reason` VARCHAR(1000) NULL,
  `dispute_reason` VARCHAR(2000) NULL,
  `paid_at` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `cancelled_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_influencer_service_order_number` (`order_number`),
  INDEX `idx_service_order_vendor` (`vendor_id`,`status`,`created_at`),
  INDEX `idx_service_order_influencer` (`influencer_id`,`status`,`created_at`),
  INDEX `idx_service_order_capacity` (`package_id`,`status`,`reservation_expires_at`),
  CONSTRAINT `fk_service_order_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_service_order_influencer` FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_service_order_package` FOREIGN KEY (`package_id`) REFERENCES `influencer_packages`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `influencer_service_order_snapshots` (
  `order_id` CHAR(36) NOT NULL PRIMARY KEY,
  `package_title` VARCHAR(160) NOT NULL,
  `package_description` VARCHAR(3000) NOT NULL,
  `deliverables_json` JSON NOT NULL,
  `platforms_json` JSON NOT NULL,
  `turnaround_days` SMALLINT UNSIGNED NOT NULL,
  `revisions` TINYINT UNSIGNED NOT NULL,
  `usage_rights` VARCHAR(1000) NOT NULL,
  `vendor_requirements` VARCHAR(1000) NOT NULL,
  `tax_treatment` VARCHAR(160) NOT NULL,
  `cancellation_terms` VARCHAR(1000) NOT NULL,
  `base_amount` DECIMAL(12,2) NOT NULL,
  `gst_rate` DECIMAL(5,2) NOT NULL,
  `gst_amount` DECIMAL(12,2) NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_service_snapshot_order` FOREIGN KEY (`order_id`) REFERENCES `influencer_service_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `influencer_service_payments` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `order_id` CHAR(36) NOT NULL,
  `provider` VARCHAR(24) NOT NULL DEFAULT 'razorpay',
  `provider_order_id` VARCHAR(180) NULL,
  `provider_payment_id` VARCHAR(180) NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `currency` CHAR(3) NOT NULL DEFAULT 'INR',
  `status` VARCHAR(24) NOT NULL DEFAULT 'created',
  `failure_message` VARCHAR(1000) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `captured_at` DATETIME NULL,
  UNIQUE KEY `uniq_service_payment_order` (`order_id`),
  UNIQUE KEY `uniq_service_provider_order` (`provider`,`provider_order_id`),
  UNIQUE KEY `uniq_service_provider_payment` (`provider`,`provider_payment_id`),
  INDEX `idx_service_payment_status` (`status`,`created_at`),
  CONSTRAINT `fk_service_payment_order` FOREIGN KEY (`order_id`) REFERENCES `influencer_service_orders`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `influencer_service_order_history` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `order_id` CHAR(36) NOT NULL,
  `from_status` VARCHAR(32) NULL,
  `to_status` VARCHAR(32) NOT NULL,
  `note` VARCHAR(1000) NULL,
  `actor_type` VARCHAR(24) NOT NULL,
  `actor_id` CHAR(36) NOT NULL,
  `created_at` DATETIME NOT NULL,
  INDEX `idx_service_history_order` (`order_id`,`created_at`),
  CONSTRAINT `fk_service_history_order` FOREIGN KEY (`order_id`) REFERENCES `influencer_service_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `influencer_service_refunds` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `order_id` CHAR(36) NOT NULL,
  `payment_id` CHAR(36) NOT NULL,
  `request_key` VARCHAR(120) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `currency` CHAR(3) NOT NULL DEFAULT 'INR',
  `reason` VARCHAR(1000) NOT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'queued',
  `provider_refund_id` VARCHAR(180) NULL,
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `max_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `next_attempt_at` DATETIME NOT NULL,
  `locked_by` VARCHAR(120) NULL,
  `locked_until` DATETIME NULL,
  `failure_message` VARCHAR(2000) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `processed_at` DATETIME NULL,
  UNIQUE KEY `uniq_service_refund_request` (`request_key`),
  UNIQUE KEY `uniq_service_provider_refund` (`provider_refund_id`),
  INDEX `idx_service_refund_queue` (`status`,`next_attempt_at`,`locked_until`),
  CONSTRAINT `fk_service_refund_order` FOREIGN KEY (`order_id`) REFERENCES `influencer_service_orders`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_service_refund_payment` FOREIGN KEY (`payment_id`) REFERENCES `influencer_service_payments`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `influencer_service_disputes` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `order_id` CHAR(36) NOT NULL,
  `opened_by_type` VARCHAR(24) NOT NULL,
  `opened_by_id` CHAR(36) NOT NULL,
  `reason` VARCHAR(2000) NOT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'open',
  `resolution_note` VARCHAR(2000) NULL,
  `resolved_by` CHAR(36) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `resolved_at` DATETIME NULL,
  INDEX `idx_service_dispute_queue` (`status`,`created_at`),
  INDEX `idx_service_dispute_order` (`order_id`,`status`),
  CONSTRAINT `fk_service_dispute_order` FOREIGN KEY (`order_id`) REFERENCES `influencer_service_orders`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `influencer_service_earnings` (
  `order_id` CHAR(36) NOT NULL PRIMARY KEY,
  `influencer_id` CHAR(36) NOT NULL,
  `ledger_entry_id` CHAR(36) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_service_earning_ledger` (`ledger_entry_id`),
  CONSTRAINT `fk_service_earning_order` FOREIGN KEY (`order_id`) REFERENCES `influencer_service_orders`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_service_earning_influencer` FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_service_earning_ledger` FOREIGN KEY (`ledger_entry_id`) REFERENCES `influencer_ledger_entries`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `permissions` (`code`,`description`) VALUES
('influencer_orders.manage','Manage influencer service orders, coordination, disputes and refunds');

INSERT IGNORE INTO `role_permissions` (`role_id`,`permission_id`)
SELECT r.id,p.id FROM roles r CROSS JOIN permissions p
WHERE r.code='admin_super' AND p.code='influencer_orders.manage';
