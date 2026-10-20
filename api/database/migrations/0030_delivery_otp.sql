-- Migration 0030: Delivery OTP gate
-- Idempotent.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ─── Add OTP columns to vendor_order_groups ───
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vendor_order_groups'
               AND COLUMN_NAME = 'delivery_otp_hash');
SET @sql := IF(@col = 0,
  'ALTER TABLE `vendor_order_groups`
     ADD COLUMN `delivery_otp_hash` VARCHAR(255) NULL,
     ADD COLUMN `delivery_otp_expires_at` DATETIME NULL,
     ADD COLUMN `delivery_otp_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
     ADD COLUMN `delivery_otp_verified_at` DATETIME NULL,
     ADD COLUMN `delivery_otp_generated_at` DATETIME NULL',
  'SELECT "exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ─── Config ───
INSERT INTO `platform_settings` (`setting_key`, `value_json`, `is_public`, `updated_by`, `updated_at`) VALUES
  ('delivery_otp_required',                 '{"value":true}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('delivery_otp_ttl_hours',                '{"value":24}',   0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('delivery_otp_max_attempts',             '{"value":5}',    0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('delivery_otp_regenerate_cooldown_min',  '{"value":5}',    0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0030 applied' AS status;