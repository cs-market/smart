ALTER TABLE `cscart_products` ADD `earned_points_eq_price` CHAR(1) NOT NULL DEFAULT 'N' AFTER `is_pbp`;
ALTER TABLE `cscart_categories` ADD `earned_points_eq_price` CHAR(1) NOT NULL DEFAULT 'N' AFTER `is_op`;
