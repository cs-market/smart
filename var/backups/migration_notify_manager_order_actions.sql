ALTER TABLE `cscart_companies` ADD `notify_manager_order_create` char(1) NOT NULL DEFAULT 'Y';
ALTER TABLE `cscart_companies` ADD `notify_manager_order_update` char(1) NOT NULL DEFAULT 'Y';
ALTER TABLE `cscart_companies` ADD `notify_manager_order_insufficient` char(1) NOT NULL DEFAULT 'Y';