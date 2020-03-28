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

use Tygh\Commerceml\SDRusEximCommerceml;
use Tygh\Commerceml\Logs;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$path_file = 'exim/1C_' . date('dmY') . '/';
$path = fn_get_files_dir_path() . $path_file;
$path_commerceml = fn_get_files_dir_path();
$log = new Logs($path_file, $path);
$company_id = fn_get_runtime_company_id();
$exim_commerceml = new SDRusEximCommerceml(Tygh::$app['db'], $log, $path_commerceml);
list($status, $user_data, $user_login, $password, $salt) = fn_auth_routines($_data, array());
$exim_commerceml->import_params['user_data'] = $auth;

list($cml, $s_commerceml) = $exim_commerceml->getParamsCommerceml();
$s_commerceml = $exim_commerceml->getCompanySettings();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$suffix = '';

	if ($mode == 'sd_save_offers_data') {
		if ($s_commerceml['exim_1c_create_prices'] == 'Y') {
			$prices = $_REQUEST['prices_1c'];
			if (!empty($_REQUEST['list_price_1c'])) {
				$_list_prices = fn_explode(',', $_REQUEST['list_price_1c']);
				$list_prices = array();
				foreach($_list_prices as $_list_price) {
					$list_prices[] = array(
							'price_1c' => trim($_list_price),
							'usergroup_id' => 0,
							'type' => 'list',
							'company_id' => $company_id
					);
				}
				$prices = fn_array_merge($list_prices, $prices, false);
			}

			$base_prices = array();
			if (!empty($_REQUEST['base_price_1c'])) {
				$_base_prices = fn_explode(',', $_REQUEST['base_price_1c']);
				foreach($_base_prices as $_base_price) {
					$base_prices[] = array(
						'price_1c' => trim($_base_price),
						'usergroup_id' => 0,
						'type' => 'base',
						'company_id' => $company_id
					);
				}
			}
			$prices = fn_array_merge($base_prices, $prices, false);

			db_query("DELETE FROM ?:rus_exim_1c_prices WHERE company_id = ?i", $company_id);
			foreach ($prices as $price) {
				if (!empty($price['price_1c'])) {
					$price['company_id'] = $company_id;
					db_query("INSERT INTO ?:rus_exim_1c_prices ?e", $price);
				}
			}
		}

		return array(CONTROLLER_STATUS_REDIRECT, 'commerceml.offers');
	}
}

