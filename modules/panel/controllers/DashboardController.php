<?php

namespace app\modules\panel\controllers;

use app\models\Domain;
use app\models\Project;
use app\models\Statistics;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;

class DashboardController extends Controller {

	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'only' => ['index'],
				'rules' => [
					[
						'actions' => ['index'],
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		];
	}


	/**
	 * @throws Exception
	 */
	public function actionIndex()
	{
		$data = $this->getStatisticsData();
		return $this->render('index', $data);
	}

	public function actionGetStatistics($what, $group, $block)
	{
		$statistics = Statistics::getChartsStatistics($group, $what, $block);

		return $this->asJson([
			'categories' => $statistics['categories'],
			'data' => $statistics['data'],
		]);
	}

	public function actionGetStatisticsTop($what, $group, $block)
	{
		$statistics = Statistics::getTableStatistics($group, $what, $block);

		$tableHtml = $this->renderPartial('_top_statistics_table', [
			'data' => $statistics,
			'what' => $what,
			'block' => $block,
			'group' => $group,
		]);

		$header = $this->generateHeader($block, $group);

		return $this->asJson([
			'header' => $header,
			'content' => $tableHtml,
		]);
	}

	public function actionUpdateStats()
	{
		$data = $this->getStatisticsData();
		return $this->asJson($data);
	}

	protected function getStatisticsData()
	{
		// Выполняем запрос для получения данных о доменах
		$query = Domain::find()
			->alias('d')
			->select([
				'SUM(CASE WHEN d.farm = 1 THEN 1 ELSE 0 END) AS farm_domain_count',
				'SUM(CASE WHEN d.farm = 0 THEN 1 ELSE 0 END) AS active_domain'
			])
			->leftJoin('project p', 'p.id = d.project_id');
//			->where(['p.status' => Project::STATUS_ACTIVE]);

		$counts = $query->asArray()->one();

		$result = Statistics::getStatisticsSummary();

		return [
			'farm_domain_count' => $counts['farm_domain_count'],
			'active_domain' => $counts['active_domain'],

			'active_today_hits' => $result['active_today_hits'],
			'active_hits_diff' => $result['active_hits_diff'],

			'farm_today_hits' => $result['farm_today_hits'],
			'farm_hits_diff' => $result['farm_hits_diff'],

			'active_today_bots' => $result['active_today_bots'],
			'active_bots_diff' => $result['active_bots_diff'],

			'farm_today_bots' => $result['farm_today_bots'],
			'farm_bots_diff' => $result['farm_bots_diff'],
		];
	}

	private function generateHeader($block, $group)
	{
		$headers = [
			'topbot' => 'Топ по ботам',
			'topfarm' => 'Топ по ферме',
			'tophit' => 'Топ по трафику',
			'topproject' => 'Топ проектов',
		];

		$timeFrames = [
			'1' => 'за сегодня',
			'7' => 'за неделю',
			'30' => 'за 30 дней',
		];

		return $headers[$block] . ' ' . $timeFrames[$group];
	}


}