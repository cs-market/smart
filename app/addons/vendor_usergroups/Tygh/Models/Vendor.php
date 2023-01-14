<?php

namespace Tygh\Models;

use Tygh\Models\Company;

class Vendor extends Company
{
    public function getFields($params)
    {
        $fields = parent::getFields($params);
        $fields[] = 'p.usergroups';
        return $fields;
    }

    public function gatherAdditionalItemsData(&$items, $params)
    {
        parent::gatherAdditionalItemsData($items, $params);
        foreach ($items as $key => $item) {
            $items[$key]['usergroups'] = !empty($item['usergroups']) ? explode(',', $item['usergroups']) : [];
        }
    }
}
