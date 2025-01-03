<?php

use yii\db\Migration;

/**
 * Class m240903_150629_add_index_to_domain_in_domain_table
 */
class m240903_150629_add_index_to_domain_in_domain_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->createIndex(
			'idx_domain_domain',
			'{{domain}}',
			'domain',
		);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropIndex(
			'idx_domain_domain',
			'{{domain}}'
		);
    }

}
