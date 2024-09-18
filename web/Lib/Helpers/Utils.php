<?php

namespace Framework\Helpers;

use Framework\Components\HostConfig;
use Framework\Models\Database\Db;

class Utils
{

    /**
     * Get localized settings from GLOBAL var, defined in constant.php
     *
     * @param string $language
     * @return array
     */
    public static function getLocaleSettings(string $language):array
    {
        if (isset($GLOBALS['REGIONAL_SETTINGS'][$language])) {
            return $GLOBALS['REGIONAL_SETTINGS'][$language];
        } else {
            return $GLOBALS['REGIONAL_SETTINGS']['default'];
        }
    }

    public static function localizeName(string $firstName, string $lastName, string $language):string
    {
        if(!$firstName) $firstName = '';
        if(!$lastName) $lastName = '';

        if($GLOBALS['REGIONAL_SETTINGS'][$language]['nameorder'] == 'first-last'){
            $name = $firstName . ' ' . $lastName;
        }else{
            $name = $lastName . ' ' . $firstName;
        }

        return trim($name);
    }

    public static function decodeCurrencyCode(string $currency):string
    {
        return $GLOBALS['CURRENCIES'][$currency];
    }

    /**
     * Check if a given key exists in an array and return its value.
     *
     * @param array $array The array to search in.
     * @param string $key The key to check for.
     * @return mixed The value of the key if it exists, null otherwise.
     */
    public static function checkKey(array $array, string $key):mixed
    {
        if(isset($array[$key])){
            return $array[$key];
        }

        return null;
    }

    public static function isAssociativeArray(array $array):bool
    {
        if(!Empty($array)) {
            if (array_keys($array) !== range(0, count($array) - 1)) {
                return true;
            } else {
                return false;
            }
        }else{
            return false;
        }
    }


    /**
     * Convert number to regular float format (replace , to .)
     *
     * @param string $str
     * @return string
     */
    public static function floatNumber(string $str):string
    {
        return (float) str_replace(',', '.', $str);
    }

    /**
     * Alias for nullPrefix
     *
     * @param int $number the number which need to add zero prefixes
     * @param int $length total length of the result
     * @return string
     */
    public static function fillNulls(int $number, int $length):string
    {
        return self::nullPrefix($number, $length);
    }

    /**
     * Add zero (0) prefixes to a number for a given length
     *
     * @param int $number
     * @param int $length
     * @return string
     */
    public static function nullPrefix(int $number, int $length):string
    {
        return str_pad($number, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Check email validity
     *
     * @param string $email
     * @param bool $mxLookup
     * @return bool
     */
    public static function checkEmail(string $email, bool $mxLookup = false):bool
    {
        $out = false;

        if(preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,6}$/i", $email)) {
            if ($mxLookup) {
                $tld = substr(strstr($email, '@'), 1);
                if (getmxrr($tld, $email_val) ) $out = true;
                if (checkdnsrr($tld,"ANY")) $out = true;
            } else {
                $out = true;
            }
        }

        return $out;
    }

    /**
     * Check URL weather is it contains HTTP(S)
     *
     * @param string $url
     * @return string
     */
    public static function checkURL(string $url):string
    {
        $url = strtolower($url);
        if(!Empty($url)){
            if(!str_starts_with($url, "http://") && !str_starts_with($url, "https://")){
                $url = "http://" . $url;
            }
        }
        return $url;
    }


    /**
     * Convert string to safe URL characters
     *
     * @param string $link
     * @return string
     */
    public static function safeURL(string $link):string
    {
        $link = Str::removeAccents($link);

        //removing specials
        $pattern = '/[^0-9a-zA-Z- _]/';
        $link = preg_replace($pattern, '', $link);

        $link = strtolower(trim($link));

        //changing _ and space to -
        $pattern = '/[_ ]/';
        $link = preg_replace($pattern, '-', $link);

        return urlencode($link);
    }

    /**
     * Convert string to safe file characters
     *
     * @param string $filename
     * @return string
     */
    public static function safeFileName(string $filename):string
    {
        $filename = trim($filename);
        $filename = Str::removeAccents($filename);

        //removing specials
        $pattern = '/[^0-9a-zA-Z-_. ]/';
        $filename = preg_replace($pattern, '', $filename);

        //changing space to _
        $pattern = '/[ ]/';
        return preg_replace($pattern, '_', $filename);
    }

    /**
     * Generate random string
     *
     * @param int $length
     * @param bool $addExtraChars
     * @return string
     */
    public static function generateRandomString(int $length = 10, bool $addExtraChars = false):string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if($addExtraChars){
            $characters .= '!$%-';
        }

        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /**
     * Credit card formatting
     *
     * @param string $cc
     * @param string $div
     * @return string
     */
    public static function formatCreditCardNumber(string $cc, string $div = ' '):string
    {
        $cc = str_replace(array('-',' '),'',$cc);
        $cc_length = strlen($cc);
        $newCreditCard = substr($cc,-4);
        for($i=$cc_length-5;$i>=0;$i--){
            if((($i+1)-$cc_length)%4 == 0){
                $newCreditCard = $div.$newCreditCard;
            }
            $newCreditCard = $cc[$i].$newCreditCard;
        }

        return $newCreditCard;
    }

    /**
     * Adds array2's content to array1
     *
     * @param $array1 array
     * @param $array2 array
     * @param $overwrite bool
     * @return array
     */
    public static function arrayMerge( array $array1, array $array2, bool $overwrite = false ):array
    {
        foreach ($array2 as $key => $value) {
            if (!isset($array1[$key])) {
                $array1[$key] = $value;
            } else {
                if (is_array($value)) {
                    $array1[$key] = self::arrayMerge($array1[$key], $value);
                } else {
                    if (empty($array1[$key]) || $overwrite) {
                        $array1[$key] = $value;
                    }
                }
            }
        }

        return $array1;
    }

    /**
     * Get top domain name from SERVER global
     *
     * @param string|bool $url
     * @return string
     */
    public static function getMainDomain(string|bool $url = false):string
    {
        if(!$url) {
            if ($_SERVER['HTTP_HOST']) {
                $url = $_SERVER['HTTP_HOST'];
            } else {
                $url = $_SERVER['SERVER_NAME'];
            }
        }

        $pieces = parse_url($url);
        $domain = $pieces['host'] ?? $pieces['path'];
        $tmp = explode('.', $domain);
        $l = count($tmp) - 1;

        return $tmp[$l-1] . '.' . $tmp[$l];
    }

    public static function getClientIP():string
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        return $ip;
    }

