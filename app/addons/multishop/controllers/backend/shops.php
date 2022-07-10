<?php

use Tygh\Registry;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($mode == 'update') {
		$shop_id = fn_update_shop($_REQUEST['shop_data'], $_REQUEST['shop_id']);
		$suffix = ".update?shop_id=$shop_id";
	}

	return array(CONTROLLER_STATUS_OK, 'shops' . $suffix);
}

if ($mode == 'get_shops_list') {
    // Check if we trying to get list by non-ajax
    if (!defined('AJAX_REQUEST')) {
        return array(CONTROLLER_STATUS_REDIRECT, fn_url());
    }

    $params = array_merge(array(
        'render_html' => 'Y'
    ), $_REQUEST);
    $condition = '';
    $pattern = !empty($params['pattern']) ? $params['pattern'] : '';
    $start = !empty($params['start']) ? $params['start'] : 0;
    $limit = (!empty($params['limit']) ? $params['limit'] : 10) + 1;

    if (AREA == 'C') {
        $condition = " AND status = 'A' ";
    }

    if (Registry::get('runtime.company_id')) {
        $condition .= db_quote(" AND company_id = ?i", Registry::get('runtime.company_id'));
    }

    fn_set_hook('get_shops_list', $condition, $pattern, $start, $limit, $params);

    $objects = db_get_hash_array("SELECT shop_id as value, shop AS name, CONCAT('switch_shop_id=', shop_id) as append FROM ?:shops WHERE 1 $condition AND shop LIKE ?l ORDER BY shop LIMIT ?i, ?i", 'value', $pattern . '%', $start, $limit);

    if (defined('AJAX_REQUEST') && sizeof($objects) < $limit) {
        Tygh::$app['ajax']->assign('completed', true);
    } else {
        array_pop($objects);
    }

    if (empty($params['start']) && empty($params['pattern'])) {
        $all_vendors = array();

        if (!empty($params['show_all']) && $params['show_all'] == 'Y') {
            $all_vendors[0] = array(
                'name' => empty($params['default_label']) ? __('all_vendors') : __($params['default_label']),
                'value' => (!empty($params['search']) && $params['search'] == 'Y') ? '' : 0,
            );
        }

        $objects = $all_vendors + $objects;
    }
    foreach ($objects as &$object) {
    	$object['url'] = fn_query_remove($params['curl'], 'switch_shop_id') . "&" . $object['append'];
    }

    Tygh::$app['ajax']->assign('objects', $objects);

    if (defined('AJAX_REQUEST') && !empty($params['action'])) {
        Tygh::$app['ajax']->assign('action', $params['action']);
    }

    if (!empty($params['onclick'])) {
        Tygh::$app['view']->assign('onclick', $params['onclick']);
    }
    Tygh::$app['view']->assign('objects', $objects);
    Tygh::$app['view']->assign('id', $params['result_ids']);

    if ($params['render_html'] === 'Y') {
        Tygh::$app['view']->display('addons/multishop/common/shop_select_object.tpl');
    }
    exit;
} elseif ($mode == 'manage') {
	$params = $_REQUEST;
	list($shops, $search) = fn_get_shops($params, Registry::get('settings.Appearance.admin_elements_per_page'));

	Tygh::$app['view']->assign('shops', $shops);
    Tygh::$app['view']->assign('search', $search);
} elseif ($mode == 'update' || $mode == 'add') {
	if ($mode == 'update') {
		$params = $_REQUEST;
		list($shops, $search) = fn_get_shops($params, Registry::get('settings.Appearance.admin_elements_per_page'));

		Tygh::$app['view']->assign('shop_data', array_shift($shops));
	}

    $tabs['detailed'] = array(
        'title' => __('general'),
        'js' => true
    );
    $tabs['addons'] = array(
        'title' => __('addons'),
        'js' => true
    );
    Registry::set('navigation.tabs', $tabs);
}