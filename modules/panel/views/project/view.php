<?php

use yii\base\DynamicModel;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Modal;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Project $model */
/** @var app\models\Domain $domain_model */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var yii\data\ActiveDataProvider $dataProviderFarm */
/** @var array $total */
/** @var array $totalFarm */


$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Проекты ', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name];

$script = <<< JS
$(document).on('click', '#settingsModalButton', function () {
    $('#settingsModal').modal('show');
});
$(document).on('click', '#domainModalButton', function () {
    $('#domainModal').modal('show');
});
JS;
$this->registerJs($script);

$js = <<<JS
$(document).ready(function() {
    $('#upload-cpanel-form').on('beforeSubmit', function(e) {
      e.preventDefault();
      var formData = new FormData(this);

      $.ajax({
        url: $(this).attr('action'),
        type: $(this).attr('method'),
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          if (response.success) {
            $('#uploadCpanel .ddl-cpanel-form').hide();
            $('#log-cpanel-container').show();
            $('#job-id').text(response.jobId);
            $('#total-uniq-cpanel').text(response.uniq_lines);
            window.total = response.uniq_lines;
            
            startFetchingLogs(response.jobId, 'cpanel');
          } else {
            alert('Ошибка при загрузке файла.');
          }
        },
        error: function() {
          alert('Произошла ошибка на сервере.');
        }
      });

      return false;
    });

    let logInterval;
    
    // Функция для получения логов и их обновления
    function startFetchingLogs(jobId, type) {
      let logContent = $('#log-cpanel-container .log-cpanel-content');
      let redis_key = 'current_cpanel';
      let  uniq_key = 'current-cpanel-uniq'
      let bar = 'progress-bar-cpanel';
      
      logInterval = setInterval(function() {
        $.ajax({
          url: '/panel/domain/get-job-log',
          data: { jobId: jobId, type: type },
          success: function(data) {
            logContent.html(data);
            logContent.scrollTop(logContent[0].scrollHeight);
            
            if (data.includes('File done!')) {
              clearInterval(logInterval);
              window.location.href = '/panel/domain/send-report?file_name=fail-cpanel.txt';
              setTimeout(function() {
                window.location.href = '/panel/domain/send-report?file_name=success-cpanel.txt';
                }, 3000);
              
            }
          }
        });
        
        $.ajax({
          url: '/panel/domain/get-current',
          data: { key: redis_key },
          success: function(data) {
            $('#' + uniq_key).text(data);
            updateProgressBar(uniq_key, bar);
          }
        });
        
      }, 1000);
    }
    
    $('#uploadCpanel').on('hidden.bs.modal', function () {
      clearInterval(logInterval);
    });
    
    function updateProgressBar(key, bar) {
      var total = window.total;
      var current = parseInt(document.getElementById(key).textContent, 10);
      
      if (!isNaN(current) && !isNaN(total) && total > 0) {
        // Рассчитываем процент выполнения
        var progress = Math.round((current / total) * 100);

        // Обновляем прогресс-бар
        var progressBar = document.getElementById(bar);
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
        progressBar.textContent = progress + '%';
    }
}
    
});
JS;

$this->registerJs($js);
?>

<div class="project-update">

    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">
            <a href="<?= Url::to(['', 'id' => $model->id]) ?>" class="text-decoration-underline link-underline-primary link-offset-2 link-underline-opacity-50 ">
                <?= Html::encode($this->title) ?>
            </a>
        </h4>
        <div class="page-title-right">
            <?= Html::button('<i class="bx bx-globe fs-16 align-bottom me-1"></i>    ДОБАВИТЬ ДОМЕНЫ',
                [
                    'id' => 'domainModalButton',
                    'class' => 'btn btn-md bg-gradient btn-primary waves-effect waves-light btn-border me-3'
                ]
            ) ?>

			<?= Html::button('<i class="bx bx-cog fs-16 align-bottom me-1"></i>   НАСТРОЙКИ',
				[
					'id' => 'settingsModalButton',
					'class' => 'btn btn-md btn-ghost-primary waves-effect waves-light btn-border'
				]
			) ?>
        </div>
    </div>

    <?= $this->render('@app/modules/panel/views/domain/_index', ['dataProvider' => $dataProvider, 'total' => $total, 'dataProviderFarm' => $dataProviderFarm, 'totalFarm' => $totalFarm]) ?>

</div>

<?php
Modal::begin([
    'title' => '<h4>Добавить домены</h4>',
    'id' => 'domainModal',
    'size' => 'modal-lg',
]);

$domain_model->project_id = $model->id;

echo $this->render('../domain/_form', ['model' => $domain_model]);

Modal::end();
?>


<?php
Modal::begin([
    'title' => '<h4>Настройки проекта</h4>',
    'id' => 'settingsModal',
    'size' => 'modal-lg',
]);

echo $this->render('_form', ['model' => $model, 'create' => false]);

Modal::end();
?>

<?php
Modal::begin([
	'id' => 'uploadCpanel',
	'title' => 'Загрузить cpanel',
	'size' => 'modal-lg'
]);

$model = new DynamicModel(['file']);
$model->addRule(['file'], 'required');
$model->addRule(['file'], 'file', ['extensions' => 'txt']);
?>

<div class="ddl-cpanel-form">
	<?php $form = ActiveForm::begin([
		'id' => 'upload-cpanel-form',
		'action' => ['domain/upload-cpanel'],
		'method' => 'post',
	]); ?>

	<?= $form->field($model, 'file')->label("Файл")->fileInput() ?>

    <div class="form-group">
		<?= Html::submitButton('Отправить', ['class' => 'btn btn-primary']) ?>
    </div>

	<?php ActiveForm::end(); ?>
</div>

<div id="log-cpanel-container" style="display: none;">
    <h3>Логи для cpanel:<span id="job-cpanel-id"></span></h3>
    <br>
    <h5 id="progress-cpanel">
        Обработано <span id="current-cpanel-uniq"></span> из: <span id="total-uniq-cpanel"></span>
    </h5>
    <div class="progress">
        <div id="progress-bar-cpanel" class="progress-bar" role="progressbar" style="width: 0;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
    </div>
    <br>

    <pre class="log-cpanel-content"></pre>
</div>

<?php Modal::end(); ?>
