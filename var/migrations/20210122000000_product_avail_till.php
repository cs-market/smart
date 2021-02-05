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
	$sql = "ALTER TABLE {$pr}products ADD `avail_till` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `avail_since`;";
	$this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        
    }
}