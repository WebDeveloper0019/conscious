-- Migration 0017: Cancellation hardening
-- Prevents double-refund, double-restore, and duplicate ledger reversals.
--
-- Idempotent — safe to run multiple times.
-- No `AFTER <col>` clauses (they fail if the reference column is missing).

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ─────────────────────────────────────────────────────────
-- 1. refunds.source — prevents double-refund per (order, source)
-- ─────────────────────────────────────────────────────────
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'refunds'
               AND COLUMN_NAME = 'source');
SET @sql := IF(@col = 0,
  'ALTER TABLE `refunds` ADD COLUMN `source` VARCHAR(20) NOT NULL DEFAULT ''cancellation''',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Backfill: any refund linked to a return request → source='return'
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'refunds'
               AND COLUMN_NAME = 'return_request_id');
SET @sql := IF(@col > 0,
  'UPDATE `refunds` SET `source` = ''return'' WHERE `return_request_id` IS NOT NULL AND `source` = ''cancellation''',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Unique key (order_id, source)
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'refunds'
               AND INDEX_NAME = 'uniq_refund_order_source');
SET @sql := IF(@idx = 0,
  'ALTER TABLE `refunds` ADD UNIQUE KEY `uniq_refund_order_source` (`order_id`, `source`)',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ─────────────────────────────────────────────────────────
-- 2. vendor_ledger_entries — status + reversed_at + unique key
-- ─────────────────────────────────────────────────────────
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vendor_ledger_entries'
               AND COLUMN_NAME = 'status');
SET @sql := IF(@col = 0,
  'ALTER TABLE `vendor_ledger_entries` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT ''active''',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vendor_ledger_entries'
               AND COLUMN_NAME = 'reversed_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `vendor_ledger_entries` ADD COLUMN `reversed_at` DATETIME NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

DELETE le1 FROM vendor_ledger_entries le1
JOIN vendor_ledger_entries le2
  ON  le1.reference_type = le2.reference_type
  AND le1.reference_id   = le2.reference_id
  AND le1.entry_type     = le2.entry_type
  AND le1.id > le2.id;

SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vendor_ledger_entries'
               AND INDEX_NAME = 'uniq_ledger_reference');
SET @sql := IF(@idx = 0,
  'ALTER TABLE `vendor_ledger_entries` ADD UNIQUE KEY `uniq_ledger_reference` (`reference_type`, `reference_id`, `entry_type`)',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ─────────────────────────────────────────────────────────
-- 3. inventory_movements — unique key
-- ─────────────────────────────────────────────────────────
DELETE im1 FROM inventory_movements im1
JOIN inventory_movements im2
  ON  im1.reference_type = im2.reference_type
  AND im1.reference_id   = im2.reference_id
  AND im1.movement_type  = im2.movement_type
  AND im1.id > im2.id;

SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventory_movements'
               AND INDEX_NAME = 'uniq_movement_ref');
SET @sql := IF(@idx = 0,
  'ALTER TABLE `inventory_movements` ADD UNIQUE KEY `uniq_movement_ref` (`reference_type`, `reference_id`, `movement_type`)',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ─────────────────────────────────────────────────────────
-- 4. coupon_redemptions — soft delete columns
-- ─────────────────────────────────────────────────────────
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'coupon_redemptions'
               AND COLUMN_NAME = 'status');
SET @sql := IF(@col = 0,
  'ALTER TABLE `coupon_redemptions` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT ''active''',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'coupon_redemptions'
               AND COLUMN_NAME = 'reversed_at');
SET @sql := IF(@col = 0,
  'ALTER TABLE `coupon_redemptions` ADD COLUMN `reversed_at` DATETIME NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'coupon_redemptions'
               AND COLUMN_NAME = 'reversed_reason');
SET @sql := IF(@col = 0,
  'ALTER TABLE `coupon_redemptions` ADD COLUMN `reversed_reason` VARCHAR(100) NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET SQL_MODE = @OLD_SQL_MODE;

SELECT '0017 applied' AS status;