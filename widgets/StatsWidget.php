<?php

namespace app\widgets;

use app\components\Middleware;
use DateTime;
use Yii;
use yii\bootstrap5\Widget;

/**
 * Виджет StatsWidget для отображения колитчество ботов в секунду и средней нагрузки.
 *
 * Этот виджет извлекает данные из логов nginx и cat/process.
 */
class StatsWidget extends Widget
{

    public $laOutput;
    public $botOutput;

    /**
     * Инициализация виджета.
     *
     * Выполняет sh скрипты и получет данные
     */
    public function init()
    {
        parent::init();

        $this->laOutput = shell_exec("sh la.sh df");

/*        $f = shell_exec("tail -n 5000 /var/log/nginx/dor.access.log | grep -E 'google|yahoo|duckduck|bing' | awk '{print $4}' | uniq -c");

        if ($f === null) {
            $f = '';
        }

        $totalBots = 0;
        $lines = explode("\n", trim($f));
        foreach ($lines as $line) {
            // Разбиваем строку на компоненты
            $parts = preg_split('/\s+/', trim($line));

            // Проверяем, что у нас есть правильный формат строки
            if (count($parts) >= 2) {
                // Извлекаем количество ботов из первой части строки
                $botCount = (int)$parts[0];

                // Увеличиваем общий счетчик ботов
                $totalBots += $botCount;
            }
        }*/

        $this->botOutput =  shell_exec('tail -n 10000 /var/log/nginx/dor.access.log | grep "$(date -d "1 second ago" +"%d/%b/%Y:%H:%M:%S")" | grep -E "google|bing|yahoo|duckduck" | wc -l');
    }

    /**
     * Выполняет рендеринг виджета.
     *
     * Создает HTML-содержимое для отображения статистики.
     *
     * @return string HTML-содержимое виджета.
     */
    public function run()
    {
        $output = '<div class="dropdown ms-sm-3 header-item" style="display:-webkit-box">';
        $output .= '<i class="bx bx-chip"></i> ' . $this->laOutput;
        $output .= '<br>';
        $output .= '<i class="bx bx-bot" style="padding-right:2px"></i> ' . $this->botOutput;
        $output .= '</div>';
        return $output;
    }
}