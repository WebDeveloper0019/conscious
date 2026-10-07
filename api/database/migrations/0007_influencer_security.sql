ALTER TABLE `influencers` ADD COLUMN `otp_last_sent_at` DATETIME NULL AFTER `otp_attempts`;

CREATE TABLE `influencer_documents` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `influencer_id` CHAR(36) NOT NULL, `document_type` VARCHAR(60) NOT NULL,
  `file_asset_id` CHAR(36) NOT NULL, `verification_status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `reviewed_by` CHAR(36) NULL, `reviewed_at` DATETIME NULL, `rejection_reason` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL, `updated_at` DATETIME NULL,
  CONSTRAINT `fk_influencer_document_owner` FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_influencer_document_file` FOREIGN KEY (`file_asset_id`) REFERENCES `file_assets`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `influencer_payout_profiles` (
  `influencer_id` CHAR(36) NOT NULL PRIMARY KEY, `beneficiary_name` VARCHAR(150) NOT NULL,
  `account_number_encrypted` TEXT NOT NULL, `account_last4` CHAR(4) NOT NULL, `ifsc_encrypted` TEXT NOT NULL,
  `pan_encrypted` TEXT NOT NULL, `pan_last4` CHAR(4) NOT NULL, `verification_status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `updated_at` DATETIME NOT NULL,
  CONSTRAINT `fk_influencer_payout_profile` FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
