<?php

use yii\db\Migration;

/**
 * Class m231112_094252_create_statistics_drop
 */
class m231112_094252_create_statistics_drop extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('statistics', [
            'id' => $this->primaryKey(),
            'domain_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'hour' => $this->integer()->notNull(),
            'bot' => $this->integer()->defaultValue(0),
            'hit' => $this->integer()->defaultValue(0),
            'bots_stat' => $this->json()
        ]);

        $this->addForeignKey(
            'fk-statistics-domain_id',
            'statistics',
            'domain_id',
            'domain',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-statistics-unique-domain-date-hour',
            'statistics',
            ['domain_id', 'date', 'hour'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-statistics-unique-domain-date-hour', 'statistics');
        $this->dropForeignKey('fk-statistics-domain_id', 'statistics');
        $this->dropTable('statistics');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231112_094252_create_statistics_drop cannot be reverted.\n";

        return false;
    }
    */
}
