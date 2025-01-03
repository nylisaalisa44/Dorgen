<?php

namespace app\models;

use app\components\BotDetector;
use app\components\Helpers;
use app\components\Middleware;
use app\components\RabbitMQ;
use app\components\Redis;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "redirects".
 *
 * @property int $id
 * @property int $status
 * @property string $redirect_type
 * @property string $redirect_url
 * @property int $bot_limit
 * @property int $type
 * @property int $entity_id
 * @property int $bots_count
 * @property string|null $allowed_bots
 * @property string $created_at
 */
class Redirects extends \yii\db\ActiveRecord
{
    const STATUS_WAIT = 0;
    const STATUS_IN_WORK = 1;
    const STATUS_DONE = 2;

    const REDIRECT_301 = 301;
    const REDIRECT_302 = 302;
    const REDIRECT_META = 1;
    const REDIRECT_JAVASCRIPT = 2;

    const TYPE_GLOBAL = 0;
    const TYPE_PROJECT = 1;
    const TYPE_DOMAIN = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'redirects';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'bot_limit', 'type', 'entity_id', 'bots_count'], 'integer'],
            [['redirect_type', 'redirect_url', 'bot_limit', 'allowed_bots'], 'required'],
            [['created_at', 'allowed_bots'], 'safe'],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Статус',
            'redirect_type' => 'Тип редиректа',
            'redirect_url' => 'Redirect Url',
            'bot_limit' => 'Bot Limit',
            'type' => 'Уровень редиректа',
            'entity_id' => 'Entity ID',
            'bots_count' => 'Bots Count',
            'allowed_bots' => 'Боты',
            'created_at' => 'Created At',
        ];
    }

	public function beforeDelete(): bool {
		if (parent::beforeDelete()) {
			$key = "redirect_{$this->type}_{$this->entity_id}_{$this->id}";
			Redis::delete($key);
			return true;
		} else {
			return false;
		}
	}

    public function getStatusName()
    {
        return ArrayHelper::getValue(self::getStatusesArray(), $this->status);
    }

    public static function getStatusesArray(): array
    {
        return [
            self::STATUS_WAIT => 'wait',
            self::STATUS_IN_WORK => 'in_work',
            self::STATUS_DONE => 'done',
        ];
    }

    public function getTypeName()
    {
        return ArrayHelper::getValue(self::getTypesArray(), $this->type);
    }

    public static function getTypesArray(): array
    {
        return [
            self::TYPE_GLOBAL => 'Global',
            self::TYPE_PROJECT => 'Project',
            self::TYPE_DOMAIN => 'Domain',
        ];
    }

    public function getEntity()
    {
        return match ($this->type) {
            self::TYPE_PROJECT => $this->hasOne(Project::class, ['id' => 'entity_id'])->one(),
            self::TYPE_DOMAIN => $this->hasOne(Domain::class, ['id' => 'entity_id'])->one(),
            self::TYPE_GLOBAL => null,
            default => throw new \InvalidArgumentException('Unknown order type'),
        };
    }

	/**
	 * @throws \Exception
	 */
	public static function checkRedirect(Middleware $middleware): array
    {
        $isRedirect = false;
        $redirect_id = 0;

        // Попытка найти редирект по домену
        $redirect = self::findRedirect($middleware->domain->id, Redirects::TYPE_DOMAIN);

        // Если редирект не найден, попробовать найти по проекту
        if (!$redirect) {
            $redirect = self::findRedirect($middleware->domain->project->id, Redirects::TYPE_PROJECT);
        }

        // Если редирект не найден и есть фарм, попробовать найти глобальный редирект, проверяем шанс редиректа
        if (!$redirect && $middleware->domain->farm) {
            if (Helpers::calculateProbability($_SERVER['FARM_REDIRECT_RATE'])) {
                $redirect = self::findRedirect(null, Redirects::TYPE_GLOBAL);
            }

            // Если не найден редирект глобальный, определяем что ферма и делаем % шанс редиректа на другой домен фермы
            if (!$redirect && Helpers::calculateProbability($middleware->domain->project->farm_redirect_inner_procent)) {
                $isRedirect = true;
                $middleware->redirect = true;
                $domains = Domain::find()->select('domain')->where(['farm' => 1])->column();
                $randomDomain = $domains[array_rand($domains)];

                if ($middleware->domain->project->use_subs) {
                    $url = "https://{w6}." . $randomDomain . '/{w6}';
                } else {
                    $url = "https://" . $randomDomain . '/{w6}';
                }

                $middleware->redirectData = [
                    'type' => 301,
                    'url' => Helpers::formatRegexUrl($url)
                ];
            }
        }

        // Обработка найденного редиректа или фарма
        if ($redirect && BotDetector::isAllowedBot($redirect->allowed_bots)) {
            $isRedirect = true;
            $redirect_id = $redirect->id;
            $middleware->redirect = true;
            $middleware->redirectData = $middleware->getRedirectDataForRedirect($redirect);
        }

        return [$isRedirect, $redirect_id];
    }

    public static function findRedirect($entityId, $type): Redirects|null
    {
		$query = Redirects::find()->where([
			'type' => $type,
			'status' => Redirects::STATUS_IN_WORK
		]);

		if ($entityId !== null) {
			$query->andWhere(['entity_id' => $entityId]);
		}

		$minId = $query->min('id');
		$maxId = $query->max('id');

		if ($minId === null || $maxId === null) {
			return null;
		}

		//todo можно наложить ограничение на попытки
		do {
			$randomId = rand($minId, $maxId);

			$cacheKey = "redirect_{$type}_{$entityId}_{$randomId}";

			$cachedRedirect = Redis::find($cacheKey);
			if ($cachedRedirect) {
				return $cachedRedirect;
			}

			$doQuery = clone $query;
			$randomRedirect = $doQuery->andWhere(['id' => $randomId])->one();

			if ($randomRedirect) {
				Redis::save($cacheKey,$randomRedirect, false, 86400);
			}

		} while ($randomRedirect === null);

		return $randomRedirect;
    }

    public static function updateRedirectStats($redirect_id)
    {
       /* Yii::$app->db->createCommand()->update(
            'redirects',
            [
                'bots_count' => new Expression('bots_count + 1'),
                'status' => new Expression("IF(bots_count >= bot_limit, " . Redirects::STATUS_DONE . ", status)")
            ],
            ['id' => $redirect_id, 'bots_count' => new Expression('bots_count < bot_limit')]
        )->execute();*/
		$rab = new RabbitMQ(queueName: 'dorgen.redirects.stat', routingKey: 'dorgen_redirects_stat');
		$rab->publish($redirect_id);
    }

	public static function clearCache($entity_id): void {
		$redirects = self::find()
			->select(['id','type'])
			->where(['entity_id' => $entity_id])
			->andWhere(['!=', 'status', self::STATUS_DONE])
			->asArray()
			->all();

		foreach ($redirects as $redirect) {
			$key = "redirect_{$redirect['type']}_{$entity_id}_{$redirect['id']}";
			Redis::delete($key);
		}
	}

}