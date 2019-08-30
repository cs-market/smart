<?php

use Tygh\Registry;

$company_id = fn_get_default_company_id();
$url = Registry::get('config.dir.addons') . 'azure_theme/demo_data/images/';

$schema = array(
	'Wide banner' => array (
		'banner' => 'Wide banner',
	    'company_id' => $company_id,
	    'position' => 0,
	    'type' => 'G',
	    'description' => "",
	    'target' => 'T',
	    'status' => 'A',
	    'image_path' => $url . 'wide_banner.jpg'
	),
	'Banner 1' => array (
		'banner' => 'Banner 1',
	    'company_id' => $company_id,
	    'position' => 0,
	    'type' => 'G',
	    'description' => "<p>iPhone SE takes an incredibly popular design and refines it even further. Crafted from bead-blasted aluminum for a satin-like finish, this light and compact phone is designed to fit comfortably in your hand. A brilliant 4‑inch1 Retina display makes everything look vibrant and sharp. And matte-chamfered edges and a color-matched stainless steel logo finish the look.
			</p>",
	    'target' => 'T',
	    'status' => 'A',
	    'image_path' => $url . 'banner1.jpg'
	),
	'Banner 2' => array (
		'banner' => 'Banner 2',
	    'company_id' => $company_id,
	    'position' => 0,
	    'type' => 'G',
	    'description' => "<p>If you’re a keen photographer of wildlife or the night sky, the 16-megapixel COOLPIX P900’s incredible 83x optical zoom lets you capture details not visible to the naked eye.
			</p> ",
	    'target' => 'T',
	    'status' => 'A',
	    'image_path' => $url . 'banner2.jpg'
	),
	'Banner 3' => array (
		'banner' => 'Banner 3',
	    'company_id' => $company_id,
	    'position' => 0,
	    'type' => 'G',
	    'description' => "<p>Head-mounted displays, or HMDs, are an almost ancient piece of tech 
			which have begun to see a reboot in the past few years as computers get 
			more powerful, and the games inside them more visually spectacular by 
			the day.
			</p>",
	    'target' => 'T',
	    'status' => 'A',
	    'image_path' => $url . 'banner3.jpg'
	),
	'Banner 4' => array (
		'banner' => 'Banner 4',
	    'company_id' => $company_id,
	    'position' => 0,
	    'type' => 'G',
	    'description' => "<p>Which security system is the best? We review and rank the top 
			security systems for you. Find prices, equipment, and monitoring for the
			 top security brands.
			</p>",
	    'target' => 'T',
	    'status' => 'A',
	    'image_path' => $url . 'banner4.jpg'
	),
	'Banner 5' => array (
		'banner' => 'Banner 5',
	    'company_id' => $company_id,
	    'position' => 0,
	    'type' => 'G',
	    'description' => "<p>Join a vibrant multiplayer community of people like you. Get free and
			 discounted games. Become a member for as low as $4.99 per month.
			</p>",
	    'target' => 'T',
	    'status' => 'A',
	    'image_path' => $url . 'banner5.jpg'
	),
	'Banner 6' => array (
		'banner' => 'Banner 6',
	    'company_id' => $company_id,
	    'position' => 0,
	    'type' => 'G',
	    'description' => "<p>Experience richer, more luminous colors in games like Gears of War 4 
			and Forza Horizon 3. With a higher contrast ratio between lights and 
			darks, High Dynamic Range technology brings out the true visual depth of
			 your games.
			</p>",
	    'target' => 'T',
	    'status' => 'A',
	    'image_path' => $url . 'banner6.jpg'
	),
);

return $schema;
