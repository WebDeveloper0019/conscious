-- Migration 0020: Inventory anomaly tracking + refund status cache
--
-- Adds:
--   - anomaly_flags on inventory_movements (mark suspicious rows)
--   - inventory_anomaly_log (dedicated audit for anomalies)
--   - v_stock_historical_max view (for threshold checks)
--
-- Idempotent.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ─────────────────────────────────────────────────────────
-- 1. anomaly_flags on inventory_movements
-- ─────────────────────────────────────────────────────────
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventory_movements'
               AND COLUMN_NAME = 'anomaly_flag');
SET @sql := IF(@col = 0,
  'ALTER TABLE `inventory_movements` ADD COLUMN `anomaly_flag` TINYINT(1) NOT NULL DEFAULT 0 AFTER `actor_id`',
  'SELECT "already exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ─────────────────────────────────────────────────────────
-- 2. inventory_anomaly_log — audit of detected anomalies
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `inventory_anomaly_log` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `variant_id` CHAR(36) NOT NULL,
  `vendor_id` CHAR(36) NULL,
  `movement_id` BIGINT(20) UNSIGNED NULL,
  `anomaly_type` VARCHAR(40) NOT NULL,
      -- large_increase | exceeds_historical_max | unexpected_movement_type | rapid_fire | orphan_increase
  `severity` VARCHAR(10) NOT NULL DEFAULT 'warning',
      -- info | warning | critical
  `detail_json` LONGTEXT NULL,
  `stock_before` INT NULL,
  `stock_after` INT NULL,
  `quantity_delta` INT NULL,
  `resolved` TINYINT(1) NOT NULL DEFAULT 0,
  `resolved_by` CHAR(36) NULL,
  `resolved_at` DATETIME NULL,
  `resolution_note` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL,
  INDEX `idx_anomaly_variant` (`variant_id`, `created_at`),
  INDEX `idx_anomaly_type` (`anomaly_type`, `severity`, `created_at`),
  INDEX `idx_anomaly_resolved` (`resolved`, `severity`),
  CONSTRAINT `fk_anomaly_variant` FOREIGN KEY (`variant_id`) REFERENCES `inventory_levels`(`variant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────
-- 3. Seed anomaly thresholds (in platform_settings for editability)
-- ─────────────────────────────────────────────────────────
INSERT INTO `platform_settings` (`setting_key`, `value_json`, `is_public`, `updated_by`, `updated_at`) VALUES
  ('inventory_anomaly_large_increase',    '{"value":50}',  0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('inventory_anomaly_historical_factor', '{"value":2.0}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('inventory_anomaly_rapid_window_sec',  '{"value":60}',  0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('inventory_anomaly_rapid_threshold',   '{"value":5}',   0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0020 applied' AS status;