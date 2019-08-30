<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (!empty($_REQUEST['geocity'])) {
    $url = '';
    if (!empty($_REQUEST['pull_url_geolocation'])) {
        $url = $_REQUEST['pull_url_geolocation'];

    } elseif (!empty($_REQUEST['url'])) {
        $url = $_REQUEST['url'];

    } elseif (!empty($_REQUEST['url_geolocation'])) {
        $url = $_REQUEST['url'];
    }

    \Tygh::$app['session']['geocity'] = $_REQUEST['geocity'];

    Tygh::$app['view']->assign('geocity', \Tygh::$app['session']['geocity']);
    return array(CONTROLLER_STATUS_REDIRECT, $url);
}
