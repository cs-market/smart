<?php

use Tygh\Registry;
use Tygh\Enum\YesNo;
use Tygh\Enum\ProfileDataTypes;

if ($mode == 'base_price' && $action) {
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
    $product_groups = fn_group_array_by_key($products, 'product_code');
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
} elseif ($mode == 'remove_extra_profiles_alidi') {

    list($users) = fn_get_users(array('company_id' => 1824), $auth);
    foreach ($users as $user) {
        $profiles = fn_get_user_profiles($user['user_id']);
        if (count($profiles) > 1) {
            $profiles = array_filter($profiles, function($v, $k) {
                return $v['profile_name'] == 'Import create';
            }, ARRAY_FILTER_USE_BOTH);

            $remove_profiles = fn_array_column($profiles, 'profile_id');
            if ($remove_profiles) {
                foreach ($remove_profiles as $profile_id) {
                    fn_delete_user_profile($user['user_id'], $profile_id);
                    $removed_profiles[] = $product_id;
                }
            }
        }
    }
    fn_print_die(count($removed_profiles));
} elseif ($mode == 'get_reward_points') {
    $company_id = ($_REQUEST['company_id']) ? $_REQUEST['company_id'] : 13;

    list($users) = fn_get_users(array('company_id' => $company_id), $auth);

    $data = array();
    require_once(Registry::get('config.dir.functions') . 'fn.sales_reports.php');
    $intervals = fn_check_intervals(7, strtotime("1 February 2022"), strtotime("31 March 2022"));

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
    $res = fn_exim_put_csv($data, $opts, '"');
    fn_print_die($res);
} elseif ($mode == 'remove_old_inactive_users') {
    $ordered_users = db_get_fields('SELECT distinct(user_id) FROM ?:orders');
    $users = db_get_fields('SELECT user_id FROM ?:users WHERE user_id < ?i AND user_id NOT IN (?a) AND user_type = ?s', 3830, $ordered_users, 'C');
    foreach ($users as $user_id) {
        fn_delete_user($user_id);
/*      $user = fn_get_user_info($user_id, true);

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
/*  $opts = array('delimiter' => ';', 'filename' => 'inactive_users.csv');
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
/*      $user['reward_point_changes'] = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i', $user['user_id']);
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
} elseif ($mode == 'load_pinta_csvs') {
    $products = db_get_fields('SELECT product_id FROM ?:products WHERE company_id IN (?a)', array(41,46));
    $fantoms = 0;
    foreach ($products as $product_id ) {
        $data = fn_get_product_data($product_id, $auth);
        if (empty($data)) {
            fn_delete_product($product_id);
            $fantoms += 1;
        }
    }
fn_print_r($fantoms);
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
            } elseif (!in_array($feature['description'], $header) && $feature['description'] == 'Объем') {
                $feature['data_id'] = 'объем';
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
            $product_id = db_get_field('SELECT product_id FROM ?:products WHERE external_id = ?s AND company_id = ?i', $data['GUID'], $company_id);
            $product_id_srt = db_quote('SELECT product_id FROM ?:products WHERE external_id = ?s AND company_id = ?i', $data['GUID'], $company_id);
            //if ($data['GUID'] == 'c0cecd75-d555-11e9-80e1-6045cba72548') fn_print_die($product_id, $data, $product_id_srt, $company_ids[$data['Продавец']], $company_ids[$data['продавец']], $company_id);
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
            } else {
                $u_data = array('product_code' => $data['Аритикул (код товара)']);
                db_query('UPDATE ?:products SET ?u WHERE product_id = ?i', $u_data, $product_id);
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
} elseif ($mode == 'correct_users') {
    $users = db_get_array('SELECT user_id, email, user_login, company_id, timestamp FROM ?:users WHERE user_type = "C" ORDER BY user_id ASC');
    $jun2019 = 1559347200;
    foreach ($users as &$user) {
        // is active user
        if ($user['timestamp'] < $jun2019) {
            $orders = db_get_array('SELECT order_id, company_id FROM ?:orders WHERE user_id = ?i', $user['user_id']);
            if (empty($orders)) {
                //fn_print_die('delete', $user['user_id']);
                fn_delete_user($user['user_id']);
                continue;
            }
        }
        // if (empty($user['company_id'])) {
        //  $order = db_get_row('SELECT count(company_id) as count, company_id FROM ?:orders WHERE user_id = ?i GROUP BY company_id ORDER BY count DESC', $user['user_id']);
        //  if ($order) $user['company_id'] = $order['company_id'];
        // }
        // // backup login info
        // $bak_login = $user['user_login'];

        // $user_data = fn_get_user_info($user['user_id']);
        // array_walk($user_data['fields'], 'fn_trim_helper');
        // $code = empty($user_data['fields']['38']) ? $user_data['fields']['39'] : $user_data['fields']['38'];

        // if (!empty($code) && $code != '9999') {
        //  $user['user_login'] = $user_data['fields'][38];
        //  // NEED TO ADD POSTFIX!!!
        // } elseif (!empty(trim($user['email'])) && $user['email'] != $user['user_login']) {
        //  // иначе заполним логин emailом
        //  if (strpos($user['user_login'], 'user_') !== false || empty(trim($user['user_login']))) {
        //      $user['user_login'] = $user['email'];
        //  }
        // }
        // $bak_login = (empty($bak_login)) ? $user['user_login'] : $bak_login;
        // if (empty($user['email'])) {
        //  $user['email'] = $bak_login;
        // }
        // db_query('UPDATE ?:users SET ?u WHERE user_id = ?i', $user, $user['user_id']);

        //db_query('UPDATE ?:users SET ?u WHERE user_id = ?i', $user, $user['user_id']);
/*      // если в логине user_ или ничего, то загрузим код1с
        
            $user_data = fn_get_user_info($user['user_id']);
            array_walk($user_data['fields'], 'fn_trim_helper');
            
            if (!empty($code)) {
                $user['user_login'] = $user_data['fields'][38];
            } elseif (!empty(trim($user['email']))) {
                // иначе заполним логин emailом
                $user['user_login'] = $user['email'];
            } else {
                
            }
        } else {
            // иначе логин заполнен
            //fn_print_r($user);
        }
        if (empty($user['email'])) {
            $user['email'] = $user['user_login'];
        }
        if (!$user['company_id']) {
            $order = db_get_row('SELECT count(company_id) as count, company_id FROM ?:orders WHERE user_id = ?i GROUP BY company_id ORDER BY count DESC', $user['user_id']);
            if ($order) $user['company_id'] = $order['company_id'];
        }

        if ($user['company_id'] && $user['company_id'] != 13) {
            
            fn_print_r(strpos($user['user_login'], '@'), $user);
            //fn_print_die(123, strpos());
        }*/
    }
    fn_print_die('stop');
} elseif ($mode == 'correct_users2') {
    $jun2019 = 1559347200;
    $users = db_get_fields('SELECT user_id FROM ?:users WHERE user_type = "C" AND timestamp > ?i AND company_id = 0 ORDER BY user_id ASC', 0);
    foreach ($users as $user_id) {
        $order = db_get_row('SELECT count(company_id) as count, company_id FROM ?:orders WHERE user_id = ?i GROUP BY company_id ORDER BY count DESC', $user_id);
        if ($order) {
            db_query('UPDATE ?:users SET company_id = ?i WHERE user_id = ?i', $order['company_id'], $user_id);
            $corrected_users[] = $user_id;
        }
    }
    fn_print_die(count($corrected_users), $corrected_users);
} elseif ($mode == 'prices_maintenance') {
    if (empty($action)) fn_print_die('empty action');

    $external_condition = empty($_REQUEST['company_id']) ? '' : db_quote(' AND company_id = ?i', $_REQUEST['company_id']);
    $companies = db_get_fields('SELECT DISTINCT(company_id) FROM ?:products WHERE 1 ?p', $external_condition);
    // $companies = [13, 1790];

    if (empty($dispatch_extra) || $dispatch_extra == 'prices') {
        $extra_usergroups = [42,0];
        foreach($companies as $company_id) {
            $wrong = [];
            if ($usergroups = db_get_field("SELECT usergroups FROM ?:vendor_plans LEFT JOIN ?:companies ON ?:companies.plan_id = ?:vendor_plans.plan_id WHERE company_id = ?i", $company_id)) {
                $usergroups = array_merge(explode(',', $usergroups), $extra_usergroups);
                if ($action == 'check') {
                    $wrong = db_get_fields('SELECT distinct(usergroup_id) FROM ?:product_prices WHERE usergroup_id NOT IN (?a) AND product_id IN (SELECT product_id FROM ?:products WHERE company_id = ?i)', $usergroups, $company_id);
                    $wrong = db_get_fields('SELECT usergroup FROM ?:usergroup_descriptions WHERE usergroup_id IN (?a)', $wrong);
                } elseif ($action == 'fix') {
                    $wrong = db_query('DELETE FROM ?:product_prices WHERE usergroup_id NOT IN (?a) AND product_id IN (SELECT product_id FROM ?:products WHERE company_id = ?i)', $usergroups, $company_id);
                }

                if ($wrong) fn_print_r($wrong, $company_id);
            }
        }
    }

    if (empty($dispatch_extra) || $dispatch_extra == 'user_prices') {
        $user_price_products = db_get_hash_multi_array("SELECT distinct(up.product_id), p.company_id FROM ?:user_price AS up LEFT JOIN ?:products AS p ON p.product_id = up.product_id $external_condition", ['company_id', 'product_id', 'product_id']);

        foreach ($user_price_products as $company_id => $products) {
            list($users) = fn_get_users(array('user_type' => 'C', 'company_id' => $company_id), $auth);
            $company_users = array_column($users, 'user_id');
            //$company_users = db_get_fields('SELECT user_id FROM ?:users WHERE company_id = ?i', $company_id);
            if ($action == 'check') {
                $wrong = db_get_array('SELECT concat(up.product_id, " - ", pd.product) AS product, concat("vendor #", u.company_id, ", ", u.firstname) AS user FROM ?:user_price AS up LEFT JOIN ?:product_descriptions AS pd ON pd.product_id = up.product_id LEFT JOIN ?:users AS u ON u.user_id = up.user_id WHERE up.product_id IN (?a) AND up.user_id NOT IN (?a)', $products, $company_users);
                $wrong = fn_group_array_by_key($wrong, 'product');
                foreach ($wrong as &$value) {
                    $value = array_column($value, 'user');
                }
            } elseif ($action == 'fix') {
                $wrong = db_query('DELETE FROM ?:user_price WHERE product_id IN (?a) AND user_id NOT IN (?a)', $products, $company_users);
            }

            if ($wrong) fn_print_r($wrong, $company_id);
        }
    }

    fn_print_die('done');
} elseif ($mode == 'import_user_price_alidi') {
    $dir = 'alidi/';
    $files = fn_get_dir_contents($dir, false, true, 'csv');
    fn_set_checkpoint();
    foreach ($files as $file) {
        fn_echo_br('file '. $file);
        $pattern = fn_exim_get_pattern_definition(strtolower('user_price'), 'import');
                
        if (!empty($pattern)) {
            $params = array(
                'delimiter' => ';',
                'price_dec_sign_delimiter' => ',',
                'category_delimiter' => '///',
                'skip_creating_new_products' => 'N',
                'unset_file' => true
            );
            if ($params['delimiter'] == ',') {
                $params['delimiter'] = 'C';
            }

            Registry::set('runtime.skip_area_checking', true);

            if (($data = fn_exim_get_csv($pattern, $dir.$file, $params))) {

                $res = fn_import($pattern, $data, $params);
            }
        }
        if ($res) {
            fn_rm($dir . $file);
        }
        fn_echo_br(fn_set_checkpoint());
    }
    fn_print_die('stop');
} elseif ($mode == 'separate_features_by_vendor') {
    /*
     * Automatic division and duplication 
     * of characteristics by vendors
     */

    // [Drop all empty feature values in DB]
    if (db_get_field("SELECT count(*) FROM ?:product_features_values WHERE variant_id = '0' AND value = '' AND value_int IS NULL")) {
        $drop_empty_feature_calue_count = db_query(
            "DELETE FROM ?:product_features_values WHERE variant_id = '0' AND value = '' AND value_int IS NULL");
        fn_echo_br("Drop empty feature values: " . $drop_empty_feature_calue_count);
    } else {
        fn_echo_br("There aren't empty feature values");
    }
    // /[Drop all empty feature values in DB]
    
    // [Drop feature values with non-existent products]
    $non_existent_products = db_get_fields("
        SELECT ?:product_features_values.product_id
        FROM ?:product_features_values
        LEFT JOIN ?:products ON (?:product_features_values.product_id = ?:products.product_id)
        WHERE ?:products.product_id IS NULL
    ");

    if ($non_existent_products) {
        $drop_non_existent_products_count = db_query(
            "DELETE FROM ?:product_features_values WHERE product_id IN (?n)",
            $non_existent_products
        );
        fn_echo_br("Drop non-existent products variants: " . $drop_non_existent_products_count);
    } else {
        fn_echo_br("There aren't non-existent products variants");
    }
    // /[Drop feature values with non-existent products]

    // function to create condition for feature value unique
    $create_feat_cond = function($f) {
        return db_quote(
            "?w", 
            [
                'feature_id' => $f['feature_id'],
                'product_id' => $f['product_id'],
                'variant_id' => $f['variant_id'],
            ]
        );
    };

    // clear all external_id
    db_query(
        'UPDATE ?:product_feature_variants SET ?u', 
        ['external_id' => '']
    );
    db_query(
        'UPDATE ?:product_features SET ?u', 
        ['external_id' => '']
    );

    // get all values without company features
    $old_features = db_get_hash_multi_array("SELECT ?:product_features_values.*, ?:products.company_id AS product_company_id 
        FROM ?:product_features_values 
        LEFT JOIN ?:product_features ON ?:product_features_values.feature_id = ?:product_features.feature_id 
        LEFT JOIN ?:products ON ?:product_features_values.product_id = ?:products.product_id 
        WHERE ?:product_features.company_id = '0'", 
        ['feature_id', 'product_id']
    );

    $statistic = [
        'skip_feat' => 0, // default feature
        'skip_new_feat' => 0, // use created before feature
        'new_feat' => 0, // create new feature
        'change_value' => 0, // change product value if use value|value_int
        'skip_variant' => 0, // use created before variant
        'new_variant' => 0, // create new variant
    ];

    foreach ($old_features as $old_feature_id => $products) {
        $first_product = array_shift($products);
        $old_company_id = $first_product['product_company_id'];
        $tmp_match_list = []; // [company_id => new_feat_id]
        $tmp_feat_variant_list = []; // [variant_id => [$feat_id => new_variant_id]]

        db_query('UPDATE ?:product_features SET ?u WHERE feature_id = ?i', ['company_id' => $old_company_id], $first_product['feature_id']);

        foreach ($products as $product) {
            $new_company_id = $product['product_company_id'];

            // skip, if old company
            if ($new_company_id == $old_company_id) {
                $statistic['skip_feat']++;
                continue;
            }

            if (isset($tmp_match_list[$new_company_id])) {
                $new_feature_id = $tmp_match_list[$new_company_id];
                $statistic['skip_new_feat']++;
            } else {
                // Dublicate the feature
                $old_feature_data = fn_get_product_feature_data($product['feature_id']);

                unset($old_feature_data['feature_id']);
                // unset($old_feature_data['external_id']);
                
                $old_feature_data['company_id'] = $new_company_id;

                $new_feature_id = fn_update_product_feature($old_feature_data, 0);

                $tmp_match_list[$new_company_id] = $new_feature_id;
                $statistic['new_feat']++;
            }

            // change feature value
            if ($product['value'] || $product['value_int']) {
                db_query('UPDATE ?:product_features_values SET ?u WHERE ?p', ['feature_id' => $new_feature_id], $create_feat_cond($product));
                $statistic['change_value']++;
            } elseif ($product['variant_id']) {
                // if use variant types

                // check if created new variant
                if (isset($tmp_feat_variant_list[$product['variant_id']][$new_feature_id])) {
                    $new_variant_id = $tmp_feat_variant_list[$product['variant_id']][$new_feature_id];
                    $statistic['skip_variant']++;
                } else {
                    // dublicat variant
                    $variant_data = fn_get_product_feature_variant($product['variant_id']);

                    unset($variant_data['variant_id']);
                    // unset($variant_data['external_id']);
                    $variant_data['feature_id'] = $new_feature_id;
                    $new_variant_id = fn_add_feature_variant($new_feature_id, $variant_data);

                    $tmp_feat_variant_list[$product['variant_id']] = [$new_feature_id => $new_variant_id];
                    
                    $statistic['new_variant']++;
                }

                db_query(
                    'UPDATE ?:product_features_values SET ?u WHERE feature_id = ?s AND product_id = ?s AND variant_id = ?s', 
                    [
                        'feature_id' => $new_feature_id,
                        'variant_id' => $new_variant_id,
                    ],
                    $product['feature_id'],
                    $product['product_id'],
                    $product['variant_id']
                );
            }
            fn_echo('.');
        }
        fn_echo('<br />');
    }

    foreach ($statistic as $key => $value) {
        fn_echo_br($key . ": " . $value);
    }

    // [remove not use variants]
    $non_existent_variants = db_get_fields("
        SELECT ?:product_feature_variants.variant_id
        FROM ?:product_feature_variants
        LEFT JOIN ?:product_features_values ON (?:product_features_values.variant_id = ?:product_feature_variants.variant_id)
        WHERE ?:product_features_values.variant_id IS NULL
    ");

    if ($non_existent_variants) {
        $drop_non_existent_variants = db_query(
            "DELETE FROM ?:product_feature_variants WHERE variant_id IN (?n)",
            $non_existent_variants
        );
        fn_echo_br("Drop non-existent products: " . $drop_non_existent_variants);
    } else {
        fn_echo_br("There aren't non-existent variants");
    }
    // [/remove not use variants]

    die('Finis');
} elseif ($mode == 'features_maintenance') {
    //delete fantom products
    // $pids = db_get_fields('SELECT product_id FROM ?:products');
    // $iteration = 0;
    // foreach ($pids as $product_id) {
    //     $iteration ++;
    //     $data = fn_get_product_data($product_id, $auth);
    //     if (empty($data)) {
    //         fn_delete_product($product_id);
    //         fn_print_r($iteration, $product_id);
    //     }
    // }

    $condition = '';
    if (!empty($action)) {
        $condition = db_quote(' WHERE company_id = ?i', $action);
    }
    $condition .= db_quote(' AND ?:product_features.feature_id = ?i ', 814);
    $all_features = db_get_hash_multi_array("SELECT * from ?:product_features LEFT JOIN ?:product_features_descriptions ON ?:product_features.feature_id = ?:product_features_descriptions.feature_id AND lang_code = ?s $condition", ['company_id', 'feature_id'], 'ru');

    foreach ($all_features as $company_id => $features) {
        foreach ($features as $feature_id => &$feature) {
            if ($feature['feature_type'] == 'G') {
                fn_delete_feature($feature_id);
                unset($features[$feature_id]);
            }
            if ($feature['description'] == 'Бренд*') {
                $feature['description'] = 'Бренд';
            }
            $feature['description'] = trim(mb_strtolower($feature['description']));
        }
        $feature_groups = fn_group_array_by_key($features, 'description');
        foreach ($feature_groups as $group) {
            if (count($group) > 1) {
                unset($target_feature);
                foreach ($group as $feature) {
                    if (strlen($feature['external_id']) > 20) {
                        $target_feature = $feature['feature_id'];
                    }
                }
                if (!$target_feature) {
                    $target_feature = reset($group)['feature_id'];
                }
                $group = fn_array_value_to_key($group, 'feature_id');
                unset($group[$target_feature]);
                fn_merge_product_features($target_feature, $group);
            }
        }
    }
} elseif ($mode == 'merge_variants') {
    $condition = "";
    if ($action) {
        $condition .= db_quote('AND company_id = ?i', $action);
    }
    $all_features = db_get_fields("SELECT feature_id FROM ?:product_features WHERE 1 $condition");

    $merge = $tmp = array();

    foreach ($all_features as $feature_id) {
        $feature = fn_get_product_feature_data($feature_id, true, true);
        if (isset($feature['variants']) && count($feature['variants']) > 1) {
            foreach ($feature['variants'] as $variant_id => $variant) {
                $variant_name = trim(mb_strtolower($variant['variant']));
                if (array_key_exists($variant_name, $tmp[$feature_id])) {
                    // merge
                    if (strlen($variant['external_id']) > 20) {
                        $merge[$feature_id][$variant['variant_id']][] = $tmp[$feature_id][$variant_name]['variant_id'];

                        fn_print_die(123);
                    } else {
                        $merge[$feature_id][$tmp[$feature_id][$variant_name]['variant_id']][] = $variant['variant_id'];
                    }
                } else {
                    $tmp[$feature_id][$variant_name] = $variant;
                }
            }
        }
    }

    foreach ($merge as $feature_id => $data) {
        foreach ($data as $source_variant_id => $merge_variants) {
            db_query("UPDATE ?:product_features_values SET variant_id = ?i WHERE variant_id IN (?a)", $source_variant_id, $merge_variants);
            fn_delete_product_feature_variants(0, $merge_variants);
        }
    }
    fn_print_die($merge);
} elseif ($mode == 'create_filters') {
    $filters = db_get_fields('SELECT feature_id FROM ?:product_filters');
    $features = db_get_fields('SELECT feature_id FROM ?:product_features WHERE feature_id NOT IN (?a)', $filters);
    $filter_data = array(
        'display' => 'Y',
        'display_count' => 10,
        'round_to' => '0.01'
    );
    foreach ($features as $featire_id) {
        $filter_data['filter_type'] = 'FF-'.$featire_id;
        $filter_data['filter'] = fn_get_feature_name($featire_id);
        fn_update_product_filter($filter_data, 0);
    }
} elseif ($mode == 'cleanup_images') {
    //$product_ids = db_get_fields('SELECT product_id FROM ?:products');
    // $pair_ids = db_get_fields("SELECT pair_id FROM ?:images_links WHERE object_id NOT IN (?a) AND object_type = ?s", $product_ids, 'product');
    // foreach ($pair_ids as $pair_id) {
    //     fn_delete_image_pair($pair_id, 'product');
    // }
    // всего чистит 500 мегабайт из 8.2 гига
    $product_ids = db_get_fields('SELECT product_id FROM ?:products');
    
    foreach ($product_ids as $product_id) {
        $images[] = array(fn_get_image_pairs($product_id, 'product', 'M'));
        $images[] = fn_get_image_pairs($product_id, 'product', 'A');
    }
    $images = array_filter($images);
    $db_files = array();
    foreach ($images as $img) {
        foreach ($img as $i) {
            $db_images[$i['pair_id']] = str_replace('detailed/', '', $i['detailed']['relative_path']);
        }
    }

    $category_ids = db_get_fields('SELECT category_id FROM ?:categories');
    foreach ($category_ids as $category_id) {
        $i = fn_get_image_pairs($category_id, 'category', 'M', true, true, $lang_code);
        $db_images[$i['pair_id']] = str_replace('detailed/', '', $i['detailed']['relative_path']);
    }

    ksort($db_images);
    $fs_images = fn_get_dir_contents('/home/bizon/www/smart/images/detailed', false, true, '', '', true);
    $diff = array_diff($fs_images, $db_images);

    // $orig_diff = $diff;
    // foreach ($diff as &$image_path) {
    //     $path = explode('/', $image_path);
    //     $image_path = array_pop($path);
    // }
    // $pair_ids = db_get_array('SELECT ?:images.*, ?:images_links.* FROM ?:images LEFT JOIN ?:images_links ON ?:images_links.detailed_id = ?:images.image_id WHERE image_path IN (?a)', $diff);
    // array_filter($pair_ids);

    foreach ($diff as $path) {
        $res[] = fn_rm("images/detailed/" . $path);
    }

    fn_print_die(count($diff), $res);
} elseif ($mode == 'vendorize_filters') {
    $filters = db_get_array('SELECT filter_id, f.feature_id, f.company_id FROM ?:product_filters LEFT JOIN ?:product_features AS f ON f.feature_id = ?:product_filters.feature_id');
    foreach ($filters as $filter) {
        db_query('UPDATE ?:product_filters SET ?u WHERE filter_id = ?i', $filter, $filter['filter_id']);
    }
} elseif ($mode == 'zero_company_orders') {
    $order_ids = db_get_fields('SELECT order_id FROM ?:orders WHERE company_id = ?i AND is_parent_order = ?s', 0, 'N');
    foreach ($order_ids as $order_id) {
        $order_info = fn_get_order_info($order_id);
        if ($order_info['products']) {
            $companies = array_unique(fn_array_column($order_info['products'], 'company_id'));
            if (!empty($companies) && count($companies) == 1) {
                db_query('UPDATE ?:orders SET company_id = ?i WHERE order_id = ?i', reset($companies), $order_id);
            }
        }
    }
    fn_print_die($order_ids);
} elseif ($mode == 'remove_import_create') {
    $profile_ids = db_get_hash_single_array('SELECT profile_id, user_id FROM ?:user_profiles WHERE profile_name LIKE ?s', ['profile_id', 'user_id'], 'Import create');

    foreach ($profile_ids as $profile_id => $user_id) {
        if (db_get_field('SELECT count(profile_id) FROM ?:user_profiles WHERE user_id = ?i', $user_id) > 1 ) {
            if (!(fn_delete_user_profile($user_id, $profile_id))) {
                $failure[$profile_id] = $user_id;
            }
        }
    }
    fn_print_die(count($profile_ids), $failure);
} elseif ($mode == 'restore_decimal') {
    $file = 'var/files/products.csv';
    $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
    foreach ($content as $data) {
        db_query('UPDATE ?:products SET ?u WHERE product_id = ?i', $data, $data['product_id']);
    }
    fn_print_die(count($content));
} elseif ($mode == 'restore_vega_products') {
    $file = 'cscart_products.csv';
    $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
    foreach ($content as $data) {
        db_query('UPDATE ?:products SET status = ?s WHERE product_id = ?i', $data['status'], $data['product_id']);
    }
} elseif ($mode == 'correct_categories') {
    $params = $_REQUEST;
    if (empty($action)) {
        return;
    }
    $params['company_id'] = $action;
    list($products) = fn_get_products($params);
    $product_categories = array_column($products, 'category_ids');
    $categories = array();
    array_walk_recursive($product_categories, function($a) use (&$categories) { $categories[] = $a;});
    $categories = array_unique($categories);
    $category_ids = fn_get_category_ids_with_parent($categories);
    $plan_id = db_get_field('SELECT plan_id FROM ?:companies WHERE company_id = ?i', $action);
    if ($plan_id) db_query('UPDATE ?:vendor_plans SET categories = ?s WHERE plan_id = ?i', implode(',',$category_ids), $plan_id);
    fn_print_die("done");
} elseif ($mode == 'correct_categories_extended') {
    // $plans = db_get_hash_array('SELECT ?:vendor_plans.plan_id, ?:vendor_plan_descriptions.plan, categories FROM ?:vendor_plans RIGHT JOIN ?:companies ON ?:companies.plan_id = ?:vendor_plans.plan_id LEFT JOIN ?:vendor_plan_descriptions ON ?:vendor_plan_descriptions.plan_id = ?:vendor_plans.plan_id WHERE ?:companies.status = ?s', 'plan_id', 'A');

    // $categories = array();
    // foreach ($plans as &$plan) {
    //     $plan['categories'] = explode(',',$plan['categories']);
    // }
    // //fn_print_r($plans);
    // foreach ($plans as &$plan) {
    //     foreach ($plan['categories'] as $category_id) {
    //         if ($category_id == '1056' or $category_id == 'on') continue;
    //         if (!in_array($category_id, $categories)) {
    //             $categories[$plan['plan_id']."_".$category_id] = $category_id;
    //         } else {
    //             $match = array_search($category_id, $categories);
    //             $match = explode('_', $match);
    //             $duplicates[] = array('category_id' => $category_id, 'name' => fn_get_category_name($category_id), 'plan1' => $plans[$plan['plan_id']]['plan'], 'plan2' => $plans[$match[0]]['plan']);
    //             //if ($category_id == 'on') fn_print_die($plan);
    //         }
    //     }
    // }
    // $params['filename'] = 'duplicates.csv';
    // $params['force_header'] = true;
    // $export = fn_exim_put_csv($duplicates, $params, '"');



    // $plans = db_get_hash_array('SELECT ?:vendor_plans.plan_id, ?:vendor_plan_descriptions.plan, ?:companies.company_id, ?:vendor_plans.categories, ?:vendor_plans.usergroup_ids  FROM ?:vendor_plans RIGHT JOIN ?:companies ON ?:companies.plan_id = ?:vendor_plans.plan_id LEFT JOIN ?:vendor_plan_descriptions ON ?:vendor_plan_descriptions.plan_id = ?:vendor_plans.plan_id WHERE ?:companies.status = ?s', 'plan_id', 'A');

    // $categories = array();
    // foreach ($plans as &$plan) {
    //     $plan['categories'] = explode(',',$plan['categories']);
    //     $plan['usergroup_ids'] = explode(',',$plan['usergroup_ids']);
    //     $plan_cats = db_get_array('SELECT category_id, usergroup_ids FROM ?:categories WHERE category_id IN (?a)', $plan['categories']);
    //     foreach ($plan_cats as $category) {
    //         if ($category['category_id'] == '1056') continue;
    //         $category['usergroup_ids'] = explode(',',$category['usergroup_ids']);
    //         foreach ($category['usergroup_ids'] as $ug) {
    //             if (!in_array($ug, $plan['usergroup_ids'])) {
    //                 $name = fn_get_usergroup_name($ug);
    //                 if (!empty($name)) $categories[] = array('usergroup_id' => $ug, 'usergroup' => fn_get_usergroup_name($ug), 'category_id' => $category['category_id'], 'category' => fn_get_category_name($category['category_id']), 'plan' => $plan['plan']);
    //             }
    //         }
    //     }
    // }
    // $params['filename'] = 'strange_categories.csv';
    // $params['force_header'] = true;
    // $export = fn_exim_put_csv($categories, $params, '"');

    

    //$plans = db_get_hash_array('SELECT ?:vendor_plans.plan_id, ?:vendor_plan_descriptions.plan, ?:companies.company_id, ?:vendor_plans.categories, ?:vendor_plans.usergroup_ids  FROM ?:vendor_plans RIGHT JOIN ?:companies ON ?:companies.plan_id = ?:vendor_plans.plan_id LEFT JOIN ?:vendor_plan_descriptions ON ?:vendor_plan_descriptions.plan_id = ?:vendor_plans.plan_id WHERE ?:companies.status = ?s AND company_id = 46', 'plan_id', 'A');
    //$plans = db_get_hash_array('SELECT ?:vendor_plans.plan_id, ?:vendor_plan_descriptions.plan, ?:companies.company_id, ?:vendor_plans.categories, ?:vendor_plans.usergroup_ids  FROM ?:vendor_plans RIGHT JOIN ?:companies ON ?:companies.plan_id = ?:vendor_plans.plan_id LEFT JOIN ?:vendor_plan_descriptions ON ?:vendor_plan_descriptions.plan_id = ?:vendor_plans.plan_id WHERE ?:companies.status = ?s', 'plan_id', 'A');
    // $report = array();
    // foreach ($plans as $plan) {
    //     $params['company_id'] = $plan['company_id'];
    //     list($products) = fn_get_products($params);
    //     $product_categories = array_column($products, 'category_ids', 'product_id');
        
    //     $categories = array();
    //     array_walk_recursive($product_categories, function($a) use (&$categories) { $categories[] = $a;});
    //     $categories = array_unique($categories);
    //     $plan['categories'] = explode(',',$plan['categories']);
    //     fn_print_r($plan['plan']);
    //     $prev = count($report);
    //     foreach ($categories as $category_id) {
    //         if ($category['category_id'] == '1056') continue;
    //         if (!in_array($category_id, $plan['categories'])) {
    //             $product_id = 0;
    //             foreach ($product_categories as $product_id => $_product_categories) {
    //                 if (in_array($category_id, $_product_categories)) {
    //                     break;
    //                 }
    //             }
    //             if ($product_id == 24385) fn_print_die($product_categories[$product_id], $plan['categories']);
    //             $report[] = array('product_id' => $product_id, 'product' => fn_get_product_name($product_id), 'category_id' => $category_id, 'category' => fn_get_category_name($category_id), 'plan' => $plan['plan']);
    //         }
    //     }
    //     fn_print_r(count($report) - $prev);
    // }

    // $params['filename'] = 'strange_categories2.csv';
    // $params['force_header'] = true;
    // $export = fn_exim_put_csv($report, $params, '"');



    $plans = db_get_hash_array('SELECT ?:vendor_plans.plan_id, ?:vendor_plan_descriptions.plan, ?:companies.company_id, ?:vendor_plans.categories, ?:vendor_plans.usergroup_ids  FROM ?:vendor_plans RIGHT JOIN ?:companies ON ?:companies.plan_id = ?:vendor_plans.plan_id LEFT JOIN ?:vendor_plan_descriptions ON ?:vendor_plan_descriptions.plan_id = ?:vendor_plans.plan_id WHERE ?:companies.status = ?s', 'plan_id', 'A');

    $report = $report2 = array();
    foreach ($plans as $plan) {
        $params['company_id'] = $plan['company_id'];
        list($products) = fn_get_products($params);
        $product_categories = array_column($products, 'category_ids', 'product_id');
        
        $categories = array();
        array_walk_recursive($product_categories, function($a) use (&$categories) { $categories[] = $a;});
        $categories = array_unique($categories);
        $plan['categories'] = explode(',',$plan['categories']);

        $prev = count($report);

        foreach ($categories as $category_id) {
            if ($category['category_id'] == '1056') continue;
            if (!in_array($category_id, $plan['categories'])) {
                $product_id = 0;
                foreach ($product_categories as $product_id => $_product_categories) {
                    if (in_array($category_id, $_product_categories)) {
                        break;
                    }
                }
                $report[] = array('product_id' => $product_id, 'product' => fn_get_product_name($product_id), 'category_id' => $category_id, 'category' => fn_get_category_name($category_id), 'plan' => $plan['plan']);
            }
        }
        $wrong_categories = array_column($report, 'category', 'category_id');

        foreach ($wrong_categories as $category_id => $category) {
            foreach ($product_categories as $product_id => $_product_categories) {
                if (in_array($category_id, $_product_categories)) {
                    $report2[] = ['product_id' => $product_id, 'product' => fn_get_product_name($product_id), 'category' => $category];
                }
            }
        }
    }

    $params['filename'] = 'strange_products.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($report2, $params, '"');

    fn_print_die('done');
} elseif ($mode == 'correct_ap') {
    $products = [73366, 73369, 73371, 73368, 52273, 52274, 52275, 52276, 52277, 52278, 52279, 52280, 52281, 52282, 52283, 52284, 52285, 52286, 52287, 52288, 52289, 52290, 52291, 52292, 52293, 52294, 52295, 52296, 52297, 52298, 52299, 52300, 52302, 52303, 52304, 52306, 52307, 52308, 52309, 52310, 52311, 52312, 52313, 52314, 52315, 52316, 52317, 52318, 52320, 52322, 71620, 52324, 73364, 61301, 73362, 71616, 71617, 71618, 71619, 71621, 71622, 71623, 71624, 71625, 71626, 73365, 74596, 74597];
    $categories = db_get_fields('SELECT distinct(category_id) FROM ?:products_categories WHERE product_id IN (?a)', $products);
    //$categories = [5373,5374,5378,5379,5381,5382,5383,5384,5385,5372];
    $categories = fn_get_category_ids_with_parent($categories);
    $categories[] = 7085;
    $categories[] = 7098;
    $categories[] = 5375;
    $categories[] = 5376;
    $product_ids = db_get_fields('SELECT product_id FROM ?:products_categories WHERE category_id IN (?a)', $categories);
    //$diff = array_diff($product_ids, $products);
    fn_print_die($product_ids);
/*    $res = fn_get_categories(array(
        'category_id' => 0,
        'current_category_id' => 5372,
        'visible' => true
    ));
    //$res = fn_get_plain_categories_tree('5372');*/
    fn_print_die($descr);
} elseif ($mode == 'check_files') {
    $folder = 'var/files';
    $files = fn_get_dir_contents($folder, false, true, ['.csv', '.xml'], '', true);
    $folders = [];
    foreach ($files as $file) {
        $folders[dirname($file)][] = fn_basename($file);
    }
    foreach ($folders as $folder => $folder_content) {
        $counter[$folder] = count($folder_content);
    }
    asort($counter);
    fn_print_die($counter);
} elseif ($mode == 'restore_akashevo') {
    $file1 = 'products_exprt_08162021_live.csv';
    $file2 = 'products_exprt_08162021.csv';
    $content1 = fn_exim_get_csv(array(), $file1, array('validate_schema'=> false, 'delimiter' => ';') );
    $content2 = fn_exim_get_csv(array(), $file2, array('validate_schema'=> false, 'delimiter' => ';') );
    $current_ids = array_column($content1, 'Product code');
    $old_ids = array_column($content2, 'Product code');
    $deleted = array_diff($old_ids, $current_ids);
    fn_print_die($deleted);
    
} elseif ($mode == 'find_fantom_products') {
    $products = db_get_fields('SELECT distinct(product_id) FROM ?:products_categories');
    $fantoms = db_get_array('SELECT product_id, product_code, company_id FROM ?:products WHERE product_id NOT IN (?a)', $products);
    if ($action == 'delete') {
        foreach ($fantoms as $product) {
            fn_update_product_categories($product['product_id'], ['category_ids' => [1056]]);
        }
    }
    fn_print_die(array_column($fantoms, 'product_id'));
} elseif ($mode == 'find_products_w_lost_images') {
    $params['filename'] = 'lost.csv';
    $params['force_header'] = true;

    if (!$action) {
        $c_products = db_get_hash_multi_array('SELECT product_id, product_code, p.company_id FROM ?:products AS p LEFT JOIN ?:companies AS c ON c.company_id = p.company_id WHERE c.status = ?s AND p.status = ?s', ['company_id', 'product_id'], 'A', 'A');
        $iteration = 0;
        $lost = array();

        foreach ($c_products as $company_id => $products) {
            $company = fn_get_company_name($company_id);
            foreach ($products as $p) {
                $iteration +=1;
                if (!($iteration % 5000)) fn_print_r($iteration);
                $images = fn_get_image_pairs($p['product_id'], 'product', 'A');
                $images[] = fn_get_image_pairs($p['product_id'], 'product', 'M');
                foreach ($images as $img) {
                    if (isset($img['detailed']['absolute_path']) && !is_file($img['detailed']['absolute_path'])) {
                        //$lost[$company_id][] = $p['product_code'];
                        $bn = fn_basename($img['detailed']['absolute_path']);
                        $lost[] = array(
                            'company_id' => $company_id,
                            'company' => $company,
                            'product_id' => $p['product_id'],
                            'product_code' => $p['product_code'],
                            'image' => $bn
                        );
                        continue 2;
                    }
                }
            }
        }

        $export = fn_exim_put_csv($lost, $params, '"');
    } else {
        fn_get_file('var/files/'.$params['filename']);
    }

    fn_print_die('end');
} elseif ($mode == 'recover_images') {
    // find uploaded images
    //Array
    /*(
        [0] => 90355
        [1] => 50016
    )
    */
    
    $file = 'lost.csv';
    $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
    foreach ($content as $p) {
        $res = db_get_array('SELECT * FROM ?:images_links WHERE object_type = ?s AND object_id = ?i', 'product', $p['product_id']);
        if (count($res) > 1) {
            $main_pair = fn_get_image_pairs($p['product_id'], 'product', 'M');
            if (!(is_file($main_pair['detailed']['absolute_path']))) {
                fn_delete_image_unconditionally($main_pair['detailed_id'], $main_pair['pair_id'], 'detailed');
                $data = fn_get_image_pairs($p['product_id'], 'product', 'A');
                
                $img = array_shift($data);
                db_query('UPDATE ?:images_links SET type = ?s WHERE detailed_id = ?i', 'M', $img['detailed_id']);
                $done_products[] = $p['product_id'];
                if ($data) {
                    foreach($data as $d_image) {
                        fn_delete_image_unconditionally($d_image['detailed_id'], $d_image['pair_id'], 'detailed');
                    }
                }
            }
        }
    }
    fn_print_die($done_products);
} elseif ($mode == 'recover_images2') {
    $cond = '';
    if ($action) $cond .= db_quote(' AND p.company_id = ?i', $action);
    $c_products = db_get_hash_multi_array('SELECT product_id, product_code, p.company_id FROM ?:products AS p LEFT JOIN ?:companies AS c ON c.company_id = p.company_id WHERE c.status = ?s AND p.status = ?s ?p', ['company_id', 'product_id'], 'A', 'A', $cond);
    $iteration = 0;
    $lost = array();

    foreach ($c_products as $company_id => $products) {
        $company = fn_get_company_name($company_id);
        foreach ($products as $p) {
            $images = fn_get_image_pairs($p['product_id'], 'product', 'A');
            $images[] = fn_get_image_pairs($p['product_id'], 'product', 'M');
            foreach ($images as $img) {
                if (isset($img['detailed']['absolute_path']) && !is_file($img['detailed']['absolute_path'])) {
                    $bn = fn_basename($img['detailed']['absolute_path']);
                    $ext = fn_get_file_ext($bn);
                    $fname = str_replace('.'.$ext, '', $bn);
                    $check = Registry::get('config.dir.files'). "restore_images/$action/".$fname.".".$ext;
                    $res = is_file($check);
                    if (!$res) {
                        list($fname) = explode('_', $fname);
                        $check = Registry::get('config.dir.files'). "restore_images/$action/".$fname."-new.".$ext;
                        $res = is_file($check);
                    }
                    if ($res) {
                        //update product image

                        $_REQUEST["server_import_image_icon"] = '';
                        $_REQUEST["type_import_image_icon"] = '';
                        $_REQUEST["type_import_image_detailed"] = array('server');
                        $_REQUEST["file_import_image_detailed"] = array($check);
                        $_REQUEST['import_image_data'] = [
                            [
                                'type'         => "M",
                                'image_alt'    => empty($image_alt) ? '' : $image_alt,
                                'detailed_alt' => empty($detailed_alt) ? '' : $detailed_alt,
                                'position'     => empty($position) ? 0 : $position,
                            ]
                        ];

                        $result = fn_attach_image_pairs('import', 'product', $p['product_id']);
                        $successful[] = $p['product_id'];
                    } else {
                        $p['basename'] = $bn;
                        $undone_products[] = $p;
                    }
                }
            }
        }
    }
    fn_print_die($undone_products, $successful);
} elseif ($mode == 'remove_duplicated_main') {
    $images_ = db_get_array('SELECT count(object_id) as cnt, object_id FROM ?:images_links WHERE object_type = ?s AND type = ?s GROUP BY object_id HAVING cnt > 1', 'product','M');
    foreach ($images_ as $img) {
        list($images) = array(fn_get_image_pairs([$img['object_id']], 'product', 'M'));
        $images = reset($images);
        $found = false;

        foreach($images as $main_pair) {
            if (is_file($main_pair['detailed']['absolute_path'])) {
                $found = $main_pair['pair_id'];

            }
        }
        if ($found) {
            foreach($images as $pair_id => $main_pair) {
                if ($pair_id != $found) {
                    fn_delete_image_unconditionally($main_pair['image_id'], $main_pair['pair_id'], 'detailed');
                }
            }
        }
    }
    fn_print_die($images);
} elseif ($mode == 'calculate_return_total') {
    $returns = db_get_fields('SELECT return_id FROM ?:returns ORDER BY return_id DESC');

    foreach ($returns as $return_id) {
        $return = fn_get_returns(['return_id' => $return_id]);
        $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $return['user_id']);
        $customer_auth = fn_fill_auth($_data, array(), false, 'C');
        if ($return['items']) {
            if ($return['items']) {
                $total = 0;
                foreach ($return['items'] as $product) {
                    if ($price = fn_get_product_price($product['product_id'], $product['amount'], $customer_auth)) {
                        $total += $price * $product['amount'];
                    }
                }
                if ($total) {
                    db_query('UPDATE ?:returns SET total = ?d WHERE return_id = ?i', $total, $return_id);
                }
            }
        }
    }
} elseif ($mode == 'import_new_usergroups') {
    Registry::set('runtime.company_id', 45);
    $file = $_REQUEST['filename'] ?? 'new_usergroups.csv';
    $file = 'var/files/'.$file;
    if (!is_file($file)) {
        fn_print_die("File $file not found");
    }

    $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
    $values = [];

    foreach($content as $row) {
        $values = array_merge($values, array_values($row));
        //break;
    }
    array_walk($values, 'fn_trim_helper');
    $values = array_unique($values);
    $ugroup = array('usergroup' => '', 'type' => 'C', 'status' => 'A');
    $counter = 0;
    foreach($values as $usergroup) {
        if ($usergroup == 'NULL') continue;
        if (!($usergroup_id = db_get_field('SELECT usergroup_id FROM ?:usergroup_descriptions WHERE usergroup = ?s', $usergroup))) {
            $counter +=1;
            $ugroup['usergroup'] = $usergroup;
            $ug_id = fn_update_usergroup($ugroup);
        }
    }
    fn_print_die('done', "новых: $counter", "итого: " . count($values));
} elseif ($mode == 'correct_reward_points_new1') {
    $orders_wo_points = [];
    $company_id = 1810;
    if ($action) {
        $company_id = $action;
    }
    if ($company_id == 2058) {
        $exclude_users = [55823,53755,56064,54738,55364,55126,55219,55479,55230,56872,55452,54199,55730,70173,54945,56057];
        $group_ids = [18];
        $promotion_id = 6761;
    } else {
        $exclude_users = [58406,58683,58434,61971,64468,59360,58435,42557,58446,58407,58429,58726,58476,58779,58780,58485,65989,65990,55823,53755,56064,54738,55364,55126,
            55219,55479,55230,56872,55452,54199,55730,70173,54945,56057];
        $group_ids = [17];
        $promotion_id = 4993;
    }
    $promotion = fn_get_promotion_data($promotion_id);
    $cond = fn_find_promotion_condition($promotion['conditions'], 'users');
    $exclude_users = explode(',', $cond['value']);

    $users = db_get_fields('SELECT user_id FROM ?:users WHERE company_id IN (?a) AND user_type = ?s AND user_id NOT IN (?a)', [$company_id], 'C', $exclude_users);
    
    //$manual_corrections = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id IN (?a) AND reason = ?s', $users, '');
    $user_orders = db_get_hash_multi_array('SELECT order_id, user_id, total, timestamp FROM ?:orders WHERE user_id IN (?a) AND timestamp > ?i AND status = ?s AND group_id NOT IN (?a) AND total > 0 AND is_parent_order = "N" ORDER BY timestamp', array('user_id','order_id'), $users, 1656806400, 'H', $group_ids);

    foreach ($user_orders as $user_id => $orders) {
        $order_ids = array_keys($orders);
        $first_order_ts = reset($orders)['timestamp'];
        $ts = strtotime("-6 months", $first_order_ts);
        $first_order = db_get_field('SELECT max(order_id) FROM ?:orders WHERE order_id < ?i AND timestamp > ?i AND user_id = ?i AND total > 0 AND is_parent_order = "N"', reset($order_ids), $ts, $user_id);
        if (!$first_order) {
            $first_order = array_shift($order_ids);
        }

        if (!empty($order_ids)) {
            foreach ($order_ids as $order_id) {
                $order_info = fn_get_order_info($order_id);
                if (empty($order_info['points_info']['reward'])) {//
                    $is_corrected = db_get_row('SELECT change_id, action, timestamp, amount, reason FROM ?:reward_point_changes WHERE user_id = ?i AND (reason like ?l OR (reason like ?l AND amount = ?i))', $user_id, "%$order_id%", '%Корректировка%', round($order_info['total']*0.02));

                    if (!empty($is_corrected)) continue;
                    $orders_wo_points[] = [
                        'order_id' => $order_id,
                        'user_id' => $order_info['user_id'],
                        'user' => $order_info['firstname'],
                        'email' => $order_info['email'],
                        'first_order' => $first_order,
                        'total' => $order_info['total'],
                        'reward' => round($order_info['total']*0.02),
                    ];
                }
            }
        }
    }

    $params['filename'] = "reward_points_$company_id.csv";
    $params['force_header'] = true;
    $export = fn_exim_put_csv($orders_wo_points, $params, '"');
fn_print_die($orders_wo_points);
    foreach ($orders_wo_points as $order) {
        fn_change_user_points($order['reward'], $order['user_id'], "Корректировка баллов по заказу #$order_id: $reward", CHANGE_DUE_ADDITION);
    }

    fn_print_die($orders_wo_points);

} elseif ($mode == 'search_orders_with_promo') {

    if (!isset($_SESSION['iteration']) || $_REQUEST['rerun']) {
        $_SESSION['iteration'] = 1;
    }

    $condition = ' 1 ';
    $promo = db_get_fields('SELECT promotion_id FROM ?:promotions WHERE status = ?s AND company_id IN (?a) AND bonuses LIKE ?l', 'A', [1810], '%'.'give_percent_points'.'%');

    $condition .= 'AND (' .  fn_find_array_in_set($promo, 'promotion_ids', false) . ')';

    $condition .= db_quote(' AND timestamp > ?i', 1651352400);
    $condition .= db_quote(' AND status = ?s', 'H');
    $condition .= db_quote(' AND group_id NOT IN (?a)', [17]);
    // $join = db_quote(' LEFT JOIN ?:order_data ON ?:order_data.order_id = ?:orders.order_id AND ?:order_data.type = ?s', 'W');
    // $condition .= db_quote(' AND ?:order_data.order_id IS NULL');
    $step = 100;
    $limit = ' LIMIT '. ($_SESSION['iteration']-1)* $step . ', ' . $step;

    $orders = db_get_fields("SELECT ?:orders.order_id FROM ?:orders $join WHERE $condition $limit");

    if (empty($orders)) {
        foreach ($_SESSION['wrong'] as $order_id => $diff) {
            $db_points = db_get_field('SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s', $order_id, 'W');
            $reward = $db_points + $diff;
            $user_id = db_get_field('SELECT user_id FROM ?:orders WHERE order_id = ?i', $order_id);
            fn_change_user_points($diff, $user_id, "Корректировка баллов по заказу #$order_id: $db_points —> $reward", CHANGE_DUE_ADDITION);
        }
        fn_print_die($_SESSION['wrong'], count($_SESSION['wrong']), array_sum($_SESSION['wrong']));
    }
    if (!isset($_SESSION['total']) || $_REQUEST['rerun']) {
        $_SESSION['total'] = db_get_field("SELECT count(?:orders.order_id) FROM ?:orders $join WHERE $condition");
        unset($_SESSION['wrong']);
    }
    fn_print_r($_SESSION['iteration'] . ' / '. ceil($_SESSION['total']/$step));
    fn_print_r($_SESSION['wrong']);

    define('ORDER_MANAGEMENT', true);
    Registry::set('runtime.mode', 'update');

    $i = 0;
    foreach ($orders as $order_id) {
        $i +=1;
        fn_echo('.');
        if ($i % 20 == 0) fn_echo('<br>');
        // $db_points = db_get_field('SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s', $order_id, POINTS);

        // fn_clear_cart($cart, true);
        // $customer_auth = fn_fill_auth(array(), array(), false, 'C');

        // $cart_status = md5(serialize($cart));
        // fn_form_cart($order_id, $cart, $customer_auth, !empty($_REQUEST['copy']));

        // fn_store_shipping_rates($order_id, $cart, $customer_auth);
        // $cart['order_id'] = $order_id;
        // $cart['calculate_shipping'] = true;

        // // calculate cart - get products with options, full shipping rates info and promotions
        // list ($cart_products, $product_groups) = fn_calculate_cart_content($cart, $customer_auth);
        $order_info = fn_get_order_info($order_id);
        $db_points = $order_info['points_info']['reward'];
        $fact = round($order_info['total'] * 0.02);
        if ($fact != $db_points) $_SESSION['wrong'][$order_id] = $fact - $db_points;
    }
    $_SESSION['iteration'] += 1;
    fn_redirect('tools.search_orders_with_promo');
} elseif ($mode == 'correct_reward_points_new') {
    $file = 'orders.csv';
    $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
    $order_ids = [];
    foreach ($content as $data) {
        $order_ids[] = reset($data);
    }
    sort($order_ids);

    define('ORDER_MANAGEMENT', true);
    Registry::set('runtime.mode', 'update');
    foreach ($order_ids as $order_id) {
        if (db_get_field('SELECT user_id FROM ?:orders WHERE order_id = ?i', $order_id) == 32278) continue;
        $db_points = db_get_field('SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s', $order_id, POINTS);

        fn_clear_cart($cart, true);
        $customer_auth = fn_fill_auth(array(), array(), false, 'C');

        $cart_status = md5(serialize($cart));
        fn_form_cart($order_id, $cart, $customer_auth, !empty($_REQUEST['copy']));

        fn_store_shipping_rates($order_id, $cart, $customer_auth);
        $cart['order_id'] = $order_id;
        $cart['calculate_shipping'] = true;

        // calculate cart - get products with options, full shipping rates info and promotions
        list ($cart_products, $product_groups) = fn_calculate_cart_content($cart, $customer_auth);

        if (!$db_points) {
            if (isset($cart['points_info']['reward'])) {
                $order_data = array(
                    'order_id' => $order_id,
                    'type' => POINTS,
                    'data' => $cart['points_info']['reward']
                );
                db_query("REPLACE INTO ?:order_data ?e", $order_data);
            }
            fn_change_user_points($cart['points_info']['reward'], $customer_auth['user_id'], serialize(['order_id' => $order_id, 'to' => fn_get_order_short_info($order_id)['status']]), CHANGE_DUE_ORDER);
            $order_changes[] = $order_id;
        } elseif ($db_points != $cart['points_info']['reward']) {
            $order = fn_get_order_info($order_id);
            $promotions = array_filter($order['promotions'], function($v) {return isset($v['bonuses']['give_percent_points']); });
            if (count($promotions) > 1) {
                // two promo
                $res = db_get_field('SELECT order_id FROM ?:orders WHERE user_id = ?i AND order_id < ?i AND status = ?s', $cart['user_data']['user_id'], $order_id, 'H');
                if (!$res) {
                    // first order and we disable 4993
                    $prev = $cart['points_info']['reward'];
                    $reward = round($order['subtotal']*0.1);

                    if (isset($cart['points_info']['reward'])) {
                        $order_data = array(
                            'order_id' => $order_id,
                            'type' => POINTS,
                            'data' => $reward
                        );
                        db_query("REPLACE INTO ?:order_data ?e", $order_data);
                    }

                    fn_change_user_points($reward-$db_points, $customer_auth['user_id'], "Корректировка баллов по заказу #$order_id: $db_points —> $reward", CHANGE_DUE_ADDITION);
                    $user_changes[] = $customer_auth['user_id'];
                    // fn_print_die($cart['points_info']['reward'], $db_points, round($order['subtotal']*0.1), $order_id, $customer_auth['user_id']);
                    // correct points
                } else {
                    // second order and we disable 4723
                    fn_print_die('second order');
                }
            } elseif ($db_points != round($order['subtotal']*0.02)) {
                // wrong amount
                fn_print_die($cart['points_info']['reward'], $db_points, round($order['subtotal']*0.02));
            }

        }
    }

    fn_print_die('done', $order_changes, $user_changes);
} elseif ($mode == 'baltica_maintenance') {
    $product_ids = db_get_fields('SELECT product_id FROM ?:products WHERE company_id = 45');
    db_query('DELETE FROM ?:product_prices WHERE price = 0 AND usergroup_id != 0 AND product_id IN (?a)', $product_ids);

    $users = db_get_fields('SELECT user_id FROM ?:users WHERE company_id = 45 AND user_type = ?s AND user_id != ?i', 'C', 5055);
    foreach($users as $user_id) {
        fn_delete_profile_fields_data(ProfileDataTypes::USER, $user_id);
    }

    $profile_ids = db_get_fields('SELECT profile_id FROM ?:user_profiles WHERE user_id IN (?a)', $users);

    foreach ($profile_ids as $profile_id) {
        fn_delete_profile_fields_data(ProfileDataTypes::PROFILE, $profile_id);
    }

    db_query("DELETE FROM ?:user_storages WHERE user_id IN (?a)", $users);
    db_query('DELETE FROM ?:user_session_products WHERE user_id IN (?a)', $users);
    db_query('DELETE FROM ?:user_data WHERE user_id IN (?a)', $users);
    db_query('UPDATE ?:orders SET user_id = 0 WHERE user_id IN (?a)', $users);
    db_query('DELETE FROM ?:usergroup_links WHERE user_id IN (?a)', $users);
    db_query('DELETE FROM ?:users WHERE user_id IN (?a)', $users);
    fn_print_die('stop');
} elseif ($mode == 'baltica_maintenance2') {
    $usergroups = db_get_field('SELECT usergroup_ids FROM ?:vendor_plans WHERE plan_id = ?i', 29);
    $usergroups = explode(',', $usergroups);
    if(($balt = array_search(548,$usergroups)) !== false){
        unset($usergroups[$balt]);
    }

    fn_delete_usergroups($usergroups);
    $products = db_get_fields('SELECT product_id FROM ?:products WHERE company_id = ?i', 45);
    foreach ($products as $product_id) {
        fn_delete_product($product_id);
    }
    fn_print_die('stop');
} elseif ($mode == 'prices_maintenance___') {
    $res = db_get_hash_single_array('SELECT MIN(p.price) AS price, p.product_id FROM ?:product_prices AS p LEFT JOIN ?:product_prices AS pp ON pp.product_id = p.product_id AND pp.usergroup_id = 0 WHERE pp.product_id IS NULL GROUP BY product_id', array('product_id', 'price'));
    $template = ['product_id' => 0, 'price' => 0, 'percentage_discount' => 0, 'lower_limit' => 1, 'usergroup_id' => USERGROUP_ALL];
    $insert = [];
    foreach($res as $template['product_id'] => $template['price']) {
        $insert[] = $template;
    }
    fn_print_die(count($insert), $insert);

    $res = db_get_fields('SELECT p.product_id FROM ?:products AS p LEFT JOIN ?:product_prices AS pp ON pp.product_id = p.product_id WHERE pp.product_id IS NULL GROUP BY product_id');
    $template['price'] = 0;
    foreach ($res as $template['product_id']) {
        $insert[] = $template;
    }

    if ($insert) db_query('INSERT INTO ?:product_prices ?m', $insert);
} elseif ($mode == 'restore_delivery_days') {
    $file = 'cscart_users.csv';
    $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
    //$content = array_filter($content);

    $data = array_column($content,'delivery_date', 'user_id');
    $our_users = db_get_fields('SELECT user_id FROM ?:users WHERE user_id IN (?a) AND delivery_date = ?s', array_keys($data), '0000000');
    foreach ($our_users as $user_id) {
        if ($data[$user_id] != '0000000') {
            db_query('UPDATE ?:users SET delivery_date = ?s WHERE user_id = ?i', $data[$user_id], $user_id);
        }
    }
} elseif ($mode == 'zeroing_amount') {
    $company_ids = ['1810', '2058'];
    db_query('UPDATE ?:products SET amount = 0 WHERE company_id IN (?a)', $company_ids);
} elseif ($mode == 'remove_usergroup_all') {
    $products = db_get_array('SELECT company_id, product_id, usergroup_ids FROM ?:products WHERE status = ?s', 'A');
    foreach ($products as $data) {
        $usergroups = $data['usergroup_ids'];
        $usergroups = explode(',', $usergroups);
        if (in_array('', $usergroups) || in_array('0', $usergroups)) {
            $wrong_products[$data['company_id']][] = $data['product_id'];
        }
    }
    fn_print_die($wrong_products);
} elseif ($mode == 'remove_usergroup_baltika') {
    $products = db_get_array('SELECT company_id, product_id, usergroup_ids FROM ?:products WHERE status = ?s AND FIND_IN_SET(?i, usergroup_ids)', 'A', '548');
    foreach ($products as $data) {
        $res = db_query("UPDATE ?:products SET usergroup_ids = ?p WHERE product_id = ?i", fn_remove_from_set('usergroup_ids', 548), $data['product_id']);
    }
    fn_print_die($products);
} elseif ($mode == 'correct_profile_fields') {
    $company_id = 45;
    $condition = '';
    //$condition = db_quote(' AND user_id = 126771');
    //$limit = db_quote(' LIMIT 200');

    $user_ids = db_get_fields("SELECT user_id FROM ?:users WHERE company_id = ?i AND user_type = ?s AND user_id != ?i $condition ORDER BY user_id DESC $limit", $company_id, 'C', 5055);

    $i = 0;
    foreach ($user_ids as $user_id) {
        $i +=1;
        if ($i % 100 == 0) fn_echo_br($i);
        $user_data = fn_get_user_info($user_id);
        $data = db_get_array('SELECT * FROM ?:profile_fields_data WHERE object_id = ?i', $user_id);

        foreach ($data as $value) {
            $field_id = $value['field_id'];
            $user_value = $user_data['fields'][$field_id];
            if (empty($value['value'])) continue;

            if ($value['value'] == $user_value) {
                if ($value['object_type'] == 'O') {
                    $order = fn_get_order_info($value['object_id']);
                    $order_user = fn_get_user_info($order['user_id']);
                    $profiles = fn_get_user_profiles($order['user_id']);
                    if (count($profiles) == 1) {
                        db_query('UPDATE ?:profile_fields_data SET value = ?s WHERE object_id = ?i AND object_type = ?s AND field_id = ?s', $order_user['fields'][$field_id], $value['object_id'], 'O', $field_id);
                        $res['O'][$value['object_id']] = $order_user['fields'][$field_id];
                    } else {
                        fn_print_die('check here order');
                    }
                } elseif ($value['object_type'] == 'P') {
                    $user_id = db_get_field('SELECT user_id FROM ?:user_profiles WHERE profile_id = ?i', $value['object_id']);
                    $profiles = fn_get_user_profiles($user_id);
                    $profile_user = fn_get_user_info($user_id);
                    if (count($profiles) == 1) {
                        db_query('UPDATE ?:profile_fields_data SET value = ?s WHERE object_id = ?i AND object_type = ?s AND field_id = ?s', $profile_user['fields'][$field_id], $value['object_id'], 'P', $field_id);
                        $res['P'][$value['object_id']] = $profile_user['fields'][$field_id];
                    } else {
                        fn_print_die('check here profile');
                    }
                } elseif ($value['object_type'] == 'U') {
                    // ну это нормально
                    //if ($user_data['company_id'] == '45') $user_value = $user_data['user_login'];
                    //fn_print_die($value, $user_data);
                    //fn_print_die('check here');
                }
            } elseif ($value['object_type'] == 'U') {
                $profiles = fn_get_user_profiles($value['object_id']);
                if (count($profiles) == 1) {
                    // на всякий случай
                    $user_value = $user_data['user_login'];
                    db_query('UPDATE ?:profile_fields_data SET value = ?s WHERE object_id = ?i AND object_type = ?s AND field_id = ?s', $user_value, $value['object_id'], 'U', $field_id);
                    $res['U'][$value['object_id']] = $user_value;
                } else {
                    fn_print_die('check here profile');
                }
            } elseif ($value['object_type'] == 'O') {
                $order = fn_get_order_info($value['object_id']);
                $order_user = fn_get_user_info($order['user_id']);
                $profiles = fn_get_user_profiles($order['user_id']);
                if (count($profiles) == 1) {
                    db_query('UPDATE ?:profile_fields_data SET value = ?s WHERE object_id = ?i AND object_type = ?s AND field_id = ?s', $order_user['fields'][$field_id], $value['object_id'], 'O', $field_id);
                    $res['O'][$value['object_id']] = $order_user['fields'][$field_id];
                } else {
                    $v = db_get_field('SELECT value FROM ?:profile_fields_data WHERE object_id = ?i AND object_type = ?s AND field_id = ?i', $order['profile_id'], 'P', $field_id);
                    if ($v != $value['value']) {
                        //fn_print_die('check here order');
                    }
                }
            }
        }
    }
    fn_print_die('end', $res);
} elseif ($mode == 'baltica_maintenance3') {
    $file = 'ug_all.csv';
    $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
    $content = array_column($content, 'usergroup', 'usergroup_id');
    //fn_print_die($content);
    $another_usergroups = [];
    $another_usergroup_ids = db_get_fields('SELECT usergroup_ids FROM ?:vendor_plans WHERE plan_id != 29 AND plan_id != 79');

    foreach ($another_usergroup_ids as $ugs) {
        $another_usergroups = array_merge($another_usergroups, explode(',',$ugs));
    }
    $another_usergroups = array_filter($another_usergroups);

    $usergroups = fn_get_usergroups(['type' => 'C', 'status' => 'A']);
    $usergroups = array_column($usergroups, 'usergroup', 'usergroup_id');

    ksort($usergroups);
    foreach ($another_usergroups as $id) {
        unset($usergroups[$id]);
    }

    if (empty($action)) {
        fn_print_die($usergroups);
    } else {
        $usergroups = array_slice($usergroups, $action, null, true);
    }

    $diff = array_diff_key($usergroups, $content);

    if (!empty($diff)) {
        fn_delete_usergroups(array_keys($diff));
    }
    db_query('UPDATE ?:vendor_plans SET usergroup_ids = ?s WHERE plan_id = ?i', implode(',', array_keys($content)), 29);
    fn_print_die('done');
} elseif ($mode == 'baltica_maintenance4') {
    Registry::set('runtime.company_id', 45);
    list($balt_users, ) = fn_get_users(array('user_type' => 'C'), $auth);
    $ids = db_get_fields('SELECT user_id FROM ?:users WHERE `company_id` = 45 AND timestamp > 1664917201 ORDER BY `timestamp` DESC;');
    db_query('DELETE FROM ?:user_session_product WHERE user_id IN (?a) AND user_type = ?s', $ids, 'R');
    db_query('DELETE FROM ?:user_price WHERE user_id IN (?a)', $ids);
    db_query('DELETE FROM ?:user_data WHERE user_id IN (?a)', $ids);
    db_query('DELETE FROM ?:user_profiles WHERE user_id IN (?a)', $ids);
    db_query('DELETE FROM ?:user_storages WHERE user_id IN (?a)', $ids);
    db_query('DELETE FROM ?:usergroup_links WHERE user_id IN (?a)', $ids);
    
    db_query('DELETE FROM ?:users WHERE user_id IN (?a)', $ids);
    
    

    fn_print_die($ids);
    $ids = array_column($balt_users, 'user_id');
    //$res = db_query("UPDATE ?:user_profiles SET `profile_name` = '' WHERE user_id IN (?a)", $ids);
    $profiles = db_get_array('SELECT user_id, max(profile_id) as profile_id, count(profile_id) AS cnt FROM ?:user_profiles WHERE user_id IN (?a) GROUP BY user_id HAVING cnt > 1 ', $ids);
    $profile_ids = array_column($profiles, 'profile_id');
    $res = db_query('DELETE FROM ?:user_profiles WHERE profile_id IN (?a)', $profile_ids);
    fn_print_die($res);
} elseif ($mode == 'export_reward_points') {
    $params = $_REQUEST;

    if (empty($action)) fn_print_die('action??');
    $params['company_id'] = $action;
    $params['period'] = 'C';
    $params['status'] = ['H'];

    $rp_condition = '';
    if (!empty($params['period']) && $params['period'] != 'A') {
        list($time_from, $time_to) = fn_create_periods($params);
        $rp_condition = db_quote("AND timestamp >= ?i AND timestamp <= ?i", $time_from, $time_to);
    }

    list($users) = fn_get_users(array('user_type' => 'C', 'company_id' => $params['company_id']), $auth);
    $user_ids = array_column($users, 'user_id');

    $data = [];
    foreach ($user_ids as $user_id) {
        $user_info = fn_get_user_info($user_id);
        $params['user_id'] = $user_id;
        list($orders, ) = fn_get_orders($params);

        if (empty($orders)) continue;
        $data[] = array(
            'user_id' => $user_id,
            'code' => $user_info['fields'][39],
            'current_points' => fn_get_user_additional_data(POINTS, $user_id) ?? 0,
            'sum_orders_total' => array_sum(array_column($orders, 'total')),

            'used_points' => abs(db_get_field("SELECT sum(amount) FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s ?p", $user_id, CHANGE_DUE_ORDER_PLACE, $rp_condition)),
        );
    }
    $opts = array('delimiter' => ';', 'filename' => 'points_'.$action.'.csv');
    $res = fn_exim_put_csv($data, $opts, '"');
    fn_get_file('var/files/'.$opts['filename']);
    exit();
} elseif ($mode == 'export_id') {
    list($users) = fn_get_users(array('user_type' => 'C', 'company_id' => 45), $auth);

    //$user_ids = array_column($users, 'user_id');
    $opts = array('delimiter' => ';', 'filename' => 'users_.csv');
    $res = fn_exim_put_csv($users, $opts, '"');
    fn_print_die($res);
} elseif ($mode == 'recover_passwords') {
    list($users) = fn_get_users(array('user_type' => 'C', 'company_id' => 45), $auth);
    $passwords = db_get_array("SELECT user_id, password FROM ?:users WHERE user_id IN (?a) AND LENGTH(`password`) < 10", array_column($users, 'user_id'));
    foreach ($passwords as $value) {
        if (empty($value['password'])) $value['password'] = 1111;
        $value['password'] = fn_password_hash($value['password']);
        db_query('UPDATE ?:users SET password = ?s WHERE user_id = ?i', $value['password'], $value['user_id']);
    }
    fn_print_die('stop');
} elseif ($mode == 'delete_users') {
    $file = 'SD_users_na.csv';
    $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
    $users = array_column($content, 'user_id');
    db_query("DELETE FROM ?:user_storages WHERE user_id IN (?a)", $users);
    db_query('DELETE FROM ?:user_session_products WHERE user_id IN (?a)', $users);
    db_query('DELETE FROM ?:user_data WHERE user_id IN (?a)', $users);
    db_query('UPDATE ?:orders SET user_id = 0 WHERE user_id IN (?a)', $users);
    db_query('DELETE FROM ?:usergroup_links WHERE user_id IN (?a)', $users);
    db_query('DELETE FROM ?:usergroup_links WHERE user_id IN (?a)', $users);
    db_query('DELETE FROM ?:user_storages WHERE user_id IN (?a)', $users);
    db_query('DELETE FROM ?:users WHERE user_id IN (?a)', $users);
    fn_print_die('stop');
} elseif ($mode == 'cleanup') {
    if ($action == 'sessions') {
        $iteration = empty($dispatch_extra) ? 1 : $dispatch_extra;
        if ($iteration == '1') {
            $_SESSION['user_sessions'] = [];
        }

        $step = 5000;
        $limit = ' LIMIT '. ($iteration - 1) * $step . ', ' . $step;

        if ($sessions = db_get_array('SELECT * FROM ?:sessions ?p', $limit)) {
            $sessions = array_map(
                function ($value) {
                    $value['data'] = \Tygh::$app['session']->decode($value['data']);
                    return $value;
                },
                $sessions
            );

            $user_sessions = array_filter($sessions, function($v) {
                return $v['data']['auth']['user_id'];
            });

            $user_sessions = array_column($user_sessions, 'session_id');

            $_SESSION['user_sessions'] = array_merge($_SESSION['user_sessions'], $user_sessions);
            fn_print_r(count($_SESSION['user_sessions']));
        } else {
            $res = [];
            $res[] = db_query('DELETE FROM ?:sessions WHERE session_id NOT IN (?a)', $_SESSION['user_sessions']);
            fn_print_die($res, count($_SESSION['user_sessions']));
        }

        $iteration += 1;
        fn_redirect('tools.cleanup.sessions.' . $iteration);
    }
    if ($action == 'products') {
        $product_ids = db_get_fields('SELECT product_id FROM ?:products');
        foreach($product_ids as $product_id) {
            $data = fn_get_product_data($product_id, $auth);
            if (empty($data)) {
                $wrong['products'][] = $product_id;
            }
        }
        db_query('DELETE FROM ?:products WHERE product_id IN (?a)', $wrong['products']);
        fn_print_r('fantoms', count($wrong['products']));
        $cleanup_db = [
            'images_links' => [
                'object_id' => 'product_id', 
                'object_type' => 'P'
            ],
            'also_bought_products',
            'also_bought_products2' => [
                'table' => 'also_bought_products',
                'related_id' => 'product_id'
            ],
            'products_categories',
            'product_descriptions',
            'product_features_values',
            'product_point_prices',
            'product_relations',
            'product_relations2' => [
                'table' => 'product_relations',
                'related_id' => 'product_id'
            ],
            'product_sales',
            'return_products',
            'reward_points' => [
                'object_id' => 'product_id', 
                'object_type' => 'P'
            ],
            'storages_products',
            'user_price',
            'user_session_products'
        ];
        foreach ($cleanup_db as $field => $table ) {
            $condition = '';
            if (is_array($table)) {
                $where = $table;
                $table = $field;
            } else {
                $where = ['product_id'];
            }
            if (isset($where['table'])) {
                $table = $where['table'];
                unset($where['table']);
            }

            foreach ($where as $key => $value) {
                if (empty($key)) $key = 'product_id';
                if ($value == 'product_id') {
                    $condition .= db_quote(' AND ?f NOT IN (?a)', $key, $product_ids);
                } else {
                    $condition .= db_quote(' AND ?f = ?s', $key, $value);
                }
            }

            $res = db_query("DELETE FROM ?:?f WHERE 1 $condition", $table);
            fn_print_r($table, $res);
        }
    }
    if ($action == 'users') {
        $user_ids = db_get_fields('SELECT user_id FROM ?:users');
        $cleanup_db = [
            'user_managers1' => [
                'table' => 'user_managers',
                'user_id' => 'user_id'
            ],
            'user_managers2' => [
                'table' => 'user_managers',
                'manager_id' => 'user_id'
            ],
            'user_session_products',
            'user_profiles',
            'user_price',
            'user_data',
            'user_storages',
            'usergroup_links',
            'reward_point_changes'
        ];
        foreach ($cleanup_db as $field => $table ) {
            $condition = '';
            if (is_array($table)) {
                $where = $table;
                $table = $field;
            } else {
                $where = ['user_id'];
            }
            if (isset($where['table'])) {
                $table = $where['table'];
                unset($where['table']);
            }
            
            foreach ($where as $key => $value) {
                if (empty($key)) $key = 'user_id';
                if ($value == 'user_id') {
                    $condition .= db_quote(' AND ?f NOT IN (?a)', $key, $user_ids);
                } else {
                    $condition .= db_quote(' AND ?f = ?s', $key, $value);
                }
            }

            $res = db_query("DELETE FROM ?:?f WHERE 1 $condition", $table);
            fn_print_r($table, $res);
        }
    }
    if ($action == 'orders') {
        $iteration = empty($dispatch_extra) ? 1 : $dispatch_extra;

        if ($iteration == 1) $res1 = db_query('DELETE FROM ?:order_data WHERE order_id NOT IN (SELECT order_id FROM ?:orders)');

        $step = 500;
        $limit = ' LIMIT '. ($iteration-1)* $step . ', ' . $step;
        $order_ids = db_get_fields("SELECT order_id FROM ?:orders WHERE timestamp < ?i ORDER BY order_id DESC $limit", strtotime('-4 months'));
        if (empty($order_ids)) {
            fn_print_die('done');
        } else {
            fn_print_r(reset($order_ids));
        }
        $order_data = db_get_hash_single_array('SELECT order_data_id, data FROM ?:order_data WHERE type = ?s AND order_id IN (?a)', array('order_data_id', 'data'), 'G', $order_ids);

        foreach($order_data as $id => $data) {

            $data = unserialize($data);
            if (count($data) > 1) {
                db_query('DELETE FROM ?:order_data WHERE order_data_id = ?i', $id);
            } else {
                unset(
                    $data[0]['package_info'],
                    $data[0]['all_edp_free_shipping'],
                    $data[0]['all_free_shipping'],
                    $data[0]['free_shipping'],
                    $data[0]['shipping_no_required'],
                    $data[0]['shippings']
                );

                $data[0]['chosen_shippings'] = array_map(function ($shipping) {
                    $shipping['data'] = [];
                    return $shipping;
                }
                    , $data[0]['chosen_shippings']);

                $data[0]['products'] = array_map(function ($product) {
                    unset(
                        $product['main_pair'], 
                        $product['user_data'], 
                        $product['user_id'], 
                        $product['timestamp'],
                        $product['type'],
                        $product['order_id'],
                        $product['user_type'],
                        $product['session_id'],
                        $product['ip_address'],
                        $product['storefront_id'],
                        $product['product_options'],
                        $product['options_type'],
                        $product['exceptions_type'],
                        $product['options_type_raw'],
                        $product['exceptions_type_raw'],
                        $product['qty_step_raw'],
                        $product['modifiers_price'],
                        $product['is_edp'],
                        $product['edp_shipping'],
                        $product['firstname'],
                        $product['lastname'],
                        $product['email'],
                        $product['phone'],
                        $product['chosen_shipping'],
                        $product['extra']['usergroup_id'],
                        $product['extra']['unlimited_download'],
                        $product['extra']['pay_by_points'],
                        $product['extra']['product_options'],
                    );

                    if (empty($product['group_id'])) unset($product['group_id']);
                    if (empty($product['promotions'])) unset($product['promotions']);
                    if (empty($product['extra'])) unset($product['extra']);
                    return $product;
                }
                    , $data[0]['products']);


                $data = serialize($data);
                db_query('UPDATE ?:order_data SET data = ?s WHERE order_data_id = ?i', $data, $id);
            }
        }
        $iteration += 1;
        fn_redirect('tools.cleanup.orders.' . $iteration);
    }
    if ($action == 'storages') {
        list($storages) = fn_get_storages();
        $wrong_storages = array_filter($storages, function($s) {
            return !$s['company_id'];
        });
        $res[] = fn_delete_storages(array_keys($wrong_storages));
        list($storages) = fn_get_storages();
        $res[] = db_query('DELETE FROM ?:user_storages WHERE storage_id NOT IN (?a)', array_keys($storages));
        $res[] = db_query('DELETE FROM ?:storages_products WHERE storage_id NOT IN (?a)', array_keys($storages));
        fn_print_die($res, 'stop');
    }
    if ($action == 'promotions') {
        if (!empty($dispatch_extra)) {
            $prev_month = date_create('last day of previous month 23:59:59');
            $promotion_ids = db_get_fields('SELECT promotion_id FROM ?:promotions WHERE ((to_date != 0 AND to_date <= ?i) OR status != ?s) AND company_id = ?i', $prev_month->getTimestamp(), 'A', $dispatch_extra);
            fn_delete_promotions($promotion_ids);
            fn_print_die('promotions', $promotion_ids);
        } else {
            fn_print_die('empty company_id in $dispatch_extra');
        }
    }
    fn_print_die('done');
} elseif ($mode == 'remove') {
    if (empty($action)) fn_print_die('dispatch=tools.cleanup.[smthng]&company_id=xxx');
    if (!($company_id = $_REQUEST['company_id'])) fn_print_die('add &company_id=xxx');

    if ($action == 'user_prices') {
        $res = db_query('DELETE FROM ?:user_price WHERE product_id IN (SELECT product_id FROM ?:products WHERE company_id = ?i)', $company_id);
    }
    if ($action == 'prices') {
        $res = db_query('DELETE FROM ?:product_prices WHERE product_id IN (SELECT product_id FROM ?:products WHERE company_id = ?i) AND usergroup_id != ?i', $company_id, 0);
    }
    if ($action == 'product_images') {
        $res = 0;
        $products = db_get_fields('SELECT product_id FROM ?:products WHERE company_id = ?i', $company_id);
        foreach ($products as $product_id) {
            fn_delete_image_pairs($product_id, 'product');
        }
        $res = count($products);
    }
    if ($action == 'products') {
        $product_ids = db_get_fields('SELECT product_id FROM ?:products WHERE company_id = ?i', $company_id);
        foreach ($product_ids as $product_id) {
            fn_delete_product($product_id);
        }
        $res = count($product_ids);
    }

    fn_print_die('Done. Count:', $res);
} elseif ($mode == 'remove_empty_storages') {
    $empty_storages = db_get_fields('SELECT storage_id FROM ?:storages WHERE company_id = 0');
    fn_delete_storages($empty_storages);
    fn_print_die(count($empty_storages));
} elseif ($mode == 'managers_addon_migration') {
    $managers = db_get_array('SELECT * FROM ?:vendors_customers');
    $managers = array_map(function($v) {
        $v['user_id'] = $v['customer_id'];
        $v['manager_id'] = $v['vendor_manager'];
        unset($v['customer_id'], $v['vendor_manager']);
        return $v;
    }, $managers);

    db_query("CREATE TABLE `?:user_managers` (
          `user_id` mediumint(8) unsigned NOT NULL,
          `manager_id` mediumint(8) unsigned NOT NULL,
          UNIQUE KEY `user_manager` (`user_id`,`manager_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
    db_query('INSERT INTO ?:user_managers ?m', $managers);
    db_query('DROP TABLE ?:vendors_customers');
    db_query('UPDATE ?:users SET user_role = ?s WHERE is_manager = ?s', 'M', 'Y');
    db_query('ALTER TABLE ?:users DROP is_manager');
    fn_print_die('done');
} elseif ($mode == 'delete_storages') {
    $file = 'storages_balt.csv';
    $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
    $ok_storages = array_column($content, 'storage_id');
    $storage_ids = db_get_fields('SELECT storage_id FROM ?:storages WHERE company_id = 45 AND storage_id NOT IN (?a)', $ok_storages);
    fn_delete_storages($storage_ids);
    fn_print_die('done');
} elseif ($mode == 'correct_rp') {
    $file = 'rp.csv';
    $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );

    foreach ($content as $value) {
        fn_change_user_points($value['разница'], $value['user_id'], "Корректировка баллов от 31.10.2022 +" . $value['разница'], CHANGE_DUE_ADDITION);
    }

    fn_print_r('done 1');

    $file = 'rp1810.csv';
    $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );

    foreach ($content as $value) {
        fn_change_user_points($value['разница'], $value['user_id'], "Корректировка баллов от 31.10.2022 +" . $value['разница'], CHANGE_DUE_ADDITION);
    }

    fn_print_die('done');
} elseif ($mode == 'remove_fantom_network_id') {
    $users = db_get_fields('SELECT user_id FROM ?:users');
    $res = db_query("UPDATE ?:users SET network_id = '' WHERE network_id NOT IN (?a)", $users);
    fn_print_die($res);
} elseif ($mode == 'categories_maintenance') {
    $category_ids = db_get_fields('SELECT category_id FROM ?:categories WHERE status != ?s', 'H');
    db_query('UPDATE ?:categories SET status = ?s WHERE category_id IN (?a)', 'A', $category_ids);
    $empty = [];
    foreach ($category_ids as $category_id) {
        list($products) = fn_get_products(['cid' => $category_id, 'subcats' => 'Y', 'amount_from' => 1, 'status' => 'A']);
        if (!empty($products)) continue;
        $empty[$category_id] = fn_get_category_name($category_id);
        if (mb_stripos($empty[$category_id], 'акции') !== false) unset($empty[$category_id]);
    }
    $res = db_query('UPDATE ?:categories SET status = ?s WHERE category_id IN (?a)', 'D', array_keys($empty));
    fn_print_die($res, $empty);
} elseif ($mode == 'storage_104_upgrade') {
    db_query("ALTER TABLE ?:storages ADD `delivery_date` VARCHAR(7) NOT NULL DEFAULT '1111111' AFTER `exception_time_till`;");
    $storages = db_get_array('SELECT storage_id, saturday_shipping, sunday_shipping, delivery_date FROM ?:storages');
    foreach ($storages as $storage_settings) {
        $storage_settings['delivery_date'][0] = YesNo::toBool($storage_settings['sunday_shipping']) ? 1 : 0;
        $storage_settings['delivery_date'][6] = YesNo::toBool($storage_settings['saturday_shipping']) ? 1 : 0;
        if ($storage_settings['delivery_date'] == '1111111') continue;
        db_query('UPDATE ?:storages SET delivery_date = ?s WHERE storage_id = ?i', $storage_settings['delivery_date'], $storage_settings['storage_id']);
        fn_print_r($storage_settings['storage_id']);
    }
    db_query("ALTER TABLE ?:storages DROP `saturday_shipping`, DROP `sunday_shipping`;");
    fn_print_die('done');
} elseif ($mode == 'remove_heavy_carts') {
    $user_ids = db_get_fields("SELECT user_id FROM ?:user_session_products WHERE type = 'C' GROUP BY user_id HAVING count(product_id) > 80");
    $res = db_query("DELETE FROM ?:user_session_products WHERE type = 'C' AND user_id IN (?a)", $user_ids);
    fn_print_die($res);
} elseif ($mode == 'remove_heavy_wishlists') {
    $user_ids = db_get_fields("SELECT user_id FROM ?:user_session_products WHERE type = 'W' GROUP BY user_id HAVING count(product_id) > 60");
    $res = db_query("DELETE FROM ?:user_session_products WHERE type = 'W' AND user_id IN (?a)", $user_ids);
    fn_print_die($res);
} elseif ($mode == 'remove_zero_prices') {
    $product_ids = db_get_fields('SELECT product_id FROM ?:products WHERE company_id = 2186');
    $res[] = db_query('DELETE FROM ?:product_prices WHERE price = 0 AND usergroup_id != 0 AND product_id IN (?a)', $product_ids);
    $res[] = db_query('DELETE FROM ?:user_price WHERE price = 0 AND product_id IN (?a)', $product_ids);
    fn_print_die($res);
} elseif ($mode == 'baltica_reports') {
    $params = $_REQUEST;
    $params['period'] = 'C';
    $condition['company'] = db_quote("?:orders.company_id = ?i", 45);
    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);
        $condition['timestamp'] = db_quote("(?:orders.timestamp >= ?i AND ?:orders.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    $users = db_get_fields('SELECT distinct(user_id) FROM ?:orders WHERE ?p', implode(' AND ', $condition));
    $report_data = [];
    foreach ($users as $user_id) {
        if (!$user_id) continue;
        $user_info = fn_get_user_info($user_id);
        $user_info['usergroups'] = array_filter($user_info['usergroups'], function($ug) {
            return $ug['status'] == 'A' && $ug['usergroup_id'] != '548';
        });
        $storages = db_get_fields('SELECT distinct(storage) FROM ?:storages LEFT JOIN ?:orders ON ?:orders.storage_id = ?:storages.storage_id WHERE ?p AND user_id = ?i', implode(' AND ', $condition), $user_id);
        $user_orders = db_get_row('SELECT SUM(?:orders.total) as total, count(?:orders.order_id) as amount FROM ?:orders WHERE ?p AND user_id = ?i', implode(' AND ', $condition), $user_id);
        $adres = !empty($user_info['s_address']) ? $user_info['s_address'] : $user_info['b_address'];
        $region = reset(explode(',', $adres));
        $report[] = [
            'user_id' => $user_id,
            'user' => $user_info['firstname'],
            'state' => $user_info['s_state'],
            'address' => $adres,
            'region' => $region,
            'login' => $user_info['user_login'],
            'usergroups' => db_get_field('SELECT GROUP_CONCAT(usergroup) FROM ?:usergroup_descriptions WHERE usergroup_id IN (?a)',  array_keys($user_info['usergroups'])),
            'storages' => implode(', ', $storages),
            'orders summ' => $user_orders['total'],
            'orders count' => $user_orders['amount'],
        ];
    }

    $params['filename'] = 'balt_report_' . str_replace('/', '.', $_REQUEST['time_from']) . '-' . str_replace('/', '.', $_REQUEST['time_to']) . '.csv';
    $export = fn_exim_put_csv($report, $params, '"');
    fn_get_file('var/files/'.$params['filename']);
    exit();
} elseif ($mode == 'remove_mv_hd') {
    $ticket_ids = db_get_fields('SELECT ticket_id FROM ?:helpdesk_tickets WHERE mailbox_id = 12');
    foreach ($ticket_ids as $value) {
        fn_delete_ticket($value);
    }
    fn_print_die('done', count($ticket_ids));
} elseif ($mode == 'remove_old_tickets') {
    $tickets = db_get_array('SELECT ticket_id, message_id, max(timestamp) as timestamp FROM ?:helpdesk_messages GROUP BY ticket_id ');
    $tickets = array_filter($tickets, function($t) {
        return $t['timestamp'] < 1672520400;
    });
    if ($ticket_ids = db_get_fields('SELECT ticket_id FROM ?:helpdesk_tickets WHERE ticket_id IN (?a) AND subject != ?s', array_column($tickets, 'ticket_id'), 'Служба поддержки')) {
        foreach ($ticket_ids as $value) {
            fn_delete_ticket($value);
        }
    }
    $tickets = db_get_array('SELECT ticket_id, message_id, max(timestamp) as timestamp FROM ?:helpdesk_messages GROUP BY ticket_id ');
    $tickets = array_column($tickets, 'ticket_id');
    $tickets = db_get_fields('SELECT ticket_id FROM ?:helpdesk_tickets WHERE ticket_id NOT IN (?a)', $tickets);
    foreach ($tickets as $value) {
        fn_delete_ticket($value);
    }

    fn_print_die('done', count($ticket_ids));
} elseif ($mode == 'correct_reward_points_march') {
    // Вега_Самара_0,5%_апрель
    $usergroups[1810] = [15033, 13452, 15034, 13629, 15035, 14038, 13630, 15037, 13631, 15036];
    $usergroups[2058] = [15038, 13632, 15039, 13633, 15040, 14039, 15041, 13635, 15042, 13636];

    // Вега_Самара_Розничные
    $promotions[1810][13858] = [20259, 19494, 19493, 19492];
    // Вега_Самара_Крупнооптовые_клиенты
    $promotions[1810][13360] = [19503, 19496, 19495];

    // Вега_Тольятти_Розничные клиенты
    $promotions[2058][14776] = [20261, 19499, 19498, 19497];
    // Вега_Тольятти_Крупный ОПТ
    $promotions[2058][13361] = [19502, 19501, 19500];

    $users_wo_usergroups = [];
    foreach ([1810, 2058] as $company_id) {
        //db_query('DELETE FROM ?:usergroup_links WHERE usergroup_id IN (?a)', $usergroups[$company_id]);
        $users = db_get_fields('SELECT user_id FROM ?:users WHERE company_id = ?i AND user_type = ?s', $company_id, 'C');
        foreach ($users as $user_id) {
            $usergroups_ = db_get_fields('SELECT usergroup_id FROM ?:usergroup_links WHERE user_id = ?i AND status = ?s', $user_id, 'A');
            if (count($usergroups_) < 3) $users_wo_usergroups[] = $user_id;
            foreach($usergroups_ as $ug_id) {
                if (!isset($promotions[$company_id][$ug_id])) continue;
                foreach ($promotions[$company_id][$ug_id] as $promotion_id) {
                    $promotion = fn_get_promotion_data($promotion_id);
                    $progress_condition = fn_find_promotion_condition($promotion['conditions'], 'progress_total_paid');
                    $val = fn_promotion_validate_promotion_progress($promotion['promotion_id'], $progress_condition, ['user_id' => $user_id], $promotion);
                    if ($val > $progress_condition['value']) {
                        foreach($promotion['bonuses'] as $bonus) {
                            if ($bonus['bonus'] == 'give_usergroup') {
                                $is_exist = db_get_field('SELECT user_id FROM ?:usergroup_links WHERE user_id = ?i AND usergroup_id = ?i', $user_id, $bonus['value']);
                                //fn_print_die($is_exist, $user_id, $bonus['value']);
                                if ($is_exist) continue;
                                $insert = ['user_id' => $user_id, 'usergroup_id' => $bonus['value'], 'status' => 'A'];
                                db_query('REPLACE INTO ?:usergroup_links SET ?u', $insert);
                                $user_info = fn_get_user_info($user_id);
                                $insert['user_login'] = $user_info['user_login'];
                                $insert['firstname'] = $user_info['firstname'];
                                $insert['usergroup'] = fn_get_usergroup_name($bonus['value']);
                                $insert['total_sales'] = $val;
                                $insert['promo'] = $promotion['name'];
                                $insert['vendor'] = ($company_id == 1810) ? 'вега' : 'вегаТ';
                                $result[] = $insert;
                            }
                        }
                        break;
                    }
                }
            }
        }
    }
    // $params['filename'] = 'grade_usergroups.csv';
    // $params['force_header'] = true;
    // $export = fn_exim_put_csv($result, $params, '"');
    fn_print_die(count($result), $result);
} elseif ($mode == 'correct_reward_points_march2') {
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, CART_LANGUAGE);
    $order_statuses = array_filter($order_statuses, function($v) {
        return $v['params']['grant_reward_points'] == 'Y';
    });

    $promo[19489] = ['00002322', '00000925', '00007230', '00008281', '00015478', '00003838', '00005407', '00015878', '00004129', '00016303', '00006378', '00016917', '00001572', '00010816', '00000196', '00005592', '00007423', '00015858', '00016361', '00016944', '00006410', '00004580', '00006152', '00010572'];
    $promo[4723] = ['00015207', '00015699', '00014217', '00015261', '00016615', '00005171', '00014458', '00010980', '00000032', '00014279', '00015563', '00002438', '00015124', '00016636', '00016454', '00014933', '00014550', '00006247', '00016170', '00005487', '00005105', '00016604', '00014997', '00009824', '00016760', '00010247', '00016725', '00008486', '00007540', '00016998', '00017009', '00017025', '00015525', '00016624', '00015321', '00011118', '00016575', '00016667', '00016455', '00001026', '00008956', '00016679', '00016802', '00004936', '00016730', '00015103', '00005255', '00015316', '00010975', '00010900', '00014096', '00015294', '00010278', '00005848', '00015192', '00015827', '00003226', '00016833', '00009215', '00013872', '00010788', '00000998', '00016842', '00016900', '00016712', '00011399', '00013912', '00008316', '00010813', '00015956', '00002890', '00009962', '00010407', '00014065', '00016153', '00008737', '00011401', '00016540', '00009896', '00016435', '00015219', '00015656', '00002104', '00015282', '00015935', '00016700', '00015497', '00016421', '00016567', '00016419', '00016684', '00008478', '00015762', '00005104', '00009789', '00003351', '00000695', '00014499', '00014525', '00011177', '00015592', '00016352', '00004288', '00010601', '00016798', '00014552', '00016651', '00005554', '00015413', '00015568', '00005066', '00010785', '00015674', '00015417', '00014593', '00007135', '00007465', '00016179', '00011327', '00016399', '00006467', '00005246', '00003354', '00005791', '00016343', '00005593', '00010461', '00016324', '00010827', '00015221', '00016801', '00016178', '00014503', '00010624', '00008440', '00003395', '00010664', '00014060', '00006288', '00000618', '00010755', '00009805', '00003393', '00010018', '00016442', '00016516', '00002040', '00007481', '00016566', '00005885', '00005306', '00008302', '00007408', '00016536', '00014935', '00010700', '00016510', '00016648', '00016737', '00010839', '00015315', '00015613', '00010280', '00000868', '00013834', '00016140', '00004939', '00010283', '00000881', '00014827', '00007583', '00008108', '00016512', '00008300', '00000880', '00014452', '00010798', '00015266', '00015657', '00013945', '00015927', '00011627', '00014536', '00016308', '00013961', '00005455', '00013957', '00010815', '00015504', '00015096', '00008198', '00015496', '00016778', '00015838', '00014031', '00013960', '00009545', '00013911', '00010789', '00016598', '00016822', '00005120', '00014543', '00010747', '00000569', '00009509', '00016950', '00002103', '00008021', '00016641', '00010585', '00011085', '00006461', '00004763', '00010791', '00015830', '00006054', '00005945', '00003350', '00007749', '00008371', '00015488', '00010023', '00007806', '00011183', '00010756', '00004404', '00009960', '00009832', '00016650', '00016812', '00011349', '00011134', '00011097', '00011312', '00016736', '00015226', '00010039', '00016866', '00015487', '00002537', '00016644', '00016508', '00006920', '00005003', '00009521', '00014490', '00016519', '00010099', '00016878', '00004631', '00015194', '00006337', '00016898', '00009867', '00004911', '00015383', '00011161', '00015690', '00006869', '00015516', '00006749', '00007552', '00016800', '00014540', '00014541', '00010767', '00015387', '00001203', '00005454', '00015679', '00014981', '00015185', '00009504', '00014457', '00016483', '00013953', '00016935', '00008096', '00005549'];
    foreach($promo as $promo_id => $users) {
        $promo_id = [$promo_id];
        foreach ($users as $login) {
            $user_id = db_get_field('SELECT user_id FROM ?:users WHERE user_login = ?s AND company_id IN (?a)', $login, [1810, 2058]);
            $wrong_orders = db_get_fields('SELECT order_id FROM ?:orders WHERE user_id = ?i AND timestamp > ?i AND ?p AND status IN (?a)', $user_id, 1675198800, fn_find_array_in_set($promo_id, '?:orders.promotion_ids'), array_keys($order_statuses));
            if (!empty($wrong_orders)) {
                foreach ($wrong_orders as $order_id) {
                    $order_info = fn_get_order_info($order_id);
                    $db_points = $order_info['points_info']['reward'];
                    fn_sd_change_user_points(-$db_points, $user_id, 'Корректировка за ошибочное начисление баллов по акции «Приветственный бонус НОВЫМ клиенам»', CHANGE_DUE_SUBTRACT);
                    if ($db_points) {
                        $current_value = (int) fn_get_user_additional_data(POINTS, $user_id);
                        fn_save_user_additional_data(POINTS, $current_value - $db_points, $user_id);
                    }
                    $decrease[] = [
                        'order_id' => $order_id,
                        'points to decrease' => $db_points,
                        'login' => $login,
                        'firstname' => $order_info['firstname'],
                    ];
                }
            }
        }
    }
    // $params['filename'] = 'points_to_decrease.csv';
    // $params['force_header'] = true;
    // $export = fn_exim_put_csv($decrease, $params, '"');
    fn_print_die($decrease);
} elseif ($mode == 'correct_reward_points_march3') {
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, CART_LANGUAGE);
    $grant_reward_order_statuses = array_filter($order_statuses, function($v) {
        return $v['params']['grant_reward_points'] == 'Y';
    });
    $corrected_orders = 0;
    $total_orders = $out_of_status = [];
    
    $usergroups = [13452 => 0.5, 13629 => 1, 13630 => 2, 13631 => 2.5, 14038 => 1.5, 13632 => 0.5, 13633 => 1, 13635 => 2, 13636 => 2.5, 14039 => 1.5];

    $users = db_get_array('SELECT user_id, usergroup_id FROM ?:usergroup_links WHERE usergroup_id IN (?a) AND status = ?s', array_keys($usergroups), 'A');
    $users = fn_group_array_by_key($users, 'user_id');

    foreach ($users as $user_id => &$value) {
        $ug = array_column($value, 'usergroup_id');
        $res = [];
        foreach ($ug as $ug_id) {
            $res[$ug_id] = $usergroups[$ug_id];
        }
        krsort($res);
        $value=[];
        $value['usergroup_id'] = key($res);
        $value['bonus'] = $res[$value['usergroup_id']];
    }

    foreach ($users as $user_id => $user) {
        if ($check_orders = db_get_fields('SELECT order_id FROM ?:orders WHERE user_id = ?i AND timestamp > ?i AND timestamp < ?i AND status IN (?a) AND group_id NOT IN (?a)', $user_id, 1677618000, 1677943839, array_keys($order_statuses), [17,18])) {

            $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $user_id, CHANGE_DUE_ORDER);

            $corrected_order_ids = [];

            foreach ($reward_point_changes as &$change) {
                $details = unserialize($change['reason']);
                if (!empty($details['order_id']) && $details['correction'] == 'correct_reward_points_march3') {
                    $corrected_order_ids[] = $details['order_id'];
                }
            }

            foreach ($check_orders as $order_id) {

                $order_info = fn_get_order_info($order_id);
                $db_points = &$order_info['points_info']['reward'];
                $correction = [
                    'user_id' => $order_info['user_id'],
                    'usergroup' => fn_get_usergroup_name($user['usergroup_id']),
                    'order_id' => $order_id,
                    'status' => $order_info['status'],
                    'login' => db_get_field('SELECT user_login FROM ?:users WHERE user_id = ?i', $order_info['user_id']),
                    'order_points' => $db_points ?? 0,
                    'total' => $order_info['total'],
                    'correct_points' => round($order_info['total'] * $usergroups[$user['usergroup_id']] / 100),
                ];

                if (abs($correction['order_points'] - $correction['correct_points']) > 2) {
                    $total_orders[] = $order_id;
                    if (in_array($correction['status'], array_keys($grant_reward_order_statuses))) {
                        $corrected_orders += 1;
                        if (!in_array($correction['order_id'], $corrected_order_ids)) {
                            $reason = array('order_id' => $correction['order_id'], 'to' => $correction['status'], 'correction' => 'correct_reward_points_march3');
                            fn_change_user_points($correction['correct_points'], $correction['user_id'], serialize($reason), CHANGE_DUE_ORDER, $order_info['timestamp']);
                            $corrections[] = $correction;
                        }
                    } else {
                        $out_of_status[] = $order_id;
                    }
                }
            }
        }
    }
    fn_print_die(
        'всего заказов к корректировке: '. count($total_orders), 
        'из них уже скорректировано: ' . $corrected_orders, 
        'ожидают статус ' . count($out_of_status) . ' заказов: ' . implode(', ', $out_of_status), 
        'последняя корректировка (ниже):', $corrections
    );
    $params['filename'] = 'points_to_correct1.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($corrections, $params, '"');
    fn_print_die('done');
} elseif ($mode == 'correct_reward_points_march4') {
    // // Вега_Самара_0,5%_апрель
    // $usergroups[1810] = [15033, 13452, 15034, 13629, 15035, 14038, 13630, 15037, 13631, 15036];
    // $usergroups[2058] = [15038, 13632, 15039, 13633, 15040, 14039, 15041, 13635, 15042, 13636];

    // Вега_Самара_Розничные
    $promotions[1810][13858] = [21800, 21798, 21796, 21794];
    // Вега_Самара_Крупнооптовые_клиенты
    $promotions[1810][13360] = [21806, 21804, 21802];

    // Вега_Тольятти_Розничные клиенты
    $promotions[2058][14776] = [21801, 21799, 21797, 21795];
    // Вега_Тольятти_Крупный ОПТ
    $promotions[2058][13361] = [21807, 21805, 21803];

    $user_ids = db_get_fields('SELECT user_id FROM ?:orders WHERE company_id IN (?a) AND timestamp > ?i', [1810, 2058], 1677618000);

    foreach ([1810, 2058] as $company_id) {
        $users = db_get_fields('SELECT user_id FROM ?:users WHERE company_id = ?i AND user_type = ?s AND user_id IN (?a)', $company_id, 'C', $user_ids);

        foreach ($users as $user_id) {
            $usergroups_ = db_get_fields('SELECT usergroup_id FROM ?:usergroup_links WHERE user_id = ?i AND status = ?s', $user_id, 'A');

            foreach($usergroups_ as $ug_id) {
                if (!isset($promotions[$company_id][$ug_id])) continue;

                foreach ($promotions[$company_id][$ug_id] as $promotion_id) {
                    $promotion = fn_get_promotion_data($promotion_id);
                    $progress_condition = fn_find_promotion_condition($promotion['conditions'], 'progress_total_paid');
                    $val = fn_promotion_validate_promotion_progress($promotion['promotion_id'], $progress_condition, ['user_id' => $user_id], $promotion);
                    if ($val > $progress_condition['value']) {
                        foreach($promotion['bonuses'] as $bonus) {
                            if ($bonus['bonus'] == 'give_usergroup') {
                                if (!in_array($bonus['value'], $usergroups_)) {
                                    $insert = ['user_id' => $user_id, 'usergroup_id' => $bonus['value'], 'status' => 'A'];
                                    db_query('REPLACE INTO ?:usergroup_links SET ?u', $insert);
                                    $user_info = fn_get_user_info($user_id);
                                    $insert['user_login'] = $user_info['user_login'];
                                    $insert['firstname'] = $user_info['firstname'];
                                    $insert['usergroup'] = fn_get_usergroup_name($bonus['value']);
                                    $insert['total_sales'] = $val;
                                    $insert['promo'] = $promotion['name'];
                                    $insert['vendor'] = ($company_id == 1810) ? 'вега' : 'вегаТ';
                                    
                                    $result[] = $insert;
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }
    }
    $params['filename'] = 'grade_usergroups_march.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($result, $params, '"');
    fn_print_die($result);
} elseif ($mode == 'correct_reward_points_march5') {
    $usergroups = [13858, 13360];

    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, CART_LANGUAGE);
    $grant_reward_order_statuses = array_filter($order_statuses, function($v) {
        return $v['params']['grant_reward_points'] == 'Y';
    });

    $users = db_get_fields('SELECT distinct(user_id) FROM ?:usergroup_links WHERE usergroup_id IN (?a)', $usergroups);
    // только февральские заказы
    $user_orders = db_get_hash_multi_array('SELECT order_id, user_id, total, timestamp FROM ?:orders WHERE user_id IN (?a) AND timestamp > ?i AND timestamp < ?i AND group_id NOT IN (?a) AND total > 0 AND is_parent_order = "N" ORDER BY timestamp', array('user_id','order_id'), $users, 1675198800, 1677618000, [17,18]);

    foreach ($user_orders as $user_id => $orders) {
        $order_ids = array_keys($orders);

        $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $user_id, CHANGE_DUE_ORDER);

        $corrected_order_ids = [];

        foreach ($reward_point_changes as &$change) {
            $details = unserialize($change['reason']);
            if (!empty($details['order_id']) && $details['correction'] == 'correct_reward_points_march5') {
                $corrected_order_ids[] = $details['order_id'];
            }
        }

        if (!empty($order_ids)) {
            foreach ($order_ids as $order_id) {
                $order_info = fn_get_order_info($order_id);

                $correction = [
                    'user_id' => $order_info['user_id'],
                    'order_id' => $order_id,
                    'status' => $order_info['status'],
                    'login' => db_get_field('SELECT user_login FROM ?:users WHERE user_id = ?i', $order_info['user_id']),
                    'total' => $order_info['total'],
                    'correct_points' => round($order_info['total']*0.01),
                ];

                if (!in_array(array_keys(19484, $order_info['promotions']))) {
                    $total += 1;
                    if (in_array($order_info['status'], array_keys($grant_reward_order_statuses))) {
                        $corrected_orders += 1;
                        if (!in_array($correction['order_id'], $corrected_order_ids)) {
                            $reason = array('order_id' => $correction['order_id'], 'to' => $correction['status'], 'correction' => 'correct_reward_points_march5');
                            fn_change_user_points($correction['correct_points'], $correction['user_id'], serialize($reason), CHANGE_DUE_ORDER, $order_info['timestamp']);
                            $corrections[] = $correction;
                        } else {
                            //fn_print_die($correction, $corrected_order_ids, $correction['correct_points'], $change['amount']);
                            $before_corrections[] = $correction;
                        }
                    } else {
                        $out_of_status[] = $order_id;
                    }
                } else {
                    fn_print_die('check_here', array_keys($order_info['promotions']));
                }
            }
        }
    }

    fn_print_die(
        'всего заказов к корректировке: '. $total, 
        'из них уже скорректировано: ' . $corrected_orders, 
        'ожидают статус ' . count($out_of_status) . ' заказов: ' . implode(', ', $out_of_status), 
        'текущая корректировка (ниже):', 
        $corrections, 
        'скорректировано ранее (ниже):', 
        $before_corrections
    );

    // $handled_orders = array_column($orders_wo_points, 'order_id');
    // $orders = db_get_array('SELECT order_id, company_id, user_id, total, status, promotion_ids FROM ?:orders WHERE company_id IN (?a) AND timestamp > 1675198800 AND timestamp < 1677618000 AND order_id NOT IN (?a) AND total > 0 AND is_parent_order = "N"', [1810], $handled_orders);

    $params['filename'] = "reward_points_vega_za_fevral.csv";
    $params['force_header'] = true;
    $export = fn_exim_put_csv($orders_wo_points, $params, '"');
    
    // $params['filename'] = "skipped_orders_za_fevral.csv";
    // $params['force_header'] = true;
    // $export = fn_exim_put_csv($orders, $params, '"');

    fn_print_die($orders_wo_points);
} elseif ($mode == 'correct_reward_points_march6') {
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, CART_LANGUAGE);
    $grand_points_order_statuses = array_filter($order_statuses, function($v) {
        return $v['params']['grant_reward_points'] == 'Y';
    });
    $promo[1810] = [4723, 19489];
    foreach ($promo as $company_id => $promotion_ids) {
        $orders = db_get_fields('SELECT order_id FROM ?:orders WHERE (?p) AND company_id = ?i AND timestamp > ?i AND total > 0 AND is_parent_order = "N" AND company_id = ?i', fn_find_array_in_set($promotion_ids, "promotion_ids"), $company_id, 1675198800, 1810);

        foreach ($orders as $order_id) {
            $order_info = fn_get_order_info($order_id);
            if (in_array($order_info['status'], array_keys($grand_points_order_statuses))) {
                if ($db_points = $order_info['points_info']['reward']) {
                    $is_corrected = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND reason = ?s', $order_info['user_id'], "Корректировка за ошибочное начисление баллов по акции «Приветственный бонус НОВЫМ клиенам»");
                    $first_order = db_get_field('SELECT max(order_id) FROM ?:orders WHERE order_id < ?i AND timestamp > ?i AND user_id = ?i AND total > 0 AND is_parent_order = "N" AND status IN (?a)', $order_id, 0, $order_info['user_id'], array_keys($grand_points_order_statuses));

                    if (empty($is_corrected)) {
                        if ($first_order) {
                            $promo = array_intersect_key($order_info['promotions'], array_flip($promotion_ids));
                            $correction = [
                                'user_id' => $order_info['user_id'],
                                'company_id' => $order_info['company_id'],
                                'login' => db_get_field('SELECT user_login FROM ?:users WHERE user_id = ?i', $order_info['user_id']),
                                'order_id' => $order_info['order_id'],
                                'month' => date('n', $order_info['timestamp']),
                                'points' => $db_points,
                                'earlier_order_id' => $first_order,
                                'promotion_id' => key($promo),
                                'promotion' => reset($promo)['name'],
                            ];
                            if ($correction['month'] == '3') {
                                $correction['points'] = $db_points -= round($order_info['total'] * 0.01);
                            }
                            fn_change_user_points(-$db_points, $order_info['user_id'], 'Корректировка за ошибочное начисление баллов по акции «Приветственный бонус НОВЫМ клиенам»', CHANGE_DUE_SUBTRACT);
                            $corrections[] = $correction;
                        }
                    }
                }
            }
        }
    }
    fn_print_r($corrections);
    $params['filename'] = 'points_to_decrease_march6.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($corrections, $params, '"');
    die();
} elseif ($mode == 'correct_reward_points_march7') {
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, CART_LANGUAGE);
    $grand_points_order_statuses = array_filter($order_statuses, function($v) {
        return $v['params']['grant_reward_points'] == 'Y';
    });
    $promo = [5860];
    // $orders = db_get_fields('SELECT order_id FROM ?:orders WHERE (?p) AND timestamp > ?i AND total > 0 AND is_parent_order = "N" AND status IN (?a) AND group_id NOT IN (?a)', fn_find_array_in_set($promo, "promotion_ids"), 1675198800, array_keys($grand_points_order_statuses), [17,18]);
    // foreach ($orders as $order_id) {
    //     $order = fn_get_order_info($order_id);
    //     $is_corrected = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND reason = ?s', $order['user_id'], "Корректировка за ошибочное начисление баллов по акции «Приветственный бонус НОВЫМ клиенам»");
    //     if ($is_corrected) continue;
    //     $correction = [
    //         'order_id' => $order_id,
    //         'company_id' => $order['company_id'],
    //         'user_id' => $order['user_id'],
    //         'order_reward' => $order['points_info']['reward'],
    //         'calculated_reward' => round($order['total']/10),
    //         'is_different' => (abs($order['points_info']['reward'] - round($order['total']/10)) > 2) ? 'da' : 'ne',
    //         'has_first' => db_get_field('SELECT max(order_id) FROM ?:orders WHERE order_id < ?i AND timestamp > ?i AND user_id = ?i AND total > 0 AND is_parent_order = "N" AND status IN (?a)', $order_id, 0, $order['user_id'], array_keys($grand_points_order_statuses)) ? 'da' : 'ne',
    //     ];
    //     if ($correction['is_different'] != 'ne') continue;
    //     if ($correction['has_first'] != 'da') continue;
    //     $not_corrected_orders[] = $correction;
    // }

    // $export = $not_corrected_orders;
    // $params['filename'] = 'not_corrected_orders_march7.csv';
    // $params['force_header'] = true;
    // $export = fn_exim_put_csv($export, $params, '"');
    // fn_print_die($not_corrected_orders);

    // 19490 дает 1%. нефиг ее корректировать
    $promo_[2058] = [4723, 5860];
    foreach ($promo_ as $company_id => $promotion_ids) {
        $orders = db_get_fields('SELECT order_id FROM ?:orders WHERE (?p) AND company_id = ?i AND timestamp > ?i AND total > 0 AND is_parent_order = "N" AND group_id NOT IN (?a)', fn_find_array_in_set($promotion_ids, "promotion_ids"), $company_id, 1675198800, [17,18]);

        foreach ($orders as $order_id) {
            $order_info = fn_get_order_info($order_id);
            if (in_array($order_info['status'], array_keys($grand_points_order_statuses))) {
                if ($db_points = $order_info['points_info']['reward']) {

                    $is_corrected = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND reason = ?s', $order_info['user_id'], "Корректировка за ошибочное начисление баллов по акции «Приветственный бонус НОВЫМ клиенам»");
                    $first_order = db_get_field('SELECT max(order_id) FROM ?:orders WHERE order_id < ?i AND timestamp > ?i AND user_id = ?i AND total > 0 AND is_parent_order = "N" AND status IN (?a)', $order_id, 0, $order_info['user_id'], array_keys($grand_points_order_statuses));

                    if (empty($is_corrected)) {
                        if ($first_order) {
                            $promot = array_intersect_key($order_info['promotions'], array_flip($promotion_ids));
                            $correction = [
                                'user_id' => $order_info['user_id'],
                                'company_id' => $order_info['company_id'],
                                'login' => db_get_field('SELECT user_login FROM ?:users WHERE user_id = ?i', $order_info['user_id']),
                                'order_id' => $order_info['order_id'],
                                'month' => date('n', $order_info['timestamp']),
                                'order_reward' => $order_info['points_info']['reward'],
                                'calculated_reward' => round($order_info['total']/100),
                                'is_different' => (abs($order_info['points_info']['reward'] - round($order_info['total']/100)) > 2) ? 'da' : 'ne',
                                'diff' => abs($order_info['points_info']['reward'] - round($order_info['total']/100)),
                                'earlier_order_id' => $first_order,
                                'promotion_id' => key($promot),
                                'promotion' => reset($promot)['name'],
                            ];
                            if ($correction['is_different'] == 'da') {
                                $corrections[] = $correction;
                                fn_change_user_points(-$correction['diff'], $order_info['user_id'], 'Корректировка за ошибочное начисление баллов по акции «Приветственный бонус НОВЫМ клиенам»', CHANGE_DUE_SUBTRACT);
                            }

                        }
                    }
                }
            }
        }
    }
    $params['filename'] = 'correct_reward_points_march7.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($corrections, $params, '"');
    fn_print_die(count($corrections), $corrections);
} elseif ($mode == 'correct_reward_points_march8') {
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, CART_LANGUAGE);
    $grand_points_order_statuses = array_filter($order_statuses, function($v) {
        return $v['params']['grant_reward_points'] == 'Y';
    });

    $orders = db_get_fields('SELECT order_id FROM ?:orders WHERE (?p) AND timestamp > ?i AND total > 0 AND is_parent_order = "N" AND group_id NOT IN (?a)', fn_find_array_in_set([19484], "promotion_ids"), 1675198800, [17,18]);
    foreach ($orders as $order_id) {
        $order_info = fn_get_order_info($order_id);
        if (in_array($order_info['status'], array_keys($grand_points_order_statuses))) {
            if ($db_points = $order_info['points_info']['reward']) {
                $correction = [
                    'user_id' => $order_info['user_id'],
                    'company_id' => $order_info['company_id'],
                    'login' => db_get_field('SELECT user_login FROM ?:users WHERE user_id = ?i', $order_info['user_id']),
                    'order_id' => $order_info['order_id'],
                    'order_reward' => $db_points,
                    'calculated_reward' => round($order_info['total']/100),
                    'is_different' => (abs($db_points - round($order_info['total']/100)) > 2) ? 'da' : 'ne',
                    'diff' => abs($db_points - round($order_info['total']/100)),
                    // '10' => 'ne',
                    // '1' => 'ne',
                ];

                if ($correction['is_different'] == 'da') {
                    $corrections[] = $correction;
                    // $reason = array('order_id' => $correction['order_id'], 'to' => $order_info['status'], 'correction' => 'correct_reward_points_march8');
                    fn_change_user_points(-$correction['order_reward'], $correction['user_id'], 'Корректировка за ошибочное начисление баллов по акции «Приветственный бонус НОВЫМ клиенам»', CHANGE_DUE_SUBTRACT, $order_info['timestamp']);
                }
            }
        }
    }
    $params['filename'] = 'correct_reward_points_march8.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($corrections, $params, '"');
    fn_print_die(count($corrections), $corrections);
} elseif ($mode == 'correct_reward_points_march9') {
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, CART_LANGUAGE);
    $grand_points_order_statuses = array_filter($order_statuses, function($v) {
        return $v['params']['grant_reward_points'] == 'Y';
    });

    $orders = db_get_fields('SELECT order_id FROM ?:orders WHERE (?p) AND timestamp > ?i AND total > 0 AND is_parent_order = "N" AND group_id NOT IN (?a)', fn_find_array_in_set([19484], "promotion_ids"), 1675198800, [17,18]);
    foreach ($orders as $order_id) {
        $order_info = fn_get_order_info($order_id);
        if (in_array($order_info['status'], array_keys($grand_points_order_statuses))) {
            if ($db_points = $order_info['points_info']['reward']) {
                $correction = [
                    'user_id' => $order_info['user_id'],
                    'company_id' => $order_info['company_id'],
                    'login' => db_get_field('SELECT user_login FROM ?:users WHERE user_id = ?i', $order_info['user_id']),
                    'order_id' => $order_info['order_id'],
                    'order_reward' => $db_points,
                    'calculated_reward' => round($order_info['total']/100),
                    'is_different' => (abs($db_points - round($order_info['total']/100)) > 2) ? 'da' : 'ne',
                    'diff' => abs($db_points - round($order_info['total']/100)),
                ];

                if ($correction['is_different'] == 'da') {
                    $changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND amount = ?i AND reason = ?s ORDER BY change_id DESC', $order_info['user_id'], -$correction['order_reward'], 'Корректировка за ошибочное начисление баллов по акции «Приветственный бонус НОВЫМ клиенам»');

                    if (count($changes) > 1) {
                        $wrong_correction = array_shift($changes);
                        $corrections[] = $correction;
                        $current_value = (int) fn_get_user_additional_data(POINTS, $wrong_correction['user_id']);
                        fn_save_user_additional_data(POINTS, $current_value - $wrong_correction['amount'], $wrong_correction['user_id']);
                        db_query("DELETE FROM ?:reward_point_changes WHERE change_id = ?i", $wrong_correction['change_id']);
                    }
                }
            }
        }
    }
    $params['filename'] = 'correct_reward_points_march9.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($corrections, $params, '"');
    fn_print_die(count($corrections), $corrections);
} elseif ($mode == 'baltica_logs') {
    list($logs, $search) = fn_get_logs(['q_type' => 'users', 'q_action' => 'delete']);
    $report = [];
    foreach ($logs as $log) {
        list($user, $extra) = explode(';', $log['content']['user']);
        $extra = trim(preg_replace('/\((.+?)\)/i', '', $extra));
        $report[] = [
            'user' => $user,
            'extra' => $extra,
            'user_id' => $log['content']['id'],
            'date' => fn_date_format($log['timestamp'], Registry::get('settings.Appearance.date_format'))
        ];
    }
    $params['filename'] = "deleted_users.csv";
    $params['force_header'] = true;
    $export = fn_exim_put_csv($report, $params, '"');
    fn_print_die($report);
} elseif ($mode == 'extract_session') {
    if ($s_id = $_REQUEST['session_id']) {
        $val = db_get_field('SELECT data FROM ?:sessions WHERE session_id = ?s', $s_id);
        $session = Tygh::$app['session']->decode($val);
        fn_print_die($session);
    }
    fn_print_die();
} elseif ($mode == 'decode_ip') {
    if ($ip = $_REQUEST['ip']) {
        fn_print_die(fn_ip_from_db($ip));
    }
    fn_print_die();
} elseif ($mode == 'encode_ip') {
    if ($ip = $_REQUEST['ip']) {
        fn_print_die(fn_ip_to_db($ip));
    }
    fn_print_die();
} if ($mode == 'speedup_store') {
    // почистим также купленные товары.
    $res = db_query("truncate table ?:also_bought_products");
    fn_print_r('почистим также купленные товары', $res);

    // очистим сессии старше месяца
    $res = db_query('DELETE FROM ?:sessions WHERE expiry < ?i', TIME + 28944000);
    db_query('UPDATE ?:sessions SET expiry = expiry - 28944000');// SECONDS_IN_DAY * 335
    fn_print_r('очистим сессии старше месяца', $res);

    // очистим корзины для незарегистрированных
    $res = db_quote("DELETE FROM ?:user_session_products WHERE user_type = 'U' AND timestamp < ?i", TIME - (SECONDS_IN_DAY * 10));
    fn_print_die($res);
    fn_print_r('очистим корзины для незарегистрированных', $res);

    // очистим тяжелые вишлисты
    $user_ids = db_get_fields("SELECT user_id FROM ?:user_session_products WHERE type = 'W' GROUP BY user_id HAVING count(product_id) > 60");
    $res = db_query("DELETE FROM ?:user_session_products WHERE type = 'W' AND user_id IN (?a)", $user_ids);
    fn_print_r('очистим тяжелые вишлисты', count($user_ids), $res);

    fn_print_die('stop');
} if ($mode == 'correct_reward_points_may') {
    $company_id = [1810, 2058];
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, CART_LANGUAGE);
    $grant_reward_points_statuses = array_filter($order_statuses, function($v) {
        return $v['params']['grant_reward_points'] == 'Y';
    });

    //$orders = db_get_fields('SELECT order_id FROM ?:orders WHERE company_id IN (?a) AND parent_order_id != ?i AND timestamp > 1677618000 AND subtotal > 0 AND order_id = 342934', $company_id, 0);
    $orders = db_get_fields('SELECT order_id FROM ?:orders WHERE company_id IN (?a) AND parent_order_id != ?i AND timestamp > 1677618000 AND subtotal > 0', $company_id, 0);
    $uncorrected_orders = [];
    foreach ($orders as $order_id) {
        $order_info = fn_get_order_info($order_id);
        if (! in_array($order_info['status'], array_keys($grant_reward_points_statuses))) continue;

        if (empty($order_info['points_info']['reward'])) {
            $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $order_info['user_id'], CHANGE_DUE_ORDER);

            $is_corrected = false;
            foreach ($reward_point_changes as $change) {
                $details = unserialize($change['reason']);

                if (!empty($details['order_id']) && $details['order_id'] == $order_id && strpos($details['correction'], 'correct_reward_points') !== false) {
                    $is_corrected = true;
                    break;
                }
            }

            if (!$is_corrected) {
                // if ($promotions = db_get_field('SELECT promotions FROM ?:orders WHERE order_id = ?i', $order_info['parent_order_id'])) {
                //     $promotions = unserialize($promotions);
                //     $promotions = array_filter($promotions, function($v) {
                //         return isset($v['bonuses']['give_percent_points']);
                //     });
                //     if (!empty($promotions)) {
                //         $max = 0;
                //         foreach ($promotions as $promo) {
                //             $max = max($max, $promo['bonuses']['give_percent_points']['value']);
                //         }
                //         $correction = [
                //             'user_id' => $order_info['user_id'],
                //             'order_id' => $order_id,
                //             'status' => $order_info['status'],
                //             'login' => db_get_field('SELECT user_login FROM ?:users WHERE user_id = ?i', $order_info['user_id']),
                //             'total' => $order_info['total'],
                //             'correct_points' => round($order_info['total']*$max/100),
                //         ];
                //         $corrections[] = $correction;
                //     }
                // }
                fn_print_r($order_id);
            }
        }
    }

    $params['filename'] = 'correct_reward_points_may.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($corrections, $params, '"');
    fn_print_die(count($corrections), $corrections);
} if ($mode == 'correct_reward_points_may2') {
    // Промо апреля Начисление ЮГ Самара на май:

    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24977 апрель круп 80к-1%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24978 апрель круп 150к-1.5%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24979 апрель круп 250к-2%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24980 апрель розн 0к-0.5%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24982 апрель розн 20к-1%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24983 апрель розн 50к-2%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24984 апрель розн 80к-2.5%

    // Промо апреля Начисление ЮГ Тольятти на май:
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24994 апрель круп 80к-1%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24995 апрель круп 150к-1.5%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24996 апрель круп 250к-2%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24990 апрель розн 0к-0.5%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24991 апрель розн 20к-1%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24992 апрель розн 50к-2%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=24993 апрель розн 80к-2.5%
 
    // Вега_Самара_Розничные
    $promotions[1810][13858] = [24984, 24983, 24982, 24980];
    // Вега_Самара_Крупнооптовые_клиенты
    $promotions[1810][13360] = [24979, 24978, 24977];

    // Вега_Тольятти_Розничные клиенты
    $promotions[2058][14776] = [24993, 24992, 24991, 24990];
    // Вега_Тольятти_Крупный ОПТ
    $promotions[2058][13361] = [24996, 24994, 24995];

    // 1 апреля - 1680296400, 1 мая 1682888400
    $user_ids = db_get_fields('SELECT user_id FROM ?:orders WHERE company_id IN (?a) AND timestamp >= ?i AND timestamp < ?i', [1810, 2058], 1680296400, 1682888400);

    foreach ([1810, 2058] as $company_id) {
        $users = db_get_fields('SELECT user_id FROM ?:users WHERE company_id = ?i AND user_type = ?s AND user_id IN (?a)', $company_id, 'C', $user_ids);

        foreach ($users as $user_id) {
            $usergroups_ = db_get_fields('SELECT usergroup_id FROM ?:usergroup_links WHERE user_id = ?i AND status = ?s', $user_id, 'A');

            foreach($usergroups_ as $ug_id) {
                if (!isset($promotions[$company_id][$ug_id])) continue;

                foreach ($promotions[$company_id][$ug_id] as $promotion_id) {
                    $promotion = fn_get_promotion_data($promotion_id);
                    $progress_condition = fn_find_promotion_condition($promotion['conditions'], 'progress_total_paid');
                    $val = fn_promotion_validate_promotion_progress($promotion['promotion_id'], $progress_condition, ['user_id' => $user_id], $promotion);
                    if ($val > $progress_condition['value']) {
                        foreach($promotion['bonuses'] as $bonus) {
                            if ($bonus['bonus'] == 'give_usergroup') {
                                if (!in_array($bonus['value'], $usergroups_)) {
                                    $insert = ['user_id' => $user_id, 'usergroup_id' => $bonus['value'], 'status' => 'A'];
                                    db_query('REPLACE INTO ?:usergroup_links SET ?u', $insert);
                                    $user_info = fn_get_user_info($user_id);
                                    $insert['user_login'] = $user_info['user_login'];
                                    $insert['firstname'] = $user_info['firstname'];
                                    $insert['usergroup'] = fn_get_usergroup_name($bonus['value']);
                                    $insert['total_sales'] = $val;
                                    $insert['promo'] = $promotion['name'];
                                    $insert['vendor'] = ($company_id == 1810) ? 'вега' : 'вегаТ';
                                    $insert['user_type'] = fn_get_usergroup_name($ug_id);

                                    $result[] = $insert;
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }
    }

    $params['filename'] = 'grade_usergroups_april.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($result, $params, '"');
    fn_print_die($result);
} if ($mode == 'correct_reward_points_may3') {
    // Промо мая, Начисление ЮГ Самара июнь:

    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30593 май круп 80к-1%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30594 май круп 150к-1.5%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30595 май круп 250к-2%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30585 май розн 0к-0.5%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30586 май розн 20к-1%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30587 май розн 50к-2%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30588 май розн 80к-2.5%

    // Промо мая, Начисление ЮГ Тольятти июнь:

    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30596 май круп 80к-1%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30597 май круп 150к-1.5%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30598 май круп 250к-2%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30589 май розн 0к-0.5%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30590 май розн 20к-1%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30591 май розн 50к-2%
    // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=30592 май розн 80к-2.5%
 
    // Вега_Самара_Розничные
    $promotions[1810][13858] = [30588, 30587, 30586, 30585];
    // Вега_Самара_Крупнооптовые_клиенты
    $promotions[1810][13360] = [30595, 30594, 30593];

    // Вега_Тольятти_Розничные клиенты
    $promotions[2058][14776] = [30592, 30591, 30590, 30589];
    // Вега_Тольятти_Крупный ОПТ
    $promotions[2058][13361] = [30598, 30597, 30596];

    // 1 мая - 1682888400, 1 июня 1685566800
    $user_ids = db_get_fields('SELECT user_id FROM ?:orders WHERE company_id IN (?a) AND timestamp >= ?i AND timestamp < ?i', [1810, 2058], 1682888400, 1685566800);

    foreach ([1810, 2058] as $company_id) {
        $users = db_get_fields('SELECT user_id FROM ?:users WHERE company_id = ?i AND user_type = ?s AND user_id IN (?a)', $company_id, 'C', $user_ids);

        foreach ($users as $user_id) {
            $usergroups_ = db_get_fields('SELECT usergroup_id FROM ?:usergroup_links WHERE user_id = ?i AND status = ?s', $user_id, 'A');

            foreach($usergroups_ as $ug_id) {
                if (!isset($promotions[$company_id][$ug_id])) continue;

                foreach ($promotions[$company_id][$ug_id] as $promotion_id) {
                    $promotion = fn_get_promotion_data($promotion_id);
                    $progress_condition = fn_find_promotion_condition($promotion['conditions'], 'progress_total_paid');
                    $val = fn_promotion_validate_promotion_progress($promotion['promotion_id'], $progress_condition, ['user_id' => $user_id], $promotion);
                    if ($val > $progress_condition['value']) {
                        foreach($promotion['bonuses'] as $bonus) {
                            if ($bonus['bonus'] == 'give_usergroup') {
                                if (!in_array($bonus['value'], $usergroups_)) {
                                    $insert = ['user_id' => $user_id, 'usergroup_id' => $bonus['value'], 'status' => 'A'];
                                    db_query('REPLACE INTO ?:usergroup_links SET ?u', $insert);
                                    $user_info = fn_get_user_info($user_id);
                                    $insert['user_login'] = $user_info['user_login'];
                                    $insert['firstname'] = $user_info['firstname'];
                                    $insert['usergroup'] = fn_get_usergroup_name($bonus['value']);
                                    $insert['total_sales'] = $val;
                                    $insert['promo'] = $promotion['name'];
                                    $insert['vendor'] = ($company_id == 1810) ? 'вега' : 'вегаТ';
                                    $insert['user_type'] = fn_get_usergroup_name($ug_id);

                                    $result[] = $insert;
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }
    }

    $params['filename'] = 'grade_usergroups_may.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($result, $params, '"');
    fn_print_die($result);
} elseif ($mode == 'correct_reward_points_may4') {
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, CART_LANGUAGE);
    $grant_reward_order_statuses = array_filter($order_statuses, function($v) {
        return $v['params']['grant_reward_points'] == 'Y';
    });
    $corrected_orders = 0;
    $total_orders = $out_of_status = [];
    
    $usergroups = [15918 => 0.5, 15919 => 1, 15920 => 1.5, 15921 => 2, 15922 => 2.5, 15923 => 0.5, 15924 => 1, 15925 => 1.5, 15926 => 2, 15927 => 2.5];

    $users = db_get_array('SELECT user_id, usergroup_id FROM ?:usergroup_links WHERE usergroup_id IN (?a) AND status = ?s', array_keys($usergroups), 'A');
    $users = fn_group_array_by_key($users, 'user_id');

    foreach ($users as $user_id => &$value) {
        $ug = array_column($value, 'usergroup_id');
        $res = [];
        foreach ($ug as $ug_id) {
            $res[$ug_id] = $usergroups[$ug_id];
        }
        krsort($res);
        $value=[];
        $value['usergroup_id'] = key($res);
        $value['bonus'] = $res[$value['usergroup_id']];
    }

    foreach ($users as $user_id => $user) {
        // 1 мая - 1682888400, 1 июня 1685566800
        if ($check_orders = db_get_fields('SELECT order_id FROM ?:orders WHERE user_id = ?i AND timestamp >= ?i AND timestamp < ?i AND status IN (?a) AND group_id NOT IN (?a)', $user_id, 1682888400, 1685566800, array_keys($order_statuses), [17,18])) {

            $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i', $user_id);

            foreach ($reward_point_changes as &$change) {
                $details = unserialize($change['reason']);
                $details = unserialize($change['reason']);
                if (!empty($details['order_id']) && $details['correction'] == 'correct_reward_points_may4') {
                    $corrected_order_ids[] = $details['order_id'];
                }

                if (!empty($details['order_id']) && in_array($details['order_id'], $check_orders)) {
                    $points_movement[$details['order_id']] += $change['amount'];
                }
            }

            foreach ($check_orders as $order_id) {

                $order_info = fn_get_order_info($order_id);
                $db_points = &$order_info['points_info']['reward'];

                $correction = [
                    'user_id' => $order_info['user_id'],
                    'usergroup' => fn_get_usergroup_name($user['usergroup_id']),
                    'order_id' => $order_id,
                    'status' => $order_info['status'],
                    'login' => db_get_field('SELECT user_login FROM ?:users WHERE user_id = ?i', $order_info['user_id']),
                    'order_points' => $points_movement[$order_id] ?? 0,
                    'total' => $order_info['total'],
                    'correct_points' => round($order_info['total'] * $usergroups[$user['usergroup_id']] / 100),
                ];

                if (abs($correction['order_points'] - $correction['correct_points']) > 2) {
                    $total_orders[] = $order_id;
                    if (in_array($correction['status'], array_keys($grant_reward_order_statuses))) {
                        $corrected_orders += 1;
                        if (!in_array($correction['order_id'], $corrected_order_ids)) {
                            $reason = array('order_id' => $correction['order_id'], 'to' => $correction['status'], 'correction' => 'correct_reward_points_may4');
                            fn_change_user_points($correction['correct_points'] - $correction['order_points'], $correction['user_id'], serialize($reason), CHANGE_DUE_ORDER, $order_info['timestamp']);
                            $corrections[] = $correction;
                        }
                    } else {
                        $out_of_status[] = $order_id;
                    }
                }
            }
        }
    }

    fn_print_r(
        'всего заказов к корректировке: '. count($total_orders), 
        'из них уже скорректировано: ' . $corrected_orders, 
        'ожидают статус ' . count($out_of_status) . ' заказов: ' . implode(', ', $out_of_status), 
        'последняя корректировка (ниже):', $corrections
    );
    $params['filename'] = 'points_to_correct_may.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($corrections, $params, '"');
    fn_print_die('done');
} elseif ($mode == 'delete_draftmaster_users') {
    $ids = db_get_fields('SELECT user_id FROM ?:users WHERE company_id = ?i AND user_type = ?s AND user_id NOT IN (?a)', 2059, 'C', [199999]);

    db_query('DELETE FROM ?:user_session_products WHERE user_id IN (?a) AND user_type = ?s', $ids, 'R');
    db_query('DELETE FROM ?:user_price WHERE user_id IN (?a)', $ids);
    db_query('DELETE FROM ?:user_data WHERE user_id IN (?a)', $ids);
    db_query('DELETE FROM ?:user_profiles WHERE user_id IN (?a)', $ids);
    db_query('DELETE FROM ?:user_storages WHERE user_id IN (?a)', $ids);
    db_query('DELETE FROM ?:usergroup_links WHERE user_id IN (?a)', $ids);
    db_query('DELETE FROM ?:users WHERE user_id IN (?a)', $ids);
    fn_print_die('end');
} elseif ($mode == 'baltica_maintenance5') {
    $users = db_get_fields('SELECT user_id FROM ?:users WHERE company_id = ?i AND user_type = ?s', 45, 'C');
    db_query('DELETE FROM ?:user_data WHERE user_id IN (?a) AND type = ?s', $users, 'W');
    db_query('DELETE FROM ?:reward_point_changes WHERE user_id IN (?a)', $users);

    fn_print_die('end');
} elseif ($mode == 'correct_reward_points_june') {
    $company_ids = [1810, 2058];
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, CART_LANGUAGE);
    $grant_reward_order_statuses = array_filter($order_statuses, function($v) {
        return $v['params']['grant_reward_points'] == 'Y';
    });
    $grant_reward_order_statuses = array_keys($grant_reward_order_statuses);

    $usergroups = ['17612', '17613', '17614', '17615', '17616',    '17617', '17618', '17619', '17620', '17621'];
    $users = db_get_fields('SELECT distinct(user_id) FROM ?:usergroup_links WHERE usergroup_id IN (?a)', $usergroups);

    foreach ($company_ids as $company_id) {
        list($orders, $params) = fn_get_orders([
            'time_to' => '01/06/2023', 
            'time_from' => '30/06/2023',
            'period' => 'M',
            'company_id' => $company_id, 
            //'status' => $grant_reward_order_statuses
        ]);

        foreach ($orders as $order) {
            $order_id = $order['order_id'];
            $order_info = fn_get_order_info($order_id);
            if (!$order_info['group_id']) continue;
            if (!empty($order_info['points_info']['reward']) && !empty($order_info['points_info']['in_use']['points']) ) {
                if (in_array($order_info['status'], $grant_reward_order_statuses)) {
                    $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $order_info['user_id'], CHANGE_DUE_ORDER);
                    $is_corrected = false;
                    $granted = 0;
                    foreach ($reward_point_changes as $change) {
                        $details = unserialize($change['reason']);
                        if (!empty($details['order_id']) && $details['order_id'] == $order_id ) {
                            if ($change['amount'] > 0) $granted = $change['amount'];
                            if (strpos($details['correction'], 'correct_reward_points_june') !== false) {
                                $is_corrected = $change;
                                break;
                            }
                        } else {
                            $details = [];
                        }
                    }

                    if (!$is_corrected && $granted) {
                        $reason = array('order_id' => $order_info['order_id'], 'to' => $order_info['status'], 'correction' => 'correct_reward_points_june');
                        fn_change_user_points(-$granted, $order_info['user_id'], serialize($reason), CHANGE_DUE_ORDER, $order_info['timestamp']);
                        $corrections[] = $reason + ['user_id' => $order_info['user_id'], 'amount' => $granted, 'total' => $order_info['total'], 'subtotal' => $order_info['subtotal'], 'group_id' => $order_info['group_id'], 'status' => $order_info['status']];
                    } elseif ($is_corrected) {
                        $reason = array('order_id' => $order_info['order_id'], 'to' => $order_info['status'], 'correction' => 'correct_reward_points_june');
                        $prev_corrections[] = $reason + ['user_id' => $order_info['user_id'], 'amount' => $granted];
                    }
                } else {
                    // грохнуть из заказа данные пока не начислялись баллы, чтобы не запускать обработку по ним потом
                    $removed[] = $order_id;
                    db_query('DELETE FROM ?:order_data WHERE order_id = ?i AND type = ?s', $order_id, 'W');
                }
            }

            // if ($order_info['total'] <= 0) continue;
            // if (!in_array($order_info['user_id'], $users)) continue;
            // fn_print_die($order_info);
        }
    }
    fn_print_r($removed);
    fn_print_r(
        'всего заказов к корректировке: '. count($corrections), 
        'из них уже скорректировано: ' . count($prev_corrections), 
        'последняя корректировка (ниже):', $corrections
    );
    $params['filename'] = 'correct_reward_points_june.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($corrections, $params, '"');
    fn_print_die('done');
} elseif ($mode == 'correct_reward_points_june2') {
    $product_ids = [179166,178598,176731,176730,176704,176703,176702,174783,174782,170420,170419,170066];
    $order_ids = db_get_fields("SELECT o.order_id FROM ?:orders AS o LEFT JOIN cscart_order_details AS od ON o.order_id = od.order_id WHERE od.product_id IN (?a) AND o.is_parent_order = ?s", $product_ids, 'N');

    $wrong = [];
    foreach ($order_ids as $order_id) {
        if ($order_id == '297266') continue;
        $order_info = fn_get_order_info($order_id);
        if (empty($order_info['points_info']['in_use']['points'])) continue;

        $products = array_filter($order_info['products'], function($v) use ($product_ids) {
            return in_array($v['product_id'], $product_ids);
        });
        fn_print_die($products);
        foreach ($products as $product) {
            $row[] = [
                'order_id' => $order_info['order_id'],
                'status' => $order_info['status'],
                'email' => $order_info['email'],
                'date' => fn_date_format($order_info['timestamp'], Registry::get('settings.Appearance.date_format')),
                'order_points_in_use' => $order_info['points_info']['in_use']['points'],
                'product' => $product['product'],
                'product_code' => $product['product_code'],
                'price incl discount' => $product['base_price'] - $product['discount'],
                'amount' => $product['amount'],
                'row_total' => $product['amount'] * ($product['base_price'] - $product['discount']),
            ];
        }
    }

    $params['filename'] = 'correct_reward_points_june2.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($row, $params, '"');
    fn_print_die($row);
} elseif ($mode == 'cleanup_wishlist') {
    $res = db_query('DELETE FROM ?:user_session_products WHERE product_id NOT IN (SELECT product_id FROM ?:products)');

    $iteration = empty($action) ? 1 : $action;
    $step = 100;
    $limit = ' LIMIT '. ($iteration - 1) * $step . ', ' . $step;
    $user_ids = db_get_fields("SELECT distinct(user_id) FROM ?:user_session_products WHERE user_type = ?s AND type = ?s $limit", 'R', 'W');
    if (empty($user_ids)) fn_print_die('done');

    foreach ($user_ids as $user_id) {
        $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $user_id);
        $customer_auth = fn_fill_auth($_data, array(), false, 'C');
        $usergroup_ids = !empty($customer_auth['usergroup_ids']) ? $customer_auth['usergroup_ids'] : array();
        $product_ids = db_get_fields('SELECT distinct(product_id) FROM ?:user_session_products WHERE user_id = ?i', $user_id);
        $avail_product_ids = db_get_fields('SELECT product_id FROM ?:products WHERE product_id IN (?a) AND (' . fn_find_array_in_set($usergroup_ids, "?:products.usergroup_ids", true) . ')', $product_ids);
        if ($diff = array_diff($product_ids, $avail_product_ids)) {
            $res = db_query('DELETE FROM ?:user_session_products WHERE product_id IN (?a) AND user_id = ?i', $diff, $user_id);
            $cnt += 1;
        }
    }

    fn_print_r($iteration);
    $iteration += 1;
    fn_redirect('tools.cleanup_wishlist.' . $iteration);
} elseif ($mode == 'check_reward_points') {
    $exclude_users = ['191070000000569','191090000000483','191090000001320','1911000758','1911006811','191120000000399','294520000098680','294520000143276','294520000274914','294520000348996','294520000785565','294520000872817','294520003799303','294520800833206','298790000830905','298790000907354','298790000925283','298790001077850','298790001194277'];
    $exclude_users = db_get_fields('SELECT user_id FROM ?:users WHERE user_login IN (?a) AND company_id = ?i', $exclude_users, 45);
    $db_orders = db_get_array('SELECT order_id, user_id FROM ?:orders WHERE company_id = 45 AND timestamp < ?i AND timestamp > ?i AND user_id NOT IN (?a)', 1688331600, 1682888400, $exclude_users);

    $user_orders = $started = $not_started = [];
    foreach ($db_orders as $data) {
        $user_orders[$data['user_id']][] = $data['order_id'];
    }

    foreach ($user_orders as $user_id => $orders) {
        $flag = 'not_started';
        $tmp = false;
        if ($reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i', $user_id)) {
            foreach ($reward_point_changes as $change) {
                if ($change['action'] == CHANGE_DUE_ORDER_PLACE) $flag = 'started';
                $details = unserialize($change['reason']);

                if (isset($details['order_id']) && in_array($details['order_id'], $orders)) {
                    $tmp = true;
                    $check[$user_id][] = $details['order_id'];
                }
            }
        }
        if ($tmp) $$flag[] = $user_id;
    }

    foreach ($check as $user_id => $orders) {
        if (!in_array($user_id, $not_started)) continue;

        $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i', $user_id);
        foreach ($reward_point_changes as $change) {
            $details = unserialize($change['reason']);
            if (isset($details['order_id']) && in_array($details['order_id'], $orders)) {
                // revert change
                fn_revert_reward_points_change($change, true);
                $change['order_id'] = $details['order_id'];
                unset($change['reason']);
                $corrections[] = $change;
            }
        }
    }

    
    $params['filename'] = 'correct_reward_points_august.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($corrections, $params, '"');

    fn_print_die('done');
} elseif ($mode == 'change_balt_order_statuses') {
    // сначала убери в reward_points/func.php text_order_status_has_not_been_changed
    if (!$action) $action = 1;
    $safe_update_status_data = function($params) {
        $status_data = fn_get_status_data($params['status'], 'O');
        $old_status_id = $status_data['status_id'];
        if ($temp_id = fn_get_status_id($params['temp_status'], 'O')) {
            $status_data['status_id'] = $temp_id;
        } else {
            unset($status_data['status_id']);
        }

        $status_data['status'] = $params['temp_status'];
        $status_data['description'] .= ' временный';

        $temp_status = fn_update_status((empty($status_data['status_id']) ? null : 'S'), $status_data, 'O');

        list($orders, ) = fn_get_orders([
            'time_from' => $params['time_from'],
            'time_to' => $params['time_to'],
            'period' => 'C',
            'company_id' => $params['company_id'],
            'status' => array($params['status'])
        ]);

        $orders = array_column($orders, 'order_id');

        if (in_array('inventory', array_keys($params['update_data']))) {
            // wasted points
            $orders = db_get_fields('SELECT order_id FROM ?:order_data WHERE type = ?s AND order_id IN (?a)', 'I', $orders);
        } elseif (in_array('grant_reward_points', array_keys($params['update_data']))) {
            // earned points
            fn_print_die('grant_reward_points grant_reward_points');
        }

        foreach ($orders as $order_id) {
            fn_change_order_status($order_id, $temp_status, '', false);
        }

        foreach ($params['update_data'] as $param => $value) {
            db_query('UPDATE ?:status_data SET value = ?s WHERE status_id = ?i AND param = ?s', $value, $old_status_id, $param);
        }

        foreach ($orders as $order_id) {
            fn_change_order_status($order_id, $params['status'], '', false);
        }
    };

    // stage 1
    if ($action == 1) {
        list($orders, ) = fn_get_orders([
            'time_from' => '14/07/2023',
            'time_to' => '17/09/2023',
            'period' => 'C',
            'company_id' => 45,
            'status' => array('L')
        ]);

        // foreach ($orders as $order) {
        //     fn_change_order_status($order['order_id'], 'H');
        // }
        db_query('UPDATE ?:orders SET status = ?s WHERE order_id IN (?a)', 'H', array_column($orders, 'order_id'));

        fn_print_die('done stage 1');
    }
} elseif ($mode == 'correct_reward_points_august1') {
    $add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%m') as `interval`, timestamp");
    $group_condition = ' GROUP BY `interval`';
    $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", fn_parse_date('01/06/2023'), fn_parse_date('1/09/2023')-1);

    $exclude_condition = db_quote(" AND ?:orders.status != 'T' AND ?:orders.status != 'I' AND ?:orders.is_parent_order != 'Y'");

    $periods = db_get_hash_multi_array("SELECT count(order_id) as count, user_id $add_field FROM ?:orders WHERE company_id IN (?a) AND $time_condition $exclude_condition GROUP BY user_id, `interval`", ['interval', 'user_id', 'count'], [1810, 2058]);

    // $test_periods = db_get_array("SELECT count(order_id) as count, order_id, user_id $add_field FROM ?:orders WHERE company_id IN (?a) AND $time_condition $exclude_condition AND user_id = ?i GROUP BY user_id, `interval`", [1810, 2058], 53950);

    foreach ($periods as &$orders) {
        $orders = array_filter($orders, function($v) {
            return $v == 1;
        });
        $orders = array_keys($orders);
    }

    foreach ($periods as $month => $users) {

        foreach ($users as $user_id) {
            $next_month = $month + 1;
            $order_id = db_get_fields("SELECT order_id FROM ?:orders WHERE user_id = ?i AND timestamp BETWEEN ?i AND ?i $exclude_condition", $user_id, fn_parse_date(date("01/$month/Y")), fn_parse_date(date("01/$next_month/Y")) - 1);

            if (count($order_id) > 1) {
                fn_print_die('check_here 1',$order_id, $user_id);
            } elseif (count($order_id) == 1) {
                $order_id = reset($order_id);
                $next_next_month = $next_month + 1;
                $next_order_id = db_get_fields("SELECT order_id FROM ?:orders WHERE user_id = ?i AND timestamp BETWEEN ?i AND ?i AND order_id > ?i $exclude_condition ORDER BY order_id LIMIT 1", $user_id, fn_parse_date(date("01/$next_month/Y")), fn_parse_date(date("01/$next_next_month/Y")) - 1, $order_id);
                if (count($next_order_id) > 1) {
                    fn_print_die('check_here 2', $order_id, $user_id, $next_order_id);
                } elseif (empty($next_order_id)) {
                    // need to add usergroup here;
                    if ($month == 6) continue; //уже не актуально

                    $ugroups = [/*розница*/14776, 13858, /*опт*/ 13360, 13361];
                    $ug_id = db_get_field('SELECT usergroup_id FROM ?:usergroup_links WHERE user_id = ?i AND usergroup_id IN (?a) AND status = ?s', $user_id, $ugroups, 'A');
                    
                    if (empty($ug_id)) continue; // что ты такое??
                    $company_id = db_get_field('SELECT company_id FROM ?:users WHERE user_id = ?i', $user_id);
                    if (!in_array($company_id, [1810, 2058])) continue;

                    if ($month == 7) {
                        // Промо июля, Начисление ЮГ Самара август:

                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36320 круп 80к-1%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36321 круп 150к-1.5%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36322 круп 250к-2%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36207 розн 0к-0.5%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36317 розн 20к-1%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36318 розн 50к-2%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36319 розн 80к-2.5%

                        // Промо июля, Начисление ЮГ Тольятти август:

                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36324 круп 80к-1%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36328 круп 150к-1.5%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36329 круп 250к-2%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36323 розн 0к-0.5%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36324 розн 20к-1%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36325 розн 50к-2%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=36326 розн 80к-2.5%
                     
                        // Вега_Самара_Розничные
                        $promotions[1810][13858] = [36319, 36318, 36317, 36207];
                        // Вега_Самара_Крупнооптовые_клиенты
                        $promotions[1810][13360] = [36322, 36321, 36320];

                        // Вега_Тольятти_Розничные клиенты
                        $promotions[2058][14776] = [36326, 36325, 36324, 36323];
                        // Вега_Тольятти_Крупный ОПТ
                        $promotions[2058][13361] = [36329, 36328, 36324];
                    }
                    
                    if ($month == 8) {
                        // Промо августа, Начисление ЮГ Самара сентябрь:

                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39984 круп 80к-1%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39985 круп 150к-1.5%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39986 круп 250к-2%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39976 розн 0к-0.5%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39977 розн 20к-1%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39978 розн 50к-2%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39979 розн 80к-2.5%

                        // Промо августа, Начисление ЮГ Тольятти сентябрь:

                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39987 круп 80к-1%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39988 круп 150к-1.5%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39989 круп 250к-2%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39980 розн 0к-0.5%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39981 розн 20к-1%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39982 розн 50к-2%
                        // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=39983 розн 80к-2.5%
                     
                        // Вега_Самара_Розничные
                        $promotions[1810][13858] = [39979, 39978, 39977, 39976];
                        // Вега_Самара_Крупнооптовые_клиенты
                        $promotions[1810][13360] = [39986, 39985, 39984];

                        // Вега_Тольятти_Розничные клиенты
                        $promotions[2058][14776] = [39983, 39982, 39981, 39980];
                        // Вега_Тольятти_Крупный ОПТ
                        $promotions[2058][13361] = [39989, 39988, 39987];
                    }

                    if (!isset($promotions[$company_id][$ug_id])) continue;

                    foreach ($promotions[$company_id][$ug_id] as $promotion_id) {
                        $promotion = fn_get_promotion_data($promotion_id);
                        $progress_condition = fn_find_promotion_condition($promotion['conditions'], 'progress_total_paid');
                        $val = fn_promotion_validate_promotion_progress($promotion['promotion_id'], $progress_condition, ['user_id' => $user_id], $promotion);

                        if ($val > $progress_condition['value']) {
                        
                            foreach($promotion['bonuses'] as $bonus) {
                                if ($bonus['bonus'] == 'give_usergroup') {
                                    $already_granted = db_get_field('SELECT link_id FROM ?:usergroup_links WHERE user_id = ?i AND usergroup_id = ?i AND status = ?s', $user_id, $bonus['value'], 'A');
                                    if (!$already_granted) {
                                        $insert = ['user_id' => $user_id, 'usergroup_id' => $bonus['value'], 'status' => 'A'];
                                        db_query('REPLACE INTO ?:usergroup_links SET ?u', $insert);
                                        $user_info = fn_get_user_info($user_id);
                                        $insert['user_login'] = $user_info['user_login'];
                                        $insert['firstname'] = $user_info['firstname'];
                                        $insert['usergroup'] = fn_get_usergroup_name($bonus['value']);
                                        $insert['total_sales'] = $val;
                                        $insert['promo'] = $promotion['name'];
                                        $insert['vendor'] = ($company_id == 1810) ? 'вега' : 'вегаТ';
                                        $insert['user_type'] = fn_get_usergroup_name($ug_id);

                                        $result[] = $insert;
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
            } else {
                $order_id2 = db_quote("SELECT order_id FROM ?:orders WHERE user_id = ?i AND timestamp BETWEEN ?i AND ?i $exclude_condition", $user_id, fn_parse_date(date("01/$month/Y")), fn_parse_date(date("t/$month/Y", true)) );

                fn_print_die('check_here 6', $order_id2, $month, $user_id);
            }
        }
    }

    fn_print_die('stop', $result);
} elseif ($mode == 'correct_reward_points_august2') {
    $add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%m') as `interval`, timestamp");
    $group_condition = ' GROUP BY `interval`';
    $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", fn_parse_date('01/06/2023'), fn_parse_date('1/09/2023')-1);

    $exclude_condition = db_quote(" AND ?:orders.status != 'T' AND ?:orders.status != 'I' AND ?:orders.is_parent_order != 'Y'");

    $periods = db_get_hash_multi_array("SELECT count(order_id) as count, user_id $add_field FROM ?:orders WHERE company_id IN (?a) AND $time_condition $exclude_condition GROUP BY user_id, `interval`", ['interval', 'user_id', 'count'], [1810, 2058]);

    // $test_periods = db_get_array("SELECT count(order_id) as count, order_id, user_id $add_field FROM ?:orders WHERE company_id IN (?a) AND $time_condition $exclude_condition AND user_id = ?i GROUP BY user_id, `interval`", [1810, 2058], 53950);

    foreach ($periods as &$orders) {
        $orders = array_filter($orders, function($v) {
            return $v == 1;
        });
        $orders = array_keys($orders);
    }

    foreach ($periods as $month => $users) {

        foreach ($users as $user_id) {
            $next_month = $month + 1;
            $order_id = db_get_fields("SELECT order_id FROM ?:orders WHERE user_id = ?i AND timestamp BETWEEN ?i AND ?i $exclude_condition", $user_id, fn_parse_date(date("01/$month/Y")), fn_parse_date(date("01/$next_month/Y")) - 1);

            if (count($order_id) > 1) {
                fn_print_die('check_here 1',$order_id, $user_id);
            } elseif (count($order_id) == 1) {
                $order_id = reset($order_id);
                $next_next_month = $next_month + 1;
                $next_order_id = db_get_fields("SELECT order_id FROM ?:orders WHERE user_id = ?i AND timestamp BETWEEN ?i AND ?i AND order_id > ?i $exclude_condition ORDER BY order_id LIMIT 1", $user_id, fn_parse_date(date("01/$next_month/Y")), fn_parse_date(date("01/$next_next_month/Y")) - 1, $order_id);
                if (count($next_order_id) > 1) {
                    fn_print_die('check_here 2', $order_id, $user_id, $next_order_id);
                } elseif (!empty($next_order_id)) {
                    // check one order 
                    $next_order_id = reset($next_order_id);
                    $total = db_get_field('SELECT total FROM ?:orders WHERE order_id = ?i', $order_id);
                    $is_rozn = db_get_field('SELECT link_id FROM ?:usergroup_links WHERE user_id = ?i AND status = ?s AND usergroup_id IN (?a)', $user_id, 'A', [13858,14776]);
                    $cb = 0;
                    if (!empty($is_rozn)) {
                        if ($total < 20000 ) {
                            $cb = 0.5;
                        } elseif ($total < 50000) {
                            $cb = 1;
                        } elseif ($total < 80000) {
                            $cb = 2;
                        } else {
                            $cb = 2.5;
                        }
                    } elseif (db_get_field('SELECT link_id FROM ?:usergroup_links WHERE user_id = ?i AND status = ?s AND usergroup_id IN (?a)', $user_id, 'A', [13360, 13361])) {
                        if ($total < 80000 ) {
                            $cb = 0;
                        } elseif ($total < 150000) {
                            $cb = 1;
                        } elseif ($total < 250000) {
                            $cb = 1.5;
                        } else {
                            $cb = 2;
                        }

                    } else {
                        // что ты такое?
                        continue;
                    }
                    if (!empty($cb)) {
                        $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $user_id, CHANGE_DUE_ORDER);

                        $is_corrected = false;
                        foreach ($reward_point_changes as $change) {
                            $details = unserialize($change['reason']);
                            if (!empty($details['order_id']) && $details['order_id'] == $next_order_id && $details['correction'] == 'correct_reward_points_august2') {
                                $is_corrected = true;
                                break;
                            }
                        }
                        if ($is_corrected) continue;

                        $order_info = db_get_row('SELECT total, timestamp, status FROM ?:orders WHERE order_id = ?i', $next_order_id);

                        if (empty((int) $order_info['total'])) continue;

                        if ($order_info['status'] != 'H') {
                            $out_of_status[] = $next_order_id;
                            continue;
                        }

                        $reason = array('order_id' => $next_order_id, 'to' => $order_info['status'], 'correction' => 'correct_reward_points_august2');
                        fn_change_user_points(round($order_info['total']*$cb/100), $user_id, serialize($reason), CHANGE_DUE_ORDER, $order_info['timestamp']);

                        $reason['amount'] = round($order_info['total']*$cb/100);
                        $reason['user_id'] = $user_id;
                        $corrections[] = $reason;
                    }
                }
            } else {
                $order_id2 = db_quote("SELECT order_id FROM ?:orders WHERE user_id = ?i AND timestamp BETWEEN ?i AND ?i $exclude_condition", $user_id, fn_parse_date(date("01/$month/Y")), fn_parse_date(date("t/$month/Y", true)) );

                fn_print_die('check_here 6', $order_id2, $month, $user_id);
            }
        }
    }

    fn_print_die('Done', $corrections, array_sum(array_column($corrections, 'amount')), 'Orders out of status:', $out_of_status);
} elseif ($mode == 'cleanup_wishlist_2') {
    $iteration = empty($action) ? 1 : $action;
    
    if ($iteration == 1) db_query('DELETE FROM ?:user_session_products WHERE product_id NOT IN (SELECT product_id FROM ?:products)');

    $step = 100;
    $limit = ' LIMIT '. ($iteration - 1) * $step . ', ' . $step;
    $user_ids = db_get_fields("SELECT distinct(user_id) FROM ?:user_session_products WHERE user_type = ?s AND type = ?s $limit", 'R', 'W');
    if (empty($user_ids)) fn_print_die('done');

    foreach ($user_ids as $user_id) {
        $wl_products = db_get_fields('SELECT product_id FROM ?:user_session_products WHERE user_id = ?i AND type = ?s', $user_id, 'W');
        $ordered_products = db_get_fields('SELECT DISTINCT(product_id) FROM ?:order_details AS od LEFT JOIN ?:orders AS o ON o.order_id = od.order_id WHERE o.user_id = ?i', $user_id);

        if ($diff = array_diff($wl_products, $ordered_products)) {
            db_query('DELETE FROM ?:user_session_products WHERE user_id = ?i AND type = ?s AND product_id IN (?a)', $user_id, 'W', $diff);
        }
    }

    fn_print_r($iteration);
    $iteration += 1;
    fn_redirect('tools.cleanup_wishlist_2.' . $iteration);
} elseif ($mode == 'cleanup_wishlist_3') {
    $iteration = empty($action) ? 1 : $action;

    if ($iteration == 1) unset($_SESSION['delete_session']);
    
    $step = 1000;
    $limit = ' LIMIT '. ($iteration - 1) * $step . ', ' . $step;

    $sessions = db_get_array("SELECT * FROM ?:sessions $limit");
    if (empty($sessions)) {
        db_query('DELETE FROM ?:sessions WHERE session_id IN (?a)', $_SESSION['delete_session']);
        unset($_SESSION['delete_session']);
        fn_print_die('done');
    }

    foreach ($sessions as &$data) {
        $decode = Tygh::$app['session']->decode($data['data']);
        $user_id = $decode['auth']['user_id'];
        if (empty($user_id)) {
            $_SESSION['delete_session'][] = $data['session_id'];
        } elseif (!empty($decode['wishlist']['products'])) {
            $decode['wishlist']['products'] = array_filter($decode['wishlist']['products'], function($p) use ($user_id) {
                return $p['user_id'] == $user_id;
            });
            $data['data'] = Tygh::$app['session']->encode($decode);
            db_query('UPDATE ?:sessions SET data = ?s WHERE session_id = ?s', $data['data'], $data['session_id']);
        }
    }

    //$user_ids = db_get_fields("SELECT distinct(user_id) FROM ?:user_session_products WHERE user_type = ?s AND type = ?s $limit", 'R', 'W');
    // foreach ($user_ids as $user_id) {
    //     $array = [
    //         'auth' => [
    //             'area' => 'C',
    //             'user_id' => "$user_id"
    //         ],
    //     ];
    //     $str = serialize($array);
    //     $search = str_replace(['a:1:{s:4:"auth";a:2:{', '}}'], ['', ''], $str);
    //     $sessions = db_get_array('SELECT data, session_id FROM ?:sessions WHERE data LIKE ?l', '%' . $search . '%');
    //     foreach ($sessions as $session) {
    //         $data = Tygh::$app['session']->decode($session['data']);
    //         $data['wishlist']['products'] = array_filter($data['wishlist']['products'], function($p) use ($user_id) {
    //             return $p['user_id'] == $user_id;
    //         });
    //         $session['data'] = Tygh::$app['session']->encode($data);
    //         db_query('UPDATE ?:sessions SET data = ?s WHERE session_id = ?s', $session['data'], $session['session_id']);
    //     }
    // }

    fn_print_r($iteration);
    $iteration += 1;
    fn_redirect('tools.cleanup_wishlist_3.' . $iteration);
} elseif ($mode == 'correct_reward_points_september') {

    $add_points = [];
    // $promotions[1810][8] = [39994, 39993, 39992, 39991, 39990];
    // $promotions[1810][9] = [43350, 43349, 43348, 43347, 43346];

    // $promotions[2058][8] = [39999, 39998, 39997, 39996, 39995];
    // $promotions[2058][9] = [43355, 43354, 43353, 43352, 43351];
    $promotions[1810][8] = [36335, 36334, 36333, 36332, 36331];
    $promotions[1810][9] = [39994, 39993, 39992, 39991, 39990];

    $promotions[2058][8] = [36340, 36339, 36338, 36337, 36336];
    $promotions[2058][9] = [39999, 39998, 39997, 39996, 39995];

    foreach ($promotions as $company_id => &$company_promotions) {
        foreach ($company_promotions as &$ps) {
            foreach ($ps as &$p) {
                $p = fn_get_promotion_data($p);
            }
        }
    }

    $month = 8;
    $next_month = $month+1;
    $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", fn_parse_date('01/0' . $month . '/2023'), fn_parse_date('01/0' . $next_month . '/2023')-1);
    $orders = db_get_fields("SELECT order_id FROM ?:orders WHERE company_id IN (?a) AND $time_condition AND parent_order_id != ?i AND group_id NOT IN (?a)", [1810, 2058], 0, [17,18]);

    foreach ($orders as $order_id) {
        $order_info = fn_get_order_info($order_id);
        if ($order_info['status'] == 'H') {
            if($order_info['points_info']['reward']) continue;

            $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $order_info['user_id'], CHANGE_DUE_ORDER);

            $is_corrected = false;
            foreach ($reward_point_changes as $change) {
                $details = unserialize($change['reason']);
                if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id'] && $details['correction'] == 'correct_reward_points_september') {
                    $is_corrected = true;
                    break;
                }
            }
            if ($is_corrected) continue;

            $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $order_info['user_id']);
            $customer_auth = fn_fill_auth($_data, array(), false, 'C');
            foreach ($promotions[$order_info['company_id']][$month] as $promotion) {
                $res = fn_check_promotion_conditions($promotion, $order_info, $customer_auth, $order_info['products']);
                if ($res) {
                    $percent = reset($promotion['bonuses'])['value'];
                    $points = round($order_info['total']*$percent/100);
                    $add_points[] = [
                        'user_id' => $order_info['user_id'],
                        'order_id' => $order_info['order_id'],
                        'status' => $order_info['status'],
                        'order_total' => $order_info['total'],
                        'percent' => $percent,
                        'points' => $points,
                        'timestamp' => $order_info['timestamp'],
                    ];
                    break;
                }
            }
        } else {
            $out_of_status[] = $order_info['order_id'];
        }
    }

    $month = 9;

    $next_month = $month+1;
    $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", fn_parse_date('01/0' . $month . '/2023'), fn_parse_date('01/0' . $next_month . '/2023')-1);
    $orders = db_get_fields("SELECT order_id FROM ?:orders WHERE company_id IN (?a) AND $time_condition AND parent_order_id != ?i AND group_id NOT IN (?a)", [1810, 2058], 0, [17,18]);

    foreach ($orders as $order_id) {
        $order_info = fn_get_order_info($order_id);
        if($order_info['points_info']['reward']) continue;

        if ($order_info['status'] == 'H') {

            $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $order_info['user_id'], CHANGE_DUE_ORDER);

            $is_corrected = false;
            foreach ($reward_point_changes as $change) {
                $details = unserialize($change['reason']);
                if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id'] && $details['correction'] == 'correct_reward_points_september') {
                    $is_corrected = true;
                    break;
                }
            }
            if ($is_corrected) continue;

            $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $order_info['user_id']);
            $customer_auth = fn_fill_auth($_data, array(), false, 'C');
            foreach ($promotions[$order_info['company_id']][$month] as $promotion) {
                $res = fn_check_promotion_conditions($promotion, $order_info, $customer_auth, $order_info['products']);
                if ($res) {
                    $percent = reset($promotion['bonuses'])['value'];
                    $points = round($order_info['total']*$percent/100);
                    $add_points[] = [
                        'user_id' => $order_info['user_id'],
                        'order_id' => $order_info['order_id'],
                        'status' => $order_info['status'],
                        'order_total' => $order_info['total'],
                        'percent' => $percent,
                        'points' => $points,
                        'timestamp' => $order_info['timestamp'],
                    ];
                    break;
                }
            }
        } else {
            $out_of_status[] = $order_info['order_id'];
        }
    }

    foreach ($add_points as $add) {
        $reason = array('order_id' => $add['order_id'], 'to' => $add['status'], 'correction' => 'correct_reward_points_september');
        fn_change_user_points($add['points'], $add['user_id'], serialize($reason), CHANGE_DUE_ORDER, $add['timestamp']);
    }

    fn_print_die('Текущая корректировка', $add_points, 'Заказы вне статуса:', $out_of_status);
} elseif ($mode == 'correct_reward_points_october') {
    $promotions[1810][8] = [36335, 36334, 36333, 36332, 36331];
    $promotions[1810][9] = [39994, 39993, 39992, 39991, 39990];
    $promotions[1810][10] = [43350, 43349, 43348, 43347, 43346];

    $promotions[2058][8] = [36340, 36339, 36338, 36337, 36336];
    $promotions[2058][9] = [39999, 39998, 39997, 39996, 39995];
    $promotions[2058][10] = [43355, 43354, 43353, 43352, 43351];

    foreach ($promotions as $company_id => &$company_promotions) {
        foreach ($company_promotions as &$ps) {
            foreach ($ps as &$p) {
                $p = fn_get_promotion_data($p);
            }
        }
    }

    $months = ['08','09','10'];
    //432165
    //$months = ['09'];

    foreach ($months as $month) {
        $add_points = [];
        $next_month = $month+1;
        $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", fn_parse_date('01/' . $month . '/2023'), fn_parse_date('01/' . $next_month . '/2023')-1);
        $orders = db_get_fields("SELECT order_id FROM ?:orders WHERE company_id IN (?a) AND $time_condition AND is_parent_order = ?s AND group_id NOT IN (?a)", [1810, 2058], 'N', [17,18]);

        foreach ($orders as $order_id) {
            if ($order_id == 455927) continue;
            $order_info = fn_get_order_info($order_id);
            if ($order_info['status'] == 'H') {
                if($order_info['points_info']['reward']) continue;

                $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $order_info['user_id'], CHANGE_DUE_ORDER);

                $is_corrected = false;
                $is_once_granted = false;
                $is_group_order = false;
                foreach ($reward_point_changes as $change) {
                    $details = unserialize($change['reason']);
                    if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id']) {
                        $is_once_granted = true;
                    }
                    if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id'] && strpos($details['correction'], 'correct_reward_points_') !== false) {
                        $is_corrected = true;
                        break;
                    }
                }
                if ($is_corrected) continue;

                // $product_ids = array_column($order_info['products'], 'product_id');
                // $group_ids = db_get_fields('SELECT group_id FROM ?:products WHERE product_id IN (?a)', $product_ids);
                // if (array_sum($group_ids)) continue;

                $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $order_info['user_id']);
                $customer_auth = fn_fill_auth($_data, array(), false, 'C');
                foreach ($promotions[$order_info['company_id']][(int) $month] as $promotion) {
                    $res = fn_check_promotion_conditions($promotion, $order_info, $customer_auth, $order_info['products']);
                    $points = round($order_info['total']*$percent/100);
                    if ($res) {
                        $percent = reset($promotion['bonuses'])['value'];
                        $points = round($order_info['total']*$percent/100);
                        if (empty($points)) break;
                        $add_points[] = [
                            'user_id' => $order_info['user_id'],
                            'company_id' => $order_info['company_id'],
                            'order_id' => $order_info['order_id'],
                            'status' => $order_info['status'],
                            'order_total' => $order_info['total'],
                            'percent' => $percent,
                            'points' => $points,
                            'timestamp' => $order_info['timestamp'],
                            'is_once_granted' => $is_once_granted,
                            'is_group_order' => $is_group_order,
                        ];
                        break;
                    }
                }
            } else {
                $out_of_status[] = $order_info['order_id'];
            }
        }

        foreach ($add_points as $add) {
            $reason = array('order_id' => $add['order_id'], 'to' => $add['status'], 'correction' => 'correct_reward_points_october');
            fn_change_user_points($add['points'], $add['user_id'], serialize($reason), CHANGE_DUE_ORDER, $add['timestamp']);
        }

        fn_print_r('Текущая корректировка ' . $month, $add_points);

        // $params['filename'] = "points_to_correct_$month.csv";
        // $params['force_header'] = true;
        // $export = fn_exim_put_csv($add_points, $params, '"');
    }

    fn_print_die('done. Out of status:', $out_of_status);
} elseif ($mode == 'ssessions') {
    $iteration = empty($action) ? 1 : $action;

    $step = 1000;
    $limit = ' LIMIT '. ($iteration - 1) * $step . ', ' . $step;

    $sessions = db_get_array("SELECT * FROM ?:user_session_products $limit");
    if ($sessions) {
        foreach ($sessions as &$data) {
            $extra = unserialize($data['extra']);
            $data['ts'] = $extra['timestamp'] ?? 0;
            $data['uid'] = $extra['user_id'] ?? 0;
            db_query('REPLACE INTO ?:user_session_products SET ?u', $data);
        }
    } else {
        fn_print_die('done');
    }

    fn_print_r($iteration);
    $iteration += 1;
    fn_redirect('tools.ssessions.' . $iteration);
} elseif ($mode == 'debug_sessions') {
    $user_id = 203165;
    $usp = db_get_array('SELECT * FROM ?:user_session_products WHERE user_id = ?i AND type = ?s', $user_id, 'W');
    foreach($usp as $key => &$record) {
        $record['extra'] = unserialize($record['extra']);
        if ($record['user_id'] == $extra['user_id']) {
            unset($usp[$key]);  
        } else {
            $record['extra_timestamp'] = $record['extra']['timestamp'];
        }
    }
    $usp = fn_sort_array_by_key($usp, 'extra_timestamp', SORT_DESC);
    fn_print_die($usp);
} elseif ($mode == 'baltica_barcodes') {
    // $file = 'cscart_products.csv';
    // $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
    // $content = fn_array_elements_to_keys($content, 'product_id');
    // $keywords = db_get_array('SELECT search_words, product_id FROM ?:product_descriptions WHERE product_id IN (?a)', array_keys($content));
    // $keywords = fn_array_elements_to_keys($keywords, 'product_id');
    // $result = fn_array_merge($content, $keywords);

    // $params['filename'] = 'cscart_products_.csv';
    // $params['force_header'] = false;
    // $export = fn_exim_put_csv(array_values($result), $params, '"');

    $file = 'cscart_products_.csv';
    $content = fn_exim_get_csv(array(), $file, array('validate_schema'=> false, 'delimiter' => ';') );
    foreach ($content as $value) {
        db_query('UPDATE ?:products SET barcode = ?s WHERE product_id = ?i', $value['barcode'], $value['product_id']);
        db_query('UPDATE ?:product_descriptions SET search_words = ?s WHERE product_id = ?i', $value['search_words'], $value['product_id']);
    }
 
    fn_print_die('done');
} elseif ($mode == 'correct_reward_points_january1') {
    $add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%m') as `interval`, timestamp");
    $group_condition = ' GROUP BY `interval`';
    $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", fn_parse_date('01/11/2023'), fn_parse_date('31/01/2024')-1);


    $exclude_condition = db_quote(" AND ?:orders.status != 'T' AND ?:orders.status != 'I' AND ?:orders.is_parent_order != 'Y'");

    $periods = db_get_hash_multi_array("SELECT count(order_id) as count, user_id $add_field FROM ?:orders WHERE company_id IN (?a) AND $time_condition $exclude_condition GROUP BY user_id, `interval` ORDER BY timestamp", ['interval', 'user_id', 'count'], [1810, 2058]);

    // $test_periods = db_get_array("SELECT count(order_id) as count, order_id, user_id $add_field FROM ?:orders WHERE company_id IN (?a) AND $time_condition $exclude_condition AND user_id = ?i GROUP BY user_id, `interval`", [1810, 2058], 53950);

    foreach ($periods as &$orders) {
        // $orders = array_filter($orders, function($v) {
        //     return $v == 1;
        // });
        $orders = array_keys($orders);
    }

    foreach ($periods as $month => $users) {
        foreach ($users as $user_id) {
            $promotions = [];
            // need to add usergroup here;
            $ugroups = [/*розница*/14776, 13858, /*опт*/ 13360, 13361];
            $ug_id = db_get_field('SELECT usergroup_id FROM ?:usergroup_links WHERE user_id = ?i AND usergroup_id IN (?a) AND status = ?s', $user_id, $ugroups, 'A');
            
            if (empty($ug_id)) continue; // что ты такое??
            $company_id = db_get_field('SELECT company_id FROM ?:users WHERE user_id = ?i', $user_id);
            if (!in_array($company_id, [1810, 2058])) continue;

            if ($month == 11) {
                // Промо ноября, Начисление ЮГ Самара декабря:

                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52303 круп 80к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52304 круп 150к-1.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52305 круп 250к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52295 розн 0к-0.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52296 розн 20к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52297 розн 50к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52298 розн 80к-2.5%

                // Промо ноября, Начисление ЮГ Тольятти декабря:

                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52306 круп 80к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52307 круп 150к-1.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52308 круп 250к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52299 розн 0к-0.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52300 розн 20к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52301 розн 50к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=52302 розн 80к-2.5%
             
                // Вега_Самара_Розничные
                $promotions[1810][13858] = [52298, 52297, 52296, 52295];
                // Вега_Самара_Крупнооптовые_клиенты
                $promotions[1810][13360] = [52305, 52304, 52303];

                // Вега_Тольятти_Розничные клиенты
                $promotions[2058][14776] = [52302, 52301, 52300, 52299];
                // Вега_Тольятти_Крупный ОПТ
                $promotions[2058][13361] = [52308, 52307, 52306];
            }
            
            if ($month == 12) {
                // Промо декабря, Начисление ЮГ Самара январь:

                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57703 круп 80к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57704 круп 150к-1.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57705 круп 250к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57691 розн 0к-0.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57692 розн 20к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57693 розн 50к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57694 розн 80к-2.5%

                // Промо декабря, Начисление ЮГ Тольятти январь:

                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57706 круп 80к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57707 круп 150к-1.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57708 круп 250к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57699 розн 0к-0.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57700 розн 20к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57701 розн 50к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=57702 розн 80к-2.5%
             
                // Вега_Самара_Розничные
                $promotions[1810][13858] = [57694, 57693, 57692, 57691];
                // Вега_Самара_Крупнооптовые_клиенты
                $promotions[1810][13360] = [57705, 57704, 57703];

                // Вега_Тольятти_Розничные клиенты
                $promotions[2058][14776] = [57702, 57701, 57700, 57699];
                // Вега_Тольятти_Крупный ОПТ
                $promotions[2058][13361] = [57708, 57707, 57706];
            }

            if ($month == '01') {
                // Промо января, Начисление ЮГ Самара февраль:

                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63015 круп 80к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63016 круп 150к-1.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63017 круп 250к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63007 розн 0к-0.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63008 розн 20к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63009 розн 50к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63010 розн 80к-2.5%

                // Промо января, Начисление ЮГ Тольятти февраль:

                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63018 круп 80к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63019 круп 150к-1.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63020 круп 250к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63011 розн 0к-0.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63012 розн 20к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63013 розн 50к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=63014 розн 80к-2.5%
             
                // Вега_Самара_Розничные
                $promotions[1810][13858] = [63010, 63009, 63008, 63007];
                // Вега_Самара_Крупнооптовые_клиенты
                $promotions[1810][13360] = [63017, 63016, 63015];

                // Вега_Тольятти_Розничные клиенты
                $promotions[2058][14776] = [63014, 63013, 63012, 63011];
                // Вега_Тольятти_Крупный ОПТ
                $promotions[2058][13361] = [63020, 63019, 63018];
            }

            if (!isset($promotions[$company_id][$ug_id])) continue;

            foreach ($promotions[$company_id][$ug_id] as $promotion_id) {
                $promotion = fn_get_promotion_data($promotion_id);
                $progress_condition = fn_find_promotion_condition($promotion['conditions'], 'progress_total_paid');
                $val = fn_promotion_validate_promotion_progress($promotion['promotion_id'], $progress_condition, ['user_id' => $user_id], $promotion);

                if ($val > $progress_condition['value']) {

                    foreach($promotion['bonuses'] as $bonus) {
                        if ($bonus['bonus'] == 'give_usergroup') {
                            $already_granted = db_get_field('SELECT link_id FROM ?:usergroup_links WHERE user_id = ?i AND usergroup_id = ?i AND status = ?s', $user_id, $bonus['value'], 'A');

                            if (!$already_granted) {
                                $insert = ['user_id' => $user_id, 'usergroup_id' => $bonus['value'], 'status' => 'A'];
                                db_query('REPLACE INTO ?:usergroup_links SET ?u', $insert);
                                $user_info = fn_get_user_info($user_id);
                                $insert['user_login'] = $user_info['user_login'];
                                $insert['firstname'] = $user_info['firstname'];
                                $insert['add_usergroup'] = fn_get_usergroup_name($bonus['value']);
                                $insert['period_total_sales'] = $val;
                                $insert['promo'] = $promotion['name'];
                                $insert['vendor'] = ($company_id == 1810) ? 'вега' : 'вегаТ';
                                $insert['user_type'] = fn_get_usergroup_name($ug_id);

                                $result[] = $insert;
                            }
                        }
                    }
                    break;
                }
            }
            // $data = db_get_array("SELECT order_id $add_field FROM ?:orders WHERE user_id = ?i AND $time_condition $exclude_condition", $user_id);
            // $data = fn_group_array_by_key($data, 'interval');
            // $order_id = array_column($data[$month], 'order_id');
            // fn_print_die(count($order_id));
            // $orders = db_get_array("SELECT order_id $add_field FROM ?:orders WHERE user_id = ?i $exclude_condition AND timestamp > ?i ORDER BY timestamp", $user_id, fn_parse_date('01/03/2023'));
        }
    }

    $params['filename'] = 'grade_usergroups_oct_jan.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($result, $params, '"');
    fn_print_die($result);
} elseif ($mode == 'correct_reward_points_january2') {
    $promotions[1810]['12'] = [52318, 52317, 52316, 52315, 52314];
    $promotions[1810]['01'] = [57721, 57720, 57719, 57718, 57717];

    $promotions[2058]['12'] = [52313, 52312, 52311, 52310, 52309];
    $promotions[2058]['01'] = [57716, 57715, 57714, 57713, 57712];

    foreach ($promotions as $company_id => &$company_promotions) {
        foreach ($company_promotions as &$ps) {
            foreach ($ps as &$p) {
                $p = fn_get_promotion_data($p);
            }
        }
    }

    $months = ['12','01'];

    foreach ($months as $month) {
        $year = 2023;
        if ($month <= date('m')) {
            $year = 2024;
        }
        $add_points = [];
        $start_ts = fn_parse_date('01/' . $month . '/'. $year);
        $end_ts = strtotime("+1 month", $start_ts) - 1;
        $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", $start_ts, $end_ts);
        $orders = db_get_fields("SELECT order_id FROM ?:orders WHERE company_id IN (?a) AND $time_condition AND is_parent_order = ?s AND group_id NOT IN (?a)", [1810, 2058], 'N', [17,18]);

        foreach ($orders as $order_id) {
            // if ($order_id == 455927) continue;
            $order_info = fn_get_order_info($order_id);

            if ($order_info['status'] == 'H') {
                if ($order_info['points_info']['reward']) continue;
                if ($order_info['points_info']['in_use']['points']) continue;

                $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $order_info['user_id'], CHANGE_DUE_ORDER);

                $is_corrected = false;
                $is_once_granted = false;
                $is_group_order = false;
                foreach ($reward_point_changes as $change) {
                    $details = unserialize($change['reason']);
                    if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id']) {
                        $is_once_granted = true;
                    }
                    if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id'] && strpos($details['correction'], 'correct_reward_points_') !== false) {
                        $is_corrected = true;
                        break;
                    }
                }
                if ($is_corrected) continue;

                $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $order_info['user_id']);
                $customer_auth = fn_fill_auth($_data, array(), false, 'C');
                foreach ($promotions[$order_info['company_id']][$month] as $promotion) {
                    $res = fn_check_promotion_conditions($promotion, $order_info, $customer_auth, $order_info['products']);
                    //$points = round($order_info['total']*$percent/100);
                    if ($res) {
                        $percent = reset($promotion['bonuses'])['value'];
                        $points = round($order_info['total']*$percent/100);

                        if (empty($points)) break;
                        $add_points[] = [
                            'user_id' => $order_info['user_id'],
                            'company_id' => $order_info['company_id'],
                            'order_id' => $order_info['order_id'],
                            'status' => $order_info['status'],
                            'order_total' => $order_info['total'],
                            'percent' => $percent,
                            'points' => $points,
                            'timestamp' => $order_info['timestamp'],
                            // 'is_once_granted' => $is_once_granted,
                            // 'is_group_order' => $is_group_order,
                        ];
                        break;
                    }
                }
            } else {
                $out_of_status[] = $order_info['order_id'];
            }
        }

        foreach ($add_points as $add) {
            $reason = array('order_id' => $add['order_id'], 'to' => $add['status'], 'correction' => 'correct_reward_points_january');
            fn_change_user_points($add['points'], $add['user_id'], serialize($reason), CHANGE_DUE_ORDER, $add['timestamp']);
        }

        fn_print_r('Текущая корректировка ' . $month, $add_points);

        $params['filename'] = "points_to_correct_$month.csv";
        $params['force_header'] = true;
        $export = fn_exim_put_csv($add_points, $params, '"');
    }

    fn_print_die('done. Out of status:', $out_of_status);
//  elseif ($mode == 'restore_points_in_use') {
//     $iteration = empty($action) ? 1 : $action;

//     $step = 500;
//     $limit = ' LIMIT '. ($iteration-1)* $step . ', ' . $step;

//     $order_ids = db_get_fields("SELECT order_id FROM ?:orders WHERE group_id != 0 AND order_id > 262068 $limit");

//     if (empty($order_ids)) {
//         fn_print_die('done');
//     } else {
//         fn_print_r(reset($order_ids));
//     }

//     foreach ($order_ids as $order_id) {
//         $info = fn_get_order_info($order_id);
//         if (!empty($info['points_info']['in_use'])) {
//             fn_reward_points_place_order($order_id, $fake, $fake, $info);
//         }
//     }

//     $iteration += 1;
//     fn_redirect('tools.restore_points_in_use.' . $iteration);
} elseif ($mode == 'correct_reward_points_february1') {
    $add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%m') as `interval`, timestamp");
    $group_condition = ' GROUP BY `interval`';
    $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", fn_parse_date('01/02/2024'), fn_parse_date('31/03/2024')-1);
    $exclude_condition = db_quote(" AND ?:orders.status != 'T' AND ?:orders.status != 'I' AND ?:orders.is_parent_order != 'Y'");
    $periods = db_get_hash_multi_array("SELECT count(order_id) as count, user_id $add_field FROM ?:orders WHERE company_id IN (?a) AND $time_condition $exclude_condition GROUP BY user_id, `interval` ORDER BY timestamp", ['interval', 'user_id', 'count'], [1810, 2058]);

    foreach ($periods as &$orders) {
        $orders = array_keys($orders);
    }
    unset($orders);

    foreach ($periods as $month => $users) {
        foreach ($users as $user_id) {
            $promotions = [];
            // need to add usergroup here;
            $ugroups = [/*розница*/14776, 13858, /*опт*/ 13360, 13361];
            $ug_id = db_get_field('SELECT usergroup_id FROM ?:usergroup_links WHERE user_id = ?i AND usergroup_id IN (?a) AND status = ?s', $user_id, $ugroups, 'A');
            
            if (empty($ug_id)) continue; // что ты такое??
            $company_id = db_get_field('SELECT company_id FROM ?:users WHERE user_id = ?i', $user_id);
            if (!in_array($company_id, [1810, 2058])) continue;

            if ($month == '02') {
                // Промо февраля, Начисление ЮГ Самара март:

                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67121 круп 80к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67122 круп 150к-1.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67123 круп 250к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67114 розн 0к-0.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67115 розн 20к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67113 розн 50к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67116 розн 80к-2.5%

                // Промо февраля, Начисление ЮГ Тольятти март:

                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67124 круп 80к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67125 круп 150к-1.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67126 круп 250к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67117 розн 0к-0.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67118 розн 20к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67119 розн 50к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67120 розн 80к-2.5%
             
                // Вега_Самара_Розничные
                $promotions[1810][13858] = [67116, 67113, 67115, 67114];
                // Вега_Самара_Крупнооптовые_клиенты
                $promotions[1810][13360] = [67123, 67122, 67121];

                // Вега_Тольятти_Розничные клиенты
                $promotions[2058][14776] = [67120, 67119, 67118, 67117];
                // Вега_Тольятти_Крупный ОПТ
                $promotions[2058][13361] = [67126, 67125, 67124];
            }

            if ($month == '03') {
                // Промо март, Начисление ЮГ Самара апрель:

                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=69673 круп 80к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=69674 круп 150к-1.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=69675 круп 250к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=69419 розн 0к-0.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=69420 розн 20к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=69421 розн 50к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=69422 розн 80к-2.5%

                // Промо март, Начисление ЮГ Тольятти апрель:

                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=69676 круп 80к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=69677 круп 150к-1.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=69678 круп 250к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67117 розн 0к-0.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67118 розн 20к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67119 розн 50к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=67120 розн 80к-2.5%
             
                // Вега_Самара_Розничные
                $promotions[1810][13858] = [69422, 69421, 69420, 69419];
                // Вега_Самара_Крупнооптовые_клиенты
                $promotions[1810][13360] = [69675, 69674, 69673];

                // Вега_Тольятти_Розничные клиенты
                $promotions[2058][14776] = [67120, 67119, 67118, 67117];
                // Вега_Тольятти_Крупный ОПТ
                $promotions[2058][13361] = [69678, 69677, 69676];
            }

            if (!isset($promotions[$company_id][$ug_id])) continue;

            foreach ($promotions[$company_id][$ug_id] as $promotion_id) {
                $promotion = fn_get_promotion_data($promotion_id);
                $progress_condition = fn_find_promotion_condition($promotion['conditions'], 'progress_total_paid');
                $val = fn_promotion_validate_promotion_progress($promotion['promotion_id'], $progress_condition, ['user_id' => $user_id], $promotion);

                if ($val > $progress_condition['value']) {
                    foreach($promotion['bonuses'] as $bonus) {
                        if ($bonus['bonus'] == 'give_usergroup') {
                            $already_granted = db_get_field('SELECT link_id FROM ?:usergroup_links WHERE user_id = ?i AND usergroup_id = ?i AND status = ?s', $user_id, $bonus['value'], 'A');

                            if (!$already_granted) {
                                $insert = ['user_id' => $user_id, 'usergroup_id' => $bonus['value'], 'status' => 'A'];
                                db_query('REPLACE INTO ?:usergroup_links SET ?u', $insert);
                                $user_info = fn_get_user_info($user_id);
                                $insert['user_login'] = $user_info['user_login'];
                                $insert['firstname'] = $user_info['firstname'];
                                $insert['add_usergroup'] = fn_get_usergroup_name($bonus['value']);
                                $insert['period_total_sales'] = $val;
                                $insert['promo'] = $promotion['name'];
                                $insert['vendor'] = ($company_id == 1810) ? 'вега' : 'вегаТ';
                                $insert['user_type'] = fn_get_usergroup_name($ug_id);

                                $result[] = $insert;
                            }
                        }
                    }
                    break;
                }
            }
        }
    }

    $params['filename'] = 'grade_usergroups_feb-march.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($result, $params, '"');
    fn_print_die($result);
} elseif ($mode == 'correct_reward_points_february2') {
    //=КЭШБЭК_Начисление_2 этап_за ЯНВАРЬ
    $promotions[1810]['02'] = [63026, 63025, 63024, 63023, 63022];
    //=КЭШБЭК_Начисление_2 этап_за ФЕВРАЛЬ
    $promotions[1810]['03'] = [67136, 67135, 67134, 67133, 67132];

    //=КЭШБЭК_Начисление_2 этап_за ЯНВАРЬ
    $promotions[2058]['02'] = [63031, 63030, 63029, 63028, 63027];
    //=КЭШБЭК_Начисление_2 этап_за ФЕВРАЛЬ
    $promotions[2058]['03'] = [67131, 67130, 67129, 67128, 67127];

    foreach ($promotions as $company_id => &$company_promotions) {
        foreach ($company_promotions as &$ps) {
            foreach ($ps as &$p) {
                $p = fn_get_promotion_data($p);
            }
        }
    }

    $months = ['02','03'];

    foreach ($months as $month) {
        $year = 2024;
        $add_points = [];
        $start_ts = fn_parse_date('01/' . $month . '/'. $year);
        $end_ts = strtotime("+1 month", $start_ts) - 1;
        $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", $start_ts, $end_ts);
        $orders = db_get_fields("SELECT order_id FROM ?:orders WHERE company_id IN (?a) AND $time_condition AND is_parent_order = ?s AND group_id NOT IN (?a)", [1810, 2058], 'N', [17,18]);

        foreach ($orders as $order_id) {
            // if ($order_id == 455927) continue;
            $order_info = fn_get_order_info($order_id);

            if ($order_info['status'] == 'H') {
                if ($order_info['points_info']['reward']) continue;
                if ($order_info['points_info']['in_use']['points']) continue;

                $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $order_info['user_id'], CHANGE_DUE_ORDER);

                $is_corrected = false;
                $is_once_granted = false;
                $is_group_order = false;
                foreach ($reward_point_changes as $change) {
                    $details = unserialize($change['reason']);
                    if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id']) {
                        $is_once_granted = true;
                    }
                    if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id'] && strpos($details['correction'], 'correct_reward_points_') !== false) {
                        $is_corrected = true;
                        break;
                    }
                }
                if ($is_corrected) continue;

                $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $order_info['user_id']);
                $customer_auth = fn_fill_auth($_data, array(), false, 'C');
                foreach ($promotions[$order_info['company_id']][$month] as $promotion) {
                    $res = fn_check_promotion_conditions($promotion, $order_info, $customer_auth, $order_info['products']);
                    //$points = round($order_info['total']*$percent/100);
                    if ($res) {
                        $percent = reset($promotion['bonuses'])['value'];
                        $points = round($order_info['total']*$percent/100);

                        if (empty($points)) break;
                        $add_points[] = [
                            'user_id' => $order_info['user_id'],
                            'company_id' => $order_info['company_id'],
                            'order_id' => $order_info['order_id'],
                            'status' => $order_info['status'],
                            'order_total' => $order_info['total'],
                            'percent' => $percent,
                            'points' => $points,
                            'timestamp' => $order_info['timestamp'],
                            // 'is_once_granted' => $is_once_granted,
                            // 'is_group_order' => $is_group_order,
                        ];
                        break;
                    }
                }
            } else {
                $out_of_status[] = $order_info['order_id'];
            }
        }

        foreach ($add_points as $add) {
            $reason = array('order_id' => $add['order_id'], 'to' => $add['status'], 'correction' => 'correct_reward_points_january');
            fn_change_user_points($add['points'], $add['user_id'], serialize($reason), CHANGE_DUE_ORDER, $add['timestamp']);
        }

        fn_print_r('Текущая корректировка ' . $month, $add_points);

        $params['filename'] = "points_to_correct_$month.csv";
        $params['force_header'] = true;
        $export = fn_exim_put_csv($add_points, $params, '"');
    }

    fn_print_die('done. Out of status:', $out_of_status);
} elseif ($mode == 'correct_reward_points_february3') {
    $promotions[1810]['02'] = [67136, 67135, 67134, 67133, 67132];
    $promotions[1810]['03'] = [69683, 69682, 69681, 69680, 69679];

    $promotions[2058]['02'] = [67131, 67130, 67129, 67128, 67127];
    $promotions[2058]['03'] = [69688, 69687, 69686, 69685, 69684];

    foreach ($promotions as $company_id => &$company_promotions) {
        foreach ($company_promotions as &$ps) {
            foreach ($ps as &$p) {
                $p = fn_get_promotion_data($p);
            }
        }
    }

    $months = ['03'];

    foreach ($months as $month) {
        $year = 2024;
        $add_points = [];
        $start_ts = fn_parse_date('01/' . $month . '/'. $year);
        $end_ts = strtotime("+1 month", $start_ts) - 1;
        $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", $start_ts, $end_ts);
        $orders = db_get_fields("SELECT order_id FROM ?:orders WHERE company_id IN (?a) AND $time_condition AND is_parent_order = ?s AND group_id NOT IN (?a)", [1810, 2058], 'N', [17,18]);

        foreach ($orders as $order_id) {
            // if ($order_id == 455927) continue;
            $order_info = fn_get_order_info($order_id);

            if ($order_info['status'] == 'H') {
                if ($order_info['points_info']['reward']) continue;
                if ($order_info['points_info']['in_use']['points']) continue;

                $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $order_info['user_id'], CHANGE_DUE_ORDER);

                $is_corrected = false;
                $is_once_granted = false;
                $is_group_order = false;
                foreach ($reward_point_changes as $change) {
                    $details = unserialize($change['reason']);
                    if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id']) {
                        $is_once_granted = true;
                    }
                    if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id'] && strpos($details['correction'], 'correct_reward_points_') !== false) {
                        $is_corrected = true;
                        break;
                    }
                }

                if (!$is_corrected) continue;

                $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $order_info['user_id']);
                
                $customer_auth = fn_fill_auth($_data, array(), false, 'C');
                foreach ($promotions[$order_info['company_id']][$month] as $promotion) {
                    $res = fn_check_promotion_conditions($promotion, $order_info, $customer_auth, $order_info['products']);
                    if ($res) {
                        $percent = reset($promotion['bonuses'])['value'];
                        $points = round($order_info['total']*$percent/100);
                        if (empty($points)) break; 
                        if ($change['amount'] == $points) break; // corrected right

                        $add_points[] = [
                            'user_id' => $order_info['user_id'],
                            'company_id' => $order_info['company_id'],
                            'order_id' => $order_info['order_id'],
                            'status' => $order_info['status'],
                            'order_total' => $order_info['total'],
                            'percent' => $percent,
                            'points' => $points,
                            'timestamp' => $order_info['timestamp'],
                            'change' => $change,
                            'diff' => $points - $change['amount'],
                            // 'is_once_granted' => $is_once_granted,
                            // 'is_group_order' => $is_group_order,
                        ];
                        break;

                    }
                }
            } else {
                $out_of_status[] = $order_info['order_id'];
            }
        }

        foreach ($add_points as $add) {
            $current_value = (int) fn_get_user_additional_data(POINTS, $add['user_id']);
            fn_save_user_additional_data(POINTS, $current_value + $add['diff'], $add['user_id']);
            db_query('UPDATE ?:reward_point_changes SET amount = ?i WHERE change_id = ?i', $add['points'], $add['change']['change_id']);
        }

        fn_print_r('Текущая корректировка ' . $month, $add_points);

        $params['filename'] = "points_to_correct_$month.csv";
        $params['force_header'] = true;
        $export = fn_exim_put_csv($add_points, $params, '"');
    }

    fn_print_die('done. Out of status:', $out_of_status);
} elseif ($mode == 'correct_reward_points_february4') {
    //=КЭШБЭК_Начисление_2 этап_за ЯНВАРЬ
    $promotions[1810]['02'] = [63026, 63025, 63024, 63023, 63022];
    //=КЭШБЭК_Начисление_2 этап_за ФЕВРАЛЬ
    $promotions[1810]['03'] = [67136, 67135, 67134, 67133, 67132];
    // $promotions[1810]['04'] = [69683, 69682, 69681, 69680, 69679];

    //=КЭШБЭК_Начисление_2 этап_за ЯНВАРЬ
    $promotions[2058]['02'] = [63031, 63030, 63029, 63028, 63027];
    //=КЭШБЭК_Начисление_2 этап_за ФЕВРАЛЬ
    $promotions[2058]['03'] = [67131, 67130, 67129, 67128, 67127];
    // $promotions[2058]['04'] = [69688, 69687, 69686, 69685, 69684]; 

    foreach ($promotions as $company_id => &$company_promotions) {
        foreach ($company_promotions as &$ps) {
            foreach ($ps as &$p) {
                $p = fn_get_promotion_data($p);
            }
        }
    }

    $months = ['02', '03'];

    foreach ($months as $month) {
        $year = 2024;
        $add_points = [];
        $start_ts = fn_parse_date('01/' . $month . '/'. $year);
        $end_ts = strtotime("+1 month", $start_ts) - 1;
        $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", $start_ts, $end_ts);
        $orders = db_get_fields("SELECT order_id FROM ?:orders WHERE company_id IN (?a) AND $time_condition AND is_parent_order = ?s AND group_id NOT IN (?a)", [1810, 2058], 'N', [17,18]);

        foreach ($orders as $order_id) {
            // if ($order_id == 455927) continue;
            $order_info = fn_get_order_info($order_id);

            if ($order_info['status'] == 'H') {
                if ($order_info['points_info']['reward']) continue;
                if ($order_info['points_info']['in_use']['points']) continue;

                $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $order_info['user_id'], CHANGE_DUE_ORDER);

                $is_corrected = false;
                $is_once_granted = false;
                $is_group_order = false;
                foreach ($reward_point_changes as $change) {
                    $details = unserialize($change['reason']);
                    if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id']) {
                        $is_once_granted = true;
                    }
                    if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id'] && strpos($details['correction'], 'correct_reward_points_') !== false) {
                        $is_corrected = true;
                        break;
                    }
                }

                if (!$is_corrected) continue;

                $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $order_info['user_id']);
                
                $customer_auth = fn_fill_auth($_data, array(), false, 'C');
                foreach ($promotions[$order_info['company_id']][$month] as $promotion) {
                    $res = fn_check_promotion_conditions($promotion, $order_info, $customer_auth, $order_info['products']);
                    if ($res) {
                        $percent = reset($promotion['bonuses'])['value'];
                        $points = round($order_info['total']*$percent/100);
                        if (empty($points)) break; 
                        if ($change['amount'] == $points) break; // corrected right

                        $add_points[] = [
                            'user_id' => $order_info['user_id'],
                            'company_id' => $order_info['company_id'],
                            'order_id' => $order_info['order_id'],
                            'status' => $order_info['status'],
                            'order_total' => $order_info['total'],
                            'percent' => $percent,
                            'points' => $points,
                            'timestamp' => $order_info['timestamp'],
                            'change' => $change,
                            'diff' => $points - $change['amount'],
                            // 'is_once_granted' => $is_once_granted,
                            // 'is_group_order' => $is_group_order,
                        ];
                        break;
                    }
                }
            } else {
                $out_of_status[] = $order_info['order_id'];
            }
        }

        foreach ($add_points as &$add) {
            $current_value = (int) fn_get_user_additional_data(POINTS, $add['user_id']);
            $add['user_points_before'] = $current_value;
            $add['user_points_after'] = $current_value + $add['diff'];
            fn_save_user_additional_data(POINTS, $current_value + $add['diff'], $add['user_id']);
            db_query('UPDATE ?:reward_point_changes SET amount = ?i WHERE change_id = ?i', $add['points'], $add['change']['change_id']);
        }
        unset($add);

        fn_print_r('Текущая корректировка ' . $month, $add_points);

        $params['filename'] = "points_to_correct_$month.csv";
        $params['force_header'] = true;
        $export = fn_exim_put_csv($add_points, $params, '"');
    }

    fn_print_die('done. Out of status:', $out_of_status);
} elseif ($mode == 'correct_reward_points_april') {
    $add_field = db_quote(", DATE_FORMAT(FROM_UNIXTIME(timestamp), '%m') as `interval`, timestamp");
    $group_condition = ' GROUP BY `interval`';
    $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", fn_parse_date('01/04/2024'), fn_parse_date('01/05/2024')-1);
    $exclude_condition = db_quote(" AND ?:orders.status != 'T' AND ?:orders.status != 'I' AND ?:orders.is_parent_order != 'Y'");
    $periods = db_get_hash_multi_array("SELECT count(order_id) as count, user_id $add_field FROM ?:orders WHERE company_id IN (?a) AND $time_condition $exclude_condition GROUP BY user_id, `interval` ORDER BY timestamp", ['interval', 'user_id', 'count'], [1810, 2058]);

    foreach ($periods as &$orders) {
        $orders = array_keys($orders);
    }
    unset($orders);

    foreach ($periods as $month => $users) {
        foreach ($users as $user_id) {
            $promotions = [];
            // need to add usergroup here;
            $ugroups = [/*розница*/14776, 13858, /*опт*/ 13360, 13361];
            $ug_id = db_get_field('SELECT usergroup_id FROM ?:usergroup_links WHERE user_id = ?i AND usergroup_id IN (?a) AND status = ?s', $user_id, $ugroups, 'A');
            
            if (empty($ug_id)) continue; // что ты такое??
            $company_id = db_get_field('SELECT company_id FROM ?:users WHERE user_id = ?i', $user_id);
            if (!in_array($company_id, [1810, 2058])) continue;

            if ($month == '04') {
                // Промо март, Начисление ЮГ Самара апрель:

                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73986 круп 80к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73987 круп 150к-1.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73988 круп 250к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73978 розн 0к-0.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73979 розн 20к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73980 розн 50к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73981 розн 80к-2.5%

                // Промо март, Начисление ЮГ Тольятти апрель:

                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73989 круп 80к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73990 круп 150к-1.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73991 круп 250к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73985 розн 0к-0.5%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73984 розн 20к-1%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73983 розн 50к-2%
                // http://i-sd.ru/fadCcCyH9P.php?dispatch=promotions.update&promotion_id=73982 розн 80к-2.5%
             
                // Вега_Самара_Розничные
                $promotions[1810][13858] = [73981, 73980, 73979, 73978];
                // Вега_Самара_Крупнооптовые_клиенты
                //$promotions[1810][13360] = [73988, 73987, 73986];

                // Вега_Тольятти_Розничные клиенты
                $promotions[2058][14776] = [73982, 73983, 73984, 73985];
                // Вега_Тольятти_Крупный ОПТ
                $promotions[2058][13361] = [73991, 73990, 73989];
            }

            if (!isset($promotions[$company_id][$ug_id])) continue;

            foreach ($promotions[$company_id][$ug_id] as $promotion_id) {
                $promotion = fn_get_promotion_data($promotion_id);
                $progress_condition = fn_find_promotion_condition($promotion['conditions'], 'progress_total_paid');
                $val = fn_promotion_validate_promotion_progress($promotion['promotion_id'], $progress_condition, ['user_id' => $user_id], $promotion);

                if ($val > $progress_condition['value']) {
                    foreach($promotion['bonuses'] as $bonus) {

                        if ($bonus['bonus'] == 'give_usergroup') {
                            $already_granted = db_get_field('SELECT link_id FROM ?:usergroup_links WHERE user_id = ?i AND usergroup_id = ?i AND status = ?s', $user_id, $bonus['value'], 'A');

                            if (!$already_granted) {
                                $insert = ['user_id' => $user_id, 'usergroup_id' => $bonus['value'], 'status' => 'A'];
                                db_query('REPLACE INTO ?:usergroup_links SET ?u', $insert);
                                $user_info = fn_get_user_info($user_id);
                                $insert['user_login'] = $user_info['user_login'];
                                $insert['firstname'] = $user_info['firstname'];
                                $insert['add_usergroup'] = fn_get_usergroup_name($bonus['value']);
                                $insert['period_total_sales'] = $val;
                                $insert['promo'] = $promotion['name'];
                                $insert['vendor'] = ($company_id == 1810) ? 'вега' : 'вегаТ';
                                $insert['user_type'] = fn_get_usergroup_name($ug_id);

                                $result[] = $insert;
                            }
                        }
                    }
                    break;
                }
            }
        }
    }

    $params['filename'] = 'grade_usergroups_feb-march.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($result, $params, '"');
    fn_print_die($result);
} elseif ($mode == 'correct_reward_points_april2') {
    //=КЭШБЭК_Начисление_2 этап_за МАРТ
    $promotions[1810]['04'] = [69683, 69682, 69681, 69680, 69679];
    $promotions[2058]['04'] = [69688, 69687, 69686, 69685, 69684]; 

    foreach ($promotions as $company_id => &$company_promotions) {
        foreach ($company_promotions as &$ps) {
            foreach ($ps as &$p) {
                $p = fn_get_promotion_data($p);
            }
        }
    }

    $months = ['04'];

    foreach ($months as $month) {
        $year = 2024;
        $add_points = [];
        $start_ts = fn_parse_date('01/' . $month . '/'. $year);
        $end_ts = strtotime("+1 month", $start_ts) - 1;
        $time_condition = db_quote(" timestamp BETWEEN ?i AND ?i", $start_ts, $end_ts);
        $orders = db_get_fields("SELECT order_id FROM ?:orders WHERE company_id IN (?a) AND $time_condition AND is_parent_order = ?s AND group_id NOT IN (?a)", [1810, 2058], 'N', [17,18]);

        foreach ($orders as $order_id) {
            // if ($order_id == 455927) continue;
            $order_info = fn_get_order_info($order_id);

            if ($order_info['status'] == 'H') {
                if ($order_info['points_info']['reward']) continue;
                if ($order_info['points_info']['in_use']['points']) continue;

                $reward_point_changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE user_id = ?i AND action = ?s', $order_info['user_id'], CHANGE_DUE_ORDER);

                $is_corrected = false;
                $is_once_granted = false;
                $is_group_order = false;
                foreach ($reward_point_changes as $change) {
                    $details = unserialize($change['reason']);
                    if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id']) {
                        $is_once_granted = true;
                    }
                    if (!empty($details['order_id']) && $details['order_id'] == $order_info['order_id'] && strpos($details['correction'], 'correct_reward_points_') !== false) {
                        $is_corrected = true;
                        break;
                    }
                }
                if ($is_corrected) continue;

                $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $order_info['user_id']);
                $customer_auth = fn_fill_auth($_data, array(), false, 'C');
                foreach ($promotions[$order_info['company_id']][$month] as $promotion) {
                    $res = fn_check_promotion_conditions($promotion, $order_info, $customer_auth, $order_info['products']);
                    //$points = round($order_info['total']*$percent/100);
                    if ($res) {
                        $percent = reset($promotion['bonuses'])['value'];
                        $points = round($order_info['total']*$percent/100);

                        if (empty($points)) break;
                        $add_points[] = [
                            'user_id' => $order_info['user_id'],
                            'company_id' => $order_info['company_id'],
                            'order_id' => $order_info['order_id'],
                            'status' => $order_info['status'],
                            'order_total' => $order_info['total'],
                            'percent' => $percent,
                            'points' => $points,
                            'timestamp' => $order_info['timestamp'],
                            // 'is_once_granted' => $is_once_granted,
                            // 'is_group_order' => $is_group_order,
                        ];
                        break;
                    }
                }
            } else {
                $out_of_status[] = $order_info['order_id'];
            }
        }

        foreach ($add_points as $add) {
            $reason = array('order_id' => $add['order_id'], 'to' => $add['status'], 'correction' => 'correct_reward_points_april2');
            fn_change_user_points($add['points'], $add['user_id'], serialize($reason), CHANGE_DUE_ORDER, $add['timestamp']);
        }

        fn_print_r('Текущая корректировка ' . $month, $add_points);

        $params['filename'] = "points_to_correct_$month.csv";
        $params['force_header'] = true;
        $export = fn_exim_put_csv($add_points, $params, '"');
    }

    fn_print_die('done. Out of status:', $out_of_status);
} elseif ($mode == 'correct_reward_points_april3') {
    $changes = db_get_array('SELECT * FROM ?:reward_point_changes WHERE reason LIKE ?l', '%correct_reward_points_april2%');
    $checked_changes = $rewert = [];
    foreach ($changes as $change) {
        $hash = md5($change['reason']);
        if (!array_key_exists($hash, $checked_changes)) {
            $checked_changes[$hash] = $change;
        } else {
            $reason = unserialize($change['reason']);
            $change['order_id'] = $reason['order_id'];
            $revert[] = $change;
        }
    }
    foreach ($revert as $change) {
        fn_revert_reward_points_change($change);
    }

    $params['filename'] = "$mode.csv";
    $params['force_header'] = true;
    $export = fn_exim_put_csv($revert, $params, '"');

    fn_print_die('done');
} elseif ($mode == 'check_storages') {
    $storages = db_get_array('SELECT distinct(sp.storage_id), sp.product_id, p.items_in_package, sp.min_qty, sp.min_qty/p.items_in_package AS sell_from FROM ?:storages_products AS sp LEFT JOIN ?:products AS p ON p.product_id = sp.product_id WHERE p.items_in_package != 1 AND sp.min_qty < p.items_in_package AND sp.min_qty != 0 AND p.company_id = 45 GROUP BY sp.storage_id');
    $rest_storages = array_filter($storages, function($v) { return $v['sell_from'] != 0.5;});
    fn_print_die(count($rest_storages), $rest_storages);

    //527827  553385
    $ordered_products = db_get_array('SELECT o.order_id, o.product_id, o.amount, p.items_in_package, o.amount/p.items_in_package AS coeff, o.amount % p.items_in_package AS rest FROM ?:order_details AS o LEFT JOIN ?:products AS p ON p.product_id = o.product_id LEFT JOIN ?:orders AS ord ON ord.order_id = o.order_id WHERE o.order_id >= 527827 AND o.order_id <= 553385 AND o.amount > 0 AND p.items_in_package > 1 AND ord.company_id = 45 HAVING coeff > 1 AND rest != 0');
    $orders = fn_group_array_by_key($ordered_products, 'order_id');

    $params['filename'] = 'ordered_products.csv';
    $params['force_header'] = true;
    $export = fn_exim_put_csv($ordered_products, $params, '"');
    fn_print_die(count($orders), $export);
}

function fn_revert_reward_points_change($change, $from_order = false) {
    $user_id = $change['user_id'];
    $current_value = (int) fn_get_user_additional_data(POINTS, $user_id);
    fn_save_user_additional_data(POINTS, $current_value - $change['amount'], $user_id);
    db_query('DELETE FROM ?:reward_point_changes WHERE change_id = ?i', $change['change_id']);
    if ($from_order) {
        $details = unserialize($change['reason']);
        if ($details['order_id']) {
            db_query('DELETE FROM ?:order_data WHERE order_id = ?i AND type = ?s', $details['order_id'], POINTS);
        }
    }
}

function fn_promotion_apply_cust($zone, &$data, &$auth = NULL, &$cart_products = NULL, $promotion_id = false)
{
    static $promotions = [];
    $applied_promotions = [];
    $get_promotions_params = [
        'expand' => true,
        'zone' => $zone,
        'sort_by' => 'stop_other_rules_and_priority',
        'sort_order' => 'descasc',
        'promotion_id' => [$promotion_id],
        'get_hidden' => true,
    ];

    $storefront = Tygh::$app['storefront'];
    $get_promotions_params['storefront_id'] = $storefront->storefront_id;

    /**
     * Executes when applying promotions, before obtaining applicable promotions.
     * Allows to modify cached promotions list.
     *
     * @param string     $zone                  Promotiontion zone (catalog, cart)
     * @param array      $data                  Data array (product - for catalog rules, cart - for cart rules)
     * @param array|null $auth                  Authentication information (for cart rules)
     * @param array|null $cart_products         Cart products (for cart rules)
     * @param array      $promotions            Cached promotions
     * @param array      $applied_promotions    Applied promotions
     * @param array      $get_promotions_params Promotions search params
     */
    fn_set_hook('promotion_apply_before_get_promotions', $zone, $data, $auth, $cart_products, $promotions, $applied_promotions, $get_promotions_params);
    if (!isset($promotions[$zone])) {
        list($promotions[$zone]) = fn_get_promotions($get_promotions_params);
    }


    // If we're in cart, set flag that promotions available
    if ($zone == 'cart') {
        $_promotion_ids = !empty($data['promotions']) ? array_keys($data['promotions']) : array();
        $data['no_promotions'] = empty($promotions[$zone]);
        $data['promotions'] = array(); // cleanup stored promotions
        $data['subtotal_discount'] = 0; // reset subtotal discount (FIXME: move to another place)
        $data['has_coupons'] = false;
    }

    /**
     * Changes before applying promotion rules
     *
     * @param array  $promotions    List of promotions
     * @param string $zone          - promotiontion zone (catalog, cart)
     * @param array  $data          data array (product - for catalog rules, cart - for cart rules)
     * @param array  $auth          (optional) - auth array (for car rules)
     * @param array  $cart_products (optional) - cart products array (for car rules)
     */
    fn_set_hook('promotion_apply_pre', $promotions, $zone, $data, $auth, $cart_products);

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if ($zone == 'cart') {
            // Delete obsolete discounts
            foreach ($cart_products as $p_id => $_val) {
                $data['products'][$p_id]['discount'] = !empty($_val['discount']) ? $_val['discount'] : 0;
                $data['products'][$p_id]['promotions'] = !empty($_val['promotions']) ? $_val['promotions'] : array();
            }

            // Summarize discounts
            foreach ($cart_products as $k => $v) {
                if (!empty($v['promotions'])) {
                    foreach ($v['promotions'] as $pr_id => $bonuses) {
                        foreach ($bonuses['bonuses'] as $bonus) {
                            if (!empty($bonus['discount'])) {
                                $data['promotions'][$pr_id]['total_discount'] = (!empty($data['promotions'][$pr_id]['total_discount']) ? $data['promotions'][$pr_id]['total_discount'] : 0) + ($bonus['discount'] * $v['amount']);
                            }
                        }
                    }
                }
            }

            $data['no_promotions'] = $data['no_promotions'] && empty($data['promotions']);
        }
    }

    if (empty($promotions[$zone])) {
        return false;
    }

    Tygh::$app['session']['promotion_notices']['promotion'] = array(
        'applied' => false,
        'messages' => array()
    );

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        // Pre-check coupon
        if ($zone == 'cart' && !empty($data['pending_coupon'])) {
            fn_promotion_check_coupon($data, true);
        }
    }
    foreach ($promotions[$zone] as $promotion) {
        // Rule is valid and can be applied
        if ($zone == 'cart') {

            $data['has_coupons'] = empty($data['has_coupons']) ? fn_promotion_has_coupon_condition($promotion['conditions']) : $data['has_coupons'];
        }
        if (fn_check_promotion_conditions($promotion, $data, $auth, $cart_products)) {
            if (fn_promotion_apply_bonuses($promotion, $data, $auth, $cart_products)) {
                $applied_promotions[$promotion['promotion_id']] = $promotion;

                // Stop processing further rules, if needed
                if (YesNo::toBool($promotion['stop']) || YesNo::toBool($promotion['stop_other_rules'])) {
                    break;
                }
            }
        }
    }

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if ($zone == 'cart') {

            // Post-check coupon
            if (!empty($data['pending_coupon'])) {
                // re-check coupons if some promotion has a coupon code "contains" condition
                if (!empty($data['pending_original_coupon'])) {
                    unset($data['coupons'][$data['pending_coupon']]);
                    $data['pending_coupon'] = $data['pending_original_coupon'];
                    unset($data['pending_original_coupon']);
                    fn_promotion_check_coupon($data, true);
                }

                fn_promotion_check_coupon($data, false, $applied_promotions);
            }

            if (!empty($applied_promotions)) {
                // Display notifications for new promotions
                $_text = array();
                foreach ($applied_promotions as $v) {
                    if (!in_array($v['promotion_id'], $_promotion_ids)) {
                        $_text[] = $v['name'];
                    }
                }

                if (!empty($_text)) {
                    Tygh::$app['session']['promotion_notices']['promotion']['applied'] = true;
                    Tygh::$app['session']['promotion_notices']['promotion']['messages'][] = 'text_applied_promotions';
                    Tygh::$app['session']['promotion_notices']['promotion']['applied_promotions'] = $_text;
                }

                $data['applied_promotions'] = $applied_promotions;

                // Delete obsolete coupons
                if (!empty($data['coupons'])) {
                    foreach ($data['coupons'] as $_coupon_code => $_p_ids) {
                        foreach ($_p_ids as $_ind => $_p_id) {
                            if (!isset($applied_promotions[$_p_id])) {
                                unset($data['coupons'][$_coupon_code][$_ind]);
                            }
                        }
                        if (empty($data['coupons'][$_coupon_code])) {
                            unset($data['coupons'][$_coupon_code]);
                        }
                    }
                }

            } else {
                $data['coupons'] = array();
            }
        }
    }

    return $applied_promotions;
}


function fn_merge_product_features($target_feature, $group) {
    $target_feature_data = fn_get_product_feature_data($target_feature, true, true);
    $target_variants = array();
    foreach ($target_feature_data['variants'] as $variant) {
        $name = trim(mb_strtolower($variant['variant']));
        $target_variants[$name] = $variant;
    }
    foreach ($group as $feature_id => $feature) {
        $data = fn_get_product_feature_data($feature_id, true, true);
        foreach ($data['variants'] as $variant_id => $variant) {
            if (array_key_exists(trim(mb_strtolower($variant['variant'])), $target_variants)) {
                $target_variant = $target_variants[trim(mb_strtolower($variant['variant']))];
                $u = array('feature_id' => $target_variant['feature_id'], 'variant_id' => $target_variant['variant_id']);
                if (!(db_get_field('SELECT variant_id FROM ?:product_features_values WHERE ?w', $u)))
                    db_query("UPDATE ?:product_features_values SET ?u WHERE variant_id = ?i", $u, $variant['variant_id']);
            } else {
                db_query("UPDATE ?:product_features_values SET feature_id = ?i WHERE variant_id = ?i", $feature_id, $variant['variant_id']);
                db_query("UPDATE ?:product_feature_variants SET feature_id = ?i WHERE variant_id = ?i", $feature_id, $variant['variant_id']);
            }
        }

        fn_delete_feature($feature_id);
    }
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

function fn_echo_br($data) {
    fn_echo($data . '<br />');
}
