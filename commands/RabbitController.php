<?php

namespace app\commands;

use app\components\RabbitMQ;
use app\models\Statistics;
use yii\console\Controller;

/**
 * Контроллер для работы с RabbitMQ.
 */
class RabbitController extends Controller
{

    /**
     * Запускает прослушивание очереди RabbitMQ для dorgen очереди dorgen.statistics
     *
     * php yii rabbit/dorgen-statistics-listen
     */
    public function actionDorgenStatisticsListen()
    {
        $rab = new RabbitMQ(queueName: 'dorgen.statistics', routingKey: 'dorgen_statistics');
        $messages = [];

        $callback = function ($msg) use (&$messages, &$rab) {
            $messages[] = $msg->getBody();
            // Подтверждаем сообщение после успешной обработки
            $rab->channel->basic_ack($msg->getDeliveryTag());

            // Если собрали 1000 сообщений, выполняем batchUpdate
            if (count($messages) >= 1000) {
                Statistics::batchUpdate($messages);
                $messages = []; // Очищаем массив для следующей партии
            }
        };

        $rab->channel->basic_qos(0, 10, false);
        $rab->channel->basic_consume($rab->queueName, '', false, false, false, false, $callback);

        while ($rab->channel->is_consuming()) {
            $rab->channel->wait();
        }
    }


    /**
     * Запускает прослушивание очереди RabbitMQ для dorgen очереди dorgen.redirects.stat
     *
     * php yii rabbit/dorgen-redirects-stat-listen
     */
    public function actionDorgenRedirectsStatListen()
    {
        $rab = new RabbitMQ(queueName: 'dorgen.redirects.stat', routingKey: 'dorgen_redirects_stat');

        $messages = [];

        $callback = function ($msg) use (&$messages, &$rab) {
            $messages[] = $msg->getBody();
            // Подтверждаем сообщение после успешной обработки
            $rab->channel->basic_ack($msg->getDeliveryTag());

            // Если собрали 1000 сообщений, выполняем batchUpdate
            if (count($messages) >= 1000) {
                Statistics::batchUpdateRedirects($messages);
                $messages = []; // Очищаем массив для следующей партии
            }
        };
        $rab->channel->basic_qos(0, 100, false);
        $rab->channel->basic_consume($rab->queueName, '', false, false, false, false, $callback);

        while ($rab->channel->is_consuming()) {
            $rab->channel->wait();
        }
    }

    /**
     * Запускает прослушивание очереди RabbitMQ для dorgen очереди dorgen.hit.stat
     *
     * php yii rabbit/dorgen-hit-stat-listen
     */
    public function actionDorgenHitStatListen()
    {
        $rab = new RabbitMQ(queueName: 'dorgen.hit.stat', routingKey: 'dorgen_hit_stat');

        $messages = [];

        $callback = function ($msg) use (&$messages, &$rab) {
            $messages[] = $msg->getBody();
            Statistics::batchUpdateHits($messages);
            $rab->channel->basic_ack($msg->getDeliveryTag());
            $messages = [];
        };

        $rab->channel->basic_qos(0, 100, false);
        $rab->channel->basic_consume($rab->queueName, '', false, false, false, false, $callback);

        while ($rab->channel->is_consuming()) {
            $rab->channel->wait();
        }
    }
}