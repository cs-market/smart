<?php

use Tygh\Enum\YesNo;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($auth['is_root'] != YesNo::YES) Tygh::$app['view']->assign('is_bottom_panel_available', false);
