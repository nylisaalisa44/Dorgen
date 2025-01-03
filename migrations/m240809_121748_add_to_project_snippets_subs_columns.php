<?php

use yii\db\Migration;

/**
 * Class m240809_121748_add_to_project_snippets_subs_columns
 */
class m240809_121748_add_to_project_snippets_subs_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
	public function safeUp()
	{
		$this->addColumn('{{%project}}', 'min_snippets', $this->integer()->after('farm_redirect_subs')->notNull()->defaultExpression(2));
		$this->addColumn('{{%project}}', 'max_snippets', $this->integer()->after('min_snippets')->notNull()->defaultExpression(2));
		$this->addColumn('{{%project}}', 'use_subs', $this->boolean()->after('max_snippets')->defaultValue(false));
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('{{%project}}', 'min_snippets');
		$this->dropColumn('{{%project}}', 'max_snippets');
		$this->dropColumn('{{%project}}', 'use_subs');
	}
}
