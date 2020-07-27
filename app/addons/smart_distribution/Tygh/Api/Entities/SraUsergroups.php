<?php

namespace Tygh\Api\Entities;

class SraUsergroups extends Usergroups
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
