<?php

use Tygh\Registry;

if ($reports = fn_get_schema('reports', 'schema')) {
    $schema['central']['marketing']['items']['reports'] = [
        'href' => 'reports.view',
        'type' => 'title',
        'position' => 900
    ];

    foreach ($reports as $report_name => $report) {
        if (isset($report['allowed_for']) && !fn_allowed_for($report['allowed_for'])) continue;
        if (isset($report['condition']) && !$report['condition']) continue;

        $schema['central']['marketing']['items']['reports']['subitems'][$report_name] = array(
            'attrs' => array(
                'class'=>'is-addon'
            ),
            'href' => "reports.view?type=$report_name",
            'alt' => "reports.view?type=$report_name",
            'position' => $report['position'] ?? 0,
        );
    }
}

return $schema;
