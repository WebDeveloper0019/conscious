-- Migration 0024: Reason master + account privacy (deactivate/delete/export)
--
-- Idempotent.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ─────────────────────────────────────────────────────────
-- 1. reason_master
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reason_master` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `scope` VARCHAR(20) NOT NULL,
      -- cancellation | return | return_replacement
  `code` VARCHAR(40) NOT NULL,
  `label` VARCHAR(120) NOT NULL,
  `description` VARCHAR(500) NULL,
  `requires_note` TINYINT(1) NOT NULL DEFAULT 0,
  `requires_evidence` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 100,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  UNIQUE KEY `uniq_scope_code` (`scope`, `code`),
  INDEX `idx_reason_active` (`scope`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────
-- 2. Seed default reasons
-- ─────────────────────────────────────────────────────────
INSERT INTO `reason_master`
  (id, scope, code, label, description, requires_note, requires_evidence, sort_order, is_active, created_at)
VALUES
  (UUID(), 'cancellation', 'changed_mind',     'Changed my mind',              'Customer decided not to proceed',         0, 0, 10, 1, UTC_TIMESTAMP()),
  (UUID(), 'cancellation', 'ordered_by_mistake','Ordered by mistake',          'Accidental purchase',                      0, 0, 20, 1, UTC_TIMESTAMP()),
  (UUID(), 'cancellation', 'found_cheaper',    'Found a better price elsewhere','Comparative purchase',                    0, 0, 30, 1, UTC_TIMESTAMP()),
  (UUID(), 'cancellation', 'delivery_too_long','Delivery taking too long',     'Delivery exceeding expectation',           0, 0, 40, 1, UTC_TIMESTAMP()),
  (UUID(), 'cancellation', 'other',            'Other reason',                 'Please describe',                          1, 0, 99, 1, UTC_TIMESTAMP()),

  (UUID(), 'return', 'damaged',           'Item arrived damaged',        'Damaged in transit or defective',        1, 1, 10, 1, UTC_TIMESTAMP()),
  (UUID(), 'return', 'wrong_item',        'Wrong item delivered',        'Received a different product',           1, 1, 20, 1, UTC_TIMESTAMP()),
  (UUID(), 'return', 'not_as_described',  'Not as described',            'Product differs from listing',           1, 1, 30, 1, UTC_TIMESTAMP()),
  (UUID(), 'return', 'size_fit',          'Size / fit issue',            'Does not fit as expected',               0, 0, 40, 1, UTC_TIMESTAMP()),
  (UUID(), 'return', 'quality_issue',     'Quality not satisfactory',    'Quality below expectation',              1, 1, 50, 1, UTC_TIMESTAMP()),
  (UUID(), 'return', 'missing_parts',     'Missing parts or accessories','Incomplete shipment',                    1, 1, 60, 1, UTC_TIMESTAMP()),
  (UUID(), 'return', 'other',             'Other reason',                'Please describe',                        1, 1, 99, 1, UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- ─────────────────────────────────────────────────────────
-- 3. users — privacy fields
-- ─────────────────────────────────────────────────────────
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
               AND COLUMN_NAME = 'account_status');
SET @sql := IF(@col = 0,
  'ALTER TABLE `users` ADD COLUMN `account_status` VARCHAR(20) NOT NULL DEFAULT ''active'' AFTER `email_verified`',
  'SELECT "already exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
               AND COLUMN_NAME = 'deactivated_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `users` ADD COLUMN `deactivated_at` DATETIME NULL',
  'SELECT "already exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
               AND COLUMN_NAME = 'deletion_requested_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `users` ADD COLUMN `deletion_requested_at` DATETIME NULL',
  'SELECT "already exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
               AND COLUMN_NAME = 'deletion_scheduled_for');
SET @sql := IF(@col = 0,
  'ALTER TABLE `users` ADD COLUMN `deletion_scheduled_for` DATETIME NULL',
  'SELECT "already exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
               AND INDEX_NAME = 'idx_users_account_status');
SET @sql := IF(@idx = 0,
  'ALTER TABLE `users` ADD INDEX `idx_users_account_status` (`account_status`)',
  'SELECT "already exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
               AND INDEX_NAME = 'idx_users_deletion_scheduled');
SET @sql := IF(@idx = 0,
  'ALTER TABLE `users` ADD INDEX `idx_users_deletion_scheduled` (`account_status`, `deletion_scheduled_for`)',
  'SELECT "already exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ─────────────────────────────────────────────────────────
-- 4. user_data_export_jobs
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_data_export_jobs` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `user_id` CHAR(36) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'queued',
      -- queued | processing | ready | failed | expired
  `file_asset_id` CHAR(36) NULL,
  `error_message` VARCHAR(500) NULL,
  `size_bytes` INT UNSIGNED NULL,
  `expires_at` DATETIME NULL,
  `started_at` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  INDEX `idx_export_user` (`user_id`, `created_at`),
  INDEX `idx_export_status` (`status`, `created_at`),
  CONSTRAINT `fk_export_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0024 applied' AS status;