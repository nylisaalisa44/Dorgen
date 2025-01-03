<?php

use app\models\Domain;
use app\models\Project;
use app\models\Statistics;
use yii\bootstrap5\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Мои проекты ';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('/panel/assets/libs/apexcharts/apexcharts.min.js', ['depends' => \yii\web\JqueryAsset::class]);

$css = <<< CSS
        .page-content {
            max-width:1900px
        }
CSS;

$this->registerCss($css);

$models = $dataProvider->getModels();
$chartData = [];

foreach ($models as $model) {
    $chartData[$model->id] = Statistics::getStatisticsByDomain(ArrayHelper::getColumn(Domain::find()->select('id')->where(['project_id' => $model->id])->asArray()->all(), 'id'));
}
?>
<div class="project-index">

    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0"><a class="text-decoration-underline link-underline-primary link-offset-2 link-underline-opacity-50"><?= Html::encode($this->title) ?></a></h4>
        <div class="page-title-right">
			<?= Html::button('<i class="bx bx-folder-plus"></i> Создать проект', [
				'class' => 'btn btn-md bg-gradient btn-primary waves-effect waves-light btn-border me-3',
				'data-bs-toggle' => 'modal',
				'data-bs-target' => '#addProject'
			]) ?>
        </div>
    </div>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => [
            'class' => 'table table-striped table-hover'
        ],
        'headerRowOptions' => ['class' => 'bg-secondary bg-opacity-10', 'style' => 'border-bottom:3px #3d4050 solid'],
        'footerRowOptions' => ['class' => 'bg-secondary bg-opacity-10', 'style' => 'border-top:3px #3d4050 solid'],
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'id',
            [
                'format' => 'raw',
                'contentOptions' => function () {
                    return ['style' => 'width: 20px', 'align' => 'center'];
                },
                'value' => function ($data) {
                    // Генерация URL для кнопок "Скрыть" и "Отобразить"
                    $hideUrl = Url::to(['hide', 'id' => $data->id]);
                    $showUrl = Url::to(['show', 'id' => $data->id]);

                    if ($data->status == Project::STATUS_ACTIVE) {
                        return '<a href="' . $hideUrl . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Скрыть проект" class="btn btn-sm text-danger waves-effect waves-light btn-border">
                        <i class="bx bx-hide fs-16 align-bottom me-1"></i>
                    </a>';
                    } else {
                        return '<a href="' . $showUrl . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Отобразить проект" class="btn btn-sm text-success waves-effect waves-light btn-border">
                        <i class="bx bx-show fs-16 align-bottom me-1"></i>
                    </a>';
                    }
                },
            ],
            [
                'attribute' => 'name',
                'format' => 'html',
                'value' => function ($data) {
                    return Html::a($data->name, Url::to(['project/view', 'id' => $data->id]), ['data-pjax' => 0, 'class' => 'text-primary']);
                },
            ],
            [
					'attribute' => 'Статус',
					'value' => function ($data) {
						return $data->getTypeName();
					},
            ],
            [
                'attribute' => 'db',
                'format' => 'html',
                'value' => function ($data) {
                    return implode('<br>', $data->db);
                },
            ],
            //'type',
            'white',
            'black',
            'black_redir_url',
			[
				'attribute' => 'allowed_bots',
				'format' => 'raw',
				'value' => function ($data) {
					$botArray = \app\components\BotDetector::getBotImagesByNumbers($data->allowed_bots);
					return '<div>' . $botArray . '</div>';
				},
			],
            [
                'label' => 'График Хитов',
                'format' => 'raw',
                'value' => function ($model) use ($chartData) {
                    return Html::tag('div', '', ['id' => 'chart_hits_' . $model->id, 'style' => 'height: 50px;']);
                },
            ],

            // Колонка с графиком ботов
            [
                'label' => 'График Ботов',
                'format' => 'raw',
                'value' => function ($model) use ($chartData) {
                    return Html::tag('div', '', ['id' => 'chart_bots_' . $model->id, 'style' => 'height: 50px;']);
                },
            ],
			[
				'format' => 'raw',
				'contentOptions' => function () {
					return ['style' => 'width: 20px'];
				},
				'value' => function ($data) {
					return Html::a(
						'<i class="bx bx-poll"></i>',
						[Url::to(['default/log', 'domain' => $data->name])],
						['title' => 'Боты', 'data-pjax' => 0, 'target' => '_blank']
					);
				}
			],
			[
				'attribute' => 'created_at',
				'value' => function ($data) {
					$date = new DateTime($data->created_at);
					return $date->format('d.m.Y');
				},
			],
            //'updated_at',
            [
                'class' => ActionColumn::className(),
                'template' => '{delete}',
                'urlCreator' => function ($action, Project $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>
</div>

<script>
    // JavaScript код для инициализации графиков
    document.addEventListener('DOMContentLoaded', function () {
        // Пример данных для графиков
        var chartData = <?= Json::encode($chartData) ?>;

        // Инициализация графиков для каждого элемента
        Object.keys(chartData).forEach(function (id) {
            var data = chartData[id];

            var options = {
                chart: {
                    height: 50,
                    type: 'area',
                    sparkline: { enabled: true },
                    toolbar: { show: false }
                },
                series: [{
                    name: "Хитов",
                    data: data.hitData
                }],
                stroke: {
                    curve: "smooth",
                    width: 1.5
                },
                xaxis: {
                    categories: data.categories
                },
              yaxis: {
                labels: {
                  formatter: function (value) {
                    if (value >= 1000000) {
                      return (value / 1000000).toFixed(1) + 'M';
                    } else if (value >= 1000) {
                      return (value / 1000).toFixed(1) + 'K';
                    }
                    return value;
                  }
                }
              },
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 1,
                        inverseColors: false,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [50, 100, 100, 100]
                    }
                }
            };

            new ApexCharts(document.querySelector("#chart_hits_" + id), options).render();

            var options2 = {
                chart: {
                    height: 50,
                    type: 'area',
                    sparkline: { enabled: true },
                    toolbar: { show: false }
                },
                series: [{
                    name: "Ботов",
                    data: data.botData
                }],
                stroke: {
                    curve: "smooth",
                    width: 1.5
                },
                xaxis: {
                    categories: data.categories
                },
                colors: ['#724ec0'],
                  yaxis: {
                    labels: {
                      formatter: function (value) {
                        if (value >= 1000000) {
                          return (value / 1000000).toFixed(1) + 'M';
                        } else if (value >= 1000) {
                          return (value / 1000).toFixed(1) + 'K';
                        }
                        return value;
                      }
                    }
                  },
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 1,
                        inverseColors: false,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [50, 100, 100, 100]
                    }
                }
            };

            new ApexCharts(document.querySelector("#chart_bots_" + id), options2).render();
        });
    });
</script>

<?php
Modal::begin([
	'id' => 'addProject',
	'size' => 'modal-lg',
	'title' => 'Add New Project',
]);

$model = new Project();

$model->loadDefaultValues();
//check redirect type, url, bots
if ($model->redirect_type !== '' && $model->redirect_type !== null)
	$model->redirect_type = explode(',', $model->redirect_type);
if ($model->redirect_urls !== '' && $model->redirect_urls !== null)
	$model->redirect_urls = str_replace(",", "\r\n", $model->redirect_urls);
if ($model->allowed_bots !== '' && $model->allowed_bots !== null)
	$model->allowed_bots = explode(',', $model->allowed_bots);

echo $this->render('_form', ['model' => $model]);

Modal::end();

?>
