<?php

use yii\db\Migration;

/**
 * Class m240808_154726_add_to_redirects_type_entity_id_columns
 */
class m240808_154726_add_to_redirects_type_entity_id_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
	public function safeUp()
	{
		$this->addColumn('{{%redirects}}', 'type', $this->integer()->after('bot_limit')->notNull());
		$this->addColumn('{{%redirects}}', 'entity_id', $this->integer()->after('type')->notNull()->defaultValue(0));
		$this->addColumn('{{%redirects}}', 'bots_count', $this->integer()->after('entity_id')->notNull()->defaultValue(0));

		$this->dropForeignKey(
			'fk-redirects-project_id',
			'redirects'
		);

		$this->dropIndex(
			'idx_project_id',
			'redirects'
		);
		$this->dropColumn('{{%redirects}}', 'project_id');

	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('{{%redirects}}', 'type');
		$this->dropColumn('{{%redirects}}', 'entity_id');
		$this->dropColumn('{{%redirects}}', 'bots_count');

		$this->addColumn('{{%redirects}}', 'project_id', $this->integer()->after('status'));

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
}
