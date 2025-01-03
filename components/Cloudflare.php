<?php

namespace app\components;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use Yii;

class Cloudflare {

	private array $cf_accounts;
	private $res;
	private $auth_code;
	private string $url = 'https://api.cloudflare.com/client/v4/user';
	private int $timeout_curl = 10;
	private array $headers;
	private $ip;
	private $replaced_ip;
	private $old_ip;
	private $new_ip;
	private int $del;
	private int $pause;
	private int $auh;
	private int $ahr;
	private string $ssl;
	private int $wildcard;
	private $rabbit;
	private string $error = '';


	/**
	 * Конструктор класса.
	 *
	 * @param int $del Искать и удалять старые DNS записи (0/1).
	 * @param int $pause Задержка перед поиском старых DNS записей (по умолчанию 10 секунд).
	 * @param int $auh Всегда использовать HTTPS (0/1).
	 * @param int $ahr Автоматические HTTPS перенаправления (0/1).
	 * @param string $ssl Тип SSL: off, flexible, full, strict (оставьте пустым, чтобы было "по умолчанию").
	 * @param int $wildcard Использовать wildcard (0/1).
	 * @throws Exception
	 */
	public function __construct (int $del = 1, int $pause = 10, int $auh = 1, int $ahr = 1, string $ssl = 'flexible', int $wildcard = 1) {
		$this->del = $del;
		$this->pause = $pause;
		$this->auh = $auh;
		$this->ahr = $ahr;
		$this->ssl = $ssl;
		$this->wildcard = $wildcard;

		//Настройка заголовков и flush для динамического вывода
/*		ob_implicit_flush(true);
		if (ob_get_level() > 0) {
			ob_end_flush();
		}

		header('HTTP/1.1 ' . 200);
//			header('Content-type: text/event-stream');
		header('Content-Type: text/html; charset=UTF-8');
		header('Cache-Control: no-cache');
		header('Connection: keep-alive');*/
	}

	public function setHeaders ($email, $api_key): void {
		$this->headers = [
			"X-Auth-Email: $email",
			"X-Auth-Key: $api_key",
			"Content-Type: application/json"
		];
	}

	public function setCfAccounts ($accounts): void {
		$this->cf_accounts = $accounts;
	}

	public function setIpForAddDomainsFunction ($ip): void {
		$this->ip = $ip;
	}

	public function setIps ($replaced_ip, $old_ip, $new_ip): void {
		$this->replaced_ip = $replaced_ip;
		$this->old_ip = $old_ip;
		$this->new_ip = $new_ip;
	}

	public function authorize ($stop = true): void {
		$this->rabbit = new RabbitMQ('cloudflare.service','cloudflare.queue','cloudflare', connection_timeout: 120);
		$this->curl($this->url);
		if ($this->auth_code == 200 && $this->res->success) {
			$this->output("authorization: <span style=\"color:green\">success</span><br><br>");
		} else {
			$this->output("authorization: <span style=\"color:red\">error</span><br>");
			if ($stop) {
				$this->stop();
			}
		}
	}

	private function curl ($url, $type = 'get', $data = null): void {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout_curl);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

		switch ($type) {
			case 'post':
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;
			case 'put':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;
			case 'delete':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
			case 'patch':
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
				break;
			default:
				break;
		}

		$res = curl_exec($ch);
		$this->res = json_decode($res);
		$this->auth_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if (isset($this->res->errors) && is_array($this->res->errors) && count($this->res->errors) > 0) {
			$error = $this->res->errors[0]->message ?? "Сообщение об ошибке отсутствует.";
		} else {
			$error = "Произошла ошибка";
		}
		$this->error = $error;

