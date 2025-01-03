<?php

use app\models\Project;
use app\models\Statistics;
use app\models\StatisticsLogs;
use yii\bootstrap5\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var yii\data\ActiveDataProvider $dataProviderFarm */
/** @var array $total */
/** @var array $totalFarm */

$projects = \app\models\Project::find()->all();
$items = ArrayHelper::map($projects, 'id', 'name');

$url_move_project = Url::to(['domain/move-project']);
$url_move_farm = Url::to(['domain/move-farm']);
$url_remove = Url::to(['domain/remove']);

$script = <<< JS
   $(document).ready(function () { 
         $('[data-bs-toggle="tooltip"]').tooltip();
       
         $('#move').click(function () {
             let keys = $('#w0').yiiGridView('getSelectedRows');
             let project = $('#projects').val();
             
             if (keys.length === 0) {
                 alert('Не выбраны строки')
                 return;
             }
             
             if (project.length === 0) {
                 alert('Не выбран проект')
                 return;
             }
             
             $.ajax({
                  url: '$url_move_project',
                  type: 'post',
                  dataType: 'json',
                  data: {keys: keys, project: project},
                  success: function (data) {
                    if (data.status === 'success') {
                        $('#projects').val('')
                        $.pjax.reload({container: '#domains_pjax', async: false})
                        alert('Успешно перемещено')
                    } else {
                        alert('Ошибка сохранения')
                    }
                  },
                  error: function (re) {
                     alert('Ошибка запроса')   
                  }
             })
         })
         
         $('#farm').click(function () {
             let keys = $('#w0').yiiGridView('getSelectedRows');
             
             if (keys.length === 0) {
                 alert('Не выбраны строки')
                 return;
             }
             
             if (!confirm('Вы уверены, что хотите перенести в ферму выделенные домены?')) {
                 return;
             }
             
             $.ajax({
                  url: '$url_move_farm',
                  type: 'post',
                  dataType: 'json',
                  data: {keys: keys},
                  success: function (data) {
                    if (data.status === 'success') {
                        $.pjax.reload({container: '#domains_pjax', async: false})
                        $.pjax.reload({container: '#pjaxFarmDomains', async: false})
                        alert('Успешно перемещено')
                    } else {
                        alert('Ошибка сохранения')
                    }
                  },
                  error: function (re) {
                     alert('Ошибка запроса')   
                  }
             })
         })
         
         $('#redirect').click(function () {
             let keys = $('#w0').yiiGridView('getSelectedRows');
             if (keys.length === 0) {
                 alert('Не выбраны строки')
                 return;
             }
             
             $('#redirect_keys').val(keys.join(','));
             $('#setRedirectModal').modal('show');
         })
         
         $('#move-domains').click(function () {
             let keys = $('#w0').yiiGridView('getSelectedRows');
             if (keys.length === 0) {
                 alert('Не выбраны строки')
                 return;
             }
             
             $('#hidden-domains').val(keys.join(','));
             $('#hidden-project').val($('#movetoprojectselect').val());
             $('#movedomainsmodal').modal('show');
         })
         
         $('#movetoprojectselect').change(function () {
          $('#hidden-project').val($(this).val());
          });
         
         $('#remove').click(function () {
             let keys = $('#w0').yiiGridView('getSelectedRows');
             
             if (keys.length === 0) {
                 alert('Не выбраны строки')
                 return;
             }
             
             if (!confirm('Вы уверены, что хотите удалить выбранные строки?')) {
                 return;
             }
             
             $.ajax({
                  url: '$url_remove',
                  type: 'post',
                  dataType: 'json',
                  data: {keys: keys},
                  success: function (data) {
                    if (data.status === 'success') {
                        $.pjax.reload({container: '#domains_pjax', async: false})
                        alert('Успешно удалено')
                    } else {
                        alert('Ошибка удаления')
                    }
                  },
                  error: function (re) {
                     alert('Ошибка запроса')   
                  }
             })
         })
         
         $('#showFarmButton').on('click', function () {
            var container = document.getElementById('farmDomainsContainer');
            if (container.style.display === 'none') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
         })
         
         $('.aRedirect').on('click', function (){
            $.get($(this).attr('href'), function(data) {
                $('#redirectModal').find('.modal-body').html(data)
                });
            return false;
        });
         
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        popoverTriggerList.forEach(function (popoverTriggerEl) {
            var popover = new bootstrap.Popover(popoverTriggerEl, {
                trigger: 'manual',
                html: true
            });

            popoverTriggerEl.addEventListener('mouseenter', function () {
                popover.show();
            });

            popoverTriggerEl.addEventListener('mouseleave', function () {
                popover.hide();
            });
        });
    });
