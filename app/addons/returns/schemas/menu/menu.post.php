<?php

use Tygh\Enum\YesNo;
use Tygh\Registry;

if (!Registry::get('runtime.company_id') || YesNo::toBool(db_get_field('SELECT support_returns FROM ?:companies WHERE company_id = ?i', Registry::get('runtime.company_id')))) {
    $schema['central']['orders']['items']['returns'] = array(
        'attrs' => array(
            'class'=>'is-addon'
        ),
        'href' => 'returns.manage',
        'position' => 900,
    );    
}

return $schema;
