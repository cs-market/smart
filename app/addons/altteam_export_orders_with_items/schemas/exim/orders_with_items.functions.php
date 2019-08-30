<?php

use Tygh\Registry;

function fn_exim_orders_with_items_get($field, $key, $data)
{
	static $orders;

	if (in_array($key, (array)$orders[$field])) {
		return '';
	} else {
		$orders[$field][] = $key;
		return $data;
	}

}

//
// Get order data information
// Parameters:
// @order_id - order ID
// @type - type of information
function fn_exim_orders_with_items_get_data($field, $order_id, $type)
{
	static $orders;

	$key = $order_id;
	if (in_array($key, (array)$orders[$field])) {
		return '';
	} else {
		$orders[$field][] = $key;

	    $data = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s", $order_id, $type);
	    if (!empty($data)) {

	        // Payment information
	        if ($type == 'P') {
	            $data = @unserialize(fn_decrypt_text($data));
	        // Coupons, Taxes and Shipping information
	        } elseif (strpos('CTL', $type) !== false) {
	            $data = @unserialize($data);
	        }

	        return fn_exim_json_encode($data);
	    }

	}

}

function fn_exim_orders_with_items_get_extra_fields($field, $order_id, $lang_code = CART_LANGUAGE)
{
	static $orders;

	$key = $order_id;
	if (in_array($key, (array)$orders[$field])) {
		return '';
	} else {
		$orders[$field][] = $key;

	    $fields = array();
	    $_user = db_get_array("SELECT d.description, f.value, a.section FROM ?:profile_fields_data as f LEFT JOIN ?:profile_field_descriptions as d ON d.object_id = f.field_id AND d.object_type = 'F' AND d.lang_code = ?s LEFT JOIN ?:profile_fields as a ON a.field_id = f.field_id WHERE f.object_id = ?i  AND f.object_type = 'O'", $lang_code, $order_id);

	    if (!empty($_user)) {
	        foreach ($_user as $field) {
	            if ($field['section'] == 'B') {
	                $type = 'billing';
	            } elseif ($field['section'] == 'S') {
	                $type = 'shipping';
	            } else {
	                $type = 'user';
	            }

	            $fields[$type][$field['description']] = $field['value'];
	        }
	    }

	    if (!empty($fields)) {
	        return fn_exim_json_encode($fields);
	    }

	    return '';
	}

}

function fn_exim_orders_with_items_get_docs($field, $order_id, $type)
{
	static $orders;

	$key = $order_id;
	if (in_array($key, (array)$orders[$field])) {
		return '';
	} else {
		$orders[$field][] = $key;

	    $data = db_get_field("SELECT doc_id FROM ?:order_docs WHERE order_id = ?i AND type = ?s", $order_id, $type);
	    if (!empty($data)) {
	        return $data;
	    }

	}


}

function fn_orders_with_items_timestamp_to_date($field, $key, $timestamp)
{
	static $orders;

	if (in_array($key, (array)$orders[$field])) {
		return '';
	} else {
		$orders[$field][] = $key;
	    return !empty($timestamp) ? date('d M Y H:i:s', intval($timestamp)) : '';
	}	
}

function fn_orders_with_items_ip_from_db($field, $key, $ip)
{
	static $orders;

	if (in_array($key, (array)$orders[$field])) {
		return '';
	} else {
		$orders[$field][] = $key;

	    // Empty or not encoded IP
	    if (empty($ip) || strpos($ip, '.') !== false || strpos($ip, ':') !== false) {
	        return $ip;
	    }

	    return inet_ntop(hex2bin($ip));

	}	
}

function fn_orders_with_items_get_company_name($field, $key, $company_id, $zero_company_name_lang_var = '')
{
	static $orders;

	if (in_array($key, (array)$orders[$field])) {
		return '';
	} else {
		$orders[$field][] = $key;

	    static $cache_names = array();

	    if (empty($company_id)) {
	        return __($zero_company_name_lang_var);
	    }

	    if (!isset($cache_names[$company_id])) {
	        if (Registry::get('runtime.company_id') === $company_id) {
	            $cache_names[$company_id] = Registry::get('runtime.company_data.company');
	        } else {
	            $cache_names[$company_id] = db_get_field("SELECT company FROM ?:companies WHERE company_id = ?i", $company_id);
	        }
	    }

	    return $cache_names[$company_id];
	}

}

/**
 * Get item extra information
 * @param array $data extra data
 * @return json-encoded data on success or empty string on failure
 */
function fn_exim_orders_with_items_get_extra($data)
{
    if (!empty($data)) {
        $data = @unserialize($data);
        return fn_exim_json_encode($data);
    }

    return '';
}


function fn_exim_orders_with_items_get_product_name($data)
{
    if (!empty($data)) {
        $data = @unserialize($data);
        if (!empty($data['product'])) {
        	return $data['product'];
        }
    }

    return '';
}

function fn_exim_orders_with_items_get_option_names($data)
{
    if (!empty($data)) {
        $data = @unserialize($data);
        if (!empty($data['product_options']) && !empty($data['product_options_value'])) {
        	$_names = array();
        	foreach ($data['product_options_value'] as $k => $v) {
        		$_names[] = $v['option_name'];	
        	}

        	return implode(',', $_names);
        }
    }

    return '';
}

function fn_exim_orders_with_items_get_variant_names($data)
{
    if (!empty($data)) {
        $data = @unserialize($data);
        if (!empty($data['product_options']) && !empty($data['product_options_value'])) {
        	$_names = array();
        	foreach ($data['product_options_value'] as $k => $v) {
        		$_names[] = $v['variant_name'];	
        	}

        	return implode(',', $_names);
        }
    }

    return '';
}