JS;
$this->registerJs($script);

$scriptFarm = <<< JS
   $(document).ready(function () {
     $('#remove-farm').click(function () {
             let keys = $('#farm-grid').yiiGridView('getSelectedRows');
             
             if (keys.length === 0) {
                 alert('Не выбраны строки из фермы')
                 return;
             }
             
             if (!confirm('Вы уверены, что хотите удалить выбранные фермы?')) {
                 return;
             }
             
             $.ajax({
                  url: '$url_remove',
                  type: 'post',
                  dataType: 'json',
                  data: {keys: keys},
                  success: function (data) {
                    if (data.status === 'success') {
                        $.pjax.reload({container: '#pjaxFarmDomains', async: false})
                        alert('Успешно удалено')
                    } else {
                        alert('Ошибка удаления')
                    }
                  },
                  error: function (re) {
                     alert('Ошибка запроса')   
                  }
             })
         })
         
      $('#revert-farm').click(function () {
             let keys = $('#farm-grid').yiiGridView('getSelectedRows');
             
             if (keys.length === 0) {
                 alert('Не выбраны фермы')
                 return;
             }
             
             if (!confirm('Вы уверены, что хотите перенести из фермы выделенные домены?')) {
                 return;
             }
             
             $.ajax({
                  url: '$url_move_farm',
                  type: 'post',
                  dataType: 'json',
                  data: {keys: keys, revert: true},
                  success: function (data) {
                    if (data.status === 'success') {
                        $.pjax.reload({container: '#pjaxFarmDomains', async: false})
                        $.pjax.reload({container: '#domains_pjax', async: false})
                        alert('Успешно перемещено')
                    } else {
                        alert('Ошибка сохранения')
                    }
                  },
                  error: function (re) {
                     alert('Ошибка запроса')   
                  }
             })
         })
         
         $('#move-farms').click(function () {
             let keys = $('#farm-grid').yiiGridView('getSelectedRows');
             if (keys.length === 0) {
                 alert('Не выбраны строки')
                 return;
             }
             
             $('#hidden-domains').val(keys.join(','));
             $('#hidden-project').val($('#movetoprojectselect').val());
             $('#movedomainsmodal').modal('show');
         })
     
     });
JS;
$this->registerJs($scriptFarm);
?>

<script>
    function getdomainllist(templ) {
        let keys = $('#w0').yiiGridView('getSelectedRows');

        if (keys.length === 0) {
            alert('Не выбраны строки')
            return;
        }

        $.ajax({
            url: "/panel/domain/get-domain-list",
            type: "POST",
            cache: false,
            data: {template: templ, keys: keys},
            success: function (data) {
                $('#pre_getdomainllisttext').hide();
                $('#getdomainllisttext').val(data);
                $('#getdomainllistmodal').modal('show');
            },
            error: function (jqXHR, textStatus) {
                alert('Error: ' + textStatus);
            }
        });
    }

    function getfarmllist(templ) {
        let keys = $('#farm-grid').yiiGridView('getSelectedRows');

        if (keys.length === 0) {
            alert('Не выбраны фермы')
            return;
        }

        $.ajax({
            url: "/panel/domain/get-domain-list",
            type: "POST",
            cache: false,
            data: {template: templ, keys: keys},
            success: function (data) {
                $('#pre_getfarmlisttext').hide();
                $('#getfarmlisttext').val(data);
                $('#getfarmlistmodal').modal('show');
            },
            error: function (jqXHR, textStatus) {
                alert('Error: ' + textStatus);
            }
        });
    }
</script>

<?php
Modal::begin([
    'id' => 'getdomainllistmodal',
    'title' => 'Список доменов',
    'size' => Modal::SIZE_LARGE,
]);
echo Html::textarea('getdomainllisttext', '', ['id' => 'getdomainllisttext', 'class' => 'form-control', 'rows' => 10]);
Modal::end();
?>

<?php
Modal::begin([
    'id' => 'getfarmlistmodal',
    'title' => 'Список доменов',
    'size' => Modal::SIZE_LARGE,
]);
echo Html::textarea('getfarmlisttext', '', ['id' => 'getfarmlisttext', 'class' => 'form-control', 'rows' => 10]);
Modal::end();
?>

<?php
Modal::begin([
    'id' => 'movedomainsmodal',
    'title' => 'Перенос доменов в другой проект',
]);

