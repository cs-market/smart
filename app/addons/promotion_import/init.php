<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'maintenance_promotion_check_existence',
    'exim_1c_export_ordered_product_with_discount'
);
