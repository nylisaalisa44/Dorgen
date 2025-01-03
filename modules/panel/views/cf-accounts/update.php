<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\CfAccounts $model */

$this->title = 'Update Cf Accounts: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Cf Accounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="cf-accounts-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
