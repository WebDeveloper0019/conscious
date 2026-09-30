ALTER TABLE `users` ADD COLUMN `status` VARCHAR(24) NOT NULL DEFAULT 'active' AFTER `email_verified`, ADD COLUMN `last_login_at` DATETIME NULL AFTER `updated_at`;

CREATE TABLE `payment_webhook_events` (
  `id` CHAR(36) NOT NULL PRIMARY KEY, `provider` VARCHAR(40) NOT NULL, `provider_event_id` VARCHAR(180) NOT NULL,
  `event_type` VARCHAR(80) NOT NULL, `payload_hash` CHAR(64) NOT NULL, `processing_status` VARCHAR(24) NOT NULL,
  `failure_message` VARCHAR(1000) NULL, `received_at` DATETIME NOT NULL, `processed_at` DATETIME NULL,
  UNIQUE KEY `uniq_payment_provider_event` (`provider`,`provider_event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `data_access_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `admin_id` CHAR(36) NOT NULL,
  `subject_type` VARCHAR(40) NOT NULL, `subject_id` CHAR(36) NOT NULL, `purpose` VARCHAR(500) NOT NULL,
  `fields_json` JSON NOT NULL, `created_at` DATETIME NOT NULL,
  INDEX `idx_data_access_subject` (`subject_type`,`subject_id`,`created_at`),
  CONSTRAINT `fk_data_access_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `permissions` (`code`,`description`) VALUES
('customers.manage','Manage customer accounts'),('payments.manage','Process signed payment events'),
('settings.manage','Manage platform configuration'),('sensitive_data.view','Purpose-limited sensitive data access');
INSERT IGNORE INTO `role_permissions` (`role_id`,`permission_id`)
SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE r.code='admin_super';
