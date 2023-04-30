<?php

$schema['export_fields']['Point price'] = array (
    'db_field' => 'point_price',
    'table' => 'product_point_prices'
);

$schema['references']['product_point_prices'] = [
    'reference_fields' => ['product_id' => '#key', 'lower_limit' => 1, 'usergroup_id' => 0],
    'join_type'        => 'LEFT'
];

return $schema;
