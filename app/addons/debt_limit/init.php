<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'get_user_short_info_pre',
    'user_init',
    'exim_1c_update_order'
);
