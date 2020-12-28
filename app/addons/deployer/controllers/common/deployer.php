<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_REQUEST['token']) && $_REQUEST['token'] == Registry::get('addons.deployer.token')) {
        $webhook = json_decode(file_get_contents('php://input'), true);
        fn_deploy($webhook);
        die("OK");
    }
}
