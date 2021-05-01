<?php

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'get_fields') {
    $relations = Tygh::$app['view']->getTemplateVars('relations');
    $relations['feature']['fields']['create-new-feature'] = array('description' => __('new_feature'), 'show_description' => true);

    Tygh::$app['view']->assign('relations', $relations);
}
