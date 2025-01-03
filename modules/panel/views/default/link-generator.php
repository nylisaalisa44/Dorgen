<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model */

$this->title = 'Генератор ссылок';
?>

<div class="site-link-generator">
	<h1><?= Html::encode($this->title) ?></h1>

	<div class="link-generator-form">
		<?php $form = ActiveForm::begin(); ?>

		<?= $form->field($model, 'template')->textarea(['rows' => 6])->label('Домены') ?>
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

		<?= $form->field($model, 'count')->textInput()->label("Количество ссылок") ?>

		<div class="form-group">
			<?= Html::submitButton('Generate', ['class' => 'btn btn-primary']) ?>
		</div>

		<?php ActiveForm::end(); ?>
	</div>
</div>
