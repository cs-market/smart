<?php
/*****************************************************************************
*                                                                            *
*                   All rights reserved! eCom Labs LLC                       *
* http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
*                                                                            *
*****************************************************************************/

$schema['banners']['content']['items']['fillings']['random_banners'] = array(
    'params' => array (
        'sort_by' => 'random',
        'sort_order' => 'asc'
    ),
    'disable_cache' => true
);
$schema['banners']['cache'] = false; //disable cache

return $schema;