if ($mode =='monolith' && !empty($action)) {
	fn_print_die(fn_monolith_generate_xml($action));
}
if ($mode == 'sync') {
	$params = $_REQUEST;

	$manual = true;
	//unset($_SESSION['exim_1c']);
	$lang_code = (!empty($s_commerceml['exim_1c_lang'])) ? $s_commerceml['exim_1c_lang'] : CART_LANGUAGE;

	$exim_commerceml->getDirCommerceML();
	$exim_commerceml->import_params['lang_code'] = $lang_code;
	$exim_commerceml->import_params['manual'] = true;
	$exim_commerceml->company_id = Registry::get('runtime.company_id');
	if ($action == 'import') {
		$filename = (!empty($params['filename'])) ? fn_basename($params['filename']) : 'import.xml';
		$fileinfo = pathinfo($filename);
		list($xml, $d_status, $text_message) = $exim_commerceml->getFileCommerceml($filename);
		$exim_commerceml->addMessageLog($text_message);

		if ($d_status === false) {
			fn_echo("failure");
			exit;
		}

		if ($s_commerceml['exim_1c_import_products'] != 'not_import') {
			$exim_commerceml->importDataProductFile($xml);
		} else {
			fn_echo("success\n");
		}
	}
	if ($action == 'offers') {
		$filename = (!empty($params['filename'])) ? fn_basename($params['filename']) : 'offers.xml';
		$fileinfo = pathinfo($filename);
		list($xml, $d_status, $text_message) = $exim_commerceml->getFileCommerceml($filename);
		$exim_commerceml->addMessageLog($text_message);
		if ($d_status === false) {
			fn_echo("failure");
			exit;
		}
		if ($s_commerceml['exim_1c_only_import_offers'] == 'Y') {
			$exim_commerceml->importDataOffersFile($xml, $service_exchange, $lang_code, $manual);
		} else {
			fn_echo("success\n");
		}
	}
	fn_print_die('done');
} elseif ($mode == 'base_price' && $action) {
	list($products,) = fn_get_products(['company_id' => $action]);
	$auth = $_SESSION['auth'];
	foreach ($products as $product_id => $p) {
		$product = fn_get_product_data($product_id, $auth, DESCR_SL, '', false, false, false, true);
		if (count(($product['prices'])) > 1) {
				fn_print_die($product['prices'], $product_id);
				$prices = array_column($product['prices'], 'price');
				$price = max($prices);
				$product['price'] = $price;
				fn_update_product($product, $product_id, DESCR_SL);
		}
	}
	fn_print_die('done');
} elseif ($mode == 'replace_manager') {
	list($users) = fn_get_users(array('managers' => 1132));
	$counter = 0;
	foreach ($users as $user) {
		$managers = db_get_fields('SELECT vendor_manager FROM ?:vendors_customers WHERE customer_id = ?i', $user['user_id']);
		if ($managers && in_array('1132', $managers) && !in_array('3760', $managers)) {
			$counter += 1;
			$udata = array('customer_id' => $user['user_id'], 'vendor_manager' => 3760);
			db_query('INSERT INTO ?:vendors_customers ?e', $udata);
			db_query('DELETE FROM ?:vendors_customers WHERE customer_id = ?i AND vendor_manager = ?i', $user['user_id'], 1132);
		}
	}
	fn_print_die('done', $counter);
} elseif ($mode == 'pservice_sku') {
	$params = array('company_id' => 28);
	list($products, ) = fn_get_products($params);
	foreach ($products as $pid => $product) {
		$pcode = trim($product['product_code']);
		if (strlen($pcode) < 11) {
				$pcode = str_pad($pcode, 11, "0", STR_PAD_LEFT);
				db_query('UPDATE ?:products SET product_code = ?s WHERE product_id = ?i;', $pcode, $pid);
		}
	}
	fn_print_die('stop');
} elseif ($mode == 'get_profiles') {
	$report = db_get_array("SELECT up.user_id, count(profile_id) as count, firstname, lastname, phone, email FROM ?:user_profiles AS up LEFT JOIN ?:users AS u ON u.user_id = up.user_id GROUP BY user_id HAVING count(profile_id) > 1 ");
	$params['filename'] = 'profiles.csv';
	$params['force_header'] = true;
	$export = fn_exim_put_csv($report, $params, '"');
} elseif ($mode == 'devide_pinta') {
	$file = 'var/files/pinta1.csv';
	$content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false) );
	$sku = array_column($content, 'Номенклатура.Код');
	array_walk($sku, 'fn_trim_helper');
	//list($pinta_products, ) = fn_get_products(array('company_id' => 41));
	//fn_print_die(count($pinta_products));
	//$products = db_get_hash_single_array('SELECT product_id, product_code FROM ?:products WHERE product_code IN (?a) AND company_id = ?i', array('product_id', 'product_code'), $sku, 41);
	$products = db_get_fields('SELECT product_code FROM ?:products WHERE product_code IN (?a) AND company_id = ?i',  $sku, 41);
	$unexist_products = array_diff($sku, $products);
	fn_print_die($sku, $products, $unexist_products, count($unexist_products), count($products));
	//fn_print_die('here');
} elseif ($mode == 'delete_pinta') {
	$pids = db_get_fields("SELECT product_id FROM ?:products WHERE 1 AND company_id in (?a)", array('41', '46'));
	$counter = 0;
	foreach ($pids as $pid) {
		if (fn_delete_product($pid)) {
			$counter += 1;
		}
	}
	fn_print_die($counter);
} elseif ($mode == 'correct_molvest') {
	$params = array('company_id' => 13);
	list($products, ) = fn_get_products($params);
	foreach($products as $product) {
		fn_get_product_prices($product['product_id'], $product, $auth);
		if (!empty($product['prices'])) {
			$old_price = $product['price'];
			$prices = fn_array_column($product['prices'], 'price');
			$product['price'] = max($prices);
			if ($old_price != $product_price) {
				fn_update_product_prices($product['product_id'], $product);
			}
		}
	}
	fn_print_die('end');
} elseif ($mode == 'correct_molvest2') {
	$params = array('company_id' => 13);
	list($products, ) = fn_get_products($params);
    fn_gather_additional_products_data($products, array(
        'get_icon' => false,
        'get_detailed' => false,
        'get_additional' => false,
        'get_options' => false,
        'get_discounts' => false,
        'get_features' => true,
        'features_display_on' => 'A',
    ));
	$product_groups = fn_array_group($products, 'product_code');
	$empty_codes = array();
	foreach ($product_groups as $code => $products) {
		$barcode = '';
		foreach ($products as $product) {
			if (!empty($product['product_features']['87']['variant'])) {
				$barcode = $product['product_features']['87']['variant'];
				break;
			}
		}
		if (!empty($barcode)) {
			db_query('UPDATE ?:products SET ?u WHERE product_id IN (?a)',array('product_code' => $barcode), fn_array_column($products, 'product_id'));
		} else {
			// remember code wo barcode
			$empty_codes[] = $code;
		}

	}
	fn_print_die($empty_codes);
	fn_print_die('end');
} elseif ($mode == 'correct_molvest3') {
	$products = array('1424', '1893', '2580', '2581', '2582', '2583', '2584', '2585', '2586', '2587', '2907', '6085', '6087', '6089', '6104', '6105', '6106', '6107', '6109', '6110', '6112', '6113', '6114', '6115', '6116', '6117', '6118', '6119', '6123', '6124', '6125', '6126', '6127', '6128', '6129', '6130', '6131', '6133', '6134', '6136', '6150', '6152', '6153', '6154', '6155', '6156', '6157', '6158', '6159', '6160', '6161', '6162', '6163', '6164', '6190', '6191', '6192', '6193', '6213', '6216', '6217', '6219', '6221', '6223', '6229', '6230', '6231', '6232', '6233', '6234', '6235', '6236', '6237', '6238', '6239', '6240', '6241', '6242', '6243', '6244', '6249', '6253', '6254', '6255', '6256', '6257', '6261', '6262', '6263', '6264', '6266', '6267', '6268', '6269', '6270', '6271', '6272', '6273', '6274', '6275', '6276', '6286', '6289', '6290', '6291', '6292', '6294', '6295', '6296', '6297', '6300', '6301', '6302', '6303', '6304', '6305', '6306', '6307', '6308', '6329', '6330', '6331', '6332', '6349', '6350', '6351', '6352', '6353', '6354', '6355', '6356', '6357', '6358', '6359', '6360', '6361', '6362', '6363', '6364', '6366', '6367', '6368', '6369', '6370', '6380', '6381', '6382', '6383', '6384', '6385', '6386', '6387', '6389', '6390', '6397', '11228', '11244', '11278', '11280', '11281', '11282', '11283', '11287', '11288', '11289', '11290', '11291', '11293', '11295', '11296', '11297', '11298', '11299', '11300', '11302', '11303', '11304', '11305', '11306', '11307', '11308', '11310', '11314', '11317', '11318', '11319', '11320', '11321', '11322', '12493');
	db_query('DELETE FROM ?:products WHERE company_id = 13 AND product_id NOT IN (?a)', $products);
	$pids = db_get_fields('SELECT product_id FROM ?:products WHERE company_id = 13');
	fn_print_die($products, $pids);
} elseif ($mode == 'pinta_job') {
	$ugroups = array('1 розница С (общий)', '1 розница С (сетевой)', '1 розница С (спец)', '1 розница С (средний)', '1ВИП ОБЩИЙ', '1ВИП СЕТЕВОЙ', '1ВИП СПЕЦ', '1ВИП СРЕДНИЙ', '2 розница БЕЗ (общий)', '2 розница БЕЗ (сетевой)', '2 розница БЕЗ (спец)', '2 розница БЕЗ (средний)', '2ВИП', '3ВИП', 'VIP1', 'Аблогина', 'Абросимова', 'Авдокунин', 'Аветисян ЭА', 'Айдуллин', 'Акопян', 'Алексашин', 'Альфа плюс', 'Алябьева', 'Аляшетдинова', 'Амиров', 'Андреев', 'Андреев Черкизовская', 'Андриянова', 'Андрюшин', 'Аникеев', 'Анохина', 'Артюшкин', 'Ашетов', 'Барбасов', 'Бардина', 'Басаев', 'Белова', 'Бельчиков', 'Бесчастнов', 'Бирмаги', 'Бирмаги+2', 'Боголюбский', 'Бойко', 'Борисов', 'Бочка', 'Бочонок', 'Брейкина', 'Бугров', 'Бузажи', 'Бузанов', 'Булычев', 'Бунин', 'Бутков', 'Буторин', 'Бушуева', 'Вартанян', 'Васильев', 'Васильев ВА', 'Векта', 'Вербицкий', 'Ветерок', 'Ветров', 'Виноградова', 'Возрождение', 'Волкова СБ', 'Волкова Солнечногорск', 'Волна', 'Воронков', 'Гаврилов', 'Галахова', 'Галичян', 'Гастроном', 'Герб', 'Гетман', 'Голованов', 'Голубков', 'Гольцов', 'Гордиенко', 'Гороненкова', 'Госселайн', 'Грабчук', 'Графский', 'Грачев', 'Григоровский', 'Григорян', 'Гришаева', 'Гришина ЕГ', 'Груненкова', 'Гуськов', 'Гутова', 'Данилов', 'Дарья', 'Дистрибьютор', 'Дорожкин', 'Дроботов', 'Дубровин', 'Евтухов', 'Егорова', 'Ельников', 'Еремин', 'Еремин Мытищи', 'Ермолаев Ивановка', 'Ермолаева', 'Жарко', 'Желтый полосатик', 'Журавлева', 'Зайцев', 'Захаркин', 'Защитин', 'Зеленский ', 'Зиновьев', 'Ибряев', 'Иван', 'Извекова', 'Ильясов', 'Импульс', 'Ионов', 'Ирна', 'Исаев', 'Кабанова', 'Казарян', 'Калинин', 'Калиш', 'Камордин', 'Карахова', 'Карелов', 'Карпов Гордеев', 'Карпов лобня', 'Карпушин', 'Кафари', 'Кашенцев', 'Квашнина', 'Киба', 'Кильдишев', 'Кириченко', 'Кирсанова', 'Клебанов', 'Ковалев Сватково', 'Ковалева НМ', 'Ковальчук', 'Козлов', 'Козлова', 'Кокорников', 'Колбанов', 'Колотилин', 'Кольчурин', 'Комова', 'Кононенко', 'Конышев', 'Костюкова', 'Котина', 'Кох', 'КП', 'КП Воронцово', 'Красноярский', 'Кручинин', 'Крылова', 'Кудашов', 'Кудиков', 'Кузнецов', 'Кулешов Чонгарский', 'Куликов Мытищи', 'Куприков', 'Курейкина', 'Куторкина', 'Лазакович', 'Лазутко', 'Лебедев', 'Лебедева', 'Липский', 'Лисай', 'Лискевич', 'Литвинов', 'Лобачева', 'Лопотовский', 'Лысенко', 'Майструк', 'Малашков', 'Маренин', 'Маринин', 'Маркус', 'Маркушина', 'Мартынова', 'Махрин', 'Медведева', 'Мильке', 'Милюхин Верн', 'Мирошников П', 'Мирошников Романенко', 'Мирошникова', 'Михайловский', 'Михалчич', 'Мишин', 'Мосторг', 'Мохнатов', 'Мытищинская ярмарка', 'Назарова Ч', 'Наумников', 'Некрасов', 'Никитин', 'Николаева Элекстросталь', 'Никонова', 'НордОстТрейдинг', 'Носов', 'Оганесян', 'Опт', 'Опт Коми', 'Осемь', 'Осколкова', 'Охотин', 'Павел Литра', 'Павлов Балашиха', 'Пайтян', 'Панова', 'Парадников', 'Партнер', 'Пастернак', 'Перепелкин', 'Петров Ш', 'Петушков', 'Пешков', 'Пивариум', 'Пивиндустрия', 'Пивное изобилие', 'Пиво воды', 'Пиф Паф', 'Подковский', 'Поеленков', 'Покрышевский', 'Полежаев', 'Попович РБО', 'Попович РСО', 'Пронькин', 'Просина', 'ПС', 'Пузиков', 'Пунько', 'Путилин', 'Пятница', 'Раченков', 'Ремезова', 'Родников', 'Романов', 'Русаков', 'Русское пиво ', 'Рыськов ', 'Савенко', 'Савкина', 'Седова', 'Семенов', 'Семин', 'Сепа', 'Сервис Групп', 'Сидоров', 'Сильченко', 'Синодский', 'Сировский', 'Склемин', 'Скуратова', 'Смирнова', 'Смирнова Посад', 'Соло', 'Спиридонов', 'Спирин', 'Стрельников', 'Стремин', 'Стригалева', 'Сухов', 'Сущик', 'Таганов', 'Таир', 'Тайм аут', 'Тапаков', 'Тарабурин', 'Тереховский', 'Тетрис Рубикон', 'Титаев', 'ТПЗ', 'Триумф', 'Труфанова', 'ТСС', 'Тузов', 'Тулубаев', 'Туманова', 'Тупиков', 'Тюков', 'Тюрина ', 'Тяпкина', 'Ульянов', 'Федоренков', 'Ферко', 'Фортуна', 'франш Екатеринбург', 'Франшиза Сетевой', 'Франшиза Спец', 'Франшиза Средний', 'Хачатурян', 'Хлоповской', 'Холопова', 'Хренков', 'Цветковская', 'Чаплин', 'Чернов М', 'ЧЛ Артем', 'ЧЛ Зеленоград', 'ЧЛ Лагода', 'ЧЛ Михаил Келлер', 'Чумичев', 'Швецов', 'Шемельфейнинг', 'Шемякова', 'Шестаков', 'Шилов', 'Шинкарева', 'Ширшова', 'Шишкин', 'Шишкин Сосенское', 'Шкодина', 'Шмаков', 'Шмегленко', 'Шмелев', 'Шустов', 'Экстра Люкс', 'Элебас алябьева', 'Юдаев', 'Юрин волоколамка', 'Яблочник', 'Яковлева', 'Якушин', 'Ярлушкина', 'Яцюк', 'ОБЩИЙ', 'СЕТЕВОЙ', 'СПЕЦ', 'СРЕДНИЙ');
	$ugroups = array_map('trim', $ugroups);
	$ugroup = array('usergroup' => '', 'type' => 'C', 'status' => 'A');
	$usergroups = array();
	foreach ($ugroups as $ug_name) {
		$ugroup['usergroup'] = 'Пинта ' . $ug_name;
		$ug_id = db_get_field('SELECT usergroup_id FROM ?:usergroup_descriptions WHERE usergroup = ?s', $ugroup['usergroup']);
		if (!$ug_id) {
			$ug_id = fn_update_usergroup($ugroup);
		}
		$usergroups[$ug_id] = $ugroup['usergroup'];

	}

	$pinta_users_ug = db_get_field('SELECT usergroup_id FROM ?:usergroup_descriptions WHERE usergroup = ?s', 'Пинта Пользователи');

	// add usergroups for vendor plans
	$res = db_quote('UPDATE ?:vendor_plans SET `usergroup_ids` = ?s WHERE plan_id in (?a)', $pinta_users_ug . ','. implode(',', array_keys($usergroups)), array(24,30));

	// add usergroups for categories
	foreach ([24, 30] as $plan_id) {
		$category_ids = db_get_field('SELECT categories FROM ?:vendor_plans WHERE plan_id = ?i', $plan_id);
		$category_ids = explode(',', $category_ids);

		foreach ($category_ids as $cid) {
			$current_category_usergroups = db_get_field('SELECT usergroup_ids FROM ?:categories WHERE category_id = ?i', $cid);
			if (!empty($current_category_usergroups)) {
				$current_category_usergroups = explode(',', $current_category_usergroups);
				foreach ($current_category_usergroups as $key => $ug_id) {
					if (in_array($ug_id, array_keys($usergroups))) {
						unset($current_category_usergroups[$key]);
					}
				}
			}
			$current_category_usergroups[] = $pinta_users_ug;
			$current_category_usergroups = implode(',', $current_category_usergroups);
			db_query('UPDATE ?:categories SET `usergroup_ids` = ?s WHERE category_id = ?i', $current_category_usergroups, $cid);
		}
	}

	// add usergroups for products
	$products = db_get_hash_single_array('SELECT product_id, usergroup_ids FROM ?:products WHERE company_id IN (?a)',array('product_id', 'usergroup_ids'), array(41,46));
	foreach ($products as $product_id => $current_product_usergroups) {
		// fantoms
		if (in_array($product_id, array(23647, 23646))) {
			fn_delete_product($product_id);
			continue;
		}
		if ($current_product_usergroups == '0') {
			$current_product_usergroups = $pinta_users_ug;
		} else {
			$current_product_usergroups = explode(',', $current_product_usergroups);

			foreach ($current_product_usergroups as $key => $ug_id) {

				if (in_array($ug_id, array_keys($usergroups))) {
					unset($current_product_usergroups[$key]);
				}
			}
			$current_product_usergroups[] = $pinta_users_ug;
			db_query('UPDATE ?:products SET `usergroup_ids` = ?s WHERE product_id = ?i', $current_category_usergroups, $product_id);
		}
	}

	// add usergroups for products
	Registry::set('runtime.company_id', 41);
	list($pinta1_users, ) = fn_get_users(array('user_type' => 'C'));
	$pinta_users = fn_array_column($pinta1_users, 'user_id');
	Registry::set('runtime.company_id', 0);
	foreach ($pinta_users as $user_id) {
		fn_change_usergroup_status('A', $user_id, $pinta_users_ug);
	}
	fn_print_die('end');
} elseif ($mode == 'pinta_job1') {
	$ugroups = array('1 розница С (общий)', '1 розница С (сетевой)', '1 розница С (спец)', '1 розница С (средний)', '1ВИП ОБЩИЙ', '1ВИП СЕТЕВОЙ', '1ВИП СПЕЦ', '1ВИП СРЕДНИЙ', '2 розница БЕЗ (общий)', '2 розница БЕЗ (сетевой)', '2 розница БЕЗ (спец)', '2 розница БЕЗ (средний)', '2ВИП', '3ВИП', 'VIP1', 'Аблогина', 'Абросимова', 'Авдокунин', 'Аветисян ЭА', 'Айдуллин', 'Акопян', 'Алексашин', 'Альфа плюс', 'Алябьева', 'Аляшетдинова', 'Амиров', 'Андреев', 'Андреев Черкизовская', 'Андриянова', 'Андрюшин', 'Аникеев', 'Анохина', 'Артюшкин', 'Ашетов', 'Барбасов', 'Бардина', 'Басаев', 'Белова', 'Бельчиков', 'Бесчастнов', 'Бирмаги', 'Бирмаги+2', 'Боголюбский', 'Бойко', 'Борисов', 'Бочка', 'Бочонок', 'Брейкина', 'Бугров', 'Бузажи', 'Бузанов', 'Булычев', 'Бунин', 'Бутков', 'Буторин', 'Бушуева', 'Вартанян', 'Васильев', 'Васильев ВА', 'Векта', 'Вербицкий', 'Ветерок', 'Ветров', 'Виноградова', 'Возрождение', 'Волкова СБ', 'Волкова Солнечногорск', 'Волна', 'Воронков', 'Гаврилов', 'Галахова', 'Галичян', 'Гастроном', 'Герб', 'Гетман', 'Голованов', 'Голубков', 'Гольцов', 'Гордиенко', 'Гороненкова', 'Госселайн', 'Грабчук', 'Графский', 'Грачев', 'Григоровский', 'Григорян', 'Гришаева', 'Гришина ЕГ', 'Груненкова', 'Гуськов', 'Гутова', 'Данилов', 'Дарья', 'Дистрибьютор', 'Дорожкин', 'Дроботов', 'Дубровин', 'Евтухов', 'Егорова', 'Ельников', 'Еремин', 'Еремин Мытищи', 'Ермолаев Ивановка', 'Ермолаева', 'Жарко', 'Желтый полосатик', 'Журавлева', 'Зайцев', 'Захаркин', 'Защитин', 'Зеленский ', 'Зиновьев', 'Ибряев', 'Иван', 'Извекова', 'Ильясов', 'Импульс', 'Ионов', 'Ирна', 'Исаев', 'Кабанова', 'Казарян', 'Калинин', 'Калиш', 'Камордин', 'Карахова', 'Карелов', 'Карпов Гордеев', 'Карпов лобня', 'Карпушин', 'Кафари', 'Кашенцев', 'Квашнина', 'Киба', 'Кильдишев', 'Кириченко', 'Кирсанова', 'Клебанов', 'Ковалев Сватково', 'Ковалева НМ', 'Ковальчук', 'Козлов', 'Козлова', 'Кокорников', 'Колбанов', 'Колотилин', 'Кольчурин', 'Комова', 'Кононенко', 'Конышев', 'Костюкова', 'Котина', 'Кох', 'КП', 'КП Воронцово', 'Красноярский', 'Кручинин', 'Крылова', 'Кудашов', 'Кудиков', 'Кузнецов', 'Кулешов Чонгарский', 'Куликов Мытищи', 'Куприков', 'Курейкина', 'Куторкина', 'Лазакович', 'Лазутко', 'Лебедев', 'Лебедева', 'Липский', 'Лисай', 'Лискевич', 'Литвинов', 'Лобачева', 'Лопотовский', 'Лысенко', 'Майструк', 'Малашков', 'Маренин', 'Маринин', 'Маркус', 'Маркушина', 'Мартынова', 'Махрин', 'Медведева', 'Мильке', 'Милюхин Верн', 'Мирошников П', 'Мирошников Романенко', 'Мирошникова', 'Михайловский', 'Михалчич', 'Мишин', 'Мосторг', 'Мохнатов', 'Мытищинская ярмарка', 'Назарова Ч', 'Наумников', 'Некрасов', 'Никитин', 'Николаева Элекстросталь', 'Никонова', 'НордОстТрейдинг', 'Носов', 'Оганесян', 'Опт', 'Опт Коми', 'Осемь', 'Осколкова', 'Охотин', 'Павел Литра', 'Павлов Балашиха', 'Пайтян', 'Панова', 'Парадников', 'Партнер', 'Пастернак', 'Перепелкин', 'Петров Ш', 'Петушков', 'Пешков', 'Пивариум', 'Пивиндустрия', 'Пивное изобилие', 'Пиво воды', 'Пиф Паф', 'Подковский', 'Поеленков', 'Покрышевский', 'Полежаев', 'Попович РБО', 'Попович РСО', 'Пронькин', 'Просина', 'ПС', 'Пузиков', 'Пунько', 'Путилин', 'Пятница', 'Раченков', 'Ремезова', 'Родников', 'Романов', 'Русаков', 'Русское пиво ', 'Рыськов ', 'Савенко', 'Савкина', 'Седова', 'Семенов', 'Семин', 'Сепа', 'Сервис Групп', 'Сидоров', 'Сильченко', 'Синодский', 'Сировский', 'Склемин', 'Скуратова', 'Смирнова', 'Смирнова Посад', 'Соло', 'Спиридонов', 'Спирин', 'Стрельников', 'Стремин', 'Стригалева', 'Сухов', 'Сущик', 'Таганов', 'Таир', 'Тайм аут', 'Тапаков', 'Тарабурин', 'Тереховский', 'Тетрис Рубикон', 'Титаев', 'ТПЗ', 'Триумф', 'Труфанова', 'ТСС', 'Тузов', 'Тулубаев', 'Туманова', 'Тупиков', 'Тюков', 'Тюрина ', 'Тяпкина', 'Ульянов', 'Федоренков', 'Ферко', 'Фортуна', 'франш Екатеринбург', 'Франшиза Сетевой', 'Франшиза Спец', 'Франшиза Средний', 'Хачатурян', 'Хлоповской', 'Холопова', 'Хренков', 'Цветковская', 'Чаплин', 'Чернов М', 'ЧЛ Артем', 'ЧЛ Зеленоград', 'ЧЛ Лагода', 'ЧЛ Михаил Келлер', 'Чумичев', 'Швецов', 'Шемельфейнинг', 'Шемякова', 'Шестаков', 'Шилов', 'Шинкарева', 'Ширшова', 'Шишкин', 'Шишкин Сосенское', 'Шкодина', 'Шмаков', 'Шмегленко', 'Шмелев', 'Шустов', 'Экстра Люкс', 'Элебас алябьева', 'Юдаев', 'Юрин волоколамка', 'Яблочник', 'Яковлева', 'Якушин', 'Ярлушкина', 'Яцюк');
	$ugroups = array_map('trim', $ugroups);

	$ugroup = array('usergroup' => '', 'type' => 'C', 'status' => 'A');
	$usergroups = array();
	foreach ($ugroups as $ug_name) {
		$ugroup['usergroup'] = 'Пинта ' . trim($ug_name);
		$ug_id = db_get_field('SELECT usergroup_id FROM ?:usergroup_descriptions WHERE usergroup = ?s', $ugroup['usergroup']);
		if (!$ug_id) {
			$ug_id = fn_update_usergroup($ugroup);
		}
		$usergroups[$ug_id] = $ugroup['usergroup'];
	}

	$file = 'pinta1.csv';
	$content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
	foreach ($content as $data) {
		$product_name = array_shift($data);
		$product_id = db_get_field('SELECT p.product_id FROM ?:product_descriptions AS pd LEFT JOIN ?:products AS p ON p.product_id = pd.product_id WHERE product = ?s AND company_id = ?i', trim($product_name), 41);
		if ($product_id) {
			$product_data = array('prices' => array());
			
			$prices = &$product_data['prices'];
			$prices[] = '';
			$data = call_user_func_array('fn_exim_import_price', array($data, ','));

			$price_data = array(
				'lower_limit' => 1,
				'price' => 0,
				'type' => 'A',
				'usergroup_id' => 0,
			);
			foreach ($data as $ug_name => $price_data['price']) {
				if (!empty($price_data['price'])){
					$price_data['usergroup_id'] = array_search('Пинта ' . trim($ug_name), $usergroups);
					if (!$price_data['usergroup_id']) {
						fn_print_die($usergroups, $ug_name);
					}
					$prices[] = $price_data;
				}
			}
			$prices[0] = array('price' => max(array_column($prices, 'price')), 'lower_limit' => 1);
			fn_update_product_prices($product_id, $product_data);
		} else {
			$unknown_products[] = $product_name;
		}
	}
	fn_print_die($unknown_products);
} elseif ($mode == 'pinta_job2') {
	$ugroups = array('1 розница С (общий)', '1 розница С (сетевой)', '1 розница С (спец)', '1 розница С (средний)', '1ВИП ОБЩИЙ', '1ВИП СЕТЕВОЙ', '1ВИП СПЕЦ', '1ВИП СРЕДНИЙ', '2 розница БЕЗ (общий)', '2 розница БЕЗ (сетевой)', '2 розница БЕЗ (спец)', '2 розница БЕЗ (средний)', '2ВИП', '3ВИП', 'VIP1', 'Аблогина', 'Абросимова', 'Авдокунин', 'Аветисян ЭА', 'Айдуллин', 'Акопян', 'Алексашин', 'Альфа плюс', 'Алябьева', 'Аляшетдинова', 'Амиров', 'Андреев', 'Андреев Черкизовская', 'Андриянова', 'Андрюшин', 'Аникеев', 'Анохина', 'Артюшкин', 'Ашетов', 'Барбасов', 'Бардина', 'Басаев', 'Белова', 'Бельчиков', 'Бесчастнов', 'Бирмаги', 'Бирмаги+2', 'Боголюбский', 'Бойко', 'Борисов', 'Бочка', 'Бочонок', 'Брейкина', 'Бугров', 'Бузажи', 'Бузанов', 'Булычев', 'Бунин', 'Бутков', 'Буторин', 'Бушуева', 'Вартанян', 'Васильев', 'Васильев ВА', 'Векта', 'Вербицкий', 'Ветерок', 'Ветров', 'Виноградова', 'Возрождение', 'Волкова СБ', 'Волкова Солнечногорск', 'Волна', 'Воронков', 'Гаврилов', 'Галахова', 'Галичян', 'Гастроном', 'Герб', 'Гетман', 'Голованов', 'Голубков', 'Гольцов', 'Гордиенко', 'Гороненкова', 'Госселайн', 'Грабчук', 'Графский', 'Грачев', 'Григоровский', 'Григорян', 'Гришаева', 'Гришина ЕГ', 'Груненкова', 'Гуськов', 'Гутова', 'Данилов', 'Дарья', 'Дистрибьютор', 'Дорожкин', 'Дроботов', 'Дубровин', 'Евтухов', 'Егорова', 'Ельников', 'Еремин', 'Еремин Мытищи', 'Ермолаев Ивановка', 'Ермолаева', 'Жарко', 'Желтый полосатик', 'Журавлева', 'Зайцев', 'Захаркин', 'Защитин', 'Зеленский ', 'Зиновьев', 'Ибряев', 'Иван', 'Извекова', 'Ильясов', 'Импульс', 'Ионов', 'Ирна', 'Исаев', 'Кабанова', 'Казарян', 'Калинин', 'Калиш', 'Камордин', 'Карахова', 'Карелов', 'Карпов Гордеев', 'Карпов лобня', 'Карпушин', 'Кафари', 'Кашенцев', 'Квашнина', 'Киба', 'Кильдишев', 'Кириченко', 'Кирсанова', 'Клебанов', 'Ковалев Сватково', 'Ковалева НМ', 'Ковальчук', 'Козлов', 'Козлова', 'Кокорников', 'Колбанов', 'Колотилин', 'Кольчурин', 'Комова', 'Кононенко', 'Конышев', 'Костюкова', 'Котина', 'Кох', 'КП', 'КП Воронцово', 'Красноярский', 'Кручинин', 'Крылова', 'Кудашов', 'Кудиков', 'Кузнецов', 'Кулешов Чонгарский', 'Куликов Мытищи', 'Куприков', 'Курейкина', 'Куторкина', 'Лазакович', 'Лазутко', 'Лебедев', 'Лебедева', 'Липский', 'Лисай', 'Лискевич', 'Литвинов', 'Лобачева', 'Лопотовский', 'Лысенко', 'Майструк', 'Малашков', 'Маренин', 'Маринин', 'Маркус', 'Маркушина', 'Мартынова', 'Махрин', 'Медведева', 'Мильке', 'Милюхин Верн', 'Мирошников П', 'Мирошников Романенко', 'Мирошникова', 'Михайловский', 'Михалчич', 'Мишин', 'Мосторг', 'Мохнатов', 'Мытищинская ярмарка', 'Назарова Ч', 'Наумников', 'Некрасов', 'Никитин', 'Николаева Элекстросталь', 'Никонова', 'НордОстТрейдинг', 'Носов', 'Оганесян', 'Опт', 'Опт Коми', 'Осемь', 'Осколкова', 'Охотин', 'Павел Литра', 'Павлов Балашиха', 'Пайтян', 'Панова', 'Парадников', 'Партнер', 'Пастернак', 'Перепелкин', 'Петров Ш', 'Петушков', 'Пешков', 'Пивариум', 'Пивиндустрия', 'Пивное изобилие', 'Пиво воды', 'Пиф Паф', 'Подковский', 'Поеленков', 'Покрышевский', 'Полежаев', 'Попович РБО', 'Попович РСО', 'Пронькин', 'Просина', 'ПС', 'Пузиков', 'Пунько', 'Путилин', 'Пятница', 'Раченков', 'Ремезова', 'Родников', 'Романов', 'Русаков', 'Русское пиво ', 'Рыськов ', 'Савенко', 'Савкина', 'Седова', 'Семенов', 'Семин', 'Сепа', 'Сервис Групп', 'Сидоров', 'Сильченко', 'Синодский', 'Сировский', 'Склемин', 'Скуратова', 'Смирнова', 'Смирнова Посад', 'Соло', 'Спиридонов', 'Спирин', 'Стрельников', 'Стремин', 'Стригалева', 'Сухов', 'Сущик', 'Таганов', 'Таир', 'Тайм аут', 'Тапаков', 'Тарабурин', 'Тереховский', 'Тетрис Рубикон', 'Титаев', 'ТПЗ', 'Триумф', 'Труфанова', 'ТСС', 'Тузов', 'Тулубаев', 'Туманова', 'Тупиков', 'Тюков', 'Тюрина ', 'Тяпкина', 'Ульянов', 'Федоренков', 'Ферко', 'Фортуна', 'франш Екатеринбург', 'Франшиза Сетевой', 'Франшиза Спец', 'Франшиза Средний', 'Хачатурян', 'Хлоповской', 'Холопова', 'Хренков', 'Цветковская', 'Чаплин', 'Чернов М', 'ЧЛ Артем', 'ЧЛ Зеленоград', 'ЧЛ Лагода', 'ЧЛ Михаил Келлер', 'Чумичев', 'Швецов', 'Шемельфейнинг', 'Шемякова', 'Шестаков', 'Шилов', 'Шинкарева', 'Ширшова', 'Шишкин', 'Шишкин Сосенское', 'Шкодина', 'Шмаков', 'Шмегленко', 'Шмелев', 'Шустов', 'Экстра Люкс', 'Элебас алябьева', 'Юдаев', 'Юрин волоколамка', 'Яблочник', 'Яковлева', 'Якушин', 'Ярлушкина', 'Яцюк', 'ОБЩИЙ', 'СЕТЕВОЙ', 'СПЕЦ', 'СРЕДНИЙ');
	$ugroups = array_map('trim', $ugroups);

	$ugroup = array('usergroup' => '', 'type' => 'C', 'status' => 'A');
	$usergroups = array();
	foreach ($ugroups as $ug_name) {
		$ugroup['usergroup'] = 'Пинта ' . trim($ug_name);
		$ug_id = db_get_field('SELECT usergroup_id FROM ?:usergroup_descriptions WHERE usergroup = ?s', $ugroup['usergroup']);
		if (!$ug_id) {
			$ug_id = fn_update_usergroup($ugroup);
		}
		$usergroups[$ug_id] = $ugroup['usergroup'];
	}

	$file = 'pinta2.csv';
	$content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
	foreach ($content as $data) {
		$product_name = array_shift($data);
		$product_id = db_get_field('SELECT p.product_id FROM ?:product_descriptions AS pd LEFT JOIN ?:products AS p ON p.product_id = pd.product_id WHERE product = ?s AND company_id = ?i', trim($product_name), 46);
		if ($product_id) {
			$product_data = array('prices' => array());
			
			$prices = &$product_data['prices'];
			$prices[] = '';
			$data = call_user_func_array('fn_exim_import_price', array($data, ','));

			$price_data = array(
				'lower_limit' => 1,
				'price' => 0,
				'type' => 'A',
				'usergroup_id' => 0,
			);
			foreach ($data as $ug_name => $price_data['price']) {
				if (!empty($price_data['price'])){
					$price_data['usergroup_id'] = array_search('Пинта ' . trim($ug_name), $usergroups);
					if (!$price_data['usergroup_id']) {
						fn_print_die($usergroups, $ug_name);
					}
					$prices[] = $price_data;
				}
			}
			$prices[0] = array('price' => max(array_column($prices, 'price')), 'lower_limit' => 1);
			fn_update_product_prices($product_id, $product_data);
		} else {
			$unknown_products[] = $product_name;
		}
	}
	fn_print_die($unknown_products);
} elseif ($mode == 'pinta_products_job1') {
	foreach (array('pinta1products.csv' => 41, 'pinta2products.csv' => 46) as $file => $company_id) {
		$content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false) );
		$guids = fn_array_column($content, 'Описание', 'GUID');
		foreach ($guids as $guid => $descr) {
			db_query('UPDATE ?:product_descriptions AS pd LEFT JOIN ?:products AS p ON p.product_id = pd.product_id SET full_description = ?s  WHERE  pd.lang_code = ?s AND p.external_id = ?s AND p.company_id = ?i', $descr, DESCR_SL, $guid, $company_id);
		}
		fn_print_r($file);
	}
	fn_print_die('end');
} elseif ($mode == 'delete_fantom_products') {
	$products = db_get_fields('SELECT product_id FROM ?:products WHERE product_id > 10000');
	foreach ($products as $product_id) {
		$product_data = fn_get_product_data($product_id, $_SESSION['auth']);
		if (empty($product_data)) {
			fn_delete_product($product_id);
		}
	}
} elseif ($mode == 'import_lamaree_xml') {
	$xml = @simplexml_load_file('1574419805_CatalogN.xml');
	$categories_xml = (array) $xml->TNICPackage->TNICXIMessage->Data->DataPacket['1']->RowData;
	$categories_array = array();
	foreach ($categories_xml['Row'] as $row) {
		$atts_object = $row->attributes();
		$atts_array = (array) $atts_object;
		$categories_array[$atts_array['@attributes']['ID']] = $atts_array['@attributes'];
	}
	$tree = fn_build_tree($categories_array, '.');
	$new_category_ids = fn_update_categories_tree($tree);
	$res = db_query('UPDATE ?:vendor_plans SET `categories` = ?s WHERE plan_id = ?i', implode(',', $new_category_ids), 27);

	$links_xml = (array) $xml->TNICPackage->TNICXIMessage->Data->DataPacket['3']->RowData;
	$links = array();
	foreach ($links_xml['Row'] as $row) {
		$atts_object = $row->attributes();
		$atts_array = (array) $atts_object;
		$data = $atts_array['@attributes'];
		$links[$data['ITEM']] = $data['PARENT'];
	}

	// load features
	$products_xml = (array) $xml->TNICPackage->TNICXIMessage->Data->DataPacket['2']->RowData;
	$products = $all_features = array();
	$allowed_features = array('108' => 'Пищевая ценность', '109' => 'Калорийность', '72' => 'Условия хранения', '83' => 'Срок годности', '92' => 'Страна происхождения');
	foreach ($products_xml['Row'] as $row) {
		$atts_object = $row->attributes();
		$atts_array = (array) $atts_object;
		$data = $atts_array['@attributes'];

		$ugly_features = explode('|', $data['PROPERTIES']);
		$features = array();
		foreach ($ugly_features as $value) {
			if (!empty($value)) {
				list($feature_name, $feature_value) = explode('&', $value);
				if ($feature_value != '28.11.13' && $feature_id = array_search($feature_name, $allowed_features)) {
					$all_features[$feature_id]['ugly_feature_name'] = $feature_name;
					$all_features[$feature_id]['feature_id'] = $feature_id;
					$all_features[$feature_id]['variants'][] = $feature_value;
				}
			}
		}
	}
	foreach ($all_features as $feature_id => &$feature_data) {
		$feature_variants = array_unique($feature_data['variants']);
		$feature_db_data = fn_get_product_feature_data($feature_id, true);
		$db_variants = fn_array_column($feature_db_data['variants'], 'variant', 'variant_id');
		$variants = array();
		foreach ($feature_variants as $variant) {
			if (!($variant_id = array_search($variant, $db_variants))) {
				$variant_id = fn_update_product_feature_variant($feature_id, 'S', array('variant' => $variant));
			}
			$variants[$variant_id] = $variant;
		}
		$feature_data['variants'] = $variants;
	}


	// load prices
	$prices_xml = (array) $xml->TNICPackage->TNICXIMessage->Data->DataPacket['4']->RowData;
	$prices = array();
	
	foreach ($prices_xml['Row'] as $row) {
		$atts_object = $row->attributes();
		$atts_array = (array) $atts_object;
		$data = $atts_array['@attributes'];
		$prices[$data['ITEM']] = $data;
	}

	// load products
	foreach ($products_xml['Row'] as $row) {
		$atts_object = $row->attributes();
		$atts_array = (array) $atts_object;
		$data = $atts_array['@attributes'];

		$ugly_features = explode('|', $data['PROPERTIES']);
		$features = array();
		foreach ($ugly_features as $value) {
			if (!empty($value)) {
				list($feature_name, $feature_value) = explode('&', $value);
				if ($feature_value != '28.11.13' && $feature_id = array_search($feature_name, $allowed_features)) {
					$features[$feature_id] = array_search($feature_value, $all_features[$feature_id]['variants']);
				}
			}
		}

		$product_data[$data['ID']] = array(
			'product' => $data['NAME'],
			'company_id' => '43',
			'price' => $prices[$data['ID']]['PRICE'],
			'category_ids' => array($new_category_ids[$links[$data['ID']]]),
			'usergroup_ids' => array(150),
			'status' => 'A',
			'amount' => $data['OSTATOK'],
			'product_code' => $data['ARTIKUL'],
			'full_description' => $data['DESCRIPTION'],
			'short_description' => $data['DESCRIPTION_SHORT'],
			'product_features' => $features,
		);
		$res = fn_update_product($product_data[$data['ID']]);
	}
	fn_print_die(count($product_data));
} elseif ($mode == 'correct_profiles') {
	$user_ids = db_get_fields('SELECT user_id FROM ?:users WHERE user_type = ?s', 'C');
	foreach ($user_ids as $user_id) {
		$profiles = fn_get_user_profiles($user_id);
		$update_profile_ids = array();
		$has_p = false;
		foreach ($profiles as $profile) {
			if ($profile['profile_type'] == 'P') {
				if ($has_p) {
					$update_profile_ids[] = $profile['profile_id'];
				} else {
					$has_p = true;
				}
			}
		}
		if (!empty($update_profile_ids)) {
			db_query('UPDATE ?:user_profiles SET `profile_type` = ?s WHERE profile_id in (?a)', 'S', $update_profile_ids);
		}
	}
	
	fn_print_die('done correct_profiles');
} elseif ($mode == 'remove_extra_profiles') {
	$file = '123.csv';
	$content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
	$emails = fn_array_column($content, 'E-mail');
	foreach ($emails as $email) {
		$user_id = db_get_field('SELECT user_id FROM ?:users WHERE email = ?s', $email);
		$profiles = fn_get_user_profiles($user_id);
		if (count($profiles) > 1) {
			$main = array_shift($profiles);
			$remove_profiles = fn_array_column($profiles, 'profile_id');
			foreach ($remove_profiles as $profile_id) {
				fn_delete_user_profile($user_id, $profile_id);
			}
		}
	}
	fn_print_die('done remove_extra_profiles');
} elseif ($mode == 'get_reward_points') {
	$company_id = ($_REQUEST['company_id']) ? $_REQUEST['company_id'] : 13;
	list($users) = fn_get_users(array('company_id' => $company_id));
	$data = array();
	require_once(Registry::get('config.dir.functions') . 'fn.sales_reports.php');
	$intervals = fn_check_intervals(7, strtotime("1 January 2019"), strtotime("30 November 2019"));

	foreach ($users as &$user) {
		$user = fn_get_user_info($user['user_id'], true);
		if (!empty($user['fields'])) {
			$fields = db_get_hash_single_array('SELECT field_id, field_name FROM ?:profile_fields WHERE field_id IN (?a)', array('field_id', 'field_name'), array_keys($user['fields']));
			foreach ($fields as $field_id => $field_name) {
				$user[$field_name] = $user['fields'][$field_id];
			}
		}
		unset($user['fields'], $user['usergroups']);
		$_data['user_id'] = $user['user_id'];
		$_data['email'] = $user['email'];
		$_data['login'] = $user['login'];
		$_data['firstname'] = ($user['firstname']) ? $user['firstname'] : (($user['b_firstname']) ? $user['b_firstname'] : $user['s_firstname']);
		$_data['address'] = ($user['b_address']) ? $user['b_address'] : $user['s_address'];
		$_data['b_client_code'] = $user['b_client_code'];
		foreach ($intervals as $key => $interval) {
			
			$_data[$interval['description']] = db_get_field('SELECT sum(amount) FROM ?:reward_point_changes WHERE user_id = ?i AND timestamp >= ?i AND timestamp <= ?i', $user['user_id'], $interval['time_from'], $interval['time_to']);
		}
		
		$data[] = $_data;
	}
	$opts = array('delimiter' => ';', 'filename' => 'mvest.csv');
	$res = fn_exim_put_csv($data, $opts);
	fn_print_die($res);
} elseif ($mode == 'remove_old_inactive_users') {
	$ordered_users = db_get_fields('SELECT distinct(user_id) FROM ?:orders');
	$users = db_get_fields('SELECT user_id FROM ?:users WHERE user_id < ?i AND user_id NOT IN (?a) AND user_type = ?s', 3830, $ordered_users, 'C');
	foreach ($users as $user_id) {
		fn_delete_user($user_id);
/*		$user = fn_get_user_info($user_id, true);

		if (!empty($user['fields'])) {
			$fields = db_get_hash_single_array('SELECT field_id, field_name FROM ?:profile_fields WHERE field_id IN (?a)', array('field_id', 'field_name'), array_keys($user['fields']));
			foreach ($fields as $field_id => $field_name) {
				$user[$field_name] = $user['fields'][$field_id];
			}
		}
		unset($user['fields'], $user['usergroups']);
		$_data['user_id'] = $user['user_id'];
		$_data['email'] = $user['email'];
		$_data['login'] = $user['login'];
		$_data['firstname'] = ($user['firstname']) ? $user['firstname'] : (($user['b_firstname']) ? $user['b_firstname'] : $user['s_firstname']);
		$_data['address'] = ($user['b_address']) ? $user['b_address'] : $user['s_address'];
		$_data['b_client_code'] = $user['b_client_code'];
		$data[] = $_data;*/
	}
/*	$opts = array('delimiter' => ';', 'filename' => 'inactive_users.csv');
	$res = fn_exim_put_csv($data, $opts);
	fn_print_die($res);*/
	fn_print_die('end');
} elseif ($mode == 'correct_reward_points') {
	$pattern = array('0' => 1200, '30' => 1800, '40' => 2600, '60' => 3800, '80' => 4900, '90' => 5900, '100' =>  10000000);
	$points_arr = array_keys($pattern);
	$pattern = array_values($pattern);
	$company_id = ($_REQUEST['company_id']) ? $_REQUEST['company_id'] : 13;
	list($users) = fn_get_users(array('company_id' => $company_id));
	foreach ($users as &$user) {
/*		$user['reward_point_changes'] = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i', $user['user_id']);
		$user['reward_point_changes'][0]['reason'] = unserialize($user['reward_point_changes'][0]['reason']);
*/
		$reward_points = array();
		$plan = db_get_field('SELECT amount_plan FROM ?:sales_plan WHERE user_id = ?i AND company_id = ?i', $user['user_id'], $company_id);
		if ($plan) {
			$between = fn_between($plan, $pattern);
			$min_order = $pattern[$between] - 0.01;
			$points = $points_arr[$between];

			if ($min_order) {
				list($orders, ) = fn_get_orders(array('total_from' => $min_order,'user_id' => $user['user_id'], 'time_to' => '01/08/2019', 'time_from' => '01/12/2018', 'period' => 'C', 'company_id' => $company_id, 'status' => array('P', 'C', 'Y', 'A')));
				if ($orders) {
					db_query('DELETE FROM ?:reward_point_changes WHERE user_id = ?i', $user['user_id']);
					fn_sd_change_user_points(30, $user['user_id'], 'Приветственные бонусы', CHANGE_DUE_ADDITION, (($user['timestamp'])? $user['timestamp'] : '1546300800'));
					$reward_points[] = 30;
					foreach ($orders as $order) {
						$between = fn_between($order['total'], $pattern);
						$points = $points_arr[$between];
						$reason = array('order_id' => $order['order_id'], 'to' => $order['status'], 'from' => 'N');
						fn_sd_change_user_points($points, $user['user_id'], serialize($reason), CHANGE_DUE_ORDER, $order['timestamp']);
						$reward_points[] = $points;
					}
					fn_save_user_additional_data(POINTS, array_sum($reward_points), $user['user_id']);
				}
			}
		}
	}
	fn_print_die($users);
} elseif ($mode == 'cleanup_orders') {
	$max = 42772;

	$current = ($_SESSION['current_order_id']) ? $_SESSION['current_order_id'] : 0;
	
	$payment_info = db_get_hash_single_array("SELECT data, order_id FROM ?:order_data WHERE type = 'G' AND order_id > ?i ORDER BY order_id limit 500", array('order_id', 'data'), $current);

	$payment_info = array_map('unserialize', $payment_info);

	foreach ($payment_info as $order_id => &$info) {
		if ($info[0]['products'])
		foreach ($info[0]['products'] as &$product) {
			unset($product['main_pair']);	
		}
	}

	$payment_info = array_map('serialize', $payment_info);
	foreach ($payment_info as $order_id => $data) {
		db_query("UPDATE ?:order_data SET data = ?s WHERE order_id = ?i AND type = 'G'", $data, $order_id);
	}
	$_SESSION['current_order_id'] = max(array_keys($payment_info));
	if ($_SESSION['current_order_id'] < $max) {
		fn_print_r($_SESSION['current_order_id']);
		fn_redirect('commerceml.cleanup_orders');
	} else {
		db_query("OPTIMIZE TABLE `cscart_order_data` ");
		fn_print_die('done');
	}
} elseif ($mode == 'load_pinta_csvs') {
	$folder = 'load/';
	$files = fn_get_dir_contents($folder, false, true, '.csv');
	$company_ids = array('Пинта 1' => 41, 'Пинта 2' => 46);

	foreach ($files as $file) {
		$content = fn_exim_get_csv(array(), $folder.$file, array('validate_schema'=> false, 'delimiter' => ';') );
		$header = array_keys(reset($content));
		foreach ($header as &$value) {
			if ($value == 'Алкоголь') $value = 'Крепость (ABV)';
			if ($value == 'Тип тары') $value = 'Тип упаковки (тары)';
		}
		unset($value);
		$features = db_get_array('SELECT feature_id, description FROM ?:product_features_descriptions WHERE description IN (?a)', $header);
		foreach ($features as &$feature) {
			if ($feature['description'] == 'Крепость (ABV)') {
				$feature['data_id'] = 'Алкоголь';
			} elseif ($feature['description'] == 'Тип упаковки (тары)') {
				$feature['data_id'] = 'Тип тары';
			} else {
				$feature['data_id'] = $feature['description'];
			}
			list($feature['variants']) = fn_get_product_feature_variants(array(
                'feature_id' => $feature['feature_id'],
            ));
		}
		unset($feature);

		foreach ($content as $data) {
			$company_id = ($company_ids[$data['Продавец']]) ? $company_ids[$data['Продавец']] : $company_ids[$data['продавец']];
			$product_id = db_get_field('SELECT product_id FROM ?:products WHERE external_id = ?i AND company_id = ?i', $data['GUID'], $company_id);
			if (empty($product_id)) {
				$product_id = db_get_field('SELECT product_id FROM ?:products WHERE product_code = ?s AND company_id = ?i', $data['Аритикул (код товара)'], $company_id);
				if (empty($product_id)) {
					$product_id = db_get_field(
						'SELECT ?:product_descriptions.product_id FROM ?:product_descriptions LEFT JOIN ?:products ON ?:products.product_id = ?:product_descriptions.product_id WHERE ?:product_descriptions.product = ?s AND company_id = ?i', 
						$data['Название'], 
						$company_id
					);
					$u_data = array('external_id' => $data['GUID'], 'product_code' => $data['Аритикул (код товара)']);
					db_query('UPDATE ?:products SET ?u WHERE product_id = ?i', $u_data, $product_id);
				} else {
					$u_data = array('external_id' => $data['GUID']);
					db_query('UPDATE ?:products SET ?u WHERE product_id = ?i', $u_data, $product_id);
				}
			}
			if (!$product_id) {
				$unknown_products[] = $data;
			} else {
				$product_features = $add_new_variant = array();
				foreach ($features as $feature) {
					$variant = $data[$feature['data_id']];
					$variants = fn_array_column($feature['variants'],  'variant', 'variant_id');

					array_walk($variants, 'fn_trim_helper');

					$variant_id = array_search(trim($variant), $variants);
					if ($variant_id) {
						$product_features[$feature['feature_id']] = $variant_id;
					} else {
						$add_new_variant[$feature['feature_id']]['variant'] = trim($variant);
					}
					
				}
				fn_update_product_features_value($product_id, $product_features, $add_new_variant, DESCR_SL);
			}
		}
	}
	fn_print_die($unknown_products);
	fn_print_die('done');
}

