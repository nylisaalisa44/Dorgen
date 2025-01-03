<?php

namespace app\models;

use app\components\Helpers;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property string $name
 * @property string $db
 * @property int|null $type
 * @property string|null $white
 * @property string|null $black
 * @property string|null $redirect_type
 * @property string|null $redirect_urls
 * @property string|null $allowed_bots
 * @property string|null $black_redir_url
 * @property int $status
 * @property int $min_perelinks
 * @property int $max_perelinks
 * @property int $farm_redirect_inner_procent
 * @property int $farm_redirect_subs
 * @property int $min_snippets
 * @property int $max_snippets
 * @property int|null $use_subs
 * @property string|null $debug
 * @property int|null $enable_debug
 * @property string $lang_white
 * @property int|null $rand_anchor_white
 * @property int|null $use_subs_white
 * @property int|null $use_project_links_white
 * @property int|null $max_snippets_white
 * @property int|null $min_snippets_white
 * @property int|null $min_snippets_links_white
 * @property int|null $max_snippets_links_white
 * @property int|null $min_links_white
 * @property int|null $max_links_white
 * @property int|null $is_html
 * @property string|null $pirog_links
 * @property int|null $html_min_links
 * @property int|null $html_max_links
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Domain[] $domains
 */
class Project extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const TYPE_DROP = 1;

    const REDIRECT_301 = 301;
    const REDIRECT_302 = 302;
    const REDIRECT_META = 1;
    const REDIRECT_JAVASCRIPT = 2;

    /**
     * @throws \Exception
     */
    public function getRedirectTypeRand(): ?string
    {
        if ($this->redirect_type) {
            $type = explode(',', $this->redirect_type);
            return $type[array_rand($type)];
        }
        return null;
    }

    /**
     * @throws \Exception
     */
    public function getRedirectUrlRand(): array|string|null
    {
        if ($this->redirect_urls) {
            $url = explode(',', $this->redirect_urls);
            return Helpers::formatRegexUrl($url[array_rand($url)]);
        }
        return null;
    }

    /**
     * @throws \Exception
     */
    public function getStatusName()
    {
        return ArrayHelper::getValue(self::getStatusesArray(), $this->status);
    }

    public static function getStatusesArray(): array
    {
        return [
            self::STATUS_ACTIVE => 'Активный',
            self::STATUS_INACTIVE => 'Неактивный',
        ];
    }

	/**
	 * @throws \Exception
	 */
	public function getTypeName()
	{
		return ArrayHelper::getValue(self::getTypesArray(), $this->type);
	}

	public static function getTypesArray(): array
	{
		return [
			self::TYPE_DROP => 'Дроп',
		];
	}

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[
				'name', 'db', 'status', 'allowed_bots', 'min_perelinks', 'max_perelinks', 'min_snippets', 'max_snippets', 'max_snippets_white',
				'min_snippets_white', 'min_links_white', 'max_links_white', 'min_snippets_links_white', 'max_snippets_links_white',
				'html_min_links', 'html_max_links'
			], 'required'],
            [[
                'type', 'status', 'min_perelinks', 'max_perelinks', 'farm_redirect_inner_procent', 'farm_redirect_subs', 'min_snippets',
                'max_snippets', 'use_subs', 'enable_debug', 'max_snippets_white', 'min_snippets_white', 'min_links_white', 'max_links_white', 'rand_anchor_white',
                'use_subs_white', 'use_project_links_white', 'min_snippets_links_white', 'max_snippets_links_white', 'is_html',
				'html_min_links', 'html_max_links'
            ], 'integer'],
            [['redirect_urls', 'redirect_type', 'allowed_bots', 'created_at', 'updated_at', 'db'], 'safe'],
            [['name', 'white', 'black', 'black_redir_url', 'debug', 'lang_white', 'pirog_links'], 'string', 'max' => 255],
            ['black_redir_url', 'url', 'defaultScheme' => 'http'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'db' => 'База',
            'type' => 'Тип',
            'white' => 'Шаблон White',
            'black' => 'Шаблон Black',
            'redirect_type' => 'Типы редиректов',
            'redirect_urls' => 'Ссылки слива',
            'allowed_bots' => 'Боты',
            'black_redir_url' => 'URL редиректа',
            'status' => 'Статус',
            'min_perelinks' => 'Min Perelinks',
            'max_perelinks' => 'Max Perelinks',
            'farm_redirect_inner_procent' => 'Farm Redirect Inner Procent',
            'farm_redirect_subs' => 'Farm Redirect Subs',
            'min_snippets' => 'Min Snippets',
            'max_snippets' => 'Max Snippets',
            'use_subs' => 'Use Subs',
            'debug' => 'Debug',
            'enable_debug' => 'Enable Debug',
            'lang_white' => 'Lang',
            'rand_anchor_white' => 'Rand or Rel Anchor',
            'use_subs_white' => 'Use Subs',
            'use_project_links_white' => 'Use Snippets Project Links',
            'max_snippets_white' => 'Max Snippets Dor',
            'min_snippets_white' => 'Min Snippets Dor',
            'min_links_white' => 'Min Links Dor',
            'max_links_white' => 'Max Links Dor',
            'min_snippets_links_white' => 'Min Snippets Links Dor',
            'max_snippets_links_white' => 'Max Snippets Links Dor',
			'is_html' => 'Is html',
			'html_min_links' => 'Html Min Links',
			'html_max_links' => 'Html Max Links',
			'pirog_links' => 'Pirog Links',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлен',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Преобразуем массив в строку, если это массив
            if (is_array($this->db)) {
                $this->db = implode(',', $this->db);
            }
            return true; // Продолжаем сохранение
        } else {
            return false; // Останавливаем сохранение, если возникли проблемы
        }
    }

    public function afterFind()
    {
        parent::afterFind();

        // Преобразуем строку в массив после получения данных из базы данных
        if ($this->db !== null) {
            $this->db = explode(',', $this->db);
        }
    }

    /**
     * Gets query for [[Domains]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDomains()
    {
        return $this->hasMany(Domain::class, ['project_id' => 'id']);
    }

    public function getTable()
    {
        // Если поле db не пустое, разбиваем его в массив
        $values = $this->db;

        // Если массив не пустой, выбираем случайное значение
        return !empty($values) ? $values[array_rand((array)$values)] : null;
    }

    public function getCountDomains()
    {
        return $this->hasMany(Domain::class, ['project_id' => 'id'])->count();
    }

	public function getCountPartDomains($is_farm = 0)
	{
		return $this->hasMany(Domain::class, ['project_id' => 'id'])
			->andWhere(['farm' => $is_farm])
			->count();
	}

    /**
     * Gets query for [[Redirects]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRedirects()
    {
        return $this->hasMany(Redirects::class, ['project_id' => 'id']);
    }

    public function getProjectBotsToday()
    {
        $domains = $this->getDomains()->asArray()->all();
        $domainIds = ArrayHelper::getColumn($domains, 'id');

        $date = date('Y-m-d');
        return (new \yii\db\Query())
            ->select(['SUM(bot) AS bot'])
            ->from('statistics')
            ->where(['date' => $date])
            ->andWhere(['in', 'domain_id', $domainIds])
            ->one()['bot'] ?? 0;
    }

    public function getDomainLinks(): array
    {
        return ArrayHelper::getColumn($this->domains, 'domain');
    }

    public static function getDbAsDropList(): array
    {
        $items = [];

        // Получаем список всех таблиц, начинающихся с 'db_'
        $tables = Yii::$app->db->createCommand("SHOW TABLES LIKE 'db\_%'")->queryColumn();

        foreach ($tables as $table) {
            $count = Yii::$app->db->createCommand("SELECT COUNT(*) FROM {$table}")->queryScalar();
            $items[$table] = "{$table} | {$count}";
        }

        return $items;
    }

}
