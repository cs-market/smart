<?php

use Phinx\Migration\AbstractMigration;

class Autoexport extends AbstractMigration
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
	$sql = "UPDATE {$pr}companies SET `export_orders` = 'N' WHERE `export_orders` = 'X'; ";
	$this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        
    }
}
