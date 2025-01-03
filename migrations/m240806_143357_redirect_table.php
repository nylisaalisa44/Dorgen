<?php

use yii\db\Migration;

/**
 * Class m240806_143357_redirect_table
 */
class m240806_143357_redirect_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->createTable('redirects', [
			'id' => $this->primaryKey(),
			'status' => $this->integer()->notNull()->defaultExpression(0),
			'project_id' => $this->integer(),
			'redirect_type' => $this->string()->notNull(),
			'redirect_url' => $this->string()->notNull(),
			'bot_limit' => $this->integer()->notNull(),
			'created_at' => $this->timestamp()->defaultExpression('NOW()'),
		]);

		$this->createIndex(
			'idx_redirect_id',
			'redirects',
			'id',
		);

		$this->createIndex(
			'idx_project_id',
			'redirects',
			'project_id',
		);

		$this->addForeignKey(
			'fk-redirects-project_id',
			'redirects',
			'project_id',
			'project',
			'id',
			'SET NULL'
		);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropForeignKey(
			'fk-redirects-project_id',
			'redirects'
		);

		$this->dropIndex(
			'idx_project_id',
			'redirects'
		);

		$this->dropIndex(
			'idx_redirect_id',
			'redirects'
		);

		$this->dropTable('redirects');
    }
}
