<?php

use app\models\Project;

if (!strpos($domain, '.')) {
    $domains = Project::find()->where(['name' => $domain])->one()->getDomainLinks();
} else {
    $domains = [$domain];
}
$pattern = implode('|', $domains);
// Выполнение shell-скриптов и получение их вывода
$logOutput = shell_exec("sh log.sh " . escapeshellarg($pattern));
$laOutput = shell_exec("sh la.sh df");

// Текущее время
$now = date('Y-m-d H:i:s');

// Обработка вывода скрипта
$logLines = explode("\n", $logOutput);

// HTML-структура страницы
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="1">
    <title><?php echo htmlspecialchars($domain); ?></title>
    <style>
        html {
            background: #1a1d21;
            color: #ddd
        }

        body, td {
            font-family: verdana;
            font-size: 11px;
        }

        td {
            padding: 0 5px
        }

        b {
            padding: 0 2px 0 1px;
            font-size: 10px;
            border-radius: 3px;
        }

        .y {
            background: #a3a905;
            color: #fff
        }

        .r {
            background: red;
            color: #fff
        }

        .g {
            background: green;
            color: #fff
        }

        .b {
            background: #6691e7;
            color: #fff
        }

        .v {
            background: #9805a9;
            color: #fff
        }

        .v2 {
            background: #4080a9;
            color: #fff
        }

        .v3 {
            background: #42a5f5;
            color: #fff
        }

        .v4 {
            background: #346b9c;
            color: #fff
        }

        .v5 {
            background: #724ec0;
            color: #fff
        }

        .v6 {
            background: #747F5D;
            color: #fff
        }

        .v7 {
            background: #7f635d;
            color: #fff
        }

        .o {
            background: #d09300;
            color: #fff
        }

        .bing {
            background: #1abad2;
            color: #fff
        }

        a, a:visited {
            color: #547ba6
        }

        tr:nth-child(odd) {
            background-color: #35383c
        }

        tr:nth-child(even) {
            background-color: #3c3f42
        }
    </style>
</head>
<body>
<h3><?php echo htmlspecialchars($domain); ?></h3>
Нагрузка сервера: <?php echo htmlspecialchars($laOutput); ?>

<table width="100%">
    <tr style="font-weight:bold">
        <td style="width: 190px">IP</td>
        <td style="width: 350px">Time</td>
        <td style="width: 350px">Host/Request</td>
        <td style="width: 20px">Status</td>
        <td style="width: 100px">Body bytes</td>
        <td>Referer</td>
        <td>User-Agent</td>
    </tr>

    <?php
    foreach ($logLines as $line) {
        // Пример обработки строки логов (нужно адаптировать под ваш формат)
        if (preg_match('/(\S+) (\S+) - (\S*) \[(.*?)\] "(.*?)" (\d+) (\d+) "(.*?)" "(.*?)" "(.*?)" (.*?)$/', $line, $matches)) {
            $ip = $matches[1];         // IP клиента
            $host = $matches[2];       // Домен (Host)
            $remote_user = $matches[3]; // Пользователь (если присутствует, но в вашем примере - пусто)
            $time = $matches[4];       // Время запроса
            $request = $matches[5];    // Запрос (например, "GET /ky8xl7vjsm HTTP/1.0")
            $status = $matches[6];     // Код состояния HTTP
            $bytes = $matches[7];      // Количество отправленных байт
            $referer = $matches[8];    // Реферер (пусто в вашем примере)
            $ua = $matches[9];         // User-Agent
            $redirect_url = $matches[11];    // URL редиректа (пусто в вашем примере)


            // Примеры классификации user-agent
            if (preg_match('/google/', $ua)) {
                $ua_class = 'v3';
            } elseif (preg_match('/Googlebot-Image/', $ua)) {
                $ua_class = 'o';
            } elseif (preg_match('/GoogleOther/', $ua)) {
                $ua_class = 'v6';
            } elseif (preg_match('/bingbot/', $ua)) {
                $ua_class = 'bing';
            } else {
                $ua_class = '';
            }

            if (preg_match('/\s(\S+)\sHTTP/', $request, $request_matches)) {
                $path = $request_matches[1]; // Получаем "/index.html" или другой путь
            } else {
                $path = ''; // Если не удалось распарсить запрос
            }

            // Вывод строки таблицы
            echo "<tr>";
            echo "<td>$ip</td>";
            echo "<td>$time</td>";
            echo "<td>$host$path";

            // Если есть редирект, выводим его
            if (!empty($redirect_url) && $redirect_url !== '-') {
                echo "<br/><span title='Redirect to'>⤷</span> <em style='background-color: darkgreen'>$redirect_url</em>";
            }

            echo "</td>";
            echo "<td><b class='$ua_class'>$status</b></td>";
            echo "<td>$bytes</td>";
            echo "<td>$referer</td>";
            echo "<td>$ua</td>";
            echo "</tr>";
        }
    }
    ?>

</table>
</body>
</html>
