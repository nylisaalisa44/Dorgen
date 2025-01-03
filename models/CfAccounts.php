<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cf_accounts".
 *
 * @property int $id
 * @property string $login
 * @property string $password
 * @property string $token
 * @property string|null $info
 */
class CfAccounts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cf_accounts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['login', 'password', 'token'], 'required'],
            [['info'], 'string'],
            [['login', 'password', 'token'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Логин',
            'password' => 'Пароль',
            'token' => 'Токен',
            'info' => 'Info',
        ];
    }
}
