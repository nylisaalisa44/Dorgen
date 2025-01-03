<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Project $model */

$this->title = 'Создать проект';
$this->params['breadcrumbs'][] = ['label' => 'Проекты'];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="col-lg-6">
        <?= $this->render('_form', [
            'model' => $model,
            'create' => true,
        ]) ?>
    </div>

</div>
