<?php

namespace app\models;

use app\components\Helpers;
use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "domain".
 *
 * @property int $id
 * @property string|null $domain
 * @property string|null $metka
 * @property string|null $redirect_type
 * @property string|null $redirect_urls
 * @property int $project_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Project $project
 */
class DomainForm extends Model
{
    public $domains;
    public $project_id;
    public $metka;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id'], 'required'],
            [['project_id'], 'integer'],
            ['metka', 'string', 'max' => 255],
            ['domains', 'string'],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['project_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'domains' => 'Домен',
            'metka' => 'Метка',
            'project_id' => 'Проект',
        ];
    }

    /**
     * @throws Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $domains = preg_split('/\r\n|\r|\n/', trim($this->domains));
        $errors = false;

        foreach ($domains as $domain) {
            $model = new Domain();
            $model->domain = $domain;
            $model->metka = $this->metka;
            $model->project_id = $this->project_id;
            if (!$model->save()) {
                $errors = true;
                Yii::$app->session->addFlash('error', "Failed to save domain: $domain. Errors: " . implode(', ', $model->getFirstErrors()));
            }
        }

        return !$errors;
    }
}
