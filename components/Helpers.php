<?php

namespace app\components;

use app\models\Statistics;
use Yii;
use yii\data\BaseDataProvider;

class Helpers
{
    public static function formatterUrl($db_id, $key): string
    {
        $key_id = $key['id'];
        $key = $key['text'];

        //Key formatting
        $key = strtolower($key);
        $key = preg_replace('/( |-)/', '-', $key);
        $key = preg_replace('/\W+/', '-', $key);
        $key = preg_replace('/_+/', '-', $key);
        return $key . '-' . $db_id . '-' . $key_id . '.html';
    }

    public static function generateRandomString($length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function guidv4($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function countDaysBetweenDates($d1): float
    {
        $d1_ts = strtotime($d1);
        $d2_ts = strtotime("now");

        $seconds = abs($d1_ts - $d2_ts);

        return floor($seconds / 86400);
    }


    public static function getRemoteIp(): string
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            // Используем IP, переданный Cloudflare
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }


    public static function getDomain($url = null): bool|string
    {
        if ($url == null) {
            $url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }
        $pieces = parse_url($url);
        $domain = $pieces['host'] ?? '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        return false;
    }


    public static function isBot()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return preg_match('/rambler|abacho|acoi|GoogleOther|Googlebot-Image|python|ahrefs|curl|censys|odin|prestashop|Buck|InternetMeasurement|Dataprovider|sindresorhus|okhttp|GeedoProductSearch|colly|Go-http-client|WordPress|spaziodati|accona|aspseek|altavista|estyle|scrubby|lycos|geona|ia_archiver|alexa|sogou|skype|facebook|twitter|pinterest|linkedin|naver|bing|google|yahoo|duckduckgo|yandex|baidu|teoma|xing|java\/1.7.0_45|bot|crawl|slurp|spider|mediapartners|\sask\s|\saol\s/i', $_SERVER['HTTP_USER_AGENT']);
        }
        return false;
    }

    public static function getSubdomain()
    {
        $host = Yii::$app->request->hostInfo;
        $urlParts = parse_url($host);

        if (isset($urlParts['host'])) {
            $hostParts = explode('.', $urlParts['host']);

            if (count($hostParts) == 3) {
                return $hostParts[0];
            }

            if (count($hostParts) == 4) {
                return $hostParts[0];
            }
        }

        return null;
    }

    public static function getDomainWithoutZone($domain): ?string
    {
        $parts = explode('.', $domain);

        if (count($parts) < 3) {
            return $parts[0];
        }

        array_pop($parts);

        return array_pop($parts);
    }

    public static function extractInfoFromUrl($url)
    {
        // Разбираем URL на части
        $parsedUrl = parse_url($url);

        // Получаем путь (часть URL после домена)
        $path = isset($parsedUrl['path']) ? trim($parsedUrl['path'], '/') : '';

        // Заменяем тире и подчеркивания пробелами
        $path = str_replace(['-', '_'], ' ', $path);

        // Разбиваем путь на части по слешам и берем только значимые части
        $segments = array_filter(explode('/', $path));

        unset($segments[0]);

        // Преобразуем массив в строку с пробелами между элементами
        return implode(' ', $segments);
    }

    public static function formatRegexUrl($url, $domain = null)
    {
        if ($domain) {
            $url = str_replace('{domain}', $domain, $url);
        }

        return preg_replace_callback('/{[wcdrand]+(\d+)(?:-(\d+))?}/i', function ($matches) {
            $randchars = '';

            switch ($matches[0][1]) {
                case 'w':
                    $randchars = '0123456789abcdefghijklmnopqrstuvwxyz';
                    break;
                case 'c':
                    $randchars = 'abcdefghijklmnopqrstuvwxyz';
                    break;
                case 'd':
                    $randchars = '0123456789';
                    break;
                case 'r':
                    return rand($matches[1], $matches[2]);
            }

            return substr(str_shuffle($randchars), 0, $matches[1]);
        }, $url);
    }

    public static function isGoogle()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return preg_match('/google/i', $_SERVER['HTTP_USER_AGENT']);
        }
        return false;
    }

    /**
     * Парсит параметр black|white для извлечения пути к виду и параметра 'url', если таковой имеется
     *
     * @param mixed $project Проект, который нужно распарсить.
     * @return array Массив, содержащий путь к виду и значение параметра 'url'.
     *               Если параметр 'url' отсутствует, возвращается пустая строка.
     */
    public static function checkAndGetUrl($project): array
    {
        $viewPath = $project->black;
        $parsedUrl = parse_url($viewPath);
        $view = $parsedUrl['path'];
        if (isset($parsedUrl['query'])) {
            $queryString = $parsedUrl['query'];
            preg_match('/url=(.*)/', $queryString, $matches);
            $url = $matches[1];
        } else {
            $url = $project->black_redir_url;
        }

        return [$view, $url];
    }

    /**
     * Вычисляет и возвращает статистику по ботам и хитам для всех моделей в заданном DataProvider.
     *
     * Этот метод суммирует количество ботов и хитов за сегодня, вчера и в общем для всех моделей,
     * возвращаемых `dataProvider`, и форматирует результат в массиве.
     *
     * @param BaseDataProvider $dataProvider Объект DataProvider, содержащий модели для вычисления статистики.
     *
     * @return array Массив с двумя элементами:
     * - `'bots'`: Строка, содержащая суммарные данные о ботах в формате:
     *   "Общее количество ботов за сегодня | Общее количество ботов за вчера | Общее количество ботов всего"
     * - `'hits'`: Строка, содержащая суммарные данные о хитах в формате:
     *   "Общее количество хитов за сегодня | Общее количество хитов за вчера | Общее количество хитов всего"
     */
    public static function getTotalStatistics(BaseDataProvider $dataProvider): array
    {
        $totalTodayBots = 0;
        $totalYesterdayBots = 0;
        $totalTotalBots = 0;

        $totalTodayHits = 0;
        $totalYesterdayHits = 0;
        $totalTotalHits = 0;


        $models = $dataProvider->getModels();

        foreach ($models as $model) {
            $stat = Statistics::class;

            $totalTodayBots += $stat::getTodayBots($model->id);
            $totalYesterdayBots += $stat::getYesterdayBots($model->id);
            $totalTotalBots += $stat::getTotalBots($model->id);

            $totalTodayHits += $stat::getTodayHits($model->id);
            $totalYesterdayHits += $stat::getYesterdayHits($model->id);
            $totalTotalHits += $stat::getTotalHits($model->id);
        }

        return [
            'bots' => [
                'today' => $totalTodayBots,
                'yesterday' => $totalYesterdayBots,
                'total' => $totalTotalBots,
            ],
            'hits' => [
                'today' => $totalTodayHits,
                'yesterday' => $totalYesterdayHits,
                'total' => $totalTotalHits,
            ]
        ];
    }

    public static function getRedirectTypeRand($types): ?string
    {
        if ($types) {
            $type = explode(',', $types);
            return $type[array_rand($type)];
        }
        return null;
    }

    public static function calculateProbability(int $chance): bool
    {
        $randomValue = mt_rand(0, 100);
        return $randomValue <= $chance;
    }

	public static function logInfo ($uniq, $message, $file_name): void {
		$logFile = Yii::getAlias('@runtime/logs/'.$file_name);
		$formattedMessage = "ID: {$uniq} | <span style='color: white; font-weight: bold'>{$message} </span>|" . PHP_EOL;
		file_put_contents($logFile, $formattedMessage, FILE_APPEND);
	}

	public static function logError ($uniq, $message, $file_name): void {
		$logFile = Yii::getAlias('@runtime/logs/'. $file_name);
		$formattedMessage = "ID: {$uniq} | <span style='color: red;'>Error: </span>{$message} |" . PHP_EOL;
		file_put_contents($logFile, $formattedMessage, FILE_APPEND);
	}

	public static function logSuccess ($uniq, $message, $file_name): void {
		$logFile = Yii::getAlias('@runtime/logs/' . $file_name);
		$formattedMessage = "ID: {$uniq} | <span style='color: green;'>Success: </span> {$message} |" . PHP_EOL;
		file_put_contents($logFile, $formattedMessage, FILE_APPEND);
	}

	public static function logReportToSend ($data, $file_name): void {
		$logFail = Yii::getAlias('@runtime/logs/'. $file_name);
		file_put_contents($logFail, $data, FILE_APPEND);
	}
}