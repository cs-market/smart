<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {}

if ($mode == 'update') {
  Tygh::$app['view']->assign('is_order_management', false);
}
