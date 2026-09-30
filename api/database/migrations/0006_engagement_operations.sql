CREATE TABLE `support_tickets` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `ticket_number` VARCHAR(40) NOT NULL,
  `opened_by_type` VARCHAR(24) NOT NULL, `opened_by_id` CHAR(36) NOT NULL,
  `order_id` BIGINT UNSIGNED NULL, `vendor_id` CHAR(36) NULL, `category` VARCHAR(60) NOT NULL,
  `subject` VARCHAR(180) NOT NULL, `priority` VARCHAR(20) NOT NULL DEFAULT 'normal',
  `status` VARCHAR(24) NOT NULL DEFAULT 'open', `assigned_admin_id` CHAR(36) NULL,
  `first_response_due_at` DATETIME NULL, `resolution_due_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL, `closed_at` DATETIME NULL,
  UNIQUE KEY `uniq_ticket_number` (`ticket_number`), INDEX `idx_ticket_owner` (`opened_by_type`,`opened_by_id`,`status`),
  INDEX `idx_ticket_queue` (`status`,`priority`,`created_at`),
  CONSTRAINT `fk_ticket_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ticket_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ticket_admin` FOREIGN KEY (`assigned_admin_id`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_messages` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `ticket_id` CHAR(36) NOT NULL,
  `sender_type` VARCHAR(24) NOT NULL, `sender_id` CHAR(36) NOT NULL, `message` VARCHAR(5000) NOT NULL,
  `is_internal` TINYINT(1) NOT NULL DEFAULT 0, `created_at` DATETIME NOT NULL,
  INDEX `idx_support_message` (`ticket_id`,`created_at`),
  CONSTRAINT `fk_support_message_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_attachments` (
  `message_id` CHAR(36) NOT NULL, `file_asset_id` CHAR(36) NOT NULL,
  PRIMARY KEY (`message_id`,`file_asset_id`),
  CONSTRAINT `fk_support_attachment_message` FOREIGN KEY (`message_id`) REFERENCES `support_messages`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_support_attachment_file` FOREIGN KEY (`file_asset_id`) REFERENCES `file_assets`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `event_hosts` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `owner_type` VARCHAR(24) NOT NULL, `owner_id` CHAR(36) NOT NULL,
  `display_name` VARCHAR(180) NOT NULL, `email` VARCHAR(190) NOT NULL, `mobile` VARCHAR(10) NOT NULL,
  `bio` VARCHAR(2000) NULL, `verification_status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_event_host_owner` (`owner_type`,`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `events` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `host_id` CHAR(36) NOT NULL, `title` VARCHAR(220) NOT NULL,
  `slug` VARCHAR(240) NOT NULL, `event_type` VARCHAR(50) NOT NULL, `short_description` VARCHAR(500) NOT NULL,
  `description` TEXT NOT NULL, `venue_type` VARCHAR(20) NOT NULL, `venue_name` VARCHAR(180) NULL,
  `address_json` JSON NULL, `online_url_encrypted` TEXT NULL, `timezone` VARCHAR(60) NOT NULL DEFAULT 'Asia/Kolkata',
  `starts_at` DATETIME NOT NULL, `ends_at` DATETIME NOT NULL, `booking_opens_at` DATETIME NOT NULL,
  `booking_closes_at` DATETIME NOT NULL, `status` VARCHAR(32) NOT NULL DEFAULT 'draft',
  `review_notes` VARCHAR(1000) NULL, `published_at` DATETIME NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_event_slug` (`slug`), INDEX `idx_event_discovery` (`status`,`starts_at`,`event_type`),
  CONSTRAINT `fk_event_host` FOREIGN KEY (`host_id`) REFERENCES `event_hosts`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `event_media` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `event_id` CHAR(36) NOT NULL, `file_asset_id` CHAR(36) NOT NULL,
  `alt_text` VARCHAR(220) NOT NULL, `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0, `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_event_media_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_event_media_file` FOREIGN KEY (`file_asset_id`) REFERENCES `file_assets`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `event_ticket_types` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `event_id` CHAR(36) NOT NULL, `name` VARCHAR(120) NOT NULL,
  `description` VARCHAR(500) NULL, `price` DECIMAL(12,2) NOT NULL, `capacity` INT UNSIGNED NOT NULL,
  `reserved_quantity` INT UNSIGNED NOT NULL DEFAULT 0, `sold_quantity` INT UNSIGNED NOT NULL DEFAULT 0,
  `min_per_booking` INT UNSIGNED NOT NULL DEFAULT 1, `max_per_booking` INT UNSIGNED NOT NULL DEFAULT 10,
  `sales_start_at` DATETIME NOT NULL, `sales_end_at` DATETIME NOT NULL, `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  INDEX `idx_event_ticket` (`event_id`,`status`), CONSTRAINT `fk_event_ticket_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `event_bookings` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `booking_number` VARCHAR(44) NOT NULL, `event_id` CHAR(36) NOT NULL,
  `ticket_type_id` CHAR(36) NOT NULL, `user_id` CHAR(36) NOT NULL, `quantity` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL, `total_amount` DECIMAL(12,2) NOT NULL,
  `attendee_json` JSON NOT NULL, `status` VARCHAR(24) NOT NULL DEFAULT 'payment_pending',
  `payment_reference` VARCHAR(180) NULL, `reservation_expires_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL, `confirmed_at` DATETIME NULL, `cancelled_at` DATETIME NULL,
  UNIQUE KEY `uniq_booking_number` (`booking_number`), INDEX `idx_event_booking_user` (`user_id`,`status`,`created_at`),
  CONSTRAINT `fk_booking_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_booking_ticket` FOREIGN KEY (`ticket_type_id`) REFERENCES `event_ticket_types`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `event_vendor_applications` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `event_id` CHAR(36) NOT NULL, `vendor_id` CHAR(36) NOT NULL,
  `application_text` VARCHAR(3000) NOT NULL, `stall_requirements` VARCHAR(1500) NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'submitted', `fee_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `payment_status` VARCHAR(24) NOT NULL DEFAULT 'not_required', `submitted_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_event_vendor_application` (`event_id`,`vendor_id`),
  CONSTRAINT `fk_event_vendor_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_event_vendor_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `influencers` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `full_name` VARCHAR(140) NOT NULL, `email` VARCHAR(190) NOT NULL,
  `mobile` VARCHAR(10) NOT NULL, `password_hash` VARCHAR(255) NOT NULL, `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `otp_hash` VARCHAR(255) NULL, `otp_expires_at` DATETIME NULL, `otp_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `handle` VARCHAR(100) NOT NULL, `bio` VARCHAR(2000) NULL, `platforms_json` JSON NOT NULL,
  `audience_json` JSON NULL, `status` VARCHAR(32) NOT NULL DEFAULT 'pending_verification',
  `verification_status` VARCHAR(32) NOT NULL DEFAULT 'not_submitted', `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL, `last_login_at` DATETIME NULL,
  UNIQUE KEY `uniq_influencer_email` (`email`), UNIQUE KEY `uniq_influencer_mobile` (`mobile`), UNIQUE KEY `uniq_influencer_handle` (`handle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `influencer_campaigns` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `name` VARCHAR(180) NOT NULL, `description` VARCHAR(3000) NOT NULL,
  `commission_type` VARCHAR(20) NOT NULL, `commission_value` DECIMAL(12,2) NOT NULL,
  `attribution_days` SMALLINT UNSIGNED NOT NULL DEFAULT 30, `starts_at` DATETIME NOT NULL, `ends_at` DATETIME NOT NULL,
  `eligibility_json` JSON NULL, `disclosure_text` VARCHAR(1000) NOT NULL, `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
  `created_by` CHAR(36) NOT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  INDEX `idx_campaign_active` (`status`,`starts_at`,`ends_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `influencer_referral_links` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `influencer_id` CHAR(36) NOT NULL, `campaign_id` CHAR(36) NOT NULL,
  `code` VARCHAR(60) NOT NULL, `destination_url` VARCHAR(500) NOT NULL, `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL, UNIQUE KEY `uniq_referral_code` (`code`),
  CONSTRAINT `fk_referral_influencer` FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_referral_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `influencer_campaigns`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `referral_clicks` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `referral_link_id` CHAR(36) NOT NULL, `visitor_hash` CHAR(64) NOT NULL,
  `ip_hash` CHAR(64) NULL, `user_agent_hash` CHAR(64) NULL, `fraud_flags_json` JSON NULL, `clicked_at` DATETIME NOT NULL,
  INDEX `idx_referral_click` (`referral_link_id`,`clicked_at`),
  CONSTRAINT `fk_click_referral` FOREIGN KEY (`referral_link_id`) REFERENCES `influencer_referral_links`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `influencer_conversions` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `influencer_id` CHAR(36) NOT NULL, `campaign_id` CHAR(36) NOT NULL,
  `click_id` CHAR(36) NULL, `order_id` BIGINT UNSIGNED NOT NULL, `order_amount` DECIMAL(12,2) NOT NULL,
  `commission_amount` DECIMAL(12,2) NOT NULL, `status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `fraud_flags_json` JSON NULL, `created_at` DATETIME NOT NULL, `eligible_at` DATETIME NULL,
  UNIQUE KEY `uniq_influencer_order_campaign` (`campaign_id`,`order_id`), INDEX `idx_conversion_influencer` (`influencer_id`,`status`,`created_at`),
  CONSTRAINT `fk_conversion_influencer` FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_conversion_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `influencer_campaigns`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_conversion_click` FOREIGN KEY (`click_id`) REFERENCES `referral_clicks`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_conversion_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `influencer_ledger_entries` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `influencer_id` CHAR(36) NOT NULL, `entry_type` VARCHAR(24) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL, `status` VARCHAR(20) NOT NULL, `reference_type` VARCHAR(40) NOT NULL,
  `reference_id` VARCHAR(80) NOT NULL, `created_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_influencer_ledger_reference` (`influencer_id`,`entry_type`,`reference_type`,`reference_id`),
  CONSTRAINT `fk_influencer_ledger_owner` FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `influencer_withdrawals` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `influencer_id` CHAR(36) NOT NULL, `amount` DECIMAL(12,2) NOT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'requested', `payment_reference` VARCHAR(180) NULL,
  `requested_at` DATETIME NOT NULL, `reviewed_by` CHAR(36) NULL, `reviewed_at` DATETIME NULL,
  `paid_at` DATETIME NULL, `rejection_reason` VARCHAR(500) NULL,
  INDEX `idx_withdrawal_queue` (`status`,`requested_at`),
  CONSTRAINT `fk_withdrawal_influencer` FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cms_entries` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `entry_type` VARCHAR(40) NOT NULL, `slug` VARCHAR(180) NOT NULL,
  `title` VARCHAR(220) NOT NULL, `content_json` JSON NOT NULL, `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
  `starts_at` DATETIME NULL, `ends_at` DATETIME NULL, `created_by` CHAR(36) NOT NULL,
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL, `published_at` DATETIME NULL,
  UNIQUE KEY `uniq_cms_type_slug` (`entry_type`,`slug`), INDEX `idx_cms_public` (`entry_type`,`status`,`starts_at`,`ends_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `platform_settings` (
  `setting_key` VARCHAR(120) NOT NULL PRIMARY KEY, `value_json` JSON NOT NULL,
  `is_public` TINYINT(1) NOT NULL DEFAULT 0, `updated_by` CHAR(36) NOT NULL, `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
