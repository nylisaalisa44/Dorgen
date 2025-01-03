<?php

namespace app\models;

use app\components\BotDetector;
use Yii;
use yii\db\Exception;
use yii\db\Expression;

/**
 * This is the model class for table "statistics".
 *
 * @property int $id
 * @property int $domain_id
 * @property string $date
 * @property int $hour
 * @property int|null $bot
 * @property string|null $bots_stat
 * @property int|null $hit
 *
 * @property Domain $domain
 */
class Statistics extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'statistics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain_id', 'date', 'hour'], 'required'],
            [['domain_id', 'hour', 'bot', 'hit'], 'integer'],
            [['date'], 'safe'],
            [['domain_id', 'date', 'hour'], 'unique', 'targetAttribute' => ['domain_id', 'date', 'hour']],
            [['domain_id'], 'exist', 'skipOnError' => true, 'targetClass' => Domain::class, 'targetAttribute' => ['domain_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'domain_id' => 'Domain ID',
            'date' => 'Date',
            'hour' => 'Hour',
            'bot' => 'Bot',
            'hit' => 'Hit',
			'bots_stat' => 'Bots Stat',
        ];
    }

    /**
     * Gets query for [[Domain]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDomain()
    {
        return $this->hasOne(Domain::class, ['id' => 'domain_id']);
    }

    public static function getStatisticsByDomain($domainId)
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));

        // Запрашиваем данные
        $results = self::find()
            ->select(['date', 'SUM(hit) AS hit', 'SUM(bot) AS bot'])
            ->where(['in', 'domain_id', $domainId])
            ->andWhere(['>=', 'date', $startDate])
            ->groupBy('date')
            ->orderBy('date')
            ->asArray()
            ->all();

        //var_dump($results);

        $categories = [];
        $hitData = [];
        $botData = [];

        foreach ($results as $result) {
            $categories[] = date('d M', strtotime($result['date']));
            $hitData[] = (int)$result['hit'];
            $botData[] = (int)$result['bot'];
        }

        return [
            'categories' => $categories,
            'hitData' => $hitData,
            'botData' => $botData,
        ];
    }

    public static function getTodayHits($id)
    {
        $date = date('Y-m-d');
        return (new \yii\db\Query())
            ->select(['SUM(hit) AS hit'])
            ->from('statistics')
            ->where(['domain_id' => $id, 'date' => $date])
            ->one()['hit'] ?? 0;
    }

    public static function getYesterdayHits($id)
    {
        $date = date('Y-m-d', strtotime('yesterday'));
        return (new \yii\db\Query())
            ->select(['SUM(hit) AS hit'])
            ->from('statistics')
            ->where(['domain_id' => $id, 'date' => $date])
            ->one()['hit'] ?? 0;
    }

    public static function getTotalHits($id)
    {
        return (new \yii\db\Query())
            ->select(['SUM(hit) AS hit'])
            ->from('statistics')
            ->where(['domain_id' => $id])
            ->one()['hit'] ?? 0;
    }

    public static function getTodayBots($id)
    {
        $date = date('Y-m-d');
        return (new \yii\db\Query())
            ->select(['SUM(bot) AS bot'])
            ->from('statistics')
            ->where(['domain_id' => $id, 'date' => $date])
            ->one()['bot'] ?? 0;
    }

    public static function getYesterdayBots($id)
    {
        $date = date('Y-m-d', strtotime('yesterday'));
        return (new \yii\db\Query())
            ->select(['SUM(bot) AS bot'])
            ->from('statistics')
            ->where(['domain_id' => $id, 'date' => $date])
            ->one()['bot'] ?? 0;
    }

    public static function getTotalBots($id)
    {
        return (new \yii\db\Query())
            ->select(['SUM(bot) AS bot'])
            ->from('statistics')
            ->where(['domain_id' => $id])
            ->one()['bot'] ?? 0;
    }
	public static function getTodayBotsAll($id): string
	{
        $date = date('Y-m-d');
        $query = (new \yii\db\Query())
            ->select(['bots_stat'])
            ->from('statistics')
            ->where(['domain_id' => $id, 'date' => $date])
            ->all();

        $totalStats = [];

        foreach ($query as $row) {
            // Проверяем, что значение не null
            $botsStatJson = $row['bots_stat'] ?? '{}';

            // Декодируем JSON, если он не пуст
            if (!empty($botsStatJson)) {
                $botsStat = json_decode($botsStatJson, true);

                // Проверяем, что декодированное значение является массивом
                if (is_array($botsStat)) {
                    foreach ($botsStat as $botType => $count) {
                        if (isset($totalStats[$botType])) {
                            $totalStats[$botType] += $count;
                        } else {
                            $totalStats[$botType] = $count;
                        }
                    }
                }
            }
        }

        return BotDetector::generateBotStatisticsHtml($totalStats);
	}

	public static function getYesterdayBotsAll($id): string
	{
		$date = date('Y-m-d', strtotime('yesterday'));
        $query = (new \yii\db\Query())
            ->select(['bots_stat'])
            ->from('statistics')
            ->where(['domain_id' => $id, 'date' => $date])
            ->all();

        $totalStats = [];

        foreach ($query as $row) {
            // Проверяем, что значение не null
            $botsStatJson = $row['bots_stat'] ?? '{}';

            // Декодируем JSON, если он не пуст
            if (!empty($botsStatJson)) {
                $botsStat = json_decode($botsStatJson, true);

                // Проверяем, что декодированное значение является массивом
                if (is_array($botsStat)) {
                    foreach ($botsStat as $botType => $count) {
                        if (isset($totalStats[$botType])) {
                            $totalStats[$botType] += $count;
                        } else {
                            $totalStats[$botType] = $count;
                        }
                    }
                }
            }
        }

        return BotDetector::generateBotStatisticsHtml($totalStats);
	}

    public static function getTotalBotsAll($id): string
    {
        $query = (new \yii\db\Query())
            ->select(['bots_stat'])
            ->from('statistics')
            ->where(['domain_id' => $id])
            ->all();

        $totalStats = [];

        foreach ($query as $row) {
            $botsStat = $row['bots_stat'] ? json_decode($row['bots_stat'], true) : [];
            if (is_array($botsStat)) {
                foreach ($botsStat as $botType => $count) {
                    if (isset($totalStats[$botType])) {
                        $totalStats[$botType] += $count;
                    } else {
                        $totalStats[$botType] = $count;
                    }
                }
            }
        }

        return BotDetector::generateBotStatisticsHtml($totalStats);
    }

	public static function getChartsStatistics($groupBy, $what, $block): array
	{
		$fields = ['date', 'hour', 'SUM(hit) AS hit', 'SUM(bot) AS bot'];
		$query = self::find();

		if ($block == 'farmchart') {
			$isFarm = true;
		} else {
			$isFarm = false;
		}

		if ($groupBy == 'hour') {
			$fields[] = 'CONCAT(date, " ", LPAD(hour, 2, "0"), ":00") AS period';
			$startDate = date('Y-m-d', strtotime('-2 days'));
			$group = ['date', 'hour'];
			$order = ['date' => SORT_ASC, 'hour' => SORT_ASC];
		} elseif ($groupBy == 'month') {
			$fields[] = 'DATE_FORMAT(date, "%Y-%m") AS period';
			$startDate = date('Y-m-d', strtotime('-1 year'));
			$group = $order = 'period';
		} else {
			$startDate = date('Y-m-d', strtotime('-7 days'));
			$group = $order = 'date';
		}

		$query->select($fields);
		$query->where(['>=', 'date', $startDate]);
		$query->groupBy($group);
		$query->orderBy($order);

		if ($isFarm) {
			$query->alias('s');
			$query->leftJoin('domain d', 'd.id = s.domain_id');
			$query->andWhere(['d.farm' => 1]);
		}

		$results = $query->asArray()->all();

		$categories = [];
		$hitData = [];
		$botData = [];

		foreach ($results as $result) {
			if ($groupBy == 'hour') {
				$categories[] = date('d M H:i', strtotime($result['period']));
			} elseif ($groupBy == 'month') {
				$categories[] = date('M Y', strtotime($result['period']));
			} else {
				$categories[] = date('d M', strtotime($result['date']));
			}

			$hitData[] = (int)$result['hit'];
			$botData[] = (int)$result['bot'];
		}

		return [
			'categories' => $categories,
			'data' => $what === 'bot' ? $botData : $hitData,
		];
	}

	public static function getTableStatistics ($group, $what, $block): array {
		$startDate = new \DateTime();
		$startDate->setTime(0, 0);

		switch ($group) {
			case '30':
				$startDate->modify('-30 days');
				break;
			case '7':
				$startDate->modify('-7 days');
				break;
			case '1':
//				$startDate->modify('-1 days');
				break;
			default:
				$startDate->modify('-7 day');
				break;
		}

		$startDate = $startDate->format('Y-m-d');

		$query = self::find()
			->alias('s')
			->select(['p.name as db','d.domain', "SUM($what) as total"])
			->leftJoin('domain d', 'd.id = s.domain_id')
			->leftJoin('project p', 'p.id = d.project_id')
			->where(['>=', 'date', $startDate])
			->orderBy(['total' => SORT_DESC])
			->limit(10);

		if ($block === 'topfarm') {
			$query->andWhere(['d.farm' => 1]);
		}
		if ($block == 'topproject') {
			$groupBy = 'p.name';
		} else {
			$groupBy = 'd.domain';
		}

		$query->groupBy($groupBy);

		return $query->asArray()->all();
	}

	public static function getStatisticsSummary()
	{
		$today = date('Y-m-d');
		$yesterday = date('Y-m-d', strtotime('yesterday'));

		return (new \yii\db\Query())
			->select([
				'SUM(CASE WHEN d.farm = 0 AND date = :today THEN hit ELSE 0 END) AS active_today_hits',
				'SUM(CASE WHEN d.farm = 0 AND date = :today THEN hit ELSE 0 END) - SUM(CASE WHEN d.farm = 0 AND date = :yesterday THEN hit ELSE 0 END) AS active_hits_diff',

				'SUM(CASE WHEN d.farm = 1 AND date = :today THEN hit ELSE 0 END) AS farm_today_hits',
				'SUM(CASE WHEN d.farm = 1 AND date = :today THEN hit ELSE 0 END) - SUM(CASE WHEN d.farm = 1 AND date = :yesterday THEN hit ELSE 0 END) AS farm_hits_diff',

				'SUM(CASE WHEN d.farm = 0 AND date = :today THEN bot ELSE 0 END) AS active_today_bots',
				'SUM(CASE WHEN d.farm = 0 AND date = :today THEN bot ELSE 0 END) - SUM(CASE WHEN d.farm = 0 AND date = :yesterday THEN bot ELSE 0 END) AS active_bots_diff',

				'SUM(CASE WHEN d.farm = 1 AND date = :today THEN bot ELSE 0 END) AS farm_today_bots',
				'SUM(CASE WHEN d.farm = 1 AND date = :today THEN bot ELSE 0 END) - SUM(CASE WHEN d.farm = 1 AND date = :yesterday THEN bot ELSE 0 END) AS farm_bots_diff',

			])
			->from('statistics as s')
			->leftJoin('domain d', 'd.id = s.domain_id')
			->addParams([':today' => $today, ':yesterday' => $yesterday])
			->one();
	}

	public static function checkNonActiveDomain($farm = false): array {
		$threeHoursAgo = date('Y-m-d H:i:s', strtotime('-24 hours'));
		$date = date('Y-m-d');
		$query = (new \yii\db\Query())
			->select(['p.name', 'd.domain', 'd.id AS domain_id'])
			->from('domain d')
			->leftJoin('statistics s', 'd.id = s.domain_id AND s.date = :date AND s.hour >= :hour', [
				':date' => $date,
				':hour' => date('H', strtotime($threeHoursAgo)),
			])
			->leftJoin('project p', 'p.id = d.project_id')
			->where(['s.domain_id' => null])
			->groupBy('d.id');

		if($farm){
			$query->andWhere(['d.farm' => 1]);
		}

        return $query->all();
	}

	/**
	 * Функция для обновления массива данных в бд статистики
	 *
	 * @throws Exception
	 */
	public static function batchUpdate ($data): void {
		$values = [];
		$casesBot = [];
		$casesBotsStat = [];
		$params = [];

		foreach ($data as $index => $jsonString) {
			$item = json_decode($jsonString, true);
			if (isset($item['data'])) {
				$statsData = $item['data'];

				// Уникальные параметры
				$values[] = "(:domain_id{$index}, :date{$index}, :hour{$index}, :bot{$index} , :bots_stat{$index})";

				// Добавляем условия для обновления
				$casesBot[] = "WHEN domain_id = :domain_id{$index} AND date = :date{$index} AND hour = :hour{$index} THEN bot + 1";
				$casesBotsStat[] = "WHEN domain_id = :domain_id{$index} AND date = :date{$index} AND hour = :hour{$index} THEN JSON_SET(COALESCE(bots_stat, '{}'), '$.\"{$statsData['bot']}\"', COALESCE(JSON_EXTRACT(bots_stat, '$.\"{$statsData['bot']}\"'), '0') + 1)";

				// Параметры для привязки
				$params[":domain_id{$index}"] = $statsData['domain_id'];
				$params[":date{$index}"] = $statsData['date'];
				$params[":hour{$index}"] = $statsData['hour'];
				$params[":bot{$index}"] = 1; // Здесь мы устанавливаем значение bot равным 1
				$params[":bots_stat{$index}"] = json_encode($statsData['bots_stat']); // JSON
			}
		}

		$valuesString = implode(', ', $values);
		$casesBotString = implode(' ', $casesBot);
		$casesBotsStatString = implode(' ', $casesBotsStat);

		$sql = "
    INSERT INTO statistics (domain_id, date, hour, bot , bots_stat) 
    VALUES $valuesString
    ON DUPLICATE KEY UPDATE 
        bot = CASE 
            $casesBotString 
            ELSE bot 
        END,
		bots_stat = CASE
            $casesBotsStatString
            ELSE bots_stat
        END
";
		Yii::$app->db->createCommand($sql)->bindValues($params)->execute();
	}

    public static function batchUpdateHits($data): void {
        $values = [];
        $casesHit = [];
        $params = [];

        foreach ($data as $index => $jsonString) {
            $item = json_decode($jsonString, true);
            if (isset($item['data'])) {
                $statsData = $item['data'];

                // Уникальные параметры
                $values[] = "(:domain_id{$index}, :date{$index}, :hour{$index}, :hit{$index})";

                // Добавляем условия для обновления
                $casesHit[] = "WHEN domain_id = :domain_id{$index} AND date = :date{$index} AND hour = :hour{$index} THEN hit + 1";

                // Параметры для привязки
                $params[":domain_id{$index}"] = $statsData['domain_id'];
                $params[":date{$index}"] = $statsData['date'];
                $params[":hour{$index}"] = $statsData['hour'];
                $params[":hit{$index}"] = 1; // Увеличиваем `hit` на 1
            }
        }

        $valuesString = implode(', ', $values);
        $casesHitString = implode(' ', $casesHit);

        $sql = "
    INSERT INTO statistics (domain_id, date, hour, hit) 
    VALUES $valuesString
    ON DUPLICATE KEY UPDATE 
        hit = CASE 
            $casesHitString 
            ELSE hit 
        END
";

        Yii::$app->db->createCommand($sql)->bindValues($params)->execute();
    }


    /**
	 * Функция для обновления bots_count и status в бд редиректов
	 *
	 * @throws Exception
	 */
	public static function batchUpdateRedirects ($data): void {

		$redirectCounts = array_count_values($data);

		//Конструктор условия для обновления всех уникальных редиректов на количество повторений в массиве
		$caseExpression = 'CASE';
		foreach ($redirectCounts as $redirect_id => $count) {
			$caseExpression .= " WHEN id = $redirect_id THEN bots_count + $count";
		}
		$caseExpression .= ' ELSE bots_count END';

		//Конструктор условия для проверки всех уникальных редиректов на то, выполнились все редиректы
		$statusExpression = 'CASE';
		foreach ($redirectCounts as $redirect_id => $count) {
			$statusExpression .= " WHEN id = $redirect_id THEN IF(bots_count + $count >= bot_limit, " . Redirects::STATUS_DONE . ", status)";
		}
		$statusExpression .= ' ELSE status END';


		Yii::$app->db->createCommand()->update(
			'redirects',
			[
				'bots_count' => new Expression($caseExpression),
				'status' => new Expression($statusExpression)
			],
			['id' => array_keys($redirectCounts)]
		)->execute();
	}


}
