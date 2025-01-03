<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\CfAccounts $model */

$this->title = 'Create Cf Accounts';
$this->params['breadcrumbs'][] = ['label' => 'Cf Accounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cf-accounts-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
