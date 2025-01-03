<?php

namespace app\components;

use app\models\Project;

class RedirectHandler {

	private array $redirectData;

	public function __construct($redirectData) {
		$this->redirectData = $redirectData;
	}

	/**
	 * Выполняет редирект или возвращает соответствующий ответ
	 * @param object $controller
	 * @return mixed
	 */
	public function execute($controller): mixed {
		switch ($this->redirectData['type']) {
			case Project::REDIRECT_301:
				return $controller->redirect($this->redirectData['url'], 301);
			case Project::REDIRECT_302:
				return $controller->redirect($this->redirectData['url']);
			case Project::REDIRECT_JAVASCRIPT:
			case Project::REDIRECT_META:
				return $controller->renderPartial('redirect', [
					'url' => $this->redirectData['url'],
					'redirect' => $this->redirectData['type']
				]);
		}
	}
}