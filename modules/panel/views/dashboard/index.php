<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

$this->title = 'Статистика';
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Подключение jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  function statindex(wh, by, bl) {
    $.ajax({
      url: "<?= \yii\helpers\Url::to(['dashboard/get-statistics']) ?>",
      type: "GET",
      cache: false,
      data: {
        what: wh,
        group: by,
        block: bl,
      },
      success: function(response) {
        let canvasElement = document.getElementById(bl);
        if (!canvasElement) {
          console.error("Canvas element with ID '" + bl + "' not found.");
          return;
        }

        let chart = Chart.getChart(bl);

        // Determine the appropriate header based on the type and filter
        let headerText = '';
        let label = wh === 'bot' ? 'Ботов' : 'Посетителей';
        let specificHeader = '';

        switch (bl) {
          case 'userchart':
            specificHeader = 'Посетители';
            break;
          case 'botchart':
            specificHeader = 'Ботов';
            break;
          case 'farmchart':
            specificHeader = 'Боты в ферме';
            break;
          default:
            specificHeader = 'Данные';
        }

        switch (by) {
          case 'month':
            headerText = `${specificHeader} по месяцам`;
            break;
          case 'days':
            headerText = `${specificHeader} по дням`;
            break;
          case 'hour':
            headerText = `${specificHeader} по часам`;
            break;
          default:
            headerText = specificHeader;
        }

        document.getElementById(bl + '_head').innerText = headerText;

        if (chart) {
          chart.data.labels = response.categories;
          chart.data.datasets[0].data = response.data;
          chart.update();
        } else {
          new Chart(canvasElement, {
            type: 'line',
            data: {
              labels: response.categories,
              datasets: [{
                label: label,
                data: response.data,
                fill: true,
                backgroundColor: wh === 'bot' ? 'rgba(114,78,192,0.1)' : 'rgba(102,145,231,0.1)',
                borderColor: wh === 'bot' ? 'rgb(114,78,192)' : 'rgb(102,145,231)',
                tension: 0.2
              }]
            },
            options: {
              scales: {
                y: {
                  beginAtZero: true
                }
              }
            }
          });
        }
      },
      error: function(xhr, status, error) {
        console.error("Ошибка AJAX-запроса: " + error);
      }
    });
  }
</script>

<script>
  function topindex(wh, by, bl) {
    $.ajax({
      url: "<?= \yii\helpers\Url::to(['dashboard/get-statistics-top']) ?>",
      type: "GET",
      cache: false,
      data: { what: wh, group: by, block: bl },
      success: function(response) {
        if (response.header && response.content) {
          $('#' + bl + '_head').html(response.header);
          $('#' + bl).html(response.content);
        }
      },
      error: function(xhr, status, error) {
        console.error("Ошибка AJAX-запроса: " + error);
      }
    });
  }
</script>

