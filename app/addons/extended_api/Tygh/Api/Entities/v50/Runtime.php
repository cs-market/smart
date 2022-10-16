<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

namespace Tygh\Api\Entities\v50;
use Tygh\Api\AEntity;
use Tygh\Api\Response;

class Runtime extends AEntity {
    public function index($id = 0, $params = array())
    {
        $status = Response::STATUS_OK;
        $data = [];

        fn_set_hook('api_runtime_handle_index_request', $id, $params, $status, $data);
        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function create($params)
    {
        $status = Response::STATUS_OK;
        $data = [];
        fn_set_hook('api_runtime_handle_create_request', $params, $status, $data);
        return array(
            'status' => $status,
            'data' => $data
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
        $data = [];

        $status = Response::STATUS_NO_CONTENT;
        fn_set_hook('api_runtime_handle_delete_request', $id, $status, $data);
        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function privileges()
    {
        $privileges = array(
            'create' => false,
            'update' => false,
            'delete' => false,
            'index'  => false
        );

        return $privileges;
    }

    public function privilegesCustomer()
    {
        return [
            'index'  => true,
            'create' => true,
            'update' => false,
            'delete' => true,
        ];
    }

    public function isValidIdentifier($id)
    {
        return true;
    }
}
