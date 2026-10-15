-- Migration 0025: Notifications soft-delete + read tracking
SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- Soft delete column
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications'
               AND COLUMN_NAME = 'deleted_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `notifications` ADD COLUMN `deleted_at` DATETIME NULL, ADD INDEX `idx_notif_deleted` (`recipient_type`, `recipient_id`, `deleted_at`)',
  'SELECT "exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- read_at (defensive — skip if exists)
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications'
               AND COLUMN_NAME = 'read_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `notifications` ADD COLUMN `read_at` DATETIME NULL',
  'SELECT "exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0025 applied' AS status;