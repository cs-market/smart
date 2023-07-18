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

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'update_product_pre',
    'update_category_pre',
    'gather_additional_product_data_params',
    'gather_additional_product_data_post',
    'gather_additional_products_data_params',
    'update_language_post',
    'delete_languages_post',
    'delete_image',
    'get_product_fields',
    'get_products_pre',
    'get_products',
    'get_product_feature_data_before_select',
    'get_product_features',
    'set_admin_notification',
    'dispatch_assign_template'
);
