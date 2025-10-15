-- SQL schema for neuralinkproducts database
-- Run this with your MySQL server to create the database and tables

CREATE DATABASE IF NOT EXISTS `neuralinkproducts` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `neuralinkproducts`;

-- Category table
CREATE TABLE IF NOT EXISTS `category` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `photo` VARCHAR(1024) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `isActive` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product table
CREATE TABLE IF NOT EXISTS `product` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cat_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `short_description` VARCHAR(500) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `product_photo` VARCHAR(1024) DEFAULT NULL,
  `multi_photos` TEXT DEFAULT NULL,
  `isActive` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cat_id` (`cat_id`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`cat_id`) REFERENCES `category`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Example seed data (optional)
INSERT INTO `category` (`name`, `photo`, `description`, `isActive`) VALUES
('Home Furniture', NULL, 'Sample category', 0);

INSERT INTO `product` (`cat_id`, `name`, `short_description`, `description`, `product_photo`, `multi_photos`, `isActive`) VALUES
(1, 'Sample Sofa', 'Comfortable sofa', 'A comfortable 3-seater sofa', NULL, NULL, 0);
