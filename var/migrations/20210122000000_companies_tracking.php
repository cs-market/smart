<?php

use Phinx\Migration\AbstractMigration;

class CompaniesTracking extends AbstractMigration
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
    	$sql = "ALTER TABLE {$pr}companies ADD `tracking` VARCHAR(1) NOT NULL DEFAULT 'B'";
    	$this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        
    }
}
