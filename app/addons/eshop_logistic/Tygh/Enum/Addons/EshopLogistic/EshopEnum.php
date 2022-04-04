<?php

namespace Tygh\Enum\Addons\EshopLogistic;

class EshopEnum
{
    const PAYMENT_TYPE_CARD      = 'C';
    const PAYMENT_TYPE_CASH      = 'S';
    const PAYMENT_TYPE_CASHLESS  = 'L';
    const PAYMENT_TYPE_PREPAY    = 'P';
    
    const TERMINAL  = 'terminal';
    const TERMINALS = 'terminals';

    const CUSTOM_DELIVERY = 'custom';

    public static function getPaymentsTypes()
    {
        return [
            self::PAYMENT_TYPE_CARD => [
                'type'        => self::PAYMENT_TYPE_CARD,
                'description' => __('eshop_logistic.payment_type_card'),
                'code'        => 'card'
            ],
            self::PAYMENT_TYPE_CASH => [
                'type'        => self::PAYMENT_TYPE_CASH,
                'description' => __('eshop_logistic.payment_type_cash'),
                'code'        => 'cash'
            ],
            self::PAYMENT_TYPE_CASHLESS => [
                'type'        => self::PAYMENT_TYPE_CASHLESS,
                'description' => __('eshop_logistic.payment_type_cashless'),
                'code'        => 'cashless'
            ],
            self::PAYMENT_TYPE_PREPAY => [
                'type'        => self::PAYMENT_TYPE_PREPAY,
                'description' => __('eshop_logistic.payment_type_prepay'),
                'code'        => 'prepay'
            ]
        ];
    }
}