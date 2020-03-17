<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	return;
}
if ($mode == 'view') {
	if ($auth['area'] == 'A') $_REQUEST['action'] = 'preview';
}