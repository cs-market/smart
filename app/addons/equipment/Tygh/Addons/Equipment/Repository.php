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

namespace Tygh\Addons\Equipment;

use Tygh\Common\OperationResult;
use Tygh\Database\Connection;

/**
 * Class Repository fetches, saves and removes Equipment. The Repository saves equipment in the store database.
 *
 * @package Tygh\Addons\Equipment
 */
class Repository
{
    /**
     * @var \Tygh\Database\Connection
     */
    protected $db;

    protected $factory;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Finds Equipment by search parameters.
     *
     * @param array $params         Search parameters
     * @param int   $items_per_page Amount of items per page
     *
     * @psalm-suppress InvalidReturnType
     */
    public function find(array $params = [], $items_per_page = 0)
    {
        $params = $this->populateDefaultFindParameters($params);

        $fields = [
            '' => 'equipment.*',
        ];
        $join = $this->buildJoins($params);
        $conditions = $this->buildConditions($params);
        $order_by = $this->buildOrderBy($params);
        $group_by = $this->buildGroupBy($params);
        $having = [];
        $limit = $this->buildLimit($params, $params['items_per_page']);

        $equipment = $this->db->getHash(
            'SELECT ?p FROM ?:equipment AS equipment ?p WHERE ?p ?p ?p ?p ?p',
            'equipment_id',
            implode(',', $fields),
            implode(' ', $join),
            implode(' ', $conditions),
            $group_by ? 'GROUP BY ' . $group_by : '',
            $having ? 'HAVING ' . implode(' ', $having) : '',
            $order_by,
            $limit
        );

        if (!empty($equipment) && $params['get_repairs']) {
            $equipment_ids = array_column($equipment, 'equipment_id');
            if ($repair_requests = db_get_array('SELECT * FROM ?:repair_requests WHERE equipment_id IN (?a)', $equipment_ids)) {
                foreach($repair_requests as $request) {
                    $request['malfunctions'] = unserialize($request['malfunctions']);
                    $equipment[$request['equipment_id']]['repairs'][] = $request;
                }
            }
        }

        /** @psalm-suppress InvalidReturnStatement */
        return [$equipment, $params];
    }

    public function findById($equipment_id) {
        $equipment_id = (int) $equipment_id;

        if (!$equipment_id) {
            return null;
        }

        list($data,) = $this->find(['equipment_id' => $equipment_id]);

        return (!empty($data)) ? reset($data) : false;
    }

    /**
     * Creates or updates Equipment.
     *
     * @param int $equipment_data Equipment data
     * @param int $equipment_id Equipment ID
     *
     * @return \Tygh\Common\OperationResult
     */
    public function save($equipment_data, $equipment_id = 0)
    {
        $result = new OperationResult(true);

        if ($equipment_id) {
            $equipment_data['equipment_id'] = $equipment_id;
        } else {
            unset($equipment_data['equipment_id']);
        }

        $this->db->replaceInto('equipment', $equipment_data);

        if (!$equipment_id) {
            $equipment_id = $this->db->getInsertId();
        }

        $result->setData($equipment_id);

        return $result;
    }

    /**
     * Deletes an equipment.
     *
     * @param int $equipment_id Equipment ID
     *
     * @return \Tygh\Common\OperationResult
     */
    public function delete($equipment_id)
    {
        $result = new OperationResult(true);

        $this->db->query('DELETE FROM ?:equipment WHERE equipment_id = ?i', $equipment_id);

        return $result;
    }

    /**
     * Populates default equipment search parameters.
     *
     * @param array $params Search parameters
     *
     * @return array
     */
    protected function populateDefaultFindParameters(array $params)
    {
        $populated_params = array_merge([
            'equipment_id'     => null,
            'user_id'          => null,
            'product_code'     => null,
            'inventory_number' => null,
            'serial_number'    => null,
            'name'             => null,
            'status'           => null,
            'page'             => 1,
            'items_per_page'   => 10,
            'sort_by'          => 'equipment_id',
            'group_by'         => 'equipment_id',
        ], $params);

        return $populated_params;
    }

    /**
     * Provides WHERE part data of an SQL query for equipment search.
     *
     * @param array $params Search parameters
     *
     * @return string[]
     */
    protected function buildConditions(array $params)
    {
        $conditions = [
            '' => '1 = 1',
        ];

        if ($params['equipment_id']) {
            $conditions['equipment_id'] = $this->db->quote(
                'AND equipment.equipment_id IN (?n)',
                (array) $params['equipment_id']
            );
        }
        if ($params['user_id']) {
            $conditions['user_id'] = $this->db->quote(
                'AND equipment.user_id = ?i',
                $params['user_id']
            );
        }
        if ($params['product_code']) {
            $conditions['product_code'] = $this->db->quote(
                'AND equipment.product_code = ?s',
                $params['product_code']
            );
        }
        if ($params['inventory_number']) {
            $conditions['inventory_number'] = $this->db->quote(
                'AND equipment.inventory_number = ?s',
                $params['inventory_number']
            );
        }
        if ($params['serial_number']) {
            $conditions['serial_number'] = $this->db->quote(
                'AND equipment.serial_number = ?s',
                $params['serial_number']
            );
        }
        if ($params['name']) {
            $conditions['name'] = $this->db->quote(
                'AND equipment.name = ?s',
                $params['name']
            );
        }
        if ($params['status']) {
            $conditions['status'] = $this->db->quote(
                'AND equipment.name = ?s',
                $params['status']
            );
        }


        return $conditions;
    }

    /**
     * Provides JOIN part data of an SQL query for equipment search.
     *
     * @param array $params Search parameters
     *
     * @return string[]
     */
    protected function buildJoins(array $params)
    {
        $joins = [];

        return $joins;
    }

    /**
     * Provides ORDER BY part data of an SQL query for equipment search.
     *
     * @param array $params Search parameters
     *
     * @return string
     */
    protected function buildOrderBy(array $params)
    {
        $sortings = [
            'equipment_id' => 'equipment.equipment_id',
        ];

        $order_by = db_sort($params, $sortings, 'timestamp', 'desc');

        return $order_by;
    }

    /**
     * Provides LIMIT part data of an SQL query for equipment search.
     *
     * @param array $params         Search parameters
     * @param int   $items_per_page Items per page
     *
     * @return string[]
     */
    protected function buildLimit(array $params, $items_per_page = 0)
    {
        $limit = '';
        if ($items_per_page != 0) {
            $limit = db_paginate($params['page'], $items_per_page);
        }

        return $limit;
    }

    /**
     * Provides GROUP BY part data of an SQL query for equipment search.
     *
     * @param array $params Search parameters
     *
     * @return string
     */
    protected function buildGroupBy(array $params)
    {
        $grouppings = [
            'equipment_id' => 'equipment.equipment_id',
        ];

        if (isset($grouppings[$params['group_by']])) {
            return $grouppings[$params['group_by']];
        }

        return '';
    }
}
