<?php

/*
 * Copyright (C) 2016 amit
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tourpage\Helpers;

/**
 * Utility Class
 * @author amit
 */
class Utils {

    /**
     * The default page size for pagination.
     */
    const DEFAULT_PAGE_SIZE = 10;

    /**
     * Default currency code
     */
    const DEFAULT_CURRENCY_CODE = '$';
    const DATE_FORMAT_SHORT = 'm/d/Y';
    const DATE_FORMAT_MEDIUM = 'd M Y';
    const DATE_FORMAT_LONG = 'l, d F Y';

    /**
     * Print an Array to PRE format
     * @param array $array
     * @param boolean $exit
     * @param string $message
     */
    public static function printArray($array, $exit = true, $message = '') {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
        if ($exit) {
            exit($message);
        }
    }

    /**
     * Encrypt Password
     * Used method "md5"
     * @param type $string
     * @return mixed
     */
    public static function encryptPassword($string) {
        return md5($string);
    }

    /**
     * Generating Unique Id
     * @return mix
     */
    public static function getUid() {
        $urand = @fopen('/dev/urandom', 'rb');
        $pr_bits = false;
        if (is_resource($urand)) {
            $pr_bits .= @fread($urand, 16);
        }
        if (!$pr_bits) {
            $fp = @fopen('/dev/urandom', 'rb');
            if ($fp !== false) {
                $pr_bits .= @fread($fp, 16);
                @fclose($fp);
            } else {
                $pr_bits = "";
                for ($cnt = 0; $cnt < 16; $cnt ++) {
                    $pr_bits .= chr(mt_rand(0, 255));
                }
            }
        }
        $time_low = bin2hex(substr($pr_bits, 0, 4));
        $time_mid = bin2hex(substr($pr_bits, 4, 2));
        $time_hi_and_version = bin2hex(substr($pr_bits, 6, 2));
        $clock_seq_hi_and_reserved = bin2hex(substr($pr_bits, 8, 2));
        $node = bin2hex(substr($pr_bits, 10, 6));

        $time_hi_and_version = hexdec($time_hi_and_version);
        $time_hi_and_version = $time_hi_and_version >> 4;
        $time_hi_and_version = $time_hi_and_version | 0x4000;

        $clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

        return strtolower(sprintf('%08s-%04s-%04x-%04x-%012s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node));
    }

    /**
     * Remove special character and spaces with $replace
     * Lowercasing the string
     * @param string $string
     * @param string $replace
     * @return string
     */
    public static function slug($string, $replace = '-') {
        if ($replace == ' ') {
            $replace = '-';
        }
        $slug = preg_replace('/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', '', strtolower($string));
        $slug = str_replace(' ', $replace, $slug);
        $slug = trim($slug, $replace);

        $characters = str_split($slug);
        if (count($characters) > 0) {
            $previusCharacter = NULL;
            foreach ($characters as $i => $c) {
                if (!empty($c)) {
                    if ($previusCharacter == $replace && $c == $replace) {
                        unset($characters[$i]);
                    }
                    $previusCharacter = $c;
                } else {
                    unset($characters[$i]);
                }
            }
        }
        $slug = implode('', $characters);
        return $slug;
    }

    /**
     * Currency formatter
     * Adding currency symbol at the beginning
     * @param number $number
     * @return mix
     */
    public static function formatCurrency($number) {
        return self::DEFAULT_CURRENCY_CODE . self::formatNumber($number, 2);
    }

    /**
     * Format number
     * @param number $number
     * @return float
     */
    public static function formatNumber($number) {
        return number_format($number, 2);
    }

    /**
     * Encode String
     * @param string $string
     * @return string
     */
    public static function encodeString($string) {
        return htmlspecialchars($string, ENT_QUOTES);
    }

    /**
     * Decode string
     * @param string $string
     * @return string
     */
    public static function decodeString($string) {
        return htmlspecialchars_decode($string, ENT_QUOTES);
    }

    /**
     * Format Date from time
     * @param int $time
     * @param string $format
     * @return string
     */
    public static function formatDate($time, $format = 'Y-m-d') {
        if (is_numeric($time)) {
            $time = gmdate('Y-m-d', $time);
        }
        $dateTime = new \DateTime($time);
        return $dateTime->format($format);
    }

    /**
     * Generate the days (1 - 31) for a month
     * Use for HTML Dropdown list
     * @return array
     */
    public static function getDays() {
        $days = array();
        for ($d = 1; $d <= 31; $d ++) :
            $days[$d] = $d;
        endfor;
        return $days;
    }

    /**
     * Week Days
     * Compatible with PHP date() function
     * 0 for Sunday, 6 for Saturday
     * @param number $wd
     * @return Ambigous <multitype:string , array>
     */
    public static function weekDays($wd = NULL, $full = TRUE) {
        $weekDays = array(
            1 => ($full ? 'Monday' : 'Mon'),
            2 => ($full ? 'Tuesday' : 'Tue'),
            3 => ($full ? 'Wednesday' : 'Wed'),
            4 => ($full ? 'Thursday' : 'Thu'),
            5 => ($full ? 'Friday' : 'Fri'),
            6 => ($full ? 'Saturday' : 'Sat'),
            0 => ($full ? 'Sunday' : 'Sun')
        );

        return $wd != NULL ? (isset($weekDays[$wd]) ? $weekDays[$wd] : '') : $weekDays;
    }

    /**
     * Generate the months list of an year
     * Use for HTML Dropdown list
     * @return array
     */
    public static function getMonths() {
        return array(
            '1' => self::encodeString('January'),
            '2' => self::encodeString('February'),
            '3' => self::encodeString('March'),
            '4' => self::encodeString('April'),
            '5' => self::encodeString('May'),
            '6' => self::encodeString('June'),
            '7' => self::encodeString('July'),
            '8' => self::encodeString('August'),
            '9' => self::encodeString('September'),
            '10' => self::encodeString('October'),
            '11' => self::encodeString('November'),
            '12' => self::encodeString('December')
        );
    }

    /**
     * Generate a range of year
     */
    public static function getYears($htmlOptions = array()) {
        $years = array();
        $start = 1900;
        if (isset($htmlOptions['start'])) {
            $start = $htmlOptions['start'];
        }
        $end = self::__getCurrentYear();
        if (isset($htmlOptions['end'])) {
            $end = $htmlOptions['end'];
        }
        if (isset($htmlOptions['range'])) {
            if (is_array($htmlOptions['range'])) {
                $start = self::__getCurrentYear() - (isset($htmlOptions['range']['start']) ? $htmlOptions['range']['start'] : 0);
                $end = self::__getCurrentYear() + (isset($htmlOptions['range']['end']) ? $htmlOptions['range']['end'] : 0);
            } else {
                $start = self::__getCurrentYear() - $htmlOptions['range'];
                $end = self::__getCurrentYear() + $htmlOptions['range'];
            }
        }

        for ($y = $start; $y <= $end; $y ++) {
            $years[$y] = $y;
        }
        return $years;
    }

    /**
     * Get the current date (01 - 31)
     * @return string
     */
    public static function __getCurrentDay() {
        return date('d');
    }

    /**
     * Get the current Month (1 -12)
     * @return int
     */
    public static function __getCurrentMonth() {
        return date('n');
    }

    /**
     * Get the current year
     * @return int
     */
    public static function __getCurrentYear() {
        return date('Y');
    }

    /**
     * Return Current Date
     * @param string $format
     * @return string
     */
    public static function currentDate($format = 'Y-m-d') {
        $time = gmdate('Y-m-d', time());
        $dateTime = new \DateTime($time);
        return $dateTime->format($format);
    }

    /**
     * Format jQuery Datepicker date to MySql format
     * @param string $date
     * @return string
     */
    public static function formatDatepickerToMySql($date) {
        list($month, $day, $year) = explode('/', $date);
        return ($year . '-' . $month . '-' . $day);
    }

    /**
     * Format MySql date to Datepicker date format
     * @param type $date
     * @return type
     */
    public static function formatMySqlToDatepicker($date) {
        list($year, $month, $day) = explode('-', $date);
        return ($month . '/' . $day . '/' . $year);
    }

    /**
     * Calculating date difference between two dates
     * @param string $start
     * @param string $end
     * @return string
     */
    public static function dateDiff($start, $end) {
        $dateStart = new \DateTime($start);
        $dateEnd = new \DateTime($end);
        $diff = $dateEnd->diff($dateStart)->format("%a Day(s)");
        return $diff;
    }

    /**
     * Return all dates between two date
     * @param string $startDate
     * @param string $endDate
     * @param string $format
     * @param bool $string
     * @return array
     */
    public static function getDates($startDate, $endDate, $format = 'Y-m-d', $string = false) {
        $dates = array();
        $startDate = \DateTime::createFromFormat($format, $startDate);
        $endDate = \DateTime::createFromFormat($format, $endDate);
        $datePeriod = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->modify('+1 day'));
        foreach ($datePeriod as $date) {
            $dates[] = $string ? '"' . $date->format($format) . '"' : $date->format($format);
        }
        return $dates;
    }

    /**
     * Getting variable from table(variable)
     * If variable not found, default value will return
     * @see \Tourpage\Models\Variable
     * @param mix $variableKey
     * @param mix $defaultValue
     * @return mix
     */
    public static function getVar($variableKey, $defaultValue = '') {
        $value = $defaultValue;
        $variable = \Tourpage\Models\Variable::findFirstByVariableKey($variableKey);
        if ($variable) {
	    $data = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $variable->variableData);
            $value = unserialize($data);
        }
        return $value;
    }

