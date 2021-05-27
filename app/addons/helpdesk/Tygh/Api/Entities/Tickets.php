<?php

namespace Tygh\Api\Entities;

use Tygh\Addons\StorefrontRestApi\ASraEntity;
use Tygh\Api\Response;

class Tickets extends ASraEntity
{
    public function index($id = 0, $params = [])
    {
        list($data, $search) = fn_get_tickets();

        return [
            'status' => Response::STATUS_OK,
            'data'   => $data,
        ];
    }

    public function create($params)
    {
        return array(
            'status' => Response::STATUS_FORBIDDEN,
            'data' => []
        );
    }

    public function update($id, $params)
    {
        return array(
            'status' => Response::STATUS_FORBIDDEN,
            'data' => []
        );
    }

    public function delete($id)
    {
        return array(
            'status' => Response::STATUS_FORBIDDEN,
            'data' => []
        );
    }

    /** @inheritdoc */
    public function privilegesCustomer()
    {
        return [
            'index' => true
        ];
    }
}
