<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'm_add_category') {
        $return_url = 'categories.manage';
        $params = $_REQUEST;
        if (!empty($params['add_products_ids']) && !empty($params['category_id'])) {
            foreach ($params['add_products_ids'] as $pid) {
                $data = array('product_id' => $pid, 'category_id' => $params['category_id'], 'link_type' => 'A');
                db_query("REPLACE INTO ?:products_categories ?e", $data);
            }
            $return_url = "categories.update&category_id=" . $params['category_id'];
        }
        
        return array(CONTROLLER_STATUS_OK, $return_url);
    }
}

if ($mode == 'manage') {

    $selected_fields = Tygh::$app['view']->getTemplateVars('selected_fields');

    $selected_fields[] = array(
        'name' => '[extra][show_out_of_stock_product]',
        'text' => __('show_out_of_stock_product')
    );

    Tygh::$app['view']->assign('selected_fields', $selected_fields);

} elseif ($mode == 'm_update') {

    $selected_fields = Tygh::$app['session']['selected_fields'];

    if (!empty($selected_fields['extra']['show_out_of_stock_product'])) {

        $field_groups = Tygh::$app['view']->getTemplateVars('field_groups');
        $filled_groups = Tygh::$app['view']->getTemplateVars('filled_groups');

        $field_groups['C']['show_out_of_stock_product'] = 'products_data';
        $filled_groups['C']['show_out_of_stock_product'] = __('show_out_of_stock_product');

        Tygh::$app['view']->assign('field_groups', $field_groups);
        Tygh::$app['view']->assign('filled_groups', $filled_groups);
    }
}
