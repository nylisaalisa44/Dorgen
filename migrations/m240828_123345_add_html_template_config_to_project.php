<?php

use yii\db\Migration;

/**
 * Class m240828_123345_add_html_template_config_to_project
 */
class m240828_123345_add_html_template_config_to_project extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('{{%project}}', 'html_min_links', $this->integer()->after('pirog_links')->defaultValue(5));
		$this->addColumn('{{%project}}', 'html_max_links', $this->integer()->after('html_min_links')->defaultValue(5));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn('{{%project}}', 'html_min_links');
		$this->dropColumn('{{%project}}', 'html_max_links');
    }
}
