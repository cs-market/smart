<?php

namespace Tygh\Enum;

class CrossSellTypes
{
    const RECOMMENDED = 'R';
    const SIMILAR = 'S';

    static function getAll() {
        return[self::RECOMMENDED => 'recommended', self::SIMILAR => 'similar'];
    }
}
