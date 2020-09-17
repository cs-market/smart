**********************
CS-Cart & Multi-Vendor
**********************

.. contents::
   :local:

===================
English Instruction
===================

-------------------------
Environment Configuration
-------------------------

+++++++++
Automatic
+++++++++

* Docker + Docker Compose (Linux only):

  #. Install `Docker <https://docs.docker.com/install/#supported-platforms>`_.

  #. Install `Docker Compose <https://docs.docker.com/compose/install/>`_.

  #. Use the `CS-Cart Sandbox <https://github.com/Protopopys/cscart-sandbox>`_ environment.

* Vagrant:

  #. Install `Vagrant <https://www.vagrantup.com/downloads.html>`_.

  #. Use the `Pepyakabox <https://bitbucket.org/torunar/pepyakabox>`_ environment.

* Local installation (Linux only):

  Use `Server Ansible Playbooks <https://github.com/cscart/server-ansible-playbooks>`_ to configure the environment.

++++++
Manual
++++++

Use one of the following variants:

* `MySQL Server <https://dev.mysql.com/downloads/installer/>`_ + `Apache2 <http://httpd.apache.org/docs/2.4/install.html>`_ + `PHP <http://php.net/manual/ru/install.php>`_

* `MySQL Server <https://dev.mysql.com/downloads/installer/>`_ + `Nginx <http://nginx.org/ru/docs/install.html>`_ + `PHP-FPM <http://php.net/manual/ru/install.fpm.php>`_

`CS-Cart & Multi-Vendor documentation <https://docs.cs-cart.com/latest/install>`_ has recommendations on environment configuration and software installation.

----------------------
Software Configuration
----------------------

Copy `local_conf.php.dist <local_conf.php.dist>`_ and rename the copy to *local_conf.php*. Make the changes below to *local_conf.php*:

#. Change ``PRODUCT_EDITION`` to the necessary value (``MULTIVENDOR`` or ``ULTIMATE``). ``ULTIMATE`` is the default value.

#. Change the following values for database connection:

   * ``$config['db_host']`` = the address of the database server; if the database is on the same server as your CS-Cart/Multi-Vendor installation, use ``localhost``.

   * ``$config['db_user']`` = the name of the database user.

   * ``$config['db_password']`` = the password of the database user.

   * ``$config['db_name']`` = the name of the database that will be used by this particular СS-Cart/Multi-Vendor store.

#. Specify the web server settings:

   * ``$config['http_host']`` = the URL of the store.

   * ``$config['http_path']`` = the path to the CS-Cart/Multi-Vendor installation directory relative to the root directory of the web server (must begin with ``/``).

#. Specify the driver for connecting to the database server:

   ``$config['database_backend']`` = ``mysqli`` or ``pdo``.

#. Specify where HTTP sessions must be stored:

   ``$config['session_backend']`` = ``database`` or ``redis``.

#. Specify where cache must be stored:

   ``$config['cache_backend']`` = possible values: ``database``, ``redis``, ``file``, ``sqlite``, ``xcache``, ``apc``, ``apcu``.

#. Specify the store prefix for ``redis`` cache storage:

   ``$config['store_prefix']`` = any prefix (the default value is an empty line).

#. Specify the settings for Redis, if you use it (if ``$config['cache_backend']`` = ``redis``):

   * ``$config['cache_redis_server']`` = the address of the ``redis`` server for cache storage.

   * `$config['session_redis_server']` = the address of the ``redis`` server for HTTP session storage.

#. Run ``php _tools/restore.php`` in the command line.

--------------------------
Installing Russian Version
--------------------------

#. Change the value of ``PRODUCT_BUILD`` to ``RU``.

#. Run ``php _tools/restore.php ult ru -b ru -t cs-cart-ru -a`` in the command line.

------------------
Access Credentials
------------------

Use these credentials to enter the admin panel:

* E-mail: ``admin@example.com``

* Password: ``admin``

===========================
Инструкция на русском языке
===========================

-------------------
Настройка окружения
-------------------

