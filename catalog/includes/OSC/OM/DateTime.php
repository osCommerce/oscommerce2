<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license GPL; https://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;

class DateTime
{
    protected $datetime;
    protected $with_time = true;

    public function __construct($datetime, $with_time = true)
    {
        $this->with_time = ($with_time === true);

        // the exclamation point prevents the current time being used
        $php_pattern = ($this->with_time === true) ? DATE_TIME_FORMAT : '!' . DATE_FORMAT;

        $this->datetime = \DateTime::createFromFormat($php_pattern, $datetime);

        if ($this->datetime !== false) {
            $errors = \DateTime::getLastErrors();

            if (($errors['warning_count'] > 0) || ($errors['error_count'] > 0)) {
                $this->datetime = false;
            }
        }
    }

    public function isValid()
    {
        return $this->datetime instanceof \DateTime;
    }

    public function get()
    {
        return $this->datetime;
    }

    public function getRaw($with_time  = true)
    {
        $pattern = 'Y-m-d';

        if (($with_time === true) && ($this->with_time === true)) {
            $pattern .= ' H:i:s';
        }

        return $this->datetime->format($pattern);
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
