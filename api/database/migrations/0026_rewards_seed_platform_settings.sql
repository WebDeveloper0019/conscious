SET @exists := (SELECT COUNT(*) FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'platform_settings');
SET @sql := IF(@exists = 1,
  'INSERT INTO platform_settings (setting_key, value_json, is_public, updated_by, updated_at)
   VALUES (''rewards.redeem_rate'', ''{"value": 0.10}'', 1, ''00000000-0000-0000-0000-000000000000'', UTC_TIMESTAMP())
   ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at)',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;