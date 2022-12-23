<?php

use Tygh\Registry;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'update') {

    if (!Registry::ifget('navigation.tabs.usergroups', false)) {
        $user_data = Tygh::$app['view']->getTemplateVars('user_data');
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
} elseif ($mode == 'manage') {
    Tygh::$app['view']->assign('can_add_user', true);
}
