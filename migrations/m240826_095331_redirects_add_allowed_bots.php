<?php

use app\components\BotDetector;
use yii\db\Migration;

/**
 * Class m240826_095331_redirects_add_allowed_bots
 */
class m240826_095331_redirects_add_allowed_bots extends Migration
{
	public function safeUp()
	{
		$botArray = BotDetector::getBotArray();
		$botIndexes = array_keys($botArray);
		$botIndexesString = implode(', ', $botIndexes);
		$this->addColumn('{{%redirects}}', 'allowed_bots', $this->string()->after('bots_count')->defaultValue($botIndexesString));
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('{{%redirects}}', 'allowed_bots');
	}
}
