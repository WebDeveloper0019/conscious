-- Migration 0029: Product Questions & Answers
-- Idempotent.

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

CREATE TABLE IF NOT EXISTS `product_questions` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `product_id` VARCHAR(100) NOT NULL,
  `user_id` CHAR(36) NOT NULL,
  `question_text` VARCHAR(500) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
      -- pending | published | rejected | hidden
  `helpful_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `not_helpful_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `answer_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `moderated_by` CHAR(36) NULL,
  `moderated_at` DATETIME NULL,
  `moderation_note` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  INDEX `idx_q_product` (`product_id`, `status`, `created_at`),
  INDEX `idx_q_user` (`user_id`, `status`),
  INDEX `idx_q_status` (`status`, `created_at`),
  CONSTRAINT `fk_q_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_answers` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `question_id` CHAR(36) NOT NULL,
  `answered_by_type` VARCHAR(20) NOT NULL,
      -- vendor | admin
  `answered_by_id` CHAR(36) NOT NULL,
  `answer_text` VARCHAR(1500) NOT NULL,
  `is_official` TINYINT(1) NOT NULL DEFAULT 1,
  `status` VARCHAR(20) NOT NULL DEFAULT 'published',
      -- published | hidden | rejected
  `helpful_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  INDEX `idx_a_question` (`question_id`, `status`, `created_at`),
  CONSTRAINT `fk_a_question` FOREIGN KEY (`question_id`) REFERENCES `product_questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET SQL_MODE = @OLD_SQL_MODE;
SELECT '0029 applied' AS status;