$projectOptions = ArrayHelper::map(Project::find()->all(), 'id', 'name');

?>

<div class="modal-body">
    <div class="mb-3">
        <?= Html::label('Новый проект', 'movetoprojectselect', ['class' => 'form-label']) ?>
        <?= Html::dropDownList('movetoproject', null, $projectOptions, [
            'class' => 'form-select mb-3',
            'id' => 'movetoprojectselect'
        ]) ?>
    </div>

    <?= Html::beginForm(Url::to(['/panel/domain/change-project']), 'post', ['id' => 'move-domains-form']) ?>
    <?= Html::hiddenInput('domains', '', ['id' => 'hidden-domains']) ?>
    <?= Html::hiddenInput('project', '', ['id' => 'hidden-project']) ?>
    <?= Html::submitButton('Перенести', ['class' => 'mt-3 btn btn-primary']) ?>
    <?= Html::endForm() ?>
</div>

<?php Modal::end(); ?>


<?php
Modal::begin([
    'id' => 'setRedirectModal',
    'title' => 'Установить редирект',
]);

echo $this->render('/redirects/_form_domain');

Modal::end();
?>


<?php Pjax::begin(['id' => 'domains_pjax']) ?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => [
        'class' => 'table table-striped table-sm table-hover'
    ],
    'headerRowOptions' => ['class' => 'bg-secondary bg-opacity-10', 'style' => 'border-bottom:3px #3d4050 solid'],
    'footerRowOptions' => ['class' => 'bg-secondary bg-opacity-10', 'style' => 'border-top:3px #3d4050 solid'],
    'showFooter' => true,
    'columns' => [
        // ['class' => 'yii\grid\SerialColumn'],
        //'id',
        [
            'attribute' => 'created_at',
            'value' => function ($data) {
                return \app\components\Helpers::countDaysBetweenDates($data->created_at) . ' дн.';
            },
            'footer' => "Всего"
        ],
        [
            'format' => 'raw',
            'contentOptions' => function () {
                return ['style' => 'width: 20px'];
            },
            'value' => function ($data) {
                $link = "https://$data->domain/{w5}";
                $link = \app\components\Helpers::formatRegexUrl($link);
                return '<a target="_blank" href="' . $link . '"><i class="bx bx-sitemap"></i></a>';
            }
        ],
        [
            'format' => 'raw',
            'contentOptions' => function () {
                return ['style' => 'width: 20px'];
            },
            'value' => function ($data) {
                return '<a target="_blank" href="https://www.google.com/search?q=site%3A' . $data->domain . '"><i class="bx bxl-google"></i></a>';
            }
        ],
        [
            'format' => 'raw',
            'contentOptions' => function () {
                return ['style' => 'width: 20px'];
            },
            'value' => function ($data) {
                return Html::a(
                    '<i class="bx bx-poll"></i>',
                    [Url::to(['default/log', 'domain' => $data->domain])],
                    ['title' => 'Боты', 'data-pjax' => 0, 'target' => '_blank']
                );
            }
        ],
        [
            'class' => 'yii\grid\CheckboxColumn',
            'contentOptions' => function () {
                return ['style' => 'width: 20px'];
            },
        ],
        [
            'attribute' => 'domain',
            'format' => 'raw',
            'value' => function ($data) {
                $url = 'https://' . $data->domain;
                $id = 'domain_' . $data->id;
                $linkClass = $data->checkRedirects() ? 'link-secondary' : 'link-info';
                $domainLink = Html::a(Html::tag('span', Html::encode($data->domain), ['id' => $id]), $url, [
                    'target' => '_blank',
                    'class' => $linkClass
                ]);

                if ($data->checkRedirects()) {
                    $icons = Html::tag('sup', Html::tag('i', '', [
                            'class' => 'bx bx-edit-alt link-secondary cursor-hand',
                            'data-id' => $data->id,
                            'data-bs-toggle' => 'modal',
                            'data-bs-target' => '#domain-redirects',
                            'onclick' => "loadDomainRedirects({$data->id})"
                        ]) . Html::tag('i', '', [
                            'class' => 'bx bx-list-ul link-secondary cursor-hand fs-10',
                            'data-id' => $data->id,
                            'data-title' => $data->domain,
                            'onclick' => "loadModalContent($(this).data('id'), $(this).data('title'))",
                        ]));
                } else {
                    $icons = '';
                }

                return $domainLink . ' ' . $icons;
            },
        ],
        [
            'header' => 'Посетители',
            'headerOptions' => [
                'scope' => 'col',
                'colspan' => 4,
                'class' => 'bg-info bg-opacity-25 text-center',
                'data-bs-toggle' => 'tooltip',
                'data-bs-html' => 'true',
                'data-bs-placement' => 'top',
                'data-bs-original-title' => "<span class='badge border border-primary text-primary'>все</span> <span class='badge border border-primary text-primary'>вчера</span> <span class='badge border border-primary text-primary'>сегодня</span> <span class='badge border border-primary text-primary'>прирост</span> <span class='badge border border-primary text-primary'>текущий час</span><br><span class='badge bg-secondary'>прирост обновляется раз в час</span>",
                'aria-describedby' => 'tooltip418967',
            ],
            'encodeLabel' => false,
            'contentOptions' => ['class' => 'd-none'],
            'footerOptions' => ['class' => 'd-none'],
        ],
        [
            'header' => 'Боты',
            'headerOptions' => [
                'scope' => 'col',
                'colspan' => 4,
                'class' => 'bg-primary bg-opacity-25 text-center',
                'data-bs-toggle' => 'tooltip',
                'data-bs-html' => 'true',
                'data-bs-placement' => 'top',
                'data-bs-original-title' => "<span class='badge border border-primary text-primary'>все</span> <span class='badge border border-primary text-primary'>вчера</span> <span class='badge border border-primary text-primary'>сегодня</span> <span class='badge border border-primary text-primary'>прирост</span> <span class='badge border border-primary text-primary'>текущий час</span><br><span class='badge bg-secondary'>прирост обновляется раз в час</span>",
                'aria-describedby' => 'tooltip418967',
            ],
            'encodeLabel' => false,
            'contentOptions' => ['class' => 'd-none'],
            'footerOptions' => ['class' => 'd-none'],
        ],
        [
            'headerOptions' => ['style' => 'display: none;'],
            'contentOptions' => ['class' => 'tduser'],
            'footerOptions' => ['class' => 'tduser'],
            'value' => function ($data) {
                $stat = Statistics::class;
                $hits = $stat::getTotalHits($data->id);
                return number_format($hits, 0, ',', '.');
            },
            'footer' => number_format($total['hits']['total'], 0, ',', '.')
        ],
        [
            'headerOptions' => ['style' => 'display: none;'],
            'contentOptions' => ['class' => 'tduser'],
            'footerOptions' => ['class' => 'tduser'],
            'value' => function ($data) {
                $stat = Statistics::class;
                $hits = $stat::getYesterdayHits($data->id);
                return number_format($hits, 0, ',', '.');
            },
            'footer' => number_format($total['hits']['yesterday'], 0, ',', '.')
        ],
        [
            'headerOptions' => ['style' => 'display: none;'],
            'contentOptions' => ['class' => 'tduser'],
            'footerOptions' => ['class' => 'tduser'],
            'value' => function ($data) {
                $stat = Statistics::class;
                $hits = $stat::getTodayHits($data->id);
                return number_format($hits, 0, ',', '.');
            },
            'footer' => number_format($total['hits']['today'], 0, ',', '.')
        ],
        [
            'headerOptions' => ['style' => 'display: none;'],
            'contentOptions' => ['class' => 'tduser tdleft'],
            'footerOptions' => ['class' => 'tduser tdleft'],
            'value' => function ($data) {
                $stat = Statistics::class;
                $todayHits = $stat::getTodayHits($data->id);
                $yesterdayHits = $stat::getYesterdayHits($data->id);
                $difference = $todayHits - $yesterdayHits;
                $badgeClass = $difference >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                $sign = $difference >= 0 ? '+' : '-';
                $difference = abs($difference);  // Чтобы отображать без знака при разнице
                return "<span class=\"badge $badgeClass badge-border\">$sign$difference</span>";
            },
            'format' => 'raw',  // Это нужно для того, чтобы GridView рендерил HTML код
        ],
        [
            'headerOptions' => ['style' => 'display: none;'],
            'contentOptions' => ['class' => 'tdbot'],
            'footerOptions' => ['class' => 'tdbot'],
            'value' => function ($data) {
                $stat = Statistics::class;
                $bots = $stat::getTotalBots($data->id);
                return number_format($bots, 0, ',', '.');
            },
            'footer' => number_format($total['bots']['total'], 0, ',', '.')
        ],
        [
            'headerOptions' => ['style' => 'display: none;'],
            'contentOptions' => ['class' => 'tdbot'],
            'footerOptions' => ['class' => 'tdbot'],
            'value' => function ($data) {
                $stat = Statistics::class;
                $bots = $stat::getYesterdayBots($data->id);
                return number_format($bots, 0, ',', '.');
            },
            'footer' => number_format($total['bots']['yesterday'], 0, ',', '.')
        ],
        [
            'headerOptions' => ['style' => 'display: none;'],
            'contentOptions' => ['class' => 'tdbot'],
            'footerOptions' => ['class' => 'tdbot'],
            'value' => function ($data) {
                $stat = Statistics::class;
                $bots = $stat::getTodayBots($data->id);
                return number_format($bots, 0, ',', '.');
            },
            'footer' => number_format($total['bots']['today'], 0, ',', '.')
        ],
        [
            'headerOptions' => ['style' => 'display: none;'],
            'contentOptions' => ['class' => 'tduser tdleft'],
            'footerOptions' => ['class' => 'tduser tdleft'],
            'value' => function ($data) {
                $stat = Statistics::class;
                $todayHits = $stat::getTodayBots($data->id);
                $yesterdayHits = $stat::getYesterdayBOts($data->id);
                $difference = $todayHits - $yesterdayHits;
                $badgeClass = $difference >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                $sign = $difference >= 0 ? '+' : '-';
                $difference = abs($difference);  // Чтобы отображать без знака при разнице
                return "<span class=\"badge $badgeClass badge-border\">$sign$difference</span>";
            },
            'format' => 'raw',  // Это нужно для того, чтобы GridView рендерил HTML код
        ],
        'metka',
    ],
    'pager' => [
        'class' => yii\widgets\LinkPager::class,
        'options' => ['class' => 'pagination'],
        'activePageCssClass' => 'page-item active',
        'disabledPageCssClass' => 'disabled page-item',
        'pageCssClass' => 'page-item',
        'linkOptions' => ['class' => 'page-link'],
        'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link disabled'],
    ]
]); ?>
<?php Pjax::end() ?>

