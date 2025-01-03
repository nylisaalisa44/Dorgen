<?php

use app\components\BotDetector;
use app\components\Helpers;
use app\models\Project;
use app\models\ProjectLinks;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Project $model */
/** @var yii\widgets\ActiveForm $form */
/** @var $create */


$script = <<< JS
$(document).ready(function() {
    $('#redirect-dropdown').on('change', function() {
        const selectedValue = this.value;
        const urlInputContainer = document.getElementById('black-redir-url-container');

        if (selectedValue === 'redirect/redirect') {
            urlInputContainer.style.display = 'block';
        } else {
            urlInputContainer.style.display = 'none';
        }
    });
});

    var directionSlider = document.getElementById('slider-direction');
    var formatForSlider = {
        from: function(formattedValue) {
            return Number(formattedValue);
        },
        to: function(numericValue) {
            return Math.round(numericValue) + '%';
        }
    };
    noUiSlider.create(directionSlider, {
        start: $('#redir_perc').val() || 50,
        step: 5,
        connect: 'lower',
        format: formatForSlider,
        tooltips: true,
        range: {
            'min': 10,
            'max': 100
        }
    });
    var directionField = document.getElementById('redir_perc');
    directionSlider.noUiSlider.on('update', function(values, handle) {
        let hann = values[handle];
        $('#redir_perc').val(hann.slice(0, -1));
    });
JS;
$this->registerJs($script);

