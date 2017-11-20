<?php

use yii\db\Schema;

class m140622_111545_add_name_to_image_table extends \yii\db\Migration
{
    public function up()
    {
         $this->addColumn('{{%image}}', 'name', $this->string(80));

    }

    public function down()
    {
        $this->dropColumn('{{%image}}', 'name');
    }
}
