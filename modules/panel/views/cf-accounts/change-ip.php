<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="site-proba">
	<h1>Cloudflare change ip</h1>

	<?php $form = ActiveForm::begin(['method' => 'post']); ?>

	<?= $form->field($model, 'replaced_ip')->textInput(['maxlength' => true]) ?>
	<?= $form->field($model, 'new_ip')->textInput(['maxlength' => true]) ?>
	<?= $form->field($model, 'old_ip')->textInput(['maxlength' => true]) ?>

	<div class="form-group">
		<?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
	</div>

	<?php ActiveForm::end(); ?>
</div>
