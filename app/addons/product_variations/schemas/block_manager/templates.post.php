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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$schema ['addons/product_variations/blocks/products/variations_list.tpl'] = array(
    'settings' => array(
        'product_variations.hide_add_to_wishlist_button' => array(
            'type' => 'checkbox',
            'default_value' => 'N'
        ),
        'product_variations.show_variation_thumbnails' => array(
            'type' => 'checkbox',
            'default_value' => 'Y'
        ),
        'product_variations.show_product_code' => array(
            'type' => 'checkbox',
            'default_value' => 'Y'
        )
    ),
    'bulk_modifier' => array(
        'fn_product_variations_blocks_prepare_variations_list' => array(
            'products' => '#this',
            'params' => array(
                'get_icon' => true,
                'get_detailed' => true,
                'get_options' => true
            ),
        ),
    ),
);

return $schema;