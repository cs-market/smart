# Краткий инструктаж

## Разворачивание репозитория

1. Ознакомимся с [матчастью](https://www.cs-cart.ru/docs/latest/install/)
2. Клонируем репозиторий в папку веб-сервера
3. Восстанавливаем бекапы var/backups/last.sql.zip и var/backups/structure.sql.zip
4. Создаем в корне local_conf.php с соответствующими локальными настройками магазина
5. Изменить адрес витрины в Администрирование->Магазины->Сады России

## Образец config.local.php

```php
<?php
$config['db_name'] = 'your_db_name';
$config['db_user'] = 'your_db_user';
$config['db_password'] = 'your_pass';
$config['http_host'] = 'localhost';
$config['http_path'] = '/'; // fill by necessity
$config['https_host'] = 'localhost';
$config['https_path'] = '/'; // fill by necessity
```

## Работа с проектом

1. Запрещается изменять файлы ядра и темы responsive
2. Все писать в отдельном модуле / модуле smart_distribution / теме (по обстоятельствам)
3. Если ваша корректировка подразумевает изменение базы данных на сервере, необходимо в добавлять в коммите файл var/backups/migration_[смысловая_нагрузка].sql
4. Крайне рекомендуется использовать [EditorConfig](https://EditorConfig.org)

## Префиксы коммитов:

```
[!] - исправление ошибки
[*] - изменение
[+] - новый функционал
[-] - выпелили функционал
```