		Yii::error(
			"Лог ответа. url:$url \r\n Заголовки: ". implode(';',$this->headers) . " \r\n Ответ ". $res,
			'rabbit'
		);
	}

	public function addDomains ($domains): void {
		foreach ($domains as $d) {
			$d = trim($d);
			$this->createZone($d);
		}
	}

	private function createZone ($domain): void {
		$url = 'https://api.cloudflare.com/client/v4/zones';
		$data = json_encode(["name" => $domain, "jump_start" => true]);
		$this->curl($url, 'post', $data);
		if ($this->res->success) {
			$this->output("create zone \"$domain\" => <span style=\"color:green\">success</span><br>");
			$id = $this->res->result->id;
			$ns1 = $this->res->result->name_servers[0];
			$ns2 = $this->res->result->name_servers[1];
			//поиск и удаление старых DNS записей
			if ($this->del == 1) {
				$this->deleteOldDnsRecords($id);
			}
			//добавление DNS записей
			$this->addDnsRecords($domain, $id);
			//always use https
			if ($this->auh == 1) {
				$this->setAlwaysUseHttps($id);
			}
			//automatic https rewrites
			if ($this->ahr == 1) {
				$this->setAutomaticHttpsRewrites($id);
			}
			//ssl
			if (!empty($this->ssl)) {
				$this->setSsl($id);
			}
			$this->output('NS: ' . $ns1 . ', ' . $ns2 . '<br><br>');
		} else {
			$this->output("create zone \"$domain\" => <span style=\"color:red\">$this->error</span><br><br>");
		}
	}

	private function addDnsRecords ($domain, $zone_id): void {
		$url = "https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records";
		$data = json_encode(["type" => "A", "name" => $domain, "content" => $this->ip, "proxied" => true]);
		$this->curl($url, 'post', $data);
		if ($this->res->success) {
			$this->output("create DNS record \"$domain\" => <span style=\"color:green\">success</span><br>");
		} else {
			$this->output("create DNS record \"$domain\" => <span style=\"color:red\">error</span><br>");
		}
		$n = $this->wildcard == 1 ? '*' : 'www';
		$data = json_encode(["type" => "A", "name" => $n, "content" => $this->ip, "proxied" => true]);
		$this->curl($url, 'post', $data);
		if ($this->res->success) {
			$this->output("create DNS record \"$n\" => <span style=\"color:green\">success</span><br>");
		} else {
			$this->output("create DNS record \"$n\" => <span style=\"color:red\">error</span><br>");
		}
	}

	private function deleteOldDnsRecords ($zone_id): void {
		$url = "https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records";
		sleep($this->pause);
		$this->curl($url);
		if (empty($this->res->result)) {
			$this->output("delete DNS record => <span style=\"color:dodgerblue\">empty</span><br>");
		}
		foreach ($this->res->result as $e) {
			$id_zone = $e->id;
			$name = $e->name;
			$url = "https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records/$id_zone";
			$this->curl($url, 'delete');
			if ($this->res->success) {
				$this->output("delete DNS record \"$name\" => <span style=\"color:green\">success</span><br>");
			} else {
				$this->output("delete DNS record \"$name\" => <span style=\"color:red\">error</span><br>");
			}
		}
	}

	private function setAlwaysUseHttps ($zone_id): void {
		$url = "https://api.cloudflare.com/client/v4/zones/$zone_id/settings/always_use_https";
		$data = json_encode(["value" => "on"]);
		$this->curl($url, 'patch', $data);
		if ($this->res->success) {
			$this->output("always use https => <span style=\"color:green\">success</span><br>");
		} else {
			$this->output("always use https => <span style=\"color:red\">error</span><br>");
		}
	}

	private function setAutomaticHttpsRewrites ($zone_id): void {
		$url = "https://api.cloudflare.com/client/v4/zones/$zone_id/settings/automatic_https_rewrites";
		$data = json_encode(["value" => "on"]);
		$this->curl($url, 'patch', $data);
		if ($this->res->success) {
			$this->output("automatic https rewrites => <span style=\"color:green\">success</span><br>");
		} else {
			$this->output("automatic https rewrites => <span style=\"color:red\">error</span><br>");
		}
	}

	private function setSsl ($zone_id): void {
		$url = "https://api.cloudflare.com/client/v4/zones/$zone_id/settings/ssl";
		$data = json_encode(["value" => $this->ssl]);
		$this->curl($url, 'patch', $data);
		if ($this->res->success) {
			$this->output("change SSL setting => <span style=\"color:green\">success</span><br>");
		} else {
			$this->output("change SSL setting => <span style=\"color:red\">error</span><br>");
		}
	}

	#[NoReturn] public function changeIp(): void {
		$this->rabbit = new RabbitMQ('cloudflare.service','cloudflare.queue','cloudflare', connection_timeout: 120);
		if (empty($this->cf_accounts)) {
			$this->output("Empty");
			$this->stop();
		}

		foreach ($this->cf_accounts as $acc) {
			$email = $acc['login'];
			$api_key = $acc['token'];
			$this->setHeaders($email, $api_key);

			$this->output($email . '<br>');
			$this->authorize(false);

			if ($this->auth_code == 200) {
				$domains = $this->getDomains();
				$this->output('domains: ' . count($domains) . '<br>');

				foreach ($domains as $domain) {
					$this->processDomain($domain);
				}
			} else {
				$this->output('<br>--------------------<br>');
				continue;
			}

			$this->output('<br>--------------------<br>');
		}
		$this->stop();
	}

	private function getDomains($num_domains = 200)
	{
		$url = "https://api.cloudflare.com/client/v4/zones?page=1&per_page=$num_domains";
		$this->curl($url);
		return $this->res->result;
	}

	private function processDomain($domain): void {
		$num_subdomains = 200;
		$name = $domain->name;
		$id = $domain->id;
		$page = 1;

		while (true) {
			$url = "https://api.cloudflare.com/client/v4/zones/$id/dns_records?page=$page&per_page=$num_subdomains";
			$this->curl($url);

			foreach ($this->res->result as $record) {
				$this->processDNSRecord($id, $record);
			}

			if ($page == $this->res->result_info->total_pages) {
				break;
			}
			$page++;
		}
	}

	private function processDNSRecord($zone_id, $record): void {
		$content_dns = $record->content;
		$id_dns = $record->id;
		$type_dns = $record->type;
		$name_dns = $record->name;

		if ($this->del == 1) {
			$this->deleteDNSRecords($zone_id);
			$this->createDNSRecords($zone_id, $name_dns);
		}

		if ($this->replaced_ip && $content_dns == $this->old_ip) {
			$data = [
				'type' => $type_dns,
				'name' => $name_dns,
				'content' => $this->new_ip,
				'proxied' => true
			];
			$this->updateDNSRecord($zone_id, $id_dns, $data);
		}
	}

	private function deleteDNSRecords($zone_id): void {
		$url = "https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records";
		sleep($this->pause);
		$this->curl($url);

		foreach ($this->res->result as $record) {
			$id_zone = $record->id;
			$name = $record->name;
			$url = "https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records/$id_zone";
			$type = 'delete';
			$this->curl($url, $type);

			if ($this->res->success) {
				$this->output("delete DNS record \"$name\" => <span style=\"color:green\">success</span><br>");
			} else {
				$this->output("delete DNS record \"$name\" => <span style=\"color:red\">error</span><br>");
			}
		}
	}

	private function createDNSRecords($zone_id, $name_dns): void {
		$url = "https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records";
		$data = [
			'type' => 'A',
			'name' => $name_dns,
			'content' => $this->new_ip,
			'proxied' => true
		];
		$this->createDNSRecord($url, $data);

		$n = $this->wildcard == 1 ? '*' : 'www';
		$data = [
			'type' => 'A',
			'name' => $n,
			'content' => $this->new_ip,
			'proxied' => true
		];
		$this->createDNSRecord($url, $data);
	}

	private function createDNSRecord($url, $data): void {
		$type = 'post';
		$this->curl($url, $type, json_encode($data));

		if ($this->res->success) {
			$this->output("create DNS record \"{$data['name']}\" => <span style=\"color:green\">success</span><br>");
		} else {
			$this->output("create DNS record \"{$data['name']}\" => <span style=\"color:red\">error</span><br>");
		}
	}

	private function updateDNSRecord($zone_id, $id_dns, $data): void {
		$url = "https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records/$id_dns";
		$type = 'put';
		$this->curl($url, $type, json_encode($data));
	}

	/**
	 * @throws Exception
	 */
	#[NoReturn] public function test(): void {
		$this->rabbit = new RabbitMQ('cloudflare.service','cloudflare.queue','cloudflare', connection_timeout: 120);
		for ($i = 1; $i <= 10; $i++) {
			$this->output("test \"{$i}\" => <span style=\"color:green\">success</span><br>");
			sleep(1);
		}
		$this->stop();
	}

	public function output ($str): void {
		$this->rabbit->publish($str);
	}

	#[NoReturn] public function stop (): void {
		$this->output('done!');
		exit();
	}
}