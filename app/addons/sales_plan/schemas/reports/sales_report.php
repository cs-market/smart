<?php

$schema = array(
    'customer' => array(
        'label' => 'customer',
        'type' => 'customer_picker',
        'name' => 'user_ids',
    ),
    'manager' => array(
        'label' => 'manager',
        'type' => 'manager_selectbox',
        'name' => 'managers',
    ),
    'usergroup' => array(
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
    'summ' => array(
        'label' => 'sales_plan.summ',
        'type' => 'checkbox',
        'name' => 'summ',
        'class' => 'clearfix',
        'selected' => true,
    ),
    'amount' => array(
        'label' => 'sales_plan.amount',
        'type' => 'checkbox',
        'name' => 'amount',
        'class' => 'clearfix',
        'selected' => true,
    ),
    'average' => array(
        'label' => 'sales_plan.average',
        'type' => 'checkbox',
        'name' => 'average',
        'class' => 'clearfix',
        'selected' => true,
    ),
    'only_zero' => array(
        'label' => 'sales_plan.only_zero',
        'type' => 'checkbox',
        'name' => 'only_zero',
        'class' => 'clearfix',
    ),
    'group_by' => array(
        'label' => 'sales_plan.group_by',
        'type' => 'select',
        'name' => 'group_by',
        'variants' => array('day', 'week', 'month'),
    ),
    'type' => array(
        'type' => 'hidden',
        'name' => 'type',
        'value' => 'sales_report',
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