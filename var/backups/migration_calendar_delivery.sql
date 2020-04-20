ALTER TABLE `cscart_companies` ADD `nearest_delivery` VARCHAR(1) NOT NULL DEFAULT '1' AFTER `staff_notes`;
ALTER TABLE `cscart_companies` CHANGE `tomorrow_timeslot` `working_time_till` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '17:00';
UPDATE cscart_companies SET working_time_till = '' WHERE tomorrow_rule = 'N';
ALTER TABLE `cscart_companies` DROP `tomorrow_rule`;
ALTER TABLE `cscart_companies` ADD `saturday_shipping` VARCHAR(1) NOT NULL DEFAULT 'Y' AFTER `working_time_till`;
ALTER TABLE `cscart_companies` CHANGE `saturday_rule` `monday_rule` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Y';
