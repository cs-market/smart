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

$schema['blocks/categories/categories_dropdown_vertical.tpl']['settings']['get_icons'] = array('type' => 'checkbox', 'default_value' => 'Y');
//$schema['blocks/categories/categories_dropdown_vertical.tpl']['bulk_modifier']['fn_azure_get_products_for_category'] = array('categories' => '#this', 'params' => array ());
$schema['blocks/products/products_multicolumns.tpl']['settings']['show_list_buttons'] = array('type' => 'checkbox', 'default_value' => 'Y');
unset($schema['blocks/products/products_multicolumns.tpl']['settings']['item_number']);
$schema['blocks/static_templates/search.tpl']['settings']['seacrch_string_random_product'] = array('type' => 'checkbox');

return $schema;