<script>
  function updateStats() {
    function formatNumber(value) {
      const sign = value < 0 ? '-' : '';
      value = Math.abs(value);

      if (value >= 1000000) {
        return sign + (value / 1000000).toFixed(1) + 'M';
      } else if (value >= 1000) {
        return sign + (value / 1000).toFixed(1) + 'K';
      }
      return sign + value;
    }

    $.ajax({
      url: "<?= \yii\helpers\Url::to(['dashboard/update-stats']) ?>",
      method: 'GET',
      success: function(data) {
        // Обновление элементов на странице
        $('#iactivedomains').text(data.active_domain);
        $('#ibandomains').text(data.farm_domain_count + ' в ферме');

        // Обновление прироста трафика (active hit)
        $('#todaystat_hit').text(formatNumber(data.active_today_hits));
        let active_hits_diff =  formatNumber(data.active_hits_diff)
        if (data.active_hits_diff >= 0) {
          $('#prirosthit_badge')
            .removeClass('bg-danger-subtle text-danger')
            .addClass('bg-success-subtle text-success')
            .html('<i class="ri-arrow-up-s-line fs-13 align-middle me-1"></i>+' + active_hits_diff);
        } else {
          $('#prirosthit_badge')
            .removeClass('bg-success-subtle text-success')
            .addClass('bg-danger-subtle text-danger')
            .html('<i class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>' + active_hits_diff);
        }

        // Обновление прироста трафика (farm hit)
        $('#todaystatban_hit_badge').text(formatNumber(data.farm_today_hits) + ' в ферме');
        let farm_hits_diff =  formatNumber(data.farm_hits_diff)
        if (data.farm_hits_diff >= 0) {
          $('#prirosthit_badge_farm')
            .removeClass('bg-danger-subtle text-danger')
            .addClass('bg-success-subtle text-success')
            .html('<i class="ri-arrow-up-s-line fs-13 align-middle me-1"></i>+' + farm_hits_diff);
        } else {
          $('#prirosthit_badge_farm')
            .removeClass('bg-success-subtle text-success')
            .addClass('bg-danger-subtle text-danger')
            .html('<i class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>' + farm_hits_diff);
        }

        // Обновление количества ботов (active bot)
        $('#todaystat_bot').text(formatNumber(data.active_today_bots));
        let active_bots_diff = formatNumber(data.active_bots_diff)
        if (data.active_bots_diff >= 0) {
          $('#prirostbot_badge')
            .removeClass('bg-danger-subtle text-danger')
            .addClass('bg-success-subtle text-success')
            .html('<i class="ri-arrow-up-s-line fs-13 align-middle me-1"></i>+' + active_bots_diff);
        } else {
          $('#prirostbot_badge')
            .removeClass('bg-success-subtle text-success')
            .addClass('bg-danger-subtle text-danger')
            .html('<i class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>' + active_bots_diff);
        }

        // Обновление количества ботов (farm bot)
        $('#todaystatban_bot_badge').text(formatNumber(data.farm_today_bots) + ' в ферме');
        let farm_bots_diff = formatNumber(data.farm_bots_diff)
        if (data.farm_bots_diff >= 0) {
          $('#prirostbot_badge_farm')
            .removeClass('bg-danger-subtle text-danger')
            .addClass('bg-success-subtle text-success')
            .html('<i class="ri-arrow-up-s-line fs-13 align-middle me-1"></i>+' + farm_bots_diff);
        } else {
          $('#prirostbot_badge_farm')
            .removeClass('bg-success-subtle text-success')
            .addClass('bg-danger-subtle text-danger')
            .html('<i class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>' + farm_bots_diff);
        }

      },
      error: function() {
        console.error('Ошибка обновления статистики');
      }
    });
  }

  // Запуск обновления каждые 60 секунд
  setInterval(updateStats, 60000);

  // Запуск обновления сразу при загрузке страницы
  updateStats();

</script>

