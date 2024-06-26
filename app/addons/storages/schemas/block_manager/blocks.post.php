<?php

$schema['storages'] = array(
    'templates' => 'addons/storages/blocks/storages.tpl',
    'wrappers' => 'blocks/wrappers',
);
$schema['storages_popup_picker'] = array(
    'templates' => 'addons/storages/blocks/storages_popup_picker.tpl',
    'wrappers' => 'blocks/wrappers',
);

$schema['product_filters']['cache']['session_handlers'][] = 'settings.storage.value';

return $schema;
