<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="site-proba">
    <h1>Cloudflare add domains</h1>

	<?php $form = ActiveForm::begin(['method' => 'post']); ?>

	<?= $form->field($model, 'ip')->textInput(['maxlength' => true])->label('IP address') ?>
	<?= $form->field($model, 'cf_acc_id')
        ->dropDownList(ArrayHelper::map(\app\models\CfAccounts::find()->all(), 'id', 'login'))
        ->label('Cloudflare account');
    ?>
	<?= $form->field($model, 'domains')->textarea(['rows' => 10])->label('Domains list') ?>

    <div class="form-group">
		<?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

	<?php ActiveForm::end(); ?>
</div>
