<?php

use Tygh\Registry;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'update') {
    $user_data = Tygh::$app['view']->getTemplateVars('user_data');
    $user_type = $user_data['user_type'];

    if ((!fn_check_user_type_admin_area($user_type) && ($auth['user_type']) == 'V' )
        || (fn_check_user_type_admin_area($user_type) && ($auth['user_type']) == 'A')
    ) {
        $navigation =  Registry::get('navigation.tabs');
        $navigation['usergroups'] = array (
            'title' => __('usergroups'),
            'js' => true
        );
        Registry::set('navigation.tabs', $navigation);

        $usergroups = fn_get_usergroups(
            fn_check_user_type_admin_area($user_type)
                ? array('status' => array('A', 'H'))
                : array('type' => 'C', 'status' => array('A', 'H')),
            CART_LANGUAGE
        );

        Tygh::$app['view']->assign('usergroups', $usergroups);
    }
} elseif ($mode == 'manage') {
    Tygh::$app['view']->assign('can_add_user', true);
}
