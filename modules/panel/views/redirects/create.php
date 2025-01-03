<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Redirects $model */

$this->title = 'Create Redirects';
$this->params['breadcrumbs'][] = ['label' => 'Redirects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="redirects-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
