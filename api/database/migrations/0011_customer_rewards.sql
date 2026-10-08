CREATE TABLE IF NOT EXISTS `customer_reward_ledger` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `user_id` CHAR(36) NOT NULL,
  `points` INT NOT NULL,
  `entry_type` VARCHAR(40) NOT NULL,
  `reference_type` VARCHAR(40) NULL,
  `reference_id` VARCHAR(100) NULL,
  `description` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_customer_reward_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_customer_reward_user` (`user_id`,`created_at`),
  INDEX `idx_customer_reward_reference` (`reference_type`,`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
