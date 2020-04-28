<?php

use Tygh\Registry;
use Tygh\Models\Company;

function fn_exim_smart_distribution_import_images($prefix, $image_file, $detailed_file, $position, $type, $object_id, $object, $import_options = null)
{
	if ($detailed_file && strpos($detailed_file, '://') !== false) {
		$extensions = [
			'jpg', 'jpeg', 'png', 'bmp'
		];

		$img_url = false;
		foreach ($extensions as $extension) {
			if (stripos($detailed_file, $extensions) !== false) {
				$img_url = true;
				break;
			}
		}

		if (!$img_url) {
			//  if return <img />, get src
			$img_tag = fn_get_contents($detailed_file);
			preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $img_tag, $matches);
			if (isset($matches[1])) {
				$detailed_file = $matches[1];
			}
		}
	}

	fn_exim_import_images($prefix, $image_file, $detailed_file, $position, $type, $object_id, $object, $import_options);
}

function fn_exim_sd_set_product_categories($product_id, $link_type, $categories_data, $category_delimiter, $store_name = '')
{
	if (fn_is_empty($categories_data)) {
		return false;
	}

	$set_delimiter = ';';
	if (fn_allowed_for('ULTIMATE')) {
		$store_delimiter = ':';
		$paths_store = array();
	}

	$paths = array();
	$updated_categories = array();

	foreach ($categories_data as $lang => $data) {
		$_paths = str_getcsv($data, $set_delimiter, "'");
		array_walk($_paths, 'fn_trim_helper');

		foreach ($_paths as $k => $cat_path) {
			if (fn_allowed_for('ULTIMATE') && strpos($cat_path, $store_delimiter)) {
				$cat_path = str_getcsv($cat_path, $store_delimiter, '|');

				if (count($cat_path) > 1) {
					$paths_store[$k] = reset($cat_path);
					$cat_path = $cat_path[1];
				} else {
					$cat_path = reset($cat_path);
				}
			}

			$category = (strpos($cat_path, $category_delimiter) !== false) ? explode($category_delimiter, $cat_path) : array($cat_path);
			foreach ($category as $key_cat => $cat) {
				$paths[$k][$key_cat][$lang] = $cat;
			}
		}
	}

	if (!fn_is_empty($paths)) {
		$category_condition = '';
		$joins = '';
		$select = '?:products_categories.*';
		if (fn_allowed_for('ULTIMATE')) {
			$joins = ' JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id ';
			$category_condition = fn_get_company_condition('?:categories.company_id');
			$select .= ', ?:categories.category_id, ?:categories.company_id';
		}

		$main_category_id = db_get_field(
			'SELECT category_id FROM ?:products_categories WHERE product_id = ?i AND link_type = ?s',
			$product_id, 'M'
		);

		$cat_ids = array();
		$old_data = db_get_hash_array("SELECT $select FROM ?:products_categories $joins WHERE product_id = ?i AND link_type = ?s $category_condition", 'category_id', $product_id, $link_type);
		foreach ($old_data as $k => $v) {
			if ($v['link_type'] == $link_type) {
				$updated_categories[] = $k;
			}
			$cat_ids[] = $v['category_id'];
		}
		if (!empty($cat_ids)) {
			db_query("DELETE FROM ?:products_categories WHERE product_id = ?i AND category_id IN (?n)", $product_id, $cat_ids);
		}
	}

	$company_id = 0;
	if (Registry::get('runtime.company_id')) {
		$company_id = Registry::get('runtime.company_id');
	} else {
		$company_id = fn_get_company_id_by_name($store_name);

		if (fn_allowed_for('ULTIMATE')) {
			if (!$company_id) {
				$company_data = array('company' => $store_name, 'email' => '');
				$company_id = fn_update_company($company_data, 0);
			}
		}
	}

	foreach ($paths as $key_path => $categories) {

		if (!empty($categories)) {
			$parent_id = '0';

			foreach ($categories as $cat) {

				$category_condition = '';
				if (fn_allowed_for('ULTIMATE')) {
					if (!empty($paths_store[$key_path]) && !Registry::get('runtime.company_id')) {
						$path_company_id = fn_get_company_id_by_name($paths_store[$key_path]);
						$category_condition = fn_get_company_condition('?:categories.company_id', true, $path_company_id);
					} else {
						$category_condition = fn_get_company_condition('?:categories.company_id', true, $company_id);
					}
				} elseif (fn_allowed_for('MULTIVENDOR')) {
					$company = Company::model()->find($company_id);
					$plan_categories = ($company->categories) ? explode(',', $company->categories) : array();
					if ($plan_categories) {
						$category_condition = db_quote(' AND ?:categories.category_id IN (?a)', $plan_categories);
					}
				}

				reset($cat);
				$main_lang = key($cat);
				$main_cat = array_shift($cat);
				$category_id = db_get_field("SELECT ?:categories.category_id FROM ?:category_descriptions INNER JOIN ?:categories ON ?:categories.category_id = ?:category_descriptions.category_id $category_condition WHERE ?:category_descriptions.category = ?s AND lang_code = ?s AND parent_id = ?i", $main_cat, $main_lang, $parent_id);

				if (!empty($category_id)) {
					$parent_id = $category_id;
					fn_set_hook('set_product_categories_exist', $category_id);
				} else {

					$category_data = array(
						'parent_id' => $parent_id,
						'category' =>  $main_cat,
						'timestamp' => TIME,
					);

					if (fn_allowed_for('ULTIMATE')) {
						$category_data['company_id'] = !empty($path_company_id) ? $path_company_id : $company_id;
					}

					$category_id = fn_update_category($category_data);

					foreach ($cat as $lang => $cat_data) {
						$category_data = array(
							'parent_id' => $parent_id,
							'category' => $cat_data,
							'timestamp' => TIME,
						);

						if (fn_allowed_for('ULTIMATE')) {
							$category_data['company_id'] = $company_id;
						}

						fn_update_category($category_data, $category_id, $lang);
					}

					$parent_id = $category_id;
				}

			}

			$data = array(
				'product_id' => $product_id,
				'category_id' => $category_id,
				'link_type' => $link_type,
			);

			if (!empty($old_data) && !empty($old_data[$category_id])) {
				$data = fn_array_merge($old_data[$category_id], $data);
			}

			if (!empty($main_category_id) && $main_category_id == $category_id) {
				$data['link_type'] = 'M';
			}

			db_query("REPLACE INTO ?:products_categories ?e", $data);

			$updated_categories[] = $category_id;
		}
	}

	if (!empty($updated_categories)) {
		fn_update_product_count($updated_categories);

		return true;
	}

	return false;
}