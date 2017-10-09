<?php

use yii\db\Migration;

class m171009_091124_create_result_parse extends Migration
{
    public function safeUp()
    {
        $this->createTable('result_parse', [
            'id' => $this->primaryKey(),
            'json' => $this->text(),
            'create_date' => 'datetime DEFAULT NOW()',
        ]);
    }

    public function down()
    {
        $this->dropTable('result_parse');

        return false;
    }
}
