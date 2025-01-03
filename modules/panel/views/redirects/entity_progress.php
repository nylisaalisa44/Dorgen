<?php

use app\models\Redirects;
use yii\bootstrap5\Html;

?>
<?php foreach ($redirects as $redirect):
    if ($redirect->status == Redirects::STATUS_DONE) {
        $colorClass = 'success';
        $textColor = '#28a745'; // Зеленый цвет
        $progress = 100;
        $status = 'готово';
    } elseif ($redirect->status == Redirects::STATUS_IN_WORK) {
        $colorClass = 'info';
        $textColor = '#17a2b8'; // Голубой цвет
        $progress = round(($redirect->bots_count / $redirect->bot_limit) * 100 ,2);
        $status = 'в работе';
    } else {
        $colorClass = 'danger';
        $textColor = '#dc3545'; // Красный цвет
        $progress = round(($redirect->bots_count / $redirect->bot_limit) * 100,2);
        $status = 'на паузе';
    }


    ?>


<div class="card bg-light overflow-hidden mb-2">
    <div class="card-body pt-2 pb-1">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">
                <h6 class="mb-0"><b class="text-<?= $colorClass ?>"><small><?= $redirect->redirect_url ?></small></b></h6>
            </div>
            <div class="flex-sm-shrink-0 ms-3">
				<?= \app\components\BotDetector::getBotImagesByNumbers($redirect->allowed_bots) ?>
            </div>
            <div class="flex-shrink-0 ms-3">
                <h6 class="mb-0">
                    <small>осталось  <?= $redirect->bot_limit - $redirect->bots_count ?> из <?= $redirect->bot_limit ?></small>
					<?= Html::a('<i class="text-danger cursor-hand bx bx-trash"></i>', ['delete', 'id' => $redirect->id], [
						'class' => 'text-danger',
						'title' => 'Clear',
						'data' => [
							'confirm' => 'Вы уверены, что хотите удалить редирект '.$redirect->redirect_url.'?',
							'method' => 'post',
						],
					]) ?>
                </h6>
            </div>
        </div>
    </div>
    <div class="progress bg-<?= $colorClass ?>-subtle rounded-0">
        <div class="progress-bar progress-bar-striped bg-<?= $colorClass ?>" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"><?= $progress ?>%</div>
    </div>
</div>
<?php endforeach; ?>