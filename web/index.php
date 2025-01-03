<?php

require __DIR__ . '/../vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// comment out the following two lines when deployed to production
$isDebug = filter_var($_SERVER['DEBUG'], FILTER_VALIDATE_BOOLEAN);
defined('YII_DEBUG') or define('YII_DEBUG', $isDebug);
defined('YII_ENV') or define('YII_ENV', $_SERVER['ENV']);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