$url = $model->isNewRecord ? Url::to(['project/create']) : Url::to(['project/update', 'id' => $model->id]);
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<div class="project-form">

    <?php $form = ActiveForm::begin([
/*        'layout' => 'horizontal',*/
        'action' => $url,
        'options' => ['data-pjax' => true],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="row" style="margin-bottom: 1rem;">
        <div class="col">
            <?= $form->field($model, 'type')->radioList([Project::TYPE_DROP => 'Дроп'], ['unselect' => null, 'id' => 'project-type-radio']); ?>
            <div class="mb-4">
                <div class="form-check form-switch form-switch-md mb-3" dir="ltr">
					<?= $form->field($model, 'is_html')->checkbox(['id' => 'is-html-checkbox'])->label('Использовать html карту') ?>
                </div>
            </div>
        </div>
        <div class="col border" style="padding-bottom: 15px;">
            <?= $form->field($model, 'db', ['options' => ['tag' => false]])->dropDownList(
                Project::getDbAsDropList(),
                ['multiple' => true, 'size' => 10]
            ); ?>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <?= $form->field($model, 'white')->dropDownList([
                'files/dor_white' => 'Door Template',
			], [
				'id' => 'dropdown-white',
			]); ?>
        </div>
        <div class="col">
            <?= $form->field($model, 'black')->dropDownList([
                'redirect/redirect' => 'Redirect',
               // 'files/black' => 'Black'
            ], [
                'id' => 'redirect-dropdown',
            ]); ?>

            <div id="black-redir-url-container"
                 style="display: <?= str_contains($model->black ?? 'redirect/redirect', 'redirect/redirect') ? 'block' : 'none' ?>;">
                <?= $form->field($model, 'black_redir_url')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link text-body active" data-bs-toggle="tab" href="#farmSettings" role="tab" aria-selected="true">
                <i class="fas fa-home"></i>
                Ферма
            </a>
        </li>
        <li class="nav-item" role="presentation" id="tab-li-white-settings">
            <a class="nav-link text-body" data-bs-toggle="tab" id="a-white-settings" href="#whiteSettings" role="tab" aria-selected="false" tabindex="-1">
                <i class="far fa-user"></i>
                Дор шаблон
            </a>
        </li>
    </ul>
<div class="card-body p-4">
    <div class="tab-content">
        <div class="tab-pane active show" id="farmSettings" role="tabpanel">
            <div class="mb-3">
                <label for="links-min" class="form-label">Количество ссылок в перелинковке фермы (мин - макс)</label>
                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'min_perelinks')->textInput(['placeholder' => 'от'])->label(false) ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'max_perelinks')->textInput(['placeholder' => 'до'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch form-switch-md mb-3" dir="ltr">
                    <?= $form->field($model, 'use_subs')->checkbox()->label('Использовать сабдомены в перелинковке') ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="links-min" class="form-label">Количество вывода снипетов в ферме</label>
                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'min_snippets')->textInput(['placeholder' => 'от'])->label(false) ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'max_snippets')->textInput(['placeholder' => 'до'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="redir_perc" class="form-label">Процент срабатываний внутренних редиректов</label>
                <?= $form->field($model, 'farm_redirect_inner_procent')->hiddenInput(['id' => 'redir_perc'])->label(false) ?>
                <div id="slider-direction" class="slider-styled mt-3 mb-1"></div>
            </div>
        </div>

        <div class="tab-pane" id="whiteSettings" role="tabpanel">
            <div class="mb-3">
                <label for="lang_white" class="form-label">Язык (2 символа)</label>
                <?= $form->field($model, 'lang_white')->textInput()->label(false) ?>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch form-switch-md mb-3" dir="ltr">
                    <?= $form->field($model, 'rand_anchor_white')->checkbox()->label('Использовать случайные анкоры (по умолчанию ближайший)') ?>
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch form-switch-md mb-3" dir="ltr">
                    <?= $form->field($model, 'use_subs_white')->checkbox()->label('Использовать сабдомены в линках') ?>
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch form-switch-md mb-3" dir="ltr">
                    <?= $form->field($model, 'use_project_links_white')->checkbox()->label('Использовать домены проекта в перелинковке') ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="links-min" class="form-label">Количество ссылок в доре (мин - макс)</label>
                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'min_links_white')->textInput(['placeholder' => 'от'])->label(false) ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'max_links_white')->textInput(['placeholder' => 'до'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="links-min" class="form-label">Количество вывода снипетов в доре</label>
                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'min_snippets_white')->textInput(['placeholder' => 'от'])->label(false) ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'max_snippets_white')->textInput(['placeholder' => 'до'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="links-min" class="form-label">Количество ссылок в снипетах</label>
                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'min_snippets_links_white')->textInput(['placeholder' => 'от'])->label(false) ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'max_snippets_links_white')->textInput(['placeholder' => 'до'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="mb-3">

            </div>
        </div>
    </div>
</div>
    <!--    <div class="row">
        <?php /*= $form->field($model, 'redirect_type')->checkboxList([
                Project::REDIRECT_301 => '301 Redirect',
                Project::REDIRECT_302 => '302 Redirect',
                Project::REDIRECT_JAVASCRIPT => 'JavaScript',
                Project::REDIRECT_META => 'Meta Redirect'
            ]
        ); */ ?>
    </div>-->

    <div class="row">
        <?= $form->field($model, 'allowed_bots')->checkboxList(BotDetector::getBotArray()); ?>
    </div>

    <!--    <div class="row">
        <?php /*= $form->field($model, 'redirect_urls', [
            'template' => '
            {label}
            <div class="col-sm-6">
                {input}
            </div>
            {error}'])->textarea(['rows' => '6']); */ ?>
    </div>-->

    <!--    <div class="row offset-sm-2">
            <p class="help-block">Формат: <br> <code>https://domain.com</code> <br> <code>https://domain2.com</code></p>
            <p class="help-block">
                <code>{w4}</code> - 4 случайных символа <br>
                <code>{c5}</code> - 5 случайных букв <br>
                <code>{d3}</code> - 3 случайные цифры <br>
                <code>{rand1-567}</code> - случайное число от 1 до 567
            </p>
        </div>-->

    <div class="mb-3">
        <label for="themename" class="form-label">Включить отладку </label>
        <div class="input-group">
            <div class="input-group-text">
<!--                <input class="form-check-input mt-0" type="checkbox" name="okdebug" id="okdebug">-->
                <?= $form->field($model, 'enable_debug', [ 'checkTemplate' => '{input}'])->checkbox()->label(false) ?>
            </div>
            <?= $form->field($model, 'debug')->textInput(['placeholder' => 'IP отладчика', 'value' => Helpers::getRemoteIp()])->label(false) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success', 'id' => 'submit-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>