<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'get_usergroups_pre',
    'update_product_post'
);

fn_init_stack(array('fn_init_storages', &$_REQUEST));
