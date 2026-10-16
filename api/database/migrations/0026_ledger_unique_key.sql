-- Migration 0026: Reward ledger unique key + metadata column
-- Prevents duplicate clawback/reversal entries. Idempotent.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- metadata_json column (agar nahi hai)
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_reward_ledger'
               AND COLUMN_NAME = 'metadata_json');
SET @sql := IF(@col = 0,
  'ALTER TABLE `customer_reward_ledger` ADD COLUMN `metadata_json` LONGTEXT NULL',
  'SELECT "exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Duplicate cleanup (safety — agar already duplicates hain)
DELETE le1 FROM customer_reward_ledger le1
JOIN customer_reward_ledger le2
  ON  le1.user_id = le2.user_id
  AND le1.entry_type = le2.entry_type
  AND le1.reference_type = le2.reference_type
  AND le1.reference_id = le2.reference_id
  AND le1.id > le2.id
WHERE le1.reference_type IS NOT NULL AND le1.reference_id IS NOT NULL;

-- Unique key (agar nahi hai)
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_reward_ledger'
               AND INDEX_NAME = 'uniq_ledger_entry');
SET @sql := IF(@idx = 0,
  'ALTER TABLE `customer_reward_ledger`
   ADD UNIQUE KEY `uniq_ledger_entry` (`user_id`, `entry_type`, `reference_type`, `reference_id`)',
  'SELECT "exists" AS info');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0026 applied' AS status;