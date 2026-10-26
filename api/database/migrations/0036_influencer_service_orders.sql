-- 1. Main Service Orders Table
CREATE TABLE IF NOT EXISTS `service_orders` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `vendor_id` CHAR(36) NOT NULL,
  `influencer_id` CHAR(36) NOT NULL,
  `package_id` CHAR(36) NOT NULL,
  `brief` TEXT NOT NULL,
  `base_amount` DECIMAL(10,2) NOT NULL,
  `gst_amount` DECIMAL(10,2) NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending_payment', 'paid', 'contact_pending', 'scheduled', 'in_progress', 'delivered', 'completed', 'cancelled', 'disputed') NOT NULL DEFAULT 'pending_payment',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Package Version Snapshot
CREATE TABLE IF NOT EXISTS `service_order_snapshots` (
  `order_id` CHAR(36) NOT NULL PRIMARY KEY,
  `package_title` VARCHAR(150) NOT NULL,
  `deliverables_json` JSON NOT NULL,
  `platforms_json` JSON NOT NULL,
  `turnaround_days` INT NOT NULL,
  `usage_rights` TEXT NOT NULL,
  `vendor_requirements` TEXT NOT NULL,
  `cancellation_terms` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Razorpay Payments & Idempotency
CREATE TABLE IF NOT EXISTS `service_payments` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `order_id` CHAR(36) NOT NULL,
  `razorpay_order_id` VARCHAR(100) NOT NULL UNIQUE,
  `razorpay_payment_id` VARCHAR(100) UNIQUE,
  `razorpay_signature` VARCHAR(255) NULL,
  `status` ENUM('created', 'authorized', 'captured', 'failed', 'refunded') NOT NULL DEFAULT 'created',
  `amount_inr` DECIMAL(10,2) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Status History (Audit trail)
CREATE TABLE IF NOT EXISTS `service_order_status_history` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `order_id` CHAR(36) NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `notes` TEXT NULL,
  `changed_by_type` ENUM('system', 'admin', 'vendor', 'influencer') NOT NULL,
  `changed_by_id` CHAR(36) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Earnings Link
CREATE TABLE IF NOT EXISTS `service_earnings_links` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `order_id` CHAR(36) NOT NULL,
  `influencer_id` CHAR(36) NOT NULL,
  `ledger_entry_id` CHAR(36) NOT NULL,
  `commission_amount` DECIMAL(10,2) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Refunds & Disputes
CREATE TABLE IF NOT EXISTS `service_refunds_disputes` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `order_id` CHAR(36) NOT NULL,
  `type` ENUM('refund', 'dispute') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('requested', 'approved', 'processed', 'rejected') NOT NULL DEFAULT 'requested',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;