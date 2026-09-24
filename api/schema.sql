-- Import this file in phpMyAdmin: Import tab -> Choose file -> Go
-- (or: mysql -u root -p < schema.sql)
-- Creates the database and the single `users` table this backend needs.

CREATE DATABASE IF NOT EXISTS `conscious_indi`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `conscious_indi`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `mobile` VARCHAR(30) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
