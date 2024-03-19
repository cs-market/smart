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

$schema['banners']['templates']['addons/aurora/blocks/grid.tpl'] = [
    'settings' => array(
        'number_of_columns' =>  array (
            'type' => 'input',
            'default_value' => 3
        ),
        'limit' =>  array (
            'type' => 'input',
            'default_value' => 0
        ),
        'section_name' =>  array (
            'type' => 'checkbox',
            'default_value' => 'N'
        )
    )
];

$schema['mobile_app_links'] = [
    'templates' => 'addons/aurora/blocks/mobile_app_links.tpl',
    'content' => array(
        'items' => array (
            'remove_indent' => true,
            'hide_label' => true,
            'type' => 'function',
            'function' => ['fn_get_mobile_app_links']
        ),
    ),
    'settings' => array(
        'number_of_columns' =>  array (
            'type' => 'input',
            'default_value' => 3
        )
    ),
    'cache' => array (
        'update_handlers' => array ('companies'),
    ),
    'wrappers' => 'blocks/wrappers'
];

$schema['products']['settings']['hide_add_to_cart_button']['default_value'] = 'N';


$schema['vendor_logo']['content']['vendor_info']['function'] = ['fn_blocks_aurora_get_vendor_info'];

$schema['banners']['templates']['addons/banners/blocks/carousel.tpl']['settings']['scroll_per_page'] = [
    'type' => 'checkbox',
    'default_value' => 'N'
];

$schema['banners']['templates']['addons/banners/blocks/carousel.tpl']['settings']['item_quantity'] = [
    'type' => 'input',
    'default_value' => 1
];

$schema['banners']['templates']['addons/banners/blocks/carousel.tpl']['settings']['navigation']['values']['O'] = 'outside_navigation';

return $schema;
