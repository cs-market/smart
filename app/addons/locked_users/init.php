<?php

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks('update_user_pre', 'api_disable_user');