<div class="row project-wrapper">
    <div class="col-xxl-8">
        <div class="row">
            <div class="col-xl-4">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-secondary-subtle text-secondary rounded-2 fs-2">
                                    <i class="bx bx-globe"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden ms-3">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-3">Активных доменов</p>
                                <div class="d-flex align-items-center mb-3">
                                    <h4 class="fs-4 flex-grow-1 mb-0">
                                        <span id="iactivedomains" class="counter-value"><?= $active_domain ?></span>
                                    </h4>
                                </div>
                                <p class="text-muted text-truncate mb-0">
                                    <span id="ibandomains2" class="badge bg-warning-subtle text-warning fs-12"><?= $farm_domain_count ?> в ферме</span>
                                </p>
                            </div>
                        </div>
                    </div><!-- end card body -->
                </div>
            </div><!-- end col Активных доменов -->

            <div class="col-xl-4">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle text-info rounded-2 fs-2">
                                    <i class="bx bx-user"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden ms-3">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-3">Трафик за сегодня</p>
                                <div class="d-flex align-items-center mb-3">
                                    <h4 id="todaystat_hit" class="fs-4 flex-grow-1 mb-0"><?= $active_today_hits ?></h4>
                                    <span id="prirosthit_badge" class="badge fs-12">
                                        <?php if ($active_hits_diff >= 0): ?>
                                            <i class="ri-arrow-up-s-line fs-13 align-middle me-1"></i>
                                            +<?= $active_hits_diff ?>
                                        <?php else: ?>
                                            <i class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>
                                            <?= $active_hits_diff ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <p class="text-muted mb-0" style="display: flex; justify-content: space-between">
                                    <span id="todaystatban_hit_badge" class="badge bg-warning-subtle text-warning fs-12">
                                        <?= $farm_today_hits ?> в ферме
                                    </span>
                                    <span id="prirosthit_badge_farm" class="badge fs-12">
                                        <?php if ($farm_hits_diff >= 0): ?>
                                            <i class="ri-arrow-up-s-line fs-13 align-middle me-1"></i>
                                            +<?= $farm_hits_diff ?>
										<?php else: ?>
                                            <i class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>
											<?= $farm_hits_diff ?>
										<?php endif; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div><!-- end card body -->
                </div>
            </div><!-- end col Трафик за сегодня -->

            <div class="col-xl-4">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-2 fs-2">
                                    <i class="bx bx-bot"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-uppercase fw-medium text-muted mb-3">Ботов за сегодня</p>
                                <div class="d-flex align-items-center mb-3">
                                    <h4 id="todaystat_bot" class="fs-4 flex-grow-1 mb-0"><?= $active_today_bots ?></h4>
                                    <span id="prirostbot_badge" class="badge fs-12">
                                        <?php if ($active_bots_diff >= 0): ?>
                                            <i class="ri-arrow-up-s-line fs-13 align-middle me-1"></i>
                                            +<?= $active_bots_diff ?>
                                        <?php else: ?>
                                            <i class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>
                                            <?= $active_bots_diff ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <p class="text-muted mb-0" style="display: flex; justify-content: space-between">
                                    <span id="todaystatban_bot_badge" class="badge bg-warning-subtle text-warning fs-12">
                                        <?= $farm_today_bots ?> в ферме
                                    </span>
                                    <span id="prirostbot_badge_farm" class="badge fs-12">
                                        <?php if ($farm_bots_diff >= 0): ?>
                                            <i class="ri-arrow-up-s-line fs-13 align-middle me-1"></i>
                                            +<?= $farm_bots_diff ?>
										<?php else: ?>
                                            <i class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>
											<?= $farm_bots_diff ?>
										<?php endif; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div><!-- end card body -->
                </div>
            </div><!-- end col Ботов за сегодня -->
        </div><!-- end row -->

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header border-0 align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1" id="userchart_head">Посетители по дням</h4>
                        <div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" onclick="statindex('hit', 'month', 'userchart')">по месяцам</button>
                            <button type="button" class="btn btn-soft-secondary btn-sm" onclick="statindex('hit', 'days', 'userchart')">по дням</button>
                            <button type="button" class="btn btn-soft-primary btn-sm" onclick="statindex('hit', 'hour', 'userchart')">по часам</button>
                        </div>
                    </div><!-- end card header -->
                    <div class="card-body p-0 pb-2">
                        <script>
                          $(document).ready(function() {
                            statindex('hit', 'days', 'userchart');
                          });
                        </script>
                        <canvas id="userchart" height="100" class="card-header"></canvas>
                    </div><!-- end card body -->
                </div>

                <div class="card">
                    <div class="card-header border-0 align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1" id="botchart_head">Ботов по дням</h4>
                        <div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" onclick="statindex('bot', 'month', 'botchart')">по месяцам</button>
                            <button type="button" class="btn btn-soft-secondary btn-sm" onclick="statindex('bot', 'days', 'botchart')">по дням</button>
                            <button type="button" class="btn btn-soft-primary btn-sm" onclick="statindex('bot', 'hour', 'botchart')">по часам</button>
                        </div>
                    </div><!-- end card header -->
                    <div class="card-body p-0 pb-2">
                        <script>
                          $(document).ready(function() {
                            statindex('bot', 'days', 'botchart');
                          });
                        </script>
                        <canvas id="botchart" height="100" class="card-header"></canvas>
                    </div><!-- end card body -->
                </div>

                <div class="card">
                    <div class="card-header border-0 align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1" id="farmchart_head">Боты в ферме по дням</h4>
                        <div>
                            <button type="button" class="btn btn-soft-secondary btn-sm" onclick="statindex('bot', 'month', 'farmchart')">по месяцам</button>
                            <button type="button" class="btn btn-soft-secondary btn-sm" onclick="statindex('bot', 'days', 'farmchart')">по дням</button>
                            <button type="button" class="btn btn-soft-primary btn-sm" onclick="statindex('bot', 'hour', 'farmchart')">по часам</button>
                        </div>
                    </div><!-- end card header -->
                    <div class="card-body p-0 pb-2">
                        <script>
                          $(document).ready(function() {
                            statindex('bot', 'days', 'farmchart');
                          });
                        </script>
                        <canvas id="farmchart" height="100" class="card-header"></canvas>
                    </div><!-- end card body -->
                </div>
            </div><!-- end col -->
        </div><!-- end row -->

    </div><!-- end col -->

    <div class="col-xxl-4">
        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1" id="tophit_head">Топ по трафику</h4>
                <div>
                    <button type="button" class="btn btn-soft-secondary btn-sm" onclick="topindex('hit','30','tophit')">за 30 дней</button>
                    <button type="button" class="btn btn-soft-secondary btn-sm" onclick="topindex('hit','7','tophit')">за неделю</button>
                    <button type="button" class="btn btn-soft-primary btn-sm" onclick="topindex('hit','1','tophit')">за сегодня</button>
                </div>
            </div><!-- end card header -->
            <script>topindex('hit', '1', 'tophit');</script>
            <div class="card-body p-3 pb-2" id="tophit"></div>
        </div><!-- end card body -->

        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1" id="topbot_head">Топ по ботам</h4>
                <div>
                    <button type="button" class="btn btn-soft-secondary btn-sm" onclick="topindex('bot','30','topbot')">за 30 дней</button>
                    <button type="button" class="btn btn-soft-secondary btn-sm" onclick="topindex('bot','7','topbot')">за неделю</button>
                    <button type="button" class="btn btn-soft-primary btn-sm" onclick="topindex('bot','1','topbot')">за сегодня</button>
                </div>
            </div><!-- end card header -->
            <script>topindex('bot', '1', 'topbot');</script>
            <div class="card-body p-3 pb-2" id="topbot">
            </div><!-- end card body -->
        </div>

        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1" id="topproject_head">Лучшие проекты</h4>
                <div>
                    <button type="button" class="btn btn-soft-secondary btn-sm" onclick="topindex('hit','30','topproject')">за 30 дней</button>
                    <button type="button" class="btn btn-soft-secondary btn-sm" onclick="topindex('hit','7','topproject')">за неделю</button>
                    <button type="button" class="btn btn-soft-primary btn-sm" onclick="topindex('hit','1','topproject')">за сегодня</button>
                </div>
            </div><!-- end card header -->
            <script>topindex('hit', '1', 'topproject');</script>
            <div class="card-body p-3 pb-2" id="topproject">
            </div><!-- end card body -->
        </div>
        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1" id="topfarm_head">Топ по ферме</h4>
                <div>
                    <button type="button" class="btn btn-soft-secondary btn-sm" onclick="topindex('bot','30','topfarm')">за 30 дней</button>
                    <button type="button" class="btn btn-soft-secondary btn-sm" onclick="topindex('bot','7','topfarm')">за неделю</button>
                    <button type="button" class="btn btn-soft-primary btn-sm" onclick="topindex('bot','1','topfarm')">за сегодня</button>
                </div>
            </div><!-- end card header -->
            <script>topindex('bot', '1', 'topfarm');</script>
            <div class="card-body p-3 pb-2" id="topfarm">
            </div><!-- end card body -->
        </div>
    </div><!-- end col -->
</div><!-- end row -->

<!--<button type="button" class="btn btn-primary" onclick="showModal()">Show Modal</button>-->

<script>
  function showModal() {
    document.getElementById('statdomainname').textContent = 'example.com';
    document.getElementById('statdomaincontent').innerHTML = '<p>Here is some content for the modal.</p>';
    const statModal = new bootstrap.Modal(document.getElementById('statdomain'));
    statModal.show();
  }
</script>


<div id="statdomain" class="modal fade" tabindex="-1" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-dialog-centered modal-dialog modal-xl">
		<div class="modal-content border-0 overflow-hidden">
			<div class="modal-header p-3">
				<h4 class="card-title mb-0">Статистика <span id="statdomainname"></span></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body text-center" id="statdomaincontent">
				<div id="pre_statdomain" class="spinner-border text-primary fs-14 mt-3 mb-5" role="status"
					 style="width:34px;height:34px;"><span class="sr-only">Loading...</span></div>
			</div>
		</div>
	</div>
</div>
