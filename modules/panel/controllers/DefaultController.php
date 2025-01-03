<?php

namespace app\modules\panel\controllers;

use app\components\Helpers;
use app\models\LoginForm;
use DateTime;
use Yii;
use yii\base\DynamicModel;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\Response;

/**
 * Default controller for the `panel` module
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'log'],
                'rules' => [
                    [
                        'actions' => ['logout', 'log'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionBots()
    {
        $logLines = shell_exec('tail -n 5000 /var/log/nginx/dor.access.log | grep -v "/panel"');

// Ищем строки, содержащие "ooglebot"
        $lines = explode(PHP_EOL, $logLines);
        $filteredLines = array_filter($lines, function ($line) {
            return strpos($line, 'ooglebot') !== false;
        });

// Подсчитываем количество уникальных IP-адресов
        $ipCounts = [];
        foreach ($filteredLines as $line) {
            $parts = preg_split('/\s+/', $line);
            if (isset($parts[0])) {
                $ip = $parts[0];
                if (isset($ipCounts[$ip])) {
                    $ipCounts[$ip]++;
                } else {
                    $ipCounts[$ip] = 1;
                }
            }
        }

// Выводим результат
        foreach ($ipCounts as $ip => $count) {
            echo "$count $ip\n";
        }

// Получаем текущее время
        $now = new DateTime();
        $now->modify('-1 second');

// Создаем шаблон для поиска в логах
        $pattern = sprintf(
            '/(\d+)\t(\d+)\/(\w+)\/%d:%02d:%02d:%02d/i',
            $now->format('Y'),
            $now->format('H'),
            $now->format('i'),
            $now->format('s')
        );

// Подсчитываем совпадения
        $matches = [];
        foreach ($filteredLines as $line) {
            if (preg_match($pattern, $line, $match)) {
                $matches[] = $match;
            }
        }

// Выводим количество совпадений
        echo count($matches) . "\n";

    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['dashboard/index']);
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['dashboard/index']);
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionLog($domain)
    {
        return $this->renderPartial('log', ['domain' => $domain]);
    }

    /**
     * @throws Exception
     */
    public function actionLinkGenerator()
    {
        $model = new DynamicModel(['template', 'count']);
        $model->addRule(['template', 'count'], 'required')
            ->addRule(['count'], 'integer');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $links = $this->generateLinks($model->template, $model->count);
            $filePath = $this->saveLinksToFile($links);

            Yii::$app->response->sendFile($filePath)->on(\yii\web\Response::EVENT_AFTER_SEND, function ($event) use ($filePath) {
                @unlink($filePath);
            });
        }

        return $this->render('link-generator', ['model' => $model]);
    }

    private function generateLinks($template, $count): array
    {
        $links = [];
        for ($i = 0; $i < $count; $i++) {
            $link = Helpers::formatRegexUrl($template);
            $links[] = $link;
        }
        return $links;
    }

    /**
     * @throws Exception
     */
    private function saveLinksToFile($links): string
    {
        $filePath = Yii::getAlias('@webroot') . '/links.txt';
        FileHelper::createDirectory(dirname($filePath));
        file_put_contents($filePath, implode("\n", $links));
        return $filePath;
    }
}

