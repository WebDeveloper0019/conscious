-- Migration 0027: Session device info for active session management
-- Stores readable user-agent / IP so user can identify their own devices.
-- Idempotent.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'auth_sessions'
               AND COLUMN_NAME = 'user_agent_text');
SET @sql := IF(@col = 0,
  'ALTER TABLE `auth_sessions` ADD COLUMN `user_agent_text` VARCHAR(500) NULL',
  'SELECT "exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'auth_sessions'
               AND COLUMN_NAME = 'ip_address');
SET @sql := IF(@col = 0,
  'ALTER TABLE `auth_sessions` ADD COLUMN `ip_address` VARCHAR(45) NULL',
  'SELECT "exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'auth_sessions'
               AND COLUMN_NAME = 'device_label');
SET @sql := IF(@col = 0,
  'ALTER TABLE `auth_sessions` ADD COLUMN `device_label` VARCHAR(100) NULL',
  'SELECT "exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0027 applied' AS status;