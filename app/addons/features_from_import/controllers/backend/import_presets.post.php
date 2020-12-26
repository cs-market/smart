<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'get_fields') {
    $relations = Tygh::$app['view']->getTemplateVars('relations');
    $relations['feature']['fields']['create-new-feature'] = array('name' => __('new_feature'), 'show_name' => true);

    Tygh::$app['view']->assign('relations', $relations);
}
