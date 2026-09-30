CREATE TABLE IF NOT EXISTS `vendors` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `vendor_type` VARCHAR(16) NOT NULL,
  `owner_name` VARCHAR(100) NOT NULL,
  `display_name` VARCHAR(140) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `mobile` VARCHAR(10) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `otp_hash` VARCHAR(255) NULL,
  `otp_expires_at` DATETIME NULL,
  `otp_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `otp_last_sent_at` DATETIME NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `verification_status` VARCHAR(32) NOT NULL DEFAULT 'not_submitted',
  `onboarding_status` VARCHAR(32) NOT NULL DEFAULT 'not_started',
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_vendor_email` (`email`),
  UNIQUE KEY `uniq_vendor_mobile` (`mobile`),
  INDEX `idx_vendor_status` (`status`,`verification_status`,`vendor_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_applications` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `vendor_id` CHAR(36) NOT NULL,
  `reference` VARCHAR(32) NOT NULL,
  `version` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `status` VARCHAR(32) NOT NULL DEFAULT 'draft',
  `completion_percent` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `submitted_at` DATETIME NULL,
  `review_started_at` DATETIME NULL,
  `reviewed_at` DATETIME NULL,
  `reviewed_by` CHAR(36) NULL,
  `decision_reason` VARCHAR(1000) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_vendor_application_reference` (`reference`),
  UNIQUE KEY `uniq_vendor_application_version` (`vendor_id`,`version`),
  CONSTRAINT `fk_vendor_application_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  INDEX `idx_vendor_application_queue` (`status`,`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_onboarding_sections` (
  `application_id` CHAR(36) NOT NULL,
  `section_code` VARCHAR(40) NOT NULL,
  `data_json` JSON NOT NULL,
  `is_complete` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`application_id`,`section_code`),
  CONSTRAINT `fk_onboarding_section_application` FOREIGN KEY (`application_id`) REFERENCES `vendor_applications`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_profiles` (
  `vendor_id` CHAR(36) NOT NULL PRIMARY KEY,
  `legal_name` VARCHAR(180) NULL,
  `public_name` VARCHAR(180) NULL,
  `primary_category` VARCHAR(100) NULL,
  `story` TEXT NULL,
  `established_year` SMALLINT UNSIGNED NULL,
  `team_size` INT UNSIGNED NULL,
  `preferred_language` VARCHAR(40) NULL,
  `instagram_url` VARCHAR(500) NULL,
  `facebook_url` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_vendor_profile_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_artisan_profiles` (
  `vendor_id` CHAR(36) NOT NULL PRIMARY KEY,
  `operating_model` VARCHAR(60) NULL,
  `craft_tradition` VARCHAR(180) NULL,
  `years_practising` SMALLINT UNSIGNED NULL,
  `maker_count` INT UNSIGNED NULL,
  `artisan_card_reference` VARCHAR(100) NULL,
  CONSTRAINT `fk_artisan_profile_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_brand_profiles` (
  `vendor_id` CHAR(36) NOT NULL PRIMARY KEY,
  `legal_structure` VARCHAR(60) NULL,
  `registration_number` VARCHAR(100) NULL,
  `representative_name` VARCHAR(120) NULL,
  `representative_designation` VARCHAR(100) NULL,
  CONSTRAINT `fk_brand_profile_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_addresses` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `vendor_id` CHAR(36) NOT NULL,
  `address_type` VARCHAR(24) NOT NULL,
  `address_line1` VARCHAR(180) NOT NULL,
  `address_line2` VARCHAR(180) NULL,
  `city` VARCHAR(80) NOT NULL,
  `district` VARCHAR(80) NULL,
  `state` VARCHAR(80) NOT NULL,
  `pincode` CHAR(6) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_vendor_address_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  INDEX `idx_vendor_address` (`vendor_id`,`address_type`,`is_primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_documents` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `vendor_id` CHAR(36) NOT NULL,
  `application_id` CHAR(36) NULL,
  `document_type` VARCHAR(80) NOT NULL,
  `file_asset_id` CHAR(36) NOT NULL,
  `reference_number_masked` VARCHAR(80) NULL,
  `verification_status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `verified_by` CHAR(36) NULL,
  `verified_at` DATETIME NULL,
  `rejection_reason` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_vendor_document_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vendor_document_application` FOREIGN KEY (`application_id`) REFERENCES `vendor_applications`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vendor_document_file` FOREIGN KEY (`file_asset_id`) REFERENCES `file_assets`(`id`),
  INDEX `idx_vendor_document` (`vendor_id`,`document_type`,`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_verifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `application_id` CHAR(36) NOT NULL,
  `reviewer_id` CHAR(36) NOT NULL,
  `decision` VARCHAR(32) NOT NULL,
  `notes` VARCHAR(2000) NULL,
  `requirements_json` JSON NULL,
  `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_vendor_verification_application` FOREIGN KEY (`application_id`) REFERENCES `vendor_applications`(`id`) ON DELETE CASCADE,
  INDEX `idx_vendor_verification_application` (`application_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_store_profiles` (
  `vendor_id` CHAR(36) NOT NULL PRIMARY KEY,
  `store_name` VARCHAR(160) NULL,
  `store_slug` VARCHAR(180) NULL,
  `tagline` VARCHAR(180) NULL,
  `description` TEXT NULL,
  `support_email` VARCHAR(190) NULL,
  `support_mobile` VARCHAR(10) NULL,
  `logo_file_id` CHAR(36) NULL,
  `banner_file_id` CHAR(36) NULL,
  `primary_color` CHAR(7) NULL,
  `secondary_color` CHAR(7) NULL,
  `video_url` VARCHAR(500) NULL,
  `is_public` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_vendor_store_slug` (`store_slug`),
  CONSTRAINT `fk_vendor_store_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_bank_accounts` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `vendor_id` CHAR(36) NOT NULL,
  `beneficiary_name` VARCHAR(150) NOT NULL,
  `account_number_encrypted` TEXT NOT NULL,
  `account_last4` CHAR(4) NOT NULL,
  `ifsc_encrypted` TEXT NOT NULL,
  `bank_name` VARCHAR(150) NULL,
  `verification_status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_vendor_bank_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  INDEX `idx_vendor_bank` (`vendor_id`,`is_primary`,`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_tax_profiles` (
  `vendor_id` CHAR(36) NOT NULL PRIMARY KEY,
  `pan_encrypted` TEXT NOT NULL,
  `pan_last4` CHAR(4) NOT NULL,
  `gst_status` VARCHAR(24) NOT NULL,
  `gstin_encrypted` TEXT NULL,
  `gstin_last4` CHAR(4) NULL,
  `udyam_encrypted` TEXT NULL,
  `verification_status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `updated_at` DATETIME NOT NULL,
  CONSTRAINT `fk_vendor_tax_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_shipping_settings` (
  `vendor_id` CHAR(36) NOT NULL PRIMARY KEY,
  `coverage_model` VARCHAR(24) NOT NULL DEFAULT 'all_india',
  `processing_min_days` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `processing_max_days` SMALLINT UNSIGNED NOT NULL DEFAULT 5,
  `courier_partners` VARCHAR(500) NULL,
  `base_charge` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `free_above` DECIMAL(12,2) NULL,
  `cod_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `fragile_handling` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_vendor_shipping_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_shipping_zones` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `vendor_id` CHAR(36) NOT NULL,
  `zone_name` VARCHAR(120) NOT NULL,
  `rule_type` VARCHAR(24) NOT NULL,
  `country_code` CHAR(2) NOT NULL DEFAULT 'IN',
  `state` VARCHAR(80) NULL,
  `city` VARCHAR(100) NULL,
  `pincode_from` CHAR(6) NULL,
  `pincode_to` CHAR(6) NULL,
  `is_exclusion` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_vendor_shipping_zone_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  INDEX `idx_shipping_zone_lookup` (`vendor_id`,`is_active`,`is_exclusion`,`state`,`city`,`pincode_from`,`pincode_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_shipping_rates` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `vendor_id` CHAR(36) NOT NULL,
  `zone_id` CHAR(36) NULL,
  `calculation_type` VARCHAR(24) NOT NULL,
  `min_weight_grams` INT UNSIGNED NULL,
  `max_weight_grams` INT UNSIGNED NULL,
  `min_order_value` DECIMAL(12,2) NULL,
  `max_order_value` DECIMAL(12,2) NULL,
  `charge` DECIMAL(12,2) NOT NULL,
  `additional_unit_charge` DECIMAL(12,2) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_vendor_shipping_rate_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vendor_shipping_rate_zone` FOREIGN KEY (`zone_id`) REFERENCES `vendor_shipping_zones`(`id`) ON DELETE CASCADE,
  INDEX `idx_shipping_rate` (`vendor_id`,`zone_id`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vendor_settings` (
  `vendor_id` CHAR(36) NOT NULL PRIMARY KEY,
  `return_policy` TEXT NULL,
  `cancellation_policy` TEXT NULL,
  `damage_policy` TEXT NULL,
  `handmade_policy` TEXT NULL,
  `materials_sourcing` TEXT NULL,
  `packaging_approach` VARCHAR(80) NULL,
  `maker_payment_model` VARCHAR(80) NULL,
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_vendor_settings_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'active',
  `mfa_secret_encrypted` TEXT NULL,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_mfa_challenges` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `admin_id` CHAR(36) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_admin_mfa_token` (`token_hash`),
  CONSTRAINT `fk_admin_mfa_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `permissions` (`code`,`description`) VALUES
('vendor_applications.view','View vendor applications'),('vendor_applications.review','Review vendor applications'),('vendor_applications.approve','Approve or reject vendor applications'),
('vendors.manage','Suspend, reactivate and manage vendors'),('products.review','Review vendor products'),('orders.oversee','Oversee multi-vendor orders'),
('returns.manage','Manage returns and refunds'),('payouts.review','Review and process payouts'),('events.manage','Manage events and bookings'),
('influencers.manage','Manage influencers'),('support.manage','Manage support queues'),('content.manage','Manage CMS content'),
('reports.view','View platform reports'),('audit.view','View audit records'),('access.manage','Manage roles and permissions');

INSERT IGNORE INTO `role_permissions` (`role_id`,`permission_id`)
SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE r.code='admin_super';
