<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Redirects $model */

$this->title = 'Update Redirects: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Redirects', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="redirects-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