function fn_between($val, $pattern)
{
	$between = array(0);
    foreach ($pattern as $key => $limit) {
    	if (isset($pattern[$key+1])) {
    		if ( ($val > $limit) and ($value < $pattern[$key+1]-0.01) ) {
    			$between[] = $key+1;
    		}
        }
    } 
    return max($between);
}

function fn_sd_change_user_points($value, $user_id, $reason = '', $action = CHANGE_DUE_ADDITION, $timestamp = TIME)
{

    $value = (int) $value;
    if (!empty($value)) {
        $change_points = array(
            'user_id' => $user_id,
            'amount' => $value,
            'timestamp' => $timestamp,
            'action' => $action,
            'reason' => $reason
        );

        return db_query("REPLACE INTO ?:reward_point_changes ?e", $change_points);
    }

    return '';
}

function fn_update_categories_tree(&$tree, $parent_id = 0) {
	global $new_category_ids;
	foreach ($tree as $key => &$value) {
		$category = array(
			'category' => $value['NAME'],
			'parent_id' => $parent_id,
			'usergroup_ids' => array(150),
		);
		$new_category_ids[$value['ID']] = $value['category_id'] = fn_update_category($category, 0);
		if (!empty($value['children'])) {
			fn_update_categories_tree($value['children'], $value['category_id']);
		}
	}
	return $new_category_ids;
	
}

