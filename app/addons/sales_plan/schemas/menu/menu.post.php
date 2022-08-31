<?php

use Tygh\Registry;

$reports = fn_get_dir_contents(Registry::get('config.dir.addons').'/sales_plan/schemas/reports', false, true);

$position = 900;
foreach ($reports as $report_file) {
    $position += 10;
    $report = (string)pathinfo($report_file, PATHINFO_FILENAME);
    $schema['central']['orders']['items'][$report] = array(
        'attrs' => array(
            'class'=>'is-addon'
        ),
        'href' => "reports.view?type=$report",
        'alt' => "reports.view?type=$report",
        'position' => $position
    );
}

return $schema;
