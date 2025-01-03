<?php

namespace app\modules\panel;

/**
 * panel module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\panel\controllers';
    public $layout = '@app/views/layouts/panel';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
