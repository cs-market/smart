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

use Tygh\Enum\SyncDataStatuses;
use Tygh\Registry;
use Tygh\Addons\CommerceML\ServiceProvider;
use Tygh\Languages\Languages;

defined('BOOTSTRAP') or die('Access denied');

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && !empty($_REQUEST['sync_provider_id'])
    && $_REQUEST['sync_provider_id'] === 'commerceml_catalog'
) {
    if ($mode === 'update') {
        if (isset($_REQUEST['records'])) {
            $mappable_schema = ServiceProvider::getManualMappableEntitiesSchema();

            $raw_records = (array) $_REQUEST['records'];
            $company_id = fn_get_runtime_company_id();

            $map_repository = ServiceProvider::getImportEntityMapRepository();
            $record_list = [];

            foreach ($raw_records as $type => $record) {
                foreach ($record as $record_data) {
                    if (empty($record_data['entity_id']) || !isset($record_data['local_id'])) {
                        continue;
                    }

                    $record_list[$type][] = [
                        'company_id'  => (int) $company_id,
                        'entity_type' => (string) $type,
                        'entity_id'   => (string) $record_data['entity_id'],
                        'local_id'    => (string) $record_data['local_id'],
                    ];
                }
            }

            foreach ($record_list as $records) {
                $map_repository->batchAdd($records);
            }

            unset($_REQUEST['records']);
        }

        return [CONTROLLER_STATUS_OK, 'sync_data.update?sync_provider_id=commerceml_catalog'];
    }

    return [CONTROLLER_STATUS_DENIED];
}

if (
    $mode === 'update'
    && !empty($_REQUEST['sync_provider_id'])
    && $_REQUEST['sync_provider_id'] === 'commerceml_catalog'
) {
    $company_id = fn_get_runtime_company_id();

    $tabs = [
        'general',
        'catalog',
        'products',
    ];

    $tab_list = [];

    foreach ($tabs as $tab) {
        $tab_list[$tab] = [
            'title' => __('commerceml.tab.' . $tab),
            'js'    => true,
        ];
    }

    $steps_results = [
        'step_1' => true,
        'step_2' => true,
        'step_3' => true,
    ];

    $mappable_schema = ServiceProvider::getManualMappableEntitiesSchema();
    $map_repository = ServiceProvider::getImportEntityMapRepository();

    $mapping_count_summary = $map_repository->getCountSummary($company_id, array_keys($mappable_schema));

    if (empty($mapping_count_summary)) {
        $steps_results['step_1'] = false;
        $steps_results['step_2'] = false;
    }

    /**
     * @var string $type
     * @var array{is_creatable?: bool|callable, parent?: string} $schema
     */
    foreach ($mappable_schema as $type => &$schema) {
        if (!isset($schema['is_creatable'])) {
            $schema['is_creatable'] = false;
        }

        if (is_callable($schema['is_creatable'])) {
            $schema['is_creatable'] = call_user_func($schema['is_creatable']);
        }

        if (!isset($tab_list[$type]) && empty($schema['parent'])) {
            $tab_list[$type] = [
                'title'        => __('commerceml.tab.' . $type),
                'href'         => sprintf('commerceml.map?type=%s', $type),
                'ajax'         => true,
                'ajax_onclick' => true,
            ];
        }

        if (
            isset($mapping_count_summary[$type])
            && empty($mapping_count_summary[$type]['matched_cnt'])
            && empty($schema['is_creatable'])
        ) {
            $steps_results['step_2'] = false;
        }
    }
    unset($schema);

    $last_sync_info = ServiceProvider::getLastSyncInfo($company_id);

    $steps_results['step_3'] = isset($last_sync_info['status_code']) && $last_sync_info['status_code'] === SyncDataStatuses::STATUS_SUCCESS
        ? $last_sync_info['last_sync_timestamp']
        : false;

    Registry::set('navigation.tabs', $tab_list);

    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $view->assign([
        'mappable_schema'       => $mappable_schema,
        'languages'             => Languages::getAll(),
        'order_statuses'        => fn_get_simple_statuses(STATUSES_ORDER, false, false, CART_LANGUAGE),
        'mapping_count_summary' => $mapping_count_summary,
        'steps_results'         => $steps_results
    ]);
}
