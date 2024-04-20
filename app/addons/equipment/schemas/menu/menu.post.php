<?php

$schema['top']['administration']['items']['malfunctions'] = [
    'title' => __('equipment.malfunctions'),
    'href' => 'malfunction_types.manage?type='. STATUS_MALFUNCTION,
    'position' => 770,
];
$schema['top']['administration']['items']['malfunctions_divider'] = [
    'type' => 'divider',
    'position' => 780,
];

return $schema;
