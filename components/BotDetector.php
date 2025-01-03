<?php

namespace app\components;

class BotDetector
{
    const GOOGLE_BOT = 1;
    const BING_BOT = 3;
    const YAHOO_BOT = 4;
    const DUCKDUCKGO_BOT = 5;

    /**
     * Возвращает массив типов ботов и их названий
     *
     * @return array Массив типов ботов
     */
    public static function getBotArray(): array
    {
        return [
            self::GOOGLE_BOT => 'Гугл бот',
            self::BING_BOT => 'Бинг бот',
            self::YAHOO_BOT => 'Яху бот',
            self::DUCKDUCKGO_BOT => 'ДакДакГо бот',
        ];
    }

    public static function getBotImages(): array
    {
        $imgStyle = 'style="max-width: 20px; max-height: 20px;"';
        return [
            self::GOOGLE_BOT => '<img src="/images/google_logo.svg" alt="Гугл бот" ' . $imgStyle . ' />',
            self::BING_BOT => '<img src="/images/bing_logo.svg" alt="Бинг бот" ' . $imgStyle . ' />',
            self::YAHOO_BOT => '<img src="/images/yahoo_logo.svg" alt="яху бот" ' . $imgStyle . ' />',
            self::DUCKDUCKGO_BOT => '<img src="/images/duckduckgo_logo.svg" alt="ДакДак бот" ' . $imgStyle . ' />',
        ];
    }

    /**
     * Возвращает массив типов ботов и их пользовательских агентов
     *
     * @return array Массив типов ботов и их пользовательских агентов
     */
    public static function getBotUserAgents(): array
    {
        return [
            self::GOOGLE_BOT => 'google',
            self::BING_BOT => 'bing',
            self::YAHOO_BOT => 'slurp',
            self::DUCKDUCKGO_BOT => 'duckduck',
        ];
    }

    /**
     * Возвращает массив цветов ботов и их пользовательских агентов
     *
     * @return array Массив типов ботов и их пользовательских агентов
     */
    public static function getBotColors(): array
    {
        return [
            self::GOOGLE_BOT => '#87b8ff',
            self::BING_BOT => '#00bcf2',
            self::YAHOO_BOT => '#720e9e',
            self::DUCKDUCKGO_BOT => '#de5833',
        ];
    }

    /**
     * Определяет тип бота по пользовательскому агенту
     *
     * @param string $userAgent Пользовательский агент
     * @return int|null Тип бота или null, если бот не определен
     */
    public static function detectBotType(string $userAgent): ?int
    {
        $bots = self::getBotUserAgents();

        foreach ($bots as $type => $botNames) {
            if (is_array($botNames)) {
                foreach ($botNames as $botName) {
                    if (stripos($userAgent, $botName) !== false) {
                        return $type;
                    }
                }
            } else {
                if (stripos($userAgent, $botNames) !== false) {
                    return $type;
                }
            }
        }

        return null;
    }

    /**
     * Проверяет, является ли пользовательский агент допустимым ботом
     *
     * @param string $allowedBots Массив допустимых типов ботов
     * @return bool Возвращает true, если пользовательский агент является допустимым ботом, иначе false
     */
    public static function isAllowedBot(string $allowedBots): bool
    {
        $allowedBots = explode(',', $allowedBots);

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $botType = self::detectBotType($_SERVER['HTTP_USER_AGENT']);
            if ($botType !== null && in_array((string)$botType, $allowedBots, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Проверяет, является ли пользовательский реферером допустимым по поисковой системе
     *
     * @param string $allowedBots Массив допустимых типов ботов
     * @return bool Возвращает true, если пользовательский агент является допустимым поисковой системы, иначе false
     */
    public static function isAllowedReferer(string $allowedBots): bool
    {
        $allowedBots = explode(',', $allowedBots);

        if (isset($_SERVER['HTTP_REFERER'])) {
            $botType = self::detectBotType($_SERVER['HTTP_REFERER']);
            if ($botType !== null && in_array((string)$botType, $allowedBots, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Возвращает строку с названиями ботов по их номерам
     *
     * @param string $bots Строка с номерами ботов, разделенными запятыми
     * @return string Строка с названиями ботов, разделенными запятыми
     */
    public static function getBotNamesByNumbers(string $bots): string
    {
        $botNumbers = explode(',', $bots);
        $botArray = self::getBotArray();
        $botNames = [];

        foreach ($botNumbers as $botNumber) {
            $botNumber = (int)$botNumber;
            if (isset($botArray[$botNumber])) {
                $botNames[] = $botArray[$botNumber];
            }
        }

        return implode(', ', $botNames);
    }

    /**
     * Возвращает строку с изображениями ботов по их номерам
     *
     * @param string $bots Строка с номерами ботов, разделенными запятыми
     * @return string Строка с изображениями ботов
     */
    public static function getBotImagesByNumbers(string $bots): string
    {
        $botNumbers = explode(',', $bots);
        $botArray = self::getBotImages();
        $botImages = [];

        foreach ($botNumbers as $botNumber) {
            $botNumber = (int)$botNumber;
            if (isset($botArray[$botNumber])) {
                $botImages[] = $botArray[$botNumber];
            }
        }

        return implode(' ', $botImages);
    }

    /**
     * Возвращает HTML строку с названием бота и соответствующим стилем
     *
     * @param string $userAgent Пользовательский агент
     * @return string HTML строка с названием бота
     */
    public static function getStyledBotName(string $userAgent): string
    {
        $botType = self::detectBotType($userAgent);
        if ($botType !== null) {
            $botArray = self::getBotArray();
            $botColors = self::getBotColors();
            $botName = $botArray[$botType] ?? 'Unknown Bot';
            $botColor = $botColors[$botType] ?? '#000000';
            return "<span style=\"padding: 5px; background: $botColor; border-radius: 5px;\">$botName</span>";
        }
        return 'Unknown Bot';
    }

    public static function generateBotStatisticsHtml($botsStat): string
    {
        $botImages = BotDetector::getBotImages();
        $output = '<div class="bot-statistics">';

        if (!empty($botsStat)) {
            foreach ($botsStat as $botType => $count) {
                if (isset($botImages[$botType])) {
                    $output .= '<div class="bot-stat">';
                    $output .= $botImages[$botType] . ' ' . $count;
                    $output .= '</div>';
                }
            }
        }

        $output .= '</div>';
        return $output;
    }
}
