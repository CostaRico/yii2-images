<?php

class m140622_111540_create_image_table extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%image}}', [
            'id' => $this->primaryKey(),
            'filePath' => $this->string(400)->notNull(),
            'itemId' => $this->integer(),
            'isMain' => $this->boolean(),
            'modelName' => $this->string(150)->notNull(),
            'urlAlias' => $this->string(400)->notNull(),
        ]);

    }

    public function down()
    {
        $this->dropTable('{{%image}}');
    }
}
