<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

class DateTime
{
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
            $time_zone = defined('CFG_TIME_ZONE') ? CFG_TIME_ZONE : date_default_timezone_get();
        }

        return date_default_timezone_set($time_zone);
    }
}
