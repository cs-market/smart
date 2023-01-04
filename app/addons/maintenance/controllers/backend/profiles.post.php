<?php

use Tygh\Registry;
use Tygh\Enum\UserTypes;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'update') {
    $user_data = Tygh::$app['view']->getTemplateVars('user_data');
    if (!Registry::ifget('navigation.tabs.usergroups', false)) {
        $user_type = $user_data['user_type'];

        if ((!fn_check_user_type_admin_area($user_type) && in_array($auth['user_type'], ['A', 'V']) )
            || (fn_check_user_type_admin_area($user_type) && ($auth['user_type']) == 'A')
        ) {
            $navigation = Registry::get('navigation.tabs');
            $navigation['usergroups'] = array (
                'title' => __('usergroups'),
                'js' => true
            );
            Registry::set('navigation.tabs', $navigation);
            $usergroups = fn_get_available_usergroups($user_type);
            Tygh::$app['view']->assign('usergroups', $usergroups);
        }
    }

    $usergroups = Tygh::$app['view']->getTemplateVars('usergroups');
    if (!empty($usergroups)) {
        $active_usergroups = array_keys(array_filter($user_data['usergroups'], function($v) {return $v['status'] == 'A';}));

        foreach ($usergroups as $id => &$value) {
            $value['sort_field'] = (!empty($active_usergroups) && in_array($id, $active_usergroups)) ? 'A_' . $value['usergroup'] : 'D_' . $value['usergroup'];
        }
        unset($value);

        $usergroups = fn_sort_array_by_key($usergroups, 'sort_field');
        array_walk($usergroups, function(&$u) {
            unset($u['sort_field']);
        });

        Tygh::$app['view']->assign('usergroups', $usergroups);
    }

    if (
        fn_check_user_type_admin_area($user_data)
        && !empty($user_data['user_id'])
        && (
            $auth['user_type'] === UserTypes::ADMIN
            || $user_data['api_key']
        )
    ) {
        $navigation = Registry::get('navigation.tabs');
        $navigation['api'] = [
            'title' => __('api_access'),
            'js'    => true
        ];
        Registry::set('navigation.tabs', $navigation);

        Tygh::$app['view']->assign('show_api_tab', true);

        if ($auth['user_type'] !== UserTypes::ADMIN) {
            Tygh::$app['view']->assign('hide_api_checkbox', true);
        }
    }
} elseif ($mode == 'manage') {
    Tygh::$app['view']->assign('can_add_user', true);
}
