<?php

namespace app\components;

use Yii;
use yii\helpers\Json;

class Redis {

	/**
	 * Сохраняет данные в Redis.
	 *
	 * @param string $key Ключ для сохранения.
	 * @param mixed $result Данные для сохранения.
	 * @param bool $upsert Указывает, нужно ли перезаписывать существующее значение (по умолчанию true).
	 * @param int|null $ttl Время жизни ключа в секундах (опционально).
	 */
	public static function save(string $key, mixed $result, bool $upsert = false, int $ttl = null): void
	{
		$redis = Yii::$app->redis;
		$serializedModel = serialize($result);

		if (!$upsert && $redis->exists($key)) {
			return;
		}

		$redis->set($key, $serializedModel);

		if ($ttl !== null) {
			$redis->expire($key, $ttl);
		}
	}

	/**
	 * Находит данные в Redis по ключу.
	 *
	 * @param string $key Ключ для поиска.
	 * @return mixed|null Декодированные данные или null, если ключ не найден.
	 */
	public static function find(string $key): mixed
	{
		$redis = Yii::$app->redis;
		$serializedModel = $redis->get($key);
		if ($serializedModel) {
			return unserialize($serializedModel);
		}
		return false;
	}

	/**
	 * Удаляет ключ из Redis.
	 *
	 * @param string $key Ключ для удаления.
	 * @return bool True, если ключ был удален, иначе false.
	 */
	public static function delete(string $key): bool
	{
		$redis = Yii::$app->redis;
		return (bool) $redis->del($key);
	}

	/**
	 * Получает оставшееся время жизни ключа.
	 *
	 * @param string $key Ключ для проверки.
	 * @return int|null Оставшееся время жизни ключа в секундах или null, если ключ не существует или не имеет установленного TTL.
	 */
	public static function getTtl(string $key): ?int
	{
		$redis = Yii::$app->redis;
		$ttl = $redis->ttl($key);

		if ($ttl >= 0) {
			return $ttl;
		}

		return null;
	}

	//TODO В случае значительных задержек переписать keys на scan
	/**
	 * Удаляет все слаги для заданного домена из Redis, используя его идентификатор.
	 *
	 * @param int $domain_id Идентификатор домена.
	 */
	public static function deleteDomainSlugs(int $domain_id): void {
		$redis = Yii::$app->redis;
		$pattern = $domain_id . '_*';
		$keys = $redis->keys($pattern);

		foreach ($keys as $key) {
			$redis->del($key);
		}
	}
}