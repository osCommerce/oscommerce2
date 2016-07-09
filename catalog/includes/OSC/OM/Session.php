<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license GPL; https://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

class Session
{
    public static function load($name = null)
    {
        $class_name = 'OSC\\OM\\Session\\' . STORE_SESSIONS;

        if (!class_exists($class_name)) {
            trigger_error('Session Handler \'' . $class_name . '\' does not exist, using default \'OSC\\OM\\Session\\File\'', E_USER_NOTICE);

            $class_name = 'OSC\\OM\\Session\\File';
        } elseif (!is_subclass_of($class_name, 'OSC\OM\SessionAbstract')) {
            trigger_error('Session Handler \'' . $class_name . '\' does not extend OSC\\OM\\SessionAbstract, using default \'OSC\\OM\\Session\\File\'', E_USER_NOTICE);

            $class_name = 'OSC\\OM\\Session\\File';
        }

        $obj = new $class_name();

        if (!isset($name)) {
            $name = 'oscomid';
        }

        $obj->setName($name);

        $force_cookies = false;

        if ((HTTP_COOKIE_DOMAIN == HTTPS_COOKIE_DOMAIN) && (HTTP_COOKIE_PATH == HTTPS_COOKIE_PATH)) {
            $force_cookies = true;
        }

        $obj->setForceCookies($force_cookies);

        return $obj;
    }
}
