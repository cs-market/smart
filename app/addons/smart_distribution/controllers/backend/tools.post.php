<?php


if ($mode =='monolith' && !empty($action)) {
    fn_print_die(fn_monolith_generate_xml($action));
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
} elseif ($mode == 'cleanup_orders') {
    $max = 87648;

    $current = ($_SESSION['current_order_id']) ? $_SESSION['current_order_id'] : 0;
    $current = 50001;
    $max = 50003;

    $some_info = db_get_hash_single_array("SELECT data, order_id FROM ?:order_data WHERE type = 'L' AND order_id > ?i AND order_id <= ?i ORDER BY order_id limit 500", array('order_id', 'data'), $current, $max);
    $some_info = array_map('unserialize', $some_info);
    fn_print_die($some_info);
    
    $payment_info = db_get_hash_single_array("SELECT data, order_id FROM ?:order_data WHERE type = 'G' AND order_id > ?i AND order_id <= ?i ORDER BY order_id limit 500", array('order_id', 'data'), $current, $max);

    $payment_info = array_map('unserialize', $payment_info);

    foreach ($payment_info as $order_id => &$info) {
        if ($info[0]['products'])
        foreach ($info[0]['products'] as &$product) {
            unset($product['main_pair']);   
        }
        unset($info[0]['package_info'], $info[0]['shippings']);
    }

    $payment_info = array_map('serialize', $payment_info);
    foreach ($payment_info as $order_id => $data) {
        db_query("UPDATE ?:order_data SET data = ?s WHERE order_id = ?i AND type = 'G'", $data, $order_id);
    }

    $_SESSION['current_order_id'] = max(array_keys($payment_info));
    if ($_SESSION['current_order_id'] < $max) {
        fn_print_r($_SESSION['current_order_id']);
        fn_redirect('tools.cleanup_orders');
    } else {
        unset($_SESSION['current_order_id']);
        db_query("OPTIMIZE TABLE `cscart_order_data` ");
        fn_print_die('done');
    }
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
} elseif ($mode == 'fix_user_price') {
    $users = db_get_fields('SELECT user_id FROM ?:users');
    $products = db_get_fields('SELECT product_id FROM ?:products');
    $count[] = db_get_field('SELECT count(*) FROM ?:user_price WHERE user_id NOT IN (?a)', $users);
    $res = db_query('DELETE FROM ?:user_price WHERE user_id NOT IN (?a)', $users);
    $count[] = db_get_field('SELECT count(*) FROM ?:user_price WHERE product_id NOT IN (?a)', $products);
    db_query('DELETE FROM ?:user_price WHERE product_id NOT IN (?a)', $products);

    $user_price_products = db_get_hash_multi_array('SELECT distinct(up.product_id), p.company_id FROM ?:user_price AS up LEFT JOIN ?:products AS p ON p.product_id = up.product_id', ['company_id', 'product_id', 'product_id']);
    foreach ($user_price_products as $company_id => $products) {
        $company_users = db_get_fields('SELECT user_id FROM ?:users WHERE company_id = ?i', $company_id);
        db_query('DELETE FROM ?:user_price WHERE product_id IN (?a) AND user_id NOT IN (?a)', $products, $company_users);
    }

    fn_print_die('stop');
} elseif ($mode == 'remove_user_price') {
    $users = db_get_fields('SELECT user_id FROM ?:users WHERE company_id = ?i AND user_type = "C"', 1824);

    // db_query('DELETE FROM ?:user_price WHERE product_id IN (?a)', $products);
    $prices = db_get_fields('SELECT distinct(user_id) FROM ?:user_price WHERE user_id IN (?a)', $users);
    $diff = array_diff($users, $prices);
    fn_print_die($diff, count($users), count($prices));
    fn_print_die('stop');
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
} elseif ($mode == 'remove_vega_images') {
    fn_delete_image_pairs($pid, 'product');
    $product_ids = db_get_fields('SELECT product_id FROM ?:products WHERE company_id = ?i', 1810);
    foreach ($product_ids as $pid) { fn_delete_image_pairs($pid, 'product'); }
    fn_print_die('Fin');
} elseif ($mode == 'remove_univita_products') {
    $product_ids = db_get_fields('SELECT product_id FROM ?:products WHERE updated_timestamp = 0 AND company_id = 1787');
    foreach ($product_ids as $product_id) {
        fn_delete_product($product_id);
    }
    fn_print_die(count($product_ids));
} elseif ($mode == 'remove_univita_images') {
    $product_ids = db_get_fields('SELECT product_id FROM ?:products WHERE company_id = 1787');
    foreach ($product_ids as $product_id) {
        fn_delete_image_pairs($product_id, 'product');
    }
    fn_print_die(count($product_ids));
} elseif ($mode == 'features_maintenance') {
    //delete fantom products
    $pids = db_get_fields('SELECT product_id FROM ?:products');
    $iteration = 0;
    foreach ($pids as $product_id) {
        $iteration ++;
        $data = fn_get_product_data($product_id, $auth);
        if (empty($data)) {
            fn_delete_product($product_id);
            fn_print_r($iteration, $product_id);
        }
    }

    $condition = '';
    if (!empty($action)) {
        $condition = db_quote(' WHERE company_id = ?i', $action);
    }
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
        $feature_groups = fn_array_group($features, 'description');
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
    $all_features = db_get_fields("SELECT feature_id FROM ?:product_features;");
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
} elseif ($mode == 'remove_mikale_images') {
    $product_ids = db_get_fields('SELECT product_id FROM ?:products WHERE company_id = ?i', 1815);
    foreach ($product_ids as $pid) { fn_delete_image_pairs($pid, 'product'); }
    fn_print_die('Fin');
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
    
} elseif ($mode == 'remove_vegat_products') {
    $company_id = 2058;
    $product_ids = db_get_fields("SELECT product_id FROM ?:products WHERE company_id = ?i AND timestamp > ?i", $company_id, 1630580300);
    $file1 = 'products.csv';
    $content = fn_exim_get_csv(array(), $file1, array('validate_schema'=> false, 'delimiter' => ';') );
    $sku = array_column($content, 'Product code');
    $product_ids = db_get_fields("SELECT product_id FROM ?:products WHERE company_id = ?i AND product_code IN (?a)", $company_id, $sku);
    fn_print_die($sku);

    // foreach ($product_ids as $product_id) {
    //     fn_delete_product($product_id);
    // }
    // fn_print_die(count($product_ids));
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
                        $lost[] = array(
                            'company_id' => $company_id,
                            'company' => $company,
                            'product_id' => $p['product_id'],
                            'product_code' => $p['product_code'],
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
