<?php

$schema['import_get_primary_object_id'] = array(
    'fill_primary_object_company_id' => array(
        'function' => 'fn_exim_apply_company',
        'args' => array('$pattern', '$alt_keys', '$object', '$skip_get_primary_object_id'),
        'import_only' => true,
    ),
);

return $schema;
