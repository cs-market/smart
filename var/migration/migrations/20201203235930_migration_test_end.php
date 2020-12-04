<?php

use Phinx\Migration\AbstractMigration;

class MigrationTestEnd extends AbstractMigration
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
            // sql style
        $sql = 'DROP TABLE `test_user`';
        $this->execute($sql);

        // Query Buider style
        $this->dropTable('test_user_qbs');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}