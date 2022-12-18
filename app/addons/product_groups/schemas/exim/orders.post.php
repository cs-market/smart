<?php

$schema['export_fields']['Product group'] = array (
    'db_field' => 'group_id',
    'linked' => true,
    'convert_put' => array('fn_exim_import_product_group', '#this'),
    'process_get' => array('fn_exim_get_product_group', '#this'),
);

return $schema;
