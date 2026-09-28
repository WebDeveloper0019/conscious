USE `conscious_indi`;

ALTER TABLE `users`
  ADD COLUMN `email_verified` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `otp_hash` VARCHAR(255) NULL,
  ADD COLUMN `otp_expires_at` DATETIME NULL,
  ADD COLUMN `otp_attempts` INT NOT NULL DEFAULT 0,
  ADD COLUMN `otp_last_sent_at` DATETIME NULL;
