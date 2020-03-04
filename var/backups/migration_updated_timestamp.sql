ALTER TABLE `cscart_users` ADD `last_update` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `timestamp`;
UPDATE cscart_users SET last_update = cscart_users.timestamp WHERE 1 