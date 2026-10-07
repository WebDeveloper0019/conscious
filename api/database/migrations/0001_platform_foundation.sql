CREATE TABLE IF NOT EXISTS `schema_migrations` (
  `version` VARCHAR(100) NOT NULL PRIMARY KEY,
  `checksum` CHAR(64) NOT NULL,
  `applied_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_sessions` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `principal_type` VARCHAR(24) NOT NULL,
  `principal_id` CHAR(36) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `csrf_hash` CHAR(64) NOT NULL,
  `ip_hash` CHAR(64) NULL,
  `user_agent_hash` CHAR(64) NULL,
  `created_at` DATETIME NOT NULL,
  `last_seen_at` DATETIME NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `revoked_at` DATETIME NULL,
  `revoke_reason` VARCHAR(80) NULL,
  UNIQUE KEY `uniq_auth_session_token` (`token_hash`),
  INDEX `idx_auth_session_principal` (`principal_type`,`principal_id`,`revoked_at`),
  INDEX `idx_auth_session_expiry` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `principal_type` VARCHAR(24) NOT NULL,
  `principal_id` CHAR(36) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME NULL,
  UNIQUE KEY `uniq_password_reset_token` (`token_hash`),
  INDEX `idx_password_reset_principal` (`principal_type`,`principal_id`,`used_at`,`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(80) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_role_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) NULL,
  UNIQUE KEY `uniq_permission_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  CONSTRAINT `fk_role_permission_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_role_permission_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `principal_roles` (
  `principal_type` VARCHAR(24) NOT NULL,
  `principal_id` CHAR(36) NOT NULL,
  `role_id` BIGINT UNSIGNED NOT NULL,
  `granted_by_type` VARCHAR(24) NULL,
  `granted_by_id` CHAR(36) NULL,
  `granted_at` DATETIME NOT NULL,
  PRIMARY KEY (`principal_type`,`principal_id`,`role_id`),
  CONSTRAINT `fk_principal_role_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `actor_type` VARCHAR(24) NULL,
  `actor_id` CHAR(36) NULL,
  `action` VARCHAR(120) NOT NULL,
  `subject_type` VARCHAR(80) NULL,
  `subject_id` VARCHAR(80) NULL,
  `request_id` CHAR(36) NOT NULL,
  `ip_hash` CHAR(64) NULL,
  `metadata_json` JSON NULL,
  `created_at` DATETIME NOT NULL,
  INDEX `idx_audit_actor` (`actor_type`,`actor_id`,`created_at`),
  INDEX `idx_audit_subject` (`subject_type`,`subject_id`,`created_at`),
  INDEX `idx_audit_action` (`action`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `status_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `entity_type` VARCHAR(80) NOT NULL,
  `entity_id` VARCHAR(80) NOT NULL,
  `from_status` VARCHAR(60) NULL,
  `to_status` VARCHAR(60) NOT NULL,
  `actor_type` VARCHAR(24) NULL,
  `actor_id` CHAR(36) NULL,
  `reason` VARCHAR(500) NULL,
  `metadata_json` JSON NULL,
  `created_at` DATETIME NOT NULL,
  INDEX `idx_status_entity` (`entity_type`,`entity_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `recipient_type` VARCHAR(24) NOT NULL,
  `recipient_id` CHAR(36) NOT NULL,
  `type` VARCHAR(100) NOT NULL,
  `title` VARCHAR(180) NOT NULL,
  `message` VARCHAR(1000) NOT NULL,
  `action_url` VARCHAR(500) NULL,
  `metadata_json` JSON NULL,
  `read_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  INDEX `idx_notification_recipient` (`recipient_type`,`recipient_id`,`read_at`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `file_assets` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `owner_type` VARCHAR(40) NOT NULL,
  `owner_id` CHAR(36) NOT NULL,
  `purpose` VARCHAR(80) NOT NULL,
  `storage_key` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `extension` VARCHAR(12) NOT NULL,
  `size_bytes` BIGINT UNSIGNED NOT NULL,
  `sha256` CHAR(64) NOT NULL,
  `visibility` VARCHAR(16) NOT NULL DEFAULT 'private',
  `scan_status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL,
  `deleted_at` DATETIME NULL,
  UNIQUE KEY `uniq_file_storage_key` (`storage_key`),
  INDEX `idx_file_owner` (`owner_type`,`owner_id`,`purpose`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `idempotency_keys` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `scope` VARCHAR(100) NOT NULL,
  `principal_type` VARCHAR(24) NOT NULL,
  `principal_id` CHAR(36) NOT NULL,
  `idempotency_key` VARCHAR(120) NOT NULL,
  `request_hash` CHAR(64) NOT NULL,
  `response_status` SMALLINT UNSIGNED NULL,
  `response_json` JSON NULL,
  `locked_until` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `expires_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_idempotency` (`scope`,`principal_type`,`principal_id`,`idempotency_key`),
  INDEX `idx_idempotency_expiry` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `roles` (`code`,`name`,`created_at`) VALUES
('customer','Customer',UTC_TIMESTAMP()),('vendor','Vendor',UTC_TIMESTAMP()),
('admin_super','Super administrator',UTC_TIMESTAMP()),('admin_operations','Operations administrator',UTC_TIMESTAMP()),
('admin_finance','Finance administrator',UTC_TIMESTAMP()),('admin_support','Support administrator',UTC_TIMESTAMP()),
('influencer','Influencer',UTC_TIMESTAMP());
