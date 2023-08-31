<?php

defined('BOOTSTRAP') or die('Access denied');

if ($mode == 'view') {
    if ($_REQUEST['category_id'] == 9064) Tygh::$app['view']->assign('but_text', 'Вернуть');
}
