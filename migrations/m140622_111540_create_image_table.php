<?php

use yii\db\Schema;

class m140622_111540_create_image_table extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('image', [
            'id' => 'pk',
            'filePath' => 'VARCHAR(400) NOT NULL',
            'itemId' => 'int(20) NOT NULL',
            'isMain' => 'int(1)',
            'modelName' => 'VARCHAR(150) NOT NULL',
            'urlAlias' => 'VARCHAR(400) NOT NULL',
        ]);

    }

    public function down()
    {
        echo "m140622_111540_create_image_table cannot be reverted.\n";

        return false;
    }
}
