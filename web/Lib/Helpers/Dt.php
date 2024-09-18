<?php

namespace Framework\Helpers;

use DateTime;
use DateTimeZone;
use Exception;
use Framework\Locale\Translate;

class Dt
{
    /**
     * @throws Exception
     */
    public static function convertTime(string $dateTime, string $tzFrom = TIMEZONE_SERVER, string $tzTo = TIMEZONE_SOURCE, string $format = 'Y-m-d H:i:s'):string
    {
        $dt = new DateTime($dateTime, new DateTimeZone($tzFrom));
        $dt->setTimeZone(new DateTimeZone($tzTo));

        return $dt->format($format);
    }


    public static function convertToUTCTime(int $timeStamp, string $originalTimeZone, string $format = 'Y-m-d H:i:s'):string
    {
        $datetime = new DateTime(date('Y-m-d H:i:s', $timeStamp), new DateTimeZone($originalTimeZone));
        $tz = new DateTimeZone(SERVER_TIME_ZONE);
        return $datetime->setTimezone($tz)->format($format);
    }

    /**
     * Return an array of dates between the two dates
     * @param string $start
     * @param string $end
     * @return array Array of dates
     * @throws Exception
     */
    public static function splitDateInterval(string $start, string $end):array
    {
        $dates = [];

        $diff = self::dateDiffDays($start, $end);
        $dt = strtotime($start);
        for($i = 0; $i <= $diff; $i++){
            $dates[] = date('Y-m-d', $dt + 60*60*24*$i);
        }

        return $dates;
    }

    /**
     * Convert date to standard date format: YYYY-MM-DD
     *
     * @param string $date
     * @param string $format
     * @return ?string
     */
    public static function standardDate(string $date, string $format = 'Y-m-d'):?string
    {
        if (empty($date) OR $date=='0000-00-00') return null;

        $date = str_replace(['/', '.'], '-', trim($date, '.'));
        $dt = strtotime($date);

        return date($format, $dt);
    }

    /**
     * Convert date to standard date-time format: YYYY-MM-DD HH:II:SS
     *
     * @param string $timestamp
     * @param string $format
     * @return string
     */
    public static function standardDateTime(string $timestamp, string $format = 'Y-m-d H:i:s'):string
    {
        if (empty($timestamp) OR $timestamp == '0000-00-00 00:00:00') return '';

        $timestamp = str_replace(['/', '.'], '-', trim($timestamp, '.'));
        $dt = strtotime($timestamp);

        return date($format, $dt);
    }

    /**
     * Convert time to standard time format: HH:II:SS
     *
     * @param string $time
     * @return string|null
     */
    public static function standardTime(string $time):?string
    {
        if (empty($time) || $time === '00:00:00') return null;

        $time = str_replace(['/', '.', '-'], ':', trim($time, '.'));
        $dt = strtotime($time);

        return date('H:i:s', $dt);
    }

    /**
     * Calculate day difference between 2 dates
     *
     * @param string $date1
     * @param string $date2
     * @return int
     * @throws Exception
     */
    public static function dateDiffDays(string $date1, string $date2):int
    {
        $date1 = new DateTime($date1);
        $date2 = new DateTime($date2);

        $interval = $date1->diff($date2);

        return $interval->format('%r%a');
    }

    /**
     * Convert minutes (int) to H:I format
     * @old name: convertToHoursMin
     * @param string $time
     * @param string $format
     * @return string
     */
    public static function minutesToHHMM(string $time, string $format = '%02d:%02d'):string
    {
        if ($time < 1) {
            return '';
        }

        $hours = floor($time / 60);
        $minutes = ($time % 60);

        return sprintf($format, $hours, $minutes);
    }

    /**
     * Get how many days is the diff between now and the given date
     *
     * @param string $date
     * @return int
     * @throws Exception
     */
    public static function daysAhead(string $date):int
    {
        $now = date('Y-m-d');

        return self::dateDiffDays($now, $date);
    }

    /**
     * Add given days to a date
     *
     * @param string $date
     * @param int $days
     * @return string
     * @throws Exception
     */
    public static function dateAddDays(string $date = 'now', int $days = 7):string
    {
        $date = new DateTime($date);
        if ($days > 0) $days = '+' . $days;
        $date->modify($days . " days");

        return $date->format('Y-m-d');
    }


