<?php

use Phinx\Migration\AbstractMigration;

class CorrectShowOutOfStockProductField extends AbstractMigration
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
	$sql = "UPDATE {$pr}products SET show_out_of_stock_product = 'N';";
	$this->execute($sql);
	$sql = "UPDATE {$pr}products SET show_out_of_stock_product = 'Y' WHERE company_id IN (1787,1815);";
	$this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        
    }
}
