<?php

$schema['product_filters_anonymous_catalog'] = $schema['product_filters_home'];
$schema['product_filters_anonymous_catalog']['templates'] = 'addons/anonymous_catalog/blocks/product_filters/';
$schema['product_filters_anonymous_catalog']['content']['items']['items_function'] = 'fn_anonymous_catalog_get_product_filters';
$schema['product_filters_anonymous_catalog']['content']['items']['fillings']['manually']['params']['request']['features_hash'] = '%FEATURES_HASH%';

return $schema;
