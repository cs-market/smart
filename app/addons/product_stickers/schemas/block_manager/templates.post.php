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

foreach ($schema as $template => &$data) {
    if (isset($data['bulk_modifier']['fn_gather_additional_products_data'])) {
        if (!in_array($template, array('blocks/products/products_small_items.tpl', 'blocks/products/short_list.tpl'))) {
            $data['bulk_modifier']['fn_gather_additional_products_data']['params']['get_stickers_for'] = 'B';
        }
    }
}

return $schema;
