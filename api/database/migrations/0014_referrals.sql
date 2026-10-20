CREATE TABLE IF NOT EXISTS `referral_codes` (
  `user_id`       CHAR(36)     NOT NULL PRIMARY KEY,
  `code`          VARCHAR(20)  NOT NULL,
  `created_at`    DATETIME     NOT NULL,
  UNIQUE KEY `uniq_referral_code` (`code`),
  CONSTRAINT `fk_referral_code_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `referrals` (
  `id`             CHAR(36)     NOT NULL PRIMARY KEY,
  `referrer_id`    CHAR(36)     NOT NULL,
  `referee_id`     CHAR(36)     NOT NULL,
  `code_used`      VARCHAR(20)  NOT NULL,
  `status`         VARCHAR(20)  NOT NULL DEFAULT 'pending',
  `reward_points`  INT          NOT NULL DEFAULT 0,
  `completed_at`   DATETIME     NULL,
  `created_at`     DATETIME     NOT NULL,
  UNIQUE KEY `uniq_referral_referee` (`referee_id`),
  KEY `idx_referral_referrer` (`referrer_id`, `created_at`),
  CONSTRAINT `fk_referral_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_referral_referee`  FOREIGN KEY (`referee_id`)  REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;