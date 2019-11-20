<?php

use Tygh\Models\VendorPlan;
use Tygh\BlockManager\Block;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'clone') {

        if (!empty($_REQUEST['clone_category']['category_id'])) {
            $clone_category = $_REQUEST['clone_category'];
            $_SESSION['cloned_products'] = array();
            $cdata = fn_clone_category($clone_category['category_id'], $clone_category);
			unset($_SESSION['cloned_products']);
            if (!empty($cdata['category_id'])) {
                $cid = $cdata['category_id'];
                return array(CONTROLLER_STATUS_REDIRECT, 'categories.update&category_id=' . $cid);
            }

        }
    }

    return array(CONTROLLER_STATUS_OK);
}

if ($mode == 'search') {
    $params = $_REQUEST;
    if (!empty($params['company_id'])) {
        $cids = db_get_field("SELECT categories FROM ?:vendor_plans AS vp LEFT JOIN ?:companies AS c ON vp.plan_id = c.plan_id WHERE company_id = ?i", $params['company_id']);
        $params['category_ids'] = explode(',', $cids);
    }

    list($categories, $search) = fn_get_categories($params);
    Tygh::$app['view']->assign('categories_tree', $categories);
    Tygh::$app['view']->assign('search', $search);
}

/*
 *
 * $category_id Int category from
 * $params Array:
 *  parent_id - {0|parent_id} for subcat
 *  clone_subcat {Y|N}
 *  clone_products {no_copy|copy|clone}
 *  company_id {0|vendor_id}
 *
 */