+++++++++++++
Автоматически
+++++++++++++

* На основе Docker + Docker Compose (только на Linux):

  1. Установите `Docker <https://docs.docker.com/install/#supported-platforms>`_.

  2. Установите `Docker Compose <https://docs.docker.com/compose/install/>`_.

  3. Используйте окружение `Cs-Cart Sandbox <https://github.com/Protopopys/cscart-sandbox>`_.

* На основе Vagrant:

  1. Установите `Vagrant <https://www.vagrantup.com/downloads.html>`_.

  2. Используйте окружение `Pepyakabox <https://bitbucket.org/torunar/pepyakabox>`_.

* Локальная установка (только на Linux):

  Используйте `Server Ansible Playbooks <https://github.com/cscart/server-ansible-playbooks>`_ для настройки окружения.

+++++++
Вручную
+++++++

Используйте один из следующих вариантов:

* `MySQL Server <https://dev.mysql.com/downloads/installer/>`_ + `Apache2 <http://httpd.apache.org/docs/2.4/install.html>`_ + `PHP <http://php.net/manual/ru/install.php>`_

* `MySQL Server <https://dev.mysql.com/downloads/installer/>`_ + `Nginx <http://nginx.org/ru/docs/install.html>`_ + `PHP-FPM <http://php.net/manual/ru/install.fpm.php>`_

В `документации CS-Cart <https://www.cs-cart.ru/docs/latest/install>`_ есть рекомендации по настройке окружения и установке программы.

--------------------
Настройка приложения
--------------------

Скопируйте файл `local_conf.php.dist <local_conf.php.dist>`_ и переименуйте копию в *local_conf.php*. Далее вносите изменения в *local_conf.php*:

#. Измените значение ``PRODUCT_EDITION`` на необходимое (``MULTIVENDOR`` или ``ULTIMATE``). Стандартное значение: ``ULTIMATE``.

#. Для соединения с базой данных измените значения:

   * ``$config['db_host']`` = адрес сервера базы данных; если база данных находится на том же сервере, что CS-Cart/Multi-Vendor, то можно использовать ``localhost``;

   * ``$config['db_user']`` = имя пользователя базы данных;

   * ``$config['db_password']`` = пароль для пользователя базы данных;

   * ``$config['db_name']`` = имя базы данных, которую будет использовать этот магазин на СS-Cart или Multi-Vendor.

#. Укажите настройки веб-сервера:

   * ``$config['http_host']`` = URL магазина;

   * ``$config['http_path']`` = путь до корневой директории CS-Cart/Multi-Vendor относительно корневой директории веб-сервера  (должен начинаться с ``/``).

#. Укажите драйвер для подключения к серверу баз данных:

   ``$config['database_backend']`` = `mysqli` или `pdo`.

#. Укажите, где хранить HTTP-сессии:

   ``$config['session_backend']``` = ``database`` или ``redis``.

#. Укажите, где хранить кэш:

   ``$config['cache_backend']`` = возможные варианты: ``database``, ``redis``, ``file``, ``sqlite``, ``xcache``, ``apc``, ``apcu``.

#. Задайте префикс магазина для хранения кэша в ``redis``:

   ``$config['store_prefix']`` = значение (стандартное значение: пустая строка).

#. Задайте настройки для Redis, если его используете (если `ё$config['cache_backend']`` = ``redis``): 

   * ``$config['cache_redis_server']`` = адрес ``redis``-сервера для хранения кэша;

   * ``$config['session_redis_server']`` = адрес ``redis``-сервера для хранения HTTP-сессий.

#. Выполните ``php _tools/restore.php`` в командной строке.

------------------------
Установка русской версии
------------------------

#. Измените значение ``PRODUCT_BUILD`` на ``RU``.

#. Выполните ``php _tools/restore.php ult ru -b ru -t cs-cart-ru -a`` в командной строке.

-------
Доступы
-------

Реквизиты для доступа в панель администратора:

* E-mail: ``admin@example.com``

* Пароль: ``admin``
