<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	return ;
}

if ($mode == 'add') {
    return [CONTROLLER_STATUS_REDIRECT, $_SERVER['HTTP_REFERER'] ?? fn_url()];
}
