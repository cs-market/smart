<?php

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'view') {
    $bc = Tygh::$app['view']->getTemplateVars('breadcrumbs');
    array_pop($bc);
    Tygh::$app['view']->assign('breadcrumbs', $bc);
}
