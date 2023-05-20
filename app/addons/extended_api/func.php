<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

use Tygh\ExtendedAPI;
use Tygh\Enum\SiteArea;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_extended_api_user_init($auth, $user_info, $first_init) {
    if (SiteArea::isAdmin(AREA)) return;

    if ($auth['user_id']) {
        if (empty(Tygh::$app['session']['cart']['user_data'])) {
            $profile_id = fn_get_session_data('last_order_profile_id');
            Tygh::$app['session']['cart']['user_data'] = fn_get_user_info($auth['user_id'], true, $profile_id);
        }
        if (defined('API')) {
            fn_extract_cart_content(Tygh::$app['session']['cart'], $auth['user_id'], 'C', 'R');
        }
    }
}

function fn_init_extended_api() {
    Tygh::$app['api'] = new ExtendedAPI();

    return array(INIT_STATUS_OK);
}


function fn_get_fresh_user_auth_token($user_id, $ttl = 604800)
{
    $token = false;
    $ekeys = fn_get_ekeys(array(
        'object_id' => $user_id,
        'object_type' => 'U',
        'ttl' => TIME
    ));

    if ($ekeys) {
        $ekey = reset($ekeys);
        $token = $ekey['ekey'];
    }

    $token = fn_generate_ekey($user_id, 'U', $ttl, $token);
    $expiry_time = time() + $ttl;

    return array($token, $expiry_time);
}

function fn_extended_api_update_user_pre($user_id, $user_data, $auth, &$ship_to_another, $notify_user, $can_update) {
    if (isset($user_data['ship_to_another'])) $ship_to_another = $user_data['ship_to_another'];
}
