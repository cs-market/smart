<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if ($mode == 'm_add_category') {
		$return_url = 'categories.manage';
		$params = $_REQUEST;
		if (!empty($params['add_products_ids']) && !empty($params['category_id'])) {
			foreach ($params['add_products_ids'] as $pid) {
				$data = array('product_id' => $pid, 'category_id' => $params['category_id'], 'link_type' => 'A');
				db_query("REPLACE INTO ?:products_categories ?e", $data);				
			}
			$return_url = "categories.update&category_id=" . $params['category_id'];
        }
        
        return array(CONTROLLER_STATUS_OK, $return_url);
	}
}
