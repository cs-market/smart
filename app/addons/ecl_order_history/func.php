<?php

use Tygh\Navigation\LastView;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_ecl_order_history_create_log($action, $data = array())
{
    $update = false;

    if (!empty($_SESSION['auth']['user_id'])) {
        $user_id = $_SESSION['auth']['user_id'];
    } else {
        $user_id = 0;
    }

	$order_status_descr = fn_get_simple_statuses(STATUSES_ORDER, true, true);

	$content = array (
		'order' => '# ' . $data['order_id'],
		'id' => $data['order_id'],
	);

	if ($action == 'status') {
		$content['status'] = $order_status_descr[$data['status_from']] . ' -> ' . $order_status_descr[$data['status_to']];
		$content['status_from'] = $data['status_from'];
		$content['status_to'] = $data['status_to'];
	} elseif ($action == 'update') {
		$content['total_from'] = $data['total_from'];
		$content['total_to'] = $data['total_to'];
	}

	$content = serialize($content);
	
    $row = array (
        'user_id' => $user_id,
		'timestamp' => TIME,
		'action' => $action,
		'content' => $content,
		'order_id' => $data['order_id']
	);

	$log_id = db_query("INSERT INTO ?:order_history ?e", $row);

    return true;
}

function fn_ecl_order_history_create_order($order)
{
	$table_status = db_get_row("SHOW TABLE STATUS WHERE name = '?:orders'");

	fn_ecl_order_history_create_log('create', array(
        'order_id' => $table_status['Auto_increment'],
    ));
	return true;
}

function fn_ecl_order_history_change_order_status($status_to, $status_from, $order_info, $force_notification, $order_statuses, $place_order)
{
	fn_ecl_order_history_create_log('status', array (
        'order_id' => $order_info['order_id'],
        'status_from' => $status_from,
        'status_to' => $status_to,
    ));
	return true;
}

function fn_ecl_order_history_delete_order($order_id)
{
	fn_ecl_order_history_create_log('delete', array(
        'order_id' => $order_id,
    ));
	return true;
}

function fn_ecl_order_history_update_order($order, $order_id)
{
	$previous_total = db_get_field("SELECT total FROM ?:orders WHERE order_id = ?i", $order_id);
	fn_ecl_order_history_create_log('update', array(
        'order_id' => $order_id,
		'total_from' => $previous_total,
		'total_to' => $order['total']
    ));
	return true;
}

function fn_get_order_history($params, $items_per_page = 0)
{
	$params = LastView::instance()->update('order_history', $params);

    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $sortings = array (
        'timestamp' => array ('?:order_history.timestamp', '?:order_history.log_id'),
        'user' => array ('?:users.lastname', '?:users.firstname'),
    );

    $fields = array (
        '?:order_history.*',
        '?:users.firstname',
        '?:users.lastname'
    );

    $sorting = db_sort($params, $sortings, 'timestamp', 'desc');

    $join = "LEFT JOIN ?:users USING(user_id)";

    $condition = $limit = '';

	if (!empty($params['order_id'])) {
        $condition .= db_quote(" AND ?:order_history.order_id = ?i", $params['order_id']);
    }
	
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:order_history.log_id)) FROM ?:order_history ?p WHERE 1 ?p", $join, $condition);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $data = db_get_array("SELECT " . join(', ', $fields) . " FROM ?:order_history ?p WHERE 1 ?p $sorting $limit", $join, $condition);

    foreach ($data as $k => $v) {
        $data[$k]['content'] = !empty($v['content']) ? unserialize($v['content']) : array();
    }

	LastView::instance()->processResults('order_history', $data, $params);
	
    return array($data, $params);
}

function fn_ecl_check_order_history_privilege($var = '')
{
	if (ACCOUNT_TYPE != 'admin') {
		return false;
	}
	if ($_SESSION['auth']['is_root'] != 'Y' && !empty($_SESSION['auth']['usergroup_ids'])) {
       $allowed = db_get_fields("SELECT privilege FROM ?:usergroup_privileges WHERE usergroup_id IN(?n)", $_SESSION['auth']['usergroup_ids']);

        if (!in_array('view_order_history', $allowed)) {
            return false;
        }
    }

    return true;
	
}