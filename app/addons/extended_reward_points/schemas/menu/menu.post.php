<?php

$position = 980;
$report = 'reward_points_report';
$schema['central']['orders']['items'][$report] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => "reports.view?type=$report",
    'alt' => "reports.view?type=$report",
    'position' => $position
);

return $schema;
