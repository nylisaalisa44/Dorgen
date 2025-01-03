<?php

namespace app\components;

use yii\web\UrlManager;
use yii\web\UrlRule;

class CustomUrlManager extends UrlManager
{
	public function parseRequest($request)
	{
		$pathInfo = $request->getPathInfo();

		$originalRules = $this->rules;

		$temporaryRules = [];

		if (!preg_match('/^(combine-api|panel|combine-panel)/', $pathInfo)) {
			$temporaryRules = [
				new UrlRule([
					'pattern' => '<slug:.+>',
					'route' => 'site/index',
					'defaults' => [],
				]),
			];
		}

		$this->rules = array_merge($originalRules, $temporaryRules);

		return parent::parseRequest($request);
	}
}