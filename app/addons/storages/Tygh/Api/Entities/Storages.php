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

namespace Tygh\Api\Entities;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
use Tygh\Registry;

class Storages extends AEntity
{
    public function index($id = 0, $params = [])
    {
        if ($id) {
            $params['storage_id'] = $id;
        }

        list($storages) = fn_get_storages($params);

        if ($id) {
            $data = reset($storages);
        } else {
            $data = $storages;
        }

        return array(
            'status' => Response::STATUS_OK,
            'data' => $data
        );
    }

    public function create($params)
    {
        $status = Response::STATUS_BAD_REQUEST;
        $data = [];
        $valid_params = true;

        if (empty($params['code'])) {
            $data['message'] = __('api_required_field', [
                '[field]' => 'code'
            ]);
            $valid_params = false;
        }
        if (empty($params['storage'])) {
            $data['message'] = __('api_required_field', [
                '[field]' => 'storage'
            ]);
            $valid_params = false;
        }

        if ($valid_params) {
            $storage_id = fn_update_storage($params, 0);

            if ($storage_id) {
                $status = Response::STATUS_CREATED;
                $data = [
                    'storage_id' => $storage_id,
                ];
            }
        }

        return [
            'status' => $status,
            'data'   => $data
        ];
    }

    public function update($id, $params)
    {
        $status = Response::STATUS_BAD_REQUEST;
        $data = [];

        $storage_id = fn_update_storage($params, $id);

        if ($storage_id) {
            $status = Response::STATUS_OK;
            $data = [
                'storage_id' => $storage_id
            ];
        }

        return [
            'status' => $status,
            'data'   => $data
        ];
    }

    public function delete($id)
    {
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;

        if (fn_delete_storages($id)) {
            $status = Response::STATUS_NO_CONTENT;
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function privileges()
    {
        return array(
            'create' => 'manage_users',
            'update' => 'manage_users',
            'delete' => 'manage_users',
            'index'  => 'view_users'
        );
    }

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
