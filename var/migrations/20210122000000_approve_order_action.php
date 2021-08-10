<?php

use Phinx\Migration\AbstractMigration;

class ApproveOrderAction extends AbstractMigration
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
       $sql = "ALTER TABLE {$pr}users ADD `approve_order_action` char(1) NOT NULL DEFAULT 'D';";
       $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        
    }
}