    /**
     * Set or insert new variable to the table(variable)
     * If variable key already exista then update the value of it
     * @param mix $variableKey
     * @param mix $variableValue
     */
    public static function setVar($variableKey, $variableValue) {
        if (!empty($variableKey) && !empty($variableValue)) {
            $variableValue = serialize($variableValue);
            $variable = \Tourpage\Models\Variable::findFirstByVariableKey($variableKey);
            if ($variable) {
                $variable->variableData = $variableValue;
                $variable->save();
            } else {
                $variable = new \Tourpage\Models\Variable();
                $variable->variableKey = $variableKey;
                $variable->variableData = $variableValue;
                $variable->save();
            }
        }
    }

    /**
     * Remove variable key and its value from table(variable)
     * @param mix $variableKey
     */
    public static function delVar($variableKey) {
        $variable = \Tourpage\Models\Variable::findFirstByVariableKey($variableKey);
        if ($variable) {
            $variable->delete();
        }
    }

    /**
     * Credit Card List
     * @return array
     */
    public static function getCcList($type = '') {
        $cards = array(
            'VISA' => 'Visa',
            'MASTERCARD' => 'Master Card',
            //'MAESTRO' => 'Maestro',
            'JCB' => 'JCB',
            'DISCOVER' => 'Discover',
            //'DINERS' => 'Diners Club',
            'AMEX' => 'American Express',
                //'SOLO' => 'Solo',
                //'SWITCH' => 'Switch'
        );
        return empty($type) ? $cards : (isset($cards[$type]) ? $cards[$type] : '');
    }

    /**
     * Padding integer with leading 0(zero)
     * @param int $int
     * @return string|int
     */
    public static function padInt($int) {
        return strlen($int) > 1 ? $int : '0' . $int;
    }

    /**
     * Retriving Longitude and Latitude by Google Geocode Api
     * @param string $address
     * @return object
     */
    public static function getGeolocation($address = '') {
        $location = new \stdClass();
        $location->lat = $location->lng = 0;
        $address = urlencode($address);
        $request_url = "http://maps.googleapis.com/maps/api/geocode/xml?address=" . $address . "&sensor=true";
        $xml = @simplexml_load_file($request_url);
        if ($xml->status && $xml->status == "OK") {
            $location->lat = (string) $xml->result->geometry->location->lat;
            $location->lng = (string) $xml->result->geometry->location->lng;
        }
        return $location;
    }

}
