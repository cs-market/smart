<?php

use Tygh\Registry;

$schema = array(
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
);

if (Registry::get('addons.push_notifications.status') == 'A') {
    $schema['push_notifications'] = array(
        'type' => 'button',
        'but_name' => "dispatch[reports.view.export.push_notifications]",
        'but_role' => 'submit-button',
        'but_text' => __('export_push_notifications'),
        'data_url' => 'push_notifications.add&user_ids=',
    );
}

if (Registry::get('addons.newsletters.status') == 'A') {
    $schema['newsletters'] = array(
        'type' => 'button',
        'but_name' => 'dispatch[reports.view.export.newsletters]',
        'but_role' => 'submit-button',
        'but_text' => __('export_newsletters'),
        'data_url' => 'newsletters.add&type=N&user_ids=',
    );
}

//if (!Registry::get('runtime.company_id')) {
    $schema['promotion'] = array(
        'type' => 'button',
        'but_name' => 'dispatch[reports.view.export.promotion]',
        'but_role' => 'submit-button',
        'but_text' => __('export_promotion'),
        'data_url' => 'promotions.add&user_ids=',
        'data_params' => array(
            'product', 'category'
        ),
    );
//}
return $schema;
