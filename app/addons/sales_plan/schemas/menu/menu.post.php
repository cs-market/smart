<?php

$schema['central']['orders']['items']['sales_plan'] = array(
	'attrs' => array(
		'class'=>'is-addon'
	),
	'href' => 'reports.view?type=sales_report',
	'alt' => 'reports.view?type=sales_report',
	'position' => 910
);
$schema['central']['orders']['items']['sales_plan_old'] = array(
	'attrs' => array(
		'class'=>'is-addon'
	),
	'href' => 'reports.view?type=sales_report_old',
	'alt' => 'reports.view?type=sales_report_old',
	'position' => 915
);
$schema['central']['orders']['items']['category_reports'] = array(
	'attrs' => array(
		'class'=>'is-addon'
	),
	'href' => 'reports.view?type=category_report',
	'alt' => 'reports.view?type=category_report',
	'position' => 920
);
$schema['central']['orders']['items']['unsold_report'] = array(
	'attrs' => array(
		'class'=>'is-addon'
	),
	'href' => 'reports.view?type=unsold_report',
	'alt' => 'reports.view?type=unsold_report',
	'position' => 930
);
return $schema;