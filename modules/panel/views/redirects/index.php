<?php

use app\models\Redirects;
use yii\base\DynamicModel;
use yii\bootstrap5\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\RedirectsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Ферма';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                <?= Html::a('Редиректы фермы', ['/redirect/index'], ['class' => 'text-decoration-underline link-underline-primary']) ?>
            </h4>
            <div class="page-title-right">
                <?= Html::button('<i class="bx bx-link fs-16 align-bottom me-1"></i> ДОБАВИТЬ ССЫЛКИ', [
                    'class' => 'btn btn-sm bg-gradient btn-primary waves-effect waves-light btn-border me-3',
                    'data-bs-toggle' => 'modal',
                    'data-bs-target' => '#addRedirect'
                ]) ?>
				<?= Html::button('УДАЛИТЬ РЕДИРЕКТЫ', [
					'class' => 'btn btn-sm bg-gradient btn-primary waves-effect waves-light btn-border me-3',
					'data-bs-toggle' => 'modal',
					'data-bs-target' => '#deleteRedirects'
				]) ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 mb-3">
        <table class="table table-striped table-hover mt-2">
            <thead>
            <tr class="bg-secondary bg-opacity-10" style="border-bottom:3px #3d4050 solid">
                <th scope="col" style="width:80px"></th>
                <th scope="col" style="min-width:150px">Проект</th>
                <th scope="col" class="text-center" style="width:150px">Ссылок</th>
                <th scope="col" style="width:40%">Прогресс</th>
                <th scope="col" style="width:20px"></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($redirects as $redirect): ?>
                <?php
                $statusBadge = '';
                $statusCounts = $statusCountsByEntity[$redirect->entity_id]['status'] ?? [
                    Redirects::STATUS_WAIT => 0,
                    Redirects::STATUS_IN_WORK => 0,
                    Redirects::STATUS_DONE => 0,
                ];

                if (isset($statusCountsByEntity[$redirect->entity_id])) {
                    $progress = round(($statusCountsByEntity[$redirect->entity_id]['bots_count'] / $statusCountsByEntity[$redirect->entity_id]['bot_limit']) * 100);
                } else {
                    $progress = 100;
                }
                $color = $redirect->status == Redirects::STATUS_DONE ? 'success' : 'info';
                ?>
                <tr class="cursor-hand"
                    data-id="<?= $redirect->entity ? $redirect->entity->id : 0 ?>"
                    data-title="<?= $redirect->entity ? $redirect->entity->name : 'Глобальная ферма' ?>"
                    onclick="loadModalContent($(this).data('id'), $(this).data('title'))"
                >
                    <td>
						<?= Html::a(
							'<i class="ms-2 me-1 fs-16 mt-1 bx bx-play-circle" title="Запустить всё"></i>',
							['change-status', 'status' => Redirects::STATUS_IN_WORK, 'id' => $redirect->entity ? $redirect->entity_id : 0],
							[
								'class' => 'text-primary',
								'title' => 'Запустить всё',
								'data' => [
									'confirm' => 'Вы уверены, что хотите запустить все редиректы проекта ' . $name = $redirect->entity ? $redirect->entity->name : 'Глобальная ферма' . ' ?',
									'method' => 'post',
								],
							]
						) ?>

						<?= Html::a(
							'<i class="ms-2 me-1 fs-16 mt-1 bx bx-pause-circle" title="Приостановить всё"></i>',
							['change-status', 'status' => Redirects::STATUS_WAIT, 'id' => $redirect->entity ? $redirect->entity_id : 0],
							[
								'class' => 'text-danger',
								'title' => 'Приостановить всё',
								'data' => [
									'confirm' => 'Вы уверены, что хотите остановить все редиректы проекта ' . $name = $redirect->entity ? $redirect->entity->name : 'Глобальная ферма' . ' ?',
									'method' => 'post',
								],
							]
						) ?>

	<!--					--><?php /*= Html::a(
							'<i class="bx bx-reset" title="Перезапустить с начальными параметрами"></i>',
							['your-action', 'param3' => 'value3'], // Замените 'your-action' и 'param3' с нужными значениями
							[
								'class' => 'text-info',
								'title' => 'Перезапустить с начальными параметрами',
								'data' => [
									'confirm' => 'Вы уверены, что хотите перезапустить с начальными параметрами?',
									'method' => 'post',
								],
								'onclick' => 'return confirm("Вы уверены, что хотите перезапустить с начальными параметрами?");' // Если требуется подтверждение
							]
						) */?>
                    </td>
                    <td>
                        <?= $redirect->entity ? $redirect->entity->name : 'Глобальная ферма' ?>
                    </td>
                    <td class="text-center">
                        <?php if (!empty($statusCounts[Redirects::STATUS_WAIT])): ?>
                            <span class="badge bg-danger me-1" title="Ожидание">
                        <?= $statusCounts[Redirects::STATUS_WAIT] ?>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($statusCounts[Redirects::STATUS_IN_WORK])): ?>
                            <span class="badge bg-primary me-1" title="В работе">
                        <?= $statusCounts[Redirects::STATUS_IN_WORK] ?>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($statusCounts[Redirects::STATUS_DONE])): ?>
                            <span class="badge bg-success me-1" title="Завершено">
                        <?= $statusCounts[Redirects::STATUS_DONE] ?>
                            </span>
                        <?php endif; ?>

                    </td>
                    <td>
                        <div class="progress progress-lg mt-1">
                            <div class="progress-bar progress-bar-striped bg-<?= $color ?>" role="progressbar"
                                 style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0"
                                 aria-valuemax="100"><?= $progress ?>%
                            </div>
                        </div>
                    </td>
                    <td>
                        <?= Html::a('<i class="text-danger ms-2 me-1 fs-16 mt-1 bx bx-eraser"></i>', ['clear', 'id' => $redirect->entity ? $redirect->entity_id : 0], [
                            'class' => 'text-danger',
                            'title' => 'Clear',
                            'data' => [
                                'confirm' => 'Вы уверены, что хотите удалить все редиректы проекта ' . $name = $redirect->entity ? $redirect->entity->name : 'Глобальная ферма' . ' ?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
Modal::begin([
    'id' => 'addRedirect',
    'title' => 'Add New Redirect',
]);

echo $this->render('_form', ['model' => new Redirects()]);

Modal::end();

?>

<?php
Modal::begin([
	'id' => 'deleteRedirects',
	'title' => 'Delete Redirects',
    'size' => 'modal-lg',
]);

$model = new DynamicModel(['redirects']);
$model->addRule(['redirects'], 'required');
?>
<div class="redirects-files-form">
	<?php $form = ActiveForm::begin([
		'action' => ['redirects/delete-files-redirects'],
		'method' => 'post',
		'options' => ['enctype' => 'multipart/form-data'],
	]); ?>

    <label class="form-label">Напишите редиректы для удаления</label>

	<?= $form->field($model, 'redirects')->label('')->textarea(['rows' => 20]) ?>
    <br>

	<div class="form-group">
		<?= Html::submitButton('Отправить', ['class' => 'btn btn-primary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>
<?php Modal::end(); ?>

<?php
Modal::begin([
    'id' => 'redirect-progress',
    'size' => 'modal-xl',
    'dialogOptions' => [
        'class' => 'modal-dialog-centered',
    ],
    'title' => "Статистика"

]);

echo '<div id="modalContent"></div>';

Modal::end();

?>

<script>
  function loadModalContent(id, title) {
    if ($(event.target).closest('.text-danger').length > 0) {
      return;
    }

    function fetchData() {
      $.ajax({
        url: '<?= Url::to(['entity-progress']) ?>',
        type: 'GET',
        data: {id: id, title: title},
        success: function (data) {
          $('#modalContent').html(data);
          $('#redirect-progress').modal('show');
          $('#redirect-progress').find('.modal-title').text('Статистика ' + title);
        },
        error: function () {
          $('#modalContent').html('<p>An error occurred while loading content.</p>');
        }
      });
    }

    fetchData();

    var intervalId = setInterval(function() {
      if ($('#redirect-progress').hasClass('show')) {
        fetchData();
      } else {
        clearInterval(intervalId);
      }
    }, 1000);

    $('#redirect-progress').on('hidden.bs.modal', function () {
      clearInterval(intervalId);
    });
  }
</script>

