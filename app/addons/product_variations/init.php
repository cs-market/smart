<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

use Tygh\Addons\ProductVariations\ServiceProvider;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

Tygh::$app->register(new ServiceProvider());

fn_register_hooks(
    'poptions_delete_product_pre',
    'get_products_pre',
    'get_products',
    'get_product_data',
    'get_product_data_post',
    'clone_product_data',
    'gather_additional_products_data_params',
    'gather_additional_product_data_before_options',
    'gather_additional_product_data_post',
    'update_product_features_value_pre',
    'reorder_product',
    'update_product_pre',
    'apply_options_rules_post',
    'get_product_code',
    'pre_get_cart_product_data',
    'get_cart_product_data',
    'update_product_amount_pre',
    'check_amount_in_stock_before_check',
    'add_product_to_cart_get_price',
    'add_to_cart',
    'get_cart_product_icon',
    'create_order_details',
    'data_feeds_export',
    'dispatch_before_display',
    'update_cart_products_post',
    'cart_agreements',
    'generate_ekeys_for_edp_post',
    'get_user_edp_post',
    'get_order_info',
    'get_product_files_before_select',
    'update_product_file_post',
    'after_options_calculation',
    'update_product_amount_post',
    'update_product_post',
    'delete_product_pre',
    'delete_product_post',
    'tools_change_status',
    'get_product_data_pre',
    'load_products_extra_data',
    'delete_product_option_before_delete',
    'clone_product_post',
    'get_cart_product_data_post',
    'shippings_get_package_info',
    'pre_add_to_cart',
    'change_approval_status_pre',
    'update_product_option_pre',
    'get_product_options',
    'clone_product_options_post'
);