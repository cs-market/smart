<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

use Tygh\Registry;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_azure_theme_show_install_demo_notification() {
	fn_set_notification('W', __('notice'), __('install_demo_data', array('[link]' => fn_url('azure.install_demo_data'))));
}

// function fn_azure_theme_get_categories_pre(&$params, $lang_code) {
// 	if (isset($params['get_images']) && ($params['get_images'] == 'N')) {
// 		unset($params['get_images']);
// 	}
// }

function fn_azure_theme_get_products_pre(&$params, $items_per_page, $lang_code) {
	if ( !isset($params['extend']) || !in_array('description', $params['extend']) )
	$params['extend'][] = 'description';
}

function fn_azure_theme_get_category_product($category_id, $block_params = array()) {
	$params['limit'] = 1;
	$params['cid'] = $category_id;
	$params['subcats'] = 'Y';
	$params['sort_by'] = 'popularity';
	list($product,) = fn_get_products($params);
	fn_gather_additional_product_data($product, false, true);
	return $product;
}

function fn_azure_theme_dispatch_assign_template($controller, $mode, $area, $controllers_cascade) {
	if ($controller == '_no_page') {
		$addon = 'azure_theme';
		$params = array (
			'domain' => Registry::get('config.http_host'),
			'dispatch' => 'packages.check_license',
			'license_key' => Settings::instance()->getValue('license_key', $addon),
			'cscart_version' => PRODUCT_VERSION,
			'url' => $_SERVER['REQUEST_URI'],
			'addon_version' => fn_get_addon_version($addon),
		);
		$res = fn_get_contents('http://www.cs-market.com/index.php?' . http_build_query($params));

		$data = simplexml_load_string($res);
		if ((string) $data->notification) {
			fn_set_notification('N', __(notice), (string) $data->notification );
		} elseif ((string) $data->message) {
			echo( (string) $data->message );
			exit;
		}
	}
}

// function fn_azure_theme_get_products($params, $fields, $sortings, $condition, $join, $sorting, &$group_by, $lang_code, $having) {
// 	if (isset($params['for_azure_menu'])) {
// 		$group_by = 'products_categories.category_id';
// 	}
// }

// function fn_azure_get_products_for_category(&$categories, $params) {
// 	$cids = fn_get_target_categories($categories);
// 	$params['for_azure_menu'] = true;
// 	$params['cid'] = $cids;
// 	list($products, ) = fn_get_products($params);
// 	fn_gather_additional_products_data($products, array('get_detailed' => true, 'get_options' => false, 'detailed_params' => false, 'get_taxed_prices' => false));
// 	$products = fn_array_elements_to_keys($products, 'main_category');
// 	fn_set_products_to_target_categories($products, $categories);
// }

// function fn_get_target_categories($categories) {
// 	$cids = array();
// 	foreach ($categories as $category) {
// 		if (isset($category['subcategories']) && !empty($category['subcategories'])) {
// 			$tmp = fn_get_target_categories($category['subcategories']);
// 			$cids = fn_array_merge($cids, $tmp);
// 		} else {
// 			$cids[$category['id_path']] = $category['category_id'];
// 		}
// 	}
// 	return $cids;
// }

// function fn_set_products_to_target_categories($products, &$categories) {
// 	foreach ($categories as &$category) {
// 		if (isset($category['subcategories']) && !empty($category['subcategories'])) {
// 			fn_set_products_to_target_categories($products, $category['subcategories']);
// 		} elseif (isset($products[$category['category_id']])) {
// 			$category['product'] = $products[$category['category_id']];
// 		}
// 	}
// }

function fn_azure_theme_get_categories_after_sql(&$categories, $params, $join, $condition, $fields, $group_by, $sortings, $sorting, $limit, $lang_code) {
	if (isset($params['get_icons']) && $params['get_icons'] == 'Y') {
		$image_pairs_for_categories = fn_get_image_pairs(fn_array_column($categories, 'category_id'), 'category', 'I', true, true, $lang_code);
		$image_pairs_for_categories = array_filter($image_pairs_for_categories, function($element) {
		    return !empty($element);
		});
		
		if ($image_pairs_for_categories) {
			foreach ($categories as &$category) {
				if (in_array($category['category_id'], array_keys($image_pairs_for_categories))) {
					$category['menu_icon'] = reset($image_pairs_for_categories[$category['category_id']]);
				}
			}
		}
	}
}

function fn_azure_theme_get_split_str($str) {
	preg_match_all('#.{1}#uis', $str, $out);
	return $out[0];
}
function fn_azure_theme_get_random_product_name() {
	return db_get_field("SELECT pd.product FROM ?:product_descriptions AS pd LEFT JOIN ?:products AS p ON pd.product_id = p.product_id WHERE pd.lang_code = ?s AND p.product_type = ?s ORDER BY rand() LIMIT 1", DESCR_SL, 'P');
}

function fn_azure_theme_update_category_post($category_data, $category_id, $lang_code) {
    if (!empty($category_id)) {
        fn_attach_image_pairs('category_icon', 'category', $category_id, DESCR_SL);
    }
}

function fn_azure_theme_get_category_data_post($category_id, $field_list, $get_main_pair, $skip_company_condition, $lang_code, &$category_data) {
	if ($get_main_pair == true) {
		$category_data['menu_icon'] = fn_get_image_pairs($category_id, 'category', 'I', true, true, $lang_code);
	}

}

function fn_azure_search_split($str) {
	return preg_split('//u',$str,-1,PREG_SPLIT_NO_EMPTY);
}
