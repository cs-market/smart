<?php

namespace Tygh\Addons\Equipment;

use Tygh\Common\OperationResult;
use Tygh\Database\Connection;

/**
 * Class Repository fetches, saves and removes RepairRequests. The Repository saves RepairRequests in the store database.
 *
 * @package Tygh\Addons\Equipment
 */
class RepairRequestsRepository
{
    /**
     * @var \Tygh\Database\Connection
     */
    protected $db;

    private $table = 'repair_requests';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Finds RepairRequests by search parameters.
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
            '' => $this->table . '.*',
        ];
        $join = $this->buildJoins($params);
        $conditions = $this->buildConditions($params);
        $order_by = $this->buildOrderBy($params);
        $group_by = $this->buildGroupBy($params);
        $having = [];
        $limit = $this->buildLimit($params, $params['items_per_page']);

        $requests = $this->db->getHash(
            'SELECT ?p FROM ?:?f AS ?f ?p WHERE ?p ?p ?p ?p ?p',
            'request_id',
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

        if (!empty($requests)) {
            array_walk($requests, function(&$r) {
                $r['malfunctions'] = !empty($r['malfunctions']) ? unserialize($r['malfunctions']) : [];
                $r['is_editable'] = $r['status'] == __('equipment.repair_status_default');
            });

            if (!empty($params['get_equipment'])) {
                $equipment_repository = \Tygh::$app['addons.equipment.equipment_repository'];
                $equipment_ids = array_column($requests, 'equipment_id');
                list($equipment,) = $equipment_repository->find(['equipment_id' => $equipment_ids]);

                foreach ($requests as &$request) {
                    $request['equipment'] = $equipment[$request['equipment_id']] ?? false;
                }
            }
        }

        /** @psalm-suppress InvalidReturnStatement */
        return [$requests, $params];
    }

    public function findById($request_id) {
        if (!$request_id) {
            return null;
        }

        list($data,) = $this->find(['request_id' => $request_id, 'get_equipment' => true]);
        return (!empty($data)) ? reset($data) : false;
    }

    /**
     * Creates or updates RepairRequests.
     *
     * @param int $request_data RepairRequests data
     * @param int $request_id RepairRequests ID
     *
     * @return \Tygh\Common\OperationResult
     */
    public function save($request_data, $request_id = 0)
    {
        $result = new OperationResult(true);
        if ($request_id) {
            $old_data = $this->findById($request_id);
            if (!empty($old_data['equipment']['status']) && $old_data['status'] != __('equipment.repair_status_default')) {
                $result->setSuccess(false);
                $result->addError('1', __('access_denied'));
            }
            $request_data['request_id'] = $request_id;
        } else {
            unset($request_data['request_id']);
            $request_data['timestamp'] = time();
            if (empty($request_data['status'])) $request_data['status'] = __('equipment.repair_status_default');
        }

        if (!empty($request_data['malfunctions'])) {
            if (is_array($request_data['malfunctions'])) {
                $request_data['malfunctions'] = serialize($request_data['malfunctions']); 
            }
        } else {
            $result->setSuccess(false);
            $result->addError('1', __('equipment.malfunctions_required'));
        }

        if ($result->isSuccess()) {
            $this->db->replaceInto('repair_requests', $request_data);

            if (!$request_id) {
                $request_id = $this->db->getInsertId();
            }

            $result->setData($request_id);
        }


        return $result;
    }

    /**
     * Deletes an request.
     *
     * @param int $request_id request ID
     *
     * @return \Tygh\Common\OperationResult
     */
    public function delete($request_id)
    {
        $result = new OperationResult(true);

        $this->db->query('DELETE FROM ?:?f WHERE request_id = ?i', $this->table, $request_id);

        return $result;
    }

    /**
     * Populates default request search parameters.
     *
     * @param array $params Search parameters
     *
     * @return array
     */
    protected function populateDefaultFindParameters(array $params)
    {
        $populated_params = array_merge([
            'request_id'     => null,
            'equipment_id'     => null,
            'status'           => null,
            'sort_by'          => 'request_id',
            'group_by'         => 'request_id',
        ], $params);

        return $populated_params;
    }

    /**
     * Provides WHERE part data of an SQL query for request search.
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
        if ($params['request_id']) {
            $conditions['request_id'] = $this->db->quote(
                'AND ?f.request_id IN (?n)',
                $this->table,
                (array) $params['request_id']
            );
        }
        if ($params['equipment_id']) {
            $conditions['equipment_id'] = $this->db->quote(
                'AND ?f.equipment_id IN (?n)',
                $this->table,
                (array) $params['equipment_id']
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
     * Provides JOIN part data of an SQL query for request search.
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
     * Provides ORDER BY part data of an SQL query for request search.
     *
     * @param array $params Search parameters
     *
     * @return string
     */
    protected function buildOrderBy(array $params)
    {
        $sortings = [
            'request_id' => $this->table . '.request_id',
        ];

        $order_by = db_sort($params, $sortings, 'timestamp', 'desc');

        return $order_by;
    }

    /**
     * Provides LIMIT part data of an SQL query for request search.
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
     * Provides GROUP BY part data of an SQL query for request search.
     *
     * @param array $params Search parameters
     *
     * @return string
     */
    protected function buildGroupBy(array $params)
    {
        $grouppings = [
            'request_id' => $this->table . '.request_id',
        ];

        if (isset($grouppings[$params['group_by']])) {
            return $grouppings[$params['group_by']];
        }

        return '';
    }
}
