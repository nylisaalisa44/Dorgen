<?php

use yii\db\Migration;

/**
 * Class m240812_180255_add_to_project_dor_template_config
 */
class m240812_180255_add_to_project_dor_template_config extends Migration
{
	/**
	 * 'max_snip' => $project->max_snippets_dor,
	 * 'min_snip' => $project->min_snippets_dor,
	 * 'min_links' => $project->min_links_dor,
	 * 'max_links' => $project->max_links_dor,
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->addColumn('{{%project}}', 'lang_white', $this->string()->after('enable_debug')->notNull()->defaultValue('en'));
		$this->addColumn('{{%project}}', 'rand_anchor_white', $this->boolean()->after('lang_white')->defaultValue(false));
        $this->addColumn('{{%project}}', 'use_subs_white', $this->boolean()->after('rand_anchor_white')->defaultValue(false));
		$this->addColumn('{{%project}}', 'max_snippets_white', $this->integer()->after('use_subs_white')->defaultValue(10));
		$this->addColumn('{{%project}}', 'min_snippets_white', $this->integer()->after('max_snippets_white')->defaultValue(5));
		$this->addColumn('{{%project}}', 'min_links_white', $this->integer()->after('min_snippets_white')->defaultValue(3));
		$this->addColumn('{{%project}}', 'max_links_white', $this->integer()->after('min_links_white')->defaultValue(5));
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('{{%project}}', 'lang_white');
		$this->dropColumn('{{%project}}', 'rand_anchor_white');
		$this->dropColumn('{{%project}}', 'use_subs_white');
		$this->dropColumn('{{%project}}', 'max_snippets_white');
		$this->dropColumn('{{%project}}', 'min_snippets_white');
		$this->dropColumn('{{%project}}', 'min_links_white');
		$this->dropColumn('{{%project}}', 'max_links_white');
	}
}
