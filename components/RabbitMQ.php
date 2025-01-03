<?php

namespace app\components;

use app\modules\combine\domain\models\Profiles;
use app\modules\combine\panel\models\AnalyticsData;
use app\modules\combine\panel\models\TasksCombine;
use app\modules\combine\domain\models\TaskLogs;
use app\modules\combine\domain\models\Tasks;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;

class RabbitMQ {
	private AMQPStreamConnection $connection;
	public $channel;
    public string $exchange;
    public string $exchangeType; // Можно выбрать 'direct', 'fanout', 'topic', 'headers'
	public string $queueName;
	private string $routingKey;
	private string $host = 'rabbitmq';
	private int $port = 5672;
	private string $user = 'guest';
	private string $password = 'guest';
	private mixed $data;

	/**
	 * @throws Exception
	 */
	public function __construct (string $exchange = 'dorgen.service', string $queueName = 'dorgen', string $routingKey = 'dorgen-test', string $exchangeType = 'direct', $connection_timeout = 5) {
		try {
			$this->connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password, connection_timeout: $connection_timeout);
			$this->channel = $this->connection->channel();
			$this->exchange = $exchange;
			$this->exchangeType = $exchangeType;
			$this->queueName = $queueName;
			$this->routingKey = $routingKey;

			// Декларируем exchange
			$this->channel->exchange_declare($this->exchange, $this->exchangeType, false, true, false);
			// Декларируем очередь
			$this->channel->queue_declare($this->queueName, false, true, false, false);
			// Привязываем очередь к exchange
			$this->channel->queue_bind($this->queueName, $this->exchange, $this->routingKey);
		} catch (Exception $e) {
			Yii::error("Ошибка инициализации RabbitMQ: " .$e->getMessage(), 'rabbit');
			throw new Exception("Failed to initialize RabbitMQ: " . $e->getMessage());
		}
	}

	public function publish ($data): void {
		try {
			$msg = new AMQPMessage($data);
			$this->channel->basic_publish($msg, $this->exchange, $this->routingKey);
			Yii::info("Публикация сообщения в RabbitMQ: $data", 'rabbit');
		} catch (Exception $e) {
			Yii::error("Ошибка публикации в RabbitMQ: " .$e->getMessage(), 'rabbit');
		}
	}

	public function __destruct () {
		try {
			$this->channel->close();
			$this->connection->close();
		} catch (Exception $e) {
			Yii::error("Ошибка закрытия коннекта RabbitMQ: " . $e->getMessage(), 'rabbit');
		}
	}

	public function getMessage(): ?string {
		$message = $this->channel->basic_get($this->queueName);

		if ($message) {
			$this->channel->basic_ack($message->getDeliveryTag());
			return $message->getBody();
		}

		return null;
	}

}