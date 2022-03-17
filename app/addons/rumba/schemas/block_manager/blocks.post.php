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

$schema['banners']['templates']['addons/rumba/blocks/grid.tpl'] = [
    'settings' => array(
        'number_of_columns' =>  array (
            'type' => 'input',
            'default_value' => 3
        )
    )
];

$schema['mobile_app_links'] = [
    'templates' => 'addons/rumba/blocks/mobile_app_links.tpl',
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

$schema['vendor_logo']['content']['vendor_info']['function'] = ['fn_blocks_rumba_get_vendor_info'];

$schema['banners']['templates']['addons/banners/blocks/carousel.tpl']['settings']['scroll_per_page'] = [
    'type' => 'checkbox',
    'default_value' => 'N'
];

$schema['banners']['templates']['addons/banners/blocks/carousel.tpl']['settings']['item_quantity'] = [
    'type' => 'input',
    'default_value' => 1
];

return $schema;