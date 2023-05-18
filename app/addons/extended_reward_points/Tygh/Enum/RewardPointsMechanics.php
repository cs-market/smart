<?php

namespace Tygh\Enum;

/**
 *  RewardPointsMechanics contains possible values for `companies`.`reward_points_mechanics` DB field.
 *
 * @package Tygh\Enum
 */
class RewardPointsMechanics
{
    const FULL_PAYMENT = 'A';
    const PARTIAL_PAYMENT = 'B';

    public static function isFullPayment($mechanics)
    {
        return $mechanics === self::FULL_PAYMENT;
    }

    public static function isPartialPayment($mechanics)
    {
        return $mechanics === self::PARTIAL_PAYMENT;
    }
}
