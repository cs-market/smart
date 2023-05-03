<?php

namespace Tygh\Api\Entities\v50;

use Tygh\Api\Entities\v40\SraProducts as BaseSraProducts;

/**
 * Class SraProducts
 *
 * @package Tygh\Api\Entities
 */
class SraProducts extends BaseSraProducts
{
    public function index($id = 0, $params = [])
    {
        $result = parent::index($id, $params);
        if (isset($result['data']['products'])) $result['data']['products'] = array_values($result['data']['products']);
        return $result;
    }
}
