-- Migration 0021: Reward expiry tracking + notification dedup
--
-- Tracks which expiry notifications have been sent per (user, stage, expiry_date)
-- so the cron worker doesn't spam.
--
-- Idempotent.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ─────────────────────────────────────────────────────────
-- 1. reward_expiry_notifications
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reward_expiry_notifications` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `user_id` CHAR(36) NOT NULL,
  `notification_type` VARCHAR(20) NOT NULL,
      -- warn_30d | warn_7d | warn_1d | expired
  `expiry_date` DATE NOT NULL,
  `points_at_send` INT NOT NULL DEFAULT 0,
  `sent_at` DATETIME NOT NULL,
  `metadata_json` LONGTEXT NULL,
  UNIQUE KEY `uniq_user_type_expiry` (`user_id`, `notification_type`, `expiry_date`),
  INDEX `idx_notif_user` (`user_id`, `sent_at`),
  CONSTRAINT `fk_exp_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────
-- 2. Configurable warning windows (days before expiry)
-- ─────────────────────────────────────────────────────────
INSERT INTO `platform_settings` (`setting_key`, `value_json`, `is_public`, `updated_by`, `updated_at`) VALUES
  ('reward_expiry_warn_30d', '{"value":true}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('reward_expiry_warn_7d',  '{"value":true}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('reward_expiry_warn_1d',  '{"value":true}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('reward_expiry_worker_batch_size', '{"value":500}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE `value_json` = VALUES(`value_json`);

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0021 applied' AS status;