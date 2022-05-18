ALTER TABLE `cscart_storages`
ADD `nearest_delivery` varchar(1) NOT NULL DEFAULT '1',
ADD `working_time_till` varchar(8) NOT NULL DEFAULT '17:00',
ADD `saturday_shipping` varchar(8) NOT NULL DEFAULT 'Y',
ADD `sunday_shipping` varchar(8) NOT NULL DEFAULT 'Y',
ADD `monday_rule` varchar(8) NOT NULL DEFAULT 'Y';
