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

$schema = array(
	'border' => array(
		'label' => 'Border',
		'type' => 'checkbox',
		'name' => 'border',
		'render' => array(
			"border-width" => "1px",
			"border-style" => "solid",
		),
	),
	'border-radius' => array(
		'label' => 'Border radius',
		'type' => 'input',
		'name' => 'border-radius',
		'suffix' => 'px',
		'class' => 'input-micro',
		'render' => array(
			"-webkit-border-radius" => "#value",
			"-moz-border-radius" => "#value",
			"border-radius" => "#value",
		),
	),
	'border-color' => array(
		'label' => 'Border color',
		'type' => 'colorpicker',
		'name' => 'border-color',
	),
	'margin' => array(
		'label' => 'Margin',
		'type' => 'input',
		'name' => 'margin',
		'suffix' => 'px',
		'class' => 'input-mini',
	),
	'padding' => array(
		'label' => 'Padding',
		'type' => 'input',
		'name' => 'padding',
		'suffix' => 'px',
		'class' => 'input-mini',
	),
	'background-color' => array(
		'label' => 'Background color',
		'type' => 'colorpicker',
		'name' => 'background-color',
	),
	'font-family' => array(
		'label' => 'Font family',
		'type' => 'select',
		'name' => 'font-family',
		'variants' => array(
			'Arial' => 'Arial,Helvetica,sans-serif',
			'Arial Black' => 'Arial Black,Gadget,sans-serif',
			'Comic Sans MS' => 'Comic Sans MS,cursive',
			'Courier New' => 'Courier New,Courier,monospace',
			'Georgia' => 'Georgia,serif',
			'Open Sans Condensed' => 'Open Sans Condensed Bold,sans-serif',
			'Impact' => 'Impact,Charcoal,sans-serif',
			'Lucida Console' => 'Lucida Console,Monaco,monospace',
			'Lucida Sans Unicode' => 'Lucida Sans Unicode,Lucida Grande,sans-serif',
			'Palatino Linotype' => 'Palatino Linotype,Book Antiqua,Palatino,serif',
			'Tahoma' => 'Tahoma,Geneva,sans-serif',
			'Times New Roman' => 'Times New Roman,Times,serif',
			'Trebuchet MS' => 'Trebuchet MS,Helvetica,sans-serif',
			'Verdana' => 'Verdana,Geneva,sans-serif',
			'Gill Sans' => 'Gill Sans,Geneva,sans-serif',
		),
	),
	'bold' => array(
		'label' => 'Bold',
		'type' => 'checkbox',
		'name' => 'bold',
		'render' => array(
			"font-weight" => "Bold",
		),
	),
	'italic' => array(
		'label' => 'Italic',
		'type' => 'checkbox',
		'name' => 'italic',
		'render' => array(
			"font-style" => "italic",
		),
	),
	'underline' => array(
		'label' => 'Underline',
		'type' => 'checkbox',
		'name' => 'underline',
		'render' => array(
			"text-decoration" => "underline",
		),
	),
	'color' => array(
		'label' => 'Font color',
		'type' => 'colorpicker',
		'name' => 'color',
	),
	'font-size' => array(
		'label' => 'Font size',
		'type' => 'input',
		'name' => 'font-size',
		'suffix' => 'px',
		'class' => 'input-mini',
	),
	'rotate' => array(
		'label' => 'Rotate',
		'type' => 'input',
		'name' => 'rotate',
		'suffix' => 'deg',
		'class' => 'input-mini',
		'render' => array(
			"transform" => "rotate(#value#suffix)",
		),
	),
	'text-shadow' => array(
		'label' => 'Text shadow',
		'type' => 'input',
		'name' => 'text-shadow',
		'class' => 'input-xxlarge',
		'tooltip' => '<a href="https://html-css-js.com/css/generator/text-shadow/" target="_blank">https://html-css-js.com/css/generator/text-shadow/</a>'
	),
	'box-shadow' => array(
		'label' => 'Box shadow',
		'type' => 'input',
		'name' => 'box-shadow',
		'class' => 'input-xxlarge',
		'tooltip' => '<a href="https://www.cssmatic.com/box-shadow" target="_blank">https://www.cssmatic.com/box-shadow</a>'
	),
	'opacity' => array(
		'label' => 'opacity',
		'type' => 'slider',
		'name' => 'opacity',
		'class' => 'input-xlarge',
	),
);

return $schema;