<?php
/***************************************************************************
 * *
 * (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev *
 * *
 * This is commercial software, only users who have purchased a valid *
 * license and accept to the terms of the License Agreement can install *
 * and use this program. *
 * *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE. *
 ****************************************************************************/

$schema['rus_customer_geolocation'] = array(
    'modes' => array(
        'update' => array(
            'permissions' => 'manage_locations',
            'vendor_only' => true,
            'use_company' => true,
        ),
        'manage' => array(
            'permissions' => 'view_locations',
            'vendor_only' => true,
            'use_company' => true,
        ),
    ),
);

return $schema;
