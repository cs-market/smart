<?php

$schema['controllers']['order_management']['permissions'] = true;
$schema['controllers']['sales_reports']['permissions'] = true;
$schema['controllers']['commerceml']['permissions'] = true;
$schema['controllers']['product_features']['modes']['update']['permissions']['POST'] = true;
$schema['controllers']['product_features']['modes']['update_status']['permissions'] = true;

$schema['controllers']['usergroups']['modes']['update_status']['permissions'] = true;
$schema['controllers']['tools']['modes']['update_status']['param_permissions']['table']['promotions'] = true;
$schema['controllers']['tools']['modes']['update_status']['param_permissions']['table']['product_filters'] = true;

return $schema;
