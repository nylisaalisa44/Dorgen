<?php

use yii\db\Migration;

/**
 * Class m240721_115144_cf_accounts
 */
class m240721_115144_cf_accounts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->createTable('cf_accounts', [
			'id' => $this->primaryKey(),
			'login' => $this->string()->notNull(),
			'password' => $this->string()->notNull(),
			'token' => $this->string()->notNull(),
			'info' => $this->text(),
		]);

		$this->createIndex(
			'idx_cf_accounts_id',
			'cf_accounts',
			'id',
		);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropIndex(
			'idx_cf_accounts_id',
			'cf_accounts'
		);

		$this->dropTable('cf_accounts');
    }

}