<div class="row">
    <div class="col-lg-12">
        <div id="forcheckeddomains" class="float-start" style="pointer-events: auto; opacity: 1;">
            <button type="button" id="farm"
                    class="btn btn-sm btn-outline-danger waves-effect waves-light">
                <i class="bx bx-archive-in fs-18 align-bottom me-1"></i> В ФЕРМУ
            </button>
            <button type="button" id="redirect"
                    class="btn btn-sm btn-outline-secondary waves-effect waves-light">
                <i class="bx bx-link-external fs-18 align-bottom me-1"></i> УСТАНОВИТЬ РЕДИРЕКТ
            </button>
            <button class="btn btn-sm btn-outline-primary waves-effect waves-light dropdown-toggle" type="button"
                    id="getdomainllist" data-bs-toggle="dropdown" aria-expanded="false">
                ПОЛУЧИТЬ СПИСОК
            </button>
            <div class="dropdown-menu" aria-labelledby="getdomainllist">
                <a class="dropdown-item" href="#"
                   onclick="getdomainllist('https://{domain}');">https://{domain}</a>
                <a class="dropdown-item" href="#"
                   onclick="getdomainllist('https://{domain}/{w6}');">https://{domain}/{w6}</a>
                <a class="dropdown-item" href="#"
                   onclick="getdomainllist('https://{domain}/page/{w6}');">https://{domain}/page/{w6}</a>
                <a class="dropdown-item" href="#"
                   onclick="getdomainllist('https://{domain}/page/map/{w6}');">https://{domain}/page/map/{w6}</a>
                <a class="dropdown-item" href="#"
                   onclick="getdomainllist('https://{domain}/news/{w6}');">https://{domain}/news/{w6}</a>
                <a class="dropdown-item" href="#"
                   onclick="getdomainllist('https://{w5}.{domain}/{w6}');">https://{w5}.{domain}/{w6}</a>
                <a class="dropdown-item" href="#"
                   onclick="getdomainllist('https://{w5}.{domain}/news/{w6}');">https://{w5}.{domain}/news/{w6}</a>
            </div>
            <button class="btn btn-sm btn-outline-primary waves-effect waves-light dropdown-toggle" type="button"
                    id="movedomains" data-bs-toggle="dropdown" aria-expanded="false">
                ДРУГИЕ ДЕЙСТВИЯ
            </button>
            <div class="dropdown-menu" aria-labelledby="movedomains">
                <a class="dropdown-item" href="#" id="move-domains">
                    Перенести в другой проект
                </a>
                <button class="dropdown-item" id="remove">Удалить</button>
            </div>
        </div>
        <div id="pre_showbanned" class="float-end spinner-border text-primary fs-14" role="status"
             style="width:34px;height:34px;display:none"><span class="sr-only">Loading...</span></div>
        <button type="button" id="showFarmButton"
                class="btn btn-sm btn-ghost-danger waves-effect waves-light btn-border float-end"><i
                    class="bx bx-archive fs-18 align-bottom me-1"></i> ДОМЕНЫ ПРОЕКТА В ФЕРМЕ
            <sup>(<?= $dataProviderFarm->getTotalCount() ?>)</sup>
        </button>
    </div>
