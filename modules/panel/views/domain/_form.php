<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\DomainForm $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="domain-form">

    <?php $form = ActiveForm::begin([
        'action' => Url::to(['domain/create']),
        'options' => ['data-pjax' => true],
        'enableClientValidation' => true
    ]); ?>

    <div class="row">
        <?= $form->field($model, 'domains')->textarea(['rows' => 6]) ?>
        <p class="help-block">Формат: <br> domain.com <br> site.ru</p>
    </div>

    <div class="row mb-3">
        <div class="form-group">
            <?= $form->field($model, 'metka')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <?= $form->field($model, 'project_id')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Добавить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
