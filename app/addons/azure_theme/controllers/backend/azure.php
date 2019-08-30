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

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	return;
}

if ($mode == 'install_demo_data') {
	
	$bak_request = $_REQUEST;

	Registry::set('runtime.allow_upload_external_paths', true);
	Registry::set('sharing_owner.banners', fn_get_default_company_id());

	$banners = fn_get_schema('demo_data', 'banners');
	$_REQUEST = array();
	$_REQUEST['banners_main_image_data'][0] = array(
		'pair_id' => '',
		'type' => 'M',
		'object_id' => 0,
		'image_alt' => '',
	);
	$_REQUEST['type_banners_main_image_icon'][0] = 'server';
	foreach ($banners as $key => &$banner_data) {
		$_REQUEST['file_banners_main_image_icon'][0] = $banner_data['image_path'];
		$banner_id = fn_banners_update_banner($banner_data, 0);
	}


	$page_id = db_get_field('SELECT page_id FROM ?:pages WHERE parent_id = ?i AND page_type = ?s', 0, PAGE_TYPE_BLOG);
	$params = array();
	$params['page_type'] = PAGE_TYPE_BLOG;
	list($old_blogs) = fn_get_pages($params);
	unset($old_blogs[$page_id]);
	db_query("UPDATE ?:pages SET `status` = ?s WHERE page_id in (?a)", 'H', array_keys($old_blogs));

	db_query("UPDATE ?:addons SET `priority` = ?i WHERE addon = ?s", 900, 'blog');


	Registry::set('sharing_owner.pages', fn_get_default_company_id());
	$blogs = fn_get_schema('demo_data', 'blog');
	$_REQUEST = array();
	$_REQUEST['blog_image_image_data'][0] = array(
		'pair_id' => '',
		'type' => 'M',
		'object_id' => 0,
		'image_alt' => '',
	);
	$_REQUEST['type_blog_image_image_icon'][0] = 'server';
	foreach ($blogs as $key => &$blog) {
		$blog['parent_id'] = $page_id;
		$_REQUEST['file_blog_image_image_icon'][0] = $blog['image_path'];
		$banner_id = fn_update_page($blog, 0);
	}
	
	Registry::set('runtime.allow_upload_external_paths', false);

	$menus = fn_get_schema('demo_data', 'menu');
	$func = is_callable('fn_update_static_data') ? 'fn_update_static_data' : 'fn_azure_update_static_data';

	foreach ($menus as $key => &$menu) {
		$func($menu, 0, 'A');
	}

	$_REQUEST = $bak_request;
	fn_clear_cache();
	fn_clear_template_cache();
	fn_set_notification('N', __("notice"), __("installed"));
	fn_redirect('');
}

function fn_azure_update_static_data($data, $param_id, $section, $lang_code = DESCR_SL)
{
    $current_id_path = '';
    $schema = fn_get_schema('static_data', 'schema');
    $section_data = $schema[$section];

    if (!empty($section_data['has_localization'])) {
        $data['localization'] = empty($data['localization']) ? '' : fn_implode_localizations($data['localization']);
    }

    if (!empty($data['megabox'])) { // parse megabox value
        foreach ($data['megabox']['type'] as $p => $v) {
            if (!empty($v)) {
                $data[$p] = $v . ':' . intval($data[$p][$v]) . ':' . $data['megabox']['use_item'][$p];
            } else {
                $data[$p] = '';
            }
        }
    }

    $condition = db_quote('param_id = ?i', $param_id);

    fn_set_hook('update_static_data', $data, $param_id, $condition, $section, $lang_code);

    if (!empty($param_id)) {
        $current_id_path = db_get_field("SELECT id_path FROM ?:static_data WHERE $condition");
        db_query("UPDATE ?:static_data SET ?u WHERE param_id = ?i", $data, $param_id);
        db_query('UPDATE ?:static_data_descriptions SET ?u WHERE param_id = ?i AND lang_code = ?s', $data, $param_id, $lang_code);
    } else {
        $data['section'] = $section;

        $param_id = $data['param_id'] = db_query("INSERT INTO ?:static_data ?e", $data);
        foreach (fn_get_translation_languages() as $data['lang_code'] => $_v) {
            db_query('REPLACE INTO ?:static_data_descriptions ?e', $data);
        }
    }

    // Generate ID path
    if (isset($data['parent_id'])) {
        if (!empty($data['parent_id'])) {
            $new_id_path = db_get_field("SELECT id_path FROM ?:static_data WHERE param_id = ?i", $data['parent_id']);
            $new_id_path .= '/' . $param_id;
        } else {
            $new_id_path = $param_id;
        }

        if (!empty($current_id_path) && $current_id_path != $new_id_path) {
            db_query("UPDATE ?:static_data SET id_path = CONCAT(?s, SUBSTRING(id_path, ?i)) WHERE id_path LIKE ?l", "$new_id_path/", strlen($current_id_path . '/') + 1, "$current_id_path/%");
        }
        db_query("UPDATE ?:static_data SET id_path = ?s WHERE param_id = ?i", $new_id_path, $param_id);
    }

    if (!empty($section_data['icon'])) {
        fn_attach_image_pairs('static_data_icon', $section_data['icon']['type'], $param_id, $lang_code);
    }

    return $param_id;
}