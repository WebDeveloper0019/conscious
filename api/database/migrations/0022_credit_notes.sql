-- Migration 0022: Credit notes for refunded/cancelled orders
--
-- GST-compliant credit notes that reverse tax liability on refunds.
-- Idempotent.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ─────────────────────────────────────────────────────────
-- 1. credit_note_sequences — one counter per FY
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `credit_note_sequences` (
  `fy` VARCHAR(10) NOT NULL PRIMARY KEY,
  `next_number` INT NOT NULL DEFAULT 1,
  `updated_at` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────
-- 2. credit_notes — immutable snapshot per refund
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `credit_notes` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `credit_note_number` VARCHAR(40) NOT NULL,
  `financial_year` VARCHAR(10) NOT NULL,
  `sequence_number` INT NOT NULL,

  `refund_id` CHAR(36) NOT NULL,
  `order_id` BIGINT(20) UNSIGNED NOT NULL,
  `user_id` CHAR(36) NOT NULL,
  `original_invoice_id` CHAR(36) NULL,
  `original_invoice_number` VARCHAR(40) NULL,

  `reason` VARCHAR(500) NOT NULL,
  `cancellation_scope` VARCHAR(20) NULL,   -- full | partial

  -- GST amounts (all positive; represent the reversal)
  `taxable_value` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `cgst_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `sgst_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `igst_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_tax` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_credit` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `currency` CHAR(3) NOT NULL DEFAULT 'INR',

  -- Immutable snapshots
  `seller_snapshot_json` LONGTEXT NOT NULL,
  `buyer_snapshot_json` LONGTEXT NOT NULL,
  `items_snapshot_json` LONGTEXT NOT NULL,
  `refund_snapshot_json` LONGTEXT NULL,

  `issued_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,

  UNIQUE KEY `uniq_credit_note_number` (`credit_note_number`),
  UNIQUE KEY `uniq_refund_credit_note` (`refund_id`),
  INDEX `idx_cn_user` (`user_id`),
  INDEX `idx_cn_order` (`order_id`),
  INDEX `idx_cn_fy` (`financial_year`),
  CONSTRAINT `fk_cn_refund` FOREIGN KEY (`refund_id`) REFERENCES `refunds`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cn_order`  FOREIGN KEY (`order_id`)  REFERENCES `orders`(`id`)  ON DELETE CASCADE,
  CONSTRAINT `fk_cn_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────
-- 3. Seed bank + terms defaults (idempotent)
-- ─────────────────────────────────────────────────────────
INSERT INTO `platform_settings` (`setting_key`, `value_json`, `is_public`, `updated_by`, `updated_at`) VALUES
  ('seller_bank_name',     '{"value":""}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('seller_bank_account',  '{"value":""}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('seller_bank_ifsc',     '{"value":""}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('seller_bank_branch',   '{"value":""}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('credit_note_prefix',   '{"value":"CN"}',   0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('invoice_terms',        '{"value":"Goods once sold will not be taken back except as per our return policy."}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('invoice_footer_note',  '{"value":"Thank you for your business."}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP()),
  ('invoice_declaration',  '{"value":"We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct."}', 0, '00000000-0000-0000-0000-000000000000', UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0022 applied' AS status;