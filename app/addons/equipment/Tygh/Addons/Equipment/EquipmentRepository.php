<?php

namespace Tygh\Addons\Equipment;

use Tygh\Common\OperationResult;
use Tygh\Database\Connection;
use Tygh\Enum\SiteArea;

/**
 * Class Repository fetches, saves and removes Equipment. The Repository saves equipment in the store database.
 *
 * @package Tygh\Addons\Equipment
 */
class EquipmentRepository
{
    /**
     * @var \Tygh\Database\Connection
     */
    protected $db;

    private $table = 'equipment';

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
        if (SiteArea::isStorefront(AREA)) {
            $params['user_id'] = \Tygh::$app['session']['auth']['user_id'];
        }
        $params = $this->populateDefaultFindParameters($params);

        $fields = [
            '' => $this->table . '.*',
        ];
        $join = $this->buildJoins($params);
        $conditions = $this->buildConditions($params);
        $order_by = $this->buildOrderBy($params);
        $group_by = $this->buildGroupBy($params);
        $having = [];
        $limit = $this->buildLimit($params, $params['items_per_page']);

        $equipment = $this->db->getHash(
            'SELECT ?p FROM ?:?f AS ?f ?p WHERE ?p ?p ?p ?p ?p',
            'equipment_id',
            implode(',', $fields),
            $this->table,
            $this->table,
            implode(' ', $join),
            implode(' ', $conditions),
            $group_by ? 'GROUP BY ' . $group_by : '',
            $having ? 'HAVING ' . implode(' ', $having) : '',
            $order_by,
            $limit
        );

        if (!empty($equipment) && ($params['get_repairs']) || SiteArea::isStorefront(AREA)) {
            $repair_requests_repository = \Tygh::$app['addons.equipment.repair_requests_repository'];
            $equipment_ids = array_column($equipment, 'equipment_id');
            list($repair_requests,) = $repair_requests_repository->find(['equipment_id' => $equipment_ids]);

            if (!empty($repair_requests)) {
                foreach($repair_requests as $request) {
                    $equipment[$request['equipment_id']]['repairs'][] = $request;
                }
            }
            if (SiteArea::isStorefront(AREA)) {
                foreach($equipment as &$e) {
                    $active_repairs = array_filter($e['repairs'], function($r) {
                        return !in_array($r['status'], [__('equipment.repair_status_deleted'), __('equipment.repair_status_fixed')]);
                    });
                    $e['is_new_repair_allowed'] = empty($active_repairs) && !in_array($e['status'], [__('equipment.equipment_status_in_repair'), __('equipment.equipment_status_write_off')]);
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

        list($data,) = $this->find(['equipment_id' => $equipment_id, 'get_repairs' => true]);

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

        $this->db->query('DELETE FROM ?:?f WHERE equipment_id = ?i', $this->table, $equipment_id);

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
                'AND ?f.equipment_id IN (?n)',
                $this->table,
                (array) $params['equipment_id']
            );
        }
        if ($params['user_id']) {
            $conditions['user_id'] = $this->db->quote(
                'AND ?f.user_id = ?i',
                $this->table,
                $params['user_id']
            );
        }
        if ($params['product_code']) {
            $conditions['product_code'] = $this->db->quote(
                'AND ?f.product_code = ?s',
                $this->table,
                $params['product_code']
            );
        }
        if ($params['inventory_number']) {
            $conditions['inventory_number'] = $this->db->quote(
                'AND ?f.inventory_number = ?s',
                $this->table,
                $params['inventory_number']
            );
        }
        if ($params['serial_number']) {
            $conditions['serial_number'] = $this->db->quote(
                'AND ?f.serial_number = ?s',
                $this->table,
                $params['serial_number']
            );
        }
        if ($params['name']) {
            $conditions['name'] = $this->db->quote(
                'AND ?f.name = ?s',
                $this->table,
                $params['name']
            );
        }
        if ($params['status']) {
            $conditions['status'] = $this->db->quote(
                'AND ?f.status = ?s',
                $this->table,
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
            'equipment_id' => $this->table . '.equipment_id',
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
            'equipment_id' => $this->table . '.equipment_id',
        ];

        if (isset($grouppings[$params['group_by']])) {
            return $grouppings[$params['group_by']];
        }

        return '';
    }
}
