<?php

use yii\db\Migration;

/**
 * Class m240827_152029_add_to_project_is_xml_and_pirog_links
 */
class m240827_152029_add_to_project_is_xml_and_pirog_links extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{

		$this->addColumn('{{%project}}', 'is_html', $this->boolean()->after('max_links_white')->defaultValue(false));
		$this->addColumn('{{%project}}', 'pirog_links', $this->string()->after('is_html'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('{{%project}}', 'is_html');
		$this->dropColumn('{{%project}}', 'pirog_links');
	}
}