</div>

<div class="row mt-4" id="farmDomainsContainer" style="display: none;">
    <div class="col-lg-12">
        <hr>
        <h4 class="mt-3">Домены в ферме:</h4>
        <?php Pjax::begin(['id' => 'pjaxFarmDomains']); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProviderFarm,
            'id' => 'farm-grid',
            'tableOptions' => [
                'class' => 'table table-striped table-sm table-hover'
            ],
            'headerRowOptions' => ['class' => 'bg-danger bg-opacity-10', 'style' => 'border-bottom:3px #3d4050 solid'],
            'footerRowOptions' => ['class' => 'bg-danger bg-opacity-10', 'style' => 'border-top:3px #3d4050 solid'],
            'showFooter' => true,
            'columns' => [
                // ['class' => 'yii\grid\SerialColumn'],
                //'id',
                [
                    'attribute' => 'created_at',
                    'value' => function ($data) {
                        return \app\components\Helpers::countDaysBetweenDates($data->created_at) . ' дн.';
                    },
                    'footer' => "Всего"
                ],
                [
                    'format' => 'raw',
                    'contentOptions' => function () {
                        return ['style' => 'width: 20px'];
                    },
                    'value' => function ($data) {
                        return '<a target="_blank" href="https://www.google.com/search?q=site%3A' . $data->domain . '"><i class="bx bxl-google"></i></a>';
                    }
                ],
                [
                    'format' => 'raw',
                    'contentOptions' => function () {
                        return ['style' => 'width: 20px'];
                    },
                    'value' => function ($data) {
                        return Html::a(
                            '<i class="bx bx-poll"></i>',
                            [Url::to(['default/log', 'domain' => $data->domain])],
                            ['title' => 'Боты', 'data-pjax' => 0, 'target' => '_blank']
                        );
                    }
                ],
                [
                    'class' => 'yii\grid\CheckboxColumn',
                    'contentOptions' => function () {
                        return ['style' => 'width: 20px'];
                    },
                ],
                [
                    'attribute' => 'domain',
                    'format' => 'raw',
                    'value' => function ($data) {
                        $url = 'https://' . $data->domain;
                        $id = 'domain_' . $data->id;
                        return Html::a(
                            Html::tag('span', Html::encode($data->domain), ['id' => $id]),
                            $url,
                            ['target' => '_blank', 'class' => 'link-danger']
                        );
                    },
                ],
                [
                    'header' => 'Посетители',
                    'headerOptions' => [
                        'scope' => 'col',
                        'colspan' => 4,
                        'class' => 'bg-info bg-opacity-25 text-center',
                        'data-bs-toggle' => 'tooltip',
                        'data-bs-html' => 'true',
                        'data-bs-placement' => 'top',
                        'data-bs-original-title' => "<span class='badge border border-primary text-primary'>все</span> <span class='badge border border-primary text-primary'>вчера</span> <span class='badge border border-primary text-primary'>сегодня</span> <span class='badge border border-primary text-primary'>прирост</span> <span class='badge border border-primary text-primary'>текущий час</span><br><span class='badge bg-secondary'>прирост обновляется раз в час</span>",
                        'aria-describedby' => 'tooltip418967',
                    ],
                    'encodeLabel' => false,
                    'contentOptions' => ['class' => 'd-none'],
                    'footerOptions' => ['class' => 'd-none'],
                ],
                [
                    'header' => 'Боты',
                    'headerOptions' => [
                        'scope' => 'col',
                        'colspan' => 4,
                        'class' => 'bg-primary bg-opacity-25 text-center',
                        'data-bs-toggle' => 'tooltip',
                        'data-bs-html' => 'true',
                        'data-bs-placement' => 'top',
                        'data-bs-original-title' => "<span class='badge border border-primary text-primary'>все</span> <span class='badge border border-primary text-primary'>вчера</span> <span class='badge border border-primary text-primary'>сегодня</span> <span class='badge border border-primary text-primary'>прирост</span> <span class='badge border border-primary text-primary'>текущий час</span><br><span class='badge bg-secondary'>прирост обновляется раз в час</span>",
                        'aria-describedby' => 'tooltip418967',
                    ],
                    'encodeLabel' => false,
                    'contentOptions' => ['class' => 'd-none'],
                    'footerOptions' => ['class' => 'd-none'],
                ],
                [
                    'headerOptions' => ['style' => 'display: none;'],
                    'contentOptions' => ['class' => 'tduser'],
                    'footerOptions' => ['class' => 'tduser'],
                    'value' => function ($data) {
                        $stat = Statistics::class;
                        $hits = $stat::getTotalHits($data->id);
                        return number_format($hits, 0, ',', '.');
                    },
                    'footer' => number_format($totalFarm['hits']['total'], 0, ',', '.')
                ],
                [
                    'headerOptions' => ['style' => 'display: none;'],
                    'contentOptions' => ['class' => 'tduser'],
                    'footerOptions' => ['class' => 'tduser'],
                    'value' => function ($data) {
                        $stat = Statistics::class;
                        $hits = $stat::getYesterdayHits($data->id);
                        return number_format($hits, 0, ',', '.');
                    },
                    'footer' => number_format($totalFarm['hits']['yesterday'], 0, ',', '.')
                ],
                [
                    'headerOptions' => ['style' => 'display: none;'],
                    'contentOptions' => ['class' => 'tduser'],
                    'footerOptions' => ['class' => 'tduser'],
                    'value' => function ($data) {
                        $stat = Statistics::class;
                        $hits = $stat::getTodayHits($data->id);
                        return number_format($hits, 0, ',', '.');
                    },
                    'footer' => number_format($totalFarm['hits']['today'], 0, ',', '.')
                ],
                [
                    'headerOptions' => ['style' => 'display: none;'],
                    'contentOptions' => ['class' => 'tduser tdleft'],
                    'footerOptions' => ['class' => 'tduser tdleft'],
                    'value' => function ($data) {
                        $stat = Statistics::class;
                        $todayHits = $stat::getTodayHits($data->id);
                        $yesterdayHits = $stat::getYesterdayHits($data->id);
                        $difference = $todayHits - $yesterdayHits;
                        $badgeClass = $difference >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                        $sign = $difference >= 0 ? '+' : '-';
                        $difference = abs($difference);  // Чтобы отображать без знака при разнице
                        return "<span class=\"badge $badgeClass badge-border\">$sign$difference</span>";
                    },
                    'format' => 'raw',  // Это нужно для того, чтобы GridView рендерил HTML код
                ],
                [
                    'headerOptions' => ['style' => 'display: none;'],
                    'contentOptions' => ['class' => 'tdbot'],
                    'footerOptions' => ['class' => 'tdbot'],
                    'value' => function ($data) {
                        $stat = Statistics::class;
                        $bots = $stat::getTotalBots($data->id);
                        return number_format($bots, 0, ',', '.');
                    },
                    'footer' => number_format($totalFarm['bots']['total'], 0, ',', '.')
                ],
                [
                    'headerOptions' => ['style' => 'display: none;'],
                    'contentOptions' => ['class' => 'tdbot'],
                    'footerOptions' => ['class' => 'tdbot'],
                    'value' => function ($data) {
                        $stat = Statistics::class;
                        $bots = $stat::getYesterdayBots($data->id);
                        return number_format($bots, 0, ',', '.');
                    },
                    'footer' => number_format($totalFarm['bots']['yesterday'], 0, ',', '.')
                ],
                [
                    'headerOptions' => ['style' => 'display: none;'],
                    'contentOptions' => ['class' => 'tdbot'],
                    'footerOptions' => ['class' => 'tdbot'],
                    'value' => function ($data) {
                        $stat = Statistics::class;
                        $bots = $stat::getTodayBots($data->id);
                        return number_format($bots, 0, ',', '.');
                    },
                    'footer' => number_format($totalFarm['bots']['today'], 0, ',', '.')
                ],
                [
                    'headerOptions' => ['style' => 'display: none;'],
                    'contentOptions' => ['class' => 'tduser tdleft'],
                    'footerOptions' => ['class' => 'tduser tdleft'],
                    'value' => function ($data) {
                        $stat = Statistics::class;
                        $todayHits = $stat::getTodayBots($data->id);
                        $yesterdayHits = $stat::getYesterdayBOts($data->id);
                        $difference = $todayHits - $yesterdayHits;
                        $badgeClass = $difference >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                        $sign = $difference >= 0 ? '+' : '-';
                        $difference = abs($difference);  // Чтобы отображать без знака при разнице
                        return "<span class=\"badge $badgeClass badge-border\">$sign$difference</span>";
                    },
                    'format' => 'raw',  // Это нужно для того, чтобы GridView рендерил HTML код
                ],
                'metka',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => function ($url, $data) {
                            return Html::a(
                                '<i class="text-danger cursor-hand bx bx-trash"></i>',
                                $url,
                                [
                                    'class' => 'text-danger',
                                    'title' => 'Удалить',
                                    'data' => [
                                        'confirm' => 'Вы уверены, что хотите удалить ' . $data->domain . ' ?',
                                        'method' => 'post',
                                    ],
                                ]
                            );
                        },
                    ],
                    'urlCreator' => function ($action, $data) {
                        return Url::to(['/panel/domain/delete', 'id' => $data->id]);
                    },
                ],
            ],
            'pager' => [
                'class' => yii\widgets\LinkPager::class,
                'options' => ['class' => 'pagination'],
                'activePageCssClass' => 'page-item active',
                'disabledPageCssClass' => 'disabled page-item',
                'pageCssClass' => 'page-item',
                'linkOptions' => ['class' => 'page-link'],
                'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link disabled'],
            ]
        ]); ?>

        <?php Pjax::end(); ?>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div id="forcheckedfarm" class="float-start" style="pointer-events: auto; opacity: 1;">
                <button type="button" id="revert-farm"
                        class="btn btn-sm btn-outline-success waves-effect waves-light">
                    <i class="bx bx-archive-out fs-18 align-bottom me-1"></i>В РАБОТУ
                </button>
                <button class="btn btn-sm btn-outline-primary waves-effect waves-light dropdown-toggle" type="button"
                        id="getfarmllist" data-bs-toggle="dropdown" aria-expanded="false">
                    ПОЛУЧИТЬ СПИСОК
                </button>
                <div class="dropdown-menu" aria-labelledby="getfarmllist">
                    <a class="dropdown-item" href="#"
                       onclick="getfarmllist('https://{domain}');">https://{domain}</a>
                    <a class="dropdown-item" href="#"
                       onclick="getfarmllist('https://{domain}/{w6}');">https://{domain}/{w6}</a>
                    <a class="dropdown-item" href="#"
                       onclick="getfarmllist('https://{domain}/page/{w6}/');">https://{domain}/page/{w6}/</a>
                    <a class="dropdown-item" href="#"
                       onclick="getfarmllist('https://{domain}/page/map/{w6}/');">https://{domain}/page/map/{w6}/</a>
                    <a class="dropdown-item" href="#"
                       onclick="getfarmllist('https://{domain}/news/{w6}/');">https://{domain}/news/{w6}/</a>
                    <a class="dropdown-item" href="#"
                       onclick="getfarmllist('https://{w5}.{domain}/{w6}');">https://{w5}.{domain}/{w6}</a>
                    <a class="dropdown-item" href="#"
                       onclick="getfarmllist('https://{w5}.{domain}/news/{w6}/');">https://{w5}.{domain}/news/{w6}/</a>
                </div>
                <button class="btn btn-sm btn-outline-primary waves-effect waves-light dropdown-toggle" type="button"
                        id="movefarms" data-bs-toggle="dropdown" aria-expanded="false">
                    ДРУГИЕ ДЕЙСТВИЯ
                </button>
                <div class="dropdown-menu" aria-labelledby="movefarms">
                    <a class="dropdown-item" href="#" id="move-farms">
                        Перенести в другой проект
                    </a>
                    <button class="dropdown-item" id="remove-farm">Удалить</button>
                </div>
            </div>
        </div>
    </div>

</div>


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

<?php
Modal::begin([
    'id' => 'domain-redirects',
    'title' => 'Редиректы домена',
]);

echo '<div class="modal-body">Загрузка...</div>';

Modal::end();
?>

<script>
    function loadModalContent(id, title) {
        if ($(event.target).closest('.text-danger').length > 0) {
            return;
        }

        function fetchData() {
            $.ajax({
                url: '<?= Url::to(['/panel/redirects/entity-progress']) ?>',
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

        let intervalId = setInterval(function () {
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

    function loadDomainRedirects(id) {
        $.ajax({
            url: '/panel/redirects/load-domain-redirect',
            type: 'GET',
            data: {id: id},
            success: function (data) {
                $('#domain-redirects .modal-body').html(data);
            },
            error: function () {
                alert('Произошла ошибка при загрузке данных.');
            }
        });
    }

</script>