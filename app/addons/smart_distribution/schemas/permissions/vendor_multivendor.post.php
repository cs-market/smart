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

$schema['controllers']['order_management']['permissions'] = true;
$schema['controllers']['sales_reports']['permissions'] = true;
$schema['controllers']['commerceml']['permissions'] = true;
$schema['controllers']['product_features']['modes']['update']['permissions']['POST'] = true;
$schema['controllers']['product_features']['modes']['update_status']['permissions'] = true;

$schema['controllers']['usergroups']['modes']['update_status']['permissions'] = true;
$schema['controllers']['tools']['modes']['update_status']['param_permissions']['table']['promotions'] = true;
$schema['controllers']['tools']['modes']['update_status']['param_permissions']['table']['product_filters'] = true;
$schema['controllers']['profiles']['modes']['act_as_user']['permissions'] = true;

return $schema;
