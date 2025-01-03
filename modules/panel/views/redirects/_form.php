<?php

use app\components\BotDetector;
use app\models\Redirects;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Redirects $model */
/** @var yii\widgets\ActiveForm $form */

$projects = ArrayHelper::map(\app\models\Project::find()->where(['status' => \app\models\Project::STATUS_ACTIVE])->all(), 'id', 'name');
$projects = ['0' => 'Глобальный'] + $projects;

if (!$model->entity_id) {
    $model->entity_id = 0;
}


$this->registerJs("
        var globalCheckbox = document.querySelector('.project-checkbox input[value=\"0\"]');
        var projectCheckboxes = document.querySelectorAll('.project-checkbox input[type=\"checkbox\"]:not([value=\"0\"])');

        globalCheckbox.addEventListener('change', function() {
            if (globalCheckbox.checked) {
                // Убираем выбор у всех проектных чекбоксов
                projectCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = false;
                });
            }
        });

        projectCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                if (checkbox.checked) {
                    // Убираем выбор у глобального чекбокса
                    globalCheckbox.checked = false;
                }
            });
        });
");


?>
<div class="redirects-form">

    <?php $form = ActiveForm::begin([
        'action' => ['redirects/create'],
        'method' => 'post',
    ]); ?>


    <?= $form->field($model, 'redirect_url')->textarea(['rows' => 6])->label('Ссылки') ?>
    <div class="">
        <p class="help-block">Формат шаблона:
            <br>
            <code>https://{c5}.domain.com</code> <br> <code>https://{w4}.domain2.com</code></p>
        <p class="help-block">
            <code>{w4}</code> - 4 случайных символа <br>
            <code>{c5}</code> - 5 случайных букв <br>
            <code>{d3}</code> - 3 случайные цифры <br>
            <code>{rand1-567}</code> - случайное число от 1 до 567
        </p>
    </div>

    <div class="row" <?= !$model->isNewRecord ? 'style="display: none;"' : '' ?>>
        <?= $form->field($model, 'allowed_bots')->checkboxList(BotDetector::getBotArray()); ?>
    </div>

    <?= $form->field($model, 'redirect_type')->checkboxList([
            Redirects::REDIRECT_301 => '301 Redirect',
            Redirects::REDIRECT_302 => '302 Redirect',
            Redirects::REDIRECT_JAVASCRIPT => 'JavaScript',
            Redirects::REDIRECT_META => 'Meta Redirect'
        ]
    ); ?>


    <div id="project-options" style="<?= ($model->type == 2) ? 'display: none;' : '' ?>">
        <?= $form->field($model, 'entity_id')->checkboxList(
            $projects,
            ['class' => 'project-checkbox']
        )->label('Выберите проект') ?>
    </div>

    <p>Лимит ботов: <?= $form->field($model, 'bot_limit')->textInput(['value' => $_SERVER['BOT_LIMIT']]) ?></p>

    <div class="form-group">
        <?= Html::checkbox('run_bots_immediately', false, ['label' => 'Запустить ботов сразу']) ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
