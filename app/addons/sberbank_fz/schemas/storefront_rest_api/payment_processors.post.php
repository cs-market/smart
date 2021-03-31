<?php

use Tygh\Enum\Addons\StorefrontRestApi\PaymentTypes;

defined('BOOTSTRAP') or die('Access denied');

$schema['sberbank_fz.php'] = array(
    'type'  => PaymentTypes::REDIRECTION,
    'class' => '\Tygh\Addons\SberbankFz\Payments\SberbankMobile',
);


return $schema;
