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

if (Registry::get('runtime.company_id')) {
    unset($schema['central']['vendors']);
}

$tickets = Registry::getOrSetCache(
    'fn_get_tickets',
    ['helpdesk_messages', 'helpdesk_tickets'],
    'locale_auth',
    static function () {
        list($tickets) = fn_get_tickets(['status' => 'N']);
        return $tickets;
    }
);

if (!empty($tickets)) {
    $schema['central']['helpdesk']['attrs']['class'] = $schema['central']['helpdesk']['items']['new_tickets']['attrs']['class'] = 'notify-dot';
}

return $schema;
