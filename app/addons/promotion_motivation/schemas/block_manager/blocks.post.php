<?php

defined('BOOTSTRAP') or die('Access denied');

$schema['products']['content']['items']['fillings']['promotion_products'] = [
    'params' => [
        'request' => [
            'promotion_pid' => '%PRODUCT_ID%'
        ]
    ]
];

$schema['products']['cache']['update_handlers'][] = 'promotions';

return $schema;
