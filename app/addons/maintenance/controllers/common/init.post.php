<?php

use Tygh\Enum\YesNo;

defined('BOOTSTRAP') or die('Access denied');

if ($auth['is_root'] != YesNo::YES) Tygh::$app['view']->assign('is_bottom_panel_available', false);
