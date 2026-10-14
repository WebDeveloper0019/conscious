-- Migration 0015: Rewards - balance table + configurable rules
--
-- The ledger table (customer_reward_ledger) was created by migration 0011.
-- This migration adds the balance cache table + a rules config table so
-- the earning/redeeming rates can be changed without code edits.
--
-- Idempotent — safe to run multiple times.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ─────────────────────────────────────────────────────────
-- 1. customer_rewards — cached balance per user
--    Source of truth is still the ledger; this is an atomic
--    counter so concurrent redeems can lock a single row.
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `customer_rewards` (
  `user_id` CHAR(36) NOT NULL PRIMARY KEY,
  `balance_points` INT NOT NULL DEFAULT 0,
  `lifetime_earned` INT NOT NULL DEFAULT 0,
  `lifetime_redeemed` INT NOT NULL DEFAULT 0,
  `tier` VARCHAR(20) NOT NULL DEFAULT 'bronze',
  `updated_at` DATETIME NULL,
  CONSTRAINT `fk_rewards_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────
-- 2. customer_reward_rules — configurable rates
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `customer_reward_rules` (
  `rule_key` VARCHAR(40) NOT NULL PRIMARY KEY,
  `value` DECIMAL(12,4) NOT NULL,
  `description` VARCHAR(255) NULL,
  `updated_at` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customer_reward_rules` (`rule_key`, `value`, `description`, `updated_at`) VALUES
  ('earn_rate_percent',  2.0000,  'Points earned as % of order total on delivery', UTC_TIMESTAMP()),
  ('redeem_max_percent', 20.0000, 'Maximum % of order subtotal that can be paid with points', UTC_TIMESTAMP()),
  ('min_redeem_points',  100,     'Minimum points required to redeem', UTC_TIMESTAMP()),
  ('point_value_rupees', 1.0000,  '1 point = ₹X discount', UTC_TIMESTAMP()),
  ('expiry_days',        365,     'Points expire after N days', UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE `rule_key` = `rule_key`;

-- ─────────────────────────────────────────────────────────
-- 3. Backfill balances from existing ledger history
--    (skips users that already have a balance row)
-- ─────────────────────────────────────────────────────────
INSERT INTO `customer_rewards`
  (`user_id`, `balance_points`, `lifetime_earned`, `lifetime_redeemed`, `updated_at`)
SELECT
  `user_id`,
  GREATEST(0, COALESCE(SUM(CASE
    WHEN `status` = 'active'
      AND (`points` < 0 OR `expires_at` IS NULL OR `expires_at` > UTC_TIMESTAMP())
    THEN `points` ELSE 0 END), 0)),
  COALESCE(SUM(CASE WHEN `entry_type` = 'earned'   THEN `points` ELSE 0 END), 0),
  COALESCE(SUM(CASE WHEN `entry_type` = 'redeemed' THEN ABS(`points`) ELSE 0 END), 0),
  UTC_TIMESTAMP()
FROM `customer_reward_ledger`
GROUP BY `user_id`
ON DUPLICATE KEY UPDATE `updated_at` = VALUES(`updated_at`);

SET SQL_MODE = @OLD_SQL_MODE;

SELECT '0015_rewards applied' AS status;
