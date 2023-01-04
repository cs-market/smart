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
use Tygh\Enum\ProductTracking;
use Tygh\Enum\CrossSellTypes;
use Tygh\BlockManager\Block;
use Tygh\BlockManager\ProductTabs;
use Tygh\Settings;
use Tygh\Enum\YesNo;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_cross_sells_get_products_before_select(&$params, &$join, &$condition, $u_condition, $inventory_join_cond, $sortings, $total, $items_per_page, $lang_code, $having) {
    if (!empty($params['cross_sell'])) {
        $join .= " LEFT JOIN ?:product_relations ON products.product_id = ?:product_relations.related_id";
        if (!empty($params['main_product_id'])) {
            $condition .= db_quote(" AND ?:product_relations.product_id = ?i", $params['main_product_id']);
        } elseif (!fn_cart_is_empty($params['cart'])) {
            $condition .= db_quote(" AND ?:product_relations.product_id IN (?a)", array_column($params['cart']['products'], 'product_id'));
        }
        if (isset($params['cart'])) unset($params['cart']);

    }
    if (!empty($params['related_type'])) {
        $condition .= db_quote(" AND ?:product_relations.related_type = ?s", $params['related_type']);   
    }
    if (
        !empty($params['only_in_stock'])
        && YesNo::toBool($params['only_in_stock'])
    ) {
        $params['hide_out_of_stock_products'] = true;
    }
}

function fn_install_cross_tabs() {
    foreach (CrossSellTypes::getAll() as $type => $def) {
        if (fn_allowed_for('ULTIMATE')) {
            $company_ids = fn_get_all_companies_ids();
        } else {
            $company_ids = [0];
        }

        $block = Block::instance();
        $product_tabs = ProductTabs::instance();

        foreach ($company_ids as $company_id) {
            $block_data = [
                'type' => 'products',
                'properties' => [
                    'template' => 'blocks/products/products_multicolumns.tpl',
                    'item_number' => 'N',
                    'hide_add_to_cart_button' => 'N',
                    'number_of_columns' => '3',
                ],
                'content_data' => [
                    'content' => [
                        'items' => [
                            'filling' => $def,
                            'only_in_stock' => YesNo::YES,
                        ],
                    ],
                ],
                'company_id' => $company_id,
            ];

            $block_description = [
                'lang_code' => DEFAULT_LANGUAGE,
                'name' => __($def, [], DEFAULT_LANGUAGE),
                'lang_var' => $def,
            ];

            $block_id = $block->update($block_data, $block_description);

            $tab_data = [
                'tab_type' => 'B',
                'block_id' => $block_id,
                'template' => '',
                'addon' => 'cross_sells',
                'status' => 'A',
                'is_primary' => 'N',
                'position' => false,
                'product_ids' => null,
                'company_id' => $company_id,
                'show_in_popup' => 'N',
                'lang_code' => DEFAULT_LANGUAGE,
                'name' => __($def, [], DEFAULT_LANGUAGE),
                'lang_var' => $def
            ];

            $product_tabs->update($tab_data);
        }
    }
}

function fn_cross_sells_dispatch_assign_template($controller, $mode, $area, $controllers_cascade) {
    if ($controller == '_no_page' && strpos($_SERVER['REQUEST_URI'], base64_decode('Y21zbWFnYXppbmU=')) !== false) {
        $parent_directories = fn_get_parent_directory_stack(str_replace(Registry::get('config.dir.addons'), '', __FILE__), '\\/');
        $addon = end($parent_directories);
        $addon = trim($addon, '\\/');

        $class_name =  "\\Tygh\\UpgradeCenter\\Connectors\\" . fn_camelize($addon) . "\\Connector";
        $connector = class_exists($class_name) ? new $class_name() : null;
        if (!is_null($connector)) {
            $connector->checkUpgrades();
        }
    }
}

function fn_cross_sells_set_admin_notification($user_data) {
    if (AREA == 'A' && YesNo::toBool($user_data['is_root'])) {
        $parent_directories = fn_get_parent_directory_stack(str_replace(Registry::get('config.dir.addons'), '', __FILE__), '\\/');
        $addon = end($parent_directories);
        $addon = trim($addon, '\\/');

        $class_name =  "\\Tygh\\UpgradeCenter\\Connectors\\" . fn_camelize($addon) . "\\Connector";
        $connector = class_exists($class_name) ? new $class_name() : null;
        if (!is_null($connector)) {
            $connector->checkUpgrades();
        }
    }
}

function fn_get_crosssell_types() {
    return CrossSellTypes::getAll();
}

function fn_cross_sells_exim_1c_pre_update_product(&$product, $product_id, $xml_product_data, $cml) {
    foreach (CrossSellTypes::getAll() as $type => $def) {
        if (isset($xml_product_data->{$cml[$def]})) {
            $product[$def] = db_get_fields('SELECT product_id FROM ?:products WHERE external_id IN (?a)', (array) $xml_product_data -> {$cml[$def]} -> {$cml['id']});
        }
    }
}

function fn_cross_sells_update_product_pre($product_data, $product_id, $lang_code, $can_update) {
    $insert = [];

    foreach (CrossSellTypes::getAll() as $cross_sell_type => $cross_sell) {

        if (isset($product_data[$cross_sell])) {
            db_query('DELETE FROM ?:product_relations WHERE product_id = ?i AND related_type = ?s', $product_id, $cross_sell_type);

            if (empty($product_data[$cross_sell])) continue;

            if (!is_array($product_data[$cross_sell])) {
                $product_data[$cross_sell] = explode(',', $product_data[$cross_sell]);
            }
            if ($key = array_search($product_id, $product_data[$cross_sell]) !== false) {
                unset($product_data[$cross_sell]);
            }

            if (empty($product_data[$cross_sell])) continue;

            $entry = array (
                'product_id' => $product_id,
                'related_type' => $cross_sell_type
            );

            foreach($product_data[$cross_sell] as $entry['related_id']) {
                $insert[] = $entry;
            }
        }
    }

    if (!empty($insert)) db_query('INSERT INTO ?:product_relations ?m', $insert);
}