    public static function ipInfo(?string $ip = NULL, string $purpose = "location", bool $deep_detect = true):?array
    {
        global $ipCache;

        $output = NULL;
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }

        $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), '', strtolower(trim($purpose)));
        $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            if (!empty($ipCache[$ip][$purpose])) {
                $output = $ipCache[$ip][$purpose];
            } else {
                $ipdat = @json_decode(file_get_contents("https://api.ipapi.com/" . $ip . "?access_key=" . IPAPI_KEY));
                if (@strlen(trim($ipdat->country_code)) == 2) {
                    switch ($purpose) {
                        case "location":
                            $output = array(
                                "city" => @$ipdat->city,
                                "state" => @$ipdat->region_name,
                                "country" => @$ipdat->country_name,
                                "country_code" => @$ipdat->country_code,
                                "continent" => @$ipdat->continent_name,
                                "continent_code" => @$ipdat->continent_code,
                            );
                            break;
                        case "address":
                            $address = array($ipdat->country_name);
                            if (@strlen($ipdat->region_name) >= 1)
                                $address[] = $ipdat->region_name;
                            if (@strlen($ipdat->city) >= 1)
                                $address[] = $ipdat->city;
                            $output = implode(", ", array_reverse($address));
                            break;
                        case "city":
                            $output = @$ipdat->city;
                            break;
                        case "state":
                        case "region":
                            $output = @$ipdat->region_name;
                            break;
                        case "country":
                            $output = @$ipdat->country_name;
                            break;
                        case "countrycode":
                            $output = @$ipdat->country_code;
                            break;
                    }
                }
                $ipCache[$ip][$purpose] = $output;
            }
        }

        return $output;
    }

    /**
     * Function to calculate the estimated reading time of the given text.
     *
     * @param string $text The text to calculate the reading time for.
     * @param int $wpm The rate of words per minute to use.
     * @return int
     */
    public static function estimateReadingTime(string $text, int $wpm = 200):int
    {
        $totalWords = str_word_count(strip_tags($text));
        $minutes = floor($totalWords / $wpm);
        $seconds = floor($totalWords % $wpm / ($wpm / 60));

        return ($minutes * 60) + $seconds;
    }

    /**
     * Clear Twig cache directory
     *
     * @return void
     */
    public static function clearTwigCache():void
    {
        if (defined('DIR_CACHE')) {
            File::delTree(DIR_CACHE . 'twig');
        }
    }

    public static function getApplicationHost(string $applicationName):false|string
    {
        $host = Db::create()->getFirstRow(
            Db::select(
                'hosts',
                [
                    'host_id AS id',
                    'host_host AS host',
                    'host_name AS name',
                ],
                [
                    'host_application' => $applicationName,
                    'host_client_id' => HostConfig::create()->getClientId()
                ]
            )
        );

        if(!Empty($host['host'])){
            return $host['host'];
        }

        return false;
    }
}