UPDATE `cscart_companies` SET autoimport = 'Y' WHERE autoload_csv = 'Y';
UPDATE `cscart_companies` SET export_orders = 'C' WHERE export_order_to_csv = 'Y';
UPDATE `cscart_companies` SET export_orders = 'X' WHERE export_order_to_xml = 'Y';
