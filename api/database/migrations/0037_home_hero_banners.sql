CREATE TABLE IF NOT EXISTS `home_hero_banners` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `eyebrow` VARCHAR(90) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` VARCHAR(360) NOT NULL,
  `button_label` VARCHAR(45) NOT NULL,
  `button_url` VARCHAR(500) NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `image_alt` VARCHAR(180) NOT NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 100,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` CHAR(36) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_home_hero_display` (`is_active`, `sort_order`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `home_hero_banners`
(`id`,`eyebrow`,`title`,`description`,`button_label`,`button_url`,`image_url`,`image_alt`,`sort_order`,`is_active`) VALUES
('ec4e4abe-1cb1-4fd4-98a4-a3f07cf20301','MADE BY INDIAN HANDS','Crafted with care. Chosen to last.','Discover beautiful pieces made by skilled Indian artisans for the spaces and rituals you love.','Shop the collection','/conscious/shop','/conscious/assets/images/eco-scene-4.png','Indian artisan weaving natural fibre textiles in her workshop',10,1),
('ec4e4abe-1cb1-4fd4-98a4-a3f07cf20302','THOUGHTFUL EVERYDAY LIVING','Good things for a gentler home.','Explore useful, lasting home essentials chosen for their materials, craft and everyday beauty.','Explore home goods','/conscious/shop','/conscious/assets/images/home-sustainable-hero.png','Handcrafted baskets, textiles and ceramic home goods in a green landscape',20,1),
('ec4e4abe-1cb1-4fd4-98a4-a3f07cf20303','TEXTILES WITH A STORY','Wear the work of human hands.','Find handwoven textures and considered pieces that keep Indian craft in everyday life.','Discover textiles','/conscious/shop','/conscious/assets/images/eco-scene-2.png','Natural fibre clothing and handwoven textiles',30,1),
('ec4e4abe-1cb1-4fd4-98a4-a3f07cf20304','CONSCIOUS HOME','Choose pieces worth keeping.','Shop crafted objects and natural materials that bring warmth and purpose into your home.','Shop home decor','/conscious/shop','/conscious/assets/images/eco-scene-3.png','Ceramic vase and natural-fibre home accessories',40,1);
