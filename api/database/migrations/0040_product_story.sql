ALTER TABLE `products`
  ADD COLUMN `product_story` TEXT NULL AFTER `description`;

UPDATE `products`
SET `product_story` = `description`
WHERE `product_story` IS NULL OR TRIM(`product_story`) = '';

ALTER TABLE `products`
  MODIFY COLUMN `product_story` TEXT NOT NULL AFTER `description`;
