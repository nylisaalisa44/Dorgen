<?php

use app\models\Project;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\widgets\Pjax;

?>
<?php $form = ActiveForm::begin([
    'layout' => 'horizontal',
    'enableAjaxValidation' => true,
    'id' => 'update-form',
    'validateOnChange' => false,
    'validateOnBlur' => false
]); ?>
    <div class="row">
        <?= $form->field($model, 'redirect_type')->checkboxList(
            [Project::REDIRECT_301 => '301 Redirect', Project::REDIRECT_302 => '302 Redirect', Project::REDIRECT_JAVASCRIPT => 'JavaScript', Project::REDIRECT_META => 'Meta Redirect']
        ); ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'redirect_urls', [
            'template' => '
            {label}
            <div class="col-sm-6">
                {input}
            </div>
            {error}'])->textarea(['rows' => '6']); ?>
    </div>

    <div class="row offset-sm-2">
        <p class="help-block">Формат: <br> <code>https://domain.com</code> <br> <code>https://domain2.com</code></p>
        <p class="help-block">
            <code>{w4}</code> - 4 случайных символа <br>
            <code>{c5}</code> - 5 случайных букв <br>
            <code>{d3}</code> - 3 случайные цифры <br>
            <code>{rand1-567}</code> - случайное число от 1 до 567
        </p>
    </div>
<?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>