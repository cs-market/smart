<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

$schema['central']['products']['items']['storages.storages'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'storages.manage',
    'position' => 660,
);

$schema['top']['administration']['items']['export_data']['subitems']['storages.storages'] = array(
    'href' => 'exim.export?section=storages',
    'position' => 351
);

$schema['top']['administration']['items']['import_data']['subitems']['storages.storages'] = array(
    'href' => 'exim.import?section=storages',
    'position' => 351
);

return $schema;
