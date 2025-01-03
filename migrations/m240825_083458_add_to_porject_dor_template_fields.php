<?php

use yii\db\Migration;

/**
 * Class m240825_083458_add_to_porject_dor_template_fields
 */
class m240825_083458_add_to_porject_dor_template_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%project}}', 'use_project_links_white', $this->boolean()->after('use_subs_white')->defaultValue(false));
        $this->addColumn('{{%project}}', 'min_snippets_links_white', $this->integer()->after('max_snippets_white')->defaultValue(4));
        $this->addColumn('{{%project}}', 'max_snippets_links_white', $this->integer()->after('min_snippets_links_white')->defaultValue(6));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%project}}', 'use_project_links_white');
        $this->dropColumn('{{%project}}', 'min_snippets_links_white');
        $this->dropColumn('{{%project}}', 'max_snippets_links_white');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240825_083458_add_to_porject_dor_template_fields cannot be reverted.\n";

        return false;
    }
    */
}
