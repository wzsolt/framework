<?php

namespace Framework\Components;

use DateTime;
use DateTimeZone;
use Exception;
use Framework\Helpers\Str;
use Framework\Helpers\Utils;
use Framework\Locale\Translate;
use Framework\Models\Database\Db;

class Functions
{
    /**
     * Custom date formatting function
     *
     * @param string $date date to convert (YYYY-MM-DD HH:II:SS or timestamp())
     * @param int $view the type of display format:
     *   1: 2014. jan. 01.
     *  10: 2014. január 1.
     *  11: 2014. január 1
     *  12: 2014. január
     *   2: 2014. jan. 01., Mon.
     *   3: jan. 01.
     *  32: 01.01.
     *   4: jan. 01., Mon.
     *  41: january 01., Monday
     *   5: 2014. jan. 01. 12:34
     *   6: 12:34
     *
     * @param bool $addTimezone whether to add timezone
     * @return string
     */
    public function formatDate(?string $date, int $view = 0, bool $addTimezone = true):string
    {
        $hostConfig = HostConfig::create();

        $item = [];

        if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') return '';
        $out = '';
        $formats = Utils::getLocaleSettings($hostConfig->getLanguage());

        if (!is_numeric($date)) {
            $date = str_replace(['/', '.'], '-', trim($date, '.'));
            $dt = strtotime($date);
        } else {
            $dt = $date;
        }
        if ($addTimezone && date('His', $dt) > 0) {
            $timeZone = $hostConfig->getTimeZone();

            try {
                $datetime = new DateTime(date('Y-m-d H:i:s', $dt));
                $tz = new DateTimeZone($timeZone);
                $date2 = $datetime->setTimezone($tz)->format('Y-m-d H:i:s');
                $dt = strtotime($date2);
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
        $_months = [];
        $_monthsShort = [];
        $_days = [];
        $_daysShort = [];
        for ($i = 1; $i <= 12; $i++) {
            $_months[$i] = Translate::get('LBL_MONTH_' . $i);
            $_monthsShort[$i] = Translate::get('LBL_MONTH_SHORT_' . $i);
            if ($i <= 7) {
                $_days[$i] = Translate::get('LBL_DAY_' . $i);
                $_daysShort[$i] = Translate::get('LBL_DAY_SHORT_' . $i);
            }
        }
        $_days[0] = $_days[7];
        $_daysShort[0] = $_daysShort[7];
        $item['m'] = date('n', $dt);
        $item['mm'] = date('m', $dt);
        $item['M'] = $_monthsShort[date('n', $dt)];
        $item['MM'] = $_months[date('n', $dt)];
        $item['d'] = date('j', $dt);
        $item['dd'] = date('d', $dt);
        $item['D'] = $_daysShort[date('w', $dt)];
        $item['DD'] = $_days[date('w', $dt)];
        $item['y'] = date('y', $dt);
        $item['yy'] = date('Y', $dt);
        if ($formats['timeformat'] == 24) {
            $item['time'] = date('H:i', $dt);
        } else {
            $item['time'] = date('h:i A', $dt);
        }
        switch ($view) {
            case 1: # 2014. jan. 01.
                for ($i = 0; $i < 3; $i++) {
                    if ($formats['dateorder'][$i] == 'y') {
                        $out .= $item['yy'] . '. ';
                    }
                    if ($formats['dateorder'][$i] == 'm') {
                        $out .= $item['M'] . ' ';
                    }
                    if ($formats['dateorder'][$i] == 'd') {
                        $out .= $item['dd'] . '. ';
                    }
                }
                break;
            case 10: # 2014. január 1.
                for ($i = 0; $i < 3; $i++) {
                    if ($formats['dateorder'][$i] == 'y') {
                        $out .= $item['yy'] . '. ';
                    }
                    if ($formats['dateorder'][$i] == 'm') {
                        $out .= $item['MM'] . ' ';
                    }
                    if ($formats['dateorder'][$i] == 'd') {
                        $out .= $item['d'] . '. ';
                    }
                }
                break;
            case 11: # 2014. január 1
                for ($i = 0; $i < 3; $i++) {
                    if ($formats['dateorder'][$i] == 'y') {
                        $out .= $item['yy'] . '. ';
                    }
                    if ($formats['dateorder'][$i] == 'm') {
                        $out .= $item['MM'] . ' ';
                    }
                    if ($formats['dateorder'][$i] == 'd') {
                        $out .= $item['d'];
                    }
                }
                break;
            case 12: # 2014. január
                for ($i = 0; $i < 3; $i++) {
                    if ($formats['dateorder'][$i] == 'y') {
                        $out .= $item['yy'] . '. ';
                    }
                    if ($formats['dateorder'][$i] == 'm') {
                        $out .= $item['MM'] . ' ';
                    }
                }
                break;
            case 2: # 2014. jan. 01., Mon.
                for ($i = 0; $i < 3; $i++) {
                    if ($formats['dateorder'][$i] == 'y') {
                        $out .= $item['yy'] . '. ';
                    }
                    if ($formats['dateorder'][$i] == 'm') {
                        $out .= $item['M'] . ' ';
                    }
                    if ($formats['dateorder'][$i] == 'd') {
                        if ($i == 0) $out .= $item['D'] . ', ';
                        $out .= $item['dd'] . '. ';
                        if ($i == 2) $out .= $item['D'];
                    }
                }
                break;
            case 3:    # jan. 01.
            case 31:
                for ($i = 0; $i < 3; $i++) {
                    if ($formats['dateorder'][$i] == 'm') {
                        $out .= $item['M'] . ' ';
                    }
                    if ($formats['dateorder'][$i] == 'd') {
                        $out .= $item['dd'] . '. ';
                    }
                }
                break;
            case 32:    # 01.01.
                for ($i = 0; $i < 3; $i++) {
                    if ($formats['dateorder'][$i] == 'm') {
                        $out .= $item['mm'] . '.';
                    }
                    if ($formats['dateorder'][$i] == 'd') {
                        $out .= $item['dd'] . '.';
                    }
                }
                break;
            case 4: # jan. 01., Mon.
                for ($i = 0; $i < 3; $i++) {
                    if ($formats['dateorder'][$i] == 'm') {
                        $out .= $item['M'] . ' ';
                    }
                    if ($formats['dateorder'][$i] == 'd') {
                        if ($i == 0) $out .= $item['D'] . ', ';
                        $out .= $item['dd'] . '. ';
                        if ($i == 2) $out .= $item['D'];
                    }
                }
                break;
            case 41: # january 01., Monday
                for ($i = 0; $i < 3; $i++) {
                    if ($formats['dateorder'][$i] == 'm') {
                        $out .= $item['MM'] . ' ';
                    }
                    if ($formats['dateorder'][$i] == 'd') {
                        if ($i == 0) $out .= $item['DD'] . ', ';
                        $out .= $item['dd'] . '. ';
                        if ($i == 2) $out .= $item['DD'];
                    }
                }
                break;
            case 5: # 2014. jan. 01. 12:34
                for ($i = 0; $i < 3; $i++) {
                    if ($formats['dateorder'][$i] == 'y') {
                        $out .= $item['yy'] . '. ';
                    }
                    if ($formats['dateorder'][$i] == 'm') {
                        $out .= $item['M'] . ' ';
                    }
                    if ($formats['dateorder'][$i] == 'd') {
                        $out .= $item['dd'] . '. ';
                    }
                }
                $out .= ' ' . $item['time'];
                break;
            case 6: # 12:34
                $out .= $item['time'];
                break;
            default:
                $tmp = explode('-', $formats['dateformat']);
                $out = [];

                foreach($tmp as $segment) {
                    if(isset($item[$segment])) {
                        $out[] = $item[$segment];
                    }
                }

                $out = implode('-', $out);
                break;
        }
        return trim($out);
    }


    /**
     * Get time in textual
     *
     * @param string $label
     * @param bool $responsive
     * @return string
     */
    public function getTimeText(string $label, bool $responsive = false):string
    {
        $result = Translate::get($label);

        if ($responsive) {
            $result = mb_substr($result, 0, 1, 'UTF-8') . '<span class="d-none d-md-block">' . mb_substr($result, 1, null, 'UTF-8') . '</span>';
        }

        return $result;
    }

    public function secondsToTime(float $seconds, int $format = 1, bool $hideSeconds = true):string
    {
        return $this->formatTime($seconds, $format, $hideSeconds);
    }

    public function minutesToTime(?float $seconds, int $format = 1, bool $hideSeconds = true):string
    {
        return $this->formatTime($seconds * 60, $format, $hideSeconds);
    }

    public function formatTime(float $seconds, int $format = 1, bool $hideSeconds = true):string
    {
        $out = '';
        $short = '';
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = floor($seconds % 60);
        if ($minutes < 1) {
            $hideSeconds = false;
        }
        switch ($format) {
            case 1: // HH:MM:SS
            default:
                $out .= sprintf("%02d:", $hours);
                $out .= sprintf("%02d", $minutes);
                if ($seconds > 0 && !$hideSeconds) {
                    $out .= sprintf(":%02d", $seconds);
                }
                break;
            case 3: // X h Y min Z sec
                $short = '_SHORT';
            case 2: // X hours Y minutes Z seconds
                if ($hours > 0) {
                    $out .= $hours . ' ' . $this->getTimeText(($hours > 1 ? 'LBL_HOURS' . $short : 'LBL_HOUR' . $short)) . ' ';
                }
                if ($minutes > 0) {
                    $out .= $minutes . ' ' . $this->getTimeText(($minutes > 1 ? 'LBL_MINUTES' . $short : 'LBL_MINUTE' . $short));
                }
                if ($seconds > 0 && !$hideSeconds) {
                    $out .= ' ' . $seconds . ' ' . $this->getTimeText(($seconds > 1 ? 'LBL_SECONDS' . $short : 'LBL_SECOND' . $short));
                }
                break;
        }

        return $out;
    }

    /**
     * Format price to localized format
     *
     * @param float $price
     * @param string|false $currency
     * @param bool $displayCurrency whether to add the currency at the end of the string or not
     * @return string
     */
    public function formatPrice(float $price, string|false $currency = false, bool $displayCurrency = true):string
    {
        $hostConfig = HostConfig::create();
        $currencies = $hostConfig->getCurrencies();

        $formats = Utils::getLocaleSettings($hostConfig->getLanguage());
        $out = number_format($price, $formats['currency_round'], $formats['decimal_point'], $formats['thousand_sep']);

        if ($currency && $displayCurrency) {
            if (!empty($currencies[$currency])) $currency = $currencies[$currency];
            $out .= ' ' . $currency;
        }

        return $out;
    }



    public static function getLocationByIp(string $ip = ''):array
    {
        static $cache = [];

        if (Empty($ip)) {
            $ip = Utils::getClientIP();
        }

        if (empty($cache[$ip])) {
            $row = Db::create()->getFirstRow(
                Db::select(
                    'ip_cache',
                    [],
                    [
                        'ic_ip' => $ip,
                        'ic_expire>' => 'NOW()'
                    ]
                )
            );

            if ($row) {
                $cache[$ip] = [
                    "ip" => $ip,
                    "city" => $row['ic_city'],
                    "state" => $row['ic_state'],
                    "country" => $row['ic_country'],
                    "countryCode" => $row['ic_country_code']
                ];
            } else {
                $cache[$ip] = Utils::ipInfo($ip);

                $cache[$ip]['ip'] = $ip;

                Db::create()->sqlQuery(
                    Db::insert(
                        'ip_cache',
                        [
                            'ic_ip' => $ip,
                            'ic_requested' => 'NOW()',
                            'ic_expire' => date('Y-m-d H:i:s', time() + (60 * 60 * 24 * IP_CACHE_TIMEOUT)),
                            'ic_request_count' => 'INCREMENT',
                            'ic_country_code' => ($cache[$ip]['country_code'] ?? ''),
                            'ic_country' => ($cache[$ip]['country'] ?? ''),
                            'ic_state' => ($cache[$ip]['state'] ?? ''),
                            'ic_city' => ($cache[$ip]['city'] ?? ''),
                            'ic_lat' => ($cache[$ip]['latitude'] ?? null),
                            'ic_lng' => ($cache[$ip]['longitude'] ?? null)
                        ],
                        [
                            'ic_ip'
                        ]
                    )
                );
            }
        }

        return $cache[$ip];
    }

    /**
     * Set global custom variable to db
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setVar(string $key, string $value):void
    {
        Db::create()->sqlQuery(
            Db::insert(
                'variables',
                [
                    'var_key' => $key,
                    'var_value' => $value,
                    'var_client_id' => HostConfig::create()->getClientId(),
                ],
                [
                    'var_key',
                    'var_ug_id',
                    'var_client_id',
                ]
            )
        );
    }

    /**
     * Get global variable from db
     *
     * @param string $key
     * @return string
     */
    public function getVar(string $key):string
    {
        $row = Db::create()->getFirstRow(
            Db::select(
                'variables',
                [
                    'var_value'
                ],
                [
                    'var_key' => $key,
                    'var_client_id' => HostConfig::create()->getClientId()
                ]
            )
        );

        if ($row) {
            return $row['var_value'];
        } else {
            return false;
        }
    }

    public static function getName(?string $code, string $firstName, string $lastName = '', bool $prependCode = false, bool $forceShowFullName = false):string
    {
        if ((User::create()->hasFunctionAccess('show-full-name') && (!empty($firstName) || !empty($lastName))) || $forceShowFullName) {
            return ($prependCode ? '[' . $code . '] ' : '') . Utils::localizeName($firstName, $lastName, HostConfig::create()->getLanguage());
        }

        if(!$code) $code = '';

        return $code;
    }
















    /**
     * Load accessible menus according user access
     * @deprecated
     *
     * @param array $menu
     * @param bool $footer
     * @param string $parentKey
     * @param string|false $group
     * @return array
     */
    public function getAccessibleMenu(array $menu, bool $footer = false, string $parentKey = '', string|false $group = false):array
    {
        $result = [];
        $access_rights = (!empty($this->owner->user->getUser()['access_rights'])) ? array_keys($this->owner->user->getUser()['access_rights']) : [];
        foreach ($menu as $key => $val) {
            switch ($val['display']) {
                case 0: // invisible
                    break;
                case 1: // visible
                case 2: // menu group
                case 3: // footer
                    if (empty($footer) && in_array($val['display'], [3, 4])) break;
                    if (!empty($footer) && in_array($val['display'], [1, 2]) && empty($val['footer'])) break;
                    if (!empty($val['userGroups']) && is_array($val['userGroups']) && $group && !in_array($group, $val['userGroups'])) {
                        break;
                    }
                    if (!empty($val['items'])) {
                        $val['items'] = $this->getAccessibleMenu($val['items'], false, $key, $group);
                        if (!empty($val['items']) || (!empty($val['access']) && in_array($key, $access_rights))) {
                            $result[$key] = $val;
                            $result[$key]['href'] = '';
                            if ($parentKey) $result[$key]['href'] .= $parentKey . '/';
                            $result[$key]['href'] .= $key . '/';
                        }

                    } else {
                        if (empty($val['access']) || in_array($key, $access_rights)) {
                            $result[$key] = $val;
                            $result[$key]['href'] = '';
                            if ($parentKey) $result[$key]['href'] .= $parentKey . '/';
                            $result[$key]['href'] .= $key . '/';
                        }
                    }
                    break;
                case 10: // label
                    $result[$key] = $val;
                    break;
            }
        }
        return $result;
    }
}
