ALTER TABLE `cscart_companies` 
    ADD `period_start` varchar(8) NOT NULL DEFAULT '',
    ADD `period_finish` varchar(8) NOT NULL DEFAULT '', 
    ADD `period_step` varchar(8) NOT NULL DEFAULT '';

ALTER TABLE `cscart_orders` ADD `delivery_period` varchar(30) NOT NULL default '';