function fn_clone_category($category_id, $params = [])
{
  $params = array_merge(
    [
      'parent_id' => 0,
      'clone_subcat' => 'N',
      'clone_products' => 'copy',
      'company_id' => 0,
    ],
    $params
  );
  $cloned_products = &$_SESSION['cloned_products'];
  //  get vendor plan usergroups
  if ($params['company_id']) {
    static $vendor_plan;
    if (!$vendor_plan) {
      $vendor_plan = VendorPlan::model()->find(array('company_id' => $params['company_id']));
    }

    $params['company_usergroup_ids'] = $vendor_plan->usergroup_ids;
    $params['company_plan_id'] = $vendor_plan->plan_id;
  }

  // Clone main data
  $main_data = $data = db_get_row("SELECT * FROM ?:categories WHERE category_id = ?i", $category_id);

  unset($data['category_id']);
  $data['status'] = 'D';
  $data['timestamp'] = time();

  if ($params['parent_id']) {
    $data['parent_id'] = $params['parent_id'];
  }

  if ($params['company_id']) {
    $data['usergroup_ids'] = $params['company_usergroup_ids'];
  }

  $cid = db_query("INSERT INTO ?:categories ?e", $data);

  // update id_path
  $id_paths = explode('/', $data['id_path']);
  array_pop($id_paths);

  if ($params['parent_id']) {
    array_pop($id_paths);
    $id_paths[] = $params['parent_id'];
  }

  $id_paths[] = $cid;
  $id_path = implode('/', $id_paths);

  db_query("UPDATE ?:categories SET id_path = ?s WHERE category_id = ?i", $id_path, $cid);

  // add category to vendor plan
  if ($params['company_id']) {
    $vendor_plan_categories = db_get_field("SELECT categories FROM ?:vendor_plans WHERE plan_id = ?i", $params['company_plan_id']);

    $new_vendor_plan_categories = implode(
      ',',
      array_unique(
        array_merge(
          explode(',', $vendor_plan_categories),
          [$cid]
        )
      )
    );

    db_query("UPDATE ?:vendor_plans SET categories = ?s WHERE plan_id = ?i", $new_vendor_plan_categories, $params['company_plan_id']);
  }

  // Clone descriptions
  $data = db_get_array("SELECT * FROM ?:category_descriptions WHERE category_id = ?i", $category_id);
  foreach ($data as $v) {
      $v['category_id'] = $cid;
      if ($v['lang_code'] == CART_LANGUAGE) {
          $orig_name = $v['category'];
          $new_name = $v['category'].' [CLONE]';
      }

      $v['category'] .= ' [CLONE]';
      db_query("INSERT INTO ?:category_descriptions ?e", $v);
  }

  // category_vendor_product_count
  $data = db_get_array("SELECT * FROM ?:category_vendor_product_count WHERE category_id = ?i", $category_id);
  foreach ($data as $v) {
      $v['category_id'] = $cid;
      db_query("INSERT INTO ?:category_vendor_product_count ?e", $v);
  }

  // add products
  if ($params['clone_products'] == 'copy'
    || $params['clone_products'] == 'clone') {
    $data = db_get_array("SELECT * FROM ?:products_categories WHERE category_id = ?i", $category_id);
    foreach ($data as $v) {

      if ($params['clone_products'] == 'clone') {
      	if (isset($cloned_products[$v['product_id']])) {
      		$clone_product = $cloned_products[$v['product_id']];
      	} elseif (in_array($v['product_id'], fn_array_column($cloned_products, 'orig_product_id', 'product_id'))) {
      		$key = array_search($v['product_id'], fn_array_column($cloned_products, 'orig_product_id', 'product_id'));
      		$clone_product = $cloned_products[$key];
      	} else {
			$clone_product = fn_clone_product($v['product_id']);
      		$cloned_products[$clone_product['product_id']] = $clone_product;
      		$cloned_products[$clone_product['product_id']]['orig_product_id'] = $v['product_id'];
      	}

        if (!$clone_product) {
          continue;
        }

        $v['product_id'] = $clone_product['product_id'];

        if ($params['company_id']) {
          //  only vendor usergroups
          db_query("UPDATE ?:products SET usergroup_ids = ?s AND company_id = ?i WHERE product_id = ?i", $params['company_usergroup_ids'], $params['company_id'], $v['product_id']);

          // change vendor
          db_query("UPDATE ?:products SET company_id = ?i WHERE product_id = ?i", $params['company_id'], $v['product_id']);

          //  update product category
          db_query("UPDATE ?:products_categories SET category_id = ?i WHERE product_id = ?i AND category_id = ?i", $cid, $v['product_id'], $v['category_id']);
        }
      } else {
        // add vendor usergroups
        if ($params['company_id']) {
          $product_usergroup_ids = db_get_field("SELECT usergroup_ids FROM ?:products WHERE product_id = ?i", $v['product_id']);

          $new_product_usergroup_ids = implode(
            ',',
            array_unique(
              array_merge(
                explode(',', $product_usergroup_ids), explode(',', $params['company_usergroup_ids'])
              )
            )
          );

          db_query("UPDATE ?:products SET usergroup_ids = ?s WHERE product_id = ?i", $new_product_usergroup_ids, $v['product_id']);
        }

        //  add product category
        $v['category_id'] = $cid;
        db_query("INSERT INTO ?:products_categories ?e", $v);
      }

    }

    fn_update_product_count([$category_id, $cid]);
  }

  // Clone blocks
  Block::instance()->cloneDynamicObjectData('category', $category_id, $cid);

  // Clone images
  fn_clone_image_pairs($cid, $category_id, 'category');

  // bonus count reward_points
  $reward_points = fn_get_reward_points($category_id, CATEGORY_REWARD_POINTS);

  if (!empty($reward_points)) {
    foreach ($reward_points as $v) {
      unset($v['reward_point_id']);
      fn_add_reward_points($v, $cid, CATEGORY_REWARD_POINTS);
    }
  }

  //  copy child categories
  if ($params['clone_subcat'] == 'Y') {
    $child_categories = fn_get_categories_tree($category_id);
    $_params = array_merge(
      $params,
      ['parent_id' => $cid]
    );
    foreach ($child_categories as $child_category) {
      fn_clone_category($child_category['category_id'], $_params);
    }
  }

  return array('category_id' => $cid);
}
