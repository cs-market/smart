<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'get_users',
);

if (fn_allowed_for('MULTIVENDOR')) {
    fn_register_hooks(
        'post_delete_user',
        'delete_company',
        'place_order',
        'get_user_info'
    );
}
if (Registry::get('addons.managers.status') == 'A') {
    fn_register_hooks(
        'create_order'
    );
}
