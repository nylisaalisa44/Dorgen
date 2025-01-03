<?php

use yii\db\Migration;

/**
 * Class m240808_141739_add_to_projects_confing_columns
 */
class m240808_141739_add_to_projects_confing_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('{{%project}}', 'min_perelinks', $this->integer()->after('status')->notNull()->defaultExpression(2));
		$this->addColumn('{{%project}}', 'max_perelinks', $this->integer()->after('min_perelinks')->notNull()->defaultExpression(2));
		$this->addColumn('{{%project}}', 'farm_redirect_inner_procent', $this->integer()->after('max_perelinks')->notNull()->defaultExpression(50));
		$this->addColumn('{{%project}}', 'farm_redirect_subs', $this->boolean()->after('farm_redirect_inner_procent')->notNull()->defaultValue(false));
		$this->addColumn('{{%project}}', 'debug', $this->string()->after('farm_redirect_subs'));
		$this->addColumn('{{%project}}', 'enable_debug', $this->boolean()->after('debug')->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn('{{%project}}', 'min_perelinks');
		$this->dropColumn('{{%project}}', 'max_perelinks');
		$this->dropColumn('{{%project}}', 'farm_redirect_inner_procent');
		$this->dropColumn('{{%project}}', 'farm_redirect_subs');
		$this->dropColumn('{{%project}}', 'debug');
		$this->dropColumn('{{%project}}', 'enable_debug');
    }
}
