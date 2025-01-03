<?php

use yii\db\Migration;

/**
 * Class m231005_190312_project_table
 */
class m231005_190312_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('project', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'db' => $this->string()->notNull(),
            'type' => $this->smallInteger(),
            'white' => $this->string(),
            'black' => $this->string(),
            'redirect_type' => $this->string(),
            'redirect_urls' => $this->string(),
            'allowed_bots' => $this->text(),
            'black_redir_url' => $this->string(),
            'status' => $this->smallInteger()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE NOW()'),
        ]);

        $this->createIndex(
            'index_project_id',
            'project',
            'id',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'index_project_id',
            'project'
        );

        $this->dropTable('project');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231005_190312_project_table cannot be reverted.\n";

        return false;
    }
    */
}
