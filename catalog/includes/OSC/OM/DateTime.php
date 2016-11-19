<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;

class DateTime
{
    protected $datetime = false;

    protected $raw_pattern_date = 'Y-m-d';
    protected $raw_pattern_time = 'H:i:s';

    public function __construct($datetime, $use_raw_pattern = false, $strict = false)
    {
        if ($use_raw_pattern === false) {
            $pattern = OSCOM::getDef('date_time_format');
        } else {
            $pattern = $this->raw_pattern_date . ' ' . $this->raw_pattern_time;
        }

        // format time as 00:00:00 if it is missing from the date
        $new_datetime = strtotime($datetime);

        if ($new_datetime !== false) {
            $new_datetime = date($pattern, $new_datetime);

            $this->datetime = \DateTime::createFromFormat($pattern, $new_datetime);

            $strict_log = false;
        }

        if ($this->datetime === false) {
            $strict_log = true;
        } else {
            $errors = \DateTime::getLastErrors();

            if (($errors['warning_count'] > 0) || ($errors['error_count'] > 0)) {
                $this->datetime = false;

                $strict_log = true;
            }
        }

        if (($strict === true) && ($strict_log === true)) {
            trigger_error('DateTime: ' . $datetime . ' (' . $new_datetime . ') cannot be formatted to ' . $pattern);
        }
    }

    public function isValid()
    {
        return $this->datetime instanceof \DateTime;
    }

    public function get($pattern = null)
    {
        if (isset($pattern)) {
            return $this->datetime->format($pattern);
        }

        return $this->datetime;
    }

    public function getShort($with_time = false)
    {
        $pattern = ($with_time === false) ? OSCOM::getDef('date_format_short') : OSCOM::getDef('date_time_format');

        return strftime($pattern, $this->getTimestamp());
    }

    public function getLong()
    {
        return strftime(OSCOM::getDef('date_format_long'), $this->getTimestamp());
    }

    public static function toShort($raw_datetime, $with_time = false, $strict = true)
    {
        $result = '';

        $date = new DateTime($raw_datetime, true, $strict);

        if ($date->isValid()) {
            $pattern = ($with_time === false) ? OSCOM::getDef('date_format_short') : OSCOM::getDef('date_time_format');

            $result = strftime($pattern, $date->getTimestamp());
        }

        return $result;
    }

    public static function toLong($raw_datetime, $strict = true)
    {
        $result = '';

        $date = new DateTime($raw_datetime, true, $strict);

        if ($date->isValid()) {
            $result = strftime(OSCOM::getDef('date_format_long'), $date->getTimestamp());
        }

        return $result;
    }

    public function getRaw($with_time = true)
    {
        $pattern = $this->raw_pattern_date;

        if ($with_time === true) {
            $pattern .= ' ' . $this->raw_pattern_time;
        }

        return $this->datetime->format($pattern);
    }

    public function getTimestamp()
    {
        return $this->datetime->getTimestamp();
    }

    public static function getTimeZones()
    {
        $time_zones_array = [];

        foreach (\DateTimeZone::listIdentifiers() as $id) {
            $tz_string = str_replace('_', ' ', $id);

            $id_array = explode('/', $tz_string, 2);

            $time_zones_array[$id_array[0]][$id] = isset($id_array[1]) ? $id_array[1] : $id_array[0];
        }

        $result = [];

        foreach ($time_zones_array as $zone => $zones_array) {
            foreach ($zones_array as $key => $value) {
                $result[] = [
                    'id' => $key,
                    'text' => $value,
                    'group' => $zone
                ];
            }
        }

        return $result;
    }

    public static function setTimeZone($time_zone = null)
    {
        if (!isset($time_zone)) {
            $time_zone = OSCOM::configExists('time_zone') ? OSCOM::getConfig('time_zone') : date_default_timezone_get();
        }

        return date_default_timezone_set($time_zone);
    }
}
