SET @category_id = (SELECT category_id FROM cscart_categories WHERE category_id = 224 OR status = 'A' ORDER BY category_id = 224 DESC LIMIT 1);

REPLACE INTO ?:products_categories (`product_id`, `category_id`, `link_type`, `position`) VALUES (278, @category_id, 'M', 0);
REPLACE INTO ?:products_categories (`product_id`, `category_id`, `link_type`, `position`) VALUES (279, @category_id, 'M', 0);
REPLACE INTO ?:products_categories (`product_id`, `category_id`, `link_type`, `position`) VALUES (280, @category_id, 'M', 0);
REPLACE INTO ?:products_categories (`product_id`, `category_id`, `link_type`, `position`) VALUES (281, @category_id, 'M', 0);
REPLACE INTO ?:products_categories (`product_id`, `category_id`, `link_type`, `position`) VALUES (282, @category_id, 'M', 0);
REPLACE INTO ?:products_categories (`product_id`, `category_id`, `link_type`, `position`) VALUES (283, @category_id, 'M', 0);
REPLACE INTO ?:products_categories (`product_id`, `category_id`, `link_type`, `position`) VALUES (284, @category_id, 'M', 0);

UPDATE cscart_product_features SET company_id = 0;