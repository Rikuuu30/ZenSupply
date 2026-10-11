-- Zen Supply POS schema export for MariaDB / MySQL.
-- This file contains schema and a generic local admin only; it intentionally
-- does not export the live customer/staff records from rikuuuDB.
-- Existing installations should use `php spark migrate` and must not import
-- this file over their current database.
CREATE DATABASE IF NOT EXISTS `rikuuuDB`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rikuuuDB`;

CREATE TABLE `customers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_active_index` (`is_active`),
  KEY `customers_name_index` (`full_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'staff',
  `password_hash` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_active_role_index` (`is_active`,`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sku` VARCHAR(40) NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `stock_quantity` INT UNSIGNED NOT NULL DEFAULT 0,
  `low_stock_threshold` INT UNSIGNED NOT NULL DEFAULT 5,
  `image` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_active_index` (`is_active`),
  KEY `products_stock_index` (`stock_quantity`,`low_stock_threshold`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sales` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `customer_id` INT NULL,
  `sold_by` INT NOT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `customer_name` VARCHAR(100) NULL,
  `sold_by_name` VARCHAR(100) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(12,2) NOT NULL,
  `idempotency_token` CHAR(64) NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_idempotency_unique` (`idempotency_token`),
  KEY `sales_product_index` (`product_id`),
  KEY `sales_customer_index` (`customer_id`),
  KEY `sales_staff_index` (`sold_by`),
  KEY `sales_created_index` (`created_at`),
  CONSTRAINT `sales_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `sales_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON UPDATE RESTRICT ON DELETE SET NULL,
  CONSTRAINT `sales_staff_fk` FOREIGN KEY (`sold_by`) REFERENCES `users` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional fresh-install administrator. Run `php spark migrate` after import.
-- The password is a hash; use the local coursework demo password stated in README.
INSERT INTO `users` (`username`, `full_name`, `role`, `password_hash`, `created_at`) VALUES
  ('admin.rikuuu', 'Zen Supply Administrator', 'admin', '$2y$10$5L/sGpd9CTJbsuwXbv48Fe/utSEmOmyQ1FFvMQjHUUnuvfzWEOraG', NOW());

-- Optional example catalog. Replace these sample specifications, prices, and
-- quantities with verified store data before selling to real customers.
INSERT INTO `products` (`sku`, `name`, `description`, `price`, `stock_quantity`, `low_stock_threshold`, `image`, `is_active`, `created_at`) VALUES
('ZS-GLV-R14', 'Red Core Training Gloves · 14 oz', 'Boxing gloves · 14 oz · red synthetic-leather shell, layered impact foam, attached thumb, wide hook-and-loop wrist closure, and wipe-clean lining. Suitable for supervised bag work and pad drills. SAMPLE CATALOG ITEM — verify specifications, price, and stock before retail use.', 1850.00, 10, 4, 'assets/images/catalog/red-training-gloves.jpg', 1, NOW()),
('ZS-GLV-BK16', 'Black Core Sparring Gloves · 16 oz', 'Boxing gloves · 16 oz · black synthetic-leather shell, firm layered padding, attached thumb, and broad adjustable wrist strap. A heavier training option for supervised sparring sessions. SAMPLE CATALOG ITEM — verify specifications, price, and stock before retail use.', 1990.00, 8, 4, 'assets/images/catalog/black-sparring-gloves.jpg', 1, NOW()),
('ZS-GLV-BL12', 'Blue Core Training Gloves · 12 oz', 'Boxing gloves · 12 oz · blue synthetic-leather shell with layered foam padding, attached thumb, and hook-and-loop wrist support. Intended for bag and mitt work. SAMPLE CATALOG ITEM — verify specifications, price, and stock before retail use.', 1790.00, 9, 4, 'assets/images/catalog/blue-training-gloves.jpg', 1, NOW()),
('ZS-WRP-180', 'Cotton Hand Wraps · 180 in Pair', 'Hand protection · pair of 180-inch cotton-blend wraps with a thumb loop and hook-and-loop tab. Reusable, washable, and designed to add wrist and knuckle support beneath training gloves. SAMPLE CATALOG ITEM — verify specifications, price, and stock before retail use.', 350.00, 18, 6, 'assets/images/catalog/cotton-hand-wraps.jpg', 1, NOW()),
('ZS-SHG-M-BK', 'Striker Shin Guards · Medium', 'Muay Thai shin guards · medium pair · black synthetic-leather exterior, extended shin and instep coverage, high-density padding, and adjustable rear straps. Wipe clean and air dry after training. SAMPLE CATALOG ITEM — verify size, specifications, price, and stock before retail use.', 2250.00, 8, 3, 'assets/images/catalog/shin-guards.jpg', 1, NOW()),
('ZS-GI-KAR-04', 'White Karate Gi · Size 4', 'Karate uniform · size 4 sample · white lightweight cotton-blend jacket and trousers with reinforced seams and an included white belt. Confirm the maker’s size chart and fabric composition before sale. SAMPLE CATALOG ITEM — verify specifications, price, and stock before retail use.', 1650.00, 12, 4, 'assets/images/catalog/karate-gi.jpg', 1, NOW()),
('ZS-SHT-M-BL', 'Blue Muay Thai Shorts · Medium', 'Training shorts · medium sample · blue satin-look polyester shell, elastic waistband with drawcord, side vents, and relaxed leg openings for movement. Wash according to the verified garment label. SAMPLE CATALOG ITEM — verify size, material, price, and stock before retail use.', 1450.00, 14, 5, 'assets/images/catalog/muay-thai-shorts.jpg', 1, NOW()),
('ZS-MIT-PAIR-RB', 'Coach Focus Mitts · Red / Black Pair', 'Focus mitts · pair · curved striking surface, padded palm and wrist area, and adjustable hand straps for partner drills. Inspect stitching and padding before each session. SAMPLE CATALOG ITEM — verify specifications, price, and stock before retail use.', 1850.00, 6, 2, 'assets/images/catalog/coach-focus-mitts.jpg', 1, NOW()),
('ZS-MGD-ADULT', 'Adult Training Mouthguard with Case', 'Protective equipment · adult boil-and-bite style sample mouthguard supplied with a ventilated storage case. Follow the manufacturer fitting directions; not a substitute for dental advice. SAMPLE CATALOG ITEM — verify safety details, price, and stock before retail use.', 450.00, 20, 6, 'assets/images/catalog/mouthguard.jpg', 1, NOW()),
('ZS-BLT-BLK-280', 'Black Woven Martial Arts Belt · 280 cm', 'Uniform accessory · 280 cm woven black belt sample, approximately 4 cm wide, with reinforced stitching for regular practice. Check length and grading requirements with the intended discipline. SAMPLE CATALOG ITEM — verify dimensions, price, and stock before retail use.', 420.00, 15, 5, 'assets/images/catalog/black-belt.jpg', 1, NOW()),
('ZS-ROP-SPD-3M', 'Adjustable Speed Rope · 3 m', 'Conditioning equipment · adjustable rope up to 3 m with textured handles and a coated cable for indoor cardio and footwork drills. Adjust carefully and avoid abrasive surfaces. SAMPLE CATALOG ITEM — verify cable construction, price, and stock before retail use.', 650.00, 12, 4, 'assets/images/catalog/speed-rope.jpg', 1, NOW()),
('ZS-BAG-100-RD', 'Hanging Heavy Bag · 100 cm', 'Striking equipment · 100 cm red synthetic-cover heavy-bag sample with reinforced hanging points. Delivered unfilled in this example; mounting hardware and safe installation must be confirmed separately. SAMPLE CATALOG ITEM — verify dimensions, filling, mounting, price, and stock before retail use.', 5500.00, 3, 1, 'assets/images/catalog/heavy-bag.jpg', 1, NOW());
