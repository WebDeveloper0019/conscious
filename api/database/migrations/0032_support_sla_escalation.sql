-- Migration 0032: Support ticket SLA escalation
-- Idempotent.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ─── Escalation log (idempotency anchor) ───
CREATE TABLE IF NOT EXISTS `support_sla_escalations` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `ticket_id` CHAR(36) NOT NULL,
  `breach_type` VARCHAR(30) NOT NULL,
      -- first_response | resolution
  `level` TINYINT UNSIGNED NOT NULL DEFAULT 1,
      -- 1 = warning, 2 = urgent, 3 = critical
  `overdue_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
  `notified_admin_id` CHAR(36) NULL,
  `alert_id` CHAR(36) NULL,
  `task_id` CHAR(36) NULL,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_breach` (`ticket_id`, `breach_type`, `level`),
  INDEX `idx_ticket` (`ticket_id`),
  INDEX `idx_created` (`created_at`),
  INDEX `idx_level` (`level`, `created_at`),
  CONSTRAINT `fk_sla_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Denormalized breach columns on support_tickets ───
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_tickets'
               AND COLUMN_NAME = 'sla_breach_level');
SET @sql := IF(@col = 0,
  'ALTER TABLE `support_tickets`
     ADD COLUMN `sla_breach_level` TINYINT UNSIGNED NOT NULL DEFAULT 0,
     ADD COLUMN `first_response_breached_at` DATETIME NULL,
     ADD COLUMN `resolution_breached_at` DATETIME NULL,
     ADD INDEX `idx_sla_breach` (`sla_breach_level`, `status`)',
  'SELECT "exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ─── Escalation thresholds + toggle ───
INSERT INTO `platform_settings`
  (`setting_key`, `value_json`, `is_public`, `updated_by`, `updated_at`)
VALUES
  ('support_sla_worker_enabled',            '{"value":true}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('support_sla_worker_batch_size',         '{"value":200}',  0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('support_sla_first_warning_minutes',     '{"value":15}',   0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('support_sla_first_urgent_minutes',      '{"value":60}',   0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('support_sla_first_critical_minutes',    '{"value":240}',  0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('support_sla_resolution_warning_minutes','{"value":60}',   0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('support_sla_resolution_urgent_minutes', '{"value":360}',  0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('support_sla_resolution_critical_minutes','{"value":1440}',0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0032 applied' AS status;