    /**
     * Check for a given date whether is it in a date range
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $checkDate
     * @return bool
     */
    public static function isDateInRange(string $startDate, string $endDate, string $checkDate):bool
    {
        $start_ts = strtotime($startDate);
        $end_ts = strtotime($endDate);
        $check_ts = strtotime($checkDate);

        return (($check_ts >= $start_ts) && ($check_ts <= $end_ts));
    }

    /**
     * Check date format validity
     *
     * @param string $date
     * @return bool
     */
    public static function validateDate(string $date):bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);

        return $d && $d->format('Y-m-d') == $date;
    }

    public static function getQuarter(DateTime $DateTime):array
    {
        $q = 1;
        $y = $DateTime->format('Y');
        $m = $DateTime->format('m');

        try {
            switch ($m) {
                case $m >= 1 && $m <= 3:
                default:
                    $start = $y . '-01-01';
                    $end = (new DateTime($y . '-03-01'))->modify('Last day of this month')->format('Y-m-d');
                    break;

                case $m >= 4 && $m <= 6:
                    $start = $y . '-04-01';
                    $end = (new DateTime($y . '-06-01'))->modify('Last day of this month')->format('Y-m-d');
                    $q = 2;
                    break;

                case $m >= 7 && $m <= 9:
                    $start = $y . '-07-01';
                    $end = (new DateTime($y . '-09-01'))->modify('Last day of this month')->format('Y-m-d');
                    $q = 3;
                    break;

                case $m >= 10 && $m <= 12:
                    $start = $y . '-10-01';
                    $end = (new DateTime($y . '-12-01'))->modify('Last day of this month')->format('Y-m-d');
                    $q = 4;
                    break;
            }
        }catch (Exception $e){
            die($e->getMessage());
        }

        return [
            'start'     => $start,
            'end'       => $end,
            'title'     => 'Q' . $q . ' ' . $y,
            'quarter'   => $q,
            'start_nix' => strtotime($start),
            'end_nix'   => strtotime($end)
        ];
    }

    /**
     * @throws Exception
     */
    public static function getQuarterPeriods(int $year, int $quarter):array
    {
        $out = [];
        switch($quarter){
            case 1:
                $out['start'] = $year . '-01-01';
                $out['end'] = (new DateTime($year . '-03-01'))->modify('Last day of this month')->format('Y-m-d');
                break;
            case 2:
                $out['start'] = $year . '-04-01';
                $out['end'] = (new DateTime($year . '-06-01'))->modify('Last day of this month')->format('Y-m-d');
                break;
            case 3:
                $out['start'] = $year . '-07-01';
                $out['end'] = (new DateTime($year . '-09-01'))->modify('Last day of this month')->format('Y-m-d');
                break;
            case 4:
                $out['start'] = $year . '-10-01';
                $out['end'] = (new DateTime($year . '-12-01'))->modify('Last day of this month')->format('Y-m-d');
                break;
        }

        return $out;
    }

    public static function calculateNextDueDate(int $recurrence, string|false $fromDate = false):string
    {
        $date = false;
        $mod = false;

        if(!$fromDate) {
            $fromDate = date('Y-m-d');
        }else{
            $fromDate = self::standardDate($fromDate);
        }

        switch($recurrence){
            case 1:     // daily
                $mod = '+1 day';
                break;
            case 2:     // 2 weeks
                $mod = '+2 week';
                break;
            case 3:     // monthly
                $mod = '+1 month';
                break;
            case 4:     // quarterly
                $mod = '+3 month';
                break;
            case 5:     // half-yearly
                $mod = '+6 month';
                break;
            case 6:     // yearly
                $mod = '+1 year';
                break;
            case 7:     // every 2 years
                $mod = '+2 year';
                break;
            case 8:     // every 4 years
                $mod = '+4 year';
                break;
        }

        if($mod) {
            $date = strtotime($fromDate . ' ' . $mod);
            $date = date('Y-m-d', $date);
        }

        return $date;
    }

    /**
     * Check two date range whether they are overlapping each other
     * @param string $startDate1
     * @param string $endDate1
     * @param string $startDate2
     * @param string $endDate2
     * @return bool
     */
    public static function checkDatesOverlapping(string $startDate1, string $endDate1, string $startDate2, string $endDate2):bool
    {
        return (($startDate1 <= $endDate2) && ($endDate1 >= $startDate2));
    }

    /**
     * Calculate age (in years) from birthdate
     *
     * @param string $birthDate
     * @param bool|string $fromDate if parameter is given, calculation is started from the given date instead of current date
     * @return int
     */
    public static function getAgeFromBirthdate(string $birthDate = '', string|bool $fromDate = false ):int
    {
        $age = false;
        if(!$fromDate) {
            $fromDate = time();
        }else{
            $fromDate = self::standardDate($fromDate);
            $fromDate = strtotime($fromDate);
            if($fromDate<time()) $fromDate = time();
        }
        $birthDate = explode("-", self::standardDate($birthDate)); # date parts
        if( !empty($birthDate[0]) ) $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md", $fromDate) ? ((date("Y", $fromDate) - $birthDate[0]) - 1) : (date("Y", $fromDate) - $birthDate[0]));

        # get age in years
        return $age;
    }


    /**
     * Calculate age (in month) from birthdate
     *
     * @param string $birthDate
     * @param string|false $fromDate
     * @return int
     */
    public static function getAgeInMonth(string $birthDate, string|false $fromDate = false):int
    {
        if(!$fromDate) $fromDate = time();
        $now = strtotime( $fromDate );
        $leapDays = self::getNumOfLeapDays($birthDate, $now);

        return intval( (( ($now - $birthDate)/(3600*24) )-$leapDays)/365 * 12 );
    }

    /**
     * Get leap days in a given year
     *
     * @param string $from
     * @param string $to
     * @return int
     */
    public static function getNumOfLeapDays(string $from, string $to):int
    {
        if ( (checkdate(2,29,date('Y',$from))) AND ($from > mktime(0,0,0,2,29, date('Y', $from))) ) {
            $fromYear = (int) date('Y', $from) + 1;
        }else {
            $fromYear = date('Y', $from);
        }

        if ( (checkdate(2,29,date('Y',$to))) AND ($to<mktime(0,0,0,2,29, date('Y', $to))) ) {
            $toYear = date('Y', $to) - 1;
        }else {
            $toYear = date('Y', $to);
        }

        $numOfLeapDays = 0;

        for($i=$fromYear; $i<=$toYear; $i++) {
            if ((($i % 4) == 0) AND ((($i % 400) == 0) OR (($i % 100) <>0))) $numOfLeapDays++;
        }

        return $numOfLeapDays;
    }

    public static function formatHour(int $hour):string
    {
        $totalSec = $hour * 60 * 60;

        return date('H:i:s', strtotime(date('Y-m-d 00:00:00')) + $totalSec);
    }

    /**
     * Convert time format (hh:ii) to number (XX min)
     *
     * @param string $time
     * @param int $unit
     * @return float
     */
    public static function timeToNumber(string $time, int $unit = 5):float
    {
        $time = explode(':', $time);
        $minutes = 0;
        switch (count($time)) {
            case 1:
                $minutes = $time[0];
                break;
            case 2:
            case 3:
                $minutes += (int)($time[0] * 60) + (int)$time[1];
                break;
        }

        if (empty($unit)) $unit = 1;

        return round($minutes / $unit);
    }

    /**
     * Convert number (XX sec/min) to time format (hh:ii)
     *
     * @param int $number
     * @param int $unit
     * @return string
     */
    public static function numberToTime(int $number, int $unit = 5):string
    {
        $time = '';
        $minutes = $number * $unit;
        $hours = floor($minutes / 60);
        if ($hours > 0) {
            if ($hours < 10) $time .= '0';
            $time .= $hours . ':';
            $minutes -= $hours * 60;
        } else {
            $time .= '00:';
        }
        if ($minutes < 10) $time .= '0';
        $time .= $minutes;

        return $time;
    }

    /**
     * Convert date format (yyyy-mm-dd) to number (XX days)
     *
     * @param string $date
     * @return int
     */
    public static function dateToDay(string $date):int
    {
        $date = str_replace(['/', '.'], '-', trim($date, '.'));
        $dt = strtotime($date);
        $day = round(date('U', $dt) / 3600 / 24);

        return $day;
    }

    /**
     * Convert number (XX days) to date format (yyyy-dd-mm)
     *
     * @param int $day
     * @return string
     */
    public static function dayToDate(int $day):string
    {
        return date('Y-m-d', $day * 3600 * 24);
    }

    public static function formatDayOfWeek(string $dow):string
    {
        $out = '';
        $dow = explode('|', trim($dow, '|'));

        if (is_array($dow)) {
            $tmp = [];
            foreach ($dow as $day) {
                $tmp[] = Translate::get('LBL_DAY_SHORT_' . $day);
            }

            $out = implode('/', $tmp);
        }

        return $out;
    }

}