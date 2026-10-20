CREATE TABLE `influencer_inquiries` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `influencer_id` CHAR(36) NOT NULL,
  `sender_name` VARCHAR(140) NOT NULL,
  `sender_email` VARCHAR(190) NOT NULL,
  `sender_phone` VARCHAR(15) NOT NULL,
  `package_name` VARCHAR(40) NOT NULL,
  `message` VARCHAR(3000) NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'new',
  `created_at` DATETIME NOT NULL,
  `read_at` DATETIME NULL,
  `closed_at` DATETIME NULL,
  INDEX `idx_influencer_inquiries` (`influencer_id`,`status`,`created_at`),
  CONSTRAINT `fk_influencer_inquiry_owner` FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
