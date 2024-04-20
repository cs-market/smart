<?php

namespace Tygh\Api\Entities\v20;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
// use Tygh\Registry;

class Equipment extends AEntity
{
    private $equipment_repository;

    public function __construct($auth = array(), $area = '') {
        parent::__construct($auth, $area);
        $this->equipment_repository = \Tygh::$app['addons.equipment.repository'];
    }
    
    public function index($id = 0, $params = array())
    {
        if ($id) {
            $data = $this->equipment_repository->findById($id);
        } else {
            list($data, $search) = $this->equipment_repository->find($params, $this->safeGet($params, 'items_per_page', 10));
        }

        return array(
            'status' => Response::STATUS_OK,
            'data' => $data
        );
    }

    public function create($params)
    {
        $status = Response::STATUS_BAD_REQUEST;
        $result = $this->equipment_repository->save($params);
        if ($result->isSuccess()) {
            $status = Response::STATUS_CREATED;
            $data = array(
                'equipment_id' => $result->getData(),
            );
        }
        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function update($id, $params)
    {
        $status = Response::STATUS_BAD_REQUEST;
        $result = $this->equipment_repository->save($params, $id);
        if ($result->isSuccess()) {
            $status = Response::STATUS_OK;
            $data = array(
                'equipment_id' => $result->getData(),
            );
        }
        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function delete($id)
    {
        $status = Response::STATUS_NOT_FOUND;
        $result = $this->equipment_repository->delete($id);
        if ($result->isSuccess()) {
            $status = Response::STATUS_NO_CONTENT;
        }
        return array(
            'status' => $status,
            'data' => []
        );
    }

    public function privileges()
    {
        return array(
            'create' => true,
            'update' => true,
            'delete' => true,
            'index'  => true
        );
    }
    public function privilegesCustomer()
    {
        return [
            'index'  => false,
            'create' => false,
            'update' => false,
            'delete' => false,
        ];
    }
}
