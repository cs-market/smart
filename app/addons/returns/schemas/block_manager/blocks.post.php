<?php

use Tygh\Registry;

require_once Registry::get('config.dir.addons') . 'returns/schemas/block_manager/blocks.functions.php';

$schema['my_account']['content']['returns_allowed'] = [
    'type' => 'function',
    'function' => ['fn_returns_allowed']
];

return $schema;
