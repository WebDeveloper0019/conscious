-- Migration 0033: Ensure support_tickets has all SLA-required columns
-- Idempotent â€” safe to run multiple times.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- first_response_at
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_tickets'
               AND COLUMN_NAME = 'first_response_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `support_tickets` ADD COLUMN `first_response_at` DATETIME NULL',
  'SELECT "exists: first_response_at" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- first_response_due_at
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_tickets'
               AND COLUMN_NAME = 'first_response_due_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `support_tickets` ADD COLUMN `first_response_due_at` DATETIME NULL',
  'SELECT "exists: first_response_due_at" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- resolution_due_at
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_tickets'
               AND COLUMN_NAME = 'resolution_due_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `support_tickets` ADD COLUMN `resolution_due_at` DATETIME NULL',
  'SELECT "exists: resolution_due_at" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- resolved_at
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_tickets'
               AND COLUMN_NAME = 'resolved_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `support_tickets` ADD COLUMN `resolved_at` DATETIME NULL',
  'SELECT "exists: resolved_at" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- assigned_admin_id
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_tickets'
               AND COLUMN_NAME = 'assigned_admin_id');
SET @sql := IF(@col = 0,
  'ALTER TABLE `support_tickets` ADD COLUMN `assigned_admin_id` CHAR(36) NULL,
   ADD INDEX `idx_assigned_admin` (`assigned_admin_id`)',
  'SELECT "exists: assigned_admin_id" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- closed_at
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_tickets'
               AND COLUMN_NAME = 'closed_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `support_tickets` ADD COLUMN `closed_at` DATETIME NULL',
  'SELECT "exists: closed_at" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- updated_at
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_tickets'
               AND COLUMN_NAME = 'updated_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `support_tickets` ADD COLUMN `updated_at` DATETIME NULL',
  'SELECT "exists: updated_at" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0033 applied' AS status;
