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

namespace Tygh\Addons\VendorDataPremoderation;

/**
 * Class StateFactory loads object states.
 *
 * @package Tygh\Addons\VendorDataPremoderation
 */
class StateFactory
{
    /**
     * @var callable $data_loader Loads data from object data sources
     */
    protected $data_loader;

    /**
     * @var array[] $source_conditions Describes conditions to extract object data from each source
     */
    protected $source_conditions;

    const OBJECT_ID_PLACEHOLDER = '$id';

    public function __construct(array $source_conditions, callable $data_loader)
    {
        $this->source_conditions = $source_conditions;
        $this->data_loader = $data_loader;
    }

    /**
     * Gets object state.
     *
     * @param string|int $id Object identifier
     *
     * @return \Tygh\Addons\VendorDataPremoderation\State
     */
    public function getState($id)
    {
        $object_data = [];
        foreach ($this->source_conditions as $source => $conditions) {
            $conditions = $this->prepareConditions($id, $conditions);
            $object_data[$source] = call_user_func($this->data_loader, $source, $conditions);
        }

        return new State($object_data);
    }

    /**
     * Prepares conditions to load object data from a source.
     *
     * @param string|int $id         Object identifier
     * @param array      $conditions Conditions to load object data from a source
     *
     * @return array
     */
    public function prepareConditions($id, array $conditions)
    {
        foreach ($conditions as &$condition) {
            if ($condition === self::OBJECT_ID_PLACEHOLDER) {
                $condition = $id;
            }
        }
        unset($condition);

        return $conditions;
    }
}
