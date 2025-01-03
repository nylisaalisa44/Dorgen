<?php

namespace app\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "domain".
 *
 * @property int $id
 * @property string|null $domain
 * @property string|null $metka
 * @property int|null $project_id
 * @property int|null $farm
 * @property string $type
 * @property string|null $redirect_type
 * @property string|null $redirect_urls
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Project $project
 */
class Domain extends \yii\db\ActiveRecord
{
    const TYPE_PROJECT = 'project';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'domain';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'farm'], 'integer'],
            [['created_at', 'updated_at', 'redirect_type', 'redirect_urls'], 'safe'],
            [['domain', 'metka'], 'string', 'max' => 255],
            ['domain', 'unique'],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['project_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'domain' => 'Домен',
            'metka' => 'Метка',
            'farm' => 'Ферма',
            'type' => 'Тип',
            'project_id' => 'Проект',
            'created_at' => 'Возраст',
            'updated_at' => 'Обновлен',
        ];
    }

    public function beforeDelete(): bool
    {
        if (parent::beforeDelete()) {
            Redirects::deleteAll(['like', 'redirect_url', $this->domain]);
            Redirects::deleteAll([
                'type' => Redirects::TYPE_DOMAIN,
                'entity_id' => $this->id
            ]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets query for [[Project]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'project_id']);
    }

    public function saveForm()
    {
        $domains = explode("\r\n", $this->domain);

        foreach ($domains as $domain) {
            $model = new Domain();
            $model->domain = $domain;
            $model->metka = $this->metka;
            $model->project_id = $this->project_id;
            $model->save();
        }

        return true;
    }


    /**
     * Получает доменное имя без зоны верхнего уровня (TLD).
     *
     * @return string|null Основное доменное имя без зоны (например, "example").
     */
    public function getDomainWithoutZone(): ?string
    {
        $parts = explode('.', $this->domain);

        if (count($parts) < 3) {
            return $parts[0];
        }

        array_pop($parts);

        return array_pop($parts);
    }

    /**
     * Проверяет есть ли редиректы у домена
     *
     * @return bool
     */
    public function checkRedirects(): bool
    {
        return Redirects::find()->where(['entity_id' => $this->id])->exists();
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
            self::TYPE_PROJECT,
        ];
    }
}
