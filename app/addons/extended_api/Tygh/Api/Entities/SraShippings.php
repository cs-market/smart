<?php

namespace Tygh\Api\Entities;

class SraShippings extends Shippings
{
    public function privilegesCustomer()
    {
        return [
            'index'  => true,
            'create' => false,
            'update' => false,
            'delete' => false,
        ];
    }
}
