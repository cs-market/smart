<?php

$schema['bonuses']['give_percent_points'] = array (
    'type' => 'input',
    'function' => array('fn_reward_points_promotion_give_percent_points', '#this', '@cart', '@auth', '@cart_products'),
    'zones' => array('cart'),
    'filter' => 'floatval'
);

$schema['conditions']['csv_users'] = array(
    'type' => 'file',
    'operators' => ['in', 'nin'],
    'zones' => ['cart', 'catalog']
);

return $schema;
