<?php

use yii\db\Migration;

/**
 * Class m231005_190304_domains_table
 */
class m231005_190400_domains_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('domain', [
            'id' => $this->primaryKey(),
            'domain' => $this->string(),
            'metka' => $this->string(),
            'project_id' => $this->integer()->notNull(),
            'farm' => $this->boolean()->defaultValue(false),
            'redirect_type' => $this->string(),
            'redirect_urls' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE NOW()'),
        ]);

        $this->createIndex(
            'index_domain_id',
            'domain',
            'id',
        );

        $this->addForeignKey(
            'fk-domain-project_id',
            'domain',
            'project_id',
            'project',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-domain-project_id',
            'domain');

        $this->dropIndex(
            'index_domain_id',
            'domain'
        );

        $this->dropTable('domain');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231005_190304_domains_table cannot be reverted.\n";

        return false;
    }
    */
}
