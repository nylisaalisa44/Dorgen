<?php

/** @var yii\web\View $this */

/** @var string $content */

use app\assets\PanelAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use app\models\Project;

PanelAsset::register($this);

$projects = Project::find()->where(['status' => Project::STATUS_ACTIVE])->all();

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html data-layout="horizontal" data-topbar="light" data-sidebar-size="lg" data-sidebar="light" data-sidebar-image="none"
      data-preloader="disable" data-bs-theme="dark" data-layout-width="fluid" data-layout-position="fixed"
      data-layout-style="default" data-sidebar-visibility="show" lang="ru">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div id="layout-wrapper">
    <?php if (!Yii::$app->user->isGuest) { ?>
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                        <div class="navbar-brand-box horizontal-logo">
                        </div>
                        <button type="button"
                                class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
                                id="topnav-hamburger-icon">
                            <span class="hamburger-icon">
                            <span></span>
                            <span></span>
                            <span></span>
                            </span>
                        </button>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="dropdown topbar-head-dropdown ms-1 header-item">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-category-alt fs-22"></i>
                            </button>
                        </div>
                        <?= \app\widgets\StatsWidget::widget() ?>
                    </div>
                </div>
            </div>
        </header>
        <div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                id="NotificationModalbtn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mt-2 text-center">
                            <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                                       colors="primary:#f7b84b,secondary:#f06548"
                                       style="width:100px;height:100px"></lord-icon>
                            <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                                <h4>Are you sure ?</h4>
                                <p class="text-muted mx-4 mb-0">Are you sure you want to remove this Notification ?</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                            <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete It!
                            </button>
                        </div>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>

        <!-- ========== App Menu ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <!-- Dark Logo-->
                <a href="index.html" class="logo logo-dark">
                <span class="logo-sm">
                <img src="/panel/assets/images/logo-sm.png" alt="" height="22">
                </span>
                    <span class="logo-lg">
                <img src="/panel/assets/images/logo-dark.png" alt="" height="17">
                </span>
                </a>
                <!-- Light Logo-->
                <a href="index.html" class="logo logo-light">
                <span class="logo-sm">
                <img src="/panel/assets/images/logo-sm.png" alt="" height="22">
                </span>
                    <span class="logo-lg">
                <img src="/panel/assets/images/logo-light.png" alt="" height="17">
                </span>
                </a>
                <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
                        id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>
            <div id="scrollbar">
                <div class="container-fluid">
                    <div id="two-column-menu">
                    </div>
                    <ul class="navbar-nav" id="navbar-nav">
                        <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="/panel/dashboard" role="button">
                                <i class="bx bxs-dashboard"></i> <span data-key="t-apps">Дашбоард</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link collapsed" href="#sidebarProjects" data-bs-toggle="collapse"
                               role="button" aria-expanded="false" aria-controls="sidebarProjects">
                                <i class="bx bxs-dashboard"></i> <span data-key="t-dashboards">Проекты</span>
                            </a>
                            <div class="collapse menu-dropdown mega-dropdown-menu" id="sidebarProjects">
                                <div class="row ms-2 me-2 mt-2">
                                    <div class="col-lg-6">
                                        <a href="/panel/project"
                                           class="badge bg-primary-subtle text-primary nav-link me-1 ms-1"><i
                                                    class="bx bx-list-ul"></i> Все проекты </a>
                                    </div>
                                    <div class="col-lg-6">
                                        <a href="/panel/project/create"
                                           class="badge bg-primary-subtle text-primary nav-link me-1 ms-1"><i
                                                    class="bx bx-folder-plus"></i> Новый проект </a>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <ul class="nav nav-sm flex-column">
                                            <?php foreach ($projects as $project) { ?>
                                                <li class="nav-item">
                                                    <a href="/panel/project/view?id=<?= $project->id ?>"
                                                       class="nav-link"> <?= $project->name ?><sup
                                                                class="rounded-pill text-primary ps-1"><?= $project->getCountPartDomains() ?></sup></a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="/panel/redirects" role="button">
                                <i class="bx bx-layer"></i> <span data-key="t-apps">Ферма</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link collapsed" href="#sidebarApps" data-bs-toggle="collapse"
                               role="button" aria-expanded="false" aria-controls="sidebarApps">
                                <i class="bx bx-layer"></i> <span data-key="t-apps">Утилиты</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarApps">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="/panel/domain/checker" class="nav-link" data-key="t-checker">Чекер
                                            дропов</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#sidebarUtilities" class="nav-link collapsed" data-bs-toggle="collapse"
                                           role="button" aria-expanded="false" aria-controls="sidebarUtilities"
                                           data-key="t-utilities">
                                            Cloudflare
                                        </a>
                                        <div class="collapse menu-dropdown" id="sidebarUtilities">
                                            <ul class="nav nav-sm flex-column">
                                                <li class="nav-item">
                                                    <a href="/panel/cf-accounts/" class="nav-link">Аккаунты</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="/panel/cf-accounts/add-domains" class="nav-link">Добавить
                                                        домены</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
                <!-- Sidebar -->
            </div>
            <div class="sidebar-background"></div>
        </div>

    <?php } ?>

    <!-- Left Sidebar End -->
    <!-- Vertical Overlay-->
    <div class="vertical-overlay"></div>
    <!-- Start right Content here -->
    <div class="main-content">
        <div class="page-content" style="max-width:1900px">
            <div class="container-fluid">
                <?php if (!empty($this->params['breadcrumbs'])): ?>
                    <?= Breadcrumbs::widget([
                        'homeLink' => [
                            'label' => 'Главная',
                            'url' => ['/panel/dashboard'],
                        ],
                        'links' => $this->params['breadcrumbs']
                    ]) ?>
                <?php endif ?>
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </div>
    </div>

</div>
<?php $this->endBody() ?>
<script>
    $('#topnav-hamburger-icon').click(function () {
        $('body').toggleClass('menu')
    })
</script>
</body>
</html>
<?php $this->endPage() ?>
