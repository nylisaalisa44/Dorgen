<?php

namespace app\commands;

use app\components\Cloudflare;
use Exception;
use yii\base\BaseObject;

class CloudflareJob extends BaseObject implements \yii\queue\JobInterface
{
	public Cloudflare $cf;
	public string $type;
	public array $domains;

	/**
	 * @throws Exception
	 */
	public function execute($queue)
	{
		if ($this->type == 'test') {
			$this->cf->test();
		} elseif ($this->type == 'change_ip') {
			$this->cf->changeIp();
		} else {
			$this->cf->authorize();

			if (empty($this->domains)) {
				$this->cf->output("Empty");
				$this->cf->stop();
			}

			$this->cf->addDomains($this->domains);
			$this->cf->stop();
		}
	}
}