<?php

defined('BOOTSTRAP') or die('Access denied');

if (isset($_REQUEST['custom_registration'])) {
    $_SESSION['custom_registration'] = $_REQUEST['custom_registration'];
}
