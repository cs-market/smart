<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_usergroups',
    'get_simple_usergroups',
    'update_usergroup',
    'delete_usergroups',
    'get_default_usergroups',
    'update_category_pre',
    'update_category_post'
);
