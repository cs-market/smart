<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'save_log',
    'update_company_pre',
    'send_order_notification'
);
