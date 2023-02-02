<?php

$schema['export_fields']['Show out of stock']['db_field'] = 'show_out_of_stock_product';

$schema['export_fields']['Available since'] = [
    'db_field'      => 'avail_since',
    'process_get'   => ['fn_exim_get_optional_timestamp', '#this'],
    'convert_put'   => ['fn_exim_put_optional_timestamp', '#this'],
    'return_result' => true
];

return $schema;
