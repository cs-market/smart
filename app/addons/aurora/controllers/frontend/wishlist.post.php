<?php

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'view') {
    $products = Tygh::$app['view']->getTemplateVars('products');

    $categories_products = fn_group_array_by_key($products, 'main_category');
    $category_ids = array_keys($categories_products);

    list($categories) = fn_get_categories(['item_ids' => implode(',', $category_ids), 'group_by_level' => false, 'simple' => false]);

    $categories = fn_array_value_to_key($categories, 'category_id');

    Tygh::$app['view']->assign('categories', $categories);
    Tygh::$app['view']->assign('categories_products', $categories_products);
}
