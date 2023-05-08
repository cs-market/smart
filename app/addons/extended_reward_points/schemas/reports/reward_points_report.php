<?php

$schema = array(
    'company_id' => array(
        'type' => 'company_field',
        'name' => 'company_id',
    ),
    'period' => array(
        'type' => 'period_selector'
    ),
    'type' => array(
        'type' => 'hidden',
        'name' => 'type',
        'value' => 'reward_points_report',
    ),
    'find' => array(
        'type' => 'button',
        'but_name' => 'dispatch[reports.view]',
        'but_role' => 'submit-button',
        'but_text' => __('search'),
        'but_meta' => "pull-left",
    ),
    'export' => array(
        'type' => 'button',
        'but_name' => 'dispatch[reports.view.csv]',
        'but_role' => 'submit-button',
        'but_text' => __('export'),
        'but_meta' => "cm-new-window pull-right",
    ),
);

return $schema;
