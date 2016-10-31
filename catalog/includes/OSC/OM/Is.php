<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license GPL; https://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

class Is
{
    public static function __callStatic($name, $arguments)
    {
        if (class_exists(__NAMESPACE__ . '\\Is\\' . $name)) {
            return (bool)call_user_func_array([
                __NAMESPACE__ . '\\Is\\' . $name,
                'execute'
            ], $arguments);
        }

        return false;
    }
}
