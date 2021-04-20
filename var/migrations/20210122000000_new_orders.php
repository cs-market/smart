<?php

use Phinx\Migration\AbstractMigration;

class ProductAvailTill extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */

    /**
     * Migrate Up.
     */
    public function up()
    {
	$options = $this->adapter->getOptions();
	$pr = $options['prefix'];
	$sql = "DROP TABLE {$pr}new_orders";
	$this->execute($sql);
	$this->execute("UPDATE {$pr}products SET min_qty = 0 WHERE min_qty IS NULL; ");
	$this->execute("UPDATE {$pr}products SET max_qty = 0 WHERE max_qty IS NULL; ");
	$this->execute("UPDATE {$pr}products SET amount = 0 WHERE amount IS NULL; ");
	$this->execute("UPDATE {$pr}products SET qty_step = 0 WHERE qty_step IS NULL; ");

	$sql = "ALTER TABLE `{$pr}products` CHANGE `amount` `amount` FLOAT NOT NULL DEFAULT '0', CHANGE `qty_step` `qty_step` FLOAT NOT NULL DEFAULT '0', CHANGE `max_qty` `max_qty` FLOAT NOT NULL DEFAULT '0', CHANGE `min_qty` `min_qty` FLOAT NOT NULL DEFAULT '0'";
	$this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        
    }
}