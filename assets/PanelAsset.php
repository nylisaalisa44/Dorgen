<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PanelAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'panel/assets/css/varib.css',
        'panel/assets/css/icons.min.css',
        'panel/assets/css/app.min.css',
        'panel/assets/css/custom.min.css',
        'panel/assets/libs/nouislider/nouislider.min.css'
    ];
    public $js = [
        'panel/assets/js/layout.js',
        'panel/assets/libs/nouislider/nouislider.min.js',
/*        'panel/assets/libs/chart.js/chart.umd.js',
        'panel/assets/libs/simplebar/simplebar.min.js',
        'panel/assets/libs/node-waves/waves.min.js',
        'panel/assets/libs/feather-icons/feather.min.js',
        'panel/assets/js/pages/plugins/lord-icon-2.1.0.js',
        'panel/assets/js/plugins.js',
        'panel/assets/js/app.js'*/
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset'
    ];
}
