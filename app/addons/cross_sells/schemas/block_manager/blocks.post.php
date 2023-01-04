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

use Tygh\Enum\CrossSellTypes;

$schema['products']['content']['items']['fillings']['similar'] = $schema['products']['content']['items']['fillings']['recommended'] = [
    'params' => [
        'cross_sell' => true,
        'related_type' => CrossSellTypes::RECOMMENDED,
        'request' => ['main_product_id' => '%PRODUCT_ID%'],
        'session' => ['cart' => '%CART%'],
    ]
];

$schema['products']['content']['items']['fillings']['similar']['params']['related_type'] = CrossSellTypes::SIMILAR;

$schema['products']['cache']['request_handlers'][] = '%PRODUCT_ID%';

return $schema;
