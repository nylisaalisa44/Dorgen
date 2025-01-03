<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "project_links".
 *
 * @property int $id
 * @property string $url
 * @property string $metka
 */
class ProjectLinks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_links';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['url', 'metka'], 'required'],
            [['metka'], 'string', 'max' => 255],
            ['url', 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'metka' => 'Metka',
        ];
    }

	public static function getUniqueMetkas()
	{
		return self::find()->select('metka')->distinct()->all();
	}

	public static function getRandomLinks($metka, $num_links): array {
		$type = 'pirog';
		$pirog_links = ProjectLinks::find()
			->select(['url'])
			->where(['metka' => $metka])
			->orderBy(new Expression('RAND()'))
			->limit($num_links)
			->asArray()
			->column();

		// Если таблица ProjectLinks пустая, получаем ссылки из таблицы Domain
		if (empty($pirog_links)) {
			$type = 'drop';
			$pirog_links = Domain::find()
				->select(['domain'])
				->where(['project_id' => $metka])
				->orderBy(new Expression('RAND()'))
				->limit($num_links)
				->asArray()
				->column();
		}

		// Если в обеих таблицах не было данных, завершаем выполнение
		if (empty($pirog_links)) {
			$pirog_links = false;
		}

		return [
			$type,
			$pirog_links
		];
	}
}
