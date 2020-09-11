<?php

use Tygh\Enum\Addons\StorefrontRestApi\PaymentTypes;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

$addons = Registry::get('addons');

if (isset($addons['rus_sberbank']['status']) && $addons['rus_sberbank']['status'] === 'A') {
    $schema['sberbank.php'] = array(
        'type'  => PaymentTypes::REDIRECTION,
        'class' => '\Tygh\Addons\SberbankMobilePayment\Payments\Sberbank',
    );
}

return $schema;