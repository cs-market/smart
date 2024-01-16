<?php

use Tygh\Registry;

$schema['category_report'] = [
    'function' => 'fn_generate_category_report',
    'include' => __DIR__ . '/category_report.functions.php',
    'allowed_for' => 'MULTIVENDOR',
    'position' => 200,
    'controls' => [
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
    ],
];

$schema['sales_report'] = [
    'function' => 'fn_generate_sales_report',
    'include' => __DIR__ . '/sales_report.functions.php',
    //'allowed_for' => 'ROOT',
    'position' => 300,
    'controls' => [
        'customer' => array(
            'label' => 'customer',
            'type' => 'customer_picker',
            'name' => 'user_ids',
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
        'only_zero' => array(
            'label' => 'sales_plan.only_zero',
            'type' => 'checkbox',
            'name' => 'only_zero',
            'class' => 'clearfix',
        ),
        'with_purchases' => array(
            'label' => 'sales_plan.with_purchases',
            'type' => 'checkbox',
            'name' => 'with_purchases',
            'class' => 'clearfix',
            'selected' => true,
        ),
        'show_plan' => array(
            'label' => 'sales_plan.show_plan',
            'type' => 'checkbox',
            'name' => 'show_plan',
            'class' => 'clearfix',
        ),
        'show_user_id' => array(
            'label' => 'sales_plan.show_user_id',
            'type' => 'checkbox',
            'name' => 'show_user_id',
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
    ],
];

$schema['unsold_report'] = [
    'include' => __DIR__ . '/unsold_report.functions.php',
    'allowed_for' => 'MULTIVENDOR',
    'position' => 400,
    'controls' => [
        'customer' => array(
            'label' => 'customer',
            'type' => 'customer_picker',
            'name' => 'user_ids',
        ),
        'product' => array(
            'label' => 'product',
            'type' => 'product_picker',
            'name' => 'product_ids',
        ),
        'category' => array(
            'label' => 'category',
            'type' => 'category_picker',
            'name' => 'category_ids',
        ),
        'hide_null' => array(
            'label' => 'sales_plan.hide_null',
            'type' => 'checkbox',
            'name' => 'hide_null',
            'class' => 'clearfix',
            'selected' => true,
        ),
        'summ' => array(
            'label' => 'sales_plan.summ',
            'type' => 'input',
            'name' => 'summ',
        ),
        'period' => array(
            'type' => 'period_selector'
        ),
        'type' => array(
            'type' => 'hidden',
            'name' => 'type',
            'value' => 'unsold_report',
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
        'button_delimeter' => array(
            'type' => 'delimeter',
        ),
    ],
];

if (Registry::get('addons.push_notifications.status') == 'A') {
    $schema['unsold_report']['controls']['push_notifications'] = array(
        'type' => 'button',
        'but_name' => "dispatch[reports.view.export.push_notifications]",
        'but_role' => 'submit-button',
        'but_text' => __('export_push_notifications'),
        'data_url' => 'push_notifications.add&user_ids=',
    );
}

if (Registry::get('addons.newsletters.status') == 'A') {
    $schema['unsold_report']['controls']['newsletters'] = array(
        'type' => 'button',
        'but_name' => 'dispatch[reports.view.export.newsletters]',
        'but_role' => 'submit-button',
        'but_text' => __('export_newsletters'),
        'data_url' => 'newsletters.add&type=N&user_ids=',
    );
}

if (!fn_allowed_for('MULTIVENDOR')) unset($schema['sales_report']['controls']['show_plan']);

return $schema;
