<?php

$schema['products']['content']['items']['fillings']['you_also_bought'] = array (
    'params' => array (
        'sort_by' => 'amnt',
        'sort_order' => 'desc',
        'session' => array(
            'current_cart_products' => '%CART%'
        ),
        'auth' => array(
            'user_id' => '%USER_ID%'
        ),
    ),
);

$schema['products']['cache']['update_handlers'][] = 'orders';

return $schema;
