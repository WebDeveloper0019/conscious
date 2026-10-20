-- Migration 0031: Granular notification preferences
-- Idempotent. Backward compatible with existing boolean columns.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ─── New JSON column ───
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_preferences'
               AND COLUMN_NAME = 'notification_settings_json');
SET @sql := IF(@col = 0,
  'ALTER TABLE `user_preferences` ADD COLUMN `notification_settings_json` LONGTEXT NULL',
  'SELECT "exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ─── Backfill from existing booleans ───
UPDATE `user_preferences`
SET `notification_settings_json` = JSON_OBJECT(
  'order_updates',   JSON_OBJECT('email', order_updates, 'sms', order_updates, 'push', order_updates, 'in_app', order_updates),
  'promotions',      JSON_OBJECT('email', offers_promotions, 'sms', 0, 'push', 0, 'in_app', offers_promotions),
  'wishlist_alerts', JSON_OBJECT('email', wishlist_alerts, 'sms', 0, 'push', wishlist_alerts, 'in_app', 1),
  'newsletter',      JSON_OBJECT('email', newsletter, 'sms', 0, 'push', 0, 'in_app', 0),
  'security',        JSON_OBJECT('email', 1, 'sms', 1, 'push', 1, 'in_app', 1)
)
WHERE `notification_settings_json` IS NULL;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0031 applied' AS status;