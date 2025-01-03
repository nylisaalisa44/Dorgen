<?php

$isUseFileTransport = filter_var($_SERVER['MAIL_FILE_TRANS'], FILTER_VALIDATE_BOOLEAN);
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue',],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'panel' => [
            'class' => 'app\modules\panel\Module',
        ],
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'CTNrckVh0Bwz2SVQUXEYkhbt0bxm1MoZ',
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => $_SERVER['REDIS_HOST'],
                'port' => $_SERVER['REDIS_PORT'],
                'database' => 0,
            ],
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $_SERVER['REDIS_HOST'],
            'port' => $_SERVER['REDIS_PORT'],
            'database' => 0,
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['panel/default/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'queue' => [
            'class' => \yii\queue\amqp_interop\Queue::class,
            'host' => 'rabbitmq',
            'port' => 5672,
            'user' => 'guest',
            'password' => 'guest',
            'exchangeName' => 'dorgen.service',
            'queueName' => 'dorgen',
            'routingKey' => 'dorgen-test',
        ],
        'urlManager' => [
            'class' => 'app\components\CustomUrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'panel/<action>' => 'panel/<action>',
                'panel/<controller>/<action>' => 'panel/<controller>/<action>',
                'robots.txt' => 'site/robots',
                '<action:\w+>.xml' => 'site/sitemap',

            ],
        ],
    ],
    'params' => $params,
];

return $config;
