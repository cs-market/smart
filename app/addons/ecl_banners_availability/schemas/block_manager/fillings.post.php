<?php
/*****************************************************************************
*                                                                            *
*                   All rights reserved! eCom Labs LLC                       *
* http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
*                                                                            *
*****************************************************************************/

$schema['random_banners'] = array(
    'limit' => array (
        'type' => 'input',
        'default_value' => 3
    ),
    'item_ids' => array(
        'type' => 'picker',
        'option_name' => 'banners',
        'picker' => 'addons/banners/pickers/banners/picker.tpl',
        'picker_params' => array (
            'type' => 'links',
            'positions' => false
        ),
        'unset_empty' => true,
    )
);

return $schema;