-- Migration 0016: Invoices
--
-- Adds the invoice_sequences counter (one per financial year) and the
-- invoices table (immutable snapshot per order). Also seeds default
-- seller/GST settings in platform_settings.
--
-- Idempotent — safe to run multiple times.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ─────────────────────────────────────────────────────────
-- 1. invoice_sequences — permanent numbering per FY
--    FY format: 2026-27 (Apr 1 → Mar 31)
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `invoice_sequences` (
  `fy` VARCHAR(10) NOT NULL PRIMARY KEY,
  `next_number` INT NOT NULL DEFAULT 1,
  `updated_at` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────
-- 2. invoices — immutable snapshot per order
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `invoice_number` VARCHAR(40) NOT NULL,
  `order_id` BIGINT(20) UNSIGNED NOT NULL,
  `user_id` CHAR(36) NOT NULL,
  `financial_year` VARCHAR(10) NOT NULL,
  `sequence_number` INT NOT NULL,

  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `shipping_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `taxable_value` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `cgst_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `sgst_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `igst_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_tax` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `grand_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `currency` CHAR(3) NOT NULL DEFAULT 'INR',

  `seller_snapshot_json` LONGTEXT NOT NULL,
  `buyer_snapshot_json` LONGTEXT NOT NULL,
  `items_snapshot_json` LONGTEXT NOT NULL,
  `payment_snapshot_json` LONGTEXT NULL,

  `issued_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,

  UNIQUE KEY `uniq_invoice_number` (`invoice_number`),
  UNIQUE KEY `uniq_order_invoice` (`order_id`),
  INDEX `idx_invoices_user` (`user_id`),
  INDEX `idx_invoices_fy` (`financial_year`),
  CONSTRAINT `fk_invoices_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoices_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────
-- 3. Seller / GST settings (idempotent seed)
--    Update these values in production BEFORE issuing invoices.
-- ─────────────────────────────────────────────────────────
INSERT INTO `platform_settings` (`setting_key`, `value_json`, `is_public`, `updated_by`, `updated_at`) VALUES
  ('seller_legal_name',   '{"value":"Conscious Indi Private Limited"}',             1, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('seller_gstin',        '{"value":""}',                                           1, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('seller_pan',          '{"value":""}',                                           1, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('seller_address',      '{"value":"1 MG Road, Bangalore, Karnataka 560001"}',     1, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('seller_state',        '{"value":"Karnataka"}',                                  1, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('seller_state_code',   '{"value":"29"}',                                         1, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('seller_email',        '{"value":"billing@consciousindi.com"}',                  1, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('seller_phone',        '{"value":""}',                                           1, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('default_gst_percent', '{"value":5}',                                              1, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('invoice_prefix',      '{"value":"INV"}',                                        1, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

SET SQL_MODE = @OLD_SQL_MODE;

SELECT '0016_invoices applied' AS status;
