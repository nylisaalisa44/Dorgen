<?php

use yii\db\Migration;

/**
 * Class m240902_100940_alter_table_project_text_db_column
 */
class m240902_100940_alter_table_project_text_db_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('project', 'db', $this->text()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('project', 'db', $this->string()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240902_100940_alter_table_project_text_db_column cannot be reverted.\n";

        return false;
    }
    */
}
