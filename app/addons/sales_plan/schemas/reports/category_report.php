<?php

$schema = array(
    'user_ids' => array(
        'label' => 'customer',
        'type' => 'customer_picker',
        'name' => 'user_ids',
    ),
    'managers' => array(
        'label' => 'manager',
        'type' => 'manager_selectbox',
        'name' => 'managers',
    ),
    'usergroup_id' => array(
        'label' => 'usergroup',
        'type' => 'usergroup_selectbox',
        'name' => 'usergroup_id',
    ),
    'company_id' => array(
        'type' => 'company_field',
        'name' => 'company_id',
    ),
    'period' => array(
        'type' => 'period_selector'
    ),
    // 'hide_zero' => array(
    //     'label' => 'sales_plan.hide_zero',
    //     'type' => 'checkbox',
    //     'name' => 'hide_zero',
    //     'class' => 'clearfix',
    //     'selected' => true,
    // ),
    'group_by' => array(
        'label' => 'sales_plan.group_by',
        'type' => 'select',
        'name' => 'group_by',
        'variants' => array('month', 'year'),
    ),
    'type' => array(
        'type' => 'hidden',
        'name' => 'type',
        'value' => 'category_report',
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