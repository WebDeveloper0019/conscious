-- Migration 0019: Admin tasks + alerts
-- Used by refund-queue worker on permanent failure (and other admin-worthy events)
--
-- Idempotent.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ─────────────────────────────────────────────────────────
-- 1. admin_tasks — work queue for staff
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `admin_tasks` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `task_type` VARCHAR(40) NOT NULL,
  `priority` VARCHAR(10) NOT NULL DEFAULT 'normal',
      -- low | normal | high | urgent
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `subject_type` VARCHAR(40) NULL,
  `subject_id` VARCHAR(80) NULL,
  `payload_json` LONGTEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'open',
      -- open | in_progress | resolved | dismissed
  `assigned_to` CHAR(36) NULL,
  `resolved_by` CHAR(36) NULL,
  `resolved_at` DATETIME NULL,
  `resolution_note` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  INDEX `idx_tasks_status` (`status`, `priority`, `created_at`),
  INDEX `idx_tasks_subject` (`subject_type`, `subject_id`),
  INDEX `idx_tasks_assigned` (`assigned_to`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────
-- 2. admin_alerts — banner/notification center
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `admin_alerts` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `alert_type` VARCHAR(40) NOT NULL,
  `severity` VARCHAR(10) NOT NULL DEFAULT 'warning',
      -- info | warning | critical
  `title` VARCHAR(255) NOT NULL,
  `message` VARCHAR(500) NOT NULL,
  `subject_type` VARCHAR(40) NULL,
  `subject_id` VARCHAR(80) NULL,
  `payload_json` LONGTEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'unread',
      -- unread | read | dismissed
  `read_by` CHAR(36) NULL,
  `read_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  INDEX `idx_alerts_status` (`status`, `severity`, `created_at`),
  INDEX `idx_alerts_subject` (`subject_type`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET SQL_MODE = @OLD_SQL_MODE;

SELECT '0019 applied' AS status;