CREATE TABLE `influencer_curated_products` (
  `influencer_id` CHAR(36) NOT NULL,
  `product_id` CHAR(36) NOT NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`influencer_id`,`product_id`),
  INDEX `idx_influencer_curated_order` (`influencer_id`,`sort_order`,`created_at`),
  CONSTRAINT `fk_influencer_curated_owner` FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_influencer_curated_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
