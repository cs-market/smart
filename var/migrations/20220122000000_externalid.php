<?php

use Phinx\Migration\AbstractMigration;

class Externalid extends AbstractMigration
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
	$sql = "ALTER TABLE {$pr}categories CHANGE `external_id` `external_id` VARCHAR(4098) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; ";
	$this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        
    }
}
