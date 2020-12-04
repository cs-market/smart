<?php

use Phinx\Migration\AbstractMigration;

class MigrationTestStart extends AbstractMigration
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
        $sql = 'CREATE TABLE `test_user` (
                `id` int(11) NOT NULL,
                `login` varchar(255) NOT NULL,
                `email` varchar(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8';
        $this->execute($sql);

        // Query Buider style
        $table = $this->table('test_user_qbs');
        $table->addColumn('login', 'string', ['limit' => 255])
        ->addColumn('email', 'string', ['limit' => 255])
        ->create();

        // alter table
        $users = $this->table('test_user_qbs');
        $users->addColumn('password', 'string', array('limit' => 40))
        ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // sql style
        $sql = 'DROP TABLE `test_user`';
        $this->execute($sql);

        // Query Buider style
        $this->dropTable('test_user_qbs');
    }
}