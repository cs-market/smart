<?php

use Tygh\Addons\RusTaxes\TaxType;

$schema = array(
    TaxType::NONE    => '',
    TaxType::VAT_0   => '0',
    TaxType::VAT_10  => '10',
    TaxType::VAT_20  => '20',
    TaxType::VAT_110 => '110',
    TaxType::VAT_120 => '120'
);

return $schema;
