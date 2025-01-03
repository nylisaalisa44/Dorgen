<?php

namespace app\components;

use app\models\Domain;
use app\models\Redirects;
use app\models\Slug;
use Yii;
use yii\base\ExitException;
use yii\db\Exception;
use yii\db\Expression;
use yii\web\BadRequestHttpException;

class Middleware
{
    public Domain $domain;
    public string $db = 'db_2023_10_11_en_sweep';
    public bool $redirect = false;
    public array $redirectData = [];

    /**
     * @throws \Exception
     */
    public function __construct($domain)
    {
        if ($domain) {
            $this->domain = $domain;
            $this->handleProject();
        } else {
            Yii::$app->response->statusCode = 403;
            Yii::$app->end();
        }
    }

    /**
     * @throws ExitException
     * @throws Exception
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    private function handleProject(): void
    {
        $this->db = $this->domain->project->getTable();

        if (!Helpers::isBot()) {
            $is_redirect = false;
        } else {
            list($is_redirect, $redirect_id) = Redirects::checkRedirect($this);
            if ($redirect_id != 0) {
                Redirects::updateRedirectStats($redirect_id);
            }
        }

        $this->saveStatistics();
        #region Если не редирект
        if (!$is_redirect) {
            $this->getRandomRedirect();
        }
        #endregion
    }

    /**
     * @throws \Exception
     */
    protected function getRandomRedirect(): void
    {
        $this->redirectData = $this->getRandomRedirectData();

        $redirectUrl = $this->redirectData['url'] ?? null;
        if ($this->redirectData['type'] && $redirectUrl !== null) {
            $this->redirect = true;
        }
    }

    /**
     * @throws \Exception
     */
    protected function getRandomRedirectData(): array
    {
        return $this->getRedirectDataForProject();
    }

    /**
     * @throws \Exception
     */
    public function getRedirectDataForRedirect(Redirects $redirect): array
    {
        $type = Helpers::getRedirectTypeRand($redirect->redirect_type);
        $url = Helpers::formatRegexUrl($redirect->redirect_url);

        return compact('type', 'url');
    }

    /**
     * @throws \Exception
     */
    protected function getRedirectDataForProject(): array
    {
        $type = $this->domain->project->getRedirectTypeRand();
        $url = $this->domain->project->getRedirectUrlRand();

        return compact('type', 'url');
    }

    /**
     * @throws Exception
     * @throws BadRequestHttpException|ExitException
     */
    protected function saveStatistics(): void
    {
        $date = date('Y-m-d');
        $hour = date("H");

        if (Helpers::isBot()) {
            $this->blockNotAllowedBots();
            $this->saveBotStatistics($date, $hour);
        } else {
            //if (BotDetector::isAllowedReferer($this->domain->project->allowed_bots))
                $this->saveHitStatistics($date, $hour);
        }
    }

    /**
     * Блокирует неразрешенных ботов.
     *
     * @return void
     * @throws ExitException
     */
    protected function blockNotAllowedBots(): void
    {
        if (!BotDetector::isAllowedBot($this->domain->project->allowed_bots)) {
            Yii::$app->response->statusCode = 403;
            Yii::$app->end();
        }
    }

    protected function saveBotStatistics($date, $hour): void
    {
        $botType = BotDetector::detectBotType($_SERVER['HTTP_USER_AGENT']);

        $table = 'statistics';
        $data = [
            'domain_id' => $this->domain->id,
            'date' => $date,
            'hour' => $hour,
            'bot' => 1,
            'bots_stat' => [$botType => 1]
        ];

        $rabbitMQ = new RabbitMQ(queueName: 'dorgen.statistics', routingKey: 'dorgen_statistics');
        $data_array = json_encode([
            'data' => $data,
            'table' => $table
        ]);
        $rabbitMQ->publish($data_array);
    }

    /**
     * @throws Exception
     */
    protected function saveHitStatistics($date, $hour): void
    {
        $table = 'statistics';

        $data = [
            'domain_id' => $this->domain->id,
            'date' => $date,
            'hour' => $hour,
            'hit' => 1,
        ];

        $rabbitMQ = new RabbitMQ(queueName: 'dorgen.hit.stat', routingKey: 'dorgen_hit_stat');
        $data_array = json_encode([
            'data' => $data,
            'table' => $table
        ]);
        $rabbitMQ->publish($data_array);
    }

    /**
     * Выполняет запрос к базе данных и возвращает результат
     * @return array Результат запроса к базе данных
     * @throws Exception
     */
    public function getQueryResult(): array
    {
        return Yii::$app->db->createCommand("SELECT * FROM $this->db JOIN ( SELECT rand() * (SELECT max(id) from $this->db) AS max_id ) AS m WHERE id >= m.max_id ORDER BY id ASC LIMIT 1;")
            ->queryOne();
    }

    /**
     * Проверяет, требуется ли редирект, и возвращает объект RedirectHandler
     * @return RedirectHandler|null
     */
    public function getRedirect(): ?RedirectHandler
    {
        if ($this->redirect) {
            return new RedirectHandler($this->redirectData);
        }

        return null;
    }

}