<?php

use Phinx\Migration\AbstractMigration;

class AddUserDeliveryDate extends AbstractMigration
{
    public function up()
    {
        $users = $this->table('users');
        $users->addColumn('delivery_date', 'string', array('limit' => 7))
        ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $users = $this->table('users');
        $users->removeColumn('delivery_date')
        ->save();
    }
}