function fn_build_tree(array &$elements, $parentId = 0) {
        $branch = array();

        foreach ($elements as $element) {

            if ($element['PARENT_ID'] == $parentId) {
                $children = fn_build_tree($elements, $element['ID']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
}

function fn_merge_products($company_id = 13)
{

  fn_echo('Start');
  fn_echo('<br />');

  $exclude_cid = [642, 538]; //exclude products`category

  list($exclude_products) = fn_get_products(['cid' => $exclude_cid]); //exclude products`category
	$exclude_products = array_keys($exclude_products);

  //  get products with dublicate pr_code
  $product_groups = db_get_hash_multi_array("SELECT A.product_id, A.product_code
	FROM ?:products A
	INNER JOIN (SELECT product_id, product_code, company_id
		FROM ?:products
		WHERE company_id = ?i
		GROUP BY product_code
		HAVING COUNT(*) > 1) B
	ON A.product_code = B.product_code AND A.company_id = B.company_id",
	['product_code', 'product_id'],
	$company_id);
  if (!$product_groups) {
	fn_echo('Did not find products');
	die();
  }

  foreach ($product_groups as $product_code => $products_info) {

	fn_echo('Process prodcut code: '  . $product_code);
	fn_echo('<br />');

	$product_ids = array_keys($products_info);
	$main_product_id = '';
	$new_data = [
		'additional_categories' => [], // from main & additional
		'price' => 0, // max price
		'usergroup_ids' => [], // доступность юзергруппе
		'prices' => [0], // mix pr qty discount
		// 'qty' => 0, // use main products qty
		// 'image' => [], // use main
		// 'name' => '', //Если в названии итогового товара есть [CLONE],  [CLONE] [CLONE], это надо подтереть
		// остальные товары удаляются.
	];
	list($products) = fn_get_products(['pid' => $product_ids]);

	fn_gather_additional_products_data($products, array('get_icon' => false, 'get_detailed' => true, 'get_options' => false, 'get_discounts' => false));

	foreach ($products as $product_id => $product) {

		//  check exclude products
		if (in_array($product_id, $exclude_products)) {
		if(($key = array_search($product_id, $product_ids)) !== false){
			unset($product_ids[$key]);
		}

		continue;
		}

		if (isset($product['main_pair']) && !empty($product['main_pair']) && empty($main_product_id)) {
		$main_product_id = $product_id;
		}

		$new_data['additional_categories'] = array_merge($new_data['additional_categories'], $product['category_ids']);

		$new_data['price'] = max($product['price'], $new_data['price']);

		foreach (explode(',', $product['usergroup_ids']) as $user_group) {
		$new_data['usergroup_ids'][] = $user_group;
		$new_data['prices'][] = [
			'lower_limit' => '1',
			'price' => $product['price'],
			'type' => 'A',
			'usergroup_id' => $user_group
		];
		}
		unset($new_data['prices'][0]);
	}

	$new_data['additional_categories'] = array_unique($new_data['additional_categories']);
	$new_data['usergroup_ids'] = array_unique($new_data['usergroup_ids']);

	$main_product = $products[$main_product_id];

	//  remove clone label
	$main_product['product'] = trim(str_replace('[CLONE]', '', $main_product['product']));

	//  some warning on the yml_export add-on
	$main_product['yml2_delivery_options'] = (
		isset($main_product['yml2_delivery_options'])
		&& gettype ($main_product['yml2_delivery_options']) !== 'string')
		? $main_product['yml2_delivery_options']
		: [$main_product['yml2_delivery_options']];

	$main_product = array_merge($main_product, $new_data);
	$product_id = fn_update_product($main_product, $main_product_id, DESCR_SL);


	if ($product_id) {
		fn_echo('Update product #' . $main_product_id);
		fn_echo('<br />');

		//  remove other products
		unset($product_ids[array_search($main_product_id, $product_ids)]);

		foreach ($product_ids as $delete_pr_id) {
		$result = fn_delete_product($delete_pr_id);
		if ($result) {
			fn_echo('Deleted product #' . $delete_pr_id);
		} else {
			fn_echo('Problem to delete product #' . $delete_pr_id);
		}
		}
	} else {
		fn_echo('Problem to save product #' . $main_product_id);
	}

	fn_echo('<hr />');
	
  }

  fn_echo("C'est finit");
  exit;
}
