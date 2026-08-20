-- KilimoTrack Database Schema — Full Version with Marketplace
-- Run this in phpMyAdmin or MySQL CLI
-- XAMPP: http://localhost/phpmyadmin

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+03:00";

CREATE DATABASE IF NOT EXISTS `kilimotrack` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kilimotrack`;

-- --------------------------------------------------------
-- farmers
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `farmers` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(150) NOT NULL,
  `phone`         VARCHAR(20)  NOT NULL,
  `email`         VARCHAR(150) DEFAULT NULL,
  `location`      VARCHAR(200) DEFAULT NULL,
  `farm_name`     VARCHAR(200) DEFAULT NULL,
  `acreage`       DECIMAL(8,2) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `profile_photo` VARCHAR(255) DEFAULT NULL,
  `auth_token`    VARCHAR(64)  DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- organizations
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `organizations` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `org_name`      VARCHAR(200) NOT NULL,
  `org_type`      ENUM('NGO','Manufacturing Company','Government Firm') NOT NULL,
  `contact_name`  VARCHAR(150) DEFAULT NULL,
  `contact_email` VARCHAR(150) NOT NULL,
  `contact_phone` VARCHAR(20)  DEFAULT NULL,
  `location`      VARCHAR(200) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `auth_token`    VARCHAR(64)  DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact_email` (`contact_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- farm_records
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `farm_records` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `farmer_id`     INT(11)      NOT NULL,
  `crop_type`     VARCHAR(100) NOT NULL,
  `season_type`   ENUM('Long Rains','Short Rains','Dry Season','Irrigation') NOT NULL,
  `planting_date` DATE         NOT NULL,
  `harvest_date`  DATE         DEFAULT NULL,
  `growth_stage`  VARCHAR(100) DEFAULT NULL,
  `acreage`       DECIMAL(8,2) DEFAULT NULL,
  `field_name`    VARCHAR(100) DEFAULT NULL,
  `notes`         TEXT         DEFAULT NULL,
  `farm_photo`    VARCHAR(255) DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `farmer_id` (`farmer_id`),
  CONSTRAINT `fk_farm_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- soil_records
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `soil_records` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `farmer_id`       INT(11)      NOT NULL,
  `field_name`      VARCHAR(100) DEFAULT NULL,
  `soil_type`       VARCHAR(100) DEFAULT NULL,
  `ph_level`        DECIMAL(4,2) NOT NULL,
  `moisture_level`  VARCHAR(50)  DEFAULT NULL,
  `test_date`       DATE         NOT NULL,
  `recommendation`  TEXT         DEFAULT NULL,
  `notes`           TEXT         DEFAULT NULL,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `farmer_id` (`farmer_id`),
  CONSTRAINT `fk_soil_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- pest_logs
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pest_logs` (
  `id`                INT(11)      NOT NULL AUTO_INCREMENT,
  `farmer_id`         INT(11)      NOT NULL,
  `pest_name`         VARCHAR(150) NOT NULL,
  `field_name`        VARCHAR(100) DEFAULT NULL,
  `severity`          ENUM('Low','Medium','High') NOT NULL DEFAULT 'Low',
  `date_observed`     DATE         NOT NULL,
  `treatment_applied` TEXT         DEFAULT NULL,
  `status`            ENUM('Active','Resolved') NOT NULL DEFAULT 'Active',
  `pest_image`        VARCHAR(255) DEFAULT NULL,
  `notes`             TEXT         DEFAULT NULL,
  `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `farmer_id` (`farmer_id`),
  CONSTRAINT `fk_pest_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- organization_farmer_mapping
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `organization_farmer_mapping` (
  `id`         INT(11)   NOT NULL AUTO_INCREMENT,
  `org_id`     INT(11)   NOT NULL,
  `farmer_id`  INT(11)   NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `org_farmer` (`org_id`,`farmer_id`),
  KEY `farmer_id` (`farmer_id`),
  CONSTRAINT `fk_map_org`    FOREIGN KEY (`org_id`)    REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_map_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`)       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- advisories
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `advisories` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `org_id`        INT(11)      NOT NULL,
  `title`         VARCHAR(255) NOT NULL,
  `content`       TEXT         NOT NULL,
  `target_region` VARCHAR(200) DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `org_id` (`org_id`),
  CONSTRAINT `fk_advisory_org` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- listings (Marketplace)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `listings` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `farmer_id`     INT(11)      NOT NULL,
  `product_name`  VARCHAR(200) NOT NULL,
  `category`      VARCHAR(100) DEFAULT NULL,
  `description`   TEXT         DEFAULT NULL,
  `quantity`      DECIMAL(10,2) DEFAULT NULL,
  `unit`          VARCHAR(50)  DEFAULT NULL,
  `price`         DECIMAL(10,2) DEFAULT NULL,
  `location`      VARCHAR(200) DEFAULT NULL,
  `listing_image` VARCHAR(255) DEFAULT NULL,
  `status`        ENUM('available','unavailable','sold','archived') NOT NULL DEFAULT 'available',
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `farmer_id` (`farmer_id`),
  KEY `status` (`status`),
  KEY `category` (`category`),
  CONSTRAINT `fk_listing_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- contact_requests (Buyer-Seller Connection)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_requests` (
  `id`               INT(11)      NOT NULL AUTO_INCREMENT,
  `listing_id`       INT(11)      NOT NULL,
  `buyer_farmer_id`  INT(11)      DEFAULT NULL,
  `buyer_name`       VARCHAR(200) DEFAULT NULL,
  `buyer_phone`      VARCHAR(20)  DEFAULT NULL,
  `seller_farmer_id` INT(11)      NOT NULL,
  `status`           ENUM('pending','accepted','declined','cancelled') NOT NULL DEFAULT 'pending',
  `message`          TEXT         DEFAULT NULL,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `listing_id` (`listing_id`),
  KEY `seller_farmer_id` (`seller_farmer_id`),
  KEY `buyer_farmer_id` (`buyer_farmer_id`),
  CONSTRAINT `fk_cr_listing` FOREIGN KEY (`listing_id`)       REFERENCES `listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cr_seller`  FOREIGN KEY (`seller_farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cr_buyer`   FOREIGN KEY (`buyer_farmer_id`)  REFERENCES `farmers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Demo Seed Data (password = "password" for all)
-- --------------------------------------------------------
INSERT IGNORE INTO `farmers` (`name`,`phone`,`email`,`location`,`farm_name`,`acreage`,`password_hash`) VALUES
('Samuel Kipchoge','+254712345678','samuel@kilimotrack.test','Rongo, Migori County','Green Valley Farm',2.5,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Grace Wanjiku','+254722000002','grace@kilimotrack.test','Kirinyaga County','Wanjiku Farm',1.5,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT IGNORE INTO `organizations` (`org_name`,`org_type`,`contact_name`,`contact_email`,`contact_phone`,`location`,`password_hash`) VALUES
('Kenya Agricultural Board','Government Firm','Jane Wanjiru','kab@kilimotrack.test','+254722000001','Nairobi','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT IGNORE INTO `farm_records` (`farmer_id`,`crop_type`,`season_type`,`planting_date`,`harvest_date`,`growth_stage`,`acreage`,`field_name`) VALUES
(1,'Maize','Long Rains','2026-03-15','2026-06-30','Tasseling',2.5,'Field A'),
(1,'Beans','Long Rains','2026-03-20','2026-06-15','Flowering',0.5,'Field B');

INSERT IGNORE INTO `soil_records` (`farmer_id`,`field_name`,`soil_type`,`ph_level`,`moisture_level`,`test_date`,`recommendation`) VALUES
(1,'Field A','Loamy',6.4,'Moderate','2026-07-01','Optimal for crops. No amendments needed.'),
(1,'Field B','Clay Loam',5.8,'High','2026-06-15','Slightly acidic. Apply agricultural lime at 250kg/acre.'),
(1,'Field C','Sandy Loam',7.1,'Low','2026-06-01','Slightly alkaline. Monitor for micronutrient deficiencies.');

INSERT IGNORE INTO `pest_logs` (`farmer_id`,`pest_name`,`field_name`,`severity`,`date_observed`,`treatment_applied`,`status`) VALUES
(1,'Fall Armyworm','Field A','Medium','2026-06-15','Applied Emamectin Benzoate 19 g/L','Active'),
(1,'Aphids','Field B','Low','2026-06-08','Applied Dimethoate 400 EC','Resolved'),
(1,'Cutworms','Field A','High','2026-05-28','Applied Chlorpyrifos soil drench','Resolved'),
(1,'Spider Mites','Field C','Low','2026-05-15','Applied Acaricide','Resolved');

INSERT IGNORE INTO `listings` (`farmer_id`,`product_name`,`category`,`description`,`quantity`,`unit`,`price`,`location`,`status`) VALUES
(1,'Fresh Maize (Dry)','Grains','Premium dry maize from Long Rains 2026 season. Sun-dried, clean, ready for milling.',200,'kg',45.00,'Rongo, Migori County','available'),
(1,'Fresh Beans (Mwitemania)','Legumes','High-quality Mwitemania beans, freshly harvested. Good protein content.',50,'kg',120.00,'Rongo, Migori County','available'),
(2,'Sweet Potatoes','Tubers & Roots','Freshly harvested orange-flesh sweet potatoes. Rich in Vitamin A.',150,'kg',30.00,'Kirinyaga County','available');
