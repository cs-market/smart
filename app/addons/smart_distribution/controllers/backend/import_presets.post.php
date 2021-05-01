<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'get_fields') {
    $relations = Tygh::$app['view']->getTemplateVars('relations');
    $feature_ids = array_keys($relations['feature']['fields']);
    $features = db_get_hash_array("SELECT f.feature_id, f.company_id, c.company FROM ?:product_features AS f LEFT JOIN ?:companies AS c ON c.company_id = f.company_id WHERE feature_id IN (?a)", 'feature_id', $feature_ids);

    foreach ($relations['feature']['fields'] as $feature_id => &$feature) {
        $feature['description'] = $feature['description']. ' (' . $features[$feature_id]['company'] . ')';
    }

    Tygh::$app['view']->assign('relations', $relations);
}
