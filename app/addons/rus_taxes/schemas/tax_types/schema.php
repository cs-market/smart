<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

use Tygh\Addons\RusTaxes\TaxType;

$schema = array(
    TaxType::NONE => array(
        'name' => __('rus_taxes.tax.none'),
    ),
    TaxType::VAT_0 => array(
        'name' => __('rus_taxes.tax.vat0'),
    ),
    TaxType::VAT_10 => array(
        'name' => __('rus_taxes.tax.vat10'),
    ),
    TaxType::VAT_18 => array(
        'name' => __('rus_taxes.tax.vat18'),
    ),
    TaxType::VAT_110 => array(
        'name' => __('rus_taxes.tax.vat110'),
    ),
    TaxType::VAT_118 => array(
        'name' => __('rus_taxes.tax.vat118'),
    ),
);

return $schema;