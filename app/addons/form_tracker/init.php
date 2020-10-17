<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'update_page_post',
    'get_page_data',
    'send_form'
);
