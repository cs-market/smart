<?php

$schema = array(
    'user_id' => array(
        'label' => 'customer',
        'type' => 'customer_picker',
        'name' => 'user_id',
    ),
    // 'managers' => array(
    //     'label' => 'manager',
    //     'type' => 'manager_selectbox',
    //     'name' => 'managers',
    // ),
    // 'usergroup_id' => array(
    //     'label' => 'usergroup',
    //     'type' => 'usergroup_selectbox',
    //     'name' => 'usergroup_id',
    // ),
    'rating_value' => array(
        'label' => 'rating',
        'type' => 'select',
        'name' => 'rating_value',
        'variants' => array_merge(['---'],range(1, 5)),
    ),
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
        'value' => 'order_reviews_report',
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
