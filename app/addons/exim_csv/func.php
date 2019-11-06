<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_exim_csv_place_order($order_id, $action, $order_status, $cart, $auth) {
	$order = fn_get_order_info($order_id);
	fn_define('DB_LIMIT_SELECT_ROW', 30);

	if (db_get_field('SELECT export_order_to_csv FROM ?:companies WHERE company_id = ?i', $order['company_id']) == 'Y') {
		$pattern_id = 'orders_with_items';
		$layout = db_get_row("SELECT ?:exim_layouts.* FROM ?:exim_layouts LEFT JOIN ?:companies ON ?:exim_layouts.name = ?:companies.company WHERE pattern_id = ?s and company_id = ?i", $pattern_id, $order['company_id']);
		if (!empty($layout)) {
			$layout['cols'] = explode(',', $layout['cols']);
			$pattern = fn_exim_get_pattern_definition($pattern_id, 'export');
			$pattern['condition']['conditions'] = fn_array_merge($pattern['condition']['conditions'], array('is_parent_order' => $order['is_parent_order'], 'order_id' => array($order['order_id'])));
			$options = array(
				'delimiter' => 'S',
				'output' => 'S',
				'filename' => 'order_#'.$order['order_id'].'.csv',
			);
			$cid = Registry::get('runtime.company_id');
			Registry::set('runtime.company_id', $order['company_id']);
			$pattern['func_save_content_to_file'] = 'fn_exim_csv_put_csv';
			ob_start(null, 0, PHP_OUTPUT_HANDLER_REMOVABLE);
			fn_export($pattern, $layout['cols'], $options);
			ob_end_clean();
			Registry::set('runtime.company_id', $cid);
		}
	}
}

function fn_exim_csv_put_csv($data, $options, $enclosure)
{
    static $output_started = false;
    $eol = "\n";

    if ($options['delimiter'] == 'C') {
        $delimiter = ',';
    } elseif ($options['delimiter'] == 'T') {
        $delimiter = "\t";
    } else {
        $delimiter = ';';
    }

    fn_mkdir(fn_get_files_dir_path());

    foreach ($data as $k => $v) {
        foreach ($v as $name => $value) {
            $data[$k][$name] = $enclosure . str_replace(array("\r","\n","\t",$enclosure), array('','','',$enclosure.$enclosure), $value) . $enclosure;
        }
        // If a line in a csv file ends with 3 or more double quotes (i.e. """), the mime content type is often
        // determined incorrectly, e.g. by using finfo_file or mime_content_type php functions.
        // To get round it, add an extra space to lines like this:
        if (substr($data[$k][$name], -3) == '"""') {
            $data[$k][$name] .= ' ';
        }
    }

    if ($output_started == false || isset($options['force_header'])) {
        Tygh::$app['view']->assign('fields', array_keys($data[0]));
    } else {
        Tygh::$app['view']->clearAssign('fields');
    }

    Tygh::$app['view']->assign('export_data', $data);
    Tygh::$app['view']->assign('delimiter', $delimiter);
    Tygh::$app['view']->assign('eol', $eol);

    $csv = Tygh::$app['view']->fetch('addons/exim_csv/views/exim/components/export_csv.tpl');

    $fd = fopen(fn_get_files_dir_path() . $options['filename'], ($output_started && !isset($options['force_header'])) ? 'ab' : 'wb');
    if ($fd) {
        fwrite($fd, $csv, strlen($csv));
        fclose($fd);
        @chmod(fn_get_files_dir_path() . $options['filename'], DEFAULT_FILE_PERMISSIONS);
    }

    if ($output_started == false) {
        $output_started = true;
    }

    unset($options['force_header']);

    return true;
}

function fn_exim_csv_mve_import_check_product_data(&$v, $primary_object_id, &$options, &$processed_data, &$skip_record)
{
    if (Registry::get('runtime.company_id')) {
        $v['company_id'] = Registry::get('runtime.company_id');
    }

    if (!empty($primary_object_id['product_id'])) {
        $v['product_id'] = $primary_object_id['product_id'];
    } else {
        unset($v['product_id']);
    }

    // Check the category name
    if (!empty($v['Category'])) {
        if (!fn_mve_import_check_exist_category($v['Category'], $options['category_delimiter'], $v['lang_code'])) {
            unset($v['Category']);
        }
    }

    if (!empty($v['Secondary categories']) && !$skip_record) {
        $delimiter = ';';
        $categories = explode($delimiter, $v['Secondary categories']);
        array_walk($categories, 'fn_trim_helper');

        foreach ($categories as $key => $category) {
            if (!fn_mve_import_check_exist_category($category, $options['category_delimiter'], $v['lang_code'])) {
                unset($categories[$key]);
            }
        }

        $v['Secondary categories'] = implode($delimiter . ' ', $categories);
    }

    return